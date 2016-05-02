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

class Dispatcher
{

    protected static $controller, $action;

    static public function dispatch()
    {
        $controller = self::getController();
        $action     = self::getAction();

        FrontController::getInstance();
    }

    /**
     * Return requested controller
     *
     * @return string
     */
    static public function getController()
    {
        if (self::$controller !== null) {
            return self::$controller;
        }

        self::$controller = (isset($_REQUEST['controller']) && trim($_REQUEST['controller']))
            ? self::clearStr($_REQUEST['controller']) : 'index';

        return self::$controller;
    }

    /**
     * Return requested action
     *
     * @return string
     */
    static public function getAction()
    {
        if (self::$action !== null) {
            return self::$action;
        }

        self::$action = (isset($_REQUEST['action']) && trim($_REQUEST['action']))
            ? self::clearStr($_REQUEST['action']) : 'index';

        return self::$action;
    }

    /**
     * Clear specified string from unsafe characters
     *
     * @param string $str
     * @return array|mixed
     */
    static private function clearStr($str)
    {
        return trim(preg_replace('/[^a-zA-Z]+/', '', $str));
    }

    /**
     * Is ajax request?
     *
     * @return boolean
     */
    static public function isAjaxRequest()
    {
        return (!isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
    }

}