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

namespace Splash\Connectors\Optilog\Services;

use ArrayObject;
use Splash\Bundle\Models\AbstractConnector;
use Splash\Connectors\Optilog\Form\EditFormType;
//use Splash\Connectors\Optilog\Models\SoapHelper as API;
use Splash\Connectors\Optilog\Models\RestHelper as API;
use Splash\Connectors\Optilog\Objects\WebHook;
use Splash\Core\SplashCore as Splash;
use Symfony\Component\Routing\RouterInterface;

/**
 * Optilog REST API Connector for Splash
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class OptilogConnector extends AbstractConnector
{
    use \Splash\Bundle\Models\Connectors\GenericObjectMapperTrait;
    use \Splash\Bundle\Models\Connectors\GenericWidgetMapperTrait;

    /**
     * Objects Type Class Map
     *
     * @var array
     */
    protected static $objectsMap = array(
        "ThirdParty" => "Splash\\Connectors\\Optilog\\Objects\\Product",
    );

    /**
     * Widgets Type Class Map
     *
     * @var array
     */
    protected static $widgetsMap = array(
//        "SelfTest" => "Splash\\Connectors\\SendInBlue\\Widgets\\SelfTest",
    );

    /**
     * {@inheritdoc}
     */
    public function ping() : bool
    {
        //====================================================================//
        // Safety Check => Verify Selftest Pass
        if (!$this->selfTest()) {
            return false;
        }
        //====================================================================//
        // Perform Ping Test
        return API::ping();
    }

    /**
     * {@inheritdoc}
     */
    public function connect() : bool
    {
        //====================================================================//
        // Safety Check => Verify Selftest Pass
        if (!$this->selfTest()) {
            return false;
        }
        //====================================================================//
        // Perform Connect Test
        if (!API::connect()) {
            return false;
        }
//        //====================================================================//
//        // Get List of Available Lists
//        if (!$this->fetchMailingLists()) {
//            return false;
//        }
//        //====================================================================//
//        // Get List of Available Members Properties
//        if (!$this->fetchAttributesLists()) {
//            return false;
//        }
        
////        $id = "TEST-SPLASH-" . uniqid();
//        $id = 'dgCmd$ctl03$ctl00';
////        
//        dump(API::post("SetCommandes", array(
////            "data" => '{ "Commandes":[{}] }'            
////            "data" => '{ "Commandes":[ { "Mode":"ALTER", "ID":"monID", "DestID":"123", "Nom":"Emile SPECIMEN", "Adresse1":"9 Rue du Testeur", "CodePostal":"75004", "Ville":"PARIS", "CodePays":"FR", "Articles": [ { "ID":"DV1006X2", "Quantite":1 }, { "ID":"EK3022X1", "Quantite":3 } ] } ] }'            
//            
//            "Commandes" => array(array(
//                "Mode" => "ALTER",
//                "ID" => $id,
//                "Operation" => "CHE00119",
//                "DO" => "	Bernard SPLASH",
//                "DestID" => $id,
//                "Nom" => "B. Paquier",
//                "Adresse1" => "28 Av. des Colombes",
//                "CodePostal" => "33700",
//                "Ville" => "Merignac",
//                "CodePays" => "FR",
////                "Transporteur" => "CHR_B2B_13",
//                "Articles" => array(
//                    array(
//                        "ID" => "DV1006X2",
//                        "Quantite" => "2",
//                    )
//                )
////            )  
//        ))
//                )));
//        
//        dump(API::get("GetStatutCommande", array(
//            "data" => json_encode( array(
//                array("ID" => $id)  
//            )
//            )
//        )));
//                
//        
//        exit;
//        dump(API::get("GetStatutCommande", array(
//            "data" => json_encode( array(
//                array("ID" => "*")  
//            )
//            )
//        )));

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function informations(ArrayObject  $informations) : ArrayObject
    {
        //====================================================================//
        // Server General Description
        $informations->shortdesc = "Optilog API";
        $informations->longdesc = "Splash Integration for Optilog Api V2";
        //====================================================================//
        // Company Informations
        $informations->company =    "Optolog";
        $informations->address =    "Rond point Robert Schuman,";
        $informations->zip =        "77127";
        $informations->town = "Lieusaint";
        $informations->country = "France";
        $informations->www = "http://www.optilog-fr.com";
        $informations->email = "commercial@optilog-fr.com";
        $informations->phone = "01.64.13.46.50";
        //====================================================================//
        // Server Logo & Ico
        $informations->icoraw = Splash::file()->readFileContents(dirname(dirname(__FILE__))."/Resources/public/img/Optilog-Logo-Mini.png");
        $informations->logourl = null;
        $informations->logoraw = Splash::file()->readFileContents(dirname(dirname(__FILE__))."/Resources/public/img/Optilog-Logo-Mini.png");
        //====================================================================//
        // Server Informations
        $informations->servertype = "Optilog Api V2";
//        $informations->serverurl = API::ENDPOINT;
        //====================================================================//
        // Module Informations
        $informations->moduleauthor = SPLASH_AUTHOR;
        $informations->moduleversion = "master";

        return $informations;
    }

    /**
     * {@inheritdoc}
     */
    public function selfTest() : bool
    {
        $config = $this->getConfiguration();

        //====================================================================//
        // Verify Webservice Url is Set
        //====================================================================//
        if (!isset($config["WsHost"]) || !in_array($config["WsHost"], API::ENDPOINTS)) {
            Splash::log()->err("Webservice Host is Invalid");

            return false;
        }        
        
        //====================================================================//
        // Verify Api Key is Set
        //====================================================================//
        if (!isset($config["ApiKey"]) || empty($config["ApiKey"])) {
            Splash::log()->err("Api Key is Invalid");

            return false;
        }

        //====================================================================//
        // Verify Api User is Set
        //====================================================================//
        if (!isset($config["ApiUser"]) || empty($config["ApiUser"])) {
            Splash::log()->err("Api User is Invalid");

            return false;
        }
        
        //====================================================================//
        // Verify Api Password is Set
        //====================================================================//
        if (!isset($config["ApiPwd"]) || empty($config["ApiPwd"])) {
            Splash::log()->err("Api Pwd is Invalid");

            return false;
        }

        //====================================================================//
        // Configure Rest API
        return API::configure(
            $config["WsHost"],
            $config["ApiKey"],
            $config["ApiUser"],
            $config["ApiPwd"]
        );
    }

    //====================================================================//
    // Objects Interfaces
    //====================================================================//

    //====================================================================//
    // Files Interfaces
    //====================================================================//

    /**
     * {@inheritdoc}
     */
    public function getFile(string $filePath, string $fileMd5)
    {
        //====================================================================//
        // Safety Check => Verify Selftest Pass
        if (!$this->selfTest()) {
            return false;
        }
        Splash::log()->err("There are No Files Reading for Mailchime Up To Now!");

        return false;
    }

    //====================================================================//
    // Profile Interfaces
    //====================================================================//

    /**
     * @abstract   Get Connector Profile Informations
     *
     * @return array
     */
    public function getProfile() : array
    {
        return array(
            'enabled' => true,                                   // is Connector Enabled
            'beta' => false,                                  // is this a Beta release
            'type' => self::TYPE_ACCOUNT,                     // Connector Type or Mode
            'name' => 'optilog',                           // Connector code (lowercase, no space allowed)
            'connector' => 'splash.connectors.optilog',         // Connector Symfony Service
            'title' => 'profile.card.title',                   // Public short name
            'label' => 'profile.card.label',                   // Public long name
            'domain' => 'OptilogBundle',                     // Translation domain for names
            'ico' => '/bundles/optilog/img/Optilog-Logo-Mini.png', // Public Icon path
            'www' => 'http://www.optilog-fr.com',                   // Website Url
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getConnectedTemplate() : string
    {
        return "@Optilog/Profile/connected.html.twig";
    }

    /**
     * {@inheritdoc}
     */
    public function getOfflineTemplate() : string
    {
        return "@Optilog/Profile/offline.html.twig";
    }

    /**
     * {@inheritdoc}
     */
    public function getNewTemplate() : string
    {
        return "@Optilog/Profile/new.html.twig";
    }

    /**
     * {@inheritdoc}
     */
    public function getFormBuilderName() : string
    {
        return EditFormType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getMasterAction()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getPublicActions() : array
    {
        return array(
//            "index" => "SendInBlueBundle:WebHooks:index",
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getSecuredActions() : array
    {
        return array(
//            "webhooks" => "SendInBlueBundle:Actions:webhooks",
        );
    }

    //====================================================================//
    //  HIGH LEVEL WEBSERVICE CALLS
    //====================================================================//
//
//    /**
//     * Check & Update SendInBlue Api Account WebHooks.
//     *
//     * @return bool
//     */
//    public function verifyWebHooks() : bool
//    {
//        //====================================================================//
//        // Connector SelfTest
//        if (!$this->selfTest()) {
//            return false;
//        }
//        //====================================================================//
//        // Generate WebHook Url
//        $webHookServer = filter_input(INPUT_SERVER, 'SERVER_NAME');
//        //====================================================================//
//        // When Running on a Local Server
//        if (false !== strpos("localhost", $webHookServer)) {
//            $webHookServer = "www.splashsync.com";
//        }
//        //====================================================================//
//        // Create Object Class
//        $webHookManager = new WebHook($this);
//        $webHookManager->configure("webhook", $this->getWebserviceId(), $this->getConfiguration());
//        //====================================================================//
//        // Get List Of WebHooks for this List
//        $webHooks = $webHookManager->objectsList();
//        if (isset($webHooks["meta"])) {
//            unset($webHooks["meta"]);
//        }
//        //====================================================================//
//        // Filter & Clean List Of WebHooks
//        foreach ($webHooks as $webHook) {
//            //====================================================================//
//            // This is a Splash WebHooks
//            if (false !== strpos(trim($webHook['url']), $webHookServer)) {
//                return true;
//            }
//        }
//        //====================================================================//
//        // Splash WebHooks was NOT Found
//        return false;
//    }
//
//    /**
//     * Check & Update SendInBlue Api Account WebHooks.
//     *
//     * @param RouterInterface $router
//     *
//     * @return bool
//     */
//    public function updateWebHooks(RouterInterface $router) : bool
//    {
//        //====================================================================//
//        // Connector SelfTest
//        if (!$this->selfTest()) {
//            return false;
//        }
//        //====================================================================//
//        // Generate WebHook Url
//        $webHookServer = filter_input(INPUT_SERVER, 'SERVER_NAME');
//        $webHookUrl = $router->generate(
//            'splash_connector_action',
//            array(
//                'connectorName' => $this->getProfile()["name"],
//                'webserviceId' => $this->getWebserviceId(),
//            ),
//            RouterInterface::ABSOLUTE_URL
//        );
//        //====================================================================//
//        // When Running on a Local Server
//        if (false !== strpos("localhost", $webHookServer)) {
//            $webHookServer = "www.splashsync.com";
//            $webHookUrl = "https://www.splashsync.com/en/ws/SendInBlue/123456";
//        }
//        //====================================================================//
//        // Create Object Class
//        $webHookManager = new WebHook($this);
//        $webHookManager->configure("webhook", $this->getWebserviceId(), $this->getConfiguration());
//        //====================================================================//
//        // Get List Of WebHooks for this List
//        $webHooks = $webHookManager->objectsList();
//        if (isset($webHooks["meta"])) {
//            unset($webHooks["meta"]);
//        }
//        //====================================================================//
//        // Filter & Clean List Of WebHooks
//        $foundWebHook = false;
//        foreach ($webHooks as $webHook) {
//            //====================================================================//
//            // This is Current Node WebHooks
//            if (trim($webHook['url']) == $webHookUrl) {
//                $foundWebHook = true;
//
//                continue;
//            }
//            //====================================================================//
//            // This is a Splash WebHooks
//            if (false !== strpos(trim($webHook['url']), $webHookServer)) {
//                $webHookManager->delete($webHook['id']);
//            }
//        }
//        //====================================================================//
//        // Splash WebHooks was Found
//        if ($foundWebHook) {
//            return true;
//        }
//        //====================================================================//
//        // Add Splash WebHooks
//        return (false !== $webHookManager->create($webHookUrl));
//    }
//
//    //====================================================================//
//    //  LOW LEVEL PRIVATE FUNCTIONS
//    //====================================================================//
//
//    /**
//     * Get SendInBlue User Lists
//     *
//     * @return bool
//     */
//    private function fetchMailingLists()
//    {
//        //====================================================================//
//        // Get User Lists from Api
//        $response = API::get('contacts/lists');
//        if (is_null($response)) {
//            return false;
//        }
//        if (!isset($response->lists)) {
//            return false;
//        }
//        //====================================================================//
//        // Parse Lists to Connector Settings
//        $listIndex = array();
//        foreach ($response->lists as $listDetails) {
//            //====================================================================//
//            // Add List Index
//            $listIndex[$listDetails->id] = $listDetails->name;
//        }
//        //====================================================================//
//        // Store in Connector Settings
//        $this->setParameter("ApiListsIndex", $listIndex);
//        $this->setParameter("ApiListsDetails", $response->lists);
//        //====================================================================//
//        // Update Connector Settings
//        $this->updateConfiguration();
//
//        return true;
//    }
//
//    /**
//     * Get SendInBlue User Attributes Lists
//     *
//     * @return bool
//     */
//    private function fetchAttributesLists()
//    {
//        //====================================================================//
//        // Get User Lists from Api
//        $response = API::get('contacts/attributes');
//        if (is_null($response)) {
//            return false;
//        }
//        // @codingStandardsIgnoreStart
//        if (!isset($response->attributes)) {
//            return false;
//        }
//        //====================================================================//
//        // Store in Connector Settings
//        $this->setParameter("ContactAttributes", $response->attributes);
//        // @codingStandardsIgnoreEnd
//        //====================================================================//
//        // Update Connector Settings
//        $this->updateConfiguration();
//
//        return true;
//    }
}
