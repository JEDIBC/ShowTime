<?php
namespace ShowTime\Handler;

use Monolog\Handler\AbstractProcessingHandler;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleHandler extends AbstractProcessingHandler {

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param bool                                              $level
     * @param bool                                              $bubble
     */
    public function __construct(OutputInterface $output, $level = Logger::DEBUG, $bubble = true)
    {
        $this->output = $output;
        parent::__construct($level, $bubble);
    }

    /**
     * @param array $record
     */
    protected function write(array $record)
    {
        $this->output->writeln(trim($record['formatted']));
    }
}