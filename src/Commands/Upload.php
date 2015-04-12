<?php

namespace Git\S3\Commands;

use Symfony\Component\Console\Input\InputOption;
use Guzzle\Batch\BatchBuilder;

class Upload extends AbstractCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'upload';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upload current git repo to s3';

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            ['zip', null, InputOption::VALUE_NONE, 'Upload a zip archive instead of files'],
        ]);
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    protected function fire()
    {
        $this->repository = $this->getRepository();

        if (!$this->repository->isCloned()) {
            $this->error('Current directory is not a valid git repository!');

            return;
        }

        try {
            if ($this->option('zip')) {
                $result = $this->uploadArchive();
            } else {
                $result = $this->uploadFiles();
            }

            $this->info('Successfully uploaded to '.'s3://'.$this->getConfig()->get('app.bucket').'/'.$result);
        } catch (\Exception $e) {
            $this->error('Upload failed!');
            throw $e;
        }
    }

    /**
     * Get name of current repo
     *
     * @return string
     */
    protected function getRepoName()
    {
        $dir = $this->repository->getDirectory();

        return basename($dir);
    }

    /**
     * Get current branch name
     *
     * @return string
     */
    protected function getCurrenBranch()
    {
        return trim($this->repository->getBranches()->head());
    }

    /**
     * Get an array of all files in the current repo
     *
     * @return array
     */
    protected function getAllFiles()
    {
        $files = $this->repository->run(['ls-files', '--full-name'])->getOutput();

        return array_map('trim', explode("\n", $files));
    }

    /**
     * Create an archive of the current repo
     *
     * @return string
     */
    protected function createArchive()
    {
        $this->comment('Creating archive');

        $branch = $this->getCurrenBranch();

        $dir = HOME.'/repos/'.$this->getRepoName().'/';

        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        $output = $dir.$branch.'-'.time().'.zip';
        $this->repository->archive(['--format=zip', $branch, '--output='.$output]);

        return $output;
    }

    /**
     * Create and upload archive to s3
     *
     * @return string
     */
    protected function uploadArchive()
    {
        $s3 = $this->getAws('s3')->registerStreamWrapper();

        $source = $this->createArchive();
        $destination = 's3://'.$this->getConfig()->get('app.bucket').'/'.$this->getRepoName().'/archives/'.basename($source);

        $this->comment('Uploading archive');
        copy($source, $destination);
        unlink($source);

        return $this->getRepoName().'/archives/'.basename($source);
    }

    /**
     * Upload all repo files to s3
     *
     * @return string
     */
    protected function uploadFiles()
    {
        $s3 = $this->getAws('s3')->registerStreamWrapper();

        foreach ($this->getAllFiles() as $file) {
            $source = $this->repository->getDirectory().'/'.$file;

            if (is_file($source)) {
                $this->comment('Uploading file '.$file);

                $destination = 's3://'.$this->getConfig()->get('app.bucket').'/'.$this->getRepoName().'/files/'.$file;

                copy($source, $destination);
            }
        }

        return $this->getRepoName().'/files';
    }
}
