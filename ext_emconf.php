<?php
/** @noinspection PhpUndefinedVariableInspection */
$EM_CONF[$_EXTKEY] = [
    'title' => 'NotiZ • Powerful notification dispatcher',
    'description' => 'Handle any type of notification in TYPO3 with ease: emails, SMS, Slack and more. Listen to your own events or provided ones (scheduler task finishing, extension installed, etc…).',

    'version' => '2.1.2',
    'state' => 'stable',

    'author' => 'Romain Canon, Nathan Boiron',
    'author_email' => 'team.cuyz@gmail.com',

    'category' => 'be',
    'clearCacheOnLoad' => 1,

    'constraints' => [
        'depends' => [
            'typo3' => '8.7.0-9.5.99',
        ],
    ],
];
