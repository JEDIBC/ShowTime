<?php
namespace ShowTime\Provider;

use Monolog\Logger;
use ShowTime\Schedule\Episode;
use Goutte\Client;
use ShowTime\Tools\String;

class Nyaaruto implements ProviderInterface
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
     * @return array
     */
    protected function getEpisodeList()
    {
        if (!isset($this->cache['list'])) {
            $list   = array();
            $client = new Client();
            /* @var $crawler \Symfony\Component\DomCrawler\Crawler */
            $crawler = $client->request('get', 'http://www.nyaa.eu/?page=torrents&user=92232');
            $nodes   = $crawler->filter("tr.tlistrow");
            if ($nodes->count()) {
                $list = $nodes->each(
                    function ($node) {
                        $xml = simplexml_import_dom($node);

                        return array(
                            'episode' => trim((string)$xml->td[1]->a),
                            'torrent' => trim((string)$xml->td[2]->a['href'])
                        );
                    }
                );
            }
            $this->logger->debug(var_export($list, true));
            $this->cache['list'] = $list;
        }

        return $this->cache['list'];
    }

    /**
     * @param \ShowTime\Schedule\Episode $episode
     * @return bool|string
     */
    public function search(Episode $episode)
    {
        // retrieve episode list
        $episodeList = $this->getEpisodeList();

        foreach ($episodeList as $item) {
            $episodeName = new String($item['episode']);
            $this->logger->debug('Test ' . $item['episode'] . '...');
            if ($episodeName->contains(' ' . $episode->getGlobal() . ' ')) {
                if ($episodeName->contains($this->config['keywords']['include']) && !$episodeName->contains($this->config['keywords']['exclude'])) {
                    $this->logger->info('Found :' . $item['episode']);

                    return $item['torrent'];
                }
            }
        }

        return false;
    }

    /**
     * @param string $file
     * @return bool
     */
    public function download($file)
    {
        $output = shell_exec("transmission-remote -a " . $file);
        $string = new String($output);

        return $string->contains(array('responded', 'success'));
    }

}