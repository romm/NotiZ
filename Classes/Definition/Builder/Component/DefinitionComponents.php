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

namespace CuyZ\Notiz\Definition\Builder\Component;

use CuyZ\Notiz\Definition\Builder\Component\Processor\DefinitionProcessor;
use CuyZ\Notiz\Definition\Builder\Component\Source\DefinitionSource;
use CuyZ\Notiz\Exception\ClassNotFoundException;
use CuyZ\Notiz\Exception\DuplicateEntryException;
use CuyZ\Notiz\Exception\EntryNotFoundException;
use CuyZ\Notiz\Exception\InvalidClassException;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;

/**
 * Components container used in the definition builder.
 *
 * You may use it in your own components registration service to add new sources
 * and processors.
 *
 * @see \CuyZ\Notiz\Definition\Builder\DefinitionBuilder
 */
class DefinitionComponents
{
    /**
     * @var DefinitionSource[]
     */
    protected $sources = [];

    /**
     * @var DefinitionProcessor[]
     */
    protected $processors = [];

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Registers a new source component, that will later be used to fetch a
     * definition array from any origin.
     *
     * The given class name must implement the interface below:
     *
     * @see \CuyZ\Notiz\Definition\Builder\Component\Source\DefinitionSource
     *
     * @param string $identifier
     * @param string $className
     * @return DefinitionSource
     *
     * @throws ClassNotFoundException
     * @throws DuplicateEntryException
     * @throws InvalidClassException
     */
    public function addSource($identifier, $className)
    {
        if (!class_exists($className)) {
            throw ClassNotFoundException::definitionSourceClassNotFound($className);
        }

        if (!in_array(DefinitionSource::class, class_implements($className))) {
            throw InvalidClassException::definitionSourceHasMissingInterface($className);
        }

        if (false === $this->hasSource($identifier)) {
            $this->sources[$identifier] = $this->objectManager->get($className);
        } else {
            $existingEntryClass = get_class($this->sources[$identifier]);

            if ($className !== $existingEntryClass) {
                throw DuplicateEntryException::definitionSourceDuplication($identifier, $existingEntryClass);
            }
        }

        return $this->sources[$identifier];
    }

    /**
     * @param string $identifier
     * @return bool
     */
    public function hasSource($identifier)
    {
        return true === isset($this->sources[$identifier]);
    }

    /**
     * @param string $identifier
     * @return DefinitionSource
     *
     * @throws EntryNotFoundException
     */
    public function getSource($identifier)
    {
        if (false === $this->hasSource($identifier)) {
            throw EntryNotFoundException::definitionSourceNotFound($identifier);
        }

        return $this->sources[$identifier];
    }

    /**
     * @return DefinitionSource[]
     */
    public function getSources()
    {
        return $this->sources;
    }

    /**
     * Registers a new processor component, that will later be used to modify
     * the definition object after it has been created from sources array.
     *
     * The given class name must implement the interface below:
     *
     * @see \CuyZ\Notiz\Definition\Builder\Component\Processor\DefinitionProcessor
     *
     * @param string $identifier
     * @param string $className
     * @return DefinitionProcessor
     *
     * @throws ClassNotFoundException
     * @throws DuplicateEntryException
     * @throws InvalidClassException
     */
    public function addProcessor($identifier, $className)
    {
        if (!class_exists($className)) {
            throw ClassNotFoundException::definitionProcessorClassNotFound($className);
        }

        if (!in_array(DefinitionProcessor::class, class_implements($className))) {
            throw InvalidClassException::definitionProcessorHasMissingInterface($className);
        }

        if (false === $this->hasProcessor($identifier)) {
            $this->processors[$identifier] = $this->objectManager->get($className);
        } else {
            $existingEntryClass = get_class($this->processors[$identifier]);

            if ($className !== $existingEntryClass) {
                throw DuplicateEntryException::definitionProcessorDuplication($identifier, $existingEntryClass);
            }
        }

        return $this->processors[$identifier];
    }

    /**
     * @param string $identifier
     * @return bool
     */
    public function hasProcessor($identifier)
    {
        return true === isset($this->processors[$identifier]);
    }

    /**
     * @param string $identifier
     * @return DefinitionProcessor
     *
     * @throws EntryNotFoundException
     */
    public function getProcessor($identifier)
    {
        if (false === $this->hasProcessor($identifier)) {
            throw EntryNotFoundException::definitionProcessorNotFound($identifier);
        }

        return $this->processors[$identifier];
    }

    /**
     * @return DefinitionProcessor[]
     */
    public function getProcessors()
    {
        return $this->processors;
    }
}
