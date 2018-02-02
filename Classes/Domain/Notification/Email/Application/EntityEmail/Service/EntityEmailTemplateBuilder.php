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
use CuyZ\Notiz\Domain\Property\Marker;
use CuyZ\Notiz\Event\Event;
use CuyZ\Notiz\Property\Service\MarkerParser;
use CuyZ\Notiz\View\Slot\Service\SlotViewService;

class EntityEmailTemplateBuilder
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
     * @var Event
     */
    protected $event;

    /**
     * @var MarkerParser
     */
    protected $markerParser;

    /**
     * @var Marker[]
     */
    protected $markers = [];

    /**
     * @var SlotViewService
     */
    protected $slotViewService;

    /**
     * @param Payload $payload
     * @param MarkerParser $markerParser
     * @param SlotViewService $slotViewService
     */
    public function __construct(Payload $payload, MarkerParser $markerParser, SlotViewService $slotViewService)
    {
        $this->notification = $payload->getNotification();
        $this->notificationSettings = $payload->getNotificationDefinition()->getSettings();

        $this->event = $payload->getEvent();

        $this->markerParser = $markerParser;
        $this->markers = $payload->getEvent()->getProperties(Marker::class);

        $this->slotViewService = $slotViewService;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->markerParser->replaceMarkers(
            $this->notification->getSubject(),
            $this->markers
        );
    }

    /**
     * @return string
     */
    public function getBody()
    {
        $eventDefinition = $this->event->getDefinition();
        $viewSettings = $this->notificationSettings->getView();

        $view = $this->slotViewService->buildView($eventDefinition, $viewSettings);

        $layout = $viewSettings->getLayout($this->notification->getLayout());

        $view->assign('layout', $layout->getPath());
        $view->assign('markers', $this->markers);

        return $view->renderWithSlots($this->notification->getBodySlots(), $this->markers);
    }
}
