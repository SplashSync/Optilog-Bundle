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

namespace Splash\Connectors\Optilog\Objects\Product;

/**
 * Send Details of Products Images Fields
 */
trait ImagesTrait
{
    /**
     * Build Fields using FieldFactory
     */
    protected function buildImagesFields(): void
    {
        $groupName = "Photos";

        //====================================================================//
        // Product Images List
        $this->fieldsFactory()->create(SPL_T_IMG)
            ->identifier("image")
            ->inList("images")
            ->name("Images")
            ->group($groupName)
            ->microData("http://schema.org/Product", "image")
            ->isWriteOnly()
        ;
        //====================================================================//
        // Product Images => Is Visible Image
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->identifier("visible")
            ->inList("images")
            ->name("Visible")
            ->microData("http://schema.org/Product", "isVisibleImage")
            ->group($groupName)
            ->isWriteOnly()
            ->isNotTested()
        ;
    }

    /**
     * Write Given Fields
     *
     * @param string  $fieldName Field Identifier / Name
     * @param array[] $fieldData Field Data
     */
    protected function setImagesFields(string $fieldName, array $fieldData): void
    {
        if ("images" !== $fieldName) {
            return;
        }
        //====================================================================//
        // Walk on Images List
        foreach ($fieldData as $inValue) {
            //====================================================================//
            // Check Image Array is here
            if (empty($inValue["image"])) {
                continue;
            }
            //====================================================================//
            // Verify Images is Visible
            if (isset($inValue["visible"]) && empty($inValue["visible"])) {
                continue;
            }
            //====================================================================//
            // Add Images to Documents
            $this->addDocumentField($inValue["image"], self::$docTypeImage);
        }
        unset($this->in[$fieldName]);
    }
}
