<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class Permission extends Constraint
{
    public $propertyClass = 'class';

    public $propertyField = 'field';

    public $invalidClassMessage = 'permission.class.invalid';

    public $requiredClassMessage = 'permission.class.required';

    public $classNotManagedMessage = 'permission.class.not_managed';

    public $invalidFieldMessage = 'permission.field.invalid';

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
