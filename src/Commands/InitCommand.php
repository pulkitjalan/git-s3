<?php

namespace Git\S3\Commands;

class InitCommand extends AbstractCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'init';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initilize Git S3';

    /**
     * Execute the console command.
     *
     * @return void
     */
    protected function fire()
    {
        $this->configureAws();

        $this->info('Successfully initilized'.($this->getEnvironment() ? ' for '.$this->getEnvironment() : '').'!');
    }

    /**
     * Configure AWS configs
     *
     * @return void
     */
    protected function configureAws()
    {
        $this->askAndSet('Enter your AWS Access Key', 'aws.key');
        $this->askAndSet('Enter your AWS Secret Key', 'aws.secret');
        $this->askAndSet('Enter your AWS Region', 'aws.region', 'us-east-1');
        $this->askAndSet('Enter your Bucket name', 'app.bucket');
    }

    /**
     * Ask user and set in config
     *
     * @param  string      $question
     * @param  string      $key
     * @param  string|null $default
     * @return void
     */
    protected function askAndSet($question, $key, $default = null)
    {
        // check for existing value in key
        $existing = $this->getConfig()->get($key);
        $existing = (is_null($existing) ? ((!is_null($default)) ? $default : null) : $existing);

        // ask user the question
        $value = $this->ask($question.($existing ? ' (Default: '.$existing.')' : '').': ', $existing);

        // set config and write file
        $this->getConfig()->set($key, $value);
    }
}
