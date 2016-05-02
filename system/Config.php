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

class Config
{

    private static $config = null,
        $configFile = null;

    /**
     * Return requested config value
     *
     * @param string $var
     * @param string $return
     * @return mixed
     * @throws ConfigException
     */
    static public function get($var = '', $return = 'exception')
    {
        self::loadConfig();

        if ($var == '' || $var === null) {
            return self::$config;
        } // if provided $var is empty - return all array
        $varName = $var;
        $value   = self::execExpr(self::$config, $var);

        if ($value === 'TINYLINK_ERROR') {
            if ($return === 'exception') {
                throw new ConfigException("Variable `{$varName}` is not exists in `config.php`", 500);
            } else {
                return $return;
            }
        }

        return $value;
    }

    /**
     * Set config value
     *
     * @param string $var
     * @param string $value
     */
    static public function set($var, $value)
    {
        self::loadConfig();
        self::$config = self::execExpr(self::$config, $var, $value);
    }

    /**
     * Process expressions like `array->another_array->var`
     *
     * @param        $array
     * @param        $expr
     * @param string $setValue
     *
     * @throws ConfigException
     *
     * @return bool
     */
    private static function execExpr($array, &$expr, $setValue = 'novalue')
    {
        if (is_array($expr) && empty($expr)) {
            return 'TINYLINK_ERROR';
        }
        if (!is_array($expr) && substr_count($expr, '->') == 0) {
            if ($setValue == 'novalue') {
                return @$array[$expr];
            }

            $array[$expr] = $setValue;
            return $array;
        }

        $expr = (is_array($expr)) ? $expr : explode('->', $expr);

        foreach ($expr as $index => $part) {
            unset($expr[$index]);

            if (isset($array[$part])) {
                if (is_array($array[$part])) {
                    $array[$part] = self::execExpr($array[$part], $expr, $setValue);
                }

                if ($setValue !== 'novalue') {
                    if (!is_array($array[$part])) {
                        $array[$part] = $setValue;
                        break;
                    }
                } else {
                    $array = $array[$part];
                    break;
                }
            } else {
                if ($setValue !== 'novalue') {
                    throw new ConfigException("Variable `{$expr}` is not exists in `config.php`", 500);
                }

                $array = 'TINYLINK_ERROR';
                break;
            }

        }

        return $array;
    }

    /**
     * Save config array to file
     *
     * @throws FileException
     * @throws ConfigException
     *
     * @return bool
     */
    static public function saveFile()
    {
        if (self::$config === null) {
            throw new ConfigException('Configuration file is not loaded!', 500);
        }

        if (!is_writable(self::$configFile)) {
            throw new FileException('File is not writable', self::$configFile);
        }

        $file_contents = var_export(self::$config, true);
        file_put_contents(self::$configFile, '<?php return ' . $file_contents . ';');
        return true;
    }

    /**
     * Load config.php file
     *
     * @throws FileException
     */
    static private function loadConfig()
    {
        if (self::$config !== null) {
            return;
        } elseif (self::$configFile === null) {
            self::$configFile = APPLICATION_PATH . '/system/_data/config.php';
        } elseif (!is_readable(self::$configFile)) {
            throw new FileException('File is not readable', self::$configFile);
        }

        self::$config = include(self::$configFile);
    }

}

class ConfigException extends SystemException
{

}