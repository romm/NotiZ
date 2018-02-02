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
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Extensionmanager\Utility\ListUtility;

/**
 * Event triggered when an extension is installed via the extension manager.
 */
class ExtensionInstalledEvent extends AbstractEvent
{
    /**
     * @label Event/TYPO3/ExtensionInstalled:marker.key
     * @marker
     *
     * @var string
     */
    protected $key;

    /**
     * @label Event/TYPO3/ExtensionInstalled:marker.title
     * @marker
     *
     * @var string
     */
    protected $title;

    /**
     * @label Event/TYPO3/ExtensionInstalled:marker.description
     * @marker
     *
     * @var string
     */
    protected $description;

    /**
     * @label Event/TYPO3/ExtensionInstalled:marker.version
     * @marker
     *
     * @var string
     */
    protected $version;

    /**
     * @var ListUtility
     */
    protected $listUtility;

    /**
     * @param string $extensionKey
     */
    public function run($extensionKey)
    {
        $extension = $this->getExtensionData($extensionKey);

        $this->key = $extensionKey;
        $this->title = $extension['title'];
        $this->version = $extension['version'];
        $this->description = $extension['description'];
    }

    /**
     * @param string $extensionKey
     * @return array
     */
    protected function getExtensionData($extensionKey)
    {
        $extensionPackage = $this->listUtility->getExtension($extensionKey);

        $data = [
            'siteRelPath' => PathUtility::getRelativePath(PATH_site, $extensionPackage->getPackagePath()),
            'key' => $extensionKey,
        ];

        $extensionsInformation = $this->listUtility->enrichExtensionsWithEmConfInformation([$extensionKey => $data]);

        return $extensionsInformation[$extensionKey];
    }

    /**
     * @param ListUtility $listUtility
     */
    public function injectListUtility(ListUtility $listUtility)
    {
        $this->listUtility = $listUtility;
    }
}
