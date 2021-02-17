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

namespace   Splash\Connectors\Optilog\Objects\Order;

use Splash\Bundle\Helpers\Objects\CachedListHelper;
use Splash\Connectors\Optilog\Models\RestHelper as API;
use Splash\Connectors\Optilog\Models\StatusHelper;
use stdClass;

/**
 * Optilog Products Objects List Functions
 */
trait ObjectsListTrait
{
    /**
     * {@inheritdoc}
     *
     * @note Order Listing NOW Always uses API V2.
     */
    public function objectsList($filter = null, $params = null)
    {
        //====================================================================//
        // Load Product Lists from Cache
        $rawData = API::postV2(
            "jGetStatutCommande",
            self::toParameters((string) $filter, (array) $params)
        );
        //====================================================================//
        // Request Failed
        if ((null == $rawData) || !isset($rawData->result)) {
            return array( 'meta' => array('current' => 0, 'total' => 0));
        }
        //====================================================================//
        // Compute Totals
        $response = array(
            'meta' => array(
                'current' => is_array($rawData->result) ? count($rawData->result) : 0,
                'total' => $rawData->pagination->TotalLignes,
            ),
        );
        //====================================================================//
        // Parse Data in response
        foreach ($rawData->result as $order) {
            $response[] = self::toOrderItem($order);
        }

        return $response;
    }

    /**
     * Get Object List from API V1
     *
     * @param null $filter
     * @param null $params
     *
     * @return array
     */
    public function getObjectsListV1($filter = null, $params = null): array
    {
        //====================================================================//
        // Check if Product Lists is Available in Cache
        $cachedList = new CachedListHelper($this->getWebserviceId(), "orders.list");
        if (!$cachedList->hasCache()) {
            //====================================================================//
            // Get Product Lists from Api
            $rawData = API::postV1("jGetStatutCommande", array(array("ID" => "*")));
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
                'id' => $order->DestID,
                'IntID' => $order->ID,
                'DestID' => $order->DestID,
                'Statut' => StatusHelper::toSplash($order->Statut),
                'Bordereau' => $order->Bordereau,
                'Commentaire' => $order->Commentaire,
            );
            /** @codingStandardsIgnoreEnd */
        }

        return $response;
    }

    /**
     * Prepare List Query Parameters
     *
     * @param string $filter
     * @param array  $params
     *
     * @return array
     */
    public function toParameters(string $filter, array $params): array
    {
        return array(array(
            "ID" => (string) $filter ?: "*",
            "Offset" => (isset($params["offset"]) && !empty($params["offset"])) ? (string) $params["offset"] : 0,
            "Fetch" => (isset($params["max"]) && !empty($params["max"])) ? (string) $params["max"] : 25,
        ));
    }

    /**
     * Parse Order Item to List Array
     *
     * @param stdClass $orderItem
     *
     * @return array
     */
    public function toOrderItem(stdClass $orderItem): array
    {
        /** @codingStandardsIgnoreStart */
        return array(
            'id' => $orderItem->DestID,
            'IntID' => $orderItem->ID,
            'DestID' => $orderItem->DestID,
            'Statut' => StatusHelper::toSplash($orderItem->IdStatut),
        );
        /** @codingStandardsIgnoreEnd */
    }
}
