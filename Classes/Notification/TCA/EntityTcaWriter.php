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

namespace CuyZ\Notiz\Notification\TCA;

use CuyZ\Notiz\Notification\Service\LegacyNotificationTcaService;
use CuyZ\Notiz\Notification\Service\NotificationTcaService;
use CuyZ\Notiz\Service\Traits\SelfInstantiateTrait;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

abstract class EntityTcaWriter implements SingletonInterface
{
    use SelfInstantiateTrait;

    const LLL_FIELDS = 'LLL:EXT:notiz/Resources/Private/Language/Notification/Entity/Fields.xlf';
    const LLL_TABS = 'LLL:EXT:notiz/Resources/Private/Language/Notification/Entity/Tabs.xlf';

    /**
     * Contains the name of the TCA table.
     *
     * @var string
     */
    protected $tableName;

    /**
     * @var NotificationTcaService
     */
    protected $service;

    /**
     * @var array
     */
    private $data = [];

    /**
     * Manual injection for the TCA service depending on the entity type.
     */
    public function __construct()
    {
        $this->service = GeneralUtility::makeInstance($this->getNotificationTcaServiceClass());
    }

    /**
     * This method must create the basic TCA configuration. It must fill at
     * least the `ctrl` and `columns` sections.
     *
     * Default TYPO3 columns are added automatically, so no need to add them.
     * Common notifications columns are also added automatically.
     *
     * @return array
     */
    abstract protected function buildTcaArray();

    /**
     * This method builds a TCA array and returns it to be used in a
     * configuration file.
     *
     * @param string $tableName
     * @return array
     */
    final public function getTcaArray($tableName)
    {
        $this->tableName = $tableName;

        // Each sub-class starts to fill the array.
        $this->data = $this->buildTcaArray();

        // Some columns are common for all notification types
        $this->addCommonColumns();

        // The default columns are always the same.
        $this->addDefaultTypo3Columns();

        // This hides all fields if the definition has any error.
        $this->addDisplayConditionToFields();

        return $this->data;
    }

    /**
     * This method returns the TCA service class for the current entity type.
     * You can override it to return a class extending `NotificationTcaService`.
     *
     * @return string
     */
    abstract protected function getNotificationTcaServiceClass();

    /**
     * This method returns the LLL string to use for the `channel` column.
     *
     * @return string
     */
    protected function getChannelLabel()
    {
        return self::LLL_FIELDS . ':field.channel';
    }

    /**
     * Returns the TCA array for the event configuration. It is a FlexForm field
     * with as many definitions as there are events using FlexForm.
     *
     * @return array
     */
    private function getEventConfiguration()
    {
        if ($this->service->definitionHasErrors()) {
            return [];
        }

        $configuration = [];
        $displayConditions = [];

        foreach ($this->service->getDefinition()->getEvents() as $event) {
            $provider = $event->getConfiguration()->getFlexFormProvider();

            if ($provider->hasFlexForm()) {
                $identifier = $event->getFullIdentifier();

                $configuration[$identifier] = $provider->getFlexFormValue();
                $displayConditions[] = $identifier;
            }
        }

        if (empty($configuration)) {
            return ['config' => ['type' => 'passthrough']];
        }

        $configuration['default'] = 'FILE:EXT:notiz/Configuration/FlexForm/Event/DefaultEventFlexForm.xml';

        return [
            'label' => self::LLL_FIELDS . ':field.event_configuration',
            'displayCond' => version_compare(VersionNumberUtility::getCurrentTypo3Version(), '8.0.0', '<')
                ? 'USER:' . LegacyNotificationTcaService::class . '->displayEventFlexForm:' . $this->tableName . ':' . implode(',', $displayConditions)
                : 'FIELD:event:IN:' . implode(',', $displayConditions),
            'config' => [
                'type' => 'flex',
                'ds_pointerField' => 'event',
                'ds' => $configuration,
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
            ],
        ];
    }

    /**
     * This will add display condition to all fields in the TCA array: if there
     * exists at least one error in the definition tree, the fields are hidden
     * and an error message is shown.
     */
    private function addDisplayConditionToFields()
    {
        $condition = 'USER:' . NotificationTcaService::class . '->definitionContainsErrors';

        foreach ($this->data['columns'] as $key => $column) {
            if ($key === 'error_message') {
                continue;
            }

            if (isset($column['displayCond'])) {
                if (isset($column['displayCond']['AND'])) {
                    $this->data['columns'][$key]['displayCond']['AND'][] = $condition;
                } else {
                    $this->data['columns'][$key]['displayCond'] = [
                        'AND' => [
                            $condition,
                            $column['displayCond'],
                        ]
                    ];
                }
            } else {
                $this->data['columns'][$key]['displayCond'] = $condition;
            }
        }
    }

