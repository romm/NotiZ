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

namespace CuyZ\Notiz\Channel;

use CuyZ\Notiz\Definition\Tree\Notification\NotificationDefinition;
use CuyZ\Notiz\Event\Event;
use CuyZ\Notiz\Notification\Notification;
use CuyZ\Notiz\Notification\Processor\NotificationProcessor;
use CuyZ\Notiz\Notification\Processor\NotificationProcessorFactory;

/**
 * Object passed to a channel object when an event is fired and triggers a
 * notification dispatch.
 *
 * It gives access to:
 *
 * - The notification instance;
 * - The notification definition object;
 * - The notification processor;
 * - The event instance.
 */
class Payload
{
    /**
     * @var Notification
     */
    protected $notification;

    /**
     * @var NotificationDefinition
     */
    protected $notificationDefinition;

    /**
     * @var Event
     */
    protected $event;

    /**
     * @var NotificationProcessor
     */
    protected $notificationProcessor;

    /**
     * @param Notification $notification
     * @param NotificationDefinition $notificationDefinition
     * @param Event $event
     */
    public function __construct(Notification $notification, NotificationDefinition $notificationDefinition, Event $event)
    {
        $this->notification = $notification;
        $this->notificationDefinition = $notificationDefinition;
        $this->event = $event;
        $this->notificationProcessor = NotificationProcessorFactory::get()->getFromNotification($this->notification);
    }

    /**
     * @return Notification
     */
    public function getNotification()
    {
        return $this->notification;
    }

    /**
     * @return NotificationDefinition
     */
    public function getNotificationDefinition()
    {
        return $this->notificationDefinition;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return NotificationProcessor
     */
    public function getNotificationProcessor()
    {
        return $this->notificationProcessor;
    }
}
