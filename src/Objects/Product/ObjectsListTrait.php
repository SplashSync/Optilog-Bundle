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

use Splash\Client\Splash;
use Splash\Connectors\Optilog\Models\RestHelper as API;
use Splash\Connectors\Optilog\Objects\Core\PaginationTrait;

/**
 * Optilog Products Objects List Functions
 */
trait ObjectsListTrait
{
    use PaginationTrait;

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function objectsList(string $filter = null, array $params = array()): array
    {
        //====================================================================//
        // Get Lists from Api
        $rawResponse = API::post(
            "jGetStocks",
            self::toPageParameters((string) $filter, (array) $params)
        );
        //====================================================================//
        // Compute Totals
        $response = array(
            'meta' => self::toPageMetadata($rawResponse),
        );
        //====================================================================//
        // Request Failed
        if ((null == $rawResponse) || !isset($rawResponse->result) || !is_array($rawResponse->result)) {
            Splash::log()->www("Error response", $rawResponse);

            return $response;
        }
        //====================================================================//
        // Parse Data in response
        foreach ($rawResponse->result as $product) {
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
