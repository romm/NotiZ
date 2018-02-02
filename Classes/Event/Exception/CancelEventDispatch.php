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

namespace CuyZ\Notiz\Event\Exception;

use Exception;

/**
 * This exception can be thrown from the method `run()` of any event instance.
 *
 * It will cancel the dispatch of the very event and prevent the notifications
 * bound to this event to be fired.
 */
class CancelEventDispatch extends Exception
{
}
