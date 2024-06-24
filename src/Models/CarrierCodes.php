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

namespace   Splash\Connectors\Optilog\Models;

/**
 * Logsystech Carriers Types Codes
 */
class CarrierCodes
{
    /**
     * Liste des Codes Transporteurs
     *
     * @var array
     */
    const CODES = array(
        //====================================================================//
        // CALBERSON
        "CFE_AFF" => "CALBERSON AFFRETEMENT",
        //        "CFE_MESSPL" => "CALBERSON CALBERSON",
        "CFE_CX" => "CALBERSON CX",
        "CFE_CXI" => "CALBERSON CXI ",
        "CFE_EURO1" => "CALBERSON EUROFIRST",
        "CFE_EEX" => "CALBERSON EXPRESS EUROPE",
        "CFE_MESSPL" => "CALBERSON MESSAGERIE",
        "CFE_RESPAL" => "CALBERSON RESOPAL",

        //====================================================================//
        // CHRONOPOST
        "CHR_B2B_10" => "CHRONOPOST CHRO 10H",
        "CHR_B2B_13" => "CHRONOPOST CHRO 13H",
        "CHR_B2B_13_SA" => "CHRONOPOST CHRO 13H SAMEDI",
        "CHRB13SSIP" => "CHRONOPOST CHRO 13H SANS INSTANCE",
        "CHR_B2B_18" => "CHRONOPOST CHRO 18H",
        "CHRB18SSIP" => "CHRONOPOST CHRO 18H SANS INSTANCE",
        "CHR_B2B_CI" => "CHRONOPOST CHRO CLASSIC I",
        "CHR_B2B_EI" => "CHRONOPOST CHRO EXPRESS I",
        "CHR_FRESH" => "CHRONOPOST CHRONO FOOD",
        "CHR_BB_REL" => "CHRONOPOST CHRONO RELAIS",
        "CHR_B2C_SECU" => "CHRONOPOST B2C SECURE",
        "CHR_REL_48" => "CHRONOPOST RELAIS 48H",
        "CHR_REL_EU" => "CHRONOPOST RELAIS EUROPE",

        //====================================================================//
        // COLIS PRIVE
        "COLPRIV" => "COLIS PRIVE COLIS PRIVE",
        "COLPRIVAS" => "COLIS PRIVE COLIS PRIVE - avec signature",

        //====================================================================//
        // COLISSIMO
        "COL_9L" => "COLISSIMO ACCESS FRANCE",
        "COL_9V" => "COLISSIMO EXPERT FRANCE",
        "COL_CA" => "COLISSIMO ACCESS EUROPE",
        "COL_CB" => "COLISSIMO EXPERT EUROPE",
        "COL_CP" => "OBSOLETE COLISSIMO EXPERT INTER",
        "COL_CM" => "COLISSIMO EUROPE MON COMMERCANT",
        "COL_CI" => "COLISSIMO EUROPE BUREAU DE POSTE",
        "COL_9W" => "COLISSIMO EXPERT OUTRE-MER",

        //====================================================================//
        // COLISSIMO ESENDEO
        "ESE_COL_9L" => "[ESENDEO] COLISSIMO ACCESS FRANCE",
        "ESE_COL_9V" => "[ESENDEO] COLISSIMO EXPERT FRANCE",
        "ESE_COL_CB" => "[ESENDEO] COLISSIMO EXPERT EUROPE",
        "ESE_COL_CM" => "[ESENDEO] COLISSIMO EUROPE MON COMMERCANT",
        "ESE_COL_6H" => "[ESENDEO] SO COLISSIMO SOCOLISSIMOBPR",
        "ESE_MR_24R" => "[ESENDEO] MONDIAL RELAY MR",

        //====================================================================//
        // DHL
        "DHL_DOM" => "DHL DHL DOMESTIC EXPRESS",
        "DHL_TDE" => "DHL DHL DOMESTIC EXPRESS 09 H - NON DOC ",
        "DHL_TDY" => "DHL DHL DOMESTIC EXPRESS 12 H - NON DOC",
        "DHL_ESU" => "DHL DHL ECONOMY SELECT ",
        "DHL_TDK" => "DHL DHL EXPRESS 09H - DOC",
        "DHL_TDT" => "DHL DHL EXPRESS 12H - DOC",
        "DHL_DOX" => "DHL DHL EXPRESS WORLDWIDE DOC",
        "DHL_WPX" => "DHL DHL EXPRESS WORLDWIDE NON DOC",
        "DHL_ECX" => "DHL DHL EXPRESS WORLDWIDE UE HORS FRANCE",
        "DHL_ESI" => "DHL DHL IMPORTANT ECONOMY SELECT ",
        "DHL_PKT" => "DHL DHL PACKET",
        "DHP_V01PAK" => "DHL DHP PAKET",

        //====================================================================//
        // DPD
        "DPD_PREDICT" => "DPD DPD - Prédict",
        "DPD_PREDICT_EU" => "DPD DPD - Prédict Europe",

        //====================================================================//
        // EURO
        "GEN003" => "EURO EXPRESS EUROEX",
        "GEN003A" => "EURO EXPRESS EUROEX A ",

        //====================================================================//
        // FEDEX
        "FDX_HOME" => "FEDEX HOME EXPRESS",
        "FDX_OPTIMU" => "FEDEX OPTIMUM",
        "FDX_THOME" => "FEDEX TAT@HOME",
        "FDX_THOME2" => "FEDEX TAT@HOME LIVRAISON A 2 PERSONNES",

        //====================================================================//
        // FRANCE EXPRESS
        //        "CFE_CXI" => "FRANCE EXPRESS FREX ",

        //====================================================================//
        // GLS
        "GLS_FDF" => "GLS GLS - Flex Delivery Service",
        "GLS_BP" => "GLS GLS BUSINESS",
        "GLS_EP" => "GLS GLS EXPRESS",
        "GLS_EBP" => "GLS GLS INTERNATIONAL",

        //====================================================================//
        // LA POSTE
        "ASENDIA" => "LA POSTE Asendia ",
        "MAX_2C" => "LA POSTE Lettre MAX",
        "LETT_LS" => "LA POSTE Lettre Simple",
        "LETS50" => "LA POSTE Lettre Suivie 50g ",
        "LETS100" => "LA POSTE Lettre Suivie 100g ",
        "LETS150" => "LA POSTE Lettre Suivie 150g",
        "LETS300" => "LA POSTE Lettre Suivie 300g",

        //====================================================================//
        // MONDIAL RELAY
        "MR_24R" => "MONDIAL RELAY MR - POINT RELAIS",

        //====================================================================//
        // SDV
        "GEN001" => "SDV SDV",

        //====================================================================//
        // COLISSIMO
        "SOCOL_6M" => "SO COLISSIMO SOCOLISSIMOA2P",
        "SOCOL_6H" => "SO COLISSIMO SOCOLISSIMOBPR",
        //        "SOCOL_6H" => "SO COLISSIMO SOCOLISSIMOCDI",
        "SOCOL_6J" => "SO COLISSIMO SOCOLISSIMOCIT",
        "SOCOL_6A" => "SO COLISSIMO SOCOLISSIMODOM",
        "SOCOL_6C" => "SO COLISSIMO SOCOLISSIMODOS",
        "SOCOL_6K" => "SO COLISSIMO SOCOLISSIMORDV",

        //====================================================================//
        // TNT
        "TNT_10" => "TNT National TNT 10H Express",
        "TNT_12" => "TNT National TNT 12H Express",
        "TNT_J" => "TNT National TNT Express",
        "TNT_JZ" => "TNT National TNT Livraison à domicile",
        "TNT_JD" => "TNT National TNT Relais colis",
        "TNT_15N" => "TNT International Global Express",
        "TNT_48N" => "TNT International Economy Express",

        //====================================================================//
        // UPS
        "UPS_STD" => "UPS Standard",
        "UPS_ACCPT" => "UPS Acces Point Eco",
        "UPS_EXP" => "UPS Express (?? Obsolète ??) ",
        "UPS_WWSVR" => "Express Saver / WorldWide Saver",

        //====================================================================//
        // PAACK
        "PCK_NT2" => "PAACK Livraison créneau 2h",
        "PCK_NT4" => "PAACK Livraison créneau 4h",

        //====================================================================//
        // Dropshiping
        "DROP" => "Dropshipping",

        //====================================================================//
        // REJECTED => This Order is NOT to be Shipped by Optilog
        "REJECTED" => "REJECTED - NOT Shipped by Optilog",

        //====================================================================//
        // VET Spécific Carrier Codes
        "VET_COL" => "VET - Custom Colissimo",
        "VET_PRIV" => "VET - Custom Colis Privé",
    );

