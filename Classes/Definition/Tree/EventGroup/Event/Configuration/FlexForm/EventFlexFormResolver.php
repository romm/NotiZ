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

namespace CuyZ\Notiz\Definition\Tree\EventGroup\Event\Configuration\FlexForm;

use CuyZ\Notiz\Definition\Tree\EventGroup\Event\Configuration\EventConfiguration;
use Romm\ConfigurationObject\Service\Items\MixedTypes\MixedTypesInterface;
use Romm\ConfigurationObject\Service\Items\MixedTypes\MixedTypesResolver;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * Resolver for the property:
 *
 * @see \CuyZ\Notiz\Definition\Tree\EventGroup\Event\Configuration\EventConfiguration::$flexForm
 */
class EventFlexFormResolver implements SingletonInterface, MixedTypesInterface
{
    /**
     * @param MixedTypesResolver $resolver
     */
    public static function getInstanceClassName(MixedTypesResolver $resolver)
    {
        $data = $resolver->getData();

        if (isset($data[EventConfiguration::PROVIDER_CLASS_NAME])) {
            $resolver->setObjectType($data[EventConfiguration::PROVIDER_CLASS_NAME]);
        }
    }
}
