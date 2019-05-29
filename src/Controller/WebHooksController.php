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
     * @var array
     */
    const ACTIONS = array(
        "NEW" => SPL_A_CREATE,
        "ALTER" => SPL_A_UPDATE,
        "DELETE" => SPL_A_DELETE,
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
        $response = $this->decodeEventCore($event);

        //==============================================================================
        // Object Type & ID
        if ($response) {
            //==============================================================================
            // Action
            if (!isset($event["Mode"]) || !isset(self::ACTIONS[$event["Mode"]])) {
                Splash::log()->err("Empty or Unknown Action Type: ".print_r($event["Mode"], true));

                return null;
            }
            $response["action"] = self::ACTIONS[$event["Mode"]];

            //==============================================================================
            // User & Comment
            $response["user"] = isset($event["User"]) ? $event["User"] : "Optilog API";
            $response["comment"] = isset($event["Comment"])
                    ? $event["Comment"]
                    : $response["objectType"]." ".$response["action"]." Notified";
        }

        return $response;
    }

    /**
     * Validate & Decode Request Event Item
     *
     * @param array $event
     *
     * @return null|array
     */
    private function decodeEventCore(array $event): ?array
    {
        $response = array();

        //==============================================================================
        // Object Type & ID
        if (!isset($event["Type"])) {
            Splash::log()->err("No Object Type Given");

            return null;
        }
        switch ($event["Type"]) {
            case "Article":
                if (!isset($event["ID"]) || !is_string($event["ID"])) {
                    Splash::log()->err("No Object ID Given");

                    return null;
                }
                $response["objectType"] = "Product";
                $response["objectId"] = $event["ID"];

                break;
            case "Commande":
                if (!isset($event["DestID"]) || !is_string($event["DestID"])) {
                    Splash::log()->err("No Order DestID Given");

                    return null;
                }
                $response["objectType"] = "Order";
                $response["objectId"] = $event["DestID"];

                break;
            default:
                Splash::log()->err("Unknown Object Type");

                return null;
        }

        return $response;
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
