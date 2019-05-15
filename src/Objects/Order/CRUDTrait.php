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

namespace Splash\Connectors\Optilog\Objects\Order;

use Splash\Connectors\Optilog\Models\RestHelper as API;
use Splash\Core\SplashCore      as Splash;
use stdClass;

/**
 * Optilog Products CRUD Functions
 */
trait CRUDTrait
{
    /**
     * Load Request Object
     *
     * @param string $objectId Object id
     *
     * @return null|stdClass
     */
    public function load($objectId)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Get Order Infos from Api
        $response = API::post("jGetStatutCommande", array(array("ID" => $objectId)));
        if ((null == $response) || !isset($response->result) || empty($response->result)) {
            return Splash::log()->errTrace("Unable to load Order (".$objectId.").");
        }
        //====================================================================//
        // Extract Order Infos from Results
        $product = array_shift($response->result);
        if ((null == $product) || !isset($product->ID)) {
            return Splash::log()->errTrace("Unable to load Order (".$objectId.").");
        }
        //====================================================================//
        // Prepare Product Data for Update
        /** @codingStandardsIgnoreStart */
        $product->Mode = "ALTER";
        /** @codingStandardsIgnoreEnd */

        return $product;
    }

    /**
     * Create Request Object
     *
     * @return false|stdClass New Object
     */
    public function create()
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();

        //====================================================================//
        // Check Order Unique Number is given
        if (empty($this->in["DestID"])) {
            return Splash::log()->err("ErrLocalFieldMissing", __CLASS__, __FUNCTION__, "DestID");
        }
        //====================================================================//
        // Init Object
        /** @codingStandardsIgnoreStart */
        $order = new stdClass();
        $order->Mode = "NEW";
        $order->ID = $this->in["DestID"];
        /** @codingStandardsIgnoreEnd */

        return $order;
    }

    /**
     * Update Request Object
     *
     * @param bool $needed Is This Update Needed
     *
     * @return false|string Object Id of False if Failed to Update
     */
    public function update(bool $needed)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // No Update Required
        if (!$needed) {
            return $this->getObjectIdentifier();
        }
//        //====================================================================//
//        // Prepare Product Data for Update
//        $this->object->Mode = "ALTER";
        //====================================================================//
        // Update Product Infos from Api
        $response = API::post("jSetCommandes", array( "Commandes" => array($this->object)));
        if (null == $response) {
            return Splash::log()->errTrace("Unable to Update Order (".$this->object->ID.").");
        }

        return $this->getObjectIdentifier();
    }

    /**
     * Delete requested Object
     *
     * @param null|string $objectId Object Id
     *
     * @return bool
     */
    public function delete($objectId = null)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        if (is_null($objectId)) {
            return true;
        }
        //====================================================================//
        // Init Object
        /** @codingStandardsIgnoreStart */
        $product = new stdClass();
        $product->Mode = "DELETE";
        $product->ID = $objectId;
        /** @codingStandardsIgnoreEnd */
        //====================================================================//
        // Update Product Infos from Api
        $response = API::post("jSetCommandes", array("Commandes" => array($product)));
        if (null == $response) {
            return Splash::log()->errTrace("Unable to Delete Order (".$objectId.").");
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectIdentifier()
    {
        if (!isset($this->object->ID)) {
            return false;
        }

        return $this->object->ID;
    }
}
