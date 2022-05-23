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

namespace Splash\Connectors\Optilog\Form;

use Symfony\Component\Form\FormBuilderInterface;

/**
 * Optilog Account Edit Form
 */
class EditFormType extends AbstractOptilogType
{
    /**
     * Build Optilog Edit Form
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->addWsHostField($builder);
        $this->addApiUserField($builder);
        $this->addApiPwdField($builder);
        $this->addApiKeyField($builder);
        $this->addApiOperationField($builder);
        $this->addMinOrderCreateDateField($builder);
        $this->addProductSkuField($builder);
        $this->addExtendedStatusField($builder);
        $this->addLocationField($builder);
        $this->addCarriersListField($builder);
        $this->addOriginFilterField($builder);
    }
}
