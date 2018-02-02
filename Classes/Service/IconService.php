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

use CuyZ\Notiz\Definition\Tree\Notification\NotificationDefinition;
use CuyZ\Notiz\Service\Traits\ExtendedSelfInstantiateTrait;
use TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\StringUtility;

class IconService implements SingletonInterface
{
    use ExtendedSelfInstantiateTrait;

    /**
     * @var IconRegistry
     */
    protected $iconRegistry;

    /**
     * @var array
     */
    protected $registeredIcons = [];

    /**
     * @param IconRegistry $iconRegistry
     */
    public function __construct(IconRegistry $iconRegistry)
    {
        $this->iconRegistry = $iconRegistry;
    }

    /**
     * @param NotificationDefinition $notification
     * @return string
     */
    public function registerNotificationIcon(NotificationDefinition $notification)
    {
        $iconIdentifier = 'tx-notiz-icon-notification-' . $notification->getIdentifier();

        if (!in_array($iconIdentifier, $this->registeredIcons)) {
            $iconPath = $notification->getIconPath();

            $iconProviderClass = StringUtility::endsWith(strtolower($iconPath), 'svg')
                ? SvgIconProvider::class
                : BitmapIconProvider::class;

            $this->iconRegistry->registerIcon(
                $iconIdentifier,
                $iconProviderClass,
                ['source' => $notification->getIconPath()]
            );
        }

        return $iconIdentifier;
    }
}
