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
use CuyZ\Notiz\Service\StringService;
use Romm\ConfigurationObject\Service\Items\DataPreProcessor\DataPreProcessor;
use Romm\ConfigurationObject\Service\Items\DataPreProcessor\DataPreProcessorInterface;
use Romm\ConfigurationObject\Traits\ConfigurationObject\StoreArrayIndexTrait;

class Recipient extends AbstractDefinitionComponent implements DataPreProcessorInterface
{
    use StoreArrayIndexTrait;

    const IDENTIFIER_PREFIX = '__NOTIZ_GLOBAL_RECIPIENT_';

    /**
     * @var string
     *
     * @validate NotEmpty
     * @validate EmailAddress
     */
    protected $email;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $rawValue;

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name ?: $this->email;
    }

    /**
     * @return string
     */
    public function getRawValue()
    {
        return $this->rawValue;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return self::IDENTIFIER_PREFIX . $this->getArrayIndex();
    }

    /**
     * The given recipient is parsed to extract the name and the email.
     * Two format are supported:
     *
     * `John Smith <john.smith@example.com>`
     * `jane.smith@example.com`
     *
     * @param DataPreProcessor $processor
     */
    public static function dataPreProcessor(DataPreProcessor $processor)
    {
        $data = $processor->getData();
        $data = is_array($data)
            ? $data
            : [];

        $formattedEmail = StringService::get()->formatEmailAddress($data['recipient']);

        $processor->setData([
            'email' => $formattedEmail['email'],
            'name' => $formattedEmail['name'],
            'rawValue' => $data['recipient'],
        ]);
    }
}
