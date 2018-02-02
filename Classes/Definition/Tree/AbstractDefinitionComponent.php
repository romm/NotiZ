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

namespace CuyZ\Notiz\Definition\Tree;

use Romm\ConfigurationObject\Service\Items\DataPreProcessor\DataPreProcessor;
use Romm\ConfigurationObject\Traits\ConfigurationObject\MagicMethodsTrait;

abstract class AbstractDefinitionComponent
{
    use MagicMethodsTrait;

    /**
     * Utility function to automatically fill the `identifier` property of the
     * entries in the given data, with the key in the array of each entry.
     *
     * Before:
     * -------
     *
     * $data = [
     *     'myProperty' => [
     *          'prop1' => ['type' => 'foo'],
     *          'prop2' => ['type' => 'bar']
     *     ]
     * ];
     *
     * After:
     * ------
     *
     * ```
     * $data = [
     *     'myProperty' => [
     *          'prop1' => [
     *              'identifier' => 'prop1',
     *              'type'       => 'foo'
     *          ],
     *          'prop2' => [
     *              'identifier' => 'prop2',
     *              'type'       => 'bar'
     *          ],
     *     ]
     * ];
     * ```
     *
     * @param DataPreProcessor $processor
     * @param string $property
     * @param string $name
     */
    protected static function forceIdentifierForProperty(DataPreProcessor $processor, $property, $name = 'identifier')
    {
        $data = $processor->getData();
        $data = is_array($data)
            ? $data
            : [];

        if (isset($data[$property])) {
            foreach ($data[$property] as $key => $entry) {
                if (is_array($entry)) {
                    $data[$property][$key][$name] = $key;
                }
            }
        }

        $processor->setData($data);
    }
}
