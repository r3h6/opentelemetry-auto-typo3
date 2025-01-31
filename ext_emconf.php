<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'opentelemetry',
    'description' => 'Opentelemetry for TYPO3',
    'constraints' => [
        'depends' => [
            'typo3' => '12.0.0-13.4.99',
        ],
    ],
    'autoload' => [
        'psr-4' => [
            'R3H6\\Opentelemetry\\' => 'Classes/',
        ],
    ],
];
