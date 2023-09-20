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
use Splash\Tests\Tools\Fields\OoFile;
use Splash\Tests\Tools\Fields\OoImage;
use Splash\Tests\Tools\Traits\SettingsTrait;

/**
 * Test of Optilog Connector WebHook Files Features
 */
class S03WebHookFilesTest extends AbstractTestCase
{
    use SettingsTrait;

    /**
     * Test WebHook Request
     *
     * @dataProvider webHooksInputsProvider
     *
     * @param array       $data
     * @param null|string $path
     * @param null|string $md5
     *
     * @return void
     */
    public function testWebhookRequest(array $data, ?string $path, ?string $md5): void
    {
        //====================================================================//
        // Load Connector
        $connector = $this->getConnector(self::SERVER_ID);
        $this->assertInstanceOf(OptilogConnector::class, $connector);
        $this->setupHeaders($connector);

        //====================================================================//
        // Execute Request
        $this->assertPublicActionWorks($connector, null, $data, "POST");

        //====================================================================//
        // Verify Wrong Requests Responses
        if (!$path || !$md5) {
            $this->assertKoResponse();

            return;
        }

        //====================================================================//
        // Verify Valid Requests Responses
        $response = $this->assertValidResponse();
        $this->assertNotEmpty($response["statutText"], "Not Answer Message Provided");
        $this->assertArrayHasKey("file", $response);

        //====================================================================//
        // Verify File Contents
        $fileContents = (array) $response["file"];
        $this->assertArrayHasKey("filename", $fileContents);
        $this->assertArrayHasKey("raw", $fileContents);
        $this->assertArrayHasKey("md5", $fileContents);
        $this->assertArrayHasKey("size", $fileContents);
        $this->assertEquals($fileContents["md5"], $md5);
        $this->assertEquals($fileContents["md5"], md5_file($path));
        $this->assertEquals(md5((string) base64_decode($fileContents["raw"], true)), $md5);
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
        // Generate Files Events
        for ($i = 0; $i < 5; $i++) {
            $hooks[] = self::buildFileEvent(OoFile::fake($this->settings), true, true);
            $hooks[] = self::buildFileEvent(OoFile::fake($this->settings), false, true);
            $hooks[] = self::buildFileEvent(OoFile::fake($this->settings), true, false);
            $hooks[] = self::buildFileEvent(OoFile::fake($this->settings), true, true);
            $hooks[] = self::buildFileEvent(OoFile::fake($this->settings), false, false);
        }
        //====================================================================//
        // Generate Images Events
        for ($i = 0; $i < 5; $i++) {
            $hooks[] = self::buildFileEvent(OoImage::fake($this->settings), true, true);
            $hooks[] = self::buildFileEvent(OoImage::fake($this->settings), false, true);
            $hooks[] = self::buildFileEvent(OoImage::fake($this->settings), true, false);
            $hooks[] = self::buildFileEvent(OoImage::fake($this->settings), true, true);
            $hooks[] = self::buildFileEvent(OoImage::fake($this->settings), false, false);
        }

        return $hooks;
    }

    /**
     * Generate Fake Event for WebHook Requests
     *
     * @param array $file
     * @param bool  $path
     * @param bool  $md5
     *
     * @return array
     */
    private static function buildFileEvent(array $file, bool $path, bool $md5): array
    {
        $data = array(
            "Type" => "FILE",
            "Path" => $path ? $file['path'] : "",
            "Md5" => $md5 ? $file['md5'] : "",
        );

        return array(
            array( "Event" => json_encode(array($data))),
            $path ? $file['path'] : null,
            $md5 ? $file['md5'] : null,
        );
    }
}
