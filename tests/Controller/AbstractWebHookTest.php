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

namespace Splash\Connectors\Optilog\Test\Controller;

use Splash\Connectors\Optilog\Services\OptilogConnector;
use Splash\Tests\Tools\AbstractBaseCase;

/**
 * Base Class for WebHooks Tests of Optilog Connector
 */
abstract class AbstractWebHookTest extends AbstractBaseCase
{
    /**
     * Verify WebHook is Ok Response
     *
     * @return void
     */
    protected function assertOkResponse(): void
    {
        $response = $this->assertValidResponse();
        $this->assertEquals(1, $response["statut"], "Request Fail ".print_r($response, true));
    }

    /**
     * Verify WebHook is Ko Response
     *
     * @return void
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
     * Setup BrowserKit Server Headers
     *
     * @param OptilogConnector $connector
     */
    protected function setupHeaders(OptilogConnector $connector): void
    {
        //==============================================================================
        // Safety Check => API Key is Valid
        $apiKey = $connector->getParameter("ApiKey");
        $this->assertNotEmpty($apiKey);
        $this->assertIsString($apiKey);
        //==============================================================================
        // Setup Client
        $this->getTestClient()->setServerParameter("HTTP_Clef", $apiKey);
    }
}
