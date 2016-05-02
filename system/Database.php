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

class Database
{

    protected static $_instance;

    private $db, $db_file, $db_dir;

    private function __construct()
    {
        $this->db_dir = APPLICATION_PATH . '/system/_data/_database/';
    }

    private function __clone() { }

    private function __wakeup() { }

    /**
     * Return initialized class instance
     *
     * @return Database
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Return all static pages on site
     *
     * @return array
     */
    public function getAllPages()
    {
        $pages = array();
        $db    = $this->readFile('pages.db');

        // check
        if (!is_array($db)) {
            $db = array();
        }

        // filter array
        foreach ($db as $alias => $entry) {
            if (!is_array($entry)) {
                continue;
            }

            $pages[$alias] = $entry;
        }

        // sort array
        usort(
            $pages,
            array($this, 'sortHelper')
        );

        return $pages;
    }

    /**
     * Return page data array
     *
     * @param $alias
     * @return array|boolean
     */
    public function getPage($alias)
    {
        $filename = 'pages.db';
        $data     = $this->readFile($filename);

        if (isset($data[$alias]) && is_array($data[$alias])) {
            return $data[$alias];
        }

        return false;
    }

    /**
     * Change page params in DB
     *
     * @param $alias
     * @param $newData
     *
     * @return boolean
     */
    public function changePage($alias, $newData)
    {
        $filename = 'pages.db';
        $data     = $this->readFile($filename);

        $data[$alias] = (isset($data[$alias]) && is_array($data[$alias]))
            ? array_merge($data[$alias], $newData)
            : $newData;

        return $this->writeFile($filename, json_encode($data));
    }

    /**
     * Delete page
     *
     * @param $alias
     * @return bool
     */
    public function deletePage($alias)
    {
        $filename = 'pages.db';
        $data     = $this->readFile($filename);

        unset($data[$alias]);

        return $this->writeFile($filename, json_encode($data));
    }

    /**
     * Load database file
     *
     * @param string $alias
     * @param bool   $return
     *
     * @return Database
     */
    private function loadFileByAlias($alias, $return = true)
    {
        $alias    = md5($alias . Config::get('hash_salt'));
        $filename = substr($alias, 0, 2) . '.db';

        $db = $this->readFile($filename);

        if ($return) {
            return $db;
        }

        $this->db_file = $this->db_dir . $filename;
        $this->db      = $db;

        return $this;
    }

