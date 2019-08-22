<?php
return [
    'driver'    => 'mysql',
    'host'      => 'localhost',
    'database'  => '<your database>',
    'username'  => 'root',
    'password'  => '<your password>',
    'charset'   => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',    //utf8mb4 is a better collation than utf8, if mysql version > 5.5.3
    'prefix'    => ''
];