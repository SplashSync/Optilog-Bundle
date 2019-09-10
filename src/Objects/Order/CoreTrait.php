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

use Splash\Core\SplashCore      as Splash;

/**
 * Access to Order Core Fields
 */
trait CoreTrait
{
    /**
     * Build Core Fields using FieldFactory
     */
    private function buildCoreFields()
    {
        //====================================================================//
        // Internal Reference
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("IntID")
            ->Name("Internal ID")
            ->isListed()
            ->isNotTested()
            ->isReadOnly();

        //====================================================================//
        // Internal Reference
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("DestID")
            ->Name("Reference")
            ->isListed()
            ->MicroData("http://schema.org/Order", "orderNumber")
            ->isRequired()
            ->isNotTested();

        //====================================================================//
        // Comment
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("Commentaire")
            ->Name("Commentaire")
            ->isListed()
            ->isReadOnly();
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     */
    private function getCoreFields($key, $fieldName)
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case 'IntID':
                $this->out[$fieldName] = $this->object->ID;

                break;
            case 'DestID':
            case 'Commentaire':
                $this->getSimple($fieldName);

                break;
            default:
                return;
        }

        unset($this->in[$key]);
    }

    /**
     * Write Given Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param mixed  $fieldData Field Data
     */
    private function setCoreFields($fieldName, $fieldData)
    {
        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
            case 'DestID':
                //====================================================================//
                // Detect Rejected Order Id => No Update Allowed
                if ($this->isRejectedId($this->object->DestID)) {
                    break;
                }
                $this->setSimple($fieldName, $fieldData);

                break;
            default:
                return;
        }
        unset($this->in[$fieldName]);
    }
}
