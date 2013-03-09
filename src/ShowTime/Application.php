<?php
namespace ShowTime;

use Monolog\Logger;
use ShowTime\Schedule\ScheduleInterface;
use ShowTime\Schedule\Episode;
use ShowTime\Provider\ProviderInterface;

class Application
{

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var array
     */
    protected $schedules = array();

    /**
     * @var array
     */
    protected $providers = array();

    /**
     * @var array
     */
    protected $routing = array();

    /**
     * @param array           $scheduleConfig
     * @param array           $providerConfig
     * @param array           $routing
     * @param \Monolog\Logger $logger
     */
    public function __construct($scheduleConfig, $providerConfig, $routing, Logger $logger)
    {
        $this->logger  = $logger;
        $this->routing = $routing;

        // Initialize schedules
        foreach ($scheduleConfig as $name => $definition) {
            $className              = '\\ShowTime\\Schedule\\' . $definition['class'];
            $this->schedules[$name] = new $className($definition['config'], $logger);
        }

        // Initialize providers
        foreach ($providerConfig as $name => $definition) {
            $className              = '\\ShowTime\\Provider\\' . $definition['class'];
            $this->providers[$name] = new $className($definition['config'], $logger);
        }
    }

    /**
     * @return array
     */
    public function getSchedulesList()
    {
        return array_keys($this->schedules);
    }

    /**
     * @param Schedule\ScheduleInterface $schedule
     * @param bool                       $dryRun
     */
    protected function getEpisodes(ScheduleInterface $schedule, $dryRun = false)
    {
        $episodes = $schedule->getShows();
        /* @var $episode Episode */
        foreach ($episodes as $episode) {
            $providerName = array_key_exists($episode->getShowId(), $this->routing['routes']) ? $this->routing['routes'][$episode->getShowId()] : $this->routing['default'];
            /* @var $provider ProviderInterface */
            $provider = $this->providers[$providerName];
            $this->logger->info('Searching ' . $episode . '...');
            $file = $provider->search($episode);
            if (false !== $file) {
                $this->logger->info('Found : ' . $file);
                if (!$dryRun) {
                    if ($provider->download($file)) {
                        $schedule->markDownloaded($episode);
                    }
                }
            } else {
                $this->logger->info('Found nothing');
            }
        }
    }

    /**
     * @param bool $dryRun
     * @param null $schedule
     */
    public function handle($dryRun = false, $schedule = null)
    {
        if (is_null($schedule)) {
            foreach ($this->schedules as $schedule) {
                $this->getEpisodes($schedule, $dryRun);
            }
        } else {
            $this->getEpisodes($this->schedules[$schedule], $dryRun);
        }
    }

    /**
     * @return \Monolog\Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }
}