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
use Splash\Client\Splash;
use Splash\Connectors\Optilog\Services\OptilogConnector;
use Splash\Models\Objects\IntelParserTrait;
use Splash\Models\Objects\ObjectsTrait;
use Splash\Models\Objects\SimpleFieldsTrait;

/**
 * Optilog Implementation of Customers Orders
 */
class Order extends AbstractStandaloneObject // implements TrackingInterface
{
    // Splash Php Core Traits
    use IntelParserTrait;
    use SimpleFieldsTrait;
    use ObjectsTrait;

    // Optilog Core Traits
    use Core\DocumentsTrait;
    use Core\ApiV2FieldsTrait;

    // Optilog Order Traits
    use Order\ObjectsListTrait;
    use Order\CRUDTrait;
    use Order\DatesFilterTrait;
    use Order\CoreTrait;
    use Order\TrackingTrait;
    use Order\DeliveryTrait;
    use Order\ItemsTrait;
    use Order\StatusTrait;
    use Order\TrackerTrait;
    use Order\RejectedTrait;
    use Order\PdfTrait;
    use Order\LabelsTrait;
    use Order\ShippedTrait;

    //====================================================================//
    // Object Definition Parameters
    //====================================================================//

    /**
     * {@inheritdoc}
     */
    protected static $NAME = "Customer Order";

    /**
     * {@inheritdoc}
     */
    protected static $DESCRIPTION = "Optilog Order Object";

    /**
     * {@inheritdoc}
     */
    protected static $ICO = "fa fa-shopping-cart";

    //====================================================================//
    // General Class Variables
    //====================================================================//

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

        //====================================================================//
        //  Load Translation File
        Splash::translator()->load('local');
    }
}
