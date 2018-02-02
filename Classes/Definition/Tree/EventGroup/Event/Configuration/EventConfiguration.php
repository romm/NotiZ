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

namespace CuyZ\Notiz\Definition\Tree\EventGroup\Event\Configuration;

use CuyZ\Notiz\Definition\Tree\AbstractDefinitionComponent;
use CuyZ\Notiz\Event\Configuration\FlexForm\DefaultEventFlexFormProvider;
use CuyZ\Notiz\Event\Configuration\FlexForm\EventFlexFormProvider;
use CuyZ\Notiz\Exception\ClassNotFoundException;
use CuyZ\Notiz\Exception\InvalidClassException;
use CuyZ\Notiz\Exception\NotizException;
use Romm\ConfigurationObject\Service\Items\DataPreProcessor\DataPreProcessor;
use Romm\ConfigurationObject\Service\Items\DataPreProcessor\DataPreProcessorInterface;
use TYPO3\CMS\Extbase\Validation\Error;

class EventConfiguration extends AbstractDefinitionComponent implements DataPreProcessorInterface
{
    const DEFAULT_PROVIDER = DefaultEventFlexFormProvider::class;

    const PROVIDER_CLASS_NAME = '__notiz_event_configuration_flexForm_provider';

    /**
     * @var \CuyZ\Notiz\Event\Configuration\FlexForm\EventFlexFormProvider
     *
     * @mixedTypesResolver \CuyZ\Notiz\Definition\Tree\EventGroup\Event\Configuration\FlexForm\EventFlexFormResolver
     */
    protected $flexForm;

    /**
     * @return EventFlexFormProvider
     */
    public function getFlexFormProvider()
    {
        return $this->flexForm;
    }

    /**
     * Pre-fills properties for this class.
     *
     * @param DataPreProcessor $processor
     */
    public static function dataPreProcessor(DataPreProcessor $processor)
    {
        $data = $processor->getData();

        if (!isset($data['flexForm'])) {
            $data['flexForm'] = [];
        }

        $data['flexForm'][self::PROVIDER_CLASS_NAME] = self::DEFAULT_PROVIDER;

        try {
            $data = self::setFlexFormProviderClassName($data);
        } catch (NotizException $exception) {
            $error = new Error($exception->getMessage(), $exception->getCode());
            $processor->addError($error);
        }

        $processor->setData($data);
    }

    /**
     * Checks the provider class name validity. If it is valid, the value is
     * added to the data array to be passed to the child property (the resolver
     * will use it to instantiate the class later).
     *
     * @param array $data
     * @return array
     *
     * @throws ClassNotFoundException
     * @throws InvalidClassException
     */
    protected static function setFlexFormProviderClassName(array $data)
    {
        if (isset($data['flexFormProviderClassName'])) {
            $className = $data['flexFormProviderClassName'];

            if (!class_exists($className)) {
                throw ClassNotFoundException::eventConfigurationFlexFormProviderClassNotFound($className);
            }

            if (!in_array(EventFlexFormProvider::class, class_implements($className))) {
                throw InvalidClassException::eventConfigurationFlexFormProviderMissingInterface($className);
            }

            $data['flexForm'][self::PROVIDER_CLASS_NAME] = $data['flexFormProviderClassName'];
        }

        return $data;
    }
}
