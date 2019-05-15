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

namespace   Splash\Connectors\Optilog\Models;

/**
 * Opilog Orders Status Codes
 */
class StatusCodes
{
    /**
     * List of Optilog Orders Status for Splash
     * 
     * Negative Status >> NOT Send to Optilog
     * 
     * @var array
     */
    const SPLASH = array(
        
        //====================================================================//
        // Real Optilog Statuses
        -1 => "OrderCanceled",          // Rejetée
        0 => "OrderProcessing",         // En attente de validation
        1 => "OrderProcessing",         // En saisie / Acceptée
        2 => "OrderProcessing",         // En cours de préparation
        3 => "OrderProcessing",         // Préparée
        4 => "OrderInTransit",          // Expédiée
        5 => "OrderProblem",            // Incident en cours
        6 => "OrderInTransit",          // En instance
        7 => "OrderDelivered",          // Livré conforme
        8 => "OrderProblem",            // Livré non-conforme
        9 => "OrderProblem",            // Perdu
        10 => "OrderDelivered",         // Retour
        60 => "OrderProblem",           // Litige en cours
    );
    
//    /**
//     * Check if a Given Code Allow Creation of the Order
//     *
//     * @param string $statut
//     * 
//     * @return bool
//     */
//    public static function isAllowedPush(string $statut): bool
//    {
//        //====================================================================//
//        // If order Status NOT Found => No Export
//        if (!in_array($statut, self::SPLASH)) {
//            return false;
//        }
//        //====================================================================//
//        // Detect ID Statut
//        $idStatut = array_search(self::SPLASH, $statut);
//        //====================================================================//
//        // Filter Negative or Above 50 Values
//        if (!$idStatut || ($idStatut < 0) | ($idStatut > 50)) {
//            return false;
//        }
//        
//        return true;
//    }
    
}
