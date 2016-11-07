<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Acl\ObjectFilter;

use Sonatra\Component\Security\Acl\Model\ObjectFilterVoterInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * The Doctrine Orm Collection Value Object Filter Voter.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class DoctrineOrmCollectionValue implements ObjectFilterVoterInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($value)
    {
        return $value instanceof Collection;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue($value)
    {
        return new ArrayCollection();
    }
}
