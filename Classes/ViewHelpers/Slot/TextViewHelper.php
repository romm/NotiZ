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

use CuyZ\Notiz\View\Slot\Application\Slot;
use CuyZ\Notiz\View\Slot\Application\TextSlot;

class TextViewHelper extends SlotViewHelper
{
    /**
     * @return Slot
     */
    protected function getSlot()
    {
        return new TextSlot(
            $this->getSlotName(),
            $this->getSlotLabel()
        );
    }
}
