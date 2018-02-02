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

namespace CuyZ\Notiz\Definition\Tree\Notification\Channel\Settings;

use CuyZ\Notiz\Channel\Settings\ChannelSettings;
use Romm\ConfigurationObject\Service\Items\MixedTypes\MixedTypesInterface;
use Romm\ConfigurationObject\Service\Items\MixedTypes\MixedTypesResolver;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * Resolver for the property:
 *
 * @see \CuyZ\Notiz\Definition\Tree\Notification\Channel\ChannelDefinition::$settings
 */
class ChannelSettingsResolver implements SingletonInterface, MixedTypesInterface
{
    /**
     * Dynamically sets the settings class name of the channel.
     *
     * This class name was previously inserted by the method below:
     *
     * @see \CuyZ\Notiz\Definition\Tree\Notification\Channel\ChannelDefinition::fetchSettingsClassName
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

        return isset($data[ChannelSettings::SETTINGS_CLASS_NAME])
            ? $data[ChannelSettings::SETTINGS_CLASS_NAME]
            : ChannelSettings::TYPE_DEFAULT;
    }
}
