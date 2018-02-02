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

namespace CuyZ\Notiz\Domain\Notification;

use CuyZ\Notiz\Definition\Tree\EventGroup\Event\EventDefinition;
use CuyZ\Notiz\Definition\Tree\Notification\Channel\ChannelDefinition;
use CuyZ\Notiz\Notification\MultipleChannelsNotification;
use CuyZ\Notiz\Notification\Notification;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Service\FlexFormService;

abstract class EntityNotification extends AbstractEntity implements Notification, MultipleChannelsNotification
{
    /**
     * @var string
     */
    protected $channel;

    /**
     * @var string
     */
    protected $eventConfigurationFlex;

    /**
     * @var array
     */
    protected $eventConfiguration;

    /**
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @param string $channel
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;
    }

    /**
     * @param string $eventConfigurationFlex
     */
    public function setEventConfigurationFlex($eventConfigurationFlex)
    {
        $this->eventConfigurationFlex = $eventConfigurationFlex;
    }

    /**
     * Returns the event configuration stored as a FlexForm string.
     *
     * @param EventDefinition $eventDefinition
     * @return array
     */
    public function getEventConfiguration(EventDefinition $eventDefinition)
    {
        if (null === $this->eventConfiguration) {
            /** @var FlexFormService $flexFormService */
            $flexFormService = GeneralUtility::makeInstance(FlexFormService::class);

            $this->eventConfiguration = $flexFormService->convertFlexFormContentToArray($this->eventConfigurationFlex);
        }

        return $this->eventConfiguration;
    }

    /**
     * The selected channel is stored in the `$channel` property.
     *
     * @inheritdoc
     */
    public function shouldDispatch(ChannelDefinition $definition)
    {
        return $definition->getClassName() === $this->getChannel();
    }
}
