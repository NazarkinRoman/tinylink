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

class Controllers_LinksController extends FrontController
{

    private function validateLink()
    {
        if (!$linkId = $_GET['linkId']) {
            throw new DispatchException('Requested link not found!', 404);
        }

        $db       = Database::getInstance();
        $linkData = $db->checkAlias($_GET['linkId']);

        if ($linkData === false) {
            throw new DispatchException('Requested link not found!', 404);
        }

        // delete link if lifetime is expired
        if ($db->linkExpired($linkData['lifetime'], $linkData['create_date'])) {
            $db->deleteLink($linkData['alias']);
            throw new DispatchException('Requested link not found!', 404);
        }

        return $linkData;
    }

    /* ==========================================================================
       Actions
       ========================================================================== */

    /**
     * Page with user links
     *
     * @throws SystemException
     */
    function myAction()
    {
        $db = Database::getInstance();

        $links       = $db->getLinksByUser($this->getUserIdentifier());
        $linkCount   = sizeof($links);
        $linkPerPage = Config::get('links_per_page');

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
            if (!isset($linkData['page_description']) || $linkData['page_description'] === null) {
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

        if ($linkCount === 0) {
            throw new SystemException('There is no links!');
        }

        if ($linkCount > $linkPerPage) {
            $this->view->pagination = $this->view->displayPagination($linkCount, $page);
            $this->view->en_block('has_pagination');
        } else {
            $this->view->del_block('has_pagination');
        }

        $this->view->linksflow = @$this->view->__result['linksflow'];
        $this->view->headMeta->addTitlePart('My links');
        // $this->view->headMeta->setDescription('some text');

        $this->view->compile('content');
    }

    /**
     * Deleting link
     *
     * @throws AccessException
     */
    function deleteAction()
    {
        $this->view->setAutocompiling(false);

        $linkData = $this->validateLink();
        if (!$this->isAdmin() && $linkData['author'] !== $this->getUserIdentifier()) {
            throw new AccessException('Access denided!', 400);
        }

        Database::getInstance()->deleteLink($linkData['alias']);

        if ($this->isAPIrequest()) {
            die(json_encode(array('status' => 'success')));
        }

        $this->view->flashMessage('Link has been deleted!');
        header('Location: ' . Config::get('site_url'));
        exit;
    }

    /**
     * Link page
     */
    function viewAction()
    {
        $linkData = $this->validateLink();

        if ($this->isAPIrequest()) {
            $this->view->setAutocompiling(false);
            unset($linkData['password'], $linkData['author']);
            die(json_encode(array('status' => 'success', 'data' => $linkData)));
        }

        // set vars
        foreach ($linkData as $key => $value) {
            if ($key === 'visits') {
                $value = number_format($value, 0, '.', ' ');
            }

            $key              = 'link_' . $key;
            $this->view->$key = $value;
        }

        // description blocks
        if ($linkData['page_description'] === null) {
            $this->view->en_block('hasnt_description')->del_block('has_description');
        } else {
            $this->view->del_block('hasnt_description')->en_block('has_description');
        }

        // title blocks
        if (!isset($linkData['page_title']) || $linkData['page_title'] === null) {
            $this->view->en_block('hasnt_title')->del_block('has_title');
        } else {
            $this->view->del_block('hasnt_title')->en_block('hasnt_title');
        }

        // `is_author` && `isnt_author` blocks
        if ($linkData['author'] === $this->getUserIdentifier() || $this->isAdmin()) {
            $this->view->en_block('is_author')->del_block('isnt_author');
        } else {
            $this->view->del_block('is_author')->en_block('isnt_author');
        }

        // password protection
        if ($linkData['author'] === $this->getUserIdentifier() || $this->isAdmin()
            || $linkData['password'] === null || ($linkData['password'] !== null && isset($_POST['password'])
                && Database::hashString($_POST['password']) === $linkData['password'])
        ) {
            $this->view->en_block('without_password')
                ->del_block('with_password');
        } else {
            if ($linkData['password'] !== null && isset($_POST['password'])
                && Database::hashString($_POST['password']) !== $linkData['password']
            ) {
                $this->view->en_block('wrong_password');
            } else {
                $this->view->del_block('wrong_password');
            }

            $this->view->en_block('with_password')->del_block('without_password');
        }

        $this->view->create_date   = date('d.m.Y H:i:s', $linkData['create_date']);
        $this->view->link_short    = Config::get('site_url') . $linkData['alias'];
        $this->view->link_url_crop = (strlen($linkData['url']) > 50) ? substr($linkData['url'], 0, 50) . '..'
            : $linkData['url'];

        // meta tags
        $this->view->headMeta->addTitlePart('View link');
        if (isset($linkData['page_description']) && $linkData['page_description'] !== null) {
            $this->view->headMeta->setDescription(substr($linkData['page_description'], 0, 150));
        }

        $this->view->compile('content');
    }

    /**
     * Go to link
     */
    function goAction()
    {
        $linkData = $this->validateLink();

        if (Config::get('redirect_page') === false) {
            if ($linkData['author'] === $this->getUserIdentifier() || $this->isAdmin()
                || $linkData['password'] === null
                || ($linkData['password'] !== null && isset($_POST['password'])
                    && Database::hashString($_POST['password']) === $linkData['password'])
            ) {
                Database::getInstance()->changeEntry($linkData['alias'], array('visits' => $linkData['visits'] + 1));
                $this->view->setAutocompiling(false);

                header('HTTP/1.1 301 Moved Permanently');
                header('Location: ' . $linkData['url']);
                exit;
            }
        }

        $this->view->setLayout('redirectPage.tpl')
            ->headMeta->addTitlePart('View link');

        if ($linkData['page_description'] !== null) {
            $this->view->headMeta->setDescription(substr($linkData['page_description'], 0, 150));
        }

        // password protection
        if ($linkData['author'] === $this->getUserIdentifier() || $this->isAdmin()
            || $linkData['password'] === null
            || ($linkData['password'] !== null && isset($_POST['password'])
                && Database::hashString($_POST['password']) === $linkData['password'])
        ) {
            Database::getInstance()->changeEntry($linkData['alias'], array('visits' => $linkData['visits'] + 1));
            $this->view->en_block('without_password')
                ->del_block('with_password');
        } else {
            if ($linkData['password'] !== null && isset($_POST['password'])
                && Database::hashString($_POST['password']) !== $linkData['password']
            ) {
                $this->view->en_block('wrong_password');
            } else {
                $this->view->del_block('wrong_password');
            }

            $this->view->en_block('with_password')
                ->del_block('without_password');
        }

        $this->view->full_url = $linkData['url'];

    }

}