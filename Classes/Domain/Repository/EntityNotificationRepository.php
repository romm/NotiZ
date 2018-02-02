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

namespace CuyZ\Notiz\Domain\Repository;

use CuyZ\Notiz\Definition\Tree\EventGroup\Event\EventDefinition;
use TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

class EntityNotificationRepository extends Repository
{
    /**
     * Initializes the query settings.
     */
    public function initializeObject()
    {
        /** @var $querySettings QuerySettingsInterface */
        $querySettings = $this->objectManager->get(QuerySettingsInterface::class);

        $querySettings->setRespectStoragePage(false);
        $querySettings->setRespectSysLanguage(false);

        $this->setDefaultQuerySettings($querySettings);
    }

    /**
     * @param EventDefinition $eventDefinition
     * @return QueryResultInterface
     */
    public function findFromEventDefinition(EventDefinition $eventDefinition)
    {
        $query = $this->createQuery();

        $query->matching(
            $query->equals('event', $eventDefinition->getFullIdentifier())
        );

        return $query->execute();
    }
}
