<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2020 Splash Sync  <www.splashsync.com>
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
use Splash\Core\SplashCore as Splash;
use stdClass;

/**
 * Access to Order Shipped Fields
 */
trait ShippedTrait
{
    /**
     * @var null|stdClass
     */
    private $orderDetails;

    /**
     * Load Order details from API V2 if Needed
     *
     * @param string $objectId Object id
     *
     * @return bool
     */
    protected function loadOrderDetails(string $objectId): bool
    {
        $this->orderDetails = null;
        //====================================================================//
        // Check if Order Details is Needed
        $fields = is_a($this->in, "ArrayObject") ? $this->in->getArrayCopy() : $this->in;
        if (!in_array("ID@shipped", $fields, true)) {
            return true;
        }
        //====================================================================//
        // Get Order Infos from Api V2
        $response = API::postV2("jGetStatutCommande", array(array("ID" => $objectId)));
        if ((null == $response) || !isset($response->result) || empty($response->result)) {
            return Splash::log()->errTrace("Unable to load Order Details from API V2.");
        }
        //====================================================================//
        // Extract Order Infos from Results
        $this->orderDetails = array_shift($response->result);
        if ((null == $this->orderDetails) || !($this->orderDetails instanceof stdClass)) {
            return Splash::log()->errTrace("Unable to load Order Details from API V2.");
        }

        return true;
    }

    /**
     * Build Fields using FieldFactory
     */
    protected function buildShippedFields(): void
    {
        //====================================================================//
        // Order Line Product Identifier (SKU is Here)
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("ID")
            ->InList("shipped")
            ->Name("Product SKU")
            ->MicroData("http://schema.org/OrderItem", "orderItemNumber")
            ->Group("Expéditions")
            ->isReadOnly()
        ;

        //====================================================================//
        // Order Line Quantity
        $this->fieldsFactory()->create(SPL_T_INT)
            ->Identifier("Quantite")
            ->InList("shipped")
            ->Name("Ordered Qty")
            ->MicroData("http://schema.org/OrderItem", "orderQuantity")
            ->Group("Expéditions")
            ->isReadOnly()
        ;

        //====================================================================//
        // Order Line Quantity
        $this->fieldsFactory()->create(SPL_T_INT)
            ->Identifier("Servie")
            ->InList("shipped")
            ->Name("Shipped Qty")
            ->MicroData("http://schema.org/OrderItem", "orderItemStatus")
            ->Group("Expéditions")
            ->isReadOnly()
        ;
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     */
    protected function getShippedFields($key, $fieldName): void
    {
        //====================================================================//
        // Check if List field & Init List Array
        $fieldId = self::lists()->InitOutput($this->out, "shipped", $fieldName);
        if (!$fieldId) {
            return;
        }
        //====================================================================//
        // Verify List is Not Empty
        if (empty($this->orderDetails) || !is_array($this->orderDetails->Articles)) {
            return;
        }
        //====================================================================//
        // Fill List with Data
        foreach ($this->orderDetails->Articles as $index => $product) {
            //====================================================================//
            // READ Fields
            switch ($fieldId) {
                //====================================================================//
                // Order Line Direct Reading Data
                case 'ID':
                    $value = $product->{$fieldId};

                    break;
                case 'Quantite':
                case 'Servie':
                    $value = isset($product->{$fieldId}) ? (int) $product->{$fieldId} : 0;

                    break;
                default:
                    return;
            }
            //====================================================================//
            // Insert Data in List
            self::lists()->Insert($this->out, "shipped", $fieldName, $index, $value);
        }

        unset($this->in[$key]);
    }
}
