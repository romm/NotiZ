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

namespace CuyZ\Notiz\View\Slot;

use CuyZ\Notiz\Definition\DefinitionService;
use CuyZ\Notiz\Definition\Tree\EventGroup\Event\EventDefinition;
use CuyZ\Notiz\ViewHelpers\Slot\RenderViewHelper;
use CuyZ\Notiz\ViewHelpers\Slot\SlotViewHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

class SlotView extends StandaloneView
{
    /**
     * @var SlotContainer
     */
    protected $slots;

    /**
     * @var EventDefinition
     */
    protected $eventDefinition;

    /**
     * @param EventDefinition $eventDefinition
     */
    public function __construct(EventDefinition $eventDefinition)
    {
        parent::__construct();

        $this->eventDefinition = $eventDefinition;
    }

    /**
     * Will render the section `Slots` of the view. This will allow collecting
     * all the slots for the event, that are then returned.
     *
     * @return SlotContainer
     */
    public function getSlots()
    {
        if (!$this->slots) {
            $this->slots = GeneralUtility::makeInstance(SlotContainer::class);

            $this->baseRenderingContext
                ->getViewHelperVariableContainer()
                ->add(SlotViewHelper::class, SlotViewHelper::SLOT_CONTAINER, $this->slots);

            $variables = [
                'event' => $this->eventDefinition,
                'definition' => DefinitionService::get()->getDefinition(),
            ];

            if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '8.0.0', '>=')) {
                $this->renderSection('Slots', $variables, true);
            } else {
                /**
                 * @deprecated Must be removed when TYPO3 v7 is not supported anymore.
                 */
                $parsedTemplate = $this->templateParser->parse($this->getTemplateSource());

                $this->startRendering(self::RENDERING_TEMPLATE, $parsedTemplate, $this->baseRenderingContext);
                $this->renderSection('Slots', $variables, true);
                $this->stopRendering();
            }
        }

        return $this->slots;
    }

    /**
     * @param array $slotsValues
     * @param array $markers
     * @return string
     */
    public function renderWithSlots(array $slotsValues, array $markers)
    {
        $viewHelperVariableContainer = $this->baseRenderingContext->getViewHelperVariableContainer();

        $viewHelperVariableContainer->add(RenderViewHelper::class, RenderViewHelper::SLOT_CONTAINER, $this->getSlots());
        $viewHelperVariableContainer->add(RenderViewHelper::class, RenderViewHelper::SLOT_VALUES, $slotsValues);
        $viewHelperVariableContainer->add(RenderViewHelper::class, RenderViewHelper::MARKERS, $markers);

        return $this->render();
    }

    /**
     * @return EventDefinition
     */
    public function getEventDefinition()
    {
        return $this->eventDefinition;
    }
}
