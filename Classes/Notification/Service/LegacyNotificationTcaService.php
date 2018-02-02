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

namespace CuyZ\Notiz\Notification\Service;

use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Utility class that allows TYPO3 v7 to know when to display the event
 * FlexForm.
 *
 * @deprecated Must be removed when TYPO3 v7 is not supported anymore.
 */
class LegacyNotificationTcaService implements SingletonInterface
{
    /**
     * @param array $parameters
     * @return bool
     */
    public function displayEventFlexForm(array $parameters)
    {
        $record = $parameters['record'];
        $parentUid = $record['l10n_parent'][0];
        $allowedEvents = GeneralUtility::trimExplode(',', $parameters['conditionParameters'][1]);

        if ($parentUid == 0) {
            $event = $record['event'][0];
        } else {
            $tableName = $parameters['conditionParameters'][0];

            $parent = $this->getDatabaseConnection()
                ->exec_SELECTgetSingleRow(
                    'event',
                    $tableName,
                    'uid=' . (int)$parentUid
                );

            $event = $parent['event'];
        }

        return in_array($event, $allowedEvents);
    }

    /**
     * @return DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }
}
