<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Splash\Connectors\Optilog\Objects\Core;

use ArrayObject;

/**
 * Send Documents Fields
 */
trait DocumentsTrait
{
    /**
     * @var string
     */
    protected static string $docTypeInvoice = "FACTURE";

    /**
     * @var string
     */
    protected static string $docTypeDelivery = "BL";

    /**
     * @var string
     */
    protected static string $docTypeImage = "IMAGE";

    /**
     * Add a Document to Output Buffer
     *
     * @param mixed  $fileData File Field Data
     * @param string $fileType
     */
    protected function addDocumentField($fileData, string $fileType): void
    {
        //====================================================================//
        // Ensure Documents Exists
        if (!isset($this->object->Documents) || !is_array($this->object->Documents)) {
            $this->object->Documents = array();
        }
        //====================================================================//
        // Safety Check
        if (!is_array($fileData) && !($fileData instanceof ArrayObject)) {
            return;
        }
        //====================================================================//
        // Complete Infos
        $fileData["type"] = $fileType;
        //====================================================================//
        // Add Document
        $this->object->Documents[] = $fileData;
    }
}
