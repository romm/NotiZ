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

namespace CuyZ\Notiz\Backend\FormEngine\DataProvider;

use CuyZ\Notiz\Core\Definition\Tree\EventGroup\Event\EventDefinition;
use CuyZ\Notiz\Core\Notification\Settings\NotificationSettings;
use CuyZ\Notiz\Domain\Notification\Email\Application\EntityEmail\EntityEmailNotification;
use CuyZ\Notiz\Domain\Notification\Email\Application\EntityEmail\Settings\EntityEmailSettings;
use CuyZ\Notiz\Service\Container;
use CuyZ\Notiz\View\Slot\Service\SlotFlexFormService;
use CuyZ\Notiz\View\Slot\Service\SlotViewService;

/**
 * Build the TCA array for the dynamic body field of the mail.
 *
 * The body is a FlexForm field, where definition sheets are handled with
 * so-called "slot" that can be registered within the template of the mail.
 */
class BodySlotsProvider extends GracefulProvider
{
    const COLUMN = '__mailBody';

    /**
     * @var SlotViewService
     */
    private $slotViewService;

    /**
     * @var SlotFlexFormService
     */
    private $slotFlexFormService;

    /**
     * Manual dependency injection.
     */
    public function __construct()
    {
        parent::__construct();

        $this->slotViewService = Container::get(SlotViewService::class);
        $this->slotFlexFormService = Container::get(SlotFlexFormService::class);
    }

    /**
     * @param array $result
     * @return array
     */
    public function process(array $result)
    {
        $tableName = $result['tableName'];

        if (!isset($GLOBALS['TCA'][$tableName]['ctrl'][self::COLUMN])) {
            return $result;
        }

        $columnName = $GLOBALS['TCA'][$tableName]['ctrl'][self::COLUMN];

        if (!isset($GLOBALS['TCA'][$tableName]['columns'][$columnName])) {
            return $result;
        }

        $GLOBALS['TCA'][$tableName]['columns'][$columnName]['displayCond'] = $this->getMailBodyDisplayCond();
        $GLOBALS['TCA'][$tableName]['columns'][$columnName]['config']['ds'] = $this->getMailBodyFlexFormList();

        return $result;
    }

    /**
     * Builds a condition allowing the mail body to be shown only if the
     * selected events does provide slots for the Fluid template.
     *
     * By default, an event with no custom Fluid template does have a single
     * slot.
     *
     * @return array
     */
    private function getMailBodyDisplayCond()
    {
        $eventsWithoutSlots = [];
        $events = $this->slotViewService->getEventsWithoutSlots($this->getNotificationSettings()->getView());

        foreach ($events as $event => $view) {
            /** @var EventDefinition $event */
            $eventsWithoutSlots[] = $event->getFullIdentifier();
        }

        return [
            'AND' => [
                'FIELD:event:!IN:' . implode(',', $eventsWithoutSlots),
                'FIELD:event:!=:', // Hide the body when no event is selected.
            ],
        ];
    }

    /**
     * @return array
     */
    private function getMailBodyFlexFormList()
    {
        $viewSettings = $this->getNotificationSettings()->getView();

        return $this->slotFlexFormService->getNotificationFlexFormList($viewSettings);
    }

    /**
     * @return EntityEmailSettings|NotificationSettings
     */
    private function getNotificationSettings()
    {
        return $this->definitionService
            ->getDefinition()
            ->getNotification(EntityEmailNotification::getDefinitionIdentifier())
            ->getSettings();
    }
}
