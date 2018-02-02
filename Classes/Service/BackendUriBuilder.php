<?php

/*
 * Copyright (C) 2018
 * Nathan Boiron <nathan.boiron@gmail.com>
 * Romain Canon <romain.hydrocanon@gmail.com>
 *
 * This file is part of the TYPO3 NotiZ project.
 * It is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License, either
 * version 3 of the License, or any later version.
 *
 * For the full copyright and license information, see:
 * http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace CuyZ\Notiz\Service;

use CuyZ\Notiz\Service\Traits\ExtendedSelfInstantiateTrait;
use CuyZ\Notiz\Support\NotizConstants;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;

class BackendUriBuilder implements SingletonInterface
{
    use ExtendedSelfInstantiateTrait;

    const MODULE_ADMINISTRATION = 'Administration';

    /**
     * @var array
     */
    protected static $modules = [
        self::MODULE_ADMINISTRATION => [
            'controller' => 'Administration',
            'module' => NotizConstants::BACKEND_MODULE_ADMINISTRATION,
        ]
    ];

    /**
     * @var UriBuilder
     */
    protected $uriBuilder;

    /**
     * @param UriBuilder $uriBuilder
     */
    public function __construct(UriBuilder $uriBuilder)
    {
        $this->uriBuilder = $uriBuilder;
    }

    /**
     * @param string $action
     * @param string $module
     * @return string
     */
    public function uriFor($action, $module = self::MODULE_ADMINISTRATION)
    {
        $config = self::$modules[$module];

        return $this->uriBuilder
            ->reset()
            ->setArguments(['M' => $config['module']])
            ->uriFor($action, [], 'Backend\\' . $config['controller'], NotizConstants::EXTENSION_KEY, $config['module']);
    }
}
