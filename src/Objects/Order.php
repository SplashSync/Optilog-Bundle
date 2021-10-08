<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2021 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Splash\Connectors\Optilog\Objects;

use Splash\Bundle\Models\AbstractStandaloneObject;
use Splash\Client\Splash;
use Splash\Connectors\Optilog\Services\OptilogConnector;
use Splash\Models\Objects\IntelParserTrait;
use Splash\Models\Objects\ObjectsTrait;
use Splash\Models\Objects\SimpleFieldsTrait;

/**
 * Optilog Implementation of Customers Orders
 *
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class Order extends AbstractStandaloneObject
{
    // Splash Php Core Traits
    use IntelParserTrait;
    use SimpleFieldsTrait;
    use ObjectsTrait;

    // Optilog Core Traits
    use Core\DocumentsTrait;
    use Core\ConfigurationTrait;

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
    use Order\ParcelsTrait;
    use Order\AssetsTrait;

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
    // Object Synchronization Recommended Configuration
    //====================================================================//

    /**
     * Enable Import Of New Local Objects
     *
     * @var bool
     */
    protected static $ENABLE_PULL_CREATED = false;

    /**
     * Enable Delete Of Remotes Objects when Deleted Locally
     *
     * @var bool
     */
    protected static $ENABLE_PULL_DELETED = false;

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
