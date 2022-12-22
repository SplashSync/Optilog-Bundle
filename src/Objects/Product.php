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

namespace Splash\Connectors\Optilog\Objects;

use Splash\Bundle\Models\AbstractStandaloneObject;
use Splash\Client\Splash;
use Splash\Connectors\Optilog\Services\OptilogConnector;
use Splash\Models\Objects\IntelParserTrait;
use Splash\Models\Objects\PricesTrait;
use Splash\Models\Objects\SimpleFieldsTrait;
use stdClass;

/**
 * Optilog Implementation of Product
 */
class Product extends AbstractStandaloneObject
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

    //====================================================================//
    // Object Definition Parameters
    //====================================================================//

    /**
     * {@inheritdoc}
     */
    protected static bool $disabled = false;

    /**
     * {@inheritdoc}
     */
    protected static string $name = "Product";

    /**
     * {@inheritdoc}
     */
    protected static string $description = "Optilog Product";

    /**
     * {@inheritdoc}
     */
    protected static string $ico = "fa fa-product-hunt";

    //====================================================================//
    // Object Synchronization Recommended Configuration
    //====================================================================//

    /**
     * Enable Import Of New Local Objects
     *
     * @var bool
     */
    protected static bool $enablePullCreated = false;

    /**
     * Enable Delete Of Remotes Objects when Deleted Locally
     *
     * @var bool
     */
    protected static bool $enablePullDeleted = false;

    //====================================================================//
    // General Class Variables
    //====================================================================//

    /**
     * @phpstan-var stdClass
     */
    protected object $object;

    /**
     * @var OptilogConnector
     */
    protected OptilogConnector $connector;

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
