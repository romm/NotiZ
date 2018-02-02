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

namespace CuyZ\Notiz\Property\Service;

use CuyZ\Notiz\Domain\Property\Marker;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * Helper class to parse markers and replace them in a given string.
 */
class MarkerParser implements SingletonInterface
{
    /**
     * @param string $string
     * @param Marker[] $markers
     * @return string
     */
    public function replaceMarkers($string, array $markers)
    {
        if (empty($markers)) {
            return $string;
        }

        $markers = $this->keyMarkersByName($markers);

        list($identifiers, $variables, $roots) = $this->matchMarkers($string);

        if (empty($identifiers)) {
            return $string;
        }

        $replacePairs = [];

        foreach ($variables as $index => $variable) {
            $identifier = $identifiers[$index];
            $root = $roots[$index];
            $marker = $markers[$root];

            $value = $this->getVariableValue($variable, $root, $marker);

            $replacePairs[$identifier] = $value;
        }

        return strtr($string, $replacePairs);
    }

    /**
     * This will set each marker's name as the array key
     *
     * @param array $markers
     * @return array
     */
    private function keyMarkersByName(array $markers)
    {
        $aux = [];

        // This will avoid looping on markers for each variable
        foreach ($markers as $marker) {
            $aux[$marker->getName()] = $marker;
        }

        return $aux;
    }

    /**
     * This method will find all markers in the string
     *
     * @param $string
     * @return array
     */
    private function matchMarkers($string)
    {
        preg_match_all(
            '/{
                (
                    ([a-z]+[a-z0-1]*)           # The root variable
                    (?:\.[a-z]+[a-z0-1]*)*      # The other parts
                )
            }/xi',
            $string,
            $matches
        );

        return $matches;
    }

    /**
     * @param string $variable
     * @param string $root
     * @param Marker $marker
     * @return mixed
     */
    protected function getVariableValue($variable, $root, Marker $marker)
    {
        // We need to have the root name to allow the ObjectAccess class to
        // retrieve the value.
        $target = [
            $root => $marker->getValue(),
        ];

        return ObjectAccess::getPropertyPath($target, $variable);
    }
}
