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

namespace CuyZ\Notiz\Exception;

class FileNotFoundException extends NotizException
{
    const DEFINITION_SOURCE_TYPOSCRIPT_FILE_NOT_FOUND = 'The TypoScript definition file at path `%s` was not found.';

    /**
     * @param string $filePath
     * @return static
     */
    public static function definitionSourceTypoScriptFileNotFound($filePath)
    {
        return self::makeNewInstance(
            self::DEFINITION_SOURCE_TYPOSCRIPT_FILE_NOT_FOUND,
            1503853091,
            [$filePath]
        );
    }
}
