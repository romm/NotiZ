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

namespace CuyZ\Notiz\Domain\Channel\Log;

use CuyZ\Notiz\Channel\AbstractChannel;
use CuyZ\Notiz\Domain\Notification\Log\Application\EntityLog\Service\EntityLogMessageBuilder;
use CuyZ\Notiz\Domain\Notification\Log\LogNotification;
use Psr\Log\LoggerInterface;

/**
 * This channel is meant to send logs using a PSR-3 logger.
 *
 * @link http://www.php-fig.org/psr/psr-3/
 *
 * To implement a custom logger, you need to extend this class and
 * implement `getLoggerInstance()`.
 */
abstract class LogChannel extends AbstractChannel
{
    /**
     * @var array
     */
    protected static $supportedNotifications = [
        LogNotification::class,
    ];

    /**
     * @var EntityLogMessageBuilder
     */
    protected $messageBuilder;

    /**
     * @var LogNotification
     */
    protected $notification;

    /**
     * Manual dependency injection.
     */
    final protected function initialize()
    {
        $this->messageBuilder = $this->objectManager->get(
            EntityLogMessageBuilder::class,
            $this->payload
        );

        $this->notification = $this->payload->getNotification();
    }

    /**
     * You must implement this method and return an instance of a PSR-3 logger.
     *
     * @return LoggerInterface
     */
    abstract protected function getLoggerInstance();

    /**
     * The logging itself is done here using the provided logger.
     */
    final protected function process()
    {
        $logger = $this->getLoggerInstance();

        $message = $this->messageBuilder->getMessage();
        $level = $this->notification->getLevel();

        $logger->log($level, $message);
    }
}
