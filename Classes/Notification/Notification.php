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

use CuyZ\Notiz\Definition\Tree\EventGroup\Event\EventDefinition;

/**
 * This interface must be implemented by notification classes that are
 * registered in the definition.
 */
interface Notification
{
    /**
     * Must return a processor class name that does extend the abstract class:
     *
     * @see \CuyZ\Notiz\Notification\Processor\NotificationProcessor
     *
     * @return string
     */
    public static function getProcessorClassName();

    /**
     * Must return a configuration array that will be used by the given event
     * during the dispatching with this notification.
     *
     * The configuration may be used by the event to change its behaviour: fill
     * properties, cancel the dispatching, or more.
     *
     * @param EventDefinition $eventDefinition
     * @return array
     */
    public function getEventConfiguration(EventDefinition $eventDefinition);
}
