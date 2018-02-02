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

/**
 * A channel is used to dispatch a notification that was triggered after an
 * event was fired by the application.
 *
 * For instance, a notification can be sent by mail, queued as a flash message,
 * saved to database, sent as SMS...
 */
interface Channel
{
    /**
     * This method will be called whenever an event has been fired and triggers
     * a notification dispatch.
     *
     * The payload parameter contains all needed information about the event and
     * the notification.
     *
     * @param Payload $payload
     * @return void
     */
    public function dispatch(Payload $payload);

    /**
     * Must return `false` if the given notification is not supported by this
     * channel.
     *
     * @param NotificationDefinition $notification
     * @return bool
     */
    public static function supportsNotification(NotificationDefinition $notification);

    /**
     * Must return a class name that implements:
     *
     * @see \CuyZ\Notiz\Channel\Settings\ChannelSettings
     *
     * @return string
     */
    public static function getSettingsClassName();
}
