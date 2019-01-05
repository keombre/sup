<?php

use \Symfony\Component\Console\Output\StreamOutput;
use \Symfony\Component\Console\Input\ArrayInput;
use \Composer\Console\Application;

class composerWrapper {

    private $application;

    private $outputStream;

    private $command;
    private $status = -1;

    private $input;
    private $output;

    function __construct() {
        $this->outputStream = fopen('php://temp', 'a');

        putenv('COMPOSER_HOME=' . __DIR__ . '/../vendor/bin/composer');

        $this->application = new Application();
        $this->application->setAutoExit(false);
        fwrite($this->outputStream, $this->application->getLongVersion() . PHP_EOL);
    }

    function runCommand(string $command) {
        $this->command = $command;
        $this->createIO();
        $this->run();
    }

    function getOutput() {
        return $this->outputStream;
    }

    function getStatus() {
        return $this->status;
    }

    private function run() {
        $this->status = $this->application->doRun($this->input, $this->output);
        rewind($this->outputStream);
    }

    private function createIO() {
        if (!is_string($this->command))
            throw new Exception('No command specified');
        
        $this->input = new ArrayInput([
            'command' => $this->command,
            '--working-dir' => __DIR__ . '/../'
        ]);
        
        $this->output = new StreamOutput(
            $this->outputStream,
            StreamOutput::VERBOSITY_VERY_VERBOSE
        );
    }
}
