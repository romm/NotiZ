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

use CuyZ\Notiz\Support\NotizConstants;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

class ViewService implements SingletonInterface
{
    /**
     * @param string $templateName
     * @return StandaloneView
     */
    public function getStandaloneView($templateName)
    {
        /** @var StandaloneView $view */
        $view = GeneralUtility::makeInstance(StandaloneView::class);

        $view->setLayoutRootPaths([NotizConstants::VIEW_LAYOUTS_ROOT_PATH]);
        $view->setPartialRootPaths([NotizConstants::VIEW_PARTIALS_ROOT_PATH]);
        $view->setTemplateRootPaths([NotizConstants::VIEW_TEMPLATES_ROOT_PATH]);

        $view->setTemplate($templateName);

        $view->getRequest()->setControllerExtensionName(NotizConstants::EXTENSION_KEY);

        return $view;
    }
}
