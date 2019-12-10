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

namespace Splash\Connectors\Optilog\Form;

use Symfony\Component\Form\FormBuilderInterface;

/**
 * Optilog Account Debug Form
 */
class DebugFormType extends AbstractOptilogType
{
    /**
     * Build Optilog Edit Form
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->addWsHostField($builder, $options);
        $this->addApiUserField($builder, $options);
        $this->addApiPwdField($builder, $options);
        $this->addApiKeyField($builder, $options);
        $this->addApiOperationField($builder, $options);
        $this->addMinOrderCreateDateField($builder, $options);
        $this->addLocationField($builder, $options);
        $this->addCarriersListField($builder, $options);

        $this->addRandomStocksField($builder, $options);
        $this->addForcedOrderStatusField($builder, $options);
    }
}
