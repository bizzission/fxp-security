<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Organizational;

use Fxp\Component\Security\Event\SetCurrentOrganizationEvent;
use Fxp\Component\Security\Event\SetCurrentOrganizationUserEvent;
use Fxp\Component\Security\Event\SetOrganizationalOptionalFilterTypeEvent;
use Fxp\Component\Security\Exception\RuntimeException;
use Fxp\Component\Security\Model\OrganizationInterface;
use Fxp\Component\Security\Model\OrganizationUserInterface;
use Fxp\Component\Security\Model\Traits\OrganizationalInterface;
use Fxp\Component\Security\Model\UserInterface;
use Fxp\Component\Security\OrganizationalTypes;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Organizational Context.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class OrganizationalContext implements OrganizationalContextInterface
{
    /**
     * @var string
     */
    protected $optionalFilterType = OrganizationalTypes::OPTIONAL_FILTER_WITH_ORG;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var null|EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var null|false|OrganizationInterface
     */
    protected $organization;

    /**
     * @var null|OrganizationUserInterface
     */
    protected $organizationUser;

    /**
     * Constructor.
     *
     * @param TokenStorageInterface         $tokenStorage The token storage
     * @param null|EventDispatcherInterface $dispatcher   The event dispatcher
     */
    public function __construct(TokenStorageInterface $tokenStorage, ?EventDispatcherInterface $dispatcher = null)
    {
        $this->tokenStorage = $tokenStorage;
        $this->dispatcher = $dispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function setCurrentOrganization($organization): void
    {
        $this->getToken('organization', $organization instanceof OrganizationInterface);

        if (null === $organization || false === $organization || $organization instanceof OrganizationInterface) {
            $old = $this->organization;
            $this->organization = $organization;
            $this->dispatch(
                SetCurrentOrganizationEvent::class,
                $organization,
                $old
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentOrganization(): ?OrganizationInterface
    {
        if (null === $this->organization) {
            $token = $this->tokenStorage->getToken();
            $user = null !== $token ? $token->getUser() : null;

            if ($user instanceof UserInterface && $user instanceof OrganizationalInterface) {
                $org = $user->getOrganization();

                if ($org instanceof OrganizationInterface) {
                    return $org;
                }
            }
        }

        return false !== $this->organization ? $this->organization : null;
    }

    /**
     * {@inheritdoc}
     */
    public function setCurrentOrganizationUser(?OrganizationUserInterface $organizationUser): void
    {
        $token = $this->getToken('organization user', $organizationUser instanceof OrganizationUserInterface);
        $user = null !== $token ? $token->getUser() : null;
        $this->organizationUser = null;
        $org = null;

        if ($user instanceof UserInterface && $organizationUser instanceof OrganizationUserInterface
                && $this->isSameUser($user, $organizationUser)) {
            $old = $this->organizationUser;
            $this->organizationUser = $organizationUser;
            $org = $organizationUser->getOrganization();
            $this->dispatch(
                SetCurrentOrganizationUserEvent::class,
                $organizationUser,
                $old
            );
        }
        $this->setCurrentOrganization($org);
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentOrganizationUser(): ?OrganizationUserInterface
    {
        return $this->organizationUser;
    }

    /**
     * {@inheritdoc}
     */
    public function isOrganization(): bool
    {
        return null !== $this->getCurrentOrganization()
            && !$this->getCurrentOrganization()->isUserOrganization()
            && null !== $this->getCurrentOrganizationUser();
    }

    /**
     * {@inheritdoc}
     */
    public function setOptionalFilterType(string $type): void
    {
        $old = $this->optionalFilterType;
        $this->optionalFilterType = $type;
        $this->dispatch(
            SetOrganizationalOptionalFilterTypeEvent::class,
            $type,
            $old
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getOptionalFilterType(): string
    {
        return $this->optionalFilterType;
    }

    /**
     * {@inheritdoc}
     */
    public function isOptionalFilterType(string $type): bool
    {
        return \is_string($this->optionalFilterType) && $type === $this->optionalFilterType;
    }

    /**
     * Get the token.
     *
     * @param string $type          The type name
     * @param bool   $tokenRequired Check if the token is required
     *
     * @throws
     *
     * @return TokenInterface
     */
    protected function getToken(string $type, bool $tokenRequired = true): ?TokenInterface
    {
        $token = $this->tokenStorage->getToken();

        if ($tokenRequired && null === $token) {
            throw new RuntimeException(sprintf('The current %s cannot be added in security token because the security token is empty', $type));
        }

        return $token;
    }

    /**
     * Dispatch the event.
     *
     * @param string                   $eventClass The class name of event
     * @param null|false|object|string $subject    The event subject
     * @param null|false|object|string $oldSubject The old event subject
     */
    protected function dispatch(string $eventClass, $subject, $oldSubject): void
    {
        if (null !== $this->dispatcher && $oldSubject !== $subject) {
            $this->dispatcher->dispatch(new $eventClass($subject));
        }
    }

    /**
     * @param UserInterface             $user
     * @param OrganizationUserInterface $organizationUser
     *
     * @return bool
     */
    private function isSameUser(UserInterface $user, OrganizationUserInterface $organizationUser): bool
    {
        return null !== $organizationUser->getUser()
            && $user->getUsername() === $organizationUser->getUser()->getUsername();
    }
}
