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

namespace CuyZ\Notiz\Domain\Channel\Email\TYPO3;

use CuyZ\Notiz\Channel\AbstractChannel;
use CuyZ\Notiz\Domain\Notification\Email\Application\EntityEmail\Service\EntityEmailAddressMapper;
use CuyZ\Notiz\Domain\Notification\Email\Application\EntityEmail\Service\EntityEmailTemplateBuilder;
use CuyZ\Notiz\Domain\Notification\Email\EmailNotification;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Channel using the default `MailMessage` provided by the TYPO3 core.
 */
class EmailChannel extends AbstractChannel
{
    /**
     * @var array
     */
    protected static $supportedNotifications = [
        EmailNotification::class,
    ];

    /**
     * @var EntityEmailTemplateBuilder
     */
    protected $templateBuilder;

    /**
     * @var EntityEmailAddressMapper
     */
    protected $addressMapper;

    /**
     * Setting up services used by this channel.
     */
    protected function initialize()
    {
        $this->templateBuilder = $this->objectManager->get(EntityEmailTemplateBuilder::class, $this->payload);
        $this->addressMapper = $this->objectManager->get(EntityEmailAddressMapper::class, $this->payload);
    }

    /**
     * Sends the mail with processed recipients and subject/body.
     */
    protected function process()
    {
        /** @var MailMessage $mailMessage */
        $mailMessage = GeneralUtility::makeInstance(MailMessage::class);

        $mailMessage
            ->setSubject($this->templateBuilder->getSubject())
            ->setBody($this->templateBuilder->getBody())
            ->setFrom($this->addressMapper->getSender())
            ->setTo($this->addressMapper->getSendTo())
            ->setCc($this->addressMapper->getSendCc())
            ->setBcc($this->addressMapper->getSendBcc())
            ->setContentType('text/html');

        $mailMessage->send();
    }
}
