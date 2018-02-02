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

namespace CuyZ\Notiz\Property;

use CuyZ\Notiz\Exception\PropertyNotAccessibleException;
use CuyZ\Notiz\Service\LocalizationService;

/**
 * An entry of a property definition, that is created in an event:
 *
 * @see \CuyZ\Notiz\Event\Event::buildPropertyDefinition
 *
 * An entry must be named, and can contain a label. You may add more class
 * properties with own getters/setters, that can be filled during the definition
 * build and used during the notification dispatching.
 *
 * You may set its value with arbitrary data that can later be used by a
 * notification, but you wont be able to force a value after the method below
 * was called:
 *
 * @see \CuyZ\Notiz\Event\Event::fillPropertyEntries
 */
abstract class PropertyEntry
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $value;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var bool
     */
    private $frozen = false;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    final public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    final public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     * @return $this
     *
     * @throws PropertyNotAccessibleException
     */
    final public function setValue($value)
    {
        if ($this->frozen) {
            throw PropertyNotAccessibleException::propertyEntryValueNotAccessible($this);
        }

        $this->value = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return LocalizationService::localize($this->label);
    }

    /**
     * @param string $label
     * @return $this
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Once the entry has been processed by the event, we freeze this entry to
     * prevent external value changes.
     *
     * @internal
     */
    final public function freeze()
    {
        $this->frozen = true;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getValue();
    }
}
