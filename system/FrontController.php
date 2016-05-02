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

class FrontController
{

    protected static $_instance,
        $controller, $action;

    public $view;

    private function __construct() { }

    private function __clone() { }

    private function __wakeup() { }

    /**
     * Route request to controller
     *
     * @throws DispatchException
     * @return FrontController
     */
    public function dispatch()
    {
        include(APPLICATION_PATH . '/system/_assets/sys_check.php');

        $controller = $this->getController();
        $action     = $this->getAction();

        $className = 'Controllers_' . ucfirst(strtolower($controller)) . 'Controller';

        if (!class_exists($className)) {
            throw new DispatchException('Controller `' . $controller . '` not found', 404);
        }

        if (!method_exists($className, strtolower($action) . 'Action')) {
            throw new DispatchException('Action `' . $action . '` not found in `' . $controller . '` controller', 404);
        }

        $class = new $className;
        call_user_func(array($class, 'initView'));
        if (method_exists($class, 'init')) {
            call_user_func(array($class, 'init'));
        }
        call_user_func(array($class, strtolower($action) . 'Action'));

        return $this;
    }

    /**
     * Check user is admin
     *
     * @return bool
     */
    static public function isAdmin()
    {
        if (Config::get('admin->enabled') === false || !isset($_COOKIE['adminLogin']) || !isset($_COOKIE['adminToken'])) {
            return false;
        }

        $hashedLogin = Database::hashString(Config::get('admin->login'), 'md5', 5);
        $hashedToken = Database::hashString(Config::get('admin->password'), 'sha1', 25);

        if ($_COOKIE['adminLogin'] === $hashedLogin && $_COOKIE['adminToken'] === $hashedToken) {
            return true;
        }

        return false;
    }

    /**
     * Check API requests
     *
     * @return bool
     */
    static public function isAPIrequest()
    {
        return isset($_REQUEST['isapi']);
    }

    /**
     * Returns a initialized instance of class
     *
     * @return FrontController
     */
    static public function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
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
     * Clear specified string
     *
     * @param string $str
     * @return array|mixed
     */
    static private function clearStr($str)
    {
        return trim(preg_replace('/[^a-zA-Z_]+/', '', $str));
    }

    /**
     * Is ajax request?
     *
     * @return boolean
     */
    static public function isAjaxRequest()
    {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
    }

    /**
     * Init template engine
     *
     * @return void
     */
    final protected function initView()
    {
        $this->view = Template::getInstance()->setLayout('mainLayout.tpl');
    }

    /**
     * Return unique user identifier
     *
     * @return string
     */
    public function getUserIdentifier()
    {
        if (isset($_COOKIE['tl_userId']) && $cookieData = @json_decode($_COOKIE['tl_userId'], true)) {
            if (is_array($cookieData) && isset($cookieData['key'])
                && preg_match('/^[a-f0-9]{80}$/', $cookieData['key'])
            ) {
                if (!isset($cookieData['time']) || time() - $cookieData['time'] > 86400) {
                    $cookieData['time'] = time();
                    setcookie('tl_userId', json_encode($cookieData), time() + 31556926); // year
                }

                return $cookieData['key'];
            }
        }

        $_COOKIE['tl_userId'] = array('key' => Database::hashString($_SERVER['REMOTE_ADDR'], 'sha1', 10));
        $_COOKIE['tl_userId']['key'] .= Database::hashString($_COOKIE['tl_userId']['key'], 'sha1', 15);
        $_COOKIE['tl_userId'] = json_encode($_COOKIE['tl_userId']);

        return $this->getUserIdentifier();
    }

}

class DispatchException extends SystemException
{

}

class AccessException extends SystemException
{

}

class FrontControllerException extends SystemException
{

}