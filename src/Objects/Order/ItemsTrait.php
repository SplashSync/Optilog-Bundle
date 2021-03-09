<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2021 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Splash\Connectors\Optilog\Objects\Order;

use Splash\Connectors\Optilog\Models\ArticlesHelper;
use Splash\Connectors\Optilog\Models\RestHelper as API;
use Splash\Core\SplashCore      as Splash;
use Splash\Models\Objects\ListsTrait;
use stdClass;

/**
 * Access to Orders Items Fields
 */
trait ItemsTrait
{
    use ListsTrait;

    /**
     * Build Fields using FieldFactory
     */
    protected function buildItemsFields(): void
    {
        //====================================================================//
        // Order Line Unique ID
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("IDunique")
            ->inList("lines")
            ->name("Line ID")
            ->microData("http://schema.org/partOfInvoice", "identifier")
            ->group("Products")
        ;
        self::setupReadOnlyOnV2($this->fieldsFactory());

        //====================================================================//
        // Order Line Product Identifier (Encoded SKU is Here)
        $this->fieldsFactory()->create((string) self::objects()->encode("Product", SPL_T_ID))
            ->identifier("ID")
            ->inList("lines")
            ->name("Product ID")
            ->microData("http://schema.org/Product", "productID")
            ->group("Products")
            ->isRequired(!$this->isProductRawSkuMode())
        ;
        self::setupReadOnlyOnV2($this->fieldsFactory());

        //====================================================================//
        // Order Line Product SKU (RAW SKU is Here)
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("SKU")
            ->inList("lines")
            ->name("Product SKU/EAN13")
            ->microData("http://schema.org/Product", "gint13")
            ->group("Products")
            ->isRequired($this->isProductRawSkuMode())
        ;
        self::setupReadOnlyOnV2($this->fieldsFactory());

        //====================================================================//
        // Order Line Quantity
        $this->fieldsFactory()->create(SPL_T_INT)
            ->Identifier("Quantite")
            ->InList("lines")
            ->Name("Quantity")
            ->MicroData("http://schema.org/QuantitativeValue", "value")
            ->Group("Products")
            ->isRequired()
        ;
        self::setupReadOnlyOnV2($this->fieldsFactory());

        //====================================================================//
        // Check if we are on API V2
        if (!API::isApiV2Mode()) {
            return;
        }

        //====================================================================//
        // Order Line Served Quantity
        $this->fieldsFactory()->create(SPL_T_INT)
            ->Identifier("Servie")
            ->InList("lines")
            ->Name("Shipped Qty")
            ->MicroData("http://schema.org/QuantitativeValue", "status")
            ->Group("Products")
            ->isReadOnly()
        ;
    }
    /**
     * Build Fields using FieldFactory
     */
    protected function buildItemsInfosFields(): void
    {
        //====================================================================//
        // Order Line Info 1 - Generally EAN13
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("Info1")
            ->InList("lines")
            ->Name("Info 1 (SKU)")
            ->MicroData("http://schema.org/Product", "sku")
            ->Group("Products")
        ;
        self::setupReadOnlyOnV2($this->fieldsFactory());

        //====================================================================//
        // Order Line Info 2
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("Info2")
            ->InList("lines")
            ->Name("Info 2 (Code)")
            ->MicroData("http://schema.org/Product", "additionalProperty")
            ->Group("Products")
        ;
        self::setupReadOnlyOnV2($this->fieldsFactory());

        //====================================================================//
        // Order Line Info 3 - Generally Label
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("Info3")
            ->InList("lines")
            ->Name("Info 3 (Label)")
            ->MicroData("http://schema.org/partOfInvoice", "description")
            ->Group("Products")
        ;
        self::setupReadOnlyOnV2($this->fieldsFactory());

        //====================================================================//
        // Order Line Info 4 - Generally Label
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("Info4")
            ->InList("lines")
            ->Name("Info 4")
            ->Group("Products")
        ;
        self::setupReadOnlyOnV2($this->fieldsFactory());
    }

