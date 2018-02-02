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

use CuyZ\Notiz\Event\AbstractEvent;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

abstract class SchedulerTaskEvent extends AbstractEvent
{
    /**
     * @marker
     *
     * @var int
     */
    protected $uid;

    /**
     * @marker
     *
     * @var string
     */
    protected $title;

    /**
     * @marker
     *
     * @var string
     */
    protected $description;

    /**
     * @param AbstractTask $task
     */
    protected function checkTaskFilter(AbstractTask $task)
    {
        if ($this->configuration['doFilterTasks']) {
            $allowedTasks = GeneralUtility::trimExplode(',', $this->configuration['filteredTasks']);

            if (!in_array($task->getTaskUid(), $allowedTasks)) {
                $this->cancelDispatch();
            }
        }
    }

    /**
     * @param AbstractTask $task
     */
    protected function fillTaskData(AbstractTask $task)
    {
        $this->uid = $task->getTaskUid();
        $this->title = $task->getTaskTitle();
        $this->description = $task->getDescription();
    }
}
