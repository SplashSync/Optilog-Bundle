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

namespace Splash\Connectors\Optilog\Objects\Order;

/**
 * Send Details of Orders Assets Fields
 */
trait AssetsTrait
{
    /**
     * Build Fields using FieldFactory
     */
    protected function buildAssetsFields(): void
    {
        //====================================================================//
        // Order Asset Files Names
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("name")
            ->name("Asset Name")
            ->microData("http://schema.org/DigitalDocument", "name")
            ->inList("assets")
            ->isWriteOnly()
        ;
        //====================================================================//
        // Order Asset Files Streams
        $this->fieldsFactory()->create(SPL_T_STREAM)
            ->identifier("file")
            ->name("Asset File")
            ->microData("http://schema.org/DigitalDocument", "description")
            ->inList("assets")
            ->isWriteOnly()
        ;
    }

    /**
     * Write Given Fields
     *
     * @param string               $fieldName Field Identifier / Name
     * @param array<string, array> $fieldData Field Data
     */
    protected function setAssetsFields(string $fieldName, array $fieldData): void
    {
        //====================================================================//
        // Safety Check
        if (("assets" !== $fieldName)) {
            return;
        }
        //====================================================================//
        // Walk on received Documents
        foreach ($fieldData as $itemData) {
            //====================================================================//
            // Add Asset to Documents
            $fileData = self::validateAsset($itemData);
            if ($fileData) {
                $this->addDocumentField($fileData["file"], $fileData["name"]);
            }
        }
        unset($this->in[$fieldName]);
    }

    /**
     * Validate Received Item data
     *
     * @param array $itemData Item Data
     *
     * @return null|array
     */
    private static function validateAsset(array $itemData): ?array
    {
        //====================================================================//
        // File Infos are Required
        if (!isset($itemData["file"]) || !is_array($itemData["file"])) {
            return null;
        }
        //====================================================================//
        // Detect file name
        if (empty($itemData["name"])) {
            $itemData["name"] = $itemData["file"]["name"] ?: $itemData["file"]["filename"];
        }

        return $itemData;
    }
}
