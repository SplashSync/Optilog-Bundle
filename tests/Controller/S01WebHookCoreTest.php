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
 * Test of Optilog Connector WebHook Core Features
 */
class S01WebHookCoreTest extends AbstractWebHookTest
{
    const PING = 'HelloWorld';
    const CONNECT = 'HelloWorldSecure';

    /**
     * Test WebHook For Ping
     *
     * @return void
     */
    public function testWebhookPing(): void
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
     *
     * @return void
     */
    public function testWebhookConnect(): void
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
     *
     * @return void
     */
    public function testWebhookErrors(): void
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
}
