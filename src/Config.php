<?php

namespace Git\S3;

class Config
{
    protected $env;
    protected $items;

    public function __construct($env = null)
    {
        $this->env = $env;
        $this->init();
        $this->items = $this->getItems();
    }

    public function set($key, $value)
    {
        array_set($this->items, $key, $value);
        $this->updateFiles();
    }

    public function get($item, $default = null)
    {
        return array_get($this->items, $item, $default);
    }

    protected function init()
    {
        $dir = $this->getDir();

        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    protected function updateFiles()
    {
        $dir = $this->getDir();

        foreach ($this->items as $fileName => $data) {
            $file = $dir.'/'.$fileName.'.php';

            file_put_contents($file, '<?php'."\n\n".'return '.var_export($data, true).';');

            chmod($file, 0755);
        }
    }

    protected function getItems()
    {
        $items = [];
        foreach ($this->getFiles() as $file) {
            $name = pathinfo($file, PATHINFO_FILENAME);
            $items[$name] = require $file;
        }

        return $items;
    }

    protected function getFiles()
    {
        $dir = $this->getDir();

        return array_filter(array_map(function ($file) use ($dir) {
            if (is_file($dir.$file) && pathinfo($file, PATHINFO_EXTENSION) == 'php') {
                return $dir.$file;
            }
        }, scandir($dir)));
    }

    protected function getDir()
    {
        $dir = HOME.'/config/';

        if (!is_null($this->env)) {
            $dir = $dir.$this->env.'/';
        }

        return $dir;
    }
}
