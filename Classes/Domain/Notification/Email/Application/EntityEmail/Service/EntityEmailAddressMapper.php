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

namespace CuyZ\Notiz\Domain\Notification\Email\Application\EntityEmail\Service;

use CuyZ\Notiz\Channel\Payload;
use CuyZ\Notiz\Domain\Notification\Email\Application\EntityEmail\EntityEmailNotification;
use CuyZ\Notiz\Domain\Notification\Email\Application\EntityEmail\Settings\EntityEmailSettings;
use CuyZ\Notiz\Domain\Notification\Email\Application\EntityEmail\Settings\GlobalRecipients\Recipient;
use CuyZ\Notiz\Domain\Property\Email;
use CuyZ\Notiz\Service\StringService;

class EntityEmailAddressMapper
{
    /**
     * @var EntityEmailNotification
     */
    protected $notification;

    /**
     * @var EntityEmailSettings
     */
    protected $notificationSettings;

    /**
     * @var Email[]
     */
    protected $eventRecipients;

    /**
     * @var Recipient[]
     */
    protected $globalRecipients;

    /**
     * @param Payload $payload
     */
    public function __construct(Payload $payload)
    {
        $this->notification = $payload->getNotification();
        $this->notificationSettings = $payload->getNotificationDefinition()->getSettings();

        $this->eventRecipients = $payload->getEvent()->getProperties(Email::class);
        $this->globalRecipients = $this->notificationSettings->getGlobalRecipients()->getRecipients();
    }

    /**
     * Returns either the custom or the default sender.
     *
     * @return string
     */
    public function getSender()
    {
        return $this->notification->isSenderCustom()
            ? $this->notification->getSender()
            : $this->notificationSettings->getDefaultSender();
    }

    /**
     * Returns the list of both manual and provided "send to" values merged
     * together.
     *
     * @return array
     */
    public function getSendTo()
    {
        return $this->getMergedRecipients(
            $this->notification->getSendTo(),
            $this->notification->getSendToProvided()
        );
    }

    /**
     * Returns the list of both manual and provided "send cc" values merged
     * together.
     *
     * @return array
     */
    public function getSendCc()
    {
        return $this->getMergedRecipients(
            $this->notification->getSendCc(),
            $this->notification->getSendCcProvided()
        );
    }

    /**
     * Returns the list of both manual and provided "send bcc" values merged
     * together.
     *
     * @return array
     */
    public function getSendBcc()
    {
        return $this->getMergedRecipients(
            $this->notification->getSendBcc(),
            $this->notification->getSendBccProvided()
        );
    }

    /**
     * Returns an array of recipients merged and cleaned up.
     *
     * @param string $manual
     * @param string $provided
     * @return array
     */
    protected function getMergedRecipients($manual, $provided)
    {
        $manual = $this->recipientStringToArray($manual);
        $provided = $this->recipientStringToArray($provided);

        $provided = $this->mapRecipients($provided);

        $manual = $this->parseRecipientsStrings($manual);
        $provided = $this->parseRecipientsStrings($provided);

        $recipients = array_merge($manual, $provided);

        $recipients = $this->cleanupRecipients($recipients);

        return $this->prepareRecipientsForMailMessage($recipients);
    }

    /**
     * This methods takes a comma or semi-colon separated list of recipients and
     * returns it as an array.
     *
     * @param $recipients
     * @return array
     */
    protected function recipientStringToArray($recipients)
    {
        $recipients = trim($recipients);
        $recipients = str_replace(',', ';', $recipients);
        $recipients = explode(';', $recipients);

        return $recipients;
    }

    /**
     * This method takes an array of recipient strings and returns them as
     * formatted email list arrays.
     *
     * @see \CuyZ\Notiz\Service\StringService::formatEmailAddress
     *
     * @param array $recipients
     * @return array
     */
    protected function parseRecipientsStrings(array $recipients)
    {
        return array_map(
            [StringService::get(), 'formatEmailAddress'],
            $recipients
        );
    }

    /**
     * This method takes an array of recipient identifiers and returns the
     * desired mapped values.
     *
     * @param array $recipientsIdentifiers
     * @return array
     */
    protected function mapRecipients(array $recipientsIdentifiers)
    {
        $recipients = [];

        foreach ($this->eventRecipients as $recipient) {
            if (in_array($recipient->getName(), $recipientsIdentifiers)) {
                $recipients[] = $recipient->getValue();
            }
        }

        foreach ($this->globalRecipients as $recipient) {
            if (in_array($recipient->getIdentifier(), $recipientsIdentifiers)) {
                $recipients[] = $recipient->getRawValue();
            }
        }

        return $recipients;
    }

    /**
     * This method takes the final array of recipients and transforms it to be
     * used by the TYPO3 MailMessage class.
     *
     * The array returned will have this format:
     * ```
     * [
     *     'john.smith@example.com' => 'John Smith',
     *     'jane.smith@example.com'
     * ]
     * ```
     *
     * @param array $recipients
     * @return array
     */
    protected function prepareRecipientsForMailMessage(array $recipients)
    {
        $emails = [];

        foreach ($recipients as $recipient) {
            if (empty($recipient['name'])) {
                $emails[] = $recipient['email'];
            } else {
                $emails[$recipient['email']] = $recipient['name'];
            }
        }

        return $emails;
    }

    /**
     * This method cleans up the given array and removes non-unique values.
     *
     * @param array $recipients
     * @return array
     */
    protected function cleanupRecipients(array $recipients)
    {
        $uniqueRecipients = [];

        return array_filter(
            $recipients,
            function ($recipient) use ($uniqueRecipients) {
                $email = trim($recipient['email']);

                if (strlen($email) === 0) {
                    return false;
                }

                if (in_array($email, $uniqueRecipients)) {
                    return false;
                }

                $uniqueRecipients[] = $email;

                return true;
            }
        );
    }
}
