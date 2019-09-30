<?php

return [
    'settings' => [
        'displayErrorDetails' => false, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header
        'determineRouteBeforeAppMiddleware' => true,

        'renderer' => [
            'template_path' => __DIR__ . '/../../templates/',
        ],
        'db_easy_pick' => [
            'host' => 'localhost',
            'user' => '',
            'pass' => '',
            'dbname' => '',
        ],
        'mailcamp_uri' => ''
    ],
];
