<?php

namespace Git\S3\Commands;

use Symfony\Component\Process\Process;

class EditCommand extends AbstractCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'edit';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Edit config files';

    /**
     * Execute the console command.
     *
     * @return void
     */
    protected function fire()
    {
        if (!$files = $this->getConfig()->getFiles()) {
            $this->error('No config files found'.($this->getEnvironment() ? ' for '.$this->getEnvironment() : ''));
            $this->comment('Please run the \'init\' command first');
            return;
        }

        $command = $this->executable().' '.implode(' ', $this->getConfig()->getFiles());

        $process = new Process($command);
        $process->run();
    }

    /**
     * Find the correct executable to run depending on the OS.
     *
     * @return string
     */
    protected function executable()
    {
        if (strpos(strtoupper(PHP_OS), 'WIN') === 0) {
            return 'start';
        } elseif (strpos(strtoupper(PHP_OS), 'DARWIN') === 0) {
            return 'open';
        }

        return 'xdg-open';
    }
}
