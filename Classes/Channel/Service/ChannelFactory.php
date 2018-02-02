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

namespace CuyZ\Notiz\Channel\Service;

use CuyZ\Notiz\Channel\Channel;
use CuyZ\Notiz\Definition\Tree\Notification\Channel\ChannelDefinition;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;

class ChannelFactory implements SingletonInterface
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param ChannelDefinition $channelDefinition
     * @return Channel
     */
    public function create(ChannelDefinition $channelDefinition)
    {
        $settings = clone $channelDefinition->getSettings();

        /** @var Channel $channel */
        $channel = $this->objectManager->get($channelDefinition->getClassName(), $settings);

        return $channel;
    }
}
