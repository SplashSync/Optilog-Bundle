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

namespace Splash\Connectors\Optilog\Models;

use Httpful\Exception\ConnectionErrorException;
use Httpful\Mime;
use Httpful\Request;
use Httpful\Response;
use SimpleXMLElement;
use Splash\Core\SplashCore as Splash;
use stdClass;

/**
 * Optilog Specific Helper
 *
 * Support for Managing Soap Transactions with Optilog Webservices...
 */
class RestHelper
{
    /**
     * @var int
     */
    const TIMEOUT = 30;

    /**
     * @var array
     */
    const ENDPOINTS = array(
        "Preprod V1" => "https://api.preprod.geolie.net/wsgestinbox.asmx",
        "Production V1" => "https://api.geolie.net/wsgestinbox.asmx",
        "Preprod V2" => "https://api.preprod.geolie.net/wsgestinbox_V2.asmx",
        "Production V2" => "https://api.geolie.net/wsgestinbox_V2.asmx",
    );

    /**
     * List of Message Parts that are no Real Errors
     *
     * @var array
     */
    const NOT_AN_ERROR = array(
        array("La commande", "est déjà validée"),
        array("La commande", "est en cours d'expédition !"),
        array("La commande", "est déjà en attente de validation"),
        array("La commande", "ne peut plus être modifiée car annulée !"),
        array("Commande", "déjà créée pour le destinataire"),
    );

    /**
     * Endpoint for Optilog Api
     *
     * @var string
     */
    private static string $endPoint;

    /**
     * Configure Optilog Soap API
     *
     * @param string $apiUrl
     * @param string $apiKey
     * @param string $apiUser
     * @param string $apiPwd
     *
     * @return bool
     */
    public static function configure(string $apiUrl, string $apiKey, string $apiUser, string $apiPwd): bool
    {
        //====================================================================//
        // Configure API Endpoint
        self::$endPoint = $apiUrl;
        //====================================================================//
        // Configure API Template Request
        $template = Request::init()
            ->sends(Mime::FORM)
            ->expects(Mime::JSON)
            ->autoParse(true)
            ->addHeaders(array(
                "Clef" => $apiKey,
                "UserName" => $apiUser,
                "Password" => $apiPwd,
            ))
            ->timeout(self::TIMEOUT);
        //====================================================================//
        // Set it as a template
        Request::ini($template);

        return true;
    }

    /**
     * Ping Optilog API as Anonymous User
     *
     * @return bool
     */
    public static function ping(): bool
    {
        //====================================================================//
        // Perform HelloWorld Test
        $response = self::get("jHelloWorld", array("Nom" => "SplashSync"));
        //====================================================================//
        // If Test Failed
        if (null === $response) {
            return false;
        }
        //====================================================================//
        // User Log message
        Splash::log()->msg("Hello World Succeeded on ".self::$endPoint);

        return true;
    }

    /**
     * Ping Optilog API Url with Security (Logged User)
     *
     * @return bool
     */
    public static function connect(): bool
    {
        //====================================================================//
        // Perform HelloWorld Test
        $response = self::get("jHelloWorldSecure", array("Nom" => "SplashSync"));
        //====================================================================//
        // If Test Failed
        if (null === $response) {
            return false;
        }
        //====================================================================//
        // User Log message
        Splash::log()->msg("Hello World Secured Succeeded on ".self::$endPoint);

        return true;
    }

    /**
     * Optilog API GET Request
     *
     * @param string $path API REST Path
     * @param array|null $body Request Data
     *
     * @return null|stdClass
     */
    public static function get(string $path, array $body = null): ?stdClass
    {
        //====================================================================//
        // Prepare Uri
        $uri = self::$endPoint."/".$path;
        if (!empty($body)) {
            $uri .= "?".http_build_query($body);
        }
        //====================================================================//
        // Perform Request
        try {
            $response = Request::get($uri)
                ->sendsType(Mime::PLAIN)
                ->send();
        } catch (ConnectionErrorException $ex) {
            Splash::log()->err($ex->getMessage());

            return null;
        }
        //====================================================================//
        // Catch Errors inResponse
        return self::catchErrors($response);
    }

    /**
     * Optilog API POST Request
     *
     * @param string         $path     API REST Path
     * @param array|stdClass $body     Request Data
     * @param null|string    $endPoint
     *
     * @return null|stdClass
     */
    public static function post(string $path, $body, string $endPoint = null): ?stdClass
    {
        //====================================================================//
        // Detect Endpoint Overrides
        $endPoint = $endPoint ?? self::$endPoint;
        //====================================================================//
        // Perform Request
        try {
            $response = Request::post($endPoint."/".$path)
                ->sends(Mime::FORM)
                ->expects(Mime::JSON)
                ->body(array("data" => json_encode($body)))
                ->send();
        } catch (ConnectionErrorException $ex) {
            Splash::log()->err($ex->getMessage());

            return null;
        }
        //====================================================================//
        // Catch Errors in Response
        return self::catchErrors($response);
    }

