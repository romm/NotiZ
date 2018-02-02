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

use CuyZ\Notiz\Definition\Tree\Definition;
use CuyZ\Notiz\Definition\Tree\EventGroup\EventGroup;
use CuyZ\Notiz\Domain\Notification\Email\Application\EntityEmail\Settings\View\Layout;

class EntryNotFoundException extends NotizException
{
    const DEFINITION_SOURCE_NOT_FOUND = 'The definition source `%s` was not registered yet.';

    const DEFINITION_PROCESSOR_NOT_FOUND = 'The definition processor `%s` was not registered yet.';

    const DEFINITION_EVENT_GROUP_NOT_FOUND = 'The event group `%s` was not found, please use method `%s::hasEventGroup()`.';

    const DEFINITION_EVENT_NOT_FOUND = 'The event `%s` was not found, please use method `%s::hasEvent()`.';

    const DEFINITION_NOTIFICATION_NOT_FOUND = 'The notification `%s` was not found, please use method `%s::hasNotification()`.';

    const ENTITY_EMAIL_VIEW_LAYOUT_NOT_FOUND = 'The view layout `%s` was not found, please use method `%s::hasLayout()`.';

    const PROPERTY_ENTRY_NOT_FOUND = 'The property `%s` for the event `%s` does not have an entry `%s`, please use method `%s::hasEntry()`.';

    const EXTENSION_CONFIGURATION_ENTRY_NOT_FOUND = 'The entry `%s` was not found in the extension configuration.';

    const EVENT_RUNNER_ENTRY_NOT_FOUND = 'The runner entry `%s` was not found, please use method `%s::has()`.';

    const EVENT_CONNECTION_TYPE_MISSING = 'The property `type` must be filled with one of these values: `%s`.';

    const SLOT_NOT_FOUND = 'The slot named `%s` was not found, please register it in the `Slots` section of your template.';

    /**
     * @param string $identifier
     * @return static
     */
    public static function definitionSourceNotFound($identifier)
    {
        return self::makeNewInstance(
            self::DEFINITION_SOURCE_NOT_FOUND,
            1503849730,
            [$identifier]
        );
    }

    /**
     * @param string $identifier
     * @return static
     */
    public static function definitionProcessorNotFound($identifier)
    {
        return self::makeNewInstance(
            self::DEFINITION_PROCESSOR_NOT_FOUND,
            1503850164,
            [$identifier]
        );
    }

    /**
     * @param string $identifier
     * @return static
     */
    public static function definitionEventGroupNotFound($identifier)
    {
        return self::makeNewInstance(
            self::DEFINITION_EVENT_GROUP_NOT_FOUND,
            1503851646,
            [$identifier, Definition::class]
        );
    }

    /**
     * @param string $identifier
     * @return static
     */
    public static function definitionEventNotFound($identifier)
    {
        return self::makeNewInstance(
            self::DEFINITION_EVENT_NOT_FOUND,
            1503851804,
            [$identifier, EventGroup::class]
        );
    }

    /**
     * @param string $identifier
     * @return static
     */
    public static function definitionNotificationNotFound($identifier)
    {
        return self::makeNewInstance(
            self::DEFINITION_NOTIFICATION_NOT_FOUND,
            1510506078,
            [$identifier, Definition::class]
        );
    }

    /**
     * @param string $identifier
     * @return static
     */
    public static function entityEmailViewLayoutNotFound($identifier)
    {
        return self::makeNewInstance(
            self::ENTITY_EMAIL_VIEW_LAYOUT_NOT_FOUND,
            1503851908,
            [$identifier, Layout::class]
        );
    }

    /**
     * @param string $name
     * @param string $eventClassName
     * @param string $propertyType
     * @param object $object
     * @return static
     */
    public static function propertyEntryNotFound($name, $eventClassName, $propertyType, $object)
    {
        return self::makeNewInstance(
            self::PROPERTY_ENTRY_NOT_FOUND,
            1504104832,
            [$propertyType, $eventClassName, $name, get_class($object)]
        );
    }

    /**
     * @param string $key
     * @return static
     */
    public static function extensionConfigurationEntryNotFound($key)
    {
        return self::makeNewInstance(
            self::EXTENSION_CONFIGURATION_ENTRY_NOT_FOUND,
            1506239859,
            [$key]
        );
    }

    /**
     * @param string $key
     * @return static
     */
    public static function eventRunnerEntryNotFound($key)
    {
        return self::makeNewInstance(
            self::EVENT_RUNNER_ENTRY_NOT_FOUND,
            1506246269,
            [$key]
        );
    }

    /**
     * @param array $allowedTypes
     * @return static
     */
    public static function eventConnectionTypeMissing(array $allowedTypes)
    {
        return self::makeNewInstance(
            self::EVENT_CONNECTION_TYPE_MISSING,
            1509630193,
            [implode('`, `', $allowedTypes)]
        );
    }

    /**
     * @param string $name
     * @return static
     */
    public static function slotNotFound($name)
    {
        return self::makeNewInstance(
            self::SLOT_NOT_FOUND,
            1517410382,
            [$name]
        );
    }
}
