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

namespace CuyZ\Notiz\Definition\Builder\Component\Processor;

use CuyZ\Notiz\Definition\Tree\Definition;

/**
 * A definition processor will be used during the build of the definition
 * object, to modify it before being finalized.
 *
 * You will need to implement the `process()` method to manipulate the
 * definition object.
 */
interface DefinitionProcessor
{
    /**
     * Modify the definition object anyway you need to.
     *
     * @param Definition $definition
     * @return void
     */
    public function process(Definition $definition);
}
