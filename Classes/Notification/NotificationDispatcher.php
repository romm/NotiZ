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

use Closure;
use Generator;
use CuyZ\Notiz\Channel\Payload;
use CuyZ\Notiz\Channel\Service\ChannelFactory;
use CuyZ\Notiz\Definition\DefinitionService;
use CuyZ\Notiz\Definition\Tree\Definition;
use CuyZ\Notiz\Definition\Tree\EventGroup\Event\EventDefinition;
use CuyZ\Notiz\Definition\Tree\Notification\Channel\ChannelDefinition;
use CuyZ\Notiz\Definition\Tree\Notification\NotificationDefinition;
use CuyZ\Notiz\Event\Event;
use CuyZ\Notiz\Notification\Container\NotificationContainer;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class NotificationDispatcher implements SingletonInterface
{
    /**
     * @var Definition
     */
    protected $definition;

    /**
     * @var ChannelFactory
     */
    protected $channelFactory;

    /**
     * @param DefinitionService $definitionService
     * @param ChannelFactory $channelFactory
     */
    public function __construct(DefinitionService $definitionService, ChannelFactory $channelFactory)
    {
        $this->definition = $definitionService->getDefinition();
        $this->channelFactory = $channelFactory;
    }

    /**
     * Fetches all notifications bound to the given event to dispatch them in
     * every registered channel.
     *
     * @param EventDefinition $eventDefinition
     * @return Generator
     */
    public function fetchNotifications(EventDefinition $eventDefinition)
    {
        $notificationTypes = $this->definition->getNotifications();

        // Looping on all existing notification types...
        foreach ($notificationTypes as $notificationDefinition) {
            // ...to filter the ones bound to this specific event definition.
            $notifications = $this->getNotificationContainer($notificationDefinition)->fetchFromEventDefinition($eventDefinition);

            foreach ($notifications as $notification) {
                yield $notification => $this->getDispatchCallback($notification, $notificationDefinition);
            }
        }
    }

    /**
     * @param Notification $notification
     * @param NotificationDefinition $notificationDefinition
     * @return Closure
     */
    protected function getDispatchCallback(Notification $notification, NotificationDefinition $notificationDefinition)
    {
        return function (Event $event) use ($notification, $notificationDefinition) {
            /** @var Payload $payload */
            $payload = GeneralUtility::makeInstance(Payload::class, $notification, $notificationDefinition, $event);

            $channels = $this->getChannels($notification, $notificationDefinition);

            foreach ($channels as $channelDefinition) {
                $channel = $this->channelFactory->create($channelDefinition);

                $channel->dispatch($payload);

                unset($channel);
            }

            unset($payload);
        };
    }

    /**
     * @param NotificationDefinition $notificationDefinition
     * @return NotificationContainer
     */
    protected function getNotificationContainer(NotificationDefinition $notificationDefinition)
    {
        /** @var NotificationContainer $notificationContainer */
        $notificationContainer = GeneralUtility::makeInstance(NotificationContainer::class, $notificationDefinition);

        return $notificationContainer;
    }

    /**
     * This method is responsible for returning the list of channels to dispatch
     * for the given notification (and its definition).
     *
     * @param Notification $notification
     * @param NotificationDefinition $definition
     * @return ChannelDefinition[]
     */
    protected function getChannels(Notification $notification, NotificationDefinition $definition)
    {
        if ($notification instanceof MultipleChannelsNotification) {
            /*
             * If a notification is supported by several channels, it can choose
             * which ones to dispatch.
             */
            $channels = array_filter(
                $definition->getChannels(),
                [$notification, 'shouldDispatch']
            );
        } else {
            $channels = $definition->getChannels();
        }

        return $channels;
    }
}
