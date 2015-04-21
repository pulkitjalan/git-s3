<?php

namespace Git\S3\Commands;

class PullCommand extends AbstractCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'pull';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pull current git repo from s3';

    /**
     * Execute the console command.
     *
     * @return void
     */
    protected function fire()
    {
        $this->repository = $this->getRepository();
    }

    /**
     * Pull all repo files from s3
     *
     * @return void
     */
    protected function pullFiles()
    {
        $branch = $this->getCurrenBranch();
    }
}
