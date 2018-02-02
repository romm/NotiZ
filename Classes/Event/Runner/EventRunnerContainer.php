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

namespace CuyZ\Notiz\Event\Runner;

use CuyZ\Notiz\Definition\Tree\EventGroup\Event\EventDefinition;
use CuyZ\Notiz\Exception\EntryNotFoundException;
use CuyZ\Notiz\Service\Container;
use CuyZ\Notiz\Service\Traits\SelfInstantiateTrait;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * Contains all the event runners.
 *
 * This class is mostly needed because of the way event hooks can be registered:
 *
 * @see \CuyZ\Notiz\Definition\Tree\EventGroup\Event\Connection\Hook::preventEvalNeverIdealStuff
 */
class EventRunnerContainer implements SingletonInterface
{
    use SelfInstantiateTrait {
        get as getInstance;
    }

    /**
     * @var EventRunner[]
     */
    protected $entries;

    /**
     * @param EventDefinition $eventDefinition
     * @return EventRunner
     */
    public function add(EventDefinition $eventDefinition)
    {
        $identifier = $eventDefinition->getFullIdentifier();

        if (false === $this->has($identifier)) {
            /** @var EventRunner $runner */
            $runner = Container::get(EventRunner::class, $eventDefinition);

            $this->entries[$identifier] = $runner;
        }

        return $this->entries[$identifier];
    }

    /**
     * @param string $identifier
     * @return bool
     */
    public function has($identifier)
    {
        return isset($this->entries[$identifier]);
    }

    /**
     * @param string $identifier
     * @return EventRunner
     *
     * @throws EntryNotFoundException
     */
    public function get($identifier)
    {
        if (false === $this->has($identifier)) {
            throw EntryNotFoundException::eventRunnerEntryNotFound($identifier);
        }

        return $this->entries[$identifier];
    }
}
