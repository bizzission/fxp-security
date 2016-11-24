<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Identity;

/**
 * Interface to retrieving security identities from tokens with caching.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface CacheSecurityIdentityManagerInterface extends SecurityIdentityManagerInterface
{
    /**
     * Invalidate the execution cache.
     */
    public function invalidateCache();
}
