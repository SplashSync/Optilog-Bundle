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
use Splash\Client\Splash;
use Splash\Connectors\Optilog\Models\RestHelper as API;
use Splash\Connectors\Optilog\Models\StatusHelper;
use Splash\Connectors\Optilog\Objects\Core\PaginationTrait;
use stdClass;

/**
 * Optilog Products Objects List Functions
 */
trait ObjectsListTrait
{
    use PaginationTrait;

    /**
     * {@inheritdoc}
     *
     * @note Order Listing NOW Always uses API V2.
     */
    public function objectsList(string $filter = null, array $params = array()): array
    {
        //====================================================================//
        // Load Product Lists from Cache
        $rawResponse = API::post(
            "jGetStatutCommande",
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
        foreach ($rawResponse->result as $order) {
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
            'Statut' => StatusHelper::toSplash(
                isset($orderItem->IdStatut) ? $orderItem->IdStatut : $orderItem->Statut
            ),
        );
        /** @codingStandardsIgnoreEnd */
    }
}
