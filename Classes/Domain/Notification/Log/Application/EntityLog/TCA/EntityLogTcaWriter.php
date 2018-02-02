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

namespace CuyZ\Notiz\Domain\Notification\Log\Application\EntityLog\TCA;

use CuyZ\Notiz\Notification\TCA\EntityTcaWriter;

class EntityLogTcaWriter extends EntityTcaWriter
{
    const LOG_LLL = 'LLL:EXT:notiz/Resources/Private/Language/Notification/Log/Entity.xlf';

    /**
     * @return string
     */
    protected function getNotificationTcaServiceClass()
    {
        return EntityLogTcaService::class;
    }

    /**
     * @return string
     */
    protected function getChannelLabel()
    {
        return self::LOG_LLL . ':field.logger';
    }

    /**
     * @inheritdoc
     */
    protected function buildTcaArray()
    {
        $lll = 'LLL:EXT:notiz/Resources/Private/Language/Notification/Log/Entity.xlf';

        return [
            'ctrl' => [
                'title' => "$lll:title",
                'label' => 'title',
                'tstamp' => 'tstamp',
                'crdate' => 'crdate',
                'cruser_id' => 'cruser_id',
                'dividers2tabs' => true,

                'requestUpdate' => 'event',

                'languageField' => 'sys_language_uid',
                'transOrigPointerField' => 'l10n_parent',
                'transOrigDiffSourceField' => 'l10n_diffsource',
                'delete' => 'deleted',
                'enablecolumns' => [
                    'disabled' => 'hidden',
                    'starttime' => 'starttime',
                    'endtime' => 'endtime',
                ],
                'searchFields' => 'title,event',
                'iconfile' => $this->service->getNotificationIconPath()
            ],

            'palettes' => [
                'content' => [
                    'showitem' => 'message,markers',
                    'canNotCollapse' => true,
                ],
                'levels' => [
                    'showitem' => 'level,levels_descriptions',
                    'canNotCollapse' => true,
                ],
            ],

            'types' => [
                '0' => [
                    'showitem' => "
                error_message,
                title, sys_language_uid, hidden,
                --div--;" . self::LLL_TABS . ":tab.event,
                    event, event_configuration_flex,
                --div--;" . self::LLL_TABS . ":tab.channel,
                    channel,
                --div--;$lll:tab.log,
                    --palette--;$lll:palette.content;content,
                    --palette--;$lll:palette.levels;levels
"
                ]
            ],

            'columns' => [

                'message' => [
                    'exclude' => 1,
                    'label' => "$lll:field.message",
                    'config' => [
                        'type' => 'input',
                        'size' => 255,
                        'eval' => 'trim,required',
                    ],
                ],

                'level' => [
                    'exclude' => 1,
                    'label' => $lll . ':field.level',
                    'l10n_mode' => 'exclude',
                    'l10n_display' => 'defaultAsReadonly',
                    'config' => [
                        'type' => 'radio',
                        'items' => [],
                        'itemsProcFunc' => EntityLogTcaService::class . '->getLogLevelsList',
                    ]
                ],

                'levels_descriptions' => [
                    'exclude' => 1,
                    'label' => "$lll:field.levels_descriptions_title",
                    'l10n_display' => 'defaultAsReadonly',
                    'config' => [
                        'type' => 'user',
                        'userFunc' => EntityLogTcaService::class . '->getLogLevelsDescriptions',
                    ]
                ],

            ],
        ];
    }
}
