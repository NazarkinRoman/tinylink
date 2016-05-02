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

class Template
{

    protected static $_instance;

    public $__result = array(),
        $headMeta;

    private $template_dir, $autoCompiling = true,
        $__vars = array(),
        $__blocks = array('enabled' => array(), 'disabled' => array()),
        $__template,
        $__layout,
        $__flashMessages = array();

    private function __clone() { }

    private function __wakeup() { }

    private function __construct()
    {
        $this->template_dir = APPLICATION_PATH . '/templates/' . Config::get('theme') . '/';
        $this->headMeta     = new Template_HeadMeta();
    }

    /**
     * Returns a initialized instance of Template class
     *
     * @return Template
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Display rendered template on script exit
     *
     * @return void
     */
    public function __destruct()
    {
        if (!empty($this->__flashMessages)) {
            $_SESSION['flashMessage'] = $this->__flashMessages;
        }

        if ($this->autoCompiling === true) {
            exit($this->compile());
        }
    }

    /**
     * Add flash message
     *
     * @param string $message
     */
    public function flashMessage($message)
    {
        if (session_id() == '') {
            session_start();
        }

        $this->__flashMessages[] = $message;
    }

    /**
     * Display flash message(s)
     *
     * @return null|string
     */
    public function getFlashMessages()
    {
        if (session_id() == '') {
            session_start();
        }

        if (!isset($_SESSION['flashMessage']) || !is_array($_SESSION['flashMessage']) || empty($_SESSION['flashMessage'])) {
            unset($_SESSION['flashMessage']);

            return null;
        }

        $html = "<ul>\r\n";
        foreach ($_SESSION['flashMessage'] as $msg) {
            $html .= "<li>{$msg}</li>\r\n";
        }

        $html .= "</ul>";
        unset($_SESSION['flashMessage']);

        return $html;
    }

    /**
     * Display pagination
     *
     * @param int  $itemsCount
     * @param int  $cPage
     * @param bool $admin
     *
     * @return string
     */
    public function displayPagination($itemsCount, $cPage, $admin = false)
    {
        $itemsPerPage = Config::get('links_per_page');
        $out          = null;

        $urlPart = Config::get('site_url');
        $urlPart .= ($admin) ? 'admin/' : 'my/';
        $pagesCount = ceil($itemsCount / $itemsPerPage);
        $stPage     = $cPage - 3;

        if ($stPage < 1) {
            $stPage = 1;
        }
        $endPage = $cPage + 3;

        if ($endPage > $pagesCount) {
            $endPage = $pagesCount;
        }

        if ($cPage > 1) {
            $out .= '<a href="' . $urlPart . ($cPage - 1) . '" class="prev big_button">Previous</a> ';
        } else {
            $out .= '<span class="inactive prev big_button">Previous</span> ';
        }

        if ($stPage > 1) {
            $out .= '<span class="inactive big_button">...</span>';
        }
        for ($i = $stPage; $i <= $endPage; $i++) {
            if ($i == $cPage) {
                $out .= '<span class="inactive big_button">' . $i . '</span> ';
            } else {
                $out .= '<a href="' . $urlPart . $i . '" class="big_button">' . $i . '</a> ';
            }
        }

        if ($endPage < $pagesCount) {
            $out .= '<span class="inactive big_button">...</span>';
        }

        if ($cPage < $pagesCount) {
            $out .= '<a href="' . $urlPart . ($cPage + 1) . '" class="next big_button">Next</a> ';
        } else {
            $out .= '<span class="inactive next big_button">Next</span> ';
        }


        return $out;
    }