    /**
     * Returns the default TYPO3 columns to include in the final TCA array.
     */
    private function addDefaultTypo3Columns()
    {
        $defaultColumns = [
            'sys_language_uid' => [
                'exclude' => 1,
                'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.language',
                'config' => [
                    'type' => 'select',
                    'foreign_table' => 'sys_language',
                    'foreign_table_where' => 'ORDER BY sys_language.title',
                    'items' => [
                        ['LLL:EXT:lang/locallang_general.xlf:LGL.allLanguages', -1],
                        ['LLL:EXT:lang/locallang_general.xlf:LGL.default_value', 0]
                    ]
                ]
            ],
            'l10n_parent' => [
                'displayCond' => 'FIELD:sys_language_uid:>:0',
                'exclude' => 1,
                'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.l18n_parent',
                'l10n_display' => 'defaultAsReadonly',
                'config' => [
                    'type' => 'select',
                    'foreign_table' => $this->tableName,
                    'foreign_table_where' => "AND {$this->tableName}.pid=###CURRENT_PID### AND {$this->tableName}.sys_language_uid IN (-1,0)",
                    'items' => [
                        ['', 0]
                    ]
                ]
            ],
            'l10n_diffsource' => [
                'config' => [
                    'type' => 'passthrough'
                ]
            ],
            't3ver_label' => [
                'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.versionLabel',
                'config' => [
                    'type' => 'input',
                    'size' => 30,
                    'max' => 255
                ]
            ],
            'hidden' => [
                'exclude' => 1,
                'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
                'config' => [
                    'type' => 'check'
                ]
            ],
            'starttime' => [
                'exclude' => 1,
                'l10n_mode' => 'mergeIfNotBlank',
                'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.starttime',
                'config' => [
                    'type' => 'input',
                    'size' => 13,
                    'max' => 20,
                    'eval' => 'datetime',
                    'checkbox' => 0,
                    'default' => 0,
                    'range' => [
                        'lower' => mktime(0, 0, 0, (int)date('m'), (int)date('d'), (int)date('Y'))
                    ]
                ]
            ],
            'endtime' => [
                'exclude' => 1,
                'l10n_mode' => 'mergeIfNotBlank',
                'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.endtime',
                'config' => [
                    'type' => 'input',
                    'size' => 13,
                    'max' => 20,
                    'eval' => 'datetime',
                    'checkbox' => 0,
                    'default' => 0,
                    'range' => [
                        'lower' => mktime(0, 0, 0, (int)date('m'), (int)date('d'), (int)date('Y'))
                    ]
                ]
            ],
        ];

        ArrayUtility::mergeRecursiveWithOverrule(
            $this->data['columns'],
            $defaultColumns
        );
    }

    /**
     * This method adds the common columns used by all Entity Notifications.
     */
    private function addCommonColumns()
    {
        $commonColumns = [
            'error_message' => [
                'displayCond' => 'USER:' . NotificationTcaService::class . '->definitionContainsErrors:inverted',
                'config' => [
                    'type' => 'user',
                    'userFunc' => NotificationTcaService::class . '->getErrorMessage',
                ],
            ],

            'title' => [
                'exclude' => 1,
                'label' => self::LLL_FIELDS . ":field.title",
                'config' => [
                    'type' => 'input',
                    'size' => 30,
                    'eval' => 'trim,required',
                ],
            ],

            // Event configuration

            'event' => [
                'exclude' => 1,
                'label' => self::LLL_FIELDS . ":field.event",
                'l10n_mode' => 'exclude',
                'l10n_display' => 'defaultAsReadonly',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectSingle',
                    'size' => 8,
                    'itemsProcFunc' => NotificationTcaService::class . '->getEventsList',
                    'eval' => 'required',
                ],
            ],

            'event_configuration_flex' => $this->getEventConfiguration(),

            // Channel configuration

            'channel' => [
                'exclude' => 1,
                'label' => $this->getChannelLabel(),
                'l10n_mode' => 'exclude',
                'l10n_display' => 'defaultAsReadonly',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectSingle',
                    'itemsProcFunc' => $this->getNotificationTcaServiceClass() . '->getChannelsList',
                    'eval' => 'required',
                ],
            ],

            // Markers configuration

            'markers' => [
                'exclude' => 1,
                'label' => self::LLL_FIELDS . ":field.markers",
                'l10n_display' => 'defaultAsReadonly',
                'config' => [
                    'type' => 'user',
                    'userFunc' => NotificationTcaService::class . '->getMarkersLabel',
                ]
            ],
        ];

        ArrayUtility::mergeRecursiveWithOverrule(
            $this->data['columns'],
            $commonColumns
        );
    }
}
