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

namespace Splash\Connectors\Optilog\Test\Controller;

use Splash\Connectors\Optilog\Services\OptilogConnector;

/**
 * Test of Optilog Connector WebHook Objects Features
 */
class S02WebHookObjectsTest extends AbstractWebHookTest
{
    /**
     * Test WebHook Member Updates
     *
     * @dataProvider webHooksInputsProvider
     *
     * @param array  $data
     * @param string $objectType
     * @param string $action
     * @param string $objectId
     *
     * @return void
     */
    public function testWebhookRequest(array $data, string $objectType, string $action, string $objectId): void
    {
        //====================================================================//
        // Load Connector
        $connector = $this->getConnector("optilog");
        $this->assertInstanceOf(OptilogConnector::class, $connector);
        $this->setupHeaders($connector);

        //====================================================================//
        // Execute Request
        $this->assertPublicActionWorks($connector, null, $data, "POST");
        $this->assertOkResponse();

        //====================================================================//
        // Verify Response
        $this->assertIsLastCommited($action, $objectType, $objectId);
    }

    /**
     * Generate Fake Inputs for WebHook Requests
     *
     * @return array
     */
    public function webHooksInputsProvider(): array
    {
        $hooks = array();

        //====================================================================//
        // Generate Products Events
        for ($i = 0; $i < 10; $i++) {
            $productId = "SKU_".uniqid();
            $hooks[] = self::buildProductEvent(SPL_A_CREATE, SPL_A_CREATE, $productId);
            $hooks[] = self::buildProductEvent(SPL_A_UPDATE, SPL_A_UPDATE, $productId);
            $hooks[] = self::buildProductEvent(SPL_A_UPDATE, SPL_A_UPDATE, $productId);
            $hooks[] = self::buildProductEvent(SPL_A_UPDATE, SPL_A_UPDATE, $productId);
            $hooks[] = self::buildProductEvent(SPL_A_DELETE, SPL_A_DELETE, $productId);
        }
        //====================================================================//
        // Generate Orders Events
        for ($i = 0; $i < 10; $i++) {
            $orderId = "SKU_".uniqid();
            $hooks[] = self::buildOrderEvent(SPL_A_CREATE, SPL_A_CREATE, $orderId);
            $hooks[] = self::buildOrderEvent(SPL_A_UPDATE, SPL_A_UPDATE, $orderId);
            $hooks[] = self::buildOrderEvent(SPL_A_UPDATE, SPL_A_UPDATE, $orderId);
            $hooks[] = self::buildOrderEvent(SPL_A_UPDATE, SPL_A_UPDATE, $orderId);
            $hooks[] = self::buildOrderEvent(SPL_A_DELETE, SPL_A_DELETE, $orderId);
        }

        return $hooks;
    }

    /**
     * Generate Fake Event for WebHook Requests
     *
     * @param string $action
     * @param string $optAction
     * @param string $objectId
     *
     * @return array
     */
    private static function buildProductEvent(string $action, string $optAction, string $objectId): array
    {
        return array(
            array( "Event" => json_encode(array(array(
                "Type" => "STK",
                "Mode" => $optAction,
                "ID" => $objectId,
                "User" => "PhpUnit",
                "Comment" => "Arcticle - PhpUnit Local Testsuite Event",
            ), )), ),
            "Product",
            $action,
            $objectId,
        );
    }

    /**
     * Generate Fake Event for WebHook Requests
     *
     * @param string $action
     * @param string $optAction
     * @param string $objectId
     *
     * @return array
     */
    private static function buildOrderEvent(string $action, string $optAction, string $objectId): array
    {
        return array(
            array( "Event" => json_encode(array(array(
                "Type" => "CMD",
                "Mode" => $optAction,
                "ID" => "OPT_".$objectId,
                "DestID" => $objectId,
                "User" => "PhpUnit",
                "Comment" => "Commande - PhpUnit Local Testsuite Event",
            ), )), ),
            "Order",
            $action,
            $objectId,
        );
    }
}
