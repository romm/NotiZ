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

namespace CuyZ\Notiz\Hook;

use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Utility class that allows TYPO3 v7 to handle the FlexForm for notifications
 * fields.
 *
 * @deprecated Must be removed when TYPO3 v7 is not supported anymore.
 */
class NotificationFlexFormProcessor implements SingletonInterface
{
    /**
     * @var array
     */
    protected static $handledTables = [
        'tx_notiz_domain_model_entityemailnotification' => 'event_configuration_flex',
        'tx_notiz_domain_model_entitylognotification' => 'event_configuration_flex'
    ];

    /**
     * @param array $flexFormArray
     * @param array $conf
     * @param array $row
     * @param string $table
     * @param string $fieldName
     */
    public function getFlexFormDS_postProcessDS(&$flexFormArray, $conf, $row, $table, $fieldName)
    {
        $flag = false;

        foreach (self::$handledTables as $handledTable => $handledField) {
            if ($handledTable === $table
                && $handledField === $fieldName
                && 0 !== $row['sys_language_uid']
            ) {
                $flag = true;
                break;
            }
        }

        if (!$flag) {
            return;
        }

        $parent = $this->getDatabaseConnection()
            ->exec_SELECTgetSingleRow(
                'event',
                $table,
                'uid=' . (int)$row['l10n_parent']
            );

        if (!$parent) {
            return;
        }

        $event = $parent['event'];

        if (!isset($conf['ds'][$event])) {
            return;
        }

        $ds = $conf['ds'][$event];

        if (substr($ds, 0, 5) == 'FILE:') {
            $file = GeneralUtility::getFileAbsFileName(substr($ds, 5));

            if ($file && @is_file($file)) {
                $flexFormArray = GeneralUtility::xml2array(GeneralUtility::getUrl($file));
            } else {
                $flexFormArray = 'The file "' . substr($ds, 5) . '" in ds-array key "' . $event . '" was not found ("' . $file . '")';
            }
        } else {
            $flexFormArray = GeneralUtility::xml2array($ds);
        }
    }

    /**
     * @return DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }
}
