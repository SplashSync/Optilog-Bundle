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
    public function load(string $objectId): ?stdClass
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
        if (empty($response->result) || !is_array($response->result)) {
            //====================================================================//
            // Order was Already Deleted from API
            if ($this->isDeleteRequest()) {
                return $this->initDeleted($objectId);
            }

            return Splash::log()->errNull("Unable to load Order  (".$objectId.").");
        }
        //====================================================================//
        // Extract Order Infos from Results
        $order = array_shift($response->result);
        if (!($order instanceof stdClass)) {
            return Splash::log()->errNull("Unable to load Order (".$objectId.").");
        }
        if (!isset($order->ID)) {
            return Splash::log()->errNull("Unable to load Order (".$objectId.").");
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
     * @return null|stdClass New Object
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function create(): ?stdClass
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();

        //====================================================================//
        // Check Order Unique Number is given
        if (empty($this->in["DestID"])) {
            Splash::log()->err("ErrLocalFieldMissing", __CLASS__, __FUNCTION__, "DestID");

            return null;
        }
        //====================================================================//
        // Check Optilog Operation Number is Given
        if (empty($this->getParameter('ApiOp'))) {
            Splash::log()->err("ErrLocalFieldMissing", __CLASS__, __FUNCTION__, "Identifiant de l’opération");

            return null;
        }
        //====================================================================//
        // Check if Order is Allowed for Creation
        // NOT ALLOWED => Set in Error
        if (!$this->isAllowedDate() || !$this->isAllowedCarrier() || !$this->isAllowedOrigin()) {
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
        /** @var array<string, null|array|string> $fields */
        $fields = $this->in;
        foreach ($fields as $fieldName => $fieldData) {
            if (is_scalar($fieldData)) {
                //====================================================================//
                // Write Meta Fields (Operation & DIL)
                $this->setMetaFields($fieldName, $fieldData);
                //====================================================================//
                // Write Labels Fields
                $this->setLabelsFields($fieldName, $fieldData);
                //====================================================================//
                // Write Delivery Fields
                $this->setDeliveryFields($fieldName, $fieldData);
                $this->setDeliverySimpleFields($fieldName, $fieldData);
                $this->setTrackingFields($fieldName, $fieldData);
            }
            if (is_array($fieldData)) {
                //====================================================================//
                // Write Items Fields
                $this->setItemsFields($fieldName, $fieldData);
            }
        }
        //====================================================================//
        // Create Order Infos from Api
        $response = API::post("jSetCommandes", array("Commandes" => array($this->object)));
        if (null === $response) {
            return Splash::log()->errNull("Unable to Create Order (".$this->object->ID.").");
        }

        //====================================================================//
        // Setup Object for Edit
        /** @codingStandardsIgnoreStart */
        $this->object->Statut = 0;
        $this->object->IdStatut = 0;
        $this->object->Mode = "ALTER";
        /** @codingStandardsIgnoreEnd */

        return $this->object;
    }

    /**
     * Update Request Object
     *
     * @param bool $needed Is This Update Needed
     *
     * @return null|string Object ID of NULL if Failed to Update
     */
    public function update(bool $needed): ?string
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
        if (null === $response) {
            return Splash::log()->errNull("Unable to Update Order (".$this->object->ID.").");
        }

        return $this->getObjectIdentifier();
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $objectId): bool
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        if (empty($objectId)) {
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
    public function getObjectIdentifier(): ?string
    {
        return $this->object->DestID ?? null;
    }

    /**
     * Check if this Order Data Update is Allowed
     *
     * @return bool
     */
    protected function isAllowedUpdate(): bool
    {
        //====================================================================//
        // Check If Rejected Order
        if ($this->isRejectedId($this->object->DestID)) {
            Splash::log()->war("Rejected Order Detected... Update Skipped");

            return false;
        }
        //====================================================================//
        // Check If Order Already Deleted
        if ("DELETED" == $this->object->ID) {
            Splash::log()->war("Order Already Deleted");

            return false;
        }
        //====================================================================//
        // Check If Mode is ALTER
        if ("ALTER" != $this->object->Mode) {
            //====================================================================//
            // Check If Mode is UNVALIDATE & Status is Draft or Canceled
            if (("UNVALIDATE" == $this->object->Mode) && ($this->getOptilogStatus() <= 0)) {
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
        if ($this->getOptilogStatus() > 1) {
            return false;
        }

        return true;
    }
}
