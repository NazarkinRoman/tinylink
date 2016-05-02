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

class Bootstrap
{

    static function run()
    {
        // hide all PHP errors
        @error_reporting(0);
        @ini_set('display_errors', false);
        @ini_set('html_errors', false);
        @ini_set('error_reporting', 0);

        // session
        session_start();

        // init autoloader
        include APPLICATION_PATH . '/system/Autoloader.php';
        Autoloader::init();

        // dispatching
        FrontController::getInstance()->dispatch();
    }

}

class SystemException extends Exception
{

    /**
     * Display HTML page with error data
     *
     * @param string    $message
     * @param int       $httpcode
     * @param Exception $previous
     *
     * @return \SystemException
     */
    public function __construct($message, $httpcode = 0, Exception $previous = null)
    {
        if (FrontController::isAPIrequest()) {
            Template::getInstance()->setAutocompiling(false);
            die(json_encode(array('status' => 'exception', 'type' => get_class($this), 'message' => $message)));
        }

        if (Template::getInstance()->checkTplFile('/_layouts/error.tpl', false)) {
            $tpl = Template::getInstance();
            $tpl->setAutocompiling(true);
            $tpl->setLayout('error.tpl');
            $tpl->headMeta->addTitlePart('Error');

            $tpl->exceptionClass = get_class($this);
            $tpl->errorMessage   = $message;
            $tpl->errorCode      = $httpcode;
            $tpl->trace          = $this->getTraceAsHTMLString();

            if ($httpcode !== 0 && function_exists('http_response_code')) {
                @http_response_code($httpcode);
            }

            exit;
        }

        Template::getInstance()->setAutocompiling(false);
        $templateFile = APPLICATION_PATH . '/system/_assets/systemError.html';
        if (!file_exists($templateFile) || !is_readable($templateFile)) {
            die('Error: <br />' . $message);
        }

        $template = file_get_contents($templateFile);

        $template = str_replace('{exceptionClass}', get_class($this), $template);
        $template = str_replace('{errorMessage}', $message, $template);
        $template = str_replace('{errorCode}', $httpcode, $template);
        $template = str_replace('{trace}', $this->getTraceAsHTMLString(), $template);

        if ($httpcode !== 0 && function_exists('http_response_code')) {
            @http_response_code($httpcode);
        }

        exit($template);
    }

    /**
     * Split trace with <br />
     *
     * @return string
     */
    public function getTraceAsHTMLString()
    {
        $trace = $this->getTraceAsString();
        $trace = join('<br />' . PHP_EOL, explode("\n", $trace));

        return $trace;
    }

}

class FileException extends SystemException
{

    public function __construct($message, $filename)
    {
        $filename = str_replace(array('\\', '/'), DIRECTORY_SEPARATOR, $filename);
        $filename = join(DIRECTORY_SEPARATOR, array_slice(explode(DIRECTORY_SEPARATOR, $filename), -3, 3));

        $message = $message . ' - `' . $filename . '`';
        parent::__construct($message, 500);
    }

}