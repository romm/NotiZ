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

namespace CuyZ\Notiz\Channel;

use CuyZ\Notiz\Channel\Settings\ChannelSettings;
use CuyZ\Notiz\Definition\Tree\Notification\NotificationDefinition;
use CuyZ\Notiz\Exception\InvalidTypeException;
use CuyZ\Notiz\Notification\Notification;
use CuyZ\Notiz\Service\Container;
use CuyZ\Notiz\Service\ObjectService;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Reflection\ReflectionService;

/**
 * Default channel implementation provided by this extension, you can use it for
 * your own channels.
 *
 * It does inject all needed data in the class (from the given payload object).
 *
 * You can override the method `initialize()` to do your own stuff.
 *
 * You need to implement the method `process()` with the business logic of your
 * channel. It will be called whenever an event is fired.
 */
abstract class AbstractChannel implements Channel
{
    /**
     * Must contain a list of notification classes that can be handled by this
     * channel.
     *
     * An empty array means every notification can be handled.
     *
     * @var array
     */
    protected static $supportedNotifications = [];

    /**
     * @var bool
     */
    private static $supportedNotificationsWereChecked = false;

    /**
     * You can change the type of the settings in your child class.
     *
     * Please note that the class name must be fully written with its whole
     * namespace.
     *
     * @var \CuyZ\Notiz\Channel\Settings\EmptyChannelSettings
     */
    protected $settings;

    /**
     * @var Payload
     */
    protected $payload;

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
     * @param ChannelSettings $settings
     * @param ObjectManager $objectManager
     */
    public function __construct(ChannelSettings $settings, ObjectManager $objectManager)
    {
        $this->settings = $settings;
        $this->objectManager = $objectManager;
    }

    /**
     * Proxy method used to fill properties of this class to make them
     * accessible easily.
     *
     * An initialization method is also called to improve the code organization.
     *
     * @param Payload $payload
     *
     * @throws InvalidTypeException
     */
    final public function dispatch(Payload $payload)
    {
        if (!self::supportsNotification($payload->getNotificationDefinition())) {
            throw InvalidTypeException::channelUnsupportedNotificationDispatched($this, $payload->getNotificationDefinition());
        }

        $this->payload = $payload;

        $this->initialize();
        $this->process();
    }

    /**
     * You can override in your children classes to implement initialization
     * code.
     */
    protected function initialize()
    {
        // ...your code...
    }

    /**
     * The actual dispatch method that must be implemented by your class.
     *
     * It will be called when an event is fired and triggers a notification
     * dispatch.
     */
    abstract protected function process();

    /**
     * @param NotificationDefinition $notification
     * @return bool
     */
    public static function supportsNotification(NotificationDefinition $notification)
    {
        self::checkSupportedNotifications();

        if (empty(static::$supportedNotifications)) {
            return true;
        }

        $notificationClassName = $notification->getClassName();

        foreach (static::$supportedNotifications as $supportedNotification) {
            if (ObjectService::classInstanceOf($notificationClassName, $supportedNotification)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks that the list of supported notifications is an array containing
     * valid values.
     *
     * @throws InvalidTypeException
     */
    private static function checkSupportedNotifications()
    {
        if (self::$supportedNotificationsWereChecked) {
            return;
        }

        self::$supportedNotificationsWereChecked = true;

        if (!is_array(static::$supportedNotifications)) {
            throw InvalidTypeException::channelSupportedNotificationsWrongType(static::class);
        }

        $wrongNotificationClassNames = array_filter(
            static::$supportedNotifications,
            function ($supportedNotification) {
                return !(class_exists($supportedNotification)
                        || interface_exists($supportedNotification)
                    ) || !in_array(Notification::class, class_implements($supportedNotification));
            }
        );

        if (!empty($wrongNotificationClassNames)) {
            throw InvalidTypeException::channelSupportedNotificationsInvalidListEntries(static::class, $wrongNotificationClassNames);
        }
    }

    /**
     * You may change the type of the settings property of this class to use a
     * custom setting class.
     *
     * @return string
     */
    public static function getSettingsClassName()
    {
        /** @var ReflectionService $reflectionService */
        $reflectionService = Container::get(ReflectionService::class);

        $settingsProperty = $reflectionService->getClassSchema(static::class)->getProperty('settings');
        $settingsClassName = $settingsProperty['type'];

        return $settingsClassName;
    }
}
