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

use CuyZ\Notiz\Exception\DuplicateEntryException;
use CuyZ\Notiz\View\Slot\Application\Slot;

class SlotContainer
{
    /**
     * @var Slot[]
     */
    protected $slots = [];

    /**
     * @param Slot $slot
     *
     * @throws DuplicateEntryException
     */
    public function add(Slot $slot)
    {
        if ($this->has($slot->getName())) {
            throw DuplicateEntryException::slotContainerDuplication($slot->getName());
        }

        $this->slots[$slot->getName()] = $slot;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function has($name)
    {
        return isset($this->slots[$name]);
    }

    /**
     * @return Slot[]
     */
    public function getList()
    {
        return $this->slots;
    }
}
