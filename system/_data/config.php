<?php return array(
    'site_url'         => 'http://tinylink.dev/',
    'theme'            => 'clean',
    'site_title'       => 'TinyLink',
    'site_description' => 'Test description value',
    'site_keywords'    => 'site,key,words',
    'hash_salt'        => 'sa#lt%4',
    'redirect_page'    => true,
    'links_per_page'   => 10,
    'admin'            =>
        array(
            'enabled'  => true,
            'login'    => 'admin',
            'password' => '123',
        ),
    'cache'            =>
        array(
            'enabled'  => true,
            'method'   => 'file',
            'memcache' =>
                array(
                    'server' => '127.0.0.1',
                    'port'   => '11211',
                ),
        ),
);