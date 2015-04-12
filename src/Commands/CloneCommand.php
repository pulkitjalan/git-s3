<?php

namespace Git\S3\Commands;

use Symfony\Component\Console\Input\InputArgument;
use ZipArchive;

class CloneCommand extends AbstractCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'clone';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clone repo from s3';

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array_merge(parent::getArguments(), [
            ['repo', InputArgument::REQUIRED, 'Name of repo'],
        ]);
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    protected function fire()
    {
        $s3 = $this->getAws('s3')->registerStreamWrapper();

        $this->comment('Searching for repo: '.$this->argument('repo'));
        $repo = 's3://'.$this->getConfig()->get('app.bucket').'/'.$this->argument('repo');
        if (!file_exists($repo)) {
            $this->error('Repo '.$this->argument('repo').' not found');
            return;
        }

        $files = scandir($repo.'/archives', SCANDIR_SORT_DESCENDING);
        $latestFile = array_get($files, 0);

        if (is_null($latestFile)) {
            $this->error('Please push the repository using \'--zip\' first.');
            return;
        }

        $dir = getcwd().'/'.$this->argument('repo');

        $this->info('Cloning into: '.$dir);

        mkdir($dir, 0755, true);

        $this->comment('Downloading archive: '.$latestFile);
        copy($repo.'/archives/'.$latestFile, $dir.'/'.$latestFile);

        $zip = new ZipArchive;
        if ($zip->open($dir.'/'.$latestFile) === true) {
            $this->comment('Extracting archive: '.$latestFile);
            $zip->extractTo($dir.'/');
            $zip->close();
            unlink($dir.'/'.$latestFile);
        } else {
            $this->error('Unable to open archive, please push and try again');
            return;
        }

        $this->info('Successfully cloned into: '.$dir);
    }
}