    public function checkBlacklist($url)
    {
        $url     = strtolower(trim(parse_url($url, PHP_URL_HOST)));
        $bl_file = @file_get_contents(APPLICATION_PATH . '/system/_data/blacklist.db');
        $bl_file = @json_decode($bl_file);

        if ($bl_file !== false && $bl_file !== null) {
            foreach ($bl_file as $item) {
                if (trim(strtolower($item)) == $url) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if alias exists in database
     *
     * @param string $alias
     * @return bool
     */
    public function checkAlias($alias)
    {
        $this->loadFileByAlias($alias, false);

        return (isset($this->db[$alias]) && is_array($this->db[$alias])) ? $this->db[$alias] : false;
    }

    /**
     * Edit saved item
     *
     * @param string $alias
     * @param array  $changes
     * @return bool
     */
    public function changeEntry($alias, $changes)
    {
        if (!is_array($this->db)) {
            $this->loadFileByAlias($alias, false);
        }

        if (!isset($this->db[$alias])) {
            return false;
        }

        $this->db[$alias] = array_merge($this->db[$alias], $changes);

        $this->writeFile(false, json_encode($this->db));

        return true;
    }

    /**
     * Add link to DB
     *
     * @param array $params
     */
    public function addLink($params)
    {
        $this->loadFileByAlias($params['alias'], false);

        $linkData = array(
            'visits'      => 0,
            'create_date' => time(),
            'author'      => FrontController::getInstance()->getUserIdentifier()
        );

        $this->db[$params['alias']] = array_merge($params, $linkData);

        $this->writeFile(false, json_encode($this->db));
        Cache::remove('user-links-' . FrontController::getInstance()->getUserIdentifier());

        return $this->db[$params['alias']];
    }

    /**
     * Load all entries linked to specified identifiers
     *
     * @param string $identifier
     * @return array
     */
    public function getLinksByUser($identifier)
    {
        $identifier = trim($identifier);
        $userLinks  = array();
        $cacheKey   = ($identifier) ? 'user-links-' . $identifier : 'all_links';

        if (($db = Cache::get($cacheKey)) === false) {
            // load all files firstly
            $files = glob($this->db_dir . '*.db');
            $db    = array();

            if (!is_array($files)) {
                $files = array();
            }

            foreach ($files as $filename) {
                $db = array_merge($db, $this->readFile($filename));
            }

            if (!empty($db)) {
                Cache::save($db, 3600);
            }
        }

        // double check cached value
        if (!array($db)) {
            Cache::remove($cacheKey);
            $db = array();
        }

        // filter array
        foreach ($db as $alias => $entry) {
            if (!is_array($entry) || !isset($entry['author']) || ($entry['author'] != $identifier && $cacheKey !== 'all_links')) {
                continue;
            } elseif ($this->linkExpired($entry['lifetime'], $entry['create_date'])) {
                continue;
            }

            $userLinks[$alias] = $entry;
        }

        // sort array
        usort(
            $userLinks,
            array($this, 'sortHelper')
        );

        return $userLinks;
    }

    /**
     * Delete link from DB
     *
     * @param string $alias
     *
     * @return bool
     */
    public function deleteLink($alias)
    {
        $this->loadFileByAlias($alias, false);
        unset($this->db[$alias]);

        Cache::remove('user-links-' . FrontController::getInstance()->getUserIdentifier());

        return $this->writeFile(false, json_encode($this->db));
    }

    /**
     * Read file contents
     *
     * @param string|bool $filename
     * @return string|array
     */
    private function readFile($filename = false)
    {
        @clearstatcache();
        $filename = ($filename) ? $this->db_dir . basename($filename) : $this->db_file;
        if (!file_exists($filename) || !is_readable($filename) || filesize($filename) == 0) {
            return array();
        }

        $fp       = fopen($filename, 'r');
        $contents = fread($fp, filesize($filename));
        fclose($fp);

        if (!is_string($contents) || !$contents = @json_decode($contents, true)) {
            return array();
        }

        return $contents;
    }

    /**
     * Write contents to file
     *
     * @param string|bool $filename
     * @param string      $contents
     * @return bool
     */
    private function writeFile($filename = false, $contents)
    {
        $filename = ($filename) ? $this->db_dir . basename($filename) : $this->db_file;
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
     * Generate secured hash of provided string
     *
     * @param string $string
     * @param string $hashFunction
     * @param int    $rounds
     *
     * @return string
     */
    public static function hashString($string, $hashFunction = 'sha1', $rounds = 10)
    {
        $string .= Config::get('hash_salt') . md5(__FILE__) . $_SERVER['DOCUMENT_ROOT'];
        $i = $rounds;

        while ($i--) {
            $string = $hashFunction($string . md5(substr($string, 0, $i)));
        }

        return $string;
    }

    /**
     * Generate unique URL alias
     *
     * @param int $length
     *
     * @return string
     */
    public function genAlias($length = 3)
    {
        $chars = 'qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP';
        $size  = strlen($chars) - 1;
        $alias = null;

        while ($length--) {
            $alias .= $chars[rand(0, $size)];
        }

        if ($this->checkAlias($alias) !== false) {
            return $this->genAlias($length + 1);
        }

        return $alias;
    }

    /**
     * Check link is expired or not
     *
     * @param int $lifetime
     * @param int $create_date
     * @return bool
     */
    public function linkExpired($lifetime, $create_date)
    {
        switch ($lifetime) {
            case 0: // never
                return false;
                break;

            case 1: // 10 minutes
                $lifetime = 600;
                break;

            case 2: // 1 hour
                $lifetime = 3600;
                break;

            case 3: // 3 hours
                $lifetime = 10800;
                break;

            case 4: // 1 day
                $lifetime = 86400;
                break;

            case 5: // 1 week
                $lifetime = 604800;
                break;

            case 6: // 1 month
                $lifetime = 2629743;
                break;

            default:
                return false;
        }

        return ($create_date + $lifetime <= time());
    }

    /**
     * Sorts array
     */
    private function sortHelper($a, $b)
    {
        if (!isset($a['create_date']) || !isset($b['create_date'])) {
            return 0;
        }

        if ($a['create_date'] == $b['create_date']) {
            return 0;
        }

        return ($a['create_date'] < $b['create_date']) ? 1 : -1;
    }

}