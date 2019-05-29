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

namespace Splash\Connectors\Optilog\Controller;

use Splash\Bundle\Models\AbstractConnector;
use Splash\Client\Splash;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Splash Optilog WebHooks Actions Controller
 */
class WebHooksController extends Controller
{
    /**
     * List of Available Action Types
     *
     * @var array
     */
    const ACTIONS = array(SPL_A_CREATE, SPL_A_UPDATE, SPL_A_DELETE);

    /**
     * List of Available Objects Types
     *
     * @var array
     */
    const OBJECTS = array(
        "STK" => "Product",
        "CMD" => "Order",
    );

    /**
     * Id Key for Objects Types
     *
     * @var array
     */
    const IDS = array(
        "STK" => "ID",
        "CMD" => "DestID",
    );

    /**
     * @var null|array
     */
    private $events;

    /**
     * @var int
     */
    private $commited = 0;

    //====================================================================//
    //  Optilog WEBHOOKS MANAGEMENT
    //====================================================================//

    /**
     * Execute WebHook Actions for A Optilog Connector
     *
     * @param Request           $request
     * @param AbstractConnector $connector
     *
     * @return JsonResponse
     *
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function indexAction(Request $request, AbstractConnector $connector)
    {
        //====================================================================//
        // Validate Request Parameters
        $validate = $this->validateRequest($connector, $request);
        if ($validate instanceof JsonResponse) {
            return $validate;
        }

        //==============================================================================
        // Commit Changes
        $this->executeCommits($connector);

        return self::buildResponse(true, 'Notified '.$this->commited.' Changes');
    }

    /**
     * Execute Changes Commits
     *
     * @param AbstractConnector $connector
     */
    private function executeCommits(AbstractConnector $connector) : void
    {
        //==============================================================================
        // Check Infos are Available
        if (empty($this->events) || !is_array($this->events)) {
            return;
        }
        //==============================================================================
        // Loop On Events
        foreach ($this->events as $event) {
            //==============================================================================
            // Validate & Decode Event
            $decoded = $this->decodeEvent($event);
            if (null == $decoded) {
                var_dump($event);

                continue;
            }
            //==============================================================================
            // Commit Changes to Splash
            $connector->commit(
                $decoded["objectType"],
                $decoded["objectId"],
                $decoded["action"],
                $decoded["user"],
                $decoded["comment"]
            );
            $this->commited++;
        }
    }

    /**
     * Validate Request Parameters
     *
     * @param AbstractConnector $connector
     * @param Request           $request
     *
     * @throws BadRequestHttpException
     *
     * @return null|JsonResponse
     */
    private function validateRequest(AbstractConnector $connector, Request $request): ?JsonResponse
    {
        //==============================================================================
        // Safety Check => Only POST Method is Allowed
        if (!$request->isMethod('POST')) {
            throw new BadRequestHttpException('Malformatted or missing data');
        }
        //==============================================================================
        // Safety Check => Data are here
        $eventData = $request->request->get("Event");
        if (empty($eventData) || !is_scalar($eventData)) {
            return self::buildResponse(false, 'Malformatted or Missing Data');
        }
        //==============================================================================
        // Unsecured Ping => Return Ok
        if ("HelloWorld" == $eventData) {
            return self::buildResponse(true, 'Hello World');
        }
        //==============================================================================
        // Safety Check => API Keys are Valid
        if (null != $this->validateApiKey($connector, $request)) {
            return $this->validateApiKey($connector, $request);
        }
        //==============================================================================
        // Secured Ping => Return Ok
        if ("HelloWorldSecure" == $eventData) {
            return self::buildResponse(true, 'Hello Optilog !!');
        }
        //==============================================================================
        // Safety Check => Event Data is An Array
        $unserilizedData = json_decode((string) $eventData, true);
        if (!is_array($unserilizedData)) {
            return self::buildResponse(false, 'Unable to Deserialize Data');
        }
        //==============================================================================
        // Request is Valid => Store Received Data
        $this->events = $unserilizedData;

        return null;
    }

