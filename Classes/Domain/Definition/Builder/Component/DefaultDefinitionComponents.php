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

namespace CuyZ\Notiz\Domain\Definition\Builder\Component;

use CuyZ\Notiz\Definition\Builder\Component\DefinitionComponents;
use CuyZ\Notiz\Definition\Builder\Component\Source\DefinitionSource;
use CuyZ\Notiz\Domain\Definition\Builder\Component\Source\TypoScriptDefinitionSource;
use CuyZ\Notiz\Service\ExtensionConfigurationService;
use CuyZ\Notiz\Support\NotizConstants;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Service used for registration of default definition components supplied by
 * the extension.
 */
class DefaultDefinitionComponents implements SingletonInterface
{
    /**
     * @var bool
     */
    protected $registrationDone = false;

    /**
     * @var ExtensionConfigurationService
     */
    protected $extensionConfigurationService;

    /**
     * @param ExtensionConfigurationService $extensionConfigurationService
     */
    public function __construct(ExtensionConfigurationService $extensionConfigurationService)
    {
        $this->extensionConfigurationService = $extensionConfigurationService;
    }

    /**
     * Default definition comes from TypoScript files, so a definition source
     * must be added to the definition builder and the files must be registered.
     *
     * @param DefinitionComponents $components
     */
    public function register(DefinitionComponents $components)
    {
        if ($this->registrationDone) {
            return;
        }

        $this->registrationDone = true;

        /** @var TypoScriptDefinitionSource $typoScriptDefinitionSource */
        $typoScriptDefinitionSource = $components->addSource(
            DefinitionSource::SOURCE_TYPOSCRIPT,
            TypoScriptDefinitionSource::class
        );

        // Default channels.
        $typoScriptDefinitionSource->addTypoScriptFilePath(NotizConstants::TYPOSCRIPT_PATH . 'Channel/Channels.Default.typoscript');

        // Default notifications.
        $typoScriptDefinitionSource->addTypoScriptFilePath(NotizConstants::TYPOSCRIPT_PATH . 'Notification/Notifications.typoscript');

        // TYPO3 events can be enabled/disabled in the extension configuration.
        if ($this->extensionConfigurationService->getConfigurationValue('events.typo3')) {
            $typoScriptDefinitionSource->addTypoScriptFilePath(NotizConstants::TYPOSCRIPT_PATH . 'Event/Events.TYPO3.typoscript');

            if (ExtensionManagementUtility::isLoaded('scheduler')) {
                $typoScriptDefinitionSource->addTypoScriptFilePath(NotizConstants::TYPOSCRIPT_PATH . 'Event/Events.Scheduler.typoscript');
            }
        }
    }
}
