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

namespace Splash\Connectors\Optilog\Objects\Product;

/**
 * Access to Product Stock Details Fields
 */
trait StockDetailsTrait
{
    /**
     * String Prefix for Stocks Details Fields
     *
     * @var string
     */
    private static string $prefix = "stockDetails_";

    /**
     * Stocks Details Form Product
     *
     * @var array<string, array<string, integer>>
     */
    private array $stocksDetails;

    /**
     * Build Fields using FieldFactory
     */
    protected function buildStockDetailsFields(): void
    {
        //====================================================================//
        // PRODUCT STOCKS DETAILS
        //====================================================================//

        foreach ($this->getStocksDetailsCodes() as $name => $code) {
            $name = trim($name);
            $code = str_replace("_", "", trim($code));
            $groupName = sprintf("Stock %s", $name);
            //====================================================================//
            // Stock Reel
            $this->fieldsFactory()->create(SPL_T_INT)
                ->identifier(sprintf("%s%s_dispo", self::$prefix, $code))
                ->name(sprintf("[%s] Stock Disponible", $code))
                ->description(sprintf("Stocks Available for on %s", $groupName))
                ->microData("http://schema.org/Offer", sprintf("inventoryLevel%s", ucfirst($code)))
                ->group($groupName)
                ->isReadOnly()
            ;
            //====================================================================//
            // Stock Physique
            $this->fieldsFactory()->create(SPL_T_INT)
                ->identifier(sprintf("%s%s_real", self::$prefix, $code))
                ->name(sprintf("[%s] Stock Physique", $code))
                ->description(sprintf("Current Stock on %s", $groupName))
                ->group($groupName)
                ->isReadOnly()
            ;
            //====================================================================//
            // Stock Commande
            $this->fieldsFactory()->create(SPL_T_INT)
                ->identifier(sprintf("%s%s_order", self::$prefix, $code))
                ->name(sprintf("[%s] Stock Commande", $code))
                ->description(sprintf("Reserved for Orders Stock on %s", $groupName))
                ->group($groupName)
                ->isReadOnly()
            ;
        }
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     */
    protected function getStockDetailsFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // Filter on Stock Details Fields
        if ((!str_starts_with($fieldName, self::$prefix)) || !isset($this->stocksDetails)) {
            return;
        }
        $position = explode('_', $fieldName);
        //====================================================================//
        // Safety Check
        if (count($position) < 3) {
            return;
        }
        //====================================================================//
        // READ Field Value
        $this->out[$fieldName] = (int) ($this->stocksDetails[$position[1]][$position[2]] ?? 0);

        unset($this->in[$key]);
    }

    /**
     * Read requested Field
     *
     * @param array $results
     */
    protected function extractStockDetailsFromResults(array $results): void
    {
        $this->stocksDetails = array();
        //====================================================================//
        // Walk on Received Products Stocks
        foreach ($results as $result) {
            //====================================================================//
            // Safety Check
            if ((!$result instanceof \stdClass) || empty($result->Stock) || !is_string($result->Stock)) {
                continue;
            }
            //====================================================================//
            // Extract Stock Details
            $this->stocksDetails[$result->Stock] = array(
                "dispo" => (int) ($result->Stk_Dispo ?? 0),
                "real" => (int) ($result->Stk_Physique ?? 0),
                "order" => (int) ($result->Stk_Commande ?? 0),
            );
        }
    }

    /**
     * Build Fields using FieldFactory
     *
     * @return array<string, string>
     */
    private function getStocksDetailsCodes(): array
    {
        //====================================================================//
        // Get List of Configured Stock Reel
        $stocks = $this->getParameter("Stocks");

        return is_array($stocks) ? $stocks : array();
    }
}