    /**
     * Liste des Codes Transporteurs Points Relais
     *
     * @var array
     */
    const RELAY = array(
        "CHR_BB_REL",
        "SOCOL_6M",
        "SOCOL_6H",
        "COL_CM",
        "MR_24R",
        "ESE_MR_24R",
        "TNT_JD",
        "UPS_ACCPT",
        "CHR_REL_48",
        "CHR_REL_EU"
    );

    /**
     * Liste des Codes Transporteurs Custom
     *
     * @var array
     */
    const CUSTOM = array(
        "VET_COL",
        "VET_PRIV",
    );

    /**
     * Return Carriers Codes Choices
     *
     * @return array
     */
    public static function getCarrierChoices(): array
    {
        static $carrierChoices;

        if (!isset($carrierChoices)) {
            $carrierChoices = array();
            foreach (self::CODES as $code => $label) {
                $carrierChoices[sprintf('[%s] %s', $code, $label)] = $code;
            }
        }

        return $carrierChoices;
    }

    /**
     * Detection des Codes Transporteurs avec Livraison en Point Relais
     *
     * @param string $carrierCode
     *
     * @return bool
     */
    public static function isRelayCarrier(string $carrierCode): bool
    {
        return in_array($carrierCode, self::RELAY, true);
    }

    /**
     * Detection des Codes Transporteurs Custom
     *
     * @param string $carrierCode
     *
     * @return bool
     */
    public static function isCustomCarrier(string $carrierCode): bool
    {
        return in_array($carrierCode, self::CUSTOM, true);
    }
}
