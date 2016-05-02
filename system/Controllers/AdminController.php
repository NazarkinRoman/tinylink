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

class Controllers_AdminController extends FrontController
{

    private $parentElem = ''; // for rendering form on `settings` page
    private $hasChanges = false; // for `save_settings` controller

    function init()
    {
        $this->view->setLayout('admin.tpl');

        if ($this->getAction() !== 'login' && !$this->isAdmin()) {
            header('Location: ' . Config::get('site_url') . 'admin/login/');
            exit;
        }

        $this->view->headMeta->addTitlePart('Admin panel');
    }

    /**
     * Display all links on site, statistics
     *
     * @throws SystemException
     */
    function indexAction()
    {
        $db = Database::getInstance();

        $links       = $db->getLinksByUser(null);
        $linkCount   = sizeof($links);
        $linkPerPage = Config::get('links_per_page');

        // statistics
        $linksToday = 0;

        foreach ($links as $item) {
            if (time() - $item['create_date'] < 86400) {
                $linksToday++;
            } else {
                break;
            }
        }

        $page = (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0) ? $_GET['page'] : 1;
        $this->view->loadTemplate($this->getController(), 'link.single');

        // pages
        $links = array_slice($links, ($page - 1) * $linkPerPage, $linkPerPage, true);

        foreach ($links as $linkData) {
            foreach ($linkData as $key => $value) {
                if ($key === 'visits') {
                    $value = number_format($value, 0, '.', ' ');
                }

                $key              = 'link_' . $key;
                $this->view->$key = $value;
            }

            // title blocks
            if (!isset($linkData['page_title']) || $linkData['page_title'] === null) {
                $this->view->en_block('hasnt_title')->del_block('has_title');
            } else {
                $this->view->del_block('hasnt_title')->en_block('has_title');
            }

            // description blocks
            if ($linkData['page_description'] === null) {
                $this->view->en_block('hasnt_description')->del_block('has_description');
            } else {
                $this->view->del_block('hasnt_description')->en_block('has_description');
            }

            $this->view->create_date   = date('d.m.Y H:i', $linkData['create_date']);
            $this->view->link_short    = Config::get('site_url') . $linkData['alias'];
            $this->view->link_url_crop = (strlen($linkData['url']) > 70) ? substr($linkData['url'], 0, 70) . '..'
                : $linkData['url'];

            $this->view->compile('linksflow');
        }

        $this->view->clear(true);

        // assign statistics
        $this->view->linksCount = number_format($linkCount, 0, '.', ' ');
        $this->view->linksToday = number_format($linksToday, 0, '.', ' ');

        if ($linkCount === 0) {
            $this->view->en_block('no_links');
        } else {
            $this->view->del_block('no_links');
        }

        if ($linkCount > $linkPerPage) {
            $this->view->pagination = $this->view->displayPagination($linkCount, $page, true);
            $this->view->en_block('has_pagination');
        } else {
            $this->view->del_block('has_pagination');
        }

        $this->view->linksflow = @$this->view->__result['linksflow'];

        $this->view->compile('content');
    }

    /**
     * Static Pages List
     */
    function pagesAction()
    {
        $db = Database::getInstance();

        $pages      = $db->getAllPages();
        $pagesCount = sizeof($pages);

        // pagination
        $page = (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0) ? $_GET['page'] : 1;
        $this->view->loadTemplate($this->getController(), 'page.single');


        foreach ($pages as $pageData) {
            foreach ($pageData as $key => $value) {
                $key              = 'page_' . $key;
                $this->view->$key = $value;
            }

            // title blocks
            if (!isset($pageData['title']) || $pageData['title'] === null) {
                $this->view->en_block('hasnt_title')->del_block('has_title');
            } else {
                $this->view->del_block('hasnt_title')->en_block('has_title');
            }

            // description blocks
            if ($pageData['description'] === null) {
                $this->view->en_block('hasnt_description')->del_block('has_description');
            } else {
                $this->view->del_block('hasnt_description')->en_block('has_description');
            }

            $this->view->create_date = date('d.m.Y H:i', @$pageData['create_date']);
            $this->view->full_url    = Config::get('site_url') . 'page/' . $pageData['alias'] . '.html';

            $this->view->compile('pagesflow');
        }

        $this->view->clear(true);

        // blocks processing
        if ($pagesCount === 0) {
            $this->view->en_block('no_pages');
        } else {
            $this->view->del_block('no_pages');
        }

        $this->view->pagesflow = @$this->view->__result['pagesflow'];

        $this->view->compile('content');
    }

