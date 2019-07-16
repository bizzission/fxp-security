<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Fixtures\Model;

use Fxp\Component\Security\Annotation as FxpSecurity;

/**
 * @FxpSecurity\Permission(
 *     operations={"view", "create", "update", "delete"},
 *     fields={
 *         "id": @FxpSecurity\PermissionField(operations={"read"})
 *     }
 * )
 *
 * @FxpSecurity\Permission(
 *     master="foo",
 *     fields={
 *         "id": @FxpSecurity\PermissionField(operations={"view"})
 *     }
 * )
 *
 * @FxpSecurity\SharingSubject(
 *     visibility="public"
 * )
 *
 * @FxpSecurity\SharingSubject(
 *     visibility="private"
 * )
 *
 * @FxpSecurity\SharingIdentity(
 *     alias="object"
 * )
 *
 * @FxpSecurity\SharingIdentity(
 *     roleable="true",
 *     permissible="true"
 * )
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class MockObjectWithAnnotation extends MockObject
{
    /**
     * @var string
     *
     * @FxpSecurity\PermissionField(operations={"read"})
     *
     * @FxpSecurity\PermissionField(operations={"edit"})
     */
    protected $name;
}
