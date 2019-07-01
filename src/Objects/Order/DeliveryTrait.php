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

/**
 * WriteOnly Access to Order Delivery Address Fields
 */
trait DeliveryTrait
{
    /**
     * Build Fields using FieldFactory
     */
    protected function buildDeliveryFields()
    {
        $groupName = "Livraison";

        //====================================================================//
        // Contact Full Name
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("Nom")
            ->Name("Nom du destinataire")
            ->MicroData("http://schema.org/PostalAddress", "alternateName")
            ->Group($groupName)
            ->isRequired()
            ->isWriteOnly();

        //====================================================================//
        // Addess
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("Adresse1")
            ->Name($groupName)
            ->MicroData("http://schema.org/PostalAddress", "streetAddress")
            ->Group($groupName)
            ->isRequired()
            ->isWriteOnly();

        //====================================================================//
        // Addess Complement
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("Adresse2")
            ->Name($groupName." (2)")
            ->Group($groupName)
            ->MicroData("http://schema.org/PostalAddress", "postOfficeBoxNumber")
            ->isWriteOnly();

        //====================================================================//
        // Addess Complement 2
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("Adresse3")
            ->Name($groupName." (3)")
            ->Group($groupName)
            ->isWriteOnly();

        //====================================================================//
        // Zip Code
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("CodePostal")
            ->Name("Zip/Postal Code")
            ->MicroData("http://schema.org/PostalAddress", "postalCode")
            ->Group($groupName)
            ->isRequired()
            ->isWriteOnly();

        //====================================================================//
        // City Name
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("Ville")
            ->Name("City")
            ->MicroData("http://schema.org/PostalAddress", "addressLocality")
            ->Group($groupName)
            ->isRequired()
            ->isWriteOnly();

        //====================================================================//
        // Country ISO Code
        $this->fieldsFactory()->create(SPL_T_COUNTRY)
            ->Identifier("Pays")
            ->Name("Code ISO du Pays")
            ->MicroData("http://schema.org/PostalAddress", "addressCountry")
            ->Group($groupName)
            ->isRequired()
            ->isWriteOnly();
    }

    /**
     * Build Fields using FieldFactory
     */
    protected function buildDeliveryPart2Fields()
    {
        $groupName = "Livraison";

        //====================================================================//
        // Code Point Relais
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("CodePR")
            ->Group($groupName)
            ->Name("Code du point relais")
            ->MicroData("http://schema.org/PostalAddress", "description")
            ->isWriteOnly();

        //====================================================================//
        // Phone
        $this->fieldsFactory()->create(SPL_T_PHONE)
            ->Identifier("Telephone")
            ->Group($groupName)
            ->Name("Téléphone du contact")
            ->MicroData("http://schema.org/PostalAddress", "telephone")
            ->isWriteOnly();

        //====================================================================//
        // Mobile Phone
        $this->fieldsFactory()->create(SPL_T_PHONE)
            ->Identifier("Mobile")
            ->Group($groupName)
            ->Name("Mobile du contact")
            ->MicroData("http://schema.org/Person", "telephone")
            ->isWriteOnly();

        //====================================================================//
        // Customer Email
        $this->fieldsFactory()->create(SPL_T_EMAIL)
            ->Identifier("Email")
            ->Name("Email du contact")
            ->MicroData("http://schema.org/ContactPoint", "email")
            ->isWriteOnly();
    }

    /**
     * Write Given Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param mixed  $fieldData Field Data
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function setDeliveryFields($fieldName, $fieldData)
    {
        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
            //====================================================================//
            // Direct Writtings
            case 'Nom':
            case 'Adresse1':
            case 'Adresse2':
            case 'Adresse3':
            case 'CodePostal':
            case 'Ville':
            case 'Pays':
            case 'CodePR':
            case 'Telephone':
            case 'Mobile':
            case 'Email':
                $this->setSimple($fieldName, $fieldData);

                break;
            default:
                return;
        }
        unset($this->in[$fieldName]);
    }
}
