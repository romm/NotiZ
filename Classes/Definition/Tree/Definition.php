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

namespace CuyZ\Notiz\Definition\Tree;

use CuyZ\Notiz\Definition\Tree\EventGroup\Event\EventDefinition;
use CuyZ\Notiz\Definition\Tree\EventGroup\EventGroup;
use CuyZ\Notiz\Definition\Tree\Notification\NotificationDefinition;
use CuyZ\Notiz\Exception\EntryNotFoundException;
use CuyZ\Notiz\Support\NotizConstants;
use Generator;
use Romm\ConfigurationObject\ConfigurationObjectInterface;
use Romm\ConfigurationObject\Service\Items\Cache\CacheService;
use Romm\ConfigurationObject\Service\Items\DataPreProcessor\DataPreProcessor;
use Romm\ConfigurationObject\Service\Items\DataPreProcessor\DataPreProcessorInterface;
use Romm\ConfigurationObject\Service\ServiceFactory;
use Romm\ConfigurationObject\Service\ServiceInterface;
use Romm\ConfigurationObject\Traits\ConfigurationObject\ArrayConversionTrait;

/**
 * Root object for the whole definition object tree.
 *
 * From this very object you can access to any definition value that you need.
 */
class Definition extends AbstractDefinitionComponent implements ConfigurationObjectInterface, DataPreProcessorInterface
{
    use ArrayConversionTrait;

    /**
     * @var \CuyZ\Notiz\Definition\Tree\EventGroup\EventGroup[]
     *
     * @validate NotEmpty
     */
    protected $eventGroups = [];

    /**
     * @var \CuyZ\Notiz\Definition\Tree\Notification\NotificationDefinition[]
     */
    protected $notifications = [];

    /**
     * @return EventGroup[]
     */
    public function getEventGroups()
    {
        return $this->eventGroups;
    }

    /**
     * @return Generator|EventDefinition[]
     */
    public function getEvents()
    {
        foreach ($this->eventGroups as $eventGroup) {
            foreach ($eventGroup->getEvents() as $event) {
                yield $eventGroup => $event;
            }
        }
    }

    /**
     * @param string $identifier
     * @return bool
     */
    public function hasEventGroup($identifier)
    {
        return true === isset($this->eventGroups[$identifier]);
    }

    /**
     * @param string $identifier
     * @return EventGroup
     *
     * @throws EntryNotFoundException
     */
    public function getEventGroup($identifier)
    {
        if (false === $this->hasEventGroup($identifier)) {
            throw EntryNotFoundException::definitionEventGroupNotFound($identifier);
        }

        return $this->eventGroups[$identifier];
    }

    /**
     * @return EventGroup
     */
    public function getFirstEventGroup()
    {
        return array_pop(array_reverse($this->getEventGroups()));
    }

    /**
     * @return NotificationDefinition[]
     */
    public function getNotifications()
    {
        return $this->notifications;
    }

    /**
     * @param $identifier
     * @return bool
     */
    public function hasNotification($identifier)
    {
        return true === isset($this->notifications[$identifier]);
    }

    /**
     * @param string $identifier
     * @return NotificationDefinition
     *
     * @throws EntryNotFoundException
     */
    public function getNotification($identifier)
    {
        if (false === $this->hasNotification($identifier)) {
            throw EntryNotFoundException::definitionNotificationNotFound($identifier);
        }

        return $this->notifications[$identifier];
    }

    /**
     * @inheritdoc
     */
    public static function getConfigurationObjectServices()
    {
        return ServiceFactory::getInstance()
            ->attach(ServiceInterface::SERVICE_CACHE)
            ->setOption(CacheService::OPTION_CACHE_NAME, NotizConstants::CACHE_KEY_DEFINITION_OBJECT)
            ->attach(ServiceInterface::SERVICE_PARENTS)
            ->attach(ServiceInterface::SERVICE_DATA_PRE_PROCESSOR)
            ->attach(ServiceInterface::SERVICE_MIXED_TYPES);
    }

    /**
     * Method called during the definition object construction: it allows
     * manipulating the data array before it is actually used to construct the
     * object.
     *
     * We use it to automatically fill the `identifier` property of the event
     * groups and notifications with the keys of the array.
     *
     * @param DataPreProcessor $processor
     */
    public static function dataPreProcessor(DataPreProcessor $processor)
    {
        self::forceIdentifierForProperty($processor, 'eventGroups');
        self::forceIdentifierForProperty($processor, 'notifications');
    }
}
