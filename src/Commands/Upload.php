<?php

namespace Git\S3\Commands;

use Symfony\Component\Console\Input\InputOption;
use Guzzle\Batch\BatchBuilder;
use GitWrapper\GitWrapper;

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

    protected $git;
    protected $repository;

    public function __construct()
    {
        parent::__construct();

        $this->git = new GitWrapper();
    }

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
    protected function process()
    {
        $this->repository = $this->git->workingCopy(getcwd());

        if (!$this->repository->isCloned()) {
            $this->error('Current directory is not a valid git repository!');
            return;
        }

        if ($this->option('zip')) {
            $this->uploadArchive();
        } else {
            $this->uploadFiles();
        }
    }

    protected function getRepoName()
    {
        $dir = $this->repository->getDirectory();

        return basename($dir).'.git';
    }

    protected function getCurrenBranch()
    {
        return trim($this->repository->getBranches()->head());
    }

    protected function getAllFiles()
    {
        $files = $this->repository->run(['ls-files', '--full-name'])->getOutput();

        return array_map('trim', explode("\n", $files));
    }

    protected function createArchive()
    {
        $branch = $this->getCurrenBranch();

        $dir = HOME.'/repos/'.$this->getRepoName().'/';

        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        $output = $dir.$branch.'-'.time().'.zip';
        $this->repository->archive(['--format=zip', $branch, '--output='.$output]);

        return $output;
    }

    protected function uploadArchive()
    {
        $archive = $this->createArchive();
        $s3 = $this->getAws('s3');

        $s3->putObject([
            'Bucket' => $this->config->get('app.bucket'),
            'Key'    => $this->getRepoName().'/archives/'.basename($archive),
            'Body'   => fopen($archive, 'r+')
        ]);

        unlink($archive);
    }

    protected function uploadFiles()
    {
        $files = $this->getAllFiles();
        $s3 = $this->getAws('s3');

        $batch = BatchBuilder::factory()
            ->transferCommands(20)
            ->autoFlushAt(40)
            ->build();

        foreach ($files as $file) {
            $uploadFile = $this->repository->getDirectory().'/'.$file;
            
            if (is_file($uploadFile)) {
                $batch->add($s3->getCommand('PutObject', [
                    'Bucket' => $this->config->get('app.bucket'),
                    'Key'    => $this->getRepoName().'/files/'.$file,
                    'Body'   => fopen($uploadFile, 'r+')
                ]));
            }
        }

        $batch->flush();
    }
}
