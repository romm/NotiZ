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

use CuyZ\Notiz\Exception\EntryNotFoundException;
use CuyZ\Notiz\Support\NotizConstants;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility;

/**
 * Service to ease the reading of the extension configuration written in the
 * file `ext_conf_template.txt`.
 */
class ExtensionConfigurationService implements SingletonInterface
{
    /**
     * @var array[]
     */
    protected $configuration;

    /**
     * @param ConfigurationUtility $configurationUtility
     */
    public function __construct(ConfigurationUtility $configurationUtility)
    {
        $this->configuration = $configurationUtility->getCurrentConfiguration(NotizConstants::EXTENSION_KEY);
    }

    /**
     * @param string $key
     * @return array
     *
     * @throws EntryNotFoundException
     */
    public function getConfiguration($key)
    {
        if (!isset($this->configuration[$key])) {
            throw EntryNotFoundException::extensionConfigurationEntryNotFound($key);
        }

        return $this->configuration[$key];
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function getConfigurationValue($key)
    {
        $configuration = $this->getConfiguration($key);

        return $configuration['value'];
    }
}
