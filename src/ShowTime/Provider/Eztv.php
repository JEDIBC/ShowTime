<?php
namespace ShowTime\Provider;

use Goutte\Client;
use Monolog\Logger;
use ShowTime\Schedule\Episode;
use ShowTime\Tools\String;

class Eztv implements ProviderInterface
{

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var array
     */
    protected $cache = array();

    /**
     * @param array           $config
     * @param \Monolog\Logger $logger
     */
    public function __construct($config, Logger $logger)
    {
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * @param \ShowTime\Schedule\Episode $episode
     *
     * @return bool|string
     */
    public function search(Episode $episode)
    {
        // check for show name replacement
        $showName = isset($this->config['shows'][$episode->getShowId()]) ? $this->config['shows'][$episode->getShowId()] : $episode->getShow();

        // retrieve show list
        $showList = $this->getShowListUrl();

        $path = isset($showList[$showName]) ? $showList[$showName] : false;
        if (false === $path) {
            return false;
        }

        // retrieve episode list
        $episodeList = $this->getEpisodeList($path);

        foreach ($episodeList as $item) {
            if (!is_null($item)) {
                $episodeName = new String($item['episode']);
                $this->logger->debug('Test ' . $item['episode'] . '...');
                if ($episodeName->contains(sprintf('%dx%02d', $episode->getSeason(), $episode->getEpisode())) || $episodeName->contains(sprintf('S%02dE%02d', $episode->getSeason(), $episode->getEpisode()))) {
                    if ($episodeName->contains($this->config['keywords']['include']) && !$episodeName->contains($this->config['keywords']['exclude'])) {
                        return $item['magnet'];
                    }
                }
            }
        }

        return false;
    }

    /**
     * @return array
     */
    protected function getShowListUrl()
    {
        if (!isset($this->cache['show.list.url'])) {
            $list   = array();
            $client = new Client();
            /* @var $crawler \Symfony\Component\DomCrawler\Crawler */
            $crawler = $client->request('get', 'http://eztv.it/showlist/');
            $nodes   = $crawler->filter("a.thread_link");
            if ($nodes->count()) {
                $data = $nodes->each(
                    function ($node) {
                        $xml = simplexml_import_dom($node);

                        return array(
                            'show' => trim((string)$xml),
                            'path' => trim((string)$xml['href'])
                        );
                    }
                );
                foreach ($data as $item) {
                    $list[$item['show']] = $item['path'];
                }
                $this->cache['show.list.url'] = $list;
            }
        }

        return $this->cache['show.list.url'];
    }

    /**
     * @param string $path
     *
     * @return array
     */
    protected function getEpisodeList($path)
    {
        $list   = array();
        $client = new Client();
        /* @var $crawler \Symfony\Component\DomCrawler\Crawler */
        $crawler = $client->request('get', 'http://eztv.it' . $path);
        $nodes   = $crawler->filter("tr.forum_header_border");
        if ($nodes->count()) {
            $list = $nodes->each(
                function ($node) {
                    $xml = simplexml_import_dom($node);

                    if (isset($xml->td[1]->a) && isset($xml->td[2]->a[0]['href'])) {
                        return array(
                            'episode' => trim((string)$xml->td[1]->a),
                            'magnet'  => trim((string)$xml->td[2]->a[0]['href'])
                        );
                    } else {
                        return null;
                    }
                }
            );
        }
        $this->logger->debug(var_export($list, true));

        return $list;
    }

    /**
     * @param string $file
     *
     * @return bool
     */
    public function download($file)
    {
        $output = shell_exec(escapeshellcmd("transmission-remote 127.0.0.1:9091 -a " . $file));
        $string = new String($output);

        return $string->contains(array('responded', 'success'));
    }

}