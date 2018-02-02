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

namespace CuyZ\Notiz\Definition;

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\Error\Result;

class DefinitionTransformer implements SingletonInterface
{
    /**
     * @var DefinitionService
     */
    protected $definitionService;

    /**
     * @param DefinitionService $definitionService
     */
    public function __construct(DefinitionService $definitionService)
    {
        $this->definitionService = $definitionService;
    }

    /**
     * Returns a custom definition array, where every last part of the array
     * is not the real value, but an array that contains the following keys:
     *
     * - `value` - the actual value
     * - `path` - the full path to this value
     * - `errors` - errors found at this path during the validation
     * - `warnings` - warnings found at this path during the validation
     *
     * @return array
     */
    public function getDefinitionArray()
    {
        return $this->transformDefinition(
            $this->definitionService->getDefinitionArray(),
            $this->definitionService->getValidationResult()
        );
    }

    /**
     * @param array $definition
     * @param Result $result
     * @param array $path
     * @return array
     */
    protected function transformDefinition(array $definition, Result $result, array $path = [])
    {
        $newDefinition = [];

        foreach ($definition as $key => $value) {
            $data = [];
            $newPath = array_merge($path, [$key]);
            $readablePath = implode('.', $newPath);

            if (is_array($value)) {
                $data['sub'] = $this->transformDefinition($value, $result, $newPath);
            } else {
                $data['value'] = $value;
                $data['path'] = $readablePath;
            }

            $propertyResult = $result->forProperty($readablePath);

            $data['errors'] = $propertyResult->getErrors();
            $data['warnings'] = $propertyResult->getWarnings();

            $newDefinition[$key] = $data;
        }

        if (empty($path)) {
            $newDefinition = [
                'sub' => $newDefinition,
            ];

            $newDefinition['errors'] = $result->getErrors();
            $newDefinition['warnings'] = $result->getWarnings();
        }

        return $newDefinition;
    }
}
