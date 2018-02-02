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

namespace CuyZ\Notiz\Domain\Notification\Email\Application\EntityEmail\Settings\GlobalRecipients;

use CuyZ\Notiz\Definition\Tree\AbstractDefinitionComponent;
use Romm\ConfigurationObject\Service\Items\DataPreProcessor\DataPreProcessor;
use Romm\ConfigurationObject\Service\Items\DataPreProcessor\DataPreProcessorInterface;

class GlobalRecipients extends AbstractDefinitionComponent implements DataPreProcessorInterface
{
    /**
     * @var \CuyZ\Notiz\Domain\Notification\Email\Application\EntityEmail\Settings\GlobalRecipients\Recipient[]
     */
    protected $recipients;

    /**
     * @return Recipient[]
     */
    public function getRecipients()
    {
        return $this->recipients;
    }

    /**
     * Each recipient is put inside an array to be able to process it in the
     * Recipient class.
     *
     * @see Recipient
     *
     * @param DataPreProcessor $processor
     */
    public static function dataPreProcessor(DataPreProcessor $processor)
    {
        $data = $processor->getData();
        $data = is_array($data)
            ? $data
            : [];

        $data = array_map(
            function ($item) {
                return ['recipient' => $item];
            },
            $data
        );

        $processor->setData([
            'recipients' => $data,
        ]);
    }
}
