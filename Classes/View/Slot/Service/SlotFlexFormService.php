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

use CuyZ\Notiz\Definition\Tree\EventGroup\Event\EventDefinition;
use CuyZ\Notiz\Service\CacheService;
use CuyZ\Notiz\View\Slot\Application\Slot;
use CuyZ\Notiz\View\Slot\SlotView;
use CuyZ\Notiz\View\ViewPathsAware;
use TYPO3\CMS\Core\SingletonInterface;

class SlotFlexFormService implements SingletonInterface
{
    /**
     * @var SlotViewService
     */
    protected $slotViewService;

    /**
     * @var CacheService
     */
    protected $cacheService;

    /**
     * @param SlotViewService $slotViewService
     * @param CacheService $cacheService
     */
    public function __construct(SlotViewService $slotViewService, CacheService $cacheService)
    {
        $this->slotViewService = $slotViewService;
        $this->cacheService = $cacheService;
    }

    /**
     * Builds a list of FlexForms, based on the slots of every event.
     *
     * Events with no slots are ignored.
     *
     * @param ViewPathsAware $viewPaths
     * @return array
     */
    public function getNotificationFlexFormList(ViewPathsAware $viewPaths)
    {
        $flexFormList = ['default' => $this->getDefaultFlexForm()];

        foreach ($this->slotViewService->getEventsWithSlots($viewPaths) as $event => $view) {
            $flexForm = $this->getSlotViewFlexForm($view);

            /** @var EventDefinition $event */
            $flexFormList[$event->getFullIdentifier()] = $flexForm;
        }

        return $flexFormList;
    }

    /**
     * @param SlotView $view
     * @return string
     */
    public function getSlotViewFlexForm(SlotView $view)
    {
        $hash = $this->getViewCacheHash($view);

        if ($this->cacheService->has($hash)) {
            $flexForm = $this->cacheService->get($hash);
        } else {
            $flexForm = $this->buildSlotViewFlexForm($view);
            $this->cacheService->set($hash, $flexForm);
        }

        return $flexForm;
    }

    /**
     * Builds a complete FlexForm XML, based on the slots of the given event
     * view.
     *
     * For each slot, a text field will be added to the FlexForm.
     *
     * @param SlotView $view
     * @return string
     */
    protected function buildSlotViewFlexForm(SlotView $view)
    {
        $slots = $view->getSlots()->getList();

        if (empty($slots)) {
            return null;
        }

        $slotsFlexForm = '';

        foreach ($slots as $slot) {
            $slotsFlexForm .= $this->getSlot($slot);
        }

        $sheet = $this->getSheet('sDEF', $slotsFlexForm);

        return $this->getBase($sheet);
    }

    /**
     * @param string $sheets
     * @return string
     */
    protected function getBase($sheets)
    {
        return <<<XML
<T3DataStructure>
    <meta>
        <langDisable>1</langDisable>
    </meta>
    <sheets>
        $sheets
    </sheets>
</T3DataStructure>
XML;
    }

    /**
     * @param string $identifier
     * @param string $slots
     * @return string
     */
    protected function getSheet($identifier, $slots)
    {
        return <<<XML
<$identifier>
    <ROOT>
        <TCEforms>
            <sheetTitle>$identifier</sheetTitle>
        </TCEforms>
        <type>array</type>
        <el>
            $slots
        </el>
    </ROOT>
</$identifier>
XML;
    }

    /**
     * @param Slot $slot
     * @return string
     */
    protected function getSlot(Slot $slot)
    {
        $label = htmlspecialchars($slot->getLabel());

        return <<<XML
<{$slot->getName()}>
    <TCEforms>
        <label>$label</label>
        <config>
            {$slot->getFlexFormConfiguration()}
        </config>
    </TCEforms>
</{$slot->getName()}>
XML;
    }

    /**
     * @return string
     */
    protected function getDefaultFlexForm()
    {
        return <<<XML
<T3DataStructure>
    <meta>
        <langDisable>1</langDisable>
    </meta>
    <sheets>
        <sDEF>
            <ROOT>
                <TCEforms>
                    <sheetTitle>default</sheetTitle>
                </TCEforms>
                <type>array</type>
                <el>
                </el>
            </ROOT>
        </sDEF>
    </sheets>
</T3DataStructure>
XML;
    }

    /**
     * @param SlotView $view
     * @return string
     */
    protected function getViewCacheHash(SlotView $view)
    {
        return 'slots-' . sha1(serialize([
                $view->getTemplatePathAndFilename(),
                $view->getLayoutRootPaths(),
                $view->getPartialRootPaths(),
                $view->getEventDefinition()->getFullIdentifier()
            ]));
    }
}
