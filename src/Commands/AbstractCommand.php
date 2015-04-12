<?php

namespace Git\S3\Commands;

use Symfony\Component\Console\Input\InputOption;
use Illuminate\Console\Command;
use GitWrapper\GitWrapper;
use Aws\Common\Aws;
use Git\S3\Config;

abstract class AbstractCommand extends Command
{
    /**
     * @var \Git\S3\Config
     */
    protected $config;

    /**
     * @var \Aws\Common\Aws
     */
    protected $aws;

    /**
     * @var \GitWrapper\GitWrapper
     */
    protected $git;

    /**
     * @var \GitWrapper\GitWorkingCopy
     */
    protected $repository;

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

    /**
     * Get configs for this env
     *
     * @param mixed $var
     * @param mixed $default
     * @return mixed
     */
    protected function getConfig()
    {
        if (is_null($this->config)) {
            $this->config = new Config($this->getEnvironment());
        }

        return $this->config;
    }

    /**
     * Get aws credentials
     *
     * @return array
     */
    protected function getAwsCredentials()
    {
        $opts = [
            'region' => $this->getConfig()->get('aws.region', 'us-east-1'),
        ];

        if ($this->getConfig()->get('aws.key')) {
            $opts['key'] = $this->getConfig()->get('aws.key');
        }

        if ($this->getConfig()->get('aws.secret')) {
            $opts['secret'] = $this->getConfig()->get('aws.secret');
        }

        return $opts;
    }

    /**
     * Get aws object or aws service
     *
     * @return mixed
     */
    protected function getAws($service = null)
    {
        if (is_null($this->aws)) {
            $this->aws = Aws::factory($this->getAwsCredentials());
        }

        if (!is_null($service)) {
            return $this->aws->get($service);
        }

        return $this->aws;
    }

    /**
     * Get git wrapper object
     *
     * @return \GitWrapper\GitWrapper
     */
    protected function getGit()
    {
        if (is_null($this->git)) {
            $this->git = new GitWrapper();
        }

        return $this->git;
    }

    /**
     * Get git repository object
     *
     * @return \GitWrapper\GitWorkingCopy
     */
    protected function getRepository()
    {
        if (is_null($this->repository)) {
            $this->repository = $this->getGit()->workingCopy(getcwd());
        }

        return $this->repository;
    }
}
