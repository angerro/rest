<?php

return [
    'db' => [
        'host'     => '172.16.238.12',
        'dbname'   => 'data',
        'user'     => 'root',
        'password' => '',
    ],
    'route_list' => [
        'records' => '\App\Entity\Record',
        'seeder' => '\App\Entity\Seed'
    ],
];
