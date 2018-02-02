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

namespace CuyZ\Notiz\Notification\Processor;

use CuyZ\Notiz\Definition\Tree\EventGroup\Event\EventDefinition;
use CuyZ\Notiz\Notification\Notification;

/**
 * A notification processor will be used by services to perform actions or fetch
 * data related to a given notification type.
 *
 * Notification fetching
 * ---------------------
 *
 * The main goal of the processor is to fetch notification entries. You  need to
 * implement the following methods that must return the correct notifications:
 *
 * @see \CuyZ\Notiz\Notification\Processor\NotificationProcessor::getNotificationsFromEventDefinition
 * @see \CuyZ\Notiz\Notification\Processor\NotificationProcessor::getAllNotifications
 */
abstract class NotificationProcessor
{
    /**
     * @var string
     */
    protected $notificationClassName;

    /**
     * WARNING
     * -------
     *
     * If you need to override the constructor, do not forget to call:
     * `parent::__construct`
     *
     * @param string $notificationClassName
     */
    public function __construct($notificationClassName)
    {
        $this->notificationClassName = $notificationClassName;
    }

    /**
     * Returns the notification instances after a filter on the given event
     * definition has been applied.
     *
     * @param EventDefinition $eventDefinition
     * @return Notification[]
     */
    abstract public function getNotificationsFromEventDefinition(EventDefinition $eventDefinition);

    /**
     * Returns all notification instances.
     *
     * @return Notification[]
     */
    abstract public function getAllNotifications();

    /**
     * @return int
     */
    abstract public function getTotalNumber();
}