    /**
     * Write Given Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param mixed  $fieldData Field Data
     */
    protected function setItemsFields($fieldName, $fieldData): void
    {
        //====================================================================//
        // Safety Check
        if (("lines" !== $fieldName)) {
            return;
        }
        //====================================================================//
        // TODO : FIX THIS!!!
        if (!empty(Splash::input('SPLASH_TRAVIS')) && ("NEW" != $this->object->Mode)) {
            unset($this->in[$fieldName]);

            return;
        }
        //====================================================================//
        // Init Articles List
        $this->object->Articles = array();
        //====================================================================//
        // Verify Lines List & Update if Needed
        foreach ($fieldData as $itemData) {
            //====================================================================//
            // Validate Inputs & Convert to Article Array
            $article = ArticlesHelper::toArticle($itemData);
            if (!$article) {
                continue;
            }
            //====================================================================//
            // Raw Product SKu Mode => Do NOT Merge Lines
            if (isset($itemData["SKU"]) && !empty($itemData["SKU"])) {
                $this->object->Articles[] = $article;

                continue;
            }
            //====================================================================//
            // Search for This Items in Products List
            $articleIndex = $this->searchItem($article["ID"]);
            if (null !== $articleIndex) {
                $this->object->Articles[$articleIndex]["Quantite"] += (int) $itemData["Quantite"];

                continue;
            }
            //====================================================================//
            // Add Product Line to List
            $this->object->Articles[] = $article;
        }

        unset($this->in[$fieldName]);
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     */
    protected function getItemsFields($key, $fieldName): void
    {
        //====================================================================//
        // Check if List field & Init List Array
        $fieldId = self::lists()->InitOutput($this->out, "lines", $fieldName);
        if (!$fieldId) {
            return;
        }
        //====================================================================//
        // Verify List is Not Empty
        if (!isset($this->object->Articles) || !is_array($this->object->Articles)) {
            return;
        }
        //====================================================================//
        // Fill List with Data
        foreach ($this->object->Articles as $index => $article) {
            //====================================================================//
            // READ Fields
            $value = $this->getItemFieldData($article, $fieldId);
            if (null === $value) {
                return;
            }
            //====================================================================//
            // Insert Data in List
            self::lists()->Insert($this->out, "lines", $fieldName, $index, $value);
        }

        unset($this->in[$key]);
    }

    /**
     * Search for Order Item in Articles
     *
     * @param string $productId Product SKU
     *
     * @return null|int
     */
    private function searchItem(string $productId): ?int
    {
        //====================================================================//
        // Safety Checks - Articles List if Empty
        if (!is_array($this->object->Articles)) {
            return null;
        }
        //====================================================================//
        // Walk on Articles List
        foreach ($this->object->Articles as $index => $item) {
            //====================================================================//
            // Same Articles SKU
            if ($item["ID"] == $productId) {
                return (int) $index;
            }
        }

        return null;
    }

    /**
     * Read Order Line Item Field Data
     *
     * @param stdClass $itemData Order Line ItemData
     * @param string   $fieldId  Field Identifier / Name
     *
     * @return null|int|string
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function getItemFieldData(stdClass $itemData, string $fieldId)
    {
        //====================================================================//
        // READ Fields
        switch ($fieldId) {
            //====================================================================//
            // Order Line Direct Reading Data
            case 'ID':
                return (!$this->isProductRawSkuMode() && !empty($itemData->ID))
                    ? (string) self::objects()->encode("Product", $itemData->ID)
                    : "";
            case 'SKU':
                return ($this->isProductRawSkuMode() && !empty($itemData->ID))
                    ? $itemData->ID
                    : "";
            case 'Quantite':
            case 'Servie':
                return isset($itemData->{$fieldId}) ? (int) $itemData->{$fieldId} : 0;
            case 'IDunique':
            case 'Info1':
            case 'Info2':
            case 'Info3':
            case 'Info4':
                return isset($itemData->{$fieldId}) ? (string) $itemData->{$fieldId} : "";
            default:
                return null;
        }
    }
}
