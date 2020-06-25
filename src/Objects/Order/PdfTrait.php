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

/**
 * Send Details of Orders Pdf Fields
 */
trait PdfTrait
{
    /**
     * Build Fields using FieldFactory
     */
    protected function buildPdfFields(): void
    {
        //====================================================================//
        // Invoice PDF
        $this->fieldsFactory()->create(SPL_T_STREAM)
            ->Identifier("Facture")
            ->Name("Facture Client [PDF]")
            ->MicroData("http://schema.org/Order", "invoicePdf")
            ->isWriteOnly();

        //====================================================================//
        // Delivery PDF
        $this->fieldsFactory()->create(SPL_T_STREAM)
            ->Identifier("BonLivraison")
            ->Name("Bon de Livraison [PDF]")
            ->MicroData("http://schema.org/Order", "deliveryPdf")
            ->isWriteOnly();
    }

    /**
     * Write Given Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param mixed  $fieldData Field Data
     */
    protected function setPdfFields($fieldName, $fieldData): void
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            //====================================================================//
            // Order Invoice PDF
            case 'Facture':
                $this->object->Facture = $fieldData;
                $this->addDocumentField($fieldData, self::$docTypeInvoice);

                break;
            //====================================================================//
            // Order Delivery PDF
            case 'BonLivraison':
                $this->object->BonLivraison = $fieldData;
                $this->addDocumentField($fieldData, self::$docTypeDelivery);

                break;
            default:
                return;
        }

        unset($this->in[$fieldName]);
    }
}
