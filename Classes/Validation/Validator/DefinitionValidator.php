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

namespace CuyZ\Notiz\Validation\Validator;

use CuyZ\Notiz\Channel\Channel;
use CuyZ\Notiz\Definition\Tree\Definition;
use CuyZ\Notiz\Exception\InvalidTypeException;
use CuyZ\Notiz\Exception\NotizException;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

/**
 * Will perform several validation operations across the whole definition tree.
 */
class DefinitionValidator extends AbstractValidator
{
    const CHANNEL_NOTIFICATION_NOT_SUPPORTED = 'The channel `%s` does not support notifications of type `%s`.';

    /**
     * @var Definition
     */
    protected $definition;

    /**
     * @param Definition $definition
     *
     * @throws InvalidTypeException
     */
    protected function isValid($definition)
    {
        if (!$definition instanceof Definition) {
            throw InvalidTypeException::definitionValidationWrongType($definition);
        }

        $this->definition = $definition;

        $this->channelsSupportNotifications();
    }

    /**
     * Checks that the channels used by the notifications actually support them.
     */
    protected function channelsSupportNotifications()
    {
        foreach ($this->definition->getNotifications() as $notification) {
            foreach ($notification->getChannels() as $channelDefinition) {
                $path = 'notifications.' . $notification->getIdentifier() . '.channels.' . $channelDefinition->getIdentifier();

                /** @var Channel $channelClassName */
                $channelClassName = $channelDefinition->getClassName();

                try {
                    $flag = $channelClassName::supportsNotification($notification);

                    if (false === $flag) {
                        $this->addPropertyError(
                            $path,
                            self::CHANNEL_NOTIFICATION_NOT_SUPPORTED,
                            1506449217,
                            [$channelClassName, $notification->getClassName()]
                        );
                    }
                } catch (NotizException $exception) {
                    $this->addPropertyError(
                        $path,
                        $exception->getMessage(),
                        $exception->getCode()
                    );
                }
            }
        }
    }

    /**
     * @param string $path
     * @param string $message
     * @param int $code
     * @param array $arguments
     */
    protected function addPropertyError($path, $message, $code, array $arguments = [])
    {
        $error = new Error($message, $code, $arguments);
        $this->result->forProperty($path)->addError($error);
    }
}
