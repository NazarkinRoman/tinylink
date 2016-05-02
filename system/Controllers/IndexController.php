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

class Controllers_IndexController extends FrontController
{

    function indexAction()
    {
        $this->view->compile('content');
    }

    function shortAction()
    {
        $this->view->setAutocompiling(false);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $db = Database::getInstance();

            // process URL
            if (!isset($_POST['url'])) {
                throw new SystemException('URL is not entered!', 400);
            } elseif (filter_var($_POST['url'], FILTER_VALIDATE_URL) === false) {
                throw new SystemException('URL is invalid!', 400);
            } elseif ($db->checkBlacklist($_POST['url'])) {
                throw new SystemException('URL is in blacklist!');
            }

            // process alias
            if (isset($_POST['alias']) && trim($_POST['alias'])) {
                $_POST['alias'] = trim($_POST['alias']);
                if (!preg_match('/^[a-zA-Z0-9]+[a-zA-Z0-9_]+[a-zA-Z0-9]+$/', $_POST['alias']) || strlen($_POST['alias']) > 15
                    || strlen($_POST['alias']) < 3
                ) {
                    throw new SystemException('Alias is invalid!', 400);
                }

                if ($db->checkAlias($_POST['alias']) !== false) {
                    throw new SystemException('Alias is taken!', 400);
                }
            } else {
                $_POST['alias'] = $db->genAlias();
            }

            // process password
            if (isset($_POST['password']) && trim($_POST['password'])) {
                if (strlen(trim($_POST['password'])) > 30) {
                    throw new SystemException('Password is too long!', 400);
                }

                $_POST['password'] = $db->hashString($_POST['password']);
            } else {
                $_POST['password'] = null;
            }

            // process time limit
            if (isset($_POST['lifetime']) && is_numeric($_POST['lifetime'])) {
                $options = array(
                    'options' => array(
                        'min_range' => 0,
                        'max_range' => 6,
                    )
                );

                if (filter_var($_POST['lifetime'], FILTER_VALIDATE_INT, $options) === false) {
                    throw new SystemException('Lifetime value is invalid!', 400);
                }
            } else {
                $_POST['lifetime'] = 0;
            }

            // so, add link to db
            $pageMeta = new HTMLparser($_POST['url']);
            $linkData = $db->addLink(
                array(
                     'url'              => $_POST['url'],
                     'alias'            => $_POST['alias'],
                     'password'         => $_POST['password'],
                     'lifetime'         => $_POST['lifetime'],
                     'page_description' => $pageMeta->description,
                     'page_title'       => $pageMeta->title,
                )
            );

            if ($this->isAjaxRequest()) {
                die(json_encode($linkData));
            } else if ($this->isAPIrequest()) {
                die(json_encode(array('status' => 'success', 'data' => $linkData)));
            }

            $this->view->flashMessage('Link has been successfully shortened!');
            header('Location: ' . Config::get('site_url') . $linkData['alias'] . '+');
            die('Go here - ' . Config::get('site_url') . $linkData['alias'] . '+');

        }

        throw new SystemException('Query is invalid!', 400);
        exit;
    }

    function staticAction()
    {
        if (!isset($_GET['pageAlias']) || !trim($_GET['pageAlias'])) {
            throw new DispatchException('Requested page not found!', 404);
        }

        $db       = Database::getInstance();
        $pageData = $db->getPage($_GET['pageAlias']);

        if (!is_array($pageData) || empty($pageData)) {
            throw new DispatchException('Requested page not found!', 404);
        }

        $this->view->headMeta->addTitlePart($pageData['title']);

        if (trim($pageData['keywords'])) {
            $this->view->headMeta->setKeywordsByString($pageData['keywords']);
        }

        if (trim($pageData['description'])) {
            $this->view->headMeta->setDescription(substr($pageData['description'], 0, 250));
        }

        foreach ($pageData as $key => $value) {
            $key              = 'page_' . $key;
            $this->view->$key = $value;
        }

        $this->view->compile('content');
    }
}