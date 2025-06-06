<?php
/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\AdobeImsApi\Api;

/**
 * Declare functionality for getting information is user authorised or not
 * @api
 */
interface UserAuthorizedInterface
{
    /**
     * Checks if user authorized.
     *
     * @param int $adminUserId
     * @return bool
     */
    public function execute(?int $adminUserId = null): bool;
}
