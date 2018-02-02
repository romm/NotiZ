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

namespace CuyZ\Notiz\Event\Service;

use CuyZ\Notiz\Definition\DefinitionService;
use CuyZ\Notiz\Event\Runner\EventRunnerContainer;
use CuyZ\Notiz\Service\Traits\ExtendedSelfInstantiateTrait;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * This class will do all the registration work for every event that was added
 * to the definition.
 *
 * Events are bound to signals/hooks, this class will process every event and
 * call their own registration method.
 */
class EventRegistry implements SingletonInterface
{
    use ExtendedSelfInstantiateTrait;

    /**
     * @var bool
     */
    protected $registrationDone = false;

    /**
     * @var DefinitionService
     */
    protected $definitionService;

    /**
     * @var EventRunnerContainer
     */
    protected $eventRunnerContainer;

    /**
     * @param DefinitionService $definitionService
     * @param EventRunnerContainer $eventRunnerContainer
     */
    public function __construct(DefinitionService $definitionService, EventRunnerContainer $eventRunnerContainer)
    {
        $this->definitionService = $definitionService;
        $this->eventRunnerContainer = $eventRunnerContainer;
    }

    /**
     * If an error is found in the definition validation result, the events
     * registration is aborted.
     *
     * @internal
     */
    public function registerEvents()
    {
        if ($this->registrationDone) {
            return;
        }

        $this->registrationDone = true;

        if (!$this->definitionService->getValidationResult()->hasErrors()) {
            $this->registerEventsInternal();
        }
    }

    /**
     * Loops on each event entry in the definition, and calls the `register()`
     * method that will do the actual registration work of the signals/hooks.
     */
    protected function registerEventsInternal()
    {
        $definition = $this->definitionService->getDefinition();

        foreach ($definition->getEvents() as $eventDefinition) {
            $eventRunner = $this->eventRunnerContainer->add($eventDefinition);

            $eventDefinition->getConnection()->register($eventRunner);
        }
    }
}
