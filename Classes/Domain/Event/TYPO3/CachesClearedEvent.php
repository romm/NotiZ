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

namespace CuyZ\Notiz\Domain\Event\TYPO3;

use CuyZ\Notiz\Event\AbstractEvent;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * Event triggered when TYPO3 caches are cleared.
 */
class CachesClearedEvent extends AbstractEvent
{
    /**
     * @label Event/TYPO3/CacheCleared:marker.cache_command
     * @marker
     *
     * @var string
     */
    protected $cacheCommand;

    /**
     * @label Event/TYPO3/CacheCleared:marker.page_uid
     * @marker
     *
     * @var int
     */
    protected $pageUid;

    /**
     * @param array $parameters
     */
    public function run(array $parameters)
    {
        $this->cacheCommand = $parameters['cacheCmd'];

        if (empty($this->cacheCommand)) {
            $this->cancelDispatch();
        }

        if (MathUtility::canBeInterpretedAsInteger($this->cacheCommand)) {
            $this->pageUid = (int)$this->cacheCommand;
            $this->cacheCommand = 'page';
        }
    }
}
