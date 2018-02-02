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

use CuyZ\Notiz\Channel\Channel;
use CuyZ\Notiz\Definition\Tree\Definition;
use CuyZ\Notiz\Definition\Tree\Notification\NotificationDefinition;

class InvalidTypeException extends NotizException
{
    const NOTIFICATION_CONTAINER_ARRAY_INVALID_TYPE = 'The notifications should be an array, the following type was fetched: `%s`.';

    const NOTIFICATION_CONTAINER_ENTRY_INVALID_TYPE = 'The notification at index `%s` is a `%s`, it should be an instance of `%s`.';

    const CHANNEL_SUPPORTED_NOTIFICATIONS_WRONG_TYPE = 'The channel property `$supportedNotifications` of the channel `%s` must be an array.';

    const CHANNEL_SUPPORTED_NOTIFICATIONS_INVALID_LIST_ENTRIES = 'The list of supported notifications for the channel `%s` is incorrect. The following values are not correct: `%s`.';

    const CHANNEL_UNSUPPORTED_NOTIFICATION_DISPATCHED = 'The channel `%s` can not dispatch notifications of type `%s`.';

    const DEFINITION_VALIDATION_WRONG_TYPE = 'The definition validator can only validate objects of type `%s`. Type of the given value was `%s`.';

    const EVENT_CONNECTION_WRONG_TYPE = 'The given value `%s` for `type` is not valid, please use one of the following values: `%s`.';

    /**
     * @param mixed $notifications
     * @return static
     */
    public static function notificationContainerArrayInvalidType($notifications)
    {
        return self::makeNewInstance(
            self::NOTIFICATION_CONTAINER_ARRAY_INVALID_TYPE,
            1506202628,
            [is_object($notifications) ? get_class($notifications) : gettype($notifications)]
        );
    }

    /**
     * @param string $key
     * @param mixed $notification
     * @param NotificationDefinition $notificationDefinition
     * @return static
     */
    public static function notificationContainerEntryInvalidType($key, $notification, NotificationDefinition $notificationDefinition)
    {
        return self::makeNewInstance(
            self::NOTIFICATION_CONTAINER_ENTRY_INVALID_TYPE,
            1506202861,
            [
                $key,
                is_object($notification) ? get_class($notification) : gettype($notification),
                $notificationDefinition->getClassName()
            ]
        );
    }

    /**
     * @param string $channelClassName
     * @return static
     */
    public static function channelSupportedNotificationsWrongType($channelClassName)
    {
        return self::makeNewInstance(
            self::CHANNEL_SUPPORTED_NOTIFICATIONS_WRONG_TYPE,
            1506446172,
            [$channelClassName]
        );
    }

    /**
     * @param string $channelClassName
     * @param array $invalidListEntries
     * @return static
     */
    public static function channelSupportedNotificationsInvalidListEntries($channelClassName, array $invalidListEntries)
    {
        return self::makeNewInstance(
            self::CHANNEL_SUPPORTED_NOTIFICATIONS_INVALID_LIST_ENTRIES,
            1506447318,
            [
                $channelClassName,
                implode('`, `', $invalidListEntries)
            ]
        );
    }

    /**
     * @param Channel                   $channel
     * @param NotificationDefinition $notification
     * @return static
     */
    public static function channelUnsupportedNotificationDispatched(Channel $channel, NotificationDefinition $notification)
    {
        return self::makeNewInstance(
            self::CHANNEL_UNSUPPORTED_NOTIFICATION_DISPATCHED,
            1506699884,
            [
                get_class($channel),
                $notification->getClassName()
            ]
        );
    }

    /**
     * @param mixed $value
     * @return static
     */
    public static function definitionValidationWrongType($value)
    {
        return self::makeNewInstance(
            self::DEFINITION_VALIDATION_WRONG_TYPE,
            1506449557,
            [
                Definition::class,
                is_object($value) ? get_class($value) : gettype($value),
            ]
        );
    }

    /**
     * @param string $type
     * @param array $allowedTypes
     * @return static
     */
    public static function eventConnectionWrongType($type, array $allowedTypes)
    {
        return self::makeNewInstance(
            self::EVENT_CONNECTION_WRONG_TYPE,
            1509630381,
            [
                $type,
                implode('`, `', $allowedTypes)
            ]
        );
    }
}
