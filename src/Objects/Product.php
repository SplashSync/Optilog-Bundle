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

namespace Splash\Connectors\Optilog\Objects;

use Splash\Bundle\Interfaces\Objects\TrackingInterface;
use Splash\Bundle\Models\AbstractStandaloneObject;
use Splash\Connectors\Optilog\Services\OptilogConnector;
use Splash\Models\Objects\IntelParserTrait;
use Splash\Models\Objects\PricesTrait;
use Splash\Models\Objects\SimpleFieldsTrait;

/**
 * Optilog Implementation of Product
 */
class Product extends AbstractStandaloneObject implements TrackingInterface
{
    use IntelParserTrait;
    use SimpleFieldsTrait;
    use PricesTrait;
    use Product\CRUDTrait;
    use Product\ObjectsListTrait;
    use Product\CoreTrait;
    use Product\MainTrait;
    use Product\PricesTrait;
    use Product\StockTrait;
    use Product\TrackerTrait;

    /**
     *  Object Disable Flag. Override this flag to disable Object.
     */
    protected static $DISABLED = false;

    /**
     *  Object Name
     */
    protected static $NAME = "Product";

    /**
     *  Object Description
     */
    protected static $DESCRIPTION = "Optilog Product";

    /**
     *  Object Icon (FontAwesome or Glyph ico tag)
     */
    protected static $ICO = "fa fa-product-hunt";

    /**
     * @var OptilogConnector
     */
    protected $connector;

    /**
     * Class Constructor
     *
     * @param OptilogConnector $parentConnector
     */
    public function __construct(OptilogConnector $parentConnector)
    {
        $this->connector = $parentConnector;

        //====================================================================//
        // Connector SelfTest
        $parentConnector->selfTest();
    }
}
