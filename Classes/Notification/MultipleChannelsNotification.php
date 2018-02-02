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

namespace CuyZ\Notiz\Notification;

use CuyZ\Notiz\Definition\Tree\Notification\Channel\ChannelDefinition;

/**
 * This interface is used to identify notifications that are supported by
 * multiple channels.
 *
 * The shouldDispatch method is used to determine which channels to dispatch
 * the notification on.
 */
interface MultipleChannelsNotification
{
    /**
     * This method allows you to decide which channels this notification
     * should be dispatched on.
     *
     * It receives all compatible channels definitions one at a time and
     * must return true if you want the given channel to dispatch the
     * notification.
     *
     * @param ChannelDefinition $definition
     * @return bool
     */
    public function shouldDispatch(ChannelDefinition $definition);
}
