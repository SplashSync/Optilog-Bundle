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

namespace Splash\Connectors\Optilog\Models;

use Splash\Core\SplashCore as Splash;
use Splash\Models\Objects\ObjectsTrait;

/**
 * Articles Helper: Validate & Parse Order Products Line Items
 */
class ArticlesHelper
{
    use ObjectsTrait;

    const EXTRA_INFOS = array(
        "IDunique", "Info1", "Info2", "Info3", "Info4",
    );

    /**
     * Validate & Convert Order Item Data to Optilog Article
     *
     * @param array $itemData Order Item data
     *
     * @return null|array
     */
    public static function toArticle(array $itemData): ?array
    {
        $article = array();
        //====================================================================//
        // Detect Product SKU
        $article["ID"] = self::detectProductSku($itemData);
        if (!$article["ID"]) {
            return null;
        }
        //====================================================================//
        // Detect Quantity
        $article["Quantite"] = self::detectQuantity($itemData);
        if (!$article["Quantite"]) {
            return null;
        }
        //====================================================================//
        // Detect Extra Informations
        foreach (self::EXTRA_INFOS as $infoKey) {
            self::addExtraInformation($article, $itemData, $infoKey);
        }

        return $article;
    }

    /**
     * Detect & Validate Order Item SKU
     *
     * @param array $itemData Order Item data
     *
     * @return null|string
     */
    private static function detectProductSku(array $itemData): ?string
    {
        //====================================================================//
        // IF Splash Product ID Given
        if (isset($itemData["ID"]) && !empty($itemData["ID"])) {
            //====================================================================//
            // Decode Product Id
            $productId = self::objects()->id($itemData["ID"]);
            if ($productId) {
                return $productId;
            }
            Splash::log()->warTrace("Invalid order Items SKU received");
        }
        //====================================================================//
        // IF Raw Product SKU Given
        if (isset($itemData["SKU"]) && !empty($itemData["SKU"])) {
            return (string) $itemData["SKU"];
        }
        Splash::log()->deb("Unable to Detect Order Items SKU");

        return null;
    }

    /**
     * Detect & Validate Order Item Quantity
     *
     * @param array $itemData Order Item data
     *
     * @return null|int
     */
    private static function detectQuantity(array $itemData): ?int
    {
        if (isset($itemData["Quantite"]) && !empty($itemData["Quantite"])) {
            return (int) $itemData["Quantite"];
        }
        Splash::log()->deb("Unable to Detect Order Items Quantity");

        return null;
    }

    /**
     * Detect & Validate Order Item Extra Informations
     *
     * @param array  $article  Target Article Infos
     * @param array  $itemData Order Item data
     * @param string $infoKey  Info Key
     *
     * @return void
     */
    private static function addExtraInformation(array &$article, array $itemData, string $infoKey): void
    {
        if (isset($itemData[$infoKey]) && !empty($itemData[$infoKey])) {
            $article[$infoKey] = (string) $itemData[$infoKey];
        }
    }
}
