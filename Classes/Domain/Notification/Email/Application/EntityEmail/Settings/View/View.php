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

namespace CuyZ\Notiz\Domain\Notification\Email\Application\EntityEmail\Settings\View;

use CuyZ\Notiz\Definition\Tree\AbstractDefinitionComponent;
use CuyZ\Notiz\Exception\EntryNotFoundException;
use CuyZ\Notiz\View\ViewPathsAware;
use Romm\ConfigurationObject\Service\Items\DataPreProcessor\DataPreProcessor;
use Romm\ConfigurationObject\Service\Items\DataPreProcessor\DataPreProcessorInterface;

class View extends AbstractDefinitionComponent implements ViewPathsAware, DataPreProcessorInterface
{
    /**
     * @var \CuyZ\Notiz\Domain\Notification\Email\Application\EntityEmail\Settings\View\Layout[]
     */
    protected $layouts;

    /**
     * @var array
     */
    protected $layoutRootPaths;

    /**
     * @var array
     */
    protected $templateRootPaths;

    /**
     * @var array
     */
    protected $partialRootPaths;

    /**
     * @return Layout[]
     */
    public function getLayouts()
    {
        return $this->layouts;
    }

    /**
     * @param string $identifier
     * @return bool
     */
    public function hasLayout($identifier)
    {
        return true === isset($this->layouts[$identifier]);
    }

    /**
     * @param string $identifier
     * @return Layout
     *
     * @throws EntryNotFoundException
     */
    public function getLayout($identifier)
    {
        if (false === $this->hasLayout($identifier)) {
            throw EntryNotFoundException::entityEmailViewLayoutNotFound($identifier);
        }

        return $this->layouts[$identifier];
    }

    /**
     * @return array
     */
    public function getLayoutRootPaths()
    {
        return $this->layoutRootPaths;
    }

    /**
     * @return array
     */
    public function getTemplateRootPaths()
    {
        return $this->templateRootPaths;
    }

    /**
     * @return array
     */
    public function getPartialRootPaths()
    {
        return $this->partialRootPaths;
    }

    /**
     * Method called during the definition object construction: it allows
     * manipulating the data array before it is actually used to construct the
     * object.
     *
     * We use it to automatically fill the `identifier` property of the layouts
     * with the keys of the array.
     *
     * @param DataPreProcessor $processor
     */
    public static function dataPreProcessor(DataPreProcessor $processor)
    {
        self::forceIdentifierForProperty($processor, 'layouts');
    }
}
