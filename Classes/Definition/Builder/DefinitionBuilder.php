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

namespace CuyZ\Notiz\Definition\Builder;

use CuyZ\Notiz\Definition\Builder\Component\DefinitionComponents;
use CuyZ\Notiz\Definition\Tree\Definition;
use CuyZ\Notiz\Service\CacheService;
use CuyZ\Notiz\Service\Traits\ExtendedSelfInstantiateTrait;
use CuyZ\Notiz\Support\NotizConstants;
use CuyZ\Notiz\Validation\Validator\DefinitionValidator;
use Romm\ConfigurationObject\ConfigurationObjectFactory;
use Romm\ConfigurationObject\ConfigurationObjectInstance;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

/**
 * This class is responsible for building a whole PHP definition object that can
 * then be used everywhere in the API.
 *
 * It works with two types of components:
 *
 * Source components
 * -----------------
 *
 * @see \CuyZ\Notiz\Definition\Builder\Component\Source\DefinitionSource
 *
 * They are used to fetch a definition array from any origin, like TypoScript,
 * YAML or others. This array must be a representation of the definition object
 * used in this extension.
 *
 * The final definition array will be a merge of all the results of the source
 * components.
 *
 * Processor components
 * --------------------
 *
 * @see \CuyZ\Notiz\Definition\Builder\Component\Processor\DefinitionProcessor
 *
 * Once the array definition has been calculated by calling all the source
 * components, a definition object is created. This object can be modified after
 * its creation, by adding so-called "processors" to the builder components.
 *
 * These processor components will have access to the definition object, and can
 * basically use any public method available to add/remove/modify any data.
 *
 * ---
 *
 * Register new components
 * -----------------------
 *
 * To register new components in your own API, you first need to connect a class
 * on a signal. Add this code to your `ext_localconf.php` file:
 *
 * ```
 * $dispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
 *     \TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class
 * );
 *
 * $dispatcher->connect(
 *     \CuyZ\Notiz\Definition\Builder\DefinitionBuilder::class,
 *     \CuyZ\Notiz\Definition\Builder\DefinitionBuilder::COMPONENTS_SIGNAL,
 *     \Vendor\MyExtension\Domain\Definition\Builder\Component\MyCustomComponents::class,
 *     'register'
 * );
 * ```
 *
 * The registration class should then look like this:
 *
 * ```
 * class MyCustomComponents
 * {
 *     public function register(\CuyZ\Notiz\Definition\Builder\Component\DefinitionComponents $components)
 *     {
 *         $components->addSource(
 *             'mySourceIdentifier',
 *             \Vendor\MyExtension\Domain\Definition\Builder\Source\MySource::class
 *         );
 *
 *         $components->addProcessor(
 *             'myProcessorIdentifier',
 *             \Vendor\MyExtension\Domain\Definition\Builder\Processor\MyProcessor::class
 *         );
 *     }
 * }
 * ```
 */
class DefinitionBuilder implements SingletonInterface
{
    use ExtendedSelfInstantiateTrait;

    const COMPONENTS_SIGNAL = 'manageDefinitionComponents';

    /**
     * @var DefinitionComponents
     */
    protected $components;

    /**
     * @var ConfigurationObjectInstance
     */
    protected $definitionObject;

    /**
     * @var CacheService
     */
    protected $cacheService;

    /**
     * @var Dispatcher
     */
    protected $dispatcher;

    /**
     * @param DefinitionComponents $components
     * @param CacheService $cacheService
     * @param Dispatcher $dispatcher
     */
    public function __construct(DefinitionComponents $components, CacheService $cacheService, Dispatcher $dispatcher)
    {
        $this->components = $components;
        $this->cacheService = $cacheService;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Builds a complete definition object, using registered sources and
     * processors.
     *
     * If no error occurred during the build, the instance is put in cache so it
     * can be retrieved during future request.
     *
     * @internal do not use in your own API!
     *
     * @return ConfigurationObjectInstance
     */
    public function buildDefinition()
    {
        if (null === $this->definitionObject) {
            if ($this->cacheService->has(NotizConstants::CACHE_KEY_DEFINITION_OBJECT)) {
                $this->definitionObject = $this->cacheService->get(NotizConstants::CACHE_KEY_DEFINITION_OBJECT);
            }

            if (false === $this->definitionObject instanceof ConfigurationObjectInstance) {
                $this->definitionObject = $this->buildDefinitionInternal();
                $validationResult = $this->definitionObject->getValidationResult();

                if (false === $validationResult->hasErrors()) {
                    /** @var DefinitionValidator $definitionValidator */
                    $definitionValidator = GeneralUtility::makeInstance(DefinitionValidator::class);

                    $result = $definitionValidator->validate($this->definitionObject->getObject());

                    if ($result->hasErrors()) {
                        $validationResult->merge($result);
                    } else {
                        $this->cacheService->set(NotizConstants::CACHE_KEY_DEFINITION_OBJECT, $this->definitionObject);
                    }
                }
            }
        }

        return $this->definitionObject;
    }

    /**
     * Runs the registered source components to get a definition array, then use
     * this array to create a definition object.
     *
     * This object is then passed to each registered processor component, that
     * is used to modify the object data.
     *
     * @return ConfigurationObjectInstance
     */
    protected function buildDefinitionInternal()
    {
        $arrayDefinition = [];

        $this->sendComponentsSignal();

        foreach ($this->components->getSources() as $source) {
            ArrayUtility::mergeRecursiveWithOverrule($arrayDefinition, $source->getDefinitionArray());
        }

        $definitionObject = ConfigurationObjectFactory::convert(Definition::class, $arrayDefinition);

        $this->runProcessors($definitionObject);

        return $definitionObject;
    }

    /**
     * Runs the registered processors, by giving them the previously created
     * definition object that they can modify like they need to.
     *
     * @param ConfigurationObjectInstance $definitionObject
     */
    protected function runProcessors(ConfigurationObjectInstance $definitionObject)
    {
        if (false === $definitionObject->getValidationResult()->hasErrors()) {
            /** @var Definition $definition */
            $definition = $definitionObject->getObject();

            foreach ($this->components->getProcessors() as $processor) {
                $processor->process($definition);
            }
        }
    }

    /**
     * Sends a signal to allow external API to manage their own definition
     * components.
     */
    protected function sendComponentsSignal()
    {
        $this->dispatcher->dispatch(
            self::class,
            self::COMPONENTS_SIGNAL,
            [$this->components]
        );
    }
}
