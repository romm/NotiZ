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

namespace CuyZ\Notiz\Notification\Service;

use CuyZ\Notiz\Definition\DefinitionService;
use CuyZ\Notiz\Definition\Tree\Definition;
use CuyZ\Notiz\Definition\Tree\EventGroup\Event\EventDefinition;
use CuyZ\Notiz\Definition\Tree\Notification\NotificationDefinition;
use CuyZ\Notiz\Domain\Property\Marker;
use CuyZ\Notiz\Event\Service\EventFactory;
use CuyZ\Notiz\Exception\NotImplementedException;
use CuyZ\Notiz\Service\BackendUriBuilder;
use CuyZ\Notiz\Service\Container;
use CuyZ\Notiz\Service\LocalizationService;
use CuyZ\Notiz\Service\StringService;
use CuyZ\Notiz\Service\ViewService;
use CuyZ\Notiz\Support\NotizConstants;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * Utility service to ease TCA manipulation for TYPO3 notification records.
 */
class NotificationTcaService implements SingletonInterface
{
    /**
     * @var EventFactory
     */
    protected $eventFactory;

    /**
     * @var DefinitionService
     */
    protected $definitionService;

    /**
     * @var ViewService
     */
    protected $viewService;

    /**
     * @var BackendUriBuilder
     */
    protected $backendUriBuilder;

    /**
     * Manual dependency injection.
     */
    public function __construct()
    {
        $this->eventFactory = Container::get(EventFactory::class);
        $this->definitionService = Container::get(DefinitionService::class);
        $this->viewService = Container::get(ViewService::class);
        $this->backendUriBuilder = Container::get(BackendUriBuilder::class);
    }

    /**
     * @param array $parameters
     * @return bool
     */
    public function definitionContainsErrors(array $parameters)
    {
        $result = !$this->definitionHasErrors();

        if (in_array('inverted', $parameters['conditionParameters'])) {
            $result = !$result;
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        $view = $this->viewService->getStandaloneView('Backend/TCA/DefinitionErrorMessage');

        $view->assign('showDefinitionUri', $this->backendUriBuilder->uriFor('showDefinition'));
        $view->assign('result', $this->definitionService->getValidationResult());

        return $view->render();
    }

    /**
     * Loads all available events and stores them as an array to be used in the
     * TCA.
     *
     * @param array $parameters
     */
    public function getEventsList(array &$parameters)
    {
        if ($this->definitionHasErrors()) {
            return;
        }

        $eventGroups = $this->getDefinition()->getEventGroups();

        foreach ($eventGroups as $eventGroup) {
            $parameters['items'][] = [
                $eventGroup->getLabel(),
                '--div--',
            ];

            foreach ($eventGroup->getEvents() as $event) {
                $parameters['items'][] = [
                    $event->getLabel(),
                    $event->getFullIdentifier(),
                ];
            }
        }
    }

    /**
     * This function will fetch the event selected in the given notification.
     *
     * If the notification is new or if the event identifier is not found, the
     * first event from the first event group is returned.
     *
     * @param array $row
     * @return EventDefinition
     */
    protected function getSelectedEvent(array $row)
    {
        $definition = $this->getDefinition();

        // The first configured event is selected by default.
        $event = $definition->getFirstEventGroup()->getFirstEvent();

        // We check if the record already exists in the database...
        if (MathUtility::canBeInterpretedAsInteger($row['uid'])) {
            // @PHP7
            $eventValue = is_array($row['event'])
                ? $row['event'][0]
                : $row['event'];

            list($eventGroupIdentifier, $eventIdentifier) = GeneralUtility::trimExplode('.', $eventValue);

            if ($definition->hasEventGroup($eventGroupIdentifier)) {
                $eventGroup = $definition->getEventGroup($eventGroupIdentifier);

                if ($eventGroup->hasEvent($eventIdentifier)) {
                    $event = $eventGroup->getEvent($eventIdentifier);
                }
            }
        }

        return $event;
    }

    /**
     * Loads all markers for the current selected event and formats them as a
     * list to be displayed on the edit form.
     *
     * @param array $parameters
     * @return string
     */
    public function getMarkersLabel(array &$parameters)
    {
        if ($this->definitionHasErrors()) {
            return '';
        }

        $eventDefinition = $this->getSelectedEvent($parameters['row']);

        /** @var Marker[] $markers */
        $markers = $eventDefinition->getPropertiesDefinition(Marker::class);

        $output = '';

        foreach ($markers as $marker) {
            $label = StringService::mark($marker->getLabel());
            $output .= "<tr><td><strong>{$marker->getFormattedName()}</strong></td><td>$label</td></tr>";
        }

        $description = LocalizationService::localize('Notification/Entity/Fields:field.markers.description', [$eventDefinition->getLabel()]);

        return <<<HTML
<p>$description</p>

<table class="table table-striped table-hover">
    <tbody>
        $output
    </tbody>
</table>
HTML;
    }

    /**
     * Loads all available channels and stores them as an array to be used in
     * the TCA.
     *
     * @param array $parameters
     */
    public function getChannelsList(array &$parameters)
    {
        if ($this->definitionHasErrors()) {
            return;
        }

        foreach ($this->getNotificationDefinition()->getChannels() as $channelDefinition) {
            $label = $channelDefinition->getLabel();

            if (empty($label)) {
                $label = $channelDefinition->getIdentifier();
            }

            $parameters['items'][] = [$label, $channelDefinition->getClassName()];
        }
    }

    /**
     * @param array $array
     * @param $label
     */
    protected function appendOptionGroup(array &$array, $label)
    {
        array_unshift($array, ['label' => "––– $label –––", 'value' => '--div--']);
    }

    /**
     * @return string
     */
    public function getNotificationIconPath()
    {
        if ($this->definitionService->getValidationResult()->hasErrors()) {
            return NotizConstants::EXTENSION_ICON_DEFAULT;
        }

        return $this->getNotificationDefinition()->getIconPath();
    }

    /**
     * @return Definition
     */
    public function getDefinition()
    {
        return $this->definitionService->getDefinition();
    }

    /**
     * @return NotificationDefinition
     */
    protected function getNotificationDefinition()
    {
        return $this->getDefinition()->getNotification($this->getNotificationIdentifier());
    }

    /**
     * This method must return the current notification identifier to be used to
     * retrieve the current notification definition.
     *
     * @return string
     * @throws NotImplementedException
     */
    protected function getNotificationIdentifier()
    {
        throw NotImplementedException::tcaServiceNotificationIdentifierMissing(__METHOD__);
    }

    /**
     * @return bool
     */
    public function definitionHasErrors()
    {
        return $this->definitionService->getValidationResult()->hasErrors();
    }
}
