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

$protocol = strpos(strtolower($_SERVER['SERVER_PROTOCOL']), 'https') === false ? 'http' : 'https';
$site_url = $protocol . '://' . $_SERVER['SERVER_NAME'] . '/';

return array(

    'site_url'         => $site_url,
    'theme'            => 'clean',
    // SEO parameters
    'site_title'       => 'TinyLink',
    'site_description' => 'Test description value',
    'site_keywords'    => 'site,   key,words,f,,',
    'hash_salt'        => 'sa#lt%4',
    'redirect_page'    => true,
    'links_per_page'   => 10,
    'admin'            => array(
        'enabled'  => true,
        'login'    => 'admin',
        'password' => '123'
    ),
    'cache'            => array(
        'enabled'  => true,
        'method'   => 'file',
        'memcache' => array(
            'server' => '127.0.0.1',
            'port'   => 11211
        )
    )

);