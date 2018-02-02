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

use Exception;
use CuyZ\Notiz\Definition\Builder\DefinitionBuilder;
use CuyZ\Notiz\Definition\Tree\Definition;
use CuyZ\Notiz\Exception\InvalidDefinitionException;
use CuyZ\Notiz\Service\RuntimeService;
use CuyZ\Notiz\Service\Traits\ExtendedSelfInstantiateTrait;
use Romm\ConfigurationObject\ConfigurationObjectInstance;
use Throwable;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\Error\Result;

/**
 * Root definition service, that can be used to get the definition object used
 * everywhere in this extension.
 *
 * A validation process is done on the object to check if all its properties and
 * sub-properties are valid. Use the code below to check for errors:
 *
 * ```
 * $validationResult = DefinitionService::get()->getValidationResult();
 *
 * if ($validationResult->hasErrors()) {
 *     // ...
 * } else {
 *     // ...
 * }
 * ```
 *
 * To get the definition object, just use the code below:
 *
 * `DefinitionService::get()->getDefinition()`
 *
 * Please note that if the definition validation failed, an exception will be
 * thrown if you try to access the definition object: you must first check that
 * no errors exist (see above).
 */
class DefinitionService implements SingletonInterface
{
    use ExtendedSelfInstantiateTrait;

    /**
     * @var ConfigurationObjectInstance
     */
    protected $definitionObject;

    /**
     * @var Result
     */
    protected $validationResult;

    /**
     * If an exception/error was thrown during the creation of the definition
     * object, this property is filled with it.
     *
     * @var Throwable
     */
    protected $exception;

    /**
     * @var DefinitionBuilder
     */
    protected $builder;

    /**
     * @var RuntimeService
     */
    protected $runtimeService;

    /**
     * @param DefinitionBuilder $builder
     * @param RuntimeService $runtimeService
     */
    public function __construct(DefinitionBuilder $builder, RuntimeService $runtimeService)
    {
        $this->builder = $builder;
        $this->runtimeService = $runtimeService;
    }

    /**
     * @return Result
     */
    public function getValidationResult()
    {
        $this->buildDefinitionObject();

        return $this->validationResult;
    }

    /**
     * @return Definition
     *
     * @throws InvalidDefinitionException
     */
    public function getDefinition()
    {
        $this->buildDefinitionObject();

        if ($this->validationResult->hasErrors()) {
            throw InvalidDefinitionException::definitionErrorNoAccess();
        }

        /** @var Definition $definition */
        $definition = $this->definitionObject->getObject();

        return $definition;
    }

    /**
     * Please note that the returned array can not be considered as a valid
     * definition array! You must still check the validation result.
     *
     * @return array
     */
    public function getDefinitionArray()
    {
        $this->buildDefinitionObject();

        if ($this->exception) {
            return [];
        }

        /** @var Definition $definition */
        $definition = $this->definitionObject->getObject(true);

        return $definition->toArray();
    }

    /**
     * Calls the builder to get the definition object instance.
     *
     * If an exception/error is thrown during the process, it is catch and added
     * to the runtime service.
     */
    protected function buildDefinitionObject()
    {
        if (null === $this->definitionObject) {
            $this->definitionObject = false;

            $exception = null;

            try {
                $this->definitionObject = $this->builder->buildDefinition();
                $this->validationResult = $this->definitionObject->getValidationResult();
            } catch (Throwable $exception) {
            } catch (Exception $exception) {
                // @PHP7
            }

            if ($exception) {
                $this->exception = $exception;
                $this->validationResult = new Result;

                $this->runtimeService->setException($exception);
            }
        }
    }
}
