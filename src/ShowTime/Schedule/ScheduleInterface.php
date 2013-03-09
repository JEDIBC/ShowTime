<?php
namespace ShowTime\Schedule;

use Monolog\Logger;
use ShowTime\Schedule\Episode;

interface ScheduleInterface {

    /**
     * @param                 $config
     * @param \Monolog\Logger $logger
     */
    public function __construct($config, Logger $logger);

    /**
     * @return array of Episode
     */
    public function getShows();

    /**
     * @param Episode $episode
     * @return mixed
     */
    public function markDownloaded(Episode $episode);
}