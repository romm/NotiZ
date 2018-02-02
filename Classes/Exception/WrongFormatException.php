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

use CuyZ\Notiz\Definition\Tree\EventGroup\Event\Connection\Hook;

class WrongFormatException extends NotizException
{
    const TAG_SERVICE_IDENTIFIER_WRONG_FORMAT = 'The given identifier for the property `%s` is not valid, given value is `%s`, a suggestion would be `%s`. The format must respect the following rules: "%s".';

    const EVENT_HOOK_METHOD_NAME_WRONG_FORMAT = 'The method name for the hook connection at the path `%s` is not valid. Given value was `%s`. The name must begin with a letter or an underscore and contain only alphanumeric characters and underscores.';

    const SLOT_NAME_WRONG_FORMAT = 'The name "%s" is not valid for a slot name. Please use only alphanumeric/underscore/minus characters.';

    /**
     * @param string $propertyType
     * @param string $identifier
     * @param string $suggestion
     * @param string $rules
     * @return static
     */
    public static function tagServiceIdentifierWrongFormat($propertyType, $identifier, $suggestion, $rules)
    {
        return self::makeNewInstance(
            self::TAG_SERVICE_IDENTIFIER_WRONG_FORMAT,
            1504169118,
            [$propertyType, $identifier, $suggestion, $rules]
        );
    }

    /**
     * @param string $methodName
     * @param Hook $hook
     * @return static
     */
    public static function eventHookMethodNameWrongFormat($methodName, Hook $hook)
    {
        return self::makeNewInstance(
            self::EVENT_HOOK_METHOD_NAME_WRONG_FORMAT,
            1506800906,
            [$hook->getPath(), $methodName]
        );
    }

    /**
     * @param string $name
     * @return static
     */
    public static function slotNameWrongFormat($name)
    {
        return self::makeNewInstance(
            self::SLOT_NAME_WRONG_FORMAT,
            1506800906,
            [$name]
        );
    }
}
