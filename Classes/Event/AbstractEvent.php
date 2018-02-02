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
use CuyZ\Notiz\Event\Exception\CancelEventDispatch;
use CuyZ\Notiz\Property\Factory\PropertyContainer;
use CuyZ\Notiz\Property\Factory\PropertyDefinition;
use CuyZ\Notiz\Property\Factory\PropertyFactory;
use CuyZ\Notiz\Property\PropertyEntry;
use CuyZ\Notiz\Property\Service\TagsPropertyService;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Default event implementation provided by this extension, you can use it for
 * your own events.
 *
 * It does implement all methods needed by the interface, so you wont have to do
 * the work yourself, unless you want to override something.
 *
 * You may still implement your own method named `run()` where you can process
 * the logic of the event (usually used to fill the properties of the class).
 *
 * Tag property service
 * --------------------
 *
 * By default, the property definitions are handled by the tag property service,
 * that will analyze your event class attributes and their annotations to
 * automatically fill the definitions.
 *
 * @see \CuyZ\Notiz\Property\Service\TagsPropertyService
 *
 * The only thing you need to do is add the correct annotations on the class
 * attributes you want to use, and fill them with the correct values during the
 * dispatch process.
 *
 * Example:
 *
 * ```
 * /**
 *  * @var string
 *  *
 *  * @marker
 *  * /
 * protected $userName;
 *
 * public function run(UserObject $userObject)
 * {
 *     $this->userName = $userObject->getName();
 * }
 * ```
 *
 * Cancel dispatch
 * ---------------
 *
 * In the implementation of the method `run()` you can call the method
 * `cancelDispatch()` whenever you need to. This will cancel the dispatch of the
 * event and prevent any notification bound to this event from being fired.
 */
abstract class AbstractEvent implements Event
{
    /**
     * @var EventDefinition
     */
    protected $eventDefinition;

    /**
     * @var array
     */
    protected $configuration = [];

    /**
     * @var PropertyFactory
     */
    protected $propertyFactory;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * WARNING
     * -------
     *
     * If you need to override the constructor, do not forget to call:
     * `parent::__construct`
     *
     * @param EventDefinition $eventDefinition
     * @param array $configuration
     * @param PropertyFactory $propertyFactory
     * @param ObjectManager $objectManager
     */
    public function __construct(EventDefinition $eventDefinition, array $configuration, PropertyFactory $propertyFactory, ObjectManager $objectManager)
    {
        $this->eventDefinition = $eventDefinition;
        $this->configuration = $configuration;
        $this->propertyFactory = $propertyFactory;
        $this->objectManager = $objectManager;
    }

    /**
     * This method can be called from the method `run()` to cancel the dispatch
     * of the event.
     *
     * You may call this under certain conditions of your own if they need the
     * event not to be dispatched, preventing the notifications bound to this
     * event to be fired.
     *
     * @throws CancelEventDispatch
     */
    protected function cancelDispatch()
    {
        throw new CancelEventDispatch;
    }

    /**
     * See class description for more information.
     *
     * @param PropertyDefinition $definition
     */
    public static function buildPropertyDefinition(PropertyDefinition $definition)
    {
        TagsPropertyService::get()->fillPropertyDefinition($definition);
    }

    /**
     * Fills the property container with the values from the class attributes.
     *
     * @param PropertyContainer $container
     */
    public function fillPropertyEntries(PropertyContainer $container)
    {
        foreach ($container->getEntries() as $property) {
            $name = $property->getName();

            if (property_exists($this, $name)) {
                $property->setValue($this->$name);
            }
        }
    }

    /**
     * @param string $propertyClassName
     * @return PropertyEntry[]
     */
    public function getProperties($propertyClassName)
    {
        return $this->propertyFactory->getProperties($propertyClassName, $this);
    }

    /**
     * @return EventDefinition
     */
    public function getDefinition()
    {
        return $this->eventDefinition;
    }
}
