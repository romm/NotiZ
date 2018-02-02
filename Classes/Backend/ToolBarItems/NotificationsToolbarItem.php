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

namespace CuyZ\Notiz\Backend\ToolBarItems;

use Exception;
use CuyZ\Notiz\Definition\DefinitionService;
use CuyZ\Notiz\Service\BackendUriBuilder;
use CuyZ\Notiz\Service\ExtensionConfigurationService;
use CuyZ\Notiz\Service\LocalizationService;
use CuyZ\Notiz\Service\ViewService;
use Throwable;
use TYPO3\CMS\Backend\Toolbar\ToolbarItemInterface;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Adds an item to the TYPO3 backend tool bar.
 */
class NotificationsToolbarItem implements ToolbarItemInterface
{
    /**
     * @var IconFactory
     */
    protected $iconFactory;

    /**
     * @var DefinitionService
     */
    protected $definitionService;

    /**
     * @var ExtensionConfigurationService
     */
    protected $extensionConfigurationService;

    /**
     * @var BackendUriBuilder
     */
    protected $backendUriBuilder;

    /**
     * @var ViewService
     */
    protected $viewService;

    /**
     * Manual dependency injection.
     */
    public function __construct()
    {
        /** @var ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        $this->iconFactory = GeneralUtility::makeInstance(IconFactory::class);
        $this->definitionService = $objectManager->get(DefinitionService::class);
        $this->extensionConfigurationService = $objectManager->get(ExtensionConfigurationService::class);
        $this->backendUriBuilder = $objectManager->get(BackendUriBuilder::class);
        $this->viewService = $objectManager->get(ViewService::class);
    }

    /**
     * @return bool
     */
    public function checkAccess()
    {
        return true;
    }

    /**
     * @return string
     */
    public function getItem()
    {
        return $this->getFluidTemplateObject('Backend/ToolBar/NotificationToolBarItem')->render();
    }

    /**
     * @return bool
     */
    public function hasDropDown()
    {
        return true;
    }

    /**
     * @return string
     */
    public function getDropDown()
    {
        $exception = null;
        $result = '';

        try {
            $result = $this->getDropDownFromDefinition();
        } catch (Throwable $exception) {
        } catch (Exception $exception) {
            // @PHP7
        }

        if ($exception) {
            $lllException = LocalizationService::localize('Backend/Toolbar/Error:exception');
            $lllExceptionCode = LocalizationService::localize('Backend/Toolbar/Error:exception.code');
            $lllExceptionMessage = LocalizationService::localize('Backend/Toolbar/Error:exception.message');

            $result = '<p class="text-danger">' . $lllException . '</p>
                <hr />
                <dl>';

            if ($exception->getCode()) {
                $result .= '<dt>' . $lllExceptionCode . '</dt>
                    <dd>' . $exception->getCode() . '</dd>';
            }

            $result .= '<dt>' . $lllExceptionMessage . '</dt>
                <dd>' . $exception->getMessage() . '</dd>
                </dl>';
        }

        return $result;
    }

    /**
     * @return string
     */
    protected function getDropDownFromDefinition()
    {
        $view = $this->getFluidTemplateObject('Backend/ToolBar/NotificationToolBarDropDown');

        $view->assign('showDefinitionUri', $this->backendUriBuilder->uriFor('showDefinition'));

        if (!$this->definitionService->getValidationResult()->hasErrors()) {
            $definition = $this->definitionService->getDefinition();
            $notifications = [];
            $total = 0;

            foreach ($definition->getNotifications() as $notification) {
                $number = $notification->getProcessor()->getTotalNumber();
                $total += $number;

                if ($number > 0) {
                    $notifications[] = $notification;
                }
            }

            $view->assign('definition', $definition);
            $view->assign('filledNotifications', $notifications);
            $view->assign('filledNotificationsTotal', $total);
        }

        return $view->render();
    }

    /**
     * @return array
     */
    public function getAdditionalAttributes()
    {
        return [];
    }

    /**
     * @return int
     */
    public function getIndex()
    {
        $index = $this->extensionConfigurationService->getConfigurationValue('toolbar.index');

        return max(min($index, 100), 0);
    }

    /**
     * @param string $templateName
     * @return StandaloneView
     */
    protected function getFluidTemplateObject($templateName)
    {
        $view = $this->viewService->getStandaloneView($templateName);

        $view->assign('result', $this->definitionService->getValidationResult());

        $legacyLayout = version_compare(VersionNumberUtility::getCurrentTypo3Version(), '8.0.0', '<');
        $view->assign('legacyLayout', $legacyLayout);

        return $view;
    }
}
