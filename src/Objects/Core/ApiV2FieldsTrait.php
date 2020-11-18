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

namespace Splash\Connectors\Optilog\Objects\Core;

use Splash\Components\FieldsFactory;
use Splash\Connectors\Optilog\Models\RestHelper;

/**
 * Configure Objects Fields for API V1 | API V2
 */
trait ApiV2FieldsTrait
{
    /**
     * Setup Field for Current API Version
     *
     * @param FieldsFactory $fieldFactory Spalsh Fields Factory
     */
    protected static function setupReadOnlyOnV2($fieldFactory): void
    {
        RestHelper::isApiV2Mode()
            ? $fieldFactory->setPreferWrite()
            : $fieldFactory->isWriteOnly()
        ;
    }
}
