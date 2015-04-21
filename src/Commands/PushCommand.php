<?php

namespace Git\S3\Commands;

use Symfony\Component\Console\Input\InputOption;

class PushCommand extends AbstractCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'push';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Push current git repo to s3';

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
                $result = $this->pushArchive();
            } else {
                $result = $this->pushFiles();
            }

            $this->info('Successfully uploaded to '.'s3://'.$this->getConfig()->get('app.bucket').'/'.$result);
        } catch (\Exception $e) {
            $this->error('Upload failed!');
            throw $e;
        }
    }

    /**
     * Create an archive of the current repo
     *
     * @return string
     */
    protected function createArchive()
    {
        $branch = $this->getCurrenBranch();

        $dir = HOME.'/repos/'.$this->getRepoName().'/'.$branch.'/archives/';

        if (!$this->getFilesystem()->exists($dir)) {
            $this->getFilesystem()->makeDirectory($dir);
        }

        $output = $dir.time().'.zip';
        $this->repository->archive(['--format=zip', $branch, '--output='.$output]);

        return $output;
    }

    /**
     * Create and push archive to s3
     *
     * @return string
     */
    protected function pushArchive()
    {
        $branch = $this->getCurrenBranch();

        $source = $this->createArchive();

        // upload to s3
        $this->sync($source, $this->getConfig()->get('app.bucket'), $this->getRepoName().'/'.$branch.'/archives/'.basename($source));

        // remove local tmp copy
        $this->getFilesystem()->delete($source);

        return $this->getRepoName().'/archives/'.basename($source);
    }

    /**
     * Push all repo files to s3
     *
     * @return string
     */
    protected function pushFiles()
    {
        $branch = $this->getCurrenBranch();

        $dir = HOME.'/repos/'.$this->getRepoName().'/'.$branch.'/files/';

        if (!$this->getFilesystem()->exists($dir)) {
            $this->getFilesystem()->makeDirectory($dir);
        }

        foreach ($this->getAllFiles() as $file) {
            if (empty($file)) {
                continue;
            }

            $sourceDir = pathinfo($file, PATHINFO_DIRNAME);
            if (!$this->getFilesystem()->exists($dir.$sourceDir)) {
                $this->getFilesystem()->makeDirectory($dir.$sourceDir);
            }

            $source = $this->repository->getDirectory().'/'.$file;
            $this->getFilesystem()->copy($source, $dir.$file);
        }

        $this->sync($dir, $this->getConfig()->get('app.bucket'), $this->getRepoName().'/'.$branch.'/files');
        $this->getFilesystem()->deleteDirectory($dir);

        return $this->getRepoName().'/'.$branch.'/files';
    }

    /**
     * Sync given source files or dir to destination on s3
     *
     * @param string $source
     * @param string $bucket
     * @param string $keyPrefix
     * @return void
     */
    protected function sync($source, $bucket, $keyPrefix)
    {
        $s3 = $this->getAws('s3');

        if ($this->getFilesystem()->isFile($source)) {
            $s3->registerStreamWrapper();
            copy($source, 's3://'.$bucket.'/'.$keyPrefix);
        } else {
            $s3->uploadDirectory($source, $bucket, $keyPrefix, [
                'concurrency' => 20,
            ]);
        }
    }
}
