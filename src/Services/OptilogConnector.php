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
use Splash\Bundle\Interfaces\Connectors\TrackingInterface;
use Splash\Bundle\Models\AbstractConnector;
use Splash\Connectors\Optilog\Form\DebugFormType;
use Splash\Connectors\Optilog\Form\EditFormType;
use Splash\Connectors\Optilog\Models\RestHelper as API;
use Splash\Core\SplashCore as Splash;

/**
 * Optilog REST API Connector for Splash
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class OptilogConnector extends AbstractConnector implements TrackingInterface
{
    use \Splash\Bundle\Models\Connectors\GenericObjectMapperTrait;
    use \Splash\Bundle\Models\Connectors\GenericWidgetMapperTrait;

    /**
     * Objects Type Class Map
     *
     * @var array
     */
    protected static $objectsMap = array(
        "Product" => "Splash\\Connectors\\Optilog\\Objects\\Product",
        "Order" => "Splash\\Connectors\\Optilog\\Objects\\Order",
    );

    /**
     * Widgets Type Class Map
     *
     * @var array
     */
    protected static $widgetsMap = array(
        "SelfTest" => "Splash\\Connectors\\Optilog\\Widgets\\SelfTest",
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
        $informations->company = "Optolog";
        $informations->address = "Rond point Robert Schuman,";
        $informations->zip = "77127";
        $informations->town = "Lieusaint";
        $informations->country = "France";
        $informations->www = "http://www.optilog-fr.com";
        $informations->email = "commercial@optilog-fr.com";
        $informations->phone = "01.64.13.46.50";
        //====================================================================//
        // Server Logo & Ico
        $informations->icoraw = Splash::file()->readFileContents(dirname(dirname(__FILE__))."/Resources/public/img/Optilog-Ico.png");
        $informations->logourl = null;
        $informations->logoraw = Splash::file()->readFileContents(dirname(dirname(__FILE__))."/Resources/public/img/Optilog-Logo.png");
        //====================================================================//
        // Server Informations
        $informations->servertype = "Optilog Api V2";
        $informations->serverurl = "www.optilog.fr";
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
        if (!isset($config["WsHost"]) || !in_array($config["WsHost"], API::ENDPOINTS, true)) {
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
            'enabled' => true,                                      // is Connector Enabled
            'beta' => false,                                        // is this a Beta release
            'type' => self::TYPE_HIDDEN,                            // Connector Type or Mode
            'name' => 'optilog',                                    // Connector code (lowercase, no space allowed)
            'connector' => 'splash.connectors.optilog',             // Connector Symfony Service
            'title' => 'profile.card.title',                        // Public short name
            'label' => 'profile.card.label',                        // Public long name
            'domain' => 'OptilogBundle',                            // Translation domain for names
            'ico' => '/bundles/optilog/img/Optilog-Logo-Mini.png',  // Public Icon path
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
        $this->selfTest();

        return $this->isDebugMode() ? DebugFormType::class : EditFormType::class;
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
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getSecuredActions() : array
    {
        return array(
        );
    }

    //====================================================================//
    //  DEBUG FEATURES
    //====================================================================//

    /**
     * Check If Server is In Debug Mode
     *
     * @return bool
     */
    public function isDebugMode() : bool
    {
        return API::isDebugMode();
    }
}