    /**
     * Validate Request Parameters
     *
     * @param AbstractConnector $connector
     * @param Request           $request
     *
     * @throws BadRequestHttpException
     *
     * @return null|JsonResponse
     */
    private function validateApiKey(AbstractConnector $connector, Request $request): ?JsonResponse
    {
        //==============================================================================
        // Safety Check => API Keys are Here & Valid
        $connectorApiKey = $connector->getParameter("ApiKey");
        $requestApiKey = $request->headers->get("Clef");
        if (empty($requestApiKey) || empty($connectorApiKey)) {
            return self::buildResponse(false, 'Connection Refused');
        }
        //==============================================================================
        // Safety Check => API Key are Similar
        if ($requestApiKey != $connectorApiKey) {
            return self::buildResponse(false, 'Connection Refused');
        }

        return null;
    }

    /**
     * Validate & Decode Request Event Item
     *
     * @param array $event
     *
     * @return null|array
     */
    private function decodeEvent(array $event): ?array
    {
        //==============================================================================
        // Build Commit Contents
        $response = array(
            //==============================================================================
            // Extract Object Type & ID
            "action" => self::getEventType($event),
            "objectType" => self::getObjectType($event),
            "objectId" => self::getObjectId($event),
            //==============================================================================
            // User & Comment
            "user" => self::getEventUser($event),
            "comment" => self::getEventComment($event),
        );
        //==============================================================================
        // Validate Contents
        if (empty($response["action"]) || empty($response["objectType"]) || empty($response["objectId"])) {
            return null;
        }

        return $response;
    }

    /**
     * Detect Request Event User
     *
     * @param array $event
     *
     * @return null|string
     */
    private static function getEventUser(array $event): ?string
    {
        return isset($event["User"]) ? (string) $event["User"] : "Optilog API";
    }

    /**
     * Detect Request Event Comment
     *
     * @param array $event
     *
     * @return null|string
     */
    private static function getEventComment(array $event): ?string
    {
        return isset($event["Comment"])
            ? $event["Comment"]
            : "Optilog Change Notified: ".print_r($event, true);
    }

    /**
     * Detect Request Event User
     *
     * @param array $event
     *
     * @return null|string
     */
    private static function getEventType(array $event): ?string
    {
        if (!isset($event["Mode"]) || !is_scalar($event["Mode"]) || !in_array($event["Mode"], self::ACTIONS, true)) {
            Splash::log()->err("Empty or Unknown Action Type: ".print_r($event, true));

            return null;
        }

        return $event["Mode"];
    }

    /**
     * Detect Request Object Type
     *
     * @param array $event
     *
     * @return null|string
     */
    private static function getObjectType(array $event): ?string
    {
        //==============================================================================
        // Object Type & ID
        if (!isset($event["Type"]) || !is_scalar($event["Type"]) || !isset(self::OBJECTS[$event["Type"]])) {
            Splash::log()->err("Empty or Unknown Object Type: ".print_r($event, true));

            return null;
        }

        return self::OBJECTS[$event["Type"]];
    }

    /**
     * Detect Request Object Id
     *
     * @param array $event
     *
     * @return null|string
     */
    private static function getObjectId(array $event): ?string
    {
        //==============================================================================
        // Object Type
        if (!isset($event["Type"]) || !isset(self::OBJECTS[$event["Type"]])) {
            return null;
        }

        //==============================================================================
        // Object ID
        $idKey = self::IDS[$event["Type"]];
        if (!isset($event[$idKey]) || !is_scalar($event[$idKey])) {
            Splash::log()->err("No Object ID Found");

            return null;
        }

        return (string) $event[$idKey];
    }

    /**
     * Preapare REST Json Response
     *
     * @param bool        $success
     * @param null|string $message
     *
     * @return JsonResponse
     */
    private static function buildResponse(bool $success, string $message = null) :JsonResponse
    {
        $response = array('statut' => $success ? 1 : 0);
        if ($message) {
            $response["statutText"] = $message;
        }

        return new JsonResponse($response);
    }
}
