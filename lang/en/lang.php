<?php

return [
    'log' => 'Log',
    'date' => 'Date',
    'undo' => 'Undo',
    'changes' => [
        'user' => ':user has :type :model',
        'unknown' => ':model is :type',
        'type' => [
            'updated' => 'updated',
            'created' => 'created',
            'deleted' => 'deleted',
        ],
        'column' => ':column changed from :from to :to'
    ],
];