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

namespace CuyZ\Notiz\Service;

use TYPO3\CMS\Core\SingletonInterface;

class ObjectService implements SingletonInterface
{
    /**
     * Checks if the given class implements or extends the given instance name.
     *
     * @param string $className
     * @param string $instanceName
     * @return bool
     */
    public static function classInstanceOf($className, $instanceName)
    {
        if (interface_exists($instanceName)
            && in_array($instanceName, class_implements($className))
        ) {
            return true;
        } elseif (class_exists($instanceName)
            && in_array($instanceName, class_parents($className))
        ) {
            return true;
        }

        return false;
    }
}
