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

namespace CuyZ\Notiz\Definition\Tree\EventGroup\Event\Connection;

use Closure;
use CuyZ\Notiz\Definition\Tree\AbstractDefinitionComponent;
use CuyZ\Notiz\Event\Runner\EventRunner;
use CuyZ\Notiz\Event\Runner\EventRunnerContainer;
use CuyZ\Notiz\Exception\ClassNotFoundException;
use CuyZ\Notiz\Exception\WrongFormatException;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class Hook extends AbstractDefinitionComponent implements Connection
{
    const INTERNAL_HOOK_KEY = '__notiz';

    /**
     * @var string
     *
     * @validate NotEmpty
     */
    protected $path;

    /**
     * @var string
     *
     * @validate Romm.ConfigurationObject:ClassExists
     */
    protected $interface;

    /**
     * @var string
     *
     * @validate RegularExpression(regularExpression=/^[_a-zA-Z]+\w*$/)
     */
    protected $method;

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Registers the hook in TYPO3.
     *
     * Two registration methods exist. The first one is pretty clean, the other
     * probably already killed dozens of puppies.
     *
     * @param EventRunner $eventRunner
     */
    public function register(EventRunner $eventRunner)
    {
        if ($this->hookIsRegistered()) {
            return;
        }

        if (!empty($this->interface)
            || !empty($this->method)
        ) {
            $closure = $this->preventEvalNeverIdealStuff($eventRunner);
        } else {
            $closure = function () use ($eventRunner) {
                return call_user_func_array($eventRunner->getCallable(), func_get_args());
            };
        }

        $this->injectHookInGlobalArray($closure);

        if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '8.0.0', '<')) {
            $this->injectHookInFrontendController($closure);
        }
    }

    /**
     * @param Closure|string $closure
     */
    protected function injectHookInGlobalArray($closure)
    {
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'] = ArrayUtility::setValueByPath(
            $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'],
            $this->getFullPath(),
            $closure,
            '|'
        );
    }

    /**
     * @param Closure|string $closure
     *
     * @deprecated Must be removed when TYPO3 v7 is not supported anymore.
     */
    protected function injectHookInFrontendController($closure)
    {
        $tsfe = $this->getTypoScriptFrontendController();

        if ($tsfe) {
            $tsfe->TYPO3_CONF_VARS['SC_OPTIONS'] = ArrayUtility::setValueByPath(
                $tsfe->TYPO3_CONF_VARS['SC_OPTIONS'],
                $this->getFullPath(),
                $closure,
                '|'
            );
        }
    }

    /**
     * This method is called when a hook is bound to a class that must implement
     * an interface.
     *
     * In this case, to keep the handling we need to dynamically create some
     * proxy class that will call the internal API itself.
     *
     * If you don't want to lose your faith in humanity, you probably should not
     * read the code below. Really.
     *
     * @param EventRunner $eventRunner
     * @return string
     *
     * @throws ClassNotFoundException
     * @throws WrongFormatException
     */
    protected function preventEvalNeverIdealStuff(EventRunner $eventRunner)
    {
        $className = 'notiz_hook_' . sha1($eventRunner->getEventDefinition()->getFullIdentifier());

        $implements = $this->interface
            ? 'implements ' . $this->interface
            : '';

        $method = $this->method ?: 'run';

        if ($this->interface
            && !interface_exists($this->interface)
        ) {
            throw ClassNotFoundException::eventHookInterfaceNotFound($this->interface, $this);
        }

        if (!preg_match('/^[_a-zA-Z]+\w*$/', $method)) {
            throw WrongFormatException::eventHookMethodNameWrongFormat($method, $this);
        }

        $classPhpCode = $this->anotherNonUsefulSystem($className, $implements, $method, $eventRunner);

        // Please lord, forgive me.
        eval($classPhpCode);

        return $className;
    }

    /**
     * @see \CuyZ\Notiz\Definition\Tree\EventGroup\Event\Connection\Hook::preventEvalNeverIdealStuff
     *
     * @param string $className
     * @param string $implements
     * @param string $method
     * @param EventRunner $eventRunner
     * @return string
     */
    protected function anotherNonUsefulSystem($className, $implements, $method, EventRunner $eventRunner)
    {
        $eventRunnerContainerClass = EventRunnerContainer::class;

        return <<<PHP
class $className $implements
{
    public function $method(...\$arguments)
    {
        \$eventRunner = $eventRunnerContainerClass::getInstance()->get('{$eventRunner->getEventDefinition()->getFullIdentifier()}');
         
        call_user_func_array(\$eventRunner->getCallable(), \$arguments);       
    }
}
PHP;
    }

    /**
     * @return bool
     */
    protected function hookIsRegistered()
    {
        return ArrayUtility::isValidPath($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'], $this->getFullPath(), '|');
    }

    /**
     * @return string
     */
    protected function getFullPath()
    {
        return $this->path . '|' . self::INTERNAL_HOOK_KEY;
    }

    /**
     * @return TypoScriptFrontendController
     */
    protected function getTypoScriptFrontendController()
    {
        return $GLOBALS['TSFE'];
    }
}
