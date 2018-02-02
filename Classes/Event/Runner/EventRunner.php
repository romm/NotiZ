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

namespace CuyZ\Notiz\Event\Runner;

use Closure;
use Exception;
use CuyZ\Notiz\Definition\Tree\EventGroup\Event\EventDefinition;
use CuyZ\Notiz\Event\Event;
use CuyZ\Notiz\Event\Exception\CancelEventDispatch;
use CuyZ\Notiz\Event\Service\EventFactory;
use CuyZ\Notiz\Notification\Notification;
use CuyZ\Notiz\Notification\NotificationDispatcher;
use CuyZ\Notiz\Service\ExtensionConfigurationService;
use Throwable;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

/**
 * This class is used as a bridge between the trigger of an event and a
 * notification dispatch.
 */
class EventRunner
{
    const SIGNAL_EVENT_WAS_DISPATCHED = 'eventWasDispatched';

    const SIGNAL_EVENT_DISPATCH_ERROR = 'eventDispatchError';

    /**
     * @var EventDefinition
     */
    protected $eventDefinition;

    /**
     * @var Dispatcher
     */
    protected $signalDispatcher;

    /**
     * @var ExtensionConfigurationService
     */
    protected $extensionConfigurationService;

    /**
     * @var NotificationDispatcher
     */
    protected $notificationDispatcher;

    /**
     * @var EventFactory
     */
    protected $eventFactory;

    /**
     * @param EventDefinition $eventDefinition
     * @param EventFactory $eventFactory
     * @param NotificationDispatcher $notificationDispatcher
     * @param Dispatcher $signalDispatcher
     * @param ExtensionConfigurationService $extensionConfigurationService
     */
    public function __construct(
        EventDefinition $eventDefinition,
        EventFactory $eventFactory,
        NotificationDispatcher $notificationDispatcher,
        Dispatcher $signalDispatcher,
        ExtensionConfigurationService $extensionConfigurationService
    ) {
        $this->eventDefinition = $eventDefinition;
        $this->signalDispatcher = $signalDispatcher;
        $this->extensionConfigurationService = $extensionConfigurationService;
        $this->notificationDispatcher = $notificationDispatcher;
        $this->eventFactory = $eventFactory;
    }

    /**
     * @param mixed ...$arguments
     */
    public function process(...$arguments)
    {
        $notifications = $this->notificationDispatcher->fetchNotifications($this->eventDefinition);

        foreach ($notifications as $notification => $dispatchCallback) {
            $event = $this->eventFactory->create($this->eventDefinition, $notification);
            $doDispatch = true;

            if (is_callable([$event, 'run'])) {
                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $event->run(...$arguments);
                } catch (CancelEventDispatch $exception) {
                    $doDispatch = false;
                }
            }

            if ($doDispatch) {
                $this->dispatchEvent($dispatchCallback, $event, $notification);
            }

            unset($event);
        }
    }

    /**
     * Does the actual dispatch work. Two signals are sent:
     *
     * - When the dispatch ran well;
     * - If an error occurred during the dispatch.
     *
     * @see \CuyZ\Notiz\Event\Runner\EventRunner::SIGNAL_EVENT_WAS_DISPATCHED
     * @see \CuyZ\Notiz\Event\Runner\EventRunner::SIGNAL_EVENT_DISPATCH_ERROR
     *
     * @param Closure $callback
     * @param Event $event
     * @param Notification $notification
     *
     * @throws Throwable
     */
    protected function dispatchEvent(Closure $callback, Event $event, Notification $notification)
    {
        $exception = null;

        try {
            $callback($event);

            $this->signalDispatcher->dispatch(
                __CLASS__,
                self::SIGNAL_EVENT_WAS_DISPATCHED,
                [$event, $notification]
            );
        } catch (Throwable $exception) {
        } catch (Exception $exception) {
            // @PHP7
        }

        if ($exception) {
            $this->signalDispatcher->dispatch(
                __CLASS__,
                self::SIGNAL_EVENT_DISPATCH_ERROR,
                [$exception, $event, $notification]
            );

            $gracefulMode = $this->extensionConfigurationService->getConfigurationValue('dispatch.graceful_mode');

            if (!$gracefulMode) {
                throw $exception;
            }
        }
    }

    /**
     * @return callable
     */
    public function getCallable()
    {
        return [$this, 'process'];
    }

    /**
     * @return EventDefinition
     */
    public function getEventDefinition()
    {
        return $this->eventDefinition;
    }

    /**
     * This object should never be serialized, because it contains services that
     * can have properties filled with closures (a closure can't be serialized).
     *
     * We need to make sure it won't happen, because there are cases where TYPO3
     * can serialize this object, because the TYPO3 slot dispatcher contains a
     * reference to it; if a closure is registered, an exception is thrown.
     *
     * @return array
     */
    public function __sleep()
    {
        return [];
    }
}
