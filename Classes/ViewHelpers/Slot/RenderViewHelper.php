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

namespace CuyZ\Notiz\ViewHelpers\Slot;

use CuyZ\Notiz\Domain\Property\Marker;
use CuyZ\Notiz\Exception\DuplicateEntryException;
use CuyZ\Notiz\Exception\EntryNotFoundException;
use CuyZ\Notiz\Property\Service\MarkerParser;
use CuyZ\Notiz\View\Slot\SlotContainer;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

class RenderViewHelper extends AbstractViewHelper
{
    const SLOT_CONTAINER = 'SlotContainer';
    const SLOT_VALUES = 'SlotValues';
    const MARKERS = 'Marker';

    /**
     * @var MarkerParser
     */
    protected $markerParser;

    /**
     * @param MarkerParser $markerParser
     */
    public function __construct(MarkerParser $markerParser)
    {
        $this->markerParser = $markerParser;
    }

    /**
     * @inheritdoc
     */
    public function initializeArguments()
    {
        $this->registerArgument('name', 'string', 'Name of the slot that will be rendered.', true);
        $this->registerArgument('markers', 'array', 'Additional markers that will be added to the slot and can be used within the FlexForm.', false, []);
    }

    /**
     * @inheritdoc
     */
    public function render()
    {
        $result = '';
        $name = $this->arguments['name'];
        $newMarkers = $this->arguments['markers'];

        $slotContainer = $this->getSlotContainer();
        $slotValues = $this->getSlotValues();
        $markers = $this->getMarkers();

        if (!$slotContainer->has($name)) {
            throw EntryNotFoundException::slotNotFound($name);
        }

        foreach ($newMarkers as $key => $value) {
            if (isset($markers[$key])) {
                throw DuplicateEntryException::markerAlreadyDefined($key, $name);
            }
        }

        if (isset($slotValues[$name])) {
            foreach ($newMarkers as $key => $value) {
                $marker = new Marker($key);
                $marker->setValue($value);

                $markers[$key] = $marker;
            }

            $result = $this->markerParser->replaceMarkers(
                $slotValues[$name],
                $markers
            );
        }


        return $result;
    }

    /**
     * @return SlotContainer
     */
    protected function getSlotContainer()
    {
        return $this->viewHelperVariableContainer->get(__CLASS__, self::SLOT_CONTAINER);
    }

    /**
     * @return array
     */
    protected function getSlotValues()
    {
        return $this->viewHelperVariableContainer->get(__CLASS__, self::SLOT_VALUES);
    }

    /**
     * @return array
     */
    protected function getMarkers()
    {
        return $this->viewHelperVariableContainer->get(__CLASS__, self::MARKERS);
    }
}
