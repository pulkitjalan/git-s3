<?php

namespace Git\S3;

class Config
{
    /**
     * @var string|null
     */
    protected $env;

    /**
     * @var array
     */
    protected $items;

    /**
     * Create a config instance.
     *
     * @param  string $env
     * @return void
     */
    public function __construct($env = null)
    {
        $this->env = $env;
        $this->init();
        $this->items = $this->getItems();
    }

    /**
     * Set config data and update config files
     *
     * @param  string $key
     * @param  string $value
     * @return void
     */
    public function set($key, $value)
    {
        array_set($this->items, $key, $value);
        $this->updateFiles();
    }

    /**
     * Get config data
     *
     * @param  string $item
     * @param  string $default
     * @return string
     */
    public function get($item, $default = null)
    {
        return array_get($this->items, $item, $default);
    }

    /**
     * Get all config files
     *
     * @return array
     */
    public function getFiles()
    {
        $dir = $this->getDir();

        return array_filter(array_map(function ($file) use ($dir) {
            if (is_file($dir.$file) && pathinfo($file, PATHINFO_EXTENSION) == 'php') {
                return $dir.$file;
            }
        }, scandir($dir)));
    }

    /**
     * Initialize and create dir if not exists
     *
     * @return void
     */
    protected function init()
    {
        $dir = $this->getDir();

        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    /**
     * Update config files
     *
     * @return void
     */
    protected function updateFiles()
    {
        $dir = $this->getDir();

        foreach ($this->items as $fileName => $data) {
            $file = $dir.'/'.$fileName.'.php';

            file_put_contents($file, '<?php'."\n\n".'return '.var_export($data, true).';');

            chmod($file, 0755);
        }
    }

    /**
     * Update items array
     *
     * @return array
     */
    protected function getItems()
    {
        $items = [];
        foreach ($this->getFiles() as $file) {
            $name = pathinfo($file, PATHINFO_FILENAME);
            $items[$name] = require $file;
        }

        return $items;
    }

    /**
     * Get config directory
     *
     * @return string
     */
    protected function getDir()
    {
        $dir = HOME.'/config/';

        if (!is_null($this->env)) {
            $dir = $dir.$this->env.'/';
        }

        return $dir;
    }
}
