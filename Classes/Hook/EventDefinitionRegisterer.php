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

use Exception;
use CuyZ\Notiz\Event\Service\EventRegistry;
use CuyZ\Notiz\Service\CacheService;
use CuyZ\Notiz\Service\RuntimeService;
use CuyZ\Notiz\Support\NotizConstants;
use Throwable;
use TYPO3\CMS\Core\Database\TableConfigurationPostProcessingHookInterface;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * This class is hooked on the TYPO3 core signal:
 *
 * `$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['extTablesInclusion-PostProcessing']`
 *
 * We get there to register events (signals, hooks) that were added to the
 * definition.
 */
class EventDefinitionRegisterer implements SingletonInterface, TableConfigurationPostProcessingHookInterface
{
    /**
     * Just calling the event registry.
     *
     * Gotta get SCHWIFTY!
     *
     * @internal
     */
    public function processData()
    {
        if ($this->clearingInstallToolCache()) {
            return;
        }

        try {
            EventRegistry::get()->registerEvents();
        } catch (Throwable $exception) {
            RuntimeService::get()->setException($exception);
        } catch (Exception $exception) {
            // @PHP7
            RuntimeService::get()->setException($exception);
        }
    }

    /**
     * When clearing caches from install tool we need to cancel the registration
     * because of the cache manager not being accessible.
     *
     * One of the only way to know that, is to check if the extension cache is
     * not registered in the cache manager, while the configuration is still
     * present in the global array.
     *
     * This behaviour is due to the special way the install tool clears the
     * caches, and registers extensions caches at the very end of the process.
     *
     * @return bool
     */
    protected function clearingInstallToolCache()
    {
        return false === CacheService::getInstance()->cacheIsRegistered()
            && isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][NotizConstants::CACHE_ID]);
    }
}
