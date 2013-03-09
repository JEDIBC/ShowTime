<?php
namespace ShowTime\Schedule;

use Monolog\Logger;
use PhpSeries\Client;

class BetaSeries implements ScheduleInterface
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
     * @var Client
     */
    protected $phpSeries;

    /**
     * @var string
     */
    protected $token;

    /**
     * @param array           $config
     * @param \Monolog\Logger $logger
     */
    public function __construct($config, Logger $logger)
    {
        $this->logger    = $logger;
        $this->config    = $config;
        $this->phpSeries = new Client($config['apiKey']);
        $this->phpSeries->setLogger($logger);
    }

    /**
     * @return string
     */
    protected function getToken()
    {
        if (empty($this->token)) {
            $data = $this->phpSeries->membersAuth($this->config['login'], $this->config['md5Password']);
            $this->logger->debug(var_export($data, true));

            $this->token = $data['member']['token'];
            $this->logger->debug('Token = ' . $this->token);
        }

        return $this->token;
    }

    /**
     * @return array of not downloaded Episode
     */
    public function getShows()
    {
        $episodes = array();

        // Retrieve episode list
        $data = $this->phpSeries->membersEpisodes($this->getToken(), 'all', null, 99);
        $this->logger->debug(var_export($data, true));

        if (isset($data['episodes'])) {
            foreach ($data['episodes'] as $episode) {
                // get only not downloaded episodes
                if (isset($episode['downloaded']) && ('0' === $episode['downloaded'])) {
                    $toDownload = new Episode(
                        $episode['url'],
                        $episode['show'],
                        $episode['title'],
                        (int)$episode['season'],
                        (int)$episode['episode'],
                        (int)$episode['global']
                    );
                    $this->logger->info('Beta Series - Episode to download : ' . (string)$toDownload);
                    $episodes[] = $toDownload;
                }
            }
        }

        return $episodes;
    }

    /**
     * @param Episode $episode
     * @return mixed|void
     */
    public function markDownloaded(Episode $episode)
    {
        $this->phpSeries->membersDownloaded($this->getToken(), $episode->getShowId(), $episode->getSeason(), $episode->getEpisode());
    }
}