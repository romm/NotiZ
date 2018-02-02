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

use CuyZ\Notiz\Exception\EntryNotFoundException;
use CuyZ\Notiz\Exception\InvalidTypeException;
use CuyZ\Notiz\Exception\NotizException;
use Romm\ConfigurationObject\Service\Items\MixedTypes\MixedTypesInterface;
use Romm\ConfigurationObject\Service\Items\MixedTypes\MixedTypesResolver;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\Error\Error;

/**
 * Resolver for the property:
 *
 * @see \CuyZ\Notiz\Definition\Tree\EventGroup\Event\EventDefinition::$connection
 */
class ConnectionResolver implements SingletonInterface, MixedTypesInterface
{
    /**
     * @var array
     */
    protected static $allowedTypes = [
        'signal' => Signal::TYPE_SIGNAL,
        'hook' => Signal::TYPE_HOOK,
    ];

    /**
     * Method used to fetch the type of the connection for an event. It can be
     * either a signal or a hook.
     *
     * @param MixedTypesResolver $resolver
     */
    public static function getInstanceClassName(MixedTypesResolver $resolver)
    {
        try {
            $connectionType = self::getConnectionType($resolver);
            $resolver->setObjectType(self::$allowedTypes[$connectionType]);
        } catch (NotizException $exception) {
            $error = new Error($exception->getMessage(), $exception->getCode());
            $resolver->addError($error);
        }
    }

    /**
     * The `type` property should be filled in the definition, with one of the
     * allowed values. If something is wrong, an error is sent to the resolver.
     *
     * @param MixedTypesResolver $resolver
     * @return string
     *
     * @throws EntryNotFoundException
     * @throws InvalidTypeException
     */
    protected static function getConnectionType(MixedTypesResolver $resolver)
    {
        $data = $resolver->getData();
        $data = is_array($data)
            ? $data
            : [];

        if (false === isset($data['type'])) {
            throw EntryNotFoundException::eventConnectionTypeMissing(array_keys(self::$allowedTypes));
        }

        $type = $data['type'];

        if (false === array_key_exists($type, self::$allowedTypes)) {
            throw InvalidTypeException::eventConnectionWrongType($type, array_keys(self::$allowedTypes));
        }

        return $type;
    }
}
