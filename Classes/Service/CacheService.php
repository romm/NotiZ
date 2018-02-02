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

namespace CuyZ\Notiz\Service;

use CuyZ\Notiz\Service\Traits\ExtendedSelfInstantiateTrait;
use CuyZ\Notiz\Support\NotizConstants;
use TYPO3\CMS\Core\Cache\Backend\TransientMemoryBackend;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class CacheService implements SingletonInterface
{
    use ExtendedSelfInstantiateTrait {
        get as getInstance;
    }

    const BACKUP_CACHE_WARNING_MESSAGE = 'The cache instance for NotiZ core could not be loaded. If this message is written often in your logs, your TYPO3 instance may suffer from performance issues.';

    /**
     * @var FrontendInterface
     */
    protected $cacheInstance;

    /**
     * @var CacheManager
     */
    protected $cacheManager;

    /**
     * @var LogManager
     */
    protected $logManager;

    /**
     * @param CacheManager $cacheManager
     * @param LogManager $logManager
     */
    public function __construct(CacheManager $cacheManager, LogManager $logManager)
    {
        $this->cacheManager = $cacheManager;
        $this->logManager = $logManager;
    }

    /**
     * Returns a cache value.
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->getCacheInstance()->get($key);
    }

    /**
     * Stores a value in the cache.
     *
     * @param string $key
     * @param mixed $data
     * @param array $tags
     * @return $this
     */
    public function set($key, $data, array $tags = [])
    {
        $this->getCacheInstance()->set($key, $data, $tags);

        return $this;
    }

    /**
     * Checks if an entry exists.
     *
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return $this->getCacheInstance()->has($key);
    }

    /**
     * Checks if the cache for this extension was registered in TYPO3 core
     * manager.
     *
     * @return bool
     */
    public function cacheIsRegistered()
    {
        return $this->cacheManager->hasCache(NotizConstants::CACHE_ID);
    }

    /**
     * Returns the extension cache frontend.
     *
     * If something was wrong during the cache registering and it is not found
     * in the cache manager, a backup cache is created to allow the extension
     * to run still.
     *
     * @return FrontendInterface
     */
    protected function getCacheInstance()
    {
        if (null === $this->cacheInstance) {
            $this->cacheInstance = $this->cacheManager->hasCache(NotizConstants::CACHE_ID)
                ? $this->cacheManager->getCache(NotizConstants::CACHE_ID)
                : $this->getBackupCache();
        }

        return $this->cacheInstance;
    }

    /**
     * Creates a transient memory cache, that will not allow true cache storage,
     * but will allow the extension to still use this service.
     *
     * This should not (or rarely) happen, so a warning is written in the logs
     * when this method is called.
     *
     * @return VariableFrontend
     */
    protected function getBackupCache()
    {
        $logger = $this->logManager->getLogger(__CLASS__);
        $logger->warning(self::BACKUP_CACHE_WARNING_MESSAGE);

        /** @var TransientMemoryBackend $backend */
        $backend = GeneralUtility::makeInstance(TransientMemoryBackend::class, 'production');

        /** @var VariableFrontend $frontend */
        $frontend = GeneralUtility::makeInstance(VariableFrontend::class, NotizConstants::CACHE_ID, $backend);

        return $frontend;
    }
}
