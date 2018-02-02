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

namespace CuyZ\Notiz\Property\Factory;

use CuyZ\Notiz\Definition\Tree\EventGroup\Event\EventDefinition;
use CuyZ\Notiz\Event\Event;
use CuyZ\Notiz\Property\PropertyEntry;
use CuyZ\Notiz\Service\Traits\ExtendedSelfInstantiateTrait;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\Container\Container;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Factory for getting both properties definitions and values, that are defined
 * by events and used by notifications.
 */
class PropertyFactory implements SingletonInterface
{
    use ExtendedSelfInstantiateTrait;

    /**
     * @var PropertyDefinition[]
     */
    protected $propertyDefinition;

    /**
     * @var PropertyContainer[]
     */
    protected $propertyContainer;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var Container
     */
    protected $objectContainer;

    /**
     * @param ObjectManager $objectManager
     * @param Container $objectContainer
     */
    public function __construct(ObjectManager $objectManager, Container $objectContainer)
    {
        $this->objectManager = $objectManager;
        $this->objectContainer = $objectContainer;
    }

    /**
     * Return properties definition from a given event definition, entries have
     * not been processed by any event instance yet. This means all their data
     * can not be accessed yet (mainly their values, but also arbitrary data the
     * property can have).
     *
     * @param string $propertyClassName
     * @param EventDefinition $eventDefinition
     * @return PropertyEntry[]
     */
    public function getPropertiesDefinition($propertyClassName, EventDefinition $eventDefinition)
    {
        return $this->buildPropertyDefinition($propertyClassName, $eventDefinition)->getEntries();
    }

    /**
     * Returns a list of property entries that have been processed by the given
     * event. This means all their data can be accessed properly.
     *
     * Note that each property type for each event is processed only once,
     * memoization is used to serve the same properties later in a same run
     * time.
     *
     * @param string $propertyClassName
     * @param Event $event
     * @return PropertyEntry[]
     */
    public function getProperties($propertyClassName, Event $event)
    {
        $hash = spl_object_hash($event) . '::' . $propertyClassName;

        if (false === isset($this->propertyContainer[$hash])) {
            $definition = $this->buildPropertyDefinition($propertyClassName, $event->getDefinition());

            /** @var PropertyContainer $propertyContainer */
            $propertyContainer = GeneralUtility::makeInstance(PropertyContainer::class, $definition);

            $event->fillPropertyEntries($propertyContainer);

            $propertyContainer->freeze();

            $this->propertyContainer[$hash] = $propertyContainer;
        }

        return $this->propertyContainer[$hash]->getEntries();
    }

    /**
     * Builds a property definition for a given event definition.
     *
     * The definition is built only once for the given property type and event
     * definition, memoization is used to serve the same definition later in a
     * same run time.
     *
     * @param string $propertyClassName
     * @param EventDefinition $eventDefinition
     * @return PropertyDefinition
     */
    protected function buildPropertyDefinition($propertyClassName, EventDefinition $eventDefinition)
    {
        $propertyClassName = $this->objectContainer->getImplementationClassName($propertyClassName);

        $identifier = $eventDefinition->getClassName() . '::' . $propertyClassName;

        if (false === isset($this->propertyDefinition[$identifier])) {
            /** @var PropertyDefinition $propertyDefinition */
            $propertyDefinition = $this->objectManager->get(PropertyDefinition::class, $eventDefinition->getClassName(), $propertyClassName);

            /** @var Event $eventClassName */
            $eventClassName = $eventDefinition->getClassName();

            $eventClassName::buildPropertyDefinition($propertyDefinition);

            $this->propertyDefinition[$identifier] = $propertyDefinition;
        }

        return $this->propertyDefinition[$identifier];
    }
}
