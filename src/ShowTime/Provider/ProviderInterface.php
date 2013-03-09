<?php
namespace ShowTime\Provider;

use Monolog\Logger;
use ShowTime\Schedule\Episode;

interface ProviderInterface
{

    /**
     * @param array           $config
     * @param \Monolog\Logger $logger
     */
    public function __construct($config, Logger $logger);

    /**
     * @param \ShowTime\Schedule\Episode $episode
     * @return bool|string
     */
    public function search(Episode $episode);

    /**
     * @param string $file
     * @return bool
     */
    public function download($file);
}