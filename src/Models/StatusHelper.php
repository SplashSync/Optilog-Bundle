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

namespace Splash\Connectors\Optilog\Models;

use Splash\Models\Objects\Order\Status;

/**
 * Optilog Orders Status Helper
 */
class StatusHelper
{
    /**
     * Use Extended Status Codes or NOT ?
     *
     * @var bool
     */
    private static $isExtended = false;

    /**
     * List of Optilog Orders Status Names
     *
     * @var array
     */
    private static $names = array(
        -6 => "En attente Exp.",
        -5 => "Rejetée",
        -4 => "A Confirmer",
        -3 => "Anomalie",
        -1 => "Rejetée",
        0 => "En attente de validation",
        1 => "En saisie / Acceptée",
        2 => "En cours de préparation",
        3 => "Préparée",
        4 => "Expédiée",
        5 => "Incident en cours",
        6 => "En instance",
        7 => "Livré conforme",
        8 => "Livré non-conforme",
        9 => "Livré non-conforme",
        10 => "Retour",
        60 => "Litige en cours",
    );

    /**
     * List of Optilog Orders Standard Status for Splash
     *
     * Negative Status >> NOT Send to Optilog
     *
     * @var array
     */
    private static $standardCodes = array(
        -6 => Status::PROCESSING,       // En attente pour expédition
        -5 => Status::CANCELED,         // Rejetée
        -4 => Status::PROCESSING,       // En attente de confirmation de mise en expédition
        -3 => Status::PROBLEM,          // Anomalie
        -1 => Status::OUT_OF_STOCK,     // Rejetée
        0 => Status::PROCESSING,        // En attente de validation
        1 => Status::PROCESSING,        // En saisie / Acceptée
        2 => Status::PROCESSING,        // En cours de préparation
        3 => Status::PROCESSING,        // Préparée
        4 => Status::IN_TRANSIT,        // Expédiée
        5 => Status::PROBLEM,           // Incident en cours
        6 => Status::IN_TRANSIT,        // En instance
        7 => Status::DELIVERED,         // Livré conforme
        8 => Status::PROBLEM,           // Livré non-conforme
        9 => Status::PROBLEM,           // Perdu
        10 => Status::RETURNED,         // Retour
        60 => Status::PROBLEM,          // Litige en cours
    );

    /**
     * List of Optilog Orders Status for Splash
     *
     * Negative Status >> NOT Send to Optilog
     *
     * @var array
     */
    private static $extendedCodes = array(
        -6 => Status::PROCESSED,        // En attente pour expédition
        -5 => Status::CANCELED,         // Rejetée
        -4 => Status::PICKUP,           // En attente de confirmation de mise en expédition
        -3 => Status::PROBLEM,          // Anomalie
        -1 => Status::OUT_OF_STOCK,     // Rejetée
        0 => Status::DRAFT,             // En attente de validation
        1 => Status::PROCESSING,        // En saisie / Acceptée
        2 => Status::PROCESSING,        // En cours de préparation
        3 => Status::PROCESSED,         // Préparée
        4 => Status::IN_TRANSIT,        // Expédiée
        5 => Status::PROBLEM,           // Incident en cours
        6 => Status::IN_TRANSIT,        // En instance
        7 => Status::DELIVERED,         // Livré conforme
        8 => Status::PROBLEM,           // Livré non-conforme
        9 => Status::PROBLEM,           // Perdu
        10 => Status::RETURNED,         // Retour
        60 => Status::PROBLEM,          // Litige en cours
    );

    /**
     * Init Status Converter
     *
     * @param bool $extended
     *
     * @return void
     */
    public static function init(bool $extended = false): void
    {
        self::$isExtended = $extended;
    }

    /**
     * Get List of All Possible Status Id & Names
     *
     * @return array<int, string>
     */
    public static function getAllNames(): array
    {
        return self::$names;
    }

    /**
     * Convert Raw Optilog Status Id to Splash Status
     *
     * @param int $optilogId
     *
     * @return null|string
     */
    public static function toSplash(int $optilogId): ?string
    {
        $statuses = self::getAll();

        return isset($statuses[$optilogId])
            ? $statuses[$optilogId]
            : null
        ;
    }

    /**
     * Convert Raw Optilog Status Id to Status Name
     *
     * @param int $optilogId
     *
     * @return string
     */
    public static function getName(int $optilogId): string
    {
        return isset(self::$names[$optilogId])
            ? self::$names[$optilogId]
            : "Unknown"
        ;
    }

    /**
     * Get List of All Possible Status
     *
     * @return array<int, string>
     */
    private static function getAll(): array
    {
        return self::$isExtended ? self::$extendedCodes : self::$standardCodes;
    }
}
