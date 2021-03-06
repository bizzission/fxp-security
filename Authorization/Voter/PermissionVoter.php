<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Authorization\Voter;

use Fxp\Component\Security\Identity\SecurityIdentityManagerInterface;
use Fxp\Component\Security\Permission\FieldVote;
use Fxp\Component\Security\Permission\PermissionManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Permission voter.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class PermissionVoter extends Voter
{
    /**
     * @var PermissionManagerInterface
     */
    private $permissionManager;

    /**
     * @var SecurityIdentityManagerInterface
     */
    private $sim;

    /**
     * @var bool
     */
    private $allowNotManagedSubject;

    /**
     * Constructor.
     *
     * @param PermissionManagerInterface       $permissionManager      The permission manager
     * @param SecurityIdentityManagerInterface $sim                    The security identity manager
     * @param bool                             $allowNotManagedSubject Check if the voter allow the not managed subject
     */
    public function __construct(
        PermissionManagerInterface $permissionManager,
        SecurityIdentityManagerInterface $sim,
        bool $allowNotManagedSubject = true
    ) {
        $this->permissionManager = $permissionManager;
        $this->sim = $sim;
        $this->allowNotManagedSubject = $allowNotManagedSubject;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject): bool
    {
        return $this->isAttributeSupported($attribute)
            && $this->isSubjectSupported($subject)
            && $this->isSubjectManaged($subject);
    }

    /**
     * Check if the attribute is supported.
     *
     * @param string $attribute The attribute
     *
     * @return bool
     */
    protected function isAttributeSupported($attribute): bool
    {
        return \is_string($attribute) && 0 === stripos(strtolower($attribute), 'perm_');
    }

    /**
     * Check if the subject is supported.
     *
     * @param null|FieldVote|mixed $subject The subject
     *
     * @return bool
     */
    protected function isSubjectSupported($subject): bool
    {
        if (null === $subject || \is_string($subject) || $subject instanceof FieldVote || \is_object($subject)) {
            return true;
        }

        return \is_array($subject)
            && isset($subject[0], $subject[1])
            && (\is_string($subject[0]) || \is_object($subject[0]))
            && \is_string($subject[1]);
    }

    /**
     * Check if the subject is managed.
     *
     * @param null|FieldVote|mixed $subject The subject
     *
     * @return bool
     */
    protected function isSubjectManaged($subject): bool
    {
        return null === $subject || $this->allowNotManagedSubject
            ? true
            : $this->permissionManager->isManaged($this->convertSubject($subject));
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        $sids = $this->sim->getSecurityIdentities($token);
        $attribute = substr($attribute, 5);
        $subject = $this->convertSubject($subject);

        return !$this->permissionManager->isEnabled()
            || $this->permissionManager->isGranted($sids, $attribute, $subject);
    }

    /**
     * @param null|FieldVote|mixed $subject The subject
     *
     * @return FieldVote|object|string
     */
    protected function convertSubject($subject)
    {
        if (\is_array($subject) && isset($subject[0], $subject[1])) {
            $subject = new FieldVote($subject[0], $subject[1]);
        }

        return $subject;
    }
}
