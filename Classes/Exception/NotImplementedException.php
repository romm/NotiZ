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

class NotImplementedException extends NotizException
{
    const TCA_SERVICE_NOTIFICATION_IDENTIFIER_MISSING = 'The method `%s` must be implemented and return the definition identifier.';

    /**
     * @param string $methodName
     * @return static
     */
    public static function tcaServiceNotificationIdentifierMissing($methodName)
    {
        return self::makeNewInstance(
            self::TCA_SERVICE_NOTIFICATION_IDENTIFIER_MISSING,
            1509884492,
            [$methodName]
        );
    }
}
