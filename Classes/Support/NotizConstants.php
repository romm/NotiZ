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

namespace CuyZ\Notiz\Support;

/**
 * This class is a collection of constants specific to this extension
 */
final class NotizConstants
{
    /**
     * The extension key.
     */
    const EXTENSION_KEY = 'notiz';

    /**
     * Path to the extension icons files.
     */
    const EXTENSION_ICON_PATH = 'EXT:' . self::EXTENSION_KEY . '/Resources/Public/Icon/';

    const EXTENSION_ICON_DEFAULT = self::EXTENSION_ICON_PATH . 'notiz-icon.svg';

    const EXTENSION_ICON_MAIN_MODULE_PATH = self::EXTENSION_ICON_PATH . 'notiz-icon-main-module.svg';

    const EXTENSION_ICON_MODULE_PATH = self::EXTENSION_ICON_PATH . 'notiz-icon-module.svg';

    /**
     * View paths.
     */
    const VIEW_LAYOUTS_ROOT_PATH = 'EXT:' . self::EXTENSION_KEY . '/Resources/Private/Layouts/';

    const VIEW_PARTIALS_ROOT_PATH = 'EXT:' . self::EXTENSION_KEY . '/Resources/Private/Partials/';

    const VIEW_TEMPLATES_ROOT_PATH = 'EXT:' . self::EXTENSION_KEY . '/Resources/Private/Templates/';

    /**
     * Root path to TypoScript files.
     */
    const TYPOSCRIPT_PATH = 'EXT:' . NotizConstants::EXTENSION_KEY . '/Configuration/TypoScript/';

    /**
     * Used to retrieve the FrontendCache instance.
     */
    const CACHE_ID = 'notiz';

    /**
     * Cache key used by the `configuration_object` API for NotiZ.
     */
    const CACHE_KEY_DEFINITION_OBJECT = 'notiz_definition_object';

    /**
     * Root node for all definitions.
     */
    const DEFINITION_ROOT_PATH = 'config.tx_' . self::EXTENSION_KEY;

    /**
     * Identifier for the administration backend module. Can be used for:
     *
     * @see \TYPO3\CMS\Backend\Utility\BackendUtility::getModuleUrl
     */
    const BACKEND_MODULE_ADMINISTRATION = 'NotizNotiz_NotizNotizAdministration';

    /**
     * The default format for event markers.
     *
     * @see \CuyZ\Notiz\Event\Event
     */
    const DEFAULT_MARKER_FORMAT = '{%s}';
}
