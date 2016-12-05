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
class Sharing extends Constraint
{
    public $subjectClass = 'subjectClass';

    public $identityClass = 'identityClass';

    public $roles = 'roles';

    public $permissions = 'permissions';

    public $invalidClassMessage = 'sharing.class.invalid';

    public $classNotManagedMessage = 'sharing.class.not_managed';

    public $identityNotRoleableMessage = 'sharing.class.identity_not_roleable';

    public $identityNotPermissibleMessage = 'sharing.class.identity_not_permissible';

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
