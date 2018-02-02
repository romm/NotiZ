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

namespace CuyZ\Notiz\Domain\Notification\Email;

use CuyZ\Notiz\Notification\Notification;

interface EmailNotification extends Notification
{
    /**
     * @return string
     */
    public function getSender();

    /**
     * @return string
     */
    public function getSendTo();

    /**
     * @return string
     */
    public function getSendCc();

    /**
     * @return string
     */
    public function getSendBcc();

    /**
     * @return string
     */
    public function getSubject();

    /**
     * @return string
     */
    public function getBody();
}
