<?php
/** @noinspection PhpUndefinedVariableInspection */
$EM_CONF[$_EXTKEY] = [
    'title' => 'NotiZ • Powerful notification dispatcher',
    'description' => 'Handle any type of notification in TYPO3 with ease: emails, SMS, Slack and more. Listen to your own events or provided ones (scheduler task finishing, extension installed, etc…).',

    'version' => '0.2.0',
    'state' => 'alpha',

    'author' => 'Romain Canon, Nathan Boiron',
    'author_email' => 'team.cuyz@gmail.com',

    'category' => 'be',
    'clearCacheOnLoad' => 1,

    'constraints' => [
        'depends' => [
            'typo3' => '7.6.0-8.7.99',
            'configuration_object' => '1.10.0-1.99.99',
        ],
    ],
];

// Test