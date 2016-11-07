<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Acl\Domain;

use Sonatra\Component\Security\Acl\Model\RuleOrmFilterDefinitionInterface;
use Sonatra\Component\Security\Acl\Model\OrmFilterRuleContextDefinitionInterface;

/**
 * Abstract class for Acl Rule Doctrine ORM Filter Definition.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class AbstractRuleOrmFilterDefinition implements RuleOrmFilterDefinitionInterface
{
    const TYPE = 'orm';

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return static::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function addFilterConstraint(OrmFilterRuleContextDefinitionInterface $rcd)
    {
        return '';
    }
}
