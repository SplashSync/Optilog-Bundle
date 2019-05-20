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

/**
 * Optilog Orders Objects Changes Tracking
 */
trait TrackerTrait
{
    /**
     * {@inheritdoc}
     */
    public function getTrackingDelay(): int
    {
        return $this->connector->isDebugMode() ? 1 : 60;
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedIds(): array
    {
        //====================================================================//
        // Check if Product Lists is Available in Cache
        $cachedList = new CachedListHelper($this->getWebserviceId(), "orders.status", 3600);
        $isCached = $cachedList->hasCache();
        //====================================================================//
        // Load Orders Previously Stored Status Lists from Cache
        $prevStatus = $cachedList->getContents();
        //====================================================================//
        // Get Order Statuses Lists from Api
        $newStatus = $this->getOrderStatus();
        if (null === $newStatus) {
            return array();
        }
        //====================================================================//
        // Store Status Lists in Cache
        $cachedList->setContents($newStatus);
        //====================================================================//
        // First run of Expired cache => Storage Only
        if (!$isCached) {
            return array();
        }

        return array_keys(array_diff($newStatus, $prevStatus));
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
    private function getOrderStatus(): ?array
    {
        $response = array();
        //====================================================================//
        // Get Orders Lists from Api
        $rawData = API::post("jGetStatutCommande", array(array("ID" => "*")));
        //====================================================================//
        // Request Failed
        if ((null == $rawData) || !isset($rawData->result)) {
            return null;
        }
        //====================================================================//
        // Parse Data in response
        foreach ($rawData->result as $order) {
            /** @codingStandardsIgnoreStart */
            //====================================================================//
            // Debug => Force Order Status
            if ($this->connector->isDebugMode() && $this->getParameter($order->ID, false, 'ForcedStatus')) {
                $order->Statut = $this->getParameter($order->ID, false, 'ForcedStatus');
            }
            $response[(string) $order->ID] = (int) $order->Statut;
            /** @codingStandardsIgnoreEnd */
        }

        return $response;
    }
}
