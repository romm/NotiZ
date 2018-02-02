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

namespace CuyZ\Notiz\Definition\Tree\EventGroup\Event;

use CuyZ\Notiz\Definition\Tree\AbstractDefinitionComponent;
use CuyZ\Notiz\Definition\Tree\EventGroup\Event\Configuration\EventConfiguration;
use CuyZ\Notiz\Definition\Tree\EventGroup\Event\Connection\Connection;
use CuyZ\Notiz\Definition\Tree\EventGroup\EventGroup;
use CuyZ\Notiz\Property\Factory\PropertyFactory;
use CuyZ\Notiz\Property\PropertyEntry;
use CuyZ\Notiz\Service\LocalizationService;
use Romm\ConfigurationObject\Service\Items\DataPreProcessor\DataPreProcessor;
use Romm\ConfigurationObject\Service\Items\DataPreProcessor\DataPreProcessorInterface;
use Romm\ConfigurationObject\Service\Items\Parents\ParentsTrait;

class EventDefinition extends AbstractDefinitionComponent implements DataPreProcessorInterface
{
    use ParentsTrait;

    /**
     * @var string
     *
     * @validate NotEmpty
     */
    protected $identifier;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var string
     *
     * @validate Romm.ConfigurationObject:ClassImplements(interface=CuyZ\Notiz\Event\Event)
     */
    protected $className;

    /**
     * @var \CuyZ\Notiz\Definition\Tree\EventGroup\Event\Configuration\EventConfiguration
     */
    protected $configuration;

    /**
     * @var \CuyZ\Notiz\Definition\Tree\EventGroup\Event\Connection\Connection
     *
     * @validate NotEmpty
     *
     * @mixedTypesResolver \CuyZ\Notiz\Definition\Tree\EventGroup\Event\Connection\ConnectionResolver
     */
    protected $connection;

    /**
     * @param string $identifier
     * @param string $className
     */
    public function __construct($identifier, $className)
    {
        $this->identifier = $identifier;
        $this->className = $className;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Returns a full version of the identifier, containing both the event group
     * identifier and the event identifier, separated by a dot.
     *
     * @return string
     */
    public function getFullIdentifier()
    {
        return $this->getGroup()->getIdentifier() . '.' . $this->identifier;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return LocalizationService::localize($this->label);
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @return EventConfiguration
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @return Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @return EventGroup
     */
    public function getGroup()
    {
        /** @var EventGroup $eventGroup */
        $eventGroup = $this->getFirstParent(EventGroup::class);

        return $eventGroup;
    }

    /**
     * @param string $propertyClassName
     * @return PropertyEntry[]
     */
    public function getPropertiesDefinition($propertyClassName)
    {
        return PropertyFactory::get()->getPropertiesDefinition($propertyClassName, $this);
    }

    /**
     * @param DataPreProcessor $processor
     */
    public static function dataPreProcessor(DataPreProcessor $processor)
    {
        $data = $processor->getData();

        // Configuration must always be set.
        if (!is_array($data['configuration'])) {
            $data['configuration'] = [];
        }

        $processor->setData($data);
    }
}
