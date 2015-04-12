<?php

namespace Git\S3\Commands;

use Symfony\Component\Console\Input\InputOption;
use Illuminate\Console\Command;
use Aws\Common\Aws;
use Git\S3\Config;

abstract class AbstractCommand extends Command
{
    protected $config;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['env', null, InputOption::VALUE_REQUIRED, 'Specific environment'],
        ];
    }

    /**
     * Get current environment
     *
     * @return string|null
     */
    protected function getEnvironment()
    {
        return $this->option('env') ?: null;
    }

    protected function getAwsCredentials()
    {
        $opts = [
            'region' => $this->config->get('aws.region'),
        ];
        
        if ($this->config->get('aws.key')) {
            $opts['key'] = $this->config->get('aws.key');
        }

        if ($this->config->get('aws.secret')) {
            $opts['secret'] = $this->config->get('aws.secret');
        }

        return $opts;
    }

    protected function getAws($service = null)
    {
        $aws = Aws::factory($this->getAwsCredentials());

        if (!is_null($service)) {
            return $aws->get($service);
        }

        return $aws;
    }

    /**
     * Set configs for this env
     *
     * @return void
     */
    protected function setConfig()
    {
        $this->config = new Config($this->getEnvironment());
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    protected function fire()
    {
        $this->setConfig();
        $this->process();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    abstract protected function process();
}
