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

use Splash\Connectors\Optilog\Models\CarrierCodes;

/**
 * WriteOnly Access to Order Delivery Address Fields
 */
trait DeliveryTrait
{
    /**
     * @var string[]
     */
    private static array $deliverySimpleFields = array(
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
        // Contact Full Name
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("Nom")
            ->name("Nom du destinataire")
            ->description("Nom du destinataire. Ex: Jean Dupont")
            ->microData("http://schema.org/PostalAddress", "alternateName")
            ->group($groupName)
            ->isRequired()
        ;
        self::setupReadOnlyOnV2($this->fieldsFactory());

        //====================================================================//
        // Company Name
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("Contact")
            ->name("Contact de livraison")
            ->description("Contact de livraison / Nom de l'entreprise. Si non fourni, « Nom » sera repris.")
            ->microData("http://schema.org/Organization", "legalName")
            ->group($groupName)
        ;
        self::setupReadOnlyOnV2($this->fieldsFactory());

        //====================================================================//
        // Address
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("Adresse1")
            ->name($groupName)
            ->microData("http://schema.org/PostalAddress", "streetAddress")
            ->group($groupName)
            ->isRequired()
        ;
        self::setupReadOnlyOnV2($this->fieldsFactory());

        //====================================================================//
        // Address Complement
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("Adresse2")
            ->name($groupName." (2)")
            ->group($groupName)
            ->microData("http://schema.org/PostalAddress", "postOfficeBoxNumber")
        ;
        self::setupReadOnlyOnV2($this->fieldsFactory());

        //====================================================================//
        // Address Complement 2
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("Adresse3")
            ->name($groupName." (3)")
            ->group($groupName)
        ;
        self::setupReadOnlyOnV2($this->fieldsFactory());

        //====================================================================//
        // Zip Code
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("CodePostal")
            ->name("Zip/Postal Code")
            ->microData("http://schema.org/PostalAddress", "postalCode")
            ->group($groupName)
            ->isRequired()
        ;
        self::setupReadOnlyOnV2($this->fieldsFactory());

        //====================================================================//
        // City Name
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("Ville")
            ->name("City")
            ->microData("http://schema.org/PostalAddress", "addressLocality")
            ->group($groupName)
            ->isRequired()
        ;
        self::setupReadOnlyOnV2($this->fieldsFactory());

        //====================================================================//
        // Country ISO Code
        $this->fieldsFactory()->create(SPL_T_COUNTRY)
            ->identifier("Pays")
            ->name("Code ISO du Pays")
            ->microData("http://schema.org/PostalAddress", "addressCountry")
            ->group($groupName)
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
            ->identifier("CodePR")
            ->group($groupName)
            ->name("Code du point relais")
            ->microData("http://schema.org/PostalAddress", "description")
        ;
        self::setupReadOnlyOnV2($this->fieldsFactory());

        //====================================================================//
        // Phone
        $this->fieldsFactory()->create(SPL_T_PHONE)
            ->identifier("Telephone")
            ->group($groupName)
            ->name("Téléphone du contact")
            ->microData("http://schema.org/PostalAddress", "telephone")
        ;
        self::setupReadOnlyOnV2($this->fieldsFactory());

        //====================================================================//
        // Mobile Phone
        $this->fieldsFactory()->create(SPL_T_PHONE)
            ->identifier("Mobile")
            ->group($groupName)
            ->name("Mobile du contact")
            ->microData("http://schema.org/Person", "telephone")
        ;
        self::setupReadOnlyOnV2($this->fieldsFactory());

        //====================================================================//
        // Customer Email
        $this->fieldsFactory()->create(SPL_T_EMAIL)
            ->identifier("Email")
            ->name("Email du contact")
            ->microData("http://schema.org/ContactPoint", "email")
        ;
        self::setupReadOnlyOnV2($this->fieldsFactory());
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     */
    protected function getDeliveryFields(string $key, string $fieldName): void
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
    protected function getDeliverySimpleFields(string $key, string $fieldName): void
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

    /**
     * Write Given Fields
     *
     * @param string      $fieldName Field Identifier / Name
     * @param null|string $fieldData Field Data
     */
    protected function setDeliveryFields(string $fieldName, ?string $fieldData): void
    {
        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
            //====================================================================//
            // Company | Contact Name
            case 'Nom':
                /** @var null|string $contact */
                $contact = $this->in['Contact'] ?? null;
                if (empty(trim((string) $fieldData)) && !empty(trim((string) $contact))) {
                    $this->setSimple($fieldName, $contact);

                    break;
                }
                $this->setSimple($fieldName, $fieldData);

                break;
            //====================================================================//
            // Relay Point | Customer Comments
            case 'CodePR':
                /** @var null|scalar $transporteur */
                $transporteur = $this->in['Transporteur'] ?? null;
                //====================================================================//
                // Detect Carrier Code
                $carrierCode = $transporteur
                    ? $this->getCarrierCode((string) $transporteur)
                    : ($this->object->Transporteur ?? null);
                //====================================================================//
                // Relay Carrier => Push to CodePR
                if ($carrierCode && CarrierCodes::isRelayCarrier($carrierCode)) {
                    $this->setSimple($fieldName, $fieldData);

                    break;
                }
                //====================================================================//
                // Others Carriers => Push to label1
                if ($fieldData) {
                    $this->setSimple("Libelle1", $fieldData);
                }

                break;
            default:
                return;
        }
        unset($this->in[$fieldName]);
    }

    /**
     * Write Given Fields
     *
     * @param string      $fieldName Field Identifier / Name
     * @param null|string $fieldData Field Data
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function setDeliverySimpleFields(string $fieldName, ?string $fieldData): void
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
}