    /**
     * Compile
     *
     * @param bool|string $block_name
     * @return string|void
     */
    public function compile($block_name = false)
    {
        $template = ($block_name === false) ? $this->__layout : $this->__template;

        if ($block_name !== false && $this->__template === null) {
            $template = $this->loadTemplate(FrontController::getController(), FrontController::getAction());
        }

        // system vars
        $this->SITEURL     = Config::get('site_url');
        $this->SITE_DOMAIN = parse_url(Config::get('site_url'), PHP_URL_HOST);
        $this->THEME       = $this->SITEURL . 'templates/' . Config::get('theme');
        $this->ACTION      = FrontController::getAction();
        $this->CONTROLLER  = FrontController::getController();
        // SEO
        $this->TITLE       = $this->headMeta->getTitle();
        $this->DESCRIPTION = $this->headMeta->getDescription();
        $this->KEYWORDS    = $this->headMeta->getKeywords();

        // flash messages
        if ($block_name === false) {
            $messages = $this->getFlashMessages();
            if ($messages !== null) {
                $this->en_block('flashmessage');
                $this->flashmessage = $messages;
            } else {
                $this->del_block('flashmessage');
            }
        }

        // remove smarty comments
        $template = preg_replace('#\{\*.*\*\}#s', '', $template);

        // inclusions
        if (strpos($template, "{include file=") !== false) {
            $template = preg_replace("#\\{include file=['\"](.+?)['\"]\\}#ies", "\$this->loadTemplateByFilename('\\1', true)", $template);
        }

        // process enabled blocks
        foreach ($this->__blocks['enabled'] as $block) {
            $template = str_replace(array('[' . $block . ']', '[/' . $block . ']'), '', $template);
        }

        // process disabled blocks
        foreach ($this->__blocks['disabled'] as $block) {
            $template = preg_replace('|\[' . $block . '\].*\[\/' . $block . '\]|Uus', '', $template);
        }

        // process vars
        foreach ($this->__vars as $var => $value) {
            $template = str_replace('{' . $var . '}', $value, $template);
        }

        // process containers
        if (strpos($template, '{container:') !== false) {
            $template = preg_replace_callback('|\{container:\s*([a-zA-Z0-9]{3,})\s*\}|is', array($this, 'getContainer'), $template);
        }

        $this->clear();

        if ($block_name === false) {
            return $template;
        }

        if (isset($this->__result[$block_name])) {
            $this->__result[$block_name] .= $template;
        } else {
            $this->__result[$block_name] = $template;
        }
    }

    /**
     * Load template file by controller/action name
     *
     * @param string $controller
     * @param string $action
     * @param bool   $return
     *
     * @return string|Template
     */
    public function loadTemplate($controller, $action, $return = false)
    {
        $filename = basename($controller) . DIRECTORY_SEPARATOR . basename($action) . '.tpl';
        $this->checkTplFile($filename);
        $contents = file_get_contents($this->template_dir . $filename);

        if ($return) {
            return $contents;
        }

        $this->__template = $contents;

        return $contents;
    }