    /**
     * Add or edit pages
     */
    function page_addAction()
    {
        $db            = Database::getInstance();
        $pageAlias     = (isset($_GET['pageAlias'])) ? trim($_GET['pageAlias']) : false;
        $defaultParams = array('title', 'alias', 'description', 'keywords', 'content');

        if ($pageAlias) {
            $pageData = $db->getPage($pageAlias);

            if (!$pageData) {
                throw new DispatchException('Requested page not found!', 404);
            }

            $this->view->del_block('isnt_edit');
            $this->view->en_block('is_edit');

            foreach ($pageData as $pageParam => $pageValue) {
                if ($pageParam == 'content') {
                    $pageValue = htmlentities($pageValue);
                }
                $this->view->set('page_' . $pageParam, $pageValue);
            }
        } else {
            $this->view->en_block('isnt_edit');
            $this->view->del_block('is_edit');

            foreach ($defaultParams as $param) {
                $this->view->set('page_' . $param, '');
            }
        }

        // process POST request
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['type'])) {

            if (!$pageAlias) {
                $pageData = array('create_date' => time());
            }

            $originalData = $pageData;

            foreach ($_POST as $param => $value) {
                $param = strtolower($param);
                if (substr($param, 0, 5) !== 'page_') {
                    continue;
                }

                $param = substr($param, 5);
                if (!in_array($param, $defaultParams)) {
                    continue;
                }
                if ($param == 'content') {
                    $value = html_entity_decode($value);
                }
                if ($param == 'alias' && !trim($value)) {
                    $this->view->flashMessage('Page successfully added!');
                }

                $pageData[$param] = $value;
            }

            if ($originalData == $pageData) {
                goto action_final;
            }

            // save data
            $db->changePage($pageData['alias'], $pageData);

            header('Location: ' . Config::get('site_url') . 'admin/pages');
            exit;
        }

        action_final:
        $this->view->compile('content');
    }

    /**
     * Delete page
     */
    function page_deleteAction()
    {
        $db = Database::getInstance();
        $db->deletePage(trim($_GET['pageAlias']));

        if (!$this->isAjaxRequest()) {
            header('Location: ' . Config::get('site_url') . 'admin/pages');
        }

        exit;
    }

    /**
     * Authorization
     */
    function loginAction()
    {
        $this->view->login = '';

        if ($this->isAdmin() || (isset($_POST['login']) && $_POST['password'])) {
            $hashedLogin = Database::hashString(Config::get('admin->login'), 'md5', 5);
            $hashedToken = Database::hashString(Config::get('admin->password'), 'sha1', 25);

            if ($this->isAdmin() || (Database::hashString(trim($_POST['login']), 'md5', 5) === $hashedLogin && Database::hashString(trim($_POST['password']), 'sha1', 25) === $hashedToken)) {
                $this->view->setAutocompiling(false);

                $path = parse_url(Config::get('site_url'), PHP_URL_PATH);
                setcookie('adminLogin', $hashedLogin, time() + 86400, $path);
                setcookie('adminToken', $hashedToken, time() + 86400, $path);

                header('Location: ' . Config::get('site_url') . 'admin/');
                exit;
            }

            $this->view->flashMessage('Username or password is wrong!');
            $this->view->en_block('wrong_password');
            $this->view->login = addslashes(htmlspecialchars(trim($_POST['login'])));
        }

        $this->view->headMeta->addTitlePart('Login');
        $this->view->compile('content');
    }

    /**
     * Log out users from admin panel
     */
    function logoutAction()
    {
        $this->view->setAutocompiling(false);

        $path = parse_url(Config::get('site_url'), PHP_URL_PATH);
        setcookie('adminLogin', null, time() - 86400, $path);
        setcookie('adminToken', null, time() - 86400, $path);

        $this->view->flashMessage('Good luck! ;)');
        header('Location: ' . Config::get('site_url') . 'admin/login/');
        exit;
    }

    /**
     * Settings page
     */
    function settingsAction()
    {
        // theme list
        $themes_dir  = APPLICATION_PATH . '/templates/';
        $themes_list = null;
        $themes      = scandir($themes_dir);
        foreach ($themes as $value) {
            if ($value === '.' || $value === '..') {
                continue;
            }
            if (is_dir($themes_dir . $value)) {
                if ($value === Config::get('theme')) {
                    $selected = ' selected';
                } else {
                    $selected = '';
                }

                $themes_list .= '<option value="' . $value . '"' . $selected . '>' . ucfirst(strtolower($value)) . '</option>' . PHP_EOL;
            }
        }

        // black-list
        $bl_view = null;
        $bl_file = @file_get_contents(APPLICATION_PATH . '/system/_data/blacklist.db');
        $bl_file = @json_decode($bl_file);
        if ($bl_file !== false && $bl_file !== null) {
            foreach ($bl_file as $item) {
                $bl_view .= $item . PHP_EOL;
            }
        }
        $this->view->themes_list     = $themes_list;
        $this->view->blacklist_items = $bl_view;

        // cache enabled block
        if (Config::get('cache->enabled') || Config::get('cache->enabled') == 'yes') {
            $this->view->en_block('cache-enabled');
        } else {
            $this->view->del_block('cache-enabled');
        }

        // cache method block
        if (Config::get('cache->method') == 'files' || Config::get('cache->method') == 'file') {
            $this->view->en_block('cache-file')->del_block('cache-memcache');
        } else {
            $this->view->del_block('cache-file')->en_block('cache-memcache');
        }

        $this->renderConfigForm(Config::get());
        $this->view->compile('content');
    }

    /**
     * Save settings
     */
    function save_settingsAction()
    {
        $this->view->setAutocompiling(false);

        // blacklist
        if (@$_POST['type'] == 'black_list') {
            if (!isset($_POST['black-list'])) {
                throw new SystemException('Invalid query!');
            }

            $blacklist = $_POST['black-list'];
            $blacklist = preg_split('/$\R?^/m', $blacklist);
            foreach ($blacklist as $i => $line) {
                $line = trim($line);

                if (!$line) {
                    unset($blacklist[$i]);
                    continue;
                }
                $blacklist[$i] = $line;
            }
            $blacklist = @json_encode($blacklist);
            @file_put_contents(APPLICATION_PATH . '/system/_data/blacklist.db', $blacklist);

            $this->view->flashMessage('Settings saved!');
            header('Location: ' . Config::get('site_url') . 'admin/settings/');
            exit;
        }

        // global settings
        if (@$_POST['type'] == 'global') {
            $this->validateConfigForm(Config::get());
            if ($this->hasChanges) {
                Config::saveFile(); // save settings to config
                $this->view->flashMessage('Settings saved!');
            }

            header('Location: ' . Config::get('site_url') . 'admin/settings/');
            exit;
        }
    }

    /**
     * Add variables to view from specified array
     *
     * @param array $config
     */
    private function renderConfigForm($config)
    {
        foreach ($config as $key => $value) {
            if (is_array($value)) {
                if ($this->parentElem !== '') {
                    $this->parentElem .= '->' . $key;
                } else {
                    $this->parentElem = $key;
                }

                $this->renderConfigForm($value);

                $this->parentElem = explode('->', $this->parentElem);
                array_pop($this->parentElem);
                $this->parentElem = join('->', $this->parentElem);

                continue;
            }

            $this->view->set(($this->parentElem !== '') ? $this->parentElem . '->' . $key : $key, $value);
        }
    }

    /**
     * Write variables to settings array from the POST
     *
     * @param array $config
     */
    private function validateConfigForm($config)
    {
        foreach ($config as $key => $value) {
            if (is_array($value)) {
                if ($this->parentElem !== '') {
                    $this->parentElem .= '->' . $key;
                } else {
                    $this->parentElem = $key;
                }

                $this->validateConfigForm($value);

                $this->parentElem = explode('->', $this->parentElem);
                array_pop($this->parentElem);
                $this->parentElem = join('->', $this->parentElem);

                continue;
            }

            // save settings to config array
            $field_name = ($this->parentElem !== '') ? $this->parentElem . '->' . $key : $key;
            if ($field_name == 'cache->enabled') {
                continue;
            }

            if (isset($_POST[$field_name]) && trim($_POST[$field_name]) && trim($_POST[$field_name]) !== Config::get($field_name)) {
                $this->hasChanges = true;
                Config::set($field_name, $_POST[$field_name]);
            }
        }

        // save `cache->enabled` option
        $cache_value = (isset($_POST['cache->enabled'])) ? true : false;
        if ($cache_value !== Config::get('cache->enabled')) {
            $this->hasChanges = true;
            Config::set('cache->enabled', $cache_value);
        }

        // update access tokens
        $hashedLogin = Database::hashString(Config::get('admin->login'), 'md5', 5);
        $hashedToken = Database::hashString(Config::get('admin->password'), 'sha1', 25);
        $path        = parse_url(Config::get('site_url'), PHP_URL_PATH);

        setcookie('adminLogin', $hashedLogin, time() + 86400, $path);
        setcookie('adminToken', $hashedToken, time() + 86400, $path);
    }

}