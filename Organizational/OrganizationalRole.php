<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Organizational;

use Sonatra\Component\Security\Identity\RoleSecurityIdentity;
use Sonatra\Component\Security\Identity\SecurityIdentityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Organizational role.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class OrganizationalRole implements OrganizationalRoleInterface
{
    /**
     * @var OrganizationalContextInterface
     */
    private $context;

    /**
     * @var SecurityIdentityManagerInterface
     */
    private $sim;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var array
     */
    private $cacheExec;

    /**
     * Constructor.
     *
     * @param OrganizationalContextInterface   $context      The organizational context
     * @param SecurityIdentityManagerInterface $sim          The security identity manager
     * @param TokenStorageInterface            $tokenStorage The token storage
     */
    public function __construct(OrganizationalContextInterface $context,
                                SecurityIdentityManagerInterface $sim,
                                TokenStorageInterface $tokenStorage)
    {
        $this->context = $context;
        $this->sim = $sim;
        $this->tokenStorage = $tokenStorage;
        $this->cacheExec = array();
    }

    /**
     * {@inheritdoc}
     */
    public function hasRole($role)
    {
        return $this->hasAnyRole((array) $role);
    }

    /**
     * {@inheritdoc}
     */
    public function hasAnyRole($roles)
    {
        $roles = (array) $roles;
        $sidRoles = $this->getTokenRoles();

        if (0 === count($sidRoles)) {
            return false;
        }

        foreach ($roles as $role) {
            if (in_array($this->formatOrgRole($role), $sidRoles)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the roles of token.
     *
     * @return string[]
     */
    protected function getTokenRoles()
    {
        $token = $this->tokenStorage->getToken();

        if (null === $token) {
            return array();
        }

        $sids = $this->sim->getSecurityIdentities($token);
        $id = sha1(implode('|', $sids));

        if (isset($this->cacheExec[$id])) {
            return $this->cacheExec[$id];
        }

        $roles = array();

        foreach ($sids as $sid) {
            if ($sid instanceof RoleSecurityIdentity) {
                $roles[] = $sid->getIdentifier();
            }
        }

        return $this->cacheExec[$id] = $roles;
    }

    /**
     * Format the role with the current organization name.
     *
     * @param string $role The role
     *
     * @return string
     */
    protected function formatOrgRole($role)
    {
        if (false === strrpos($role, '__')) {
            $suffix = '';

            if (null !== $org = $this->context->getCurrentOrganization()) {
                $suffix = $org->getName();
            }

            $role .= '__'.strtoupper($suffix);
        }

        return $role;
    }
}
