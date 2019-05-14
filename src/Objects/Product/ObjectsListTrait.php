<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2019 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace   Splash\Connectors\Optilog\Objects\Product;

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
        // Get Product Lists from Api
        $rawData = API::getWithData("GetStocks", array(array("ID" => "*")));
        //====================================================================//
        // Request Failed
        if ((null == $rawData) || !isset($rawData->result)) {
            return array( 'meta' => array('current' => 0, 'total' => 0));
        }
        //====================================================================//
        // Compute Totals
        $response = array(
            'meta' => array('current' => count($rawData->result), 'total' => $rawData->result),
        );
        
        //====================================================================//
        // Parse Data in response
        foreach ($rawData->result as $product) {
            /** @codingStandardsIgnoreStart */            
            $response[] = array(
                'id' => $product->ID,
                'sku' => $product->ID,
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
