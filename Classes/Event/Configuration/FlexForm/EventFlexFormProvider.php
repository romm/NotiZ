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

namespace CuyZ\Notiz\Event\Configuration\FlexForm;

/**
 * An event FlexForm provider is responsible from returning the FlexForm
 * configuration for its bound event.
 *
 * This will mainly be used by notifications TCA to be able to easily display an
 * event configuration in the TYPO3 backend. This configuration is then turned
 * to an array and can be used during the event dispatching.
 */
interface EventFlexFormProvider
{
    /**
     * Must return a valid FlexForm value.
     *
     * The "file" syntax is accepted: `FILE:EXT:my_ext/.../MyFlexForm.xml`
     *
     * @return string
     */
    public function getFlexFormValue();

    /**
     * @return bool
     */
    public function hasFlexForm();
}
