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

namespace CuyZ\Notiz\Notification\Container;

use Generator;
use CuyZ\Notiz\Definition\Tree\EventGroup\Event\EventDefinition;
use CuyZ\Notiz\Definition\Tree\Notification\NotificationDefinition;
use CuyZ\Notiz\Exception\InvalidTypeException;

/**
 * This container is used as a proxy for the notification fetching: it will
 * check itself that the notification entries returned by a notification
 * processor are correct, without having to do it in the public API.
 */
class NotificationContainer
{
    /**
     * @var NotificationDefinition
     */
    protected $notificationDefinition;

    /**
     * @param NotificationDefinition $notificationDefinition
     */
    public function __construct(NotificationDefinition $notificationDefinition)
    {
        $this->notificationDefinition = $notificationDefinition;
    }

    /**
     * Fetches all notifications that are bound to the given event definition.
     *
     * @param EventDefinition $eventDefinition
     * @return Generator
     */
    public function fetchFromEventDefinition(EventDefinition $eventDefinition)
    {
        $processor = $this->notificationDefinition->getProcessor();
        $notifications = $processor->getNotificationsFromEventDefinition($eventDefinition);

        return $this->loop($notifications);
    }

    /**
     * Fetches the totality of notifications for the definition of this
     * container.
     *
     * @return Generator
     */
    public function fetchAll()
    {
        $processor = $this->notificationDefinition->getProcessor();
        $notifications = $processor->getAllNotifications();

        return $this->loop($notifications);
    }

    /**
     * Checks that the notifications that were fetched are all correct
     * instances.
     *
     * @param array $notifications
     * @return Generator
     *
     * @throws InvalidTypeException
     */
    protected function loop($notifications)
    {
        if (!is_array($notifications)) {
            throw InvalidTypeException::notificationContainerArrayInvalidType($notifications);
        }

        $notificationClassName = $this->notificationDefinition->getClassName();

        foreach ($notifications as $key => $notification) {
            if (!$notification instanceof $notificationClassName) {
                throw InvalidTypeException::notificationContainerEntryInvalidType($key, $notification, $this->notificationDefinition);
            }

            yield $notification;
        }
    }
}
