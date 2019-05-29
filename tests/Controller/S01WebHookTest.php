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
use Splash\Tests\Tools\TestCase;

/**
 * Test of Optilog Connector WebHook Controller
 */
class S01WebHookTest extends TestCase
{
    const PING = 'HelloWorld';
    const CONNECT = 'HelloWorldSecure';

    /**
     * Test WebHook For Ping
     */
    public function testWebhookPing()
    {
        //====================================================================//
        // Load Connector
        $connector = $this->getConnector("optilog");
        $this->assertInstanceOf(OptilogConnector::class, $connector);

        //====================================================================//
        // Ping Action -> GET -> KO
        $this->assertPublicActionFail($connector, null, array(), "GET");
        //====================================================================//
        // Ping Action -> PUT -> KO
        $this->assertPublicActionFail($connector, null, array(), "PUT");

        //====================================================================//
        // Ping Action -> Empty Contents -> KO
        $this->assertPublicActionWorks($connector, null, array(), "POST");
        $this->assertKoResponse();

        //====================================================================//
        // Ping Action -> HelloWorld -> OK
        $this->assertPublicActionWorks($connector, null, array("Event" => self::PING), "POST");
        $this->assertOkResponse();
    }

    /**
     * Test WebHook For Connect
     */
    public function testWebhookConnect()
    {
        //====================================================================//
        // Load Connector
        $connector = $this->getConnector("optilog");
        $this->assertInstanceOf(OptilogConnector::class, $connector);

        //====================================================================//
        // Ping Action -> Without ApiKey -> KO
        $this->assertPublicActionWorks($connector, null, array("Event" => self::CONNECT), "POST");
        $this->assertKoResponse();

        //====================================================================//
        // Setup Headers
        $this->setupHeaders($connector);

        //====================================================================//
        // Ping Action -> With ApiKey -> OK
        $this->assertPublicActionWorks($connector, null, array("Event" => self::CONNECT), "POST");
        $this->assertOkResponse();
    }

    /**
     * Test WebHook with Errors
     */
    public function testWebhookErrors()
    {
        //====================================================================//
        // Load Connector
        $connector = $this->getConnector("optilog");
        $this->assertInstanceOf(OptilogConnector::class, $connector);
        $this->setupHeaders($connector);

        //====================================================================//
        // Empty Contents
        //====================================================================//

        $this->assertPublicActionWorks($connector, null, array(), "POST");
        $this->assertKoResponse();

        //====================================================================//
        // NULL EVENT
        //====================================================================//

        $this->assertPublicActionWorks($connector, null, array("Event" => null), "POST");
        $this->assertKoResponse();

        //====================================================================//
        // SCALAR EVENT
        //====================================================================//

        $this->assertPublicActionWorks($connector, null, array("Event" => "ShouldBeAnArray"), "POST");
        $this->assertKoResponse();
    }

    /**
     * Test WebHook Member Updates
     *
     * @dataProvider webHooksInputsProvider
     *
     * @param array  $data
     * @param string $objectType
     * @param string $action
     * @param string $objectId
     */
    public function testWebhookRequest(array $data, string $objectType, string $action, string $objectId)
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
    public function webHooksInputsProvider()
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
     * Verify WebHook is Ok Response
     */
    protected function assertOkResponse(): void
    {
        $response = $this->assertValidResponse();
        $this->assertEquals(1, $response["statut"], "Request Fail ".print_r($response, true));
    }

    /**
     * Verify WebHook is Ko Response
     */
    protected function assertKoResponse(): void
    {
        $response = $this->assertValidResponse();
        $this->assertEquals(0, $response["statut"]);
        $this->assertNotEmpty($response["statutText"], "Fails, but Not Error Message Provided");
    }

    /**
     * Verify WebHook Response is at Expected Format
     *
     * @return array
     */
    protected function assertValidResponse(): array
    {
        $raw = $this->getResponseContents();
        $this->assertIsString($raw);

        $response = (array) json_decode($raw);
        $this->assertIsArray($response);
        $this->assertArrayHasKey("statut", $response);

        return $response;
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

    /**
     * Setup BrowserKit Server Headers
     *
     * @param OptilogConnector $connector
     */
    private function setupHeaders(OptilogConnector $connector): void
    {
        //==============================================================================
        // Safety Check => API Key is Valid
        $apiKey = $connector->getParameter("ApiKey");
        $this->assertNotEmpty($apiKey);
        $this->assertIsString($apiKey);
        //==============================================================================
        // Setup Client
        $this->getClient()->setServerParameter("HTTP_Clef", $apiKey);
    }
}
