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

namespace   Splash\Connectors\Optilog\Objects\Product;

use Splash\Bundle\Helpers\Objects\CachedListHelper;
use Splash\Connectors\Optilog\Models\RestHelper as API;

/**
 * Optilog Products Objects Changes Tracking
 */
trait TrackerTrait
{
    /**
     * {@inheritdoc}
     */
    public function getTrackingDelay(): int
    {
        return $this->connector->isDebugMode() ? 1 : 10;
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedIds(): array
    {
        //====================================================================//
        // Check if Product Lists is Available in Cache
        $cachedList = new CachedListHelper($this->getWebserviceId(), "products.stocks", 3600);
        $isCached = $cachedList->hasCache();
        //====================================================================//
        // Load Product Previously Stored Stocks Lists from Cache
        $prevStocks = $cachedList->getContents();
        //====================================================================//
        // Get Product Stocks Lists from Api
        $newStocks = $this->getProductsStocks();
        if (null === $newStocks) {
            return array();
        }
        //====================================================================//
        // Store Product Lists in Cache
        $cachedList->setContents($newStocks);
        //====================================================================//
        // First run of Expired cache => Storage Only
        if (!$isCached) {
            return array();
        }

        return array_keys(array_diff($newStocks, $prevStocks));
    }

    /**
     * {@inheritdoc}
     */
    public function getDeletedIds(): array
    {
        return array();
    }

    /**
     * Read List of Stocks for Whole Available Products
     *
     * @return null|array
     */
    private function getProductsStocks(): ?array
    {
        $response = array();
        //====================================================================//
        // Get Product Lists from Api
        $rawData = API::post("jGetStocks", array(array("ID" => "*")));
        //====================================================================//
        // Request Failed
        if ((null == $rawData) || !isset($rawData->result)) {
            return null;
        }
        //====================================================================//
        // Parse Data in response
        foreach ($rawData->result as $product) {
            /** @codingStandardsIgnoreStart */
            //====================================================================//
            // Debug => Random Stocks
            if ($this->connector->isDebugMode() && $this->getParameter($product->ID, false, 'RandomStocks')) {
                $product->Stk_Dispo = rand(10, 100);
            }
            $response[(string) $product->ID] = (int) $product->Stk_Dispo;
            /** @codingStandardsIgnoreEnd */
        }

        return $response;
    }
}
