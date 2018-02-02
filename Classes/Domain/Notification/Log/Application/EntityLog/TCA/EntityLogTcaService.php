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

namespace CuyZ\Notiz\Domain\Notification\Log\Application\EntityLog\TCA;

use CuyZ\Notiz\Notification\Service\NotificationTcaService;
use CuyZ\Notiz\Service\LocalizationService;
use Psr\Log\LogLevel;

class EntityLogTcaService extends NotificationTcaService
{
    /**
     * List all available log levels and stores them as an array to be used by
     * the TCA.
     *
     * @param array $parameters
     */
    public function getLogLevelsList(array &$parameters)
    {
        if ($this->definitionHasErrors()) {
            return;
        }

        foreach ($this->getLevels() as $level) {
            $parameters['items'][] = [strtoupper($level), $level];
        }
    }

    /**
     * @return string
     */
    public function getLogLevelsDescriptions()
    {
        $lll = 'Notification/Log/Entity';

        $levels = array_map(
            function ($level) use ($lll) {
                $level = strtoupper($level);

                $description = LocalizationService::localize("$lll:level.$level");

                return "<tr><td><strong>$level</strong></td><td>$description</td></tr>";
            },
            $this->getLevels()
        );

        $rows = implode('', $levels);

        $linkLabel = LocalizationService::localize("$lll:levels.more_info");

        return <<<HTML
<table class="table table-striped table-hover">
    <tbody>
        $rows
    </tbody>
</table>
<small>
    <a href="http://www.php-fig.org/psr/psr-3/#psrlogloglevel" target="_blank">$linkLabel</a>
</small>
HTML;
    }

    /**
     * @return array
     */
    private function getLevels()
    {
        return [
            LogLevel::DEBUG,
            LogLevel::INFO,
            LogLevel::NOTICE,
            LogLevel::WARNING,
            LogLevel::ERROR,
            LogLevel::CRITICAL,
            LogLevel::ALERT,
            LogLevel::EMERGENCY,
        ];
    }

    /**
     * @return string
     */
    protected function getNotificationIdentifier()
    {
        return 'entityLog';
    }
}
