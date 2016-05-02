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

if (!defined('APPLICATION_PATH')) {
    die('Forbidden!');
}

// files and folders that should be writable
$permList = array(
    '/system/_data/',
    '/system/_data/_cache/',
    '/system/_data/_database/',
    '/system/_data/blacklist.db',
    '/system/_data/config.php'
);
$errors   = array();

foreach ($permList as $file) {
    $check = APPLICATION_PATH . $file;
    $type  = (is_dir($check)) ? 'Folder' : 'File';

    if (!is_writable($check)) {
        $errors[] = "{$type} <b>{$file}</b> is not writable!";
    } elseif (!is_readable($check)) {
        $errors[] = "{$type} <b>{$file}</b> is not readable!";
    }
}

if (is_dir(APPLICATION_PATH . '/UPDATE/')) {
    $errors[] = 'Delete the "UPDATE" directory.';
}

if (!empty($errors)) {
    echo "Some errors found:" . PHP_EOL . "<ul>" . PHP_EOL;
    foreach ($errors as $msg) {
        echo "<li>{$msg}</li>" . PHP_EOL;
    }
    echo "</ul>" . PHP_EOL . "<span style=\"color:red\">Please correct these errors and refresh the page. <br /> If you have any problems - see the documentation or contact author.</span>";
    exit;
}