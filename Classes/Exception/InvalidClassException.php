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

namespace CuyZ\Notiz\Exception;

use CuyZ\Notiz\Channel\Settings\ChannelSettings;
use CuyZ\Notiz\Definition\Builder\Component\Processor\DefinitionProcessor;
use CuyZ\Notiz\Definition\Builder\Component\Source\DefinitionSource;
use CuyZ\Notiz\Event\Configuration\FlexForm\EventFlexFormProvider;
use CuyZ\Notiz\Event\Event;
use CuyZ\Notiz\Notification\Notification;
use CuyZ\Notiz\Notification\Processor\NotificationProcessor;
use CuyZ\Notiz\Notification\Settings\NotificationSettings;
use CuyZ\Notiz\Property\PropertyEntry;

class InvalidClassException extends NotizException
{
    const DEFINITION_SOURCE_MISSING_INTERFACE = 'The definition source class `%s` must implement the interface `%s`.';

    const DEFINITION_PROCESSOR_MISSING_INTERFACE = 'The definition processor class `%s` must implement the interface `%s`.';

    const EVENT_CLASS_MISSING_INTERFACE = 'The event class `%s` must implement the interface `%s`.';

    const TAG_SERVICE_PROPERTY_WRONG_PARENT = 'The given property class `%s` for identifier `%s` must extend the class `%s`.';

    const NOTIFICATION_MISSING_INTERFACE = 'The notification class `%s` must implement the interface `%s`.';

    const NOTIFICATION_PROCESSOR_WRONG_PARENT = 'The processor class of the notification `%s` must extend the class `%s` (given class is `%s`).';

    const NOTIFICATION_SETTINGS_MISSING_INTERFACE = 'The notification settings class `%s` must implement the interface `%s`.';

    const EVENT_CONFIGURATION_FLEX_FORM_PROVIDER_MISSING_INTERFACE = 'The FlexForm provider class `%s` must implement the interface `%s`.';

    const CHANNEL_SETTINGS_MISSING_INTERFACE = 'The channel settings class `%s` must implement the interface `%s`.';

    /**
     * @param string $className
     * @return static
     */
    public static function definitionSourceHasMissingInterface($className)
    {
        return self::makeNewInstance(
            self::DEFINITION_SOURCE_MISSING_INTERFACE,
            1503849499,
            [$className, DefinitionSource::class]
        );
    }

    /**
     * @param string $className
     * @return static
     */
    public static function definitionProcessorHasMissingInterface($className)
    {
        return self::makeNewInstance(
            self::DEFINITION_PROCESSOR_MISSING_INTERFACE,
            1503850131,
            [$className, DefinitionProcessor::class]
        );
    }

    /**
     * @param string $className
     * @return static
     */
    public static function eventHasMissingInterface($className)
    {
        return self::makeNewInstance(
            self::EVENT_CLASS_MISSING_INTERFACE,
            1503873348,
            [$className, Event::class]
        );
    }

    /**
     * @param string $propertyType
     * @param string $identifier
     * @return static
     */
    public static function tagServicePropertyWrongParent($propertyType, $identifier)
    {
        return self::makeNewInstance(
            self::TAG_SERVICE_PROPERTY_WRONG_PARENT,
            1504167339,
            [$propertyType, $identifier, PropertyEntry::class]
        );
    }

    /**
     * @param string $className
     * @return static
     */
    public static function notificationMissingInterface($className)
    {
        return self::makeNewInstance(
            self::NOTIFICATION_MISSING_INTERFACE,
            1505821532,
            [$className, Notification::class]
        );
    }

    /**
     * @param string $notificationClassName
     * @param string $processorClassName
     * @return static
     */
    public static function notificationProcessorWrongParent($notificationClassName, $processorClassName)
    {
        return self::makeNewInstance(
            self::NOTIFICATION_PROCESSOR_WRONG_PARENT,
            1505829694,
            [$notificationClassName, NotificationProcessor::class, $processorClassName]
        );
    }

    /**
     * @param string $className
     * @return static
     */
    public static function notificationSettingsMissingInterface($className)
    {
        return self::makeNewInstance(
            self::NOTIFICATION_SETTINGS_MISSING_INTERFACE,
            1506245423,
            [$className, NotificationSettings::class]
        );
    }

    /**
     * @param string $className
     * @return static
     */
    public static function eventConfigurationFlexFormProviderMissingInterface($className)
    {
        return self::makeNewInstance(
            self::EVENT_CONFIGURATION_FLEX_FORM_PROVIDER_MISSING_INTERFACE,
            1506952217,
            [$className, EventFlexFormProvider::class]
        );
    }

    /**
     * @param string $className
     * @return static
     */
    public static function channelSettingsMissingInterface($className)
    {
        return self::makeNewInstance(
            self::CHANNEL_SETTINGS_MISSING_INTERFACE,
            1507409177,
            [$className, ChannelSettings::class]
        );
    }
}
