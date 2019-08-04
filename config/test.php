<?php
$app['debug'] = true;
$app['db.options'] = array(
    'driver'   => 'pdo_mysql',
    'host'     => 'localhost',
    'dbname'   => 'ac_todos',
    'user'     => 'root',
    'password' => '',
);
$app['security.users'] = array('username' => array('ROLE_USER', 'password'));
