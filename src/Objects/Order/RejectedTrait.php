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

namespace Splash\Connectors\Optilog\Objects\Order;

use stdClass;

/**
 * Manage Rejected Orders Creation
 */
trait RejectedTrait
{
    /**
     * @var string
     */
    private static $rejectedId = "REJECTED";

    /**
     * Check if Order ID is a Rejected ID
     *
     * @param string $objectId
     *
     * @return bool
     */
    protected function isRejectedId(string $objectId): bool
    {
        return (false !== strpos($objectId, self::$rejectedId));
    }

    /**
     * Init Order Object as Rejected
     *
     * @return stdClass
     */
    protected function initRejected(): stdClass
    {
        /** @codingStandardsIgnoreStart */
        $this->object = new stdClass();
        $this->object->ID = self::$rejectedId;
        $this->object->Mode = self::$rejectedId;
        $this->object->DestID = self::$rejectedId;
        $this->object->Statut = -5;
        $this->object->Transporteur = "";
        /** @codingStandardsIgnoreEnd */

        $this->in["Transporteur"] = self::$rejectedId;

        return $this->object;
    }

    /**
     * Check if Order Delete was Requested
     *
     * @return bool
     */
    protected function isDeleteRequest(): bool
    {
        return (bool) ($this->in['isToDelete'] ?? false);
    }

    /**
     * Init Order Object as Deleted
     *
     * @param string $objectId
     *
     * @return stdClass
     */
    protected function initDeleted(string $objectId): stdClass
    {
        /** @codingStandardsIgnoreStart */
        $this->object = new stdClass();
        $this->object->ID = "DELETED";
        $this->object->Mode = "DELETED";
        $this->object->DestID = $objectId;
        $this->object->Statut = -5;
        $this->object->Transporteur = "";
        /** @codingStandardsIgnoreEnd */

        $this->in["Transporteur"] = self::$rejectedId;

        return $this->object;
    }
}
