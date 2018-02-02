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

namespace CuyZ\Notiz\View\Slot\Service;

use CuyZ\Notiz\Definition\DefinitionService;
use CuyZ\Notiz\Definition\Tree\EventGroup\Event\EventDefinition;
use CuyZ\Notiz\Service\Container;
use CuyZ\Notiz\Service\StringService;
use CuyZ\Notiz\View\Slot\SlotView;
use CuyZ\Notiz\View\ViewPathsAware;
use Generator;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException;

class SlotViewService implements SingletonInterface
{
    /**
     * @var DefinitionService
     */
    protected $definitionService;

    /**
     * @var StringService
     */
    protected $stringService;

    /**
     * @param DefinitionService $definitionService
     * @param StringService $stringService
     */
    public function __construct(DefinitionService $definitionService, StringService $stringService)
    {
        $this->definitionService = $definitionService;
        $this->stringService = $stringService;
    }

    /**
     * @param EventDefinition $eventDefinition
     * @param ViewPathsAware $viewPaths
     * @return SlotView
     */
    public function buildView(EventDefinition $eventDefinition, ViewPathsAware $viewPaths)
    {
        /** @var SlotView $view */
        $view = Container::get(SlotView::class, $eventDefinition);

        $view->setLayoutRootPaths($viewPaths->getLayoutRootPaths());
        $view->setTemplateRootPaths($viewPaths->getTemplateRootPaths());
        $view->setPartialRootPaths($viewPaths->getPartialRootPaths());

        try {
            $view->setTemplate($this->getEventTemplatePath($eventDefinition));
        } catch (InvalidTemplateResourceException $exception) {
            /**
             * @deprecated This try/catch block can be removed when TYPO3 v7 is
             *             not supported anymore.
             */
        }

        if (!$view->hasTemplate()) {
            $view->setTemplate('Default');
        }

        return $view;
    }

    /**
     * Yields every event from the definition, and their Fluid view that may
     * contain slots.
     *
     * @param ViewPathsAware $viewPaths
     * @return Generator
     */
    public function getEventsViews(ViewPathsAware $viewPaths)
    {
        if ($this->definitionService->getValidationResult()->hasErrors()) {
            return;
        }

        $definition = $this->definitionService->getDefinition();

        foreach ($definition->getEvents() as $event) {
            $view = $this->buildView($event, $viewPaths);

            yield $event => $view;
        }
    }

    /**
     * @param ViewPathsAware $viewPaths
     * @return Generator
     */
    public function getEventsWithSlots(ViewPathsAware $viewPaths)
    {
        foreach ($this->getEventsViews($viewPaths) as $event => $view) {
            /** @var SlotView $view */
            if (!empty($view->getSlots()->getList())) {
                yield $event => $view;
            }
        }
    }

    /**
     * @param ViewPathsAware $viewPaths
     * @return Generator
     */
    public function getEventsWithoutSlots(ViewPathsAware $viewPaths)
    {
        foreach ($this->getEventsViews($viewPaths) as $event => $view) {
            /** @var SlotView $view */
            if (empty($view->getSlots()->getList())) {
                yield $event => $view;
            }
        }
    }

    /**
     * Returns the calculated template path, based on the identifiers of both
     * the dispatched event and its group. The identifiers will be sanitized to
     * match the UpperCamelCase format.
     *
     * For instance, the template path for the event `myEvent` from the group
     * `my_company` will be located at `MyCompany/MyEvent.html`.
     *
     * @param EventDefinition $eventDefinition
     * @return string
     */
    protected function getEventTemplatePath(EventDefinition $eventDefinition)
    {
        $groupPath = $this->stringService->upperCamelCase($eventDefinition->getGroup()->getIdentifier());
        $eventPath = $this->stringService->upperCamelCase($eventDefinition->getIdentifier());

        return "$groupPath/$eventPath";
    }
}
