<?php

/**
 * Class LCAutoloader
 * @package LC\Autoload
 * @author Ralph Dittrich <dittrich.ralph@lupuscoding.de>
 * @license <http://creativecommons.org/licenses/by-sa/4.0/> Attribution-ShareAlike 4.0 International
 */
class LCAutoloader
{
    /** @var array */
    protected $prefixes = array();

    /**
     * @return void
     */
    public function register()
    {
        spl_autoload_register(array($this, 'loadClass'));
    }

    /**
     * @param string $prefix
     * @param string $base_dir
     * @param bool $prepend
     * @return void
     */
    public function addNamespace(string $prefix, string $base_dir, $prepend = false)
    {
        $prefix = trim($prefix, '\\') . '\\';
        $base_dir = rtrim($base_dir, DIRECTORY_SEPARATOR) . '/';
        if (isset($this->prefixes[$prefix]) === false) {
            $this->prefixes[$prefix] = array();
        }
        if ($prepend === true) {
            array_unshift($this->prefixes[$prefix], $base_dir);
        } else {
            array_push($this->prefixes[$prefix], $base_dir);
        }
    }

    /**
     * @param string $file
     * @return bool
     */
    public function reqFile(string $file): bool
    {
        if (file_exists($file)) {
            require_once($file);
            return true;
        }
        return false;
    }

    /**
     * @param string $prefix
     * @param string $class
     * @return bool|string
     */
    public function loadMappedFile(string $prefix, string $class)
    {
        if (isset($this->prefixes[$prefix]) === false) {
            return false;
        }
        foreach ($this->prefixes[$prefix] as $base_dir) {
            $file = $base_dir . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
            if ($this->reqFile($file)) {
                return $file;
            }
        }
        return false;
    }

    /**
     * @param string $class
     * @return bool|string
     */
    public function loadClass(string $class)
    {
        $prefix = $class;
        while (false !== ($pos = strrpos($prefix, '\\'))) {
            $prefix = substr($class, 0, $pos + 1);
            $relative = substr($class, $pos + 1);
            $mapped = $this->loadMappedFile($prefix, $relative);
            if ($mapped) {
                return $mapped;
            }
            $prefix = rtrim($prefix, '\\');
        }
        return false;
    }

}