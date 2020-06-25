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

namespace Splash\Connectors\Optilog\Objects;

//use Splash\Bundle\Interfaces\Objects\TrackingInterface;
use Splash\Bundle\Models\AbstractStandaloneObject;
use Splash\Connectors\Optilog\Services\OptilogConnector;
use Splash\Models\Objects\IntelParserTrait;
use Splash\Models\Objects\PricesTrait;
use Splash\Models\Objects\SimpleFieldsTrait;

/**
 * Optilog Implementation of Product
 */
class Product extends AbstractStandaloneObject // implements TrackingInterface
{
    // Splash Php Core Traits
    use IntelParserTrait;
    use SimpleFieldsTrait;
    use PricesTrait;

    // Optilog Core Traits
    use Core\DocumentsTrait;

    // Optilog Products Traits
    use Product\CRUDTrait;
    use Product\ObjectsListTrait;
    use Product\CoreTrait;
    use Product\MainTrait;
    use Product\PricesTrait;
    use Product\StockTrait;
    use Product\LocationTrait;
    use Product\TrackerTrait;
    use Product\ImagesTrait;

    /**
     * {@inheritdoc}
     */
    protected static $DISABLED = false;

    /**
     * {@inheritdoc}
     */
    protected static $NAME = "Product";

    /**
     * {@inheritdoc}
     */
    protected static $DESCRIPTION = "Optilog Product";

    /**
     * {@inheritdoc}
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
