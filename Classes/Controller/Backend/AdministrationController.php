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

namespace CuyZ\Notiz\Controller\Backend;

use Exception;
use CuyZ\Notiz\Definition\DefinitionService;
use CuyZ\Notiz\Definition\DefinitionTransformer;
use CuyZ\Notiz\Service\RuntimeService;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;

class AdministrationController extends ActionController
{
    /**
     * @var DefinitionService
     */
    protected $definitionService;

    /**
     * @var DefinitionTransformer
     */
    protected $definitionTransformer;

    /**
     * @var RuntimeService
     */
    protected $runtimeService;

    /**
     * Checking if the definition contains errors: if at least one is found, the
     * action is forwarded to `showDefinition` to inform the user of what is
     * wrong.
     */
    public function initializeAction()
    {
        if ($this->definitionService->getValidationResult()->hasErrors()
            && !in_array($this->request->getControllerActionName(), ['showDefinition', 'showException'])
        ) {
            $this->forward('showDefinition');
        }
    }

    /**
     * Adding default variables to the view for all actions.
     *
     * @param ViewInterface $view
     */
    public function initializeView(ViewInterface $view)
    {
        $view->assignMultiple([
            'result' => $this->definitionService->getValidationResult(),
            'request' => $this->request
        ]);
    }

    /**
     * Shows an interface where notifications can be added/edited.
     */
    public function indexAction()
    {
        // Incoming in stable release...
    }

    /**
     * Shows a tree for all definition values as well as errors and warnings
     * for every entry.
     */
    public function showDefinitionAction()
    {
        $this->view->assign('definition', $this->definitionTransformer->getDefinitionArray());
        $this->view->assign('exception', $this->runtimeService->getException());
    }

    /**
     * This action will be called if an exception was thrown during the building
     * of the definition. The exception will be displayed, using TYPO3 core
     * exception handling.
     *
     * @throws Exception
     */
    public function showExceptionAction()
    {
        $exception = $this->runtimeService->getException();

        if (!$exception) {
            $this->forward('showDefinition');
        }

        throw $exception;
    }

    /**
     * @param DefinitionService $definitionService
     */
    public function injectDefinitionService(DefinitionService $definitionService)
    {
        $this->definitionService = $definitionService;
    }

    /**
     * @param DefinitionTransformer $definitionTransformer
     */
    public function injectDefinitionTransformer(DefinitionTransformer $definitionTransformer)
    {
        $this->definitionTransformer = $definitionTransformer;
    }

    /**
     * @param RuntimeService $runtimeService
     */
    public function injectRuntimeService(RuntimeService $runtimeService)
    {
        $this->runtimeService = $runtimeService;
    }
}
