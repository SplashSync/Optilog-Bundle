<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2021 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Splash\Connectors\Optilog\Objects\Order;

use Splash\Connectors\Optilog\Models\CarrierCodes;

/**
 * WriteOnly Access to Order Delivery Address Fields
 */
trait DeliveryTrait
{
    /**
     * @var string[]
     */
    private static $deliverySimpleFields = array(
        'Contact',
        'Adresse1',
        'Adresse2',
        'Adresse3',
        'CodePostal',
        'Ville',
        'Pays',
        'Telephone',
        'Mobile',
        'Email',
    );

    /**
     * Build Fields using FieldFactory
     */
    protected function buildDeliveryFields(): void
    {
        $groupName = "Livraison";

        //====================================================================//
        // Company Name
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("Nom")
            ->Name("Nom de l'entreprise")
            ->MicroData("http://schema.org/Organization", "legalName")
            ->Group($groupName)
        ;
        self::setupReadOnlyOnV2($this->fieldsFactory());

        //====================================================================//
        // Contact Full Name
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("Contact")
            ->Name("Nom du destinataire")
            ->MicroData("http://schema.org/PostalAddress", "alternateName")
            ->Group($groupName)
            ->isRequired()
        ;
        self::setupReadOnlyOnV2($this->fieldsFactory());

        //====================================================================//
        // Address
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("Adresse1")
            ->Name($groupName)
            ->MicroData("http://schema.org/PostalAddress", "streetAddress")
            ->Group($groupName)
            ->isRequired()
        ;
        self::setupReadOnlyOnV2($this->fieldsFactory());

        //====================================================================//
        // Address Complement
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("Adresse2")
            ->Name($groupName." (2)")
            ->Group($groupName)
            ->MicroData("http://schema.org/PostalAddress", "postOfficeBoxNumber")
        ;
        self::setupReadOnlyOnV2($this->fieldsFactory());

        //====================================================================//
        // Address Complement 2
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("Adresse3")
            ->Name($groupName." (3)")
            ->Group($groupName)
        ;
        self::setupReadOnlyOnV2($this->fieldsFactory());

        //====================================================================//
        // Zip Code
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("CodePostal")
            ->Name("Zip/Postal Code")
            ->MicroData("http://schema.org/PostalAddress", "postalCode")
            ->Group($groupName)
            ->isRequired()
        ;
        self::setupReadOnlyOnV2($this->fieldsFactory());

        //====================================================================//
        // City Name
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("Ville")
            ->Name("City")
            ->MicroData("http://schema.org/PostalAddress", "addressLocality")
            ->Group($groupName)
            ->isRequired()
        ;
        self::setupReadOnlyOnV2($this->fieldsFactory());

        //====================================================================//
        // Country ISO Code
        $this->fieldsFactory()->create(SPL_T_COUNTRY)
            ->Identifier("Pays")
            ->Name("Code ISO du Pays")
            ->MicroData("http://schema.org/PostalAddress", "addressCountry")
            ->Group($groupName)
            ->isRequired()
        ;
        self::setupReadOnlyOnV2($this->fieldsFactory());
    }

    /**
     * Build Fields using FieldFactory
     */
    protected function buildDeliveryPart2Fields(): void
    {
        $groupName = "Livraison";

        //====================================================================//
        // Code Point Relais
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("CodePR")
            ->Group($groupName)
            ->Name("Code du point relais")
            ->MicroData("http://schema.org/PostalAddress", "description")
        ;
        self::setupReadOnlyOnV2($this->fieldsFactory());

        //====================================================================//
        // Phone
        $this->fieldsFactory()->create(SPL_T_PHONE)
            ->Identifier("Telephone")
            ->Group($groupName)
            ->Name("Téléphone du contact")
            ->MicroData("http://schema.org/PostalAddress", "telephone")
        ;
        self::setupReadOnlyOnV2($this->fieldsFactory());

        //====================================================================//
        // Mobile Phone
        $this->fieldsFactory()->create(SPL_T_PHONE)
            ->Identifier("Mobile")
            ->Group($groupName)
            ->Name("Mobile du contact")
            ->MicroData("http://schema.org/Person", "telephone")
        ;
        self::setupReadOnlyOnV2($this->fieldsFactory());

        //====================================================================//
        // Customer Email
        $this->fieldsFactory()->create(SPL_T_EMAIL)
            ->Identifier("Email")
            ->Name("Email du contact")
            ->MicroData("http://schema.org/ContactPoint", "email")
        ;
        self::setupReadOnlyOnV2($this->fieldsFactory());
    }

    /**
     * Write Given Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param mixed  $fieldData Field Data
     */
    protected function setDeliveryFields($fieldName, $fieldData): void
    {
        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
            //====================================================================//
            // Company | Contact Name
            case 'Nom':
                if (empty($fieldData) && !empty($this->in['Contact'])) {
                    $this->setSimple($fieldName, $this->in['Contact']);

                    break;
                }
                $this->setSimple($fieldName, $fieldData);

                break;
            //====================================================================//
            // Relay Point | Customer Comments
            case 'CodePR':
                //====================================================================//
                // Detect Carrier Code
                $carrierCode = isset($this->in['Transporteur'])
                    ? $this->getCarrierCode((string) $this->in['Transporteur'])
                    : (isset($this->object->Transporteur) ? $this->object->Transporteur : null);
                //====================================================================//
                // Relay Carrier => Push to CodePR
                if ($carrierCode && CarrierCodes::isRelayCarrier($carrierCode)) {
                    $this->setSimple($fieldName, $fieldData);

                    break;
                }
                //====================================================================//
                // Others Carriers => Push to label1
                $this->setSimple("Libelle1", $fieldData);

                break;
            default:
                return;
        }
        unset($this->in[$fieldName]);
    }

    /**
     * Write Given Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param mixed  $fieldData Field Data
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function setDeliverySimpleFields($fieldName, $fieldData): void
    {
        //====================================================================//
        // Is Delivery Simple Field
        if (!in_array($fieldName, self::$deliverySimpleFields, true)) {
            return;
        }
        //====================================================================//
        // WRITE Field
        $this->setSimple($fieldName, $fieldData);

        unset($this->in[$fieldName]);
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     */
    protected function getDeliveryFields($key, $fieldName): void
    {
        //====================================================================//
        // Is Delivery Simple Field
        if (!in_array($fieldName, array('Nom', 'CodePR'), true)) {
            return;
        }
        //====================================================================//
        // READ Fields
        $this->out[$fieldName] = isset($this->object->Destinataire->{$fieldName})
            ? (string) $this->object->Destinataire->{$fieldName}
            : null;
        unset($this->in[$key]);
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     */
    protected function getDeliverySimpleFields($key, $fieldName): void
    {
        //====================================================================//
        // Is Delivery Simple Field
        if (!in_array($fieldName, self::$deliverySimpleFields, true)) {
            return;
        }
        //====================================================================//
        // READ Fields
        $this->out[$fieldName] = isset($this->object->Destinataire->{$fieldName})
            ? (string) $this->object->Destinataire->{$fieldName}
            : null;
        unset($this->in[$key]);
    }
}
