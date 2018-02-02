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

namespace CuyZ\Notiz\Definition\Builder\Component\Source;

use CuyZ\Notiz\Domain\Definition\Builder\Component\Source\TypoScriptDefinitionSource;

/**
 * A definition source will be used to fetch a definition array from any
 * origin: TypoScript, YAML or others.
 *
 * You will need to implement the `getDefinitionArray()` method and implement
 * your own fetching logic, that will return an array respecting the structure
 * of the definition.
 */
interface DefinitionSource
{
    const SOURCE_TYPOSCRIPT = TypoScriptDefinitionSource::class;

    /**
     * Returns a valid definition array from any origin.
     *
     * @return array
     */
    public function getDefinitionArray();
}
