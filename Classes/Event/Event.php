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

namespace CuyZ\Notiz\Event;

use CuyZ\Notiz\Definition\Tree\EventGroup\Event\EventDefinition;
use CuyZ\Notiz\Property\Factory\PropertyContainer;
use CuyZ\Notiz\Property\Factory\PropertyDefinition;
use CuyZ\Notiz\Property\PropertyEntry;

/**
 * Interface for an event that will be processed when a hook/signal is called.
 *
 * The purpose of an event is to process the data coming from the hook/signal to
 * fill properties. These properties are then used by the notifications bound to
 * this very event, to dispatch it with correct information.
 *
 * An example of an event usage could be:
 *
 * Event:
 *  -> A user has registered.
 *
 * Notifications:
 *  -> #1 - A mail is sent to the user with registration information.
 *  -> #2 - A different mail is sent to an administrator to warn him.
 *  -> #3 - A flash message is queued to welcome the user on next page.
 *
 * In this example, the job of the event would be to fetch the user name and
 * email address, and fill the appropriate properties with these values so they
 * can be used by the three notifications.
 *
 * The user name will be inserted in the messages, the email address will allow
 * sending the email #1 to the user correctly.
 *
 * ---
 *
 * The event workflow has three phases:
 *
 * #1 - Property definition
 * ------------------------
 *
 * An event must implement the method `buildPropertyDefinition()` that will be
 * called to build a full definition of a property type that can be processed by
 * the event.
 *
 * The method is static, meaning that an event implementation should always know
 * in advance which data exactly can be handled during the event dispatch
 * process.
 *
 * The method will be called for every property type that can be asked by a
 * notification, so the code must be adapted to handle these several calls. See
 * the method documentation for more information:
 *
 * @see \CuyZ\Notiz\Event\Event::buildPropertyDefinition
 *
 * In order to be sure that an event can be dispatched by all notifications you
 * need to cover every case for all registered property types.
 *
 * #2 - Dispatch process
 * ---------------------
 *
 * When the actual signal/hook is sent from anywhere during the TYPO3 rendering
 * process, an event entry is created and its dispatch process begins.
 *
 * During this phase, the role of the event is to fetch all the useful data that
 * was passed by the signal/hook, and store it in any way as long as these data
 * can be accessed during the phase #3.
 *
 * For this, the event class should implement a method named `run()` that can
 * take as many parameters as needed (these parameters come from the hook/signal
 * that triggered the event).
 *
 * #3 - Properties filling
 * -----------------------
 *
 * Now that the data was saved during phase #2, the notifications bound to this
 * event will want to access to this data.
 *
 * @see \CuyZ\Notiz\Event\Event::fillPropertyEntries
 *
 * In this method, the properties that were added in the definitions during
 * phase #1 should be filled with correct values.
 */
interface Event
{
    /**
     * Builds a definition for a given property type that can be used by
     * notifications.
     *
     * Be aware that this method can be called several times, depending on what
     * type of property the notifications need.
     *
     * An example of implementation for this method can be:
     *
     * ```
     * public static function buildPropertyDefinition(PropertyDefinition $definition)
     * {
     *     switch ($definition->getPropertyType()) {
     *         case Marker::class:
     *             $entry = $definition->addEntry('user_name');
     *             $entry->setLabel('Registered user name');
     *             break;
     *         case Email::class:
     *             $entry = $definition->addEntry('user_email');
     *             $entry->setLabel('Registered user');
     *             break;
     *     }
     * }
     * ```
     *
     * @param PropertyDefinition $definition
     * @return void
     */
    public static function buildPropertyDefinition(PropertyDefinition $definition);

    /**
     * Method called to fill the values of the properties that were added during
     * the definition phase, so they can be used by notifications.
     *
     * @see \CuyZ\Notiz\Event\Event::buildPropertyDefinition
     *
     * The property container passed as a parameter contains the entries added
     * in the definition: each one should be filled with a value that was
     * fetched during the dispatch process of the event.
     *
     * Be aware that this method may be called multiple times, as a notification
     * may need several property types.
     *
     * An example of implementation for this method can be:
     *
     * ```
     * public function fillPropertyEntries(PropertyContainer $container)
     * {
     *     switch ($container->getPropertyType()) {
     *         case Marker::class:
     *             $container->getEntry('user_name')
     *                 ->setValue($this->userName);
     *             break;
     *         case Email::class:
     *             $container->getEntry('user_email')
     *                 ->setValue($this->userEmail);
     *             break;
     *     }
     * }
     * ```
     *
     * @param PropertyContainer $container
     * @return void
     */
    public function fillPropertyEntries(PropertyContainer $container);

    /**
     * Returns the property entries list for the given property type.
     *
     * @param string $propertyClassName
     * @return PropertyEntry[]
     */
    public function getProperties($propertyClassName);

    /**
     * Must return the definition for this event instance.
     *
     * Please note that event factory will give the definition instance as first
     * argument for the constructor of your event, you may use it to store it in
     * your object.
     *
     * @return EventDefinition
     */
    public function getDefinition();
}
