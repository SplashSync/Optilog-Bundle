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

namespace   Splash\Connectors\Optilog\Objects\Product;

use Splash\Bundle\Helpers\Objects\CachedListHelper;
use Splash\Connectors\Optilog\Models\RestHelper as API;

/**
 * Optilog Products Objects List Functions
 */
trait ObjectsListTrait
{
    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function objectsList($filter = null, $params = null)
    {
        //====================================================================//
        // Check if Product Lists is Available in Cache
        $cachedList = new CachedListHelper($this->getWebserviceId(), "products.list");
        if (!$cachedList->hasCache()) {
            //====================================================================//
            // Get Product Lists from Api
            $rawData = API::post("jGetStocks", array(array("ID" => "*")));
            //====================================================================//
            // Request Failed
            if ((null == $rawData) || !isset($rawData->result)) {
                return array( 'meta' => array('current' => 0, 'total' => 0));
            }
            //====================================================================//
            // Store Product Lists in Cache
            $cachedList->setContents($rawData->result);
        }
        //====================================================================//
        // Load Product Lists from Cache
        $listData = $cachedList->getPagedContents($filter, $params);
        //====================================================================//
        // Compute Totals
        $response = array(
            'meta' => array('current' => count($listData), 'total' => $cachedList->getFilteredTotal()),
        );
        //====================================================================//
        // Parse Data in response
        foreach ($listData as $product) {
            /** @codingStandardsIgnoreStart */
            $response[] = array(
                'id' => $product->ID,
                'sku' => $product->ID,
                'Stock' => $product->Stock,
                'Libelle' => $product->Libelle,
                'Stk_Dispo' => $product->Stk_Dispo,
                'Stk_Physique' => $product->Stk_Physique,
                'IsActif' => $product->IsActif,
            );
            /** @codingStandardsIgnoreEnd */
        }

        return $response;
    }
}
