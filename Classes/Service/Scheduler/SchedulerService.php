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

namespace CuyZ\Notiz\Service\Scheduler;

use CuyZ\Notiz\Service\LocalizationService;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Scheduler\Scheduler as CoreScheduler;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

/**
 * Little service to fetch scheduler tasks, because TYPO3 core doesn't provide
 * any.
 */
class SchedulerService implements SingletonInterface
{
    /**
     * @var CoreScheduler
     */
    protected $scheduler;

    /**
     * Manual dependency injection.
     */
    public function __construct()
    {
        $this->scheduler = GeneralUtility::makeInstance(CoreScheduler::class);
    }

    /**
     * Too bad we don't have a repository for this.
     *
     * @return array
     */
    public function getTasksList()
    {
        if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '8.3.0', '<')) {
            /** @var DatabaseConnection $database */
            $database = $GLOBALS['TYPO3_DB'];

            $tasks = $database->exec_SELECTgetRows(
                '*',
                'tx_scheduler_task',
                'disable=0'
            );
        } else {
            /** @var ConnectionPool $connectionPool */
            $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);

            $queryBuilder = $connectionPool->getQueryBuilderForTable('tx_scheduler_task');
            $queryBuilder->getRestrictions()->removeAll();

            $tasks = $queryBuilder->select('*')
                ->from('tx_scheduler_task')
                ->where(
                    $queryBuilder->expr()->eq(
                        'disable',
                        $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT)
                    )
                )
                ->execute()
                ->fetchAll();
        }

        return $tasks;
    }

    /**
     * Adds all existing and enabled scheduler tasks to the given TCA array.
     *
     * @param array $parameters
     */
    public function getTasksListForTca(array &$parameters)
    {
        $parameters['items'] = array_map(
            function ($task) {
                $uid = $task['uid'];

                /** @var AbstractTask $taskObject */
                $taskObject = unserialize($task['serialized_task_object']);

                $title = $this->scheduler->isValidTaskObject($taskObject)
                    ? $taskObject->getTaskTitle()
                    : '*' . LocalizationService::localize('Event/Scheduler/SchedulerTask:flex_form.invalid_task') . '*';

                return [
                    $title . ' [' . $uid . ']',
                    $uid,
                ];
            },
            $this->getTasksList()
        );
    }
}
