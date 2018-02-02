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

namespace CuyZ\Notiz\Domain\Event\Scheduler;

use Throwable;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

/**
 * Event triggered when an exception/error was thrown during the execution of a
 * scheduler task.
 */
class SchedulerTaskExecutionFailedEvent extends SchedulerTaskEvent
{
    /**
     * @label Event/Scheduler/SchedulerTask:execution_failed.marker.exception
     * @marker
     *
     * @var string
     */
    protected $exception;

    /**
     * @param AbstractTask $task
     * @param Throwable $exception
     */
    public function run(AbstractTask $task, $exception)
    {
        $this->checkTaskFilter($task);
        $this->fillTaskData($task);

        $this->exception = $exception;
    }
}