    /**
     * Check specified file
     *
     * @param string  $filename
     * @param boolean $throw
     *
     * @throws FileException
     *
     * @return bool|void
     */
    public function checkTplFile($filename, $throw = true)
    {
        $filename = preg_replace('~((/|\\\)[\.]+(/|\\\))~', DIRECTORY_SEPARATOR, $this->template_dir . $filename);
        $filename = str_replace(array('\\', '/'), DIRECTORY_SEPARATOR, $filename);
        $except   = null;

        if (!file_exists($filename)) {
            $except = 'File is not exists';
        } elseif (!is_readable($filename)) {
            $except = 'File is not readable';
        }

        if ($except !== null) {
            if ($throw) {
                throw new FileException($except, $filename);
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     * Clear variable buffer
     *
     * @param bool $template
     * @return void
     */
    public function clear($template = false)
    {
        if ($template) {
            $this->__template = null;
        }
        $this->__blocks = array('enabled' => array(), 'disabled' => array());
        $this->__vars   = array();
    }

    /**
     * Load template by filename
     *
     * @param string $filename
     * @param bool   $return
     * @return string
     */
    public function loadTemplateByFilename($filename, $return = false)
    {
        $this->checkTplFile($filename);
        $contents = file_get_contents($this->template_dir . $filename);

        if ($return) {
            return $contents;
        }

        $this->__template = $contents;

        return $contents;
    }

    /**
     * Set layout file
     *
     * @param string $filename
     * @return Template
     */
    public function setLayout($filename)
    {
        $filename = '/_layouts/' . basename($filename);
        $this->checkTplFile($filename);

        $this->__layout = file_get_contents($this->template_dir . $filename);

        return $this;
    }

    /**
     * Change compiling mode
     *
     * @param boolean $state
     */
    public function setAutocompiling($state)
    {
        $this->autoCompiling = ($state);
    }

    /**
     * PHP5 magiс methods for work with template variables
     *
     * @param string $var
     * @internal param string $value
     *
     * @return mixed
     */
    public function __get($var) { return $this->__vars[$var]; }

    public function __set($var, $value) { $this->__vars[$var] = $value; }

    public function set($var, $value) { $this->__vars[$var] = $value; }

    public function __unset($var) { unset($this->__vars[$var]); }

    public function __isset($var) { return isset($this->__vars[$var]); }

    /**
     * Enable/Disable template blocks
     *
     * @param string $name
     *
     * @return Template
     */
    public function en_block($name)
    {
        $this->__blocks['enabled'][] = $name;

        return $this;
    }

    public function del_block($name)
    {
        $this->__blocks['disabled'][] = $name;

        return $this;
    }

    /**
     * Return container text
     *
     * @param string $name
     * @return string
     */
    private function getContainer($name)
    {
        if (!isset($name[1])) {
            return '';
        }
        $name = $name[1];

        return (isset($this->__result[$name])) ? $this->__result[$name] : '';
    }

}

class Template_HeadMeta
{

    /**
     * @var $__headTitle Meta-tag `title`
     */
    private $__titleParts = array(),
        $__titleDelimiter = ' / ',
        $__titleArrangeMethod;

    /**
     * `Title` arrange method
     */
    const HEADTITLE_PREPEND = 1,
        HEADTITLE_APPEND    = 0;

    /**
     * @var $__description Meta-tag `description`
     */
    private $__description;

    /**
     * @var $__keywords Meta-tag `keywords`
     */
    private $__keywords = array(),
        $__keywordsDelimiter = ', ';

    /**
     * Set default values
     */
    public function __construct()
    {
        $this->__titleArrangeMethod = self::HEADTITLE_PREPEND;

        $this->addTitlePart(Config::get('site_title', null)); // title

        $this->setDescription(Config::get('site_description', null)); // description

        $this->setKeywordsByString(Config::get('site_keywords', null)); // keywords
    }

    /**
     * Add title part
     *
     * @param string $titlePart
     */
    public function addTitlePart($titlePart)
    {
        if ($this->__titleArrangeMethod == self::HEADTITLE_PREPEND) {
            array_unshift($this->__titleParts, $titlePart);
        } elseif ($this->__titleArrangeMethod == self::HEADTITLE_APPEND) {
            array_push($this->__titleParts, $titlePart);
        }
    }

    /**
     * Change page title delimiter
     *
     * @param string $delimiter
     */
    public function setTitleDelimiter($delimiter)
    {
        if (is_string($delimiter)) {
            $this->__titleDelimiter = $delimiter;
        }
    }

    /**
     * Change title arrange method
     *
     * @param const|int $arrange
     */
    public function setTitleArrange($arrange = self::HEADTITLE_PREPEND)
    {
        if ($arrange == HEADTITLE_PREPEND) {
            $this->__titleArrangeMethod = self::HEADTITLE_PREPEND;
        } elseif ($arrange == HEADTITLE_APPEND) {
            $this->__titleArrangeMethod = self::HEADTITLE_APPEND;
        }
    }

    /**
     * Return page title as string
     *
     * @return string
     */
    public function getTitle()
    {
        return addslashes(trim(join($this->__titleDelimiter, $this->__titleParts)));
    }

    /**
     * Set `keywords` meta-tag
     *
     * @param string $keywords
     */
    public function setKeywordsByArray($keywords)
    {
        $this->__keywords = array_merge($this->__keywords, $keywords);
    }

    /**
     * Set `keywords` meta-tag as string
     *
     * @param string $keywords
     * @param string $delimiter
     * @return bool
     */
    public function setKeywordsByString($keywords, $delimiter = ',')
    {
        $keywords = explode($delimiter, $keywords);

        if ($keywords === false || empty($keywords)) {
            return false;
        }

        // check all values
        foreach ($keywords as $key => $value) {
            $keywords[$key] = trim($value);

            if (!$keywords[$key]) {
                unset($keywords[$key]);
            }
        }

        $this->setKeywordsByArray($keywords);
    }

    /**
     * Change `keywords` delimiter
     *
     * @param string $delimiter
     */
    public function setKeywordsDelimiter($delimiter)
    {
        if (is_string($delimiter)) {
            $this->__keywordsDelimiter = $delimiter;
        }
    }

    /**
     * Return keywords as string
     */
    public function getKeywords()
    {
        return addslashes(trim(join($this->__keywordsDelimiter, $this->__keywords)));
    }

    /**
     * Set `description` meta-tag
     *
     * @param string $descr
     */
    public function setDescription($descr)
    {
        if (is_string($descr) && strlen($descr) > 0) {
            $this->__description = $descr;
        }
    }

    public function getDescription()
    {
        return addslashes(trim($this->__description));
    }
}