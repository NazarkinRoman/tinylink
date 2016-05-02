<?php

/* ===================================
 * Author: Nazarkin Roman
 * -----------------------------------
 * Contacts:
 * email - roman@nazarkin.su
 * icq - 642971062
 * skype - roman444ik
 * -----------------------------------
 * GitHub:
 * https://github.com/NazarkinRoman
 * ===================================
*/

class Autoloader
{

    static protected $_paths = array();
    static protected $_classMap = array();

    /**
     * Register paths to libraries
     *
     * @param string $path
     * @return void
     */
    static public function registerPath($path)
    {
        if (!in_array($path, self::$_paths)) {
            self::$_paths[] = $path;
        }
    }

    /**
     * Load class
     *
     * @param string $class
     * @return boolean
     */
    public static function load($class)
    {
        if (!empty(self::$_classMap) && array_key_exists($class, self::$_classMap[$class])) {
            require self::$_classMap[$class];
            return true;
        }

        $file = implode(DIRECTORY_SEPARATOR, array_map('ucfirst', explode('_', $class))) . '.php';

        foreach (self::$_paths as $path) {
            if (file_exists($path . DIRECTORY_SEPARATOR . $file)) {
                include $path . DIRECTORY_SEPARATOR . $file;
                return true;
            }
        }
        return false;
    }

    /**
     * Connect class map
     *
     * @property string $path
     * @param           $path
     * @return array
     */
    static public function loadMap($path)
    {
        if (!file_exists($path)) {
            self::$_classMap = array();
            return;
        }
        self::$_classMap = include($path);
    }

    /**
     * Initialize autoload
     *
     * @return void
     */
    static public function init()
    {
        spl_autoload_register(array(__CLASS__, 'load'));
        self::registerPath(APPLICATION_PATH . '/system/');
        self::registerPath(APPLICATION_PATH . '/system/_assets');
    }
}
