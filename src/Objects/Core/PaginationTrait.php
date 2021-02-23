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

namespace Splash\Connectors\Optilog\Objects\Core;

use stdClass;

/**
 * Manage Objects List Pagination
 */
trait PaginationTrait
{
    /**
     * Prepare List Query Parameters
     *
     * @param string $filter
     * @param array  $params
     *
     * @return array
     */
    protected static function toPageParameters(string $filter, array $params): array
    {
        return array(array(
            "ID" => (string) $filter ?: "*",
            "Offset" => (isset($params["offset"]) && !empty($params["offset"])) ? $params["offset"] : 0,
            "Fetch" => (isset($params["max"]) && !empty($params["max"])) ? (string) $params["max"] : 25,
        ));
    }

    /**
     * Extract List Query Metadata
     *
     * @param null|stdClass $rawResponse
     *
     * @return array
     */
    protected static function toPageMetadata(?stdClass $rawResponse): array
    {
        //====================================================================//
        // Request Failed
        if (!$rawResponse || !isset($rawResponse->result) || !is_array($rawResponse->result)) {
            return array('current' => 0, 'total' => 0);
        }
        //====================================================================//
        // Request has NO Pagination Infos
        if (!isset($rawResponse->pagination->nbLignes) || !isset($rawResponse->pagination->nbTotal)) {
            return array(
                'current' => count($rawResponse->result),
                'total' => count($rawResponse->result),
            );
        }
        //====================================================================//
        // Pagination Infos are Defined
        return array(
            'current' => (int) $rawResponse->pagination->nbLignes,
            'total' => (int) $rawResponse->pagination->nbTotal,
        );
    }
}
