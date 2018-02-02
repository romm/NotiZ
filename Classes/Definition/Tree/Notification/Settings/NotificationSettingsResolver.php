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

namespace CuyZ\Notiz\Definition\Tree\Notification\Settings;

use CuyZ\Notiz\Notification\Settings\NotificationSettings;
use Romm\ConfigurationObject\Service\Items\MixedTypes\MixedTypesInterface;
use Romm\ConfigurationObject\Service\Items\MixedTypes\MixedTypesResolver;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * Resolver for the property:
 *
 * @see \CuyZ\Notiz\Definition\Tree\Notification\NotificationDefinition::$settings
 */
class NotificationSettingsResolver implements SingletonInterface, MixedTypesInterface
{
    /**
     * Dynamically sets the settings class name of the notification.
     *
     * This class name was previously inserted by the method below:
     *
     * @see \CuyZ\Notiz\Definition\Tree\Notification\NotificationDefinition::fetchSettingsClassName
     *
     * @param MixedTypesResolver $resolver
     */
    public static function getInstanceClassName(MixedTypesResolver $resolver)
    {
        $settingsClassName = self::getSettingsClassName($resolver);

        if (null !== $settingsClassName) {
            $resolver->setObjectType($settingsClassName);
        }
    }

    /**
     * @param MixedTypesResolver $resolver
     * @return string
     */
    protected static function getSettingsClassName(MixedTypesResolver $resolver)
    {
        $data = $resolver->getData();

        $data = is_array($data)
            ? $data
            : [];

        return isset($data[NotificationSettings::SETTINGS_CLASS_NAME])
            ? $data[NotificationSettings::SETTINGS_CLASS_NAME]
            : NotificationSettings::TYPE_DEFAULT;
    }
}
