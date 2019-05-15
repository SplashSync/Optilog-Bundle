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

namespace   Splash\Connectors\Optilog\Objects\Order;

use Splash\Bundle\Helpers\Objects\CachedListHelper;
use Splash\Connectors\Optilog\Models\RestHelper as API;
use Splash\Connectors\Optilog\Models\StatusCodes;

/**
 * Optilog Products Objects List Functions
 */
trait ObjectsListTrait
{
    /**
     * {@inheritdoc}
     */
    public function objectsList($filter = null, $params = null)
    {
        //====================================================================//
        // Check if Product Lists is Available in Cache
        $cachedList = new CachedListHelper($this->getWebserviceId(), "orders.list");
        if (!$cachedList->hasCache()) {
            //====================================================================//
            // Get Product Lists from Api
            $rawData = API::post("jGetStatutCommande", array(array("ID" => "*")));
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
        foreach ($listData as $order) {
            /** @codingStandardsIgnoreStart */
            $response[] = array(
                'id' => $order->ID,
                'IntID' => $order->ID,
                'DestID' => $order->DestID,
                'Statut' => StatusCodes::SPLASH[$order->Statut],
                'Bordereau' => $order->Bordereau,
                'Commentaire' => $order->Commentaire,
            );
            /** @codingStandardsIgnoreEnd */
        }

        return $response;
    }
}