    /**
     * Optilog API V1 POST Request (Forced)
     *
     * @param string         $path API REST Path
     * @param array|stdClass $body Request Data
     *
     * @return null|stdClass
     */
    public static function postV1(string $path, $body): ?stdClass
    {
        //====================================================================//
        // Build EndPoint Url
        $endPoint = str_replace("wsgestinbox_V2.asmx", "wsgestinbox.asmx", self::$endPoint);
        //====================================================================//
        // Execute Post Request
        return self::post($path, $body, $endPoint);
    }

    /**
     * Optilog API V2 POST Request (Forced)
     *
     * @param string         $path API REST Path
     * @param array|stdClass $body Request Data
     *
     * @return null|stdClass
     */
    public static function postV2(string $path, $body): ?stdClass
    {
        //====================================================================//
        // Build EndPoint Url
        $endPoint = str_replace("wsgestinbox.asmx", "wsgestinbox_V2.asmx", self::$endPoint);
        //====================================================================//
        // Execute Post Request
        return self::post($path, $body, $endPoint);
    }

    //====================================================================//
    //  DEBUG FEATURES
    //====================================================================//

    /**
     * Check If Server is In Debug Mode
     *
     * @return bool
     */
    public static function isDebugMode() : bool
    {
        return (false !== strpos(self::$endPoint, "api.preprod.geolie.net"));
    }

    /**
     * Check If Server is API V2 Mode
     *
     * @return bool
     */
    public static function isApiV2Mode() : bool
    {
        return (false !== strpos(self::$endPoint, "wsgestinbox_V2"));
    }

    //====================================================================//
    //  PRIVATE METHODS
    //====================================================================//

    /**
     * Analyze Optilog Api Response & Push Errors to Splash Log
     *
     * @param Response $response
     *
     * @return null|stdClass Null if Errors Detected
     */
    private static function catchErrors(Response $response): ?stdClass
    {
        //====================================================================//
        // Check if Optilog Response has Errors
        if (!$response->hasBody()) {
            return null;
        }
        //====================================================================//
        // Decode Optilog Response
        $body = self::decodeBody($response->body);
        if (null === $body) {
            Splash::log()->err("Unable to decode response");
            Splash::log()->www("Full Response", $response);

            return null;
        }
        //====================================================================//
        // Detect Disabled Auto-parse
        if (!$response->request->auto_parse) {
            Splash::log()->err("Auto-parse is disabled. This should never happen.");
        }
        //====================================================================//
        // Check Response Message
        /** @codingStandardsIgnoreStart */
        if (isset($body->Message) && self::isAnError($body->Message)) {
            Splash::log()->err($body->Message);

            return null;
        }
        /** @codingStandardsIgnoreEnd */
        //====================================================================//
        // Check Response Status
        if (1 != $body->statut && self::isAnError($body->statutText)) {
            Splash::log()->err($body->statutText);
            self::collectErrorDetails($body);

            return null;
        }

        return $body;
    }

    /**
     * Analyze Optilog Api Error Message to Filter Known Errors
     *
     * @param string $message
     *
     * @return bool
     */
    private static function isAnError(string $message): bool
    {
        //====================================================================//
        // Walk on Known Responses
        foreach (self::NOT_AN_ERROR as $errorParts) {
            //====================================================================//
            // Walk on Known Responses Part to Identify
            if (self::isNotAnError($message, $errorParts)) {
                return false;
            }
        }

        //====================================================================//
        // Message is An Error
        return true;
    }

    /**
     * Check if Error Message is Known None Error Message
     *
     * @param string $message
     * @param array  $known
     *
     * @return bool
     */
    private static function isNotAnError(string $message, array $known): bool
    {
        //====================================================================//
        // Walk on Known Responses Part to Identify
        foreach ($known as $errorPart) {
            if (false === strpos($message, $errorPart)) {
                return false;
            }
        }
        //====================================================================//
        // Message is Not An Error
        return true;
    }

    /**
     * Decode Optilog Api Response to Std Object
     *
     * @param Response|SimpleXMLElement|stdClass $responseBody
     *
     * @return null|stdClass Null if Errors Detected
     */
    private static function decodeBody($responseBody): ?stdClass
    {
        if ($responseBody instanceof SimpleXMLElement) {
            /** @phpstan-ignore-next-line  */
            return json_decode($responseBody->__toString());
        }

        if (isset($responseBody->d)) {
            /** @phpstan-ignore-next-line  */
            return json_decode($responseBody->d);
        }

        if ($responseBody instanceof stdClass) {
            return $responseBody;
        }

        return null;
    }

    /**
     * Decode Optilog Api Error Details
     *
     * @param stdClass $responseBody
     *
     * @return void
     */
    private static function collectErrorDetails(stdClass $responseBody): void
    {
        //====================================================================//
        // Look for Error Details
        if (!isset($responseBody->result->Errors) || !is_iterable($responseBody->result->Errors)) {
            return;
        }
        //====================================================================//
        // Collect Error Details
        foreach ($responseBody->result->Errors as $errorDetails) {
            Splash::log()->err(sprintf(
                '[DETAILS][%s] %s => %s',
                $errorDetails->ID ?: "ID",
                $errorDetails->Mode ?: "Mode",
                $errorDetails->Errors ?: "Error Details Text",
            ));
        }
    }
}
