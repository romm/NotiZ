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

namespace CuyZ\Notiz\Service\Extension;

use CuyZ\Notiz\Backend\ToolBarItems\NotificationsToolbarItem;
use CuyZ\Notiz\Definition\Builder\DefinitionBuilder;
use CuyZ\Notiz\Domain\Definition\Builder\Component\DefaultDefinitionComponents;
use CuyZ\Notiz\Hook\EventDefinitionRegisterer;
use CuyZ\Notiz\Hook\NotificationFlexFormProcessor;
use CuyZ\Notiz\Service\Container;
use CuyZ\Notiz\Service\ExtensionConfigurationService;
use CuyZ\Notiz\Service\Traits\SelfInstantiateTrait;
use CuyZ\Notiz\Support\NotizConstants;
use TYPO3\CMS\Core\Cache\Backend\FileBackend;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Core\Database\TableConfigurationPostProcessingHookInterface;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;
use TYPO3\CMS\Scheduler\Scheduler;

/**
 * This class replaces the old-school procedural way of handling configuration
 * in `ext_localconf.php` file.
 *
 * @internal
 */
class LocalConfigurationService implements SingletonInterface, TableConfigurationPostProcessingHookInterface
{
    use SelfInstantiateTrait;

    /**
     * @var IconRegistry
     */
    protected $iconRegistry;

    /**
     * @var Dispatcher
     */
    protected $dispatcher;

    /**
     * @var ExtensionConfigurationService
     */
    protected $extensionConfigurationService;

    /**
     * Manual dependency injection.
     */
    public function __construct()
    {
        $this->dispatcher = GeneralUtility::makeInstance(Dispatcher::class);
        $this->iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);
    }

    /**
     * Main processing methods that will call every method of this class.
     */
    public function process()
    {
        $this->registerLaterProcessHook();
        $this->registerDefinitionComponents();
        $this->registerEventDefinitionHook();
        $this->registerNotificationFlexFormProcessorHook();
        $this->registerInternalCache();
        $this->registerIcons();
        $this->resetTypeConvertersArray();
        $this->overrideScheduler();
    }

    /**
     * This is the second part of the process.
     *
     * Because the `ExtensionConfigurationService` needs the database to be
     * initialized (Extbase reflection service may need it), we need to hook
     * later in the TYPO3 bootstrap process to ensure everything has been
     * initialized.
     *
     * @see \CuyZ\Notiz\Service\Extension\LocalConfigurationService::registerLaterProcessHook
     */
    public function processData()
    {
        $this->extensionConfigurationService = Container::get(ExtensionConfigurationService::class);

        $this->registerToolBarItem();
    }

    /**
     * @see \CuyZ\Notiz\Service\Extension\LocalConfigurationService::processData
     */
    protected function registerLaterProcessHook()
    {
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['extTablesInclusion-PostProcessing'][] = static::class;
    }

    /**
     * Connects a slot on the definition components customization signal.
     */
    protected function registerDefinitionComponents()
    {
        $this->dispatcher->connect(
            DefinitionBuilder::class,
            DefinitionBuilder::COMPONENTS_SIGNAL,
            DefaultDefinitionComponents::class,
            'register'
        );
    }

    /**
     * Hooking in TYPO3 early process to register all hooks/signals added to the
     * event definition.
     */
    protected function registerEventDefinitionHook()
    {
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['extTablesInclusion-PostProcessing'][] = EventDefinitionRegisterer::class;
    }

    /**
     * @deprecated Must be removed when TYPO3 v7 is not supported anymore.
     */
    protected function registerNotificationFlexFormProcessorHook()
    {
        if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '8.0.0', '<')) {
            $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_befunc.php']['getFlexFormDSClass'][] = NotificationFlexFormProcessor::class;
        }
    }

    /**
     * Internal cache used by the extension.
     */
    protected function registerInternalCache()
    {
        if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][NotizConstants::CACHE_ID])) {
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][NotizConstants::CACHE_ID] = [
                'backend' => FileBackend::class,
                'frontend' => VariableFrontend::class,
                'groups' => ['all', 'system', 'pages'],
            ];
        }
    }

    /**
     * Registers icons that can then be used wherever in TYPO3 with the icon
     * API.
     */
    protected function registerIcons()
    {
        $this->iconRegistry->registerIcon(
            'tx-notiz-icon',
            SvgIconProvider::class,
            ['source' => NotizConstants::EXTENSION_ICON_DEFAULT]
        );

        $this->iconRegistry->registerIcon(
            'tx-notiz-icon-toolbar',
            SvgIconProvider::class,
            ['source' => NotizConstants::EXTENSION_ICON_PATH . 'notiz-icon-toolbar.svg']
        );

        $this->iconRegistry->registerIcon(
            'tx-notiz-icon-main-module',
            SvgIconProvider::class,
            ['source' => NotizConstants::EXTENSION_ICON_MAIN_MODULE_PATH]
        );
    }

    /**
     * Registers the tool bar item shown on the header of the TYPO3 backend.
     */
    protected function registerToolBarItem()
    {
        $enableToolbar = $this->extensionConfigurationService->getConfigurationValue('toolbar.enable');

        if ($enableToolbar) {
            $GLOBALS['TYPO3_CONF_VARS']['BE']['toolbarItems'][1505997677] = NotificationsToolbarItem::class;
        }
    }

    /**
     * Because of some core issue concerning the type converters registration,
     * we need to make sure the array containing the entries is valid.
     *
     * See the ticket below for more information:
     *
     * @link https://forge.typo3.org/issues/82651
     *
     * @deprecated This method should be removed when the patch has been merged.
     */
    protected function resetTypeConvertersArray()
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['extbase']['typeConverters'] = array_unique($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['extbase']['typeConverters']);
    }

    /**
     * Registering a xClass that overrides core scheduler, to have access to
     * signals for when tasks are executed.
     *
     * @see \CuyZ\Notiz\Service\Scheduler\Scheduler
     */
    protected function overrideScheduler()
    {
        if (ExtensionManagementUtility::isLoaded('scheduler')) {
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][Scheduler::class] = ['className' => \CuyZ\Notiz\Service\Scheduler\Scheduler::class];
        }
    }
}
