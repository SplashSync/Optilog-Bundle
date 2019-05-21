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

namespace Splash\Connectors\Optilog\Objects\Product;

use Splash\Connectors\Optilog\Models\RestHelper as API;
use Splash\Core\SplashCore      as Splash;
use stdClass;

/**
 * Optilog Products CRUD Functions
 */
trait CRUDTrait
{
    private static $defaultGamme = "Vernis";

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
        // Get Product Infos from Api
        $response = API::post("jGetStocks", array(array("ID" => $objectId)));
        if ((null == $response) || !isset($response->result) || empty($response->result)) {
            return Splash::log()->errTrace("Unable to load Product (".$objectId.").");
        }
        //====================================================================//
        // Extract Product Infos from Results
        $product = array_shift($response->result);
        if ((null == $product) || !($product instanceof stdClass)) {
            return Splash::log()->errTrace("Unable to load Product (".$objectId.").");
        }
        if (!isset($product->ID)) {
            return Splash::log()->errTrace("Unable to load Product (".$objectId.").");
        }

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
        // Check Product SKU is given
        if (empty($this->in["sku"])) {
            return Splash::log()->err("ErrLocalFieldMissing", __CLASS__, __FUNCTION__, "sku");
        }
        //====================================================================//
        // Check Product Name is given
        if (empty($this->in["Libelle"])) {
            return Splash::log()->err("ErrLocalFieldMissing", __CLASS__, __FUNCTION__, "Libelle");
        }
        //====================================================================//
        // Init Object
        /** @codingStandardsIgnoreStart */
        $product = new stdClass();
        $product->Mode = "NEW";
        $product->Gamme = static::$defaultGamme;
        $product->ID = $this->in["sku"];
        $product->Libelle = $this->in["Libelle"];
        $product->Poids = 0;
        /** @codingStandardsIgnoreEnd */
        //====================================================================//
        // Create Product Infos from Api
        $response = API::post("jSetArticles", array( $product ));
        if (null == $response) {
            return Splash::log()->errTrace("Unable to Create Product (".$this->in["sku"].").");
        }

        return $this->load($product->ID);
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
        //====================================================================//
        // Id Changed
        if ($this->oldSKU) {
            //====================================================================//
            // Delete Old Product
            $this->delete($this->oldSKU);
            //====================================================================//
            // Force Params for New Product
            $this->object->Gamme = static::$defaultGamme;
            $this->object->Poids = 0;
        }
        //====================================================================//
        // Prepare Product Data for Update
        $this->object->Mode = "ALTER";
        //====================================================================//
        // Update Product Infos from Api
        $response = API::post("jSetArticles", array( $this->object ));
        if (null == $response) {
            return Splash::log()->errTrace("Unable to Update Product (".$this->object->ID.").");
        }
        //====================================================================//
        // Update Id if Changed
        if ($this->oldSKU) {
            //====================================================================//
            // Dispatch Object Id Updated Event
            $this->connector->objectIdChanged("Product", $this->oldSKU, $this->object->ID);

            return $this->oldSKU;
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
        API::post("jSetArticles", array( $product ));

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
