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
     * @return false|stdClass
     */
    public function load($objectId)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Detect Rejected Order Id => Init Rejected Object
        if ($this->isRejectedId($objectId)) {
            return $this->initRejected();
        }
        //====================================================================//
        // Get Order Infos from Api
        $response = API::post("jGetStatutCommande", array(array("ID" => $objectId)));
        if ((null == $response) || !isset($response->result) || empty($response->result)) {
            return Splash::log()->errTrace("Unable to load Order (".$objectId.").");
        }
        //====================================================================//
        // Extract Order Infos from Results
        $order = array_shift($response->result);
        if ((null == $order) || !($order instanceof stdClass)) {
            return Splash::log()->errTrace("Unable to load Order (".$objectId.").");
        }
        if (!isset($order->ID)) {
            return Splash::log()->errTrace("Unable to load Order (".$objectId.").");
        }
        //====================================================================//
        // Prepare Product Data for Update
        /** @codingStandardsIgnoreStart */
        $order->Mode = "ALTER";
        /** @codingStandardsIgnoreEnd */

        return $order;
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
        // Check Optilog Operation Number is Given
        if (empty($this->getParameter('ApiOp'))) {
            return Splash::log()->err("ErrLocalFieldMissing", __CLASS__, __FUNCTION__, "Identifiant de l’opération");
        }
        //====================================================================//
        // Check if Order is Allowed for Creation
        // NOT ALLOWED => Set in Error
        if (!$this->isAllowedDate() || !$this->isAllowedCarrier()) {
            $this->logFilteredOrder();

            return $this->initRejected();
        }
        //====================================================================//
        // Init Object
        /** @codingStandardsIgnoreStart */
        $this->object = new stdClass();
        $this->object->Mode = "NEW";
        $this->object->ID = $this->in["DestID"];
        $this->object->DestID = $this->in["DestID"];
        $this->object->Operation = $this->getParameter('ApiOp');
        /** @codingStandardsIgnoreEnd */

        //====================================================================//
        // Write Minimal Object Data
        $fields = is_a($this->in, "ArrayObject") ? $this->in->getArrayCopy() : $this->in;
        foreach ($fields as $fieldName => $fieldData) {
            //====================================================================//
            // Write Delivery Fields
            $this->setDeliveryFields($fieldName, $fieldData);
            $this->setDeliverySimpleFields($fieldName, $fieldData);
            $this->setTrackingFields($fieldName, $fieldData);
            //====================================================================//
            // Write Items Fields
            $this->setItemsFields($fieldName, $fieldData);
        }

        //====================================================================//
        // Create Order Infos from Api
        $response = API::post("jSetCommandes", array("Commandes" => array($this->object)));
        if (null == $response) {
            return Splash::log()->errTrace("Unable to Create Order (".$this->object->ID.").");
        }

        //====================================================================//
        // Setup Object for Edit
        /** @codingStandardsIgnoreStart */
        $this->object->Statut = 0;
        $this->object->Mode = "ALTER";
        /** @codingStandardsIgnoreEnd */

        return $this->object;
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
        if (!$needed || !$this->isAllowedUpdate()) {
            return $this->getObjectIdentifier();
        }
        //====================================================================//
        // Update Order Infos from Api
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
        API::post("jSetCommandes", array("Commandes" => array($product)));

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectIdentifier()
    {
        if (!isset($this->object->DestID)) {
            return false;
        }

        return $this->object->DestID;
    }

    /**
     * Check if this Order Data Update is Allowed
     *
     * @return bool
     */
    protected function isAllowedUpdate(): ?bool
    {
        //====================================================================//
        // Check If Rejected Order
        if ($this->isRejectedId($this->object->DestID)) {
            Splash::log()->war("Rejected Order Detected... Update Skipped");

            return false;
        }
        //====================================================================//
        // Check If Mode is ALTER
        if ("ALTER" != $this->object->Mode) {
            //====================================================================//
            // Check If Mode is UNVALIDATE & Status is Draft or Canceled
            if (("UNVALIDATE" == $this->object->Mode) && ($this->object->Statut <= 0)) {
                return false;
            }

            return true;
        }
        //====================================================================//
        // Check If Order Status is Defined
        if (!isset($this->object->Statut)) {
            return true;
        }
        //====================================================================//
        // Check If Order Status is Above 1
        if ($this->object->Statut > 1) {
            return false;
        }

        return true;
    }
}
