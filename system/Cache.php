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

class Cache
{

    static private $memcache,
        $key;

    /**
     * Retrieve data from cache by key
     *
     * @param string $key
     * @return bool|mixed
     */
    static public function get($key)
    {
        if (Config::get('cache->enabled') === false || Config::get('cache->enabled') == 'no') {
            return false;
        }

        $key       = self::hashKey($key);
        self::$key = $key;

        switch (Config::get('cache->method')) {
            case 'memcache':
                return self::getFromMemcached($key);
                break;

            case 'files':
            case  'file':
                return self::getFromFile($key);
                break;

            default:
                return false;
                break;
        }
    }

    /**
     * Store data to cache
     *
     * @param mixed $contents
     * @param int   $lifetime
     * @param bool  $key
     * @return bool
     */
    static public function save($contents, $lifetime = 3600, $key = false)
    {
        if (Config::get('cache->enabled') === false || Config::get('cache->enabled') == 'no') {
            return false;
        }
        if ($key === false && self::$key === null) {
            return false;
        }

        $key      = ($key) ? self::hashKey($key) : self::$key;
        $lifetime = (int)$lifetime;

        switch (Config::get('cache->method')) {
            case 'memcache':
                return self::writeToMemcache($key, $contents, $lifetime);
                break;

            case 'files':
            case  'file':
                return self::writeToFile($key, $contents, $lifetime);
                break;

            default:
                return false;
                break;
        }
    }

    /**
     * Remove entry from cache
     *
     * @param string $key
     * @return bool
     */
    static public function remove($key)
    {
        if (Config::get('cache->enabled') === false || Config::get('cache->enabled') == 'no') {
            return false;
        }

        $key = self::hashKey($key);

        switch (Config::get('cache->method')) {
            case 'memcache':
                return self::removeFromMemcache($key);
                break;

            case 'files':
            case  'file':
                return self::removeCacheFile($key);
                break;

            default:
                return false;
                break;
        }
    }

    /**
     * Read cache
     *
     * @param string $key
     * @return mixed
     */
    static private function getFromFile($key)
    {
        @clearstatcache();
        $filename = APPLICATION_PATH . '/system/_data/_cache/' . basename($key) . '.cache';
        if (!file_exists($filename) || !is_readable($filename)) {
            return false;
        }

        $fp       = fopen($filename, 'r');
        $contents = fread($fp, filesize($filename));
        fclose($fp);

        if (!is_string($contents) || !$contents = @unserialize($contents)) {
            return false;
        }

        if ($contents['lifetime'] != '-1' && filemtime($filename) + $contents['lifetime'] <= time()) {
            @unlink($filename);
            return false;
        }

        return $contents['data'];
    }

    static private function hashKey($key)
    {
        $salt   = Config::get('hash_salt');
        $domain = @parse_url(Config::get('site_url'), PHP_URL_HOST);
        $domain = ($domain) ? str_replace('.', '_', $domain) : 'tinylink';

        return $domain . '_' . md5(sha1($key . $salt) . md5($salt));
    }

    /**
     * Write contents to file
     *
     * @param string $key
     * @param mixed  $contents
     * @param int    $lifetime
     * @return bool
     */
    static private function writeToFile($key, $contents, $lifetime = 3600)
    {
        $filename = APPLICATION_PATH . '/system/_data/_cache/' . basename($key) . '.cache';
        $contents = serialize(array('lifetime' => $lifetime, 'data' => $contents));
        $fp       = fopen($filename, 'w');

        for ($i = 0; $i < 10; $i++) {

            if (flock($fp, LOCK_EX)) {
                ftruncate($fp, 0);
                fwrite($fp, $contents);
                fflush($fp);

                flock($fp, LOCK_UN);
                fclose($fp);
                return true;
            } else {
                if ($i = 9) {
                    fclose($fp);
                    return false;
                }
                sleep(0.1);
                continue;
            }
        }
    }

    /**
     * Remove cache file
     *
     * @param string $key
     * @return bool
     */
    static private function removeCacheFile($key)
    {
        $filename = APPLICATION_PATH . '/system/_data/_cache/' . basename($key) . '.cache';

        if (file_exists($filename)) {
            return @unlink($filename);
        }

        return true;
    }

    /**
     * Retrieve data from memcached server
     *
     * @param string $key
     * @return bool|mixed
     */
    static private function getFromMemcached($key)
    {
        if (!self::connectToMemcache()) {
            return false;
        }

        return self::$memcache->get($key);
    }

    /**
     * Store data at memcached server
     *
     * @param string $key
     * @param mixed  $contents
     * @param int    $lifetime
     * @return bool
     */
    static private function writeToMemcache($key, $contents, $lifetime = 3600)
    {
        if (!self::connectToMemcache()) {
            return false;
        }

        return self::$memcache->set($key, $contents, 0, $lifetime);
    }

    /**
     * Remove entry from memcache
     *
     * @param string $key
     * @return bool
     */
    static private function removeFromMemcache($key)
    {
        if (!self::connectToMemcache()) {
            return false;
        }

        self::$memcache->delete($key);
        self::$memcache->replace($key, '', 0, -1);
        // because `delete` function does not work on some versions of memcache
    }

    /**
     * Initiate connect to memcached server
     *
     * @return bool
     */
    static private function connectToMemcache()
    {
        if (!class_exists('Memcache')) {
            return false;
        }

        self::$memcache = new Memcache;
        $connectState   = self::$memcache->connect(
            Config::get('cache->memcache->server'),
            Config::get('cache->memcache->port')
        );

        if ($connectState === false) {
            self::$memcache = null;
            return false;
        }

        return true;
    }

}