<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Validator\Constraints;

use Fxp\Component\Security\Identity\SubjectIdentity;
use Fxp\Component\Security\Sharing\SharingIdentityConfig;
use Fxp\Component\Security\Sharing\SharingManagerInterface;
use Fxp\Component\Security\Tests\Fixtures\Model\MockObject;
use Fxp\Component\Security\Tests\Fixtures\Model\MockPermission;
use Fxp\Component\Security\Tests\Fixtures\Model\MockRole;
use Fxp\Component\Security\Tests\Fixtures\Model\MockSharing;
use Fxp\Component\Security\Validator\Constraints\PermissionValidator;
use Fxp\Component\Security\Validator\Constraints\Sharing;
use Fxp\Component\Security\Validator\Constraints\SharingValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class SharingValidatorTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|SharingManagerInterface
     */
    protected $sharingManager;

    /**
     * @var ExecutionContextInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $context;

    /**
     * @var PermissionValidator
     */
    protected $validator;

    protected function setUp(): void
    {
        $this->sharingManager = $this->getMockBuilder(SharingManagerInterface::class)->getMock();
        $this->context = $this->getMockBuilder(ExecutionContextInterface::class)->getMock();
        $this->validator = new SharingValidator($this->sharingManager);
    }

    public function testValidateWithEmptyFields(): void
    {
        $constraint = new Sharing();
        $sharing = new MockSharing();

        $this->addViolation(0, 'sharing.class.invalid', 'subjectClass', [
            '%class_property%' => 'subjectClass',
            '%class%' => null,
        ]);

        $this->addViolation(1, 'sharing.class.invalid', 'identityClass', [
            '%class_property%' => 'identityClass',
            '%class%' => null,
        ]);

        $this->validator->initialize($this->context);
        $this->validator->validate($sharing, $constraint);
    }

    public function testValidateWithNotManagedClass(): void
    {
        $constraint = new Sharing();
        $sharing = new MockSharing();
        $sharing->setSubjectClass(MockObject::class);
        $sharing->setIdentityClass(MockRole::class);

        $this->sharingManager->expects(static::at(0))
            ->method('hasSharingVisibility')
            ->with(SubjectIdentity::fromClassname(MockObject::class))
            ->willReturn(false)
        ;

        $this->addViolation(0, 'sharing.class.not_managed', 'subjectClass', [
            '%class_property%' => 'subjectClass',
            '%class%' => MockObject::class,
        ]);

        $this->sharingManager->expects(static::at(1))
            ->method('hasIdentityConfig')
            ->with(MockRole::class)
            ->willReturn(false)
        ;

        $this->addViolation(1, 'sharing.class.not_managed', 'identityClass', [
            '%class_property%' => 'identityClass',
            '%class%' => MockRole::class,
        ]);

        $this->validator->initialize($this->context);
        $this->validator->validate($sharing, $constraint);
    }

    public function testValidateFieldWithInvalidRole(): void
    {
        $constraint = new Sharing();
        $sharing = new MockSharing();
        $sharing->setSubjectClass(MockObject::class);
        $sharing->setIdentityClass(MockRole::class);
        $sharing->setRoles(['ROLE_TEST']);

        $this->sharingManager->expects(static::at(0))
            ->method('hasSharingVisibility')
            ->with(SubjectIdentity::fromClassname(MockObject::class))
            ->willReturn(true)
        ;

        $this->sharingManager->expects(static::at(1))
            ->method('hasIdentityConfig')
            ->with(MockRole::class)
            ->willReturn(true)
        ;

        $config = new SharingIdentityConfig(MockRole::class);

        $this->sharingManager->expects(static::at(2))
            ->method('getIdentityConfig')
            ->with(MockRole::class)
            ->willReturn($config)
        ;

        $this->addViolation(0, 'sharing.class.identity_not_roleable', 'roles', [
            '%class_property%' => 'identityClass',
            '%class%' => MockRole::class,
        ]);

        $this->validator->initialize($this->context);
        $this->validator->validate($sharing, $constraint);
    }

    public function testValidateFieldWithInvalidPermission(): void
    {
        $constraint = new Sharing();
        $sharing = new MockSharing();
        $sharing->setSubjectClass(MockObject::class);
        $sharing->setIdentityClass(MockRole::class);
        $sharing->getPermissions()->add(new MockPermission());

        $this->sharingManager->expects(static::at(0))
            ->method('hasSharingVisibility')
            ->with(SubjectIdentity::fromClassname(MockObject::class))
            ->willReturn(true)
        ;

        $this->sharingManager->expects(static::at(1))
            ->method('hasIdentityConfig')
            ->with(MockRole::class)
            ->willReturn(true)
        ;

        $config = new SharingIdentityConfig(MockRole::class);

        $this->sharingManager->expects(static::at(2))
            ->method('getIdentityConfig')
            ->with(MockRole::class)
            ->willReturn($config)
        ;

        $this->addViolation(0, 'sharing.class.identity_not_permissible', 'permissions', [
            '%class_property%' => 'identityClass',
            '%class%' => MockRole::class,
        ]);

        $this->validator->initialize($this->context);
        $this->validator->validate($sharing, $constraint);
    }

    public function testValidate(): void
    {
        $constraint = new Sharing();
        $sharing = new MockSharing();
        $sharing->setSubjectClass(MockObject::class);
        $sharing->setIdentityClass(MockRole::class);
        $sharing->setRoles(['ROLE_TEST']);
        $sharing->getPermissions()->add(new MockPermission());

        $this->sharingManager->expects(static::at(0))
            ->method('hasSharingVisibility')
            ->with(SubjectIdentity::fromClassname(MockObject::class))
            ->willReturn(true)
        ;

        $this->sharingManager->expects(static::at(1))
            ->method('hasIdentityConfig')
            ->with(MockRole::class)
            ->willReturn(true)
        ;

        $config = new SharingIdentityConfig(MockRole::class, 'role', true, true);

        $this->sharingManager->expects(static::at(2))
            ->method('getIdentityConfig')
            ->with(MockRole::class)
            ->willReturn($config)
        ;

        $this->validator->initialize($this->context);
        $this->validator->validate($sharing, $constraint);
    }

    /**
     * Add violation.
     *
     * @param int    $position   The position
     * @param string $message    The message
     * @param string $path       The property path
     * @param array  $parameters The violation parameters
     */
    protected function addViolation($position, $message, $path, array $parameters = []): void
    {
        $vb = $this->getMockBuilder(ConstraintViolationBuilderInterface::class)->getMock();
        $i = 0;

        $this->context->expects(static::at($position))
            ->method('buildViolation')
            ->with($message)
            ->willReturn($vb)
        ;

        $vb->expects(static::at(0))
            ->method('atPath')
            ->with($path)
            ->willReturn($vb)
        ;

        foreach ($parameters as $key => $value) {
            ++$i;
            $vb->expects(static::at($i))
                ->method('setParameter')
                ->with($key, $value)
                ->willReturn($vb)
            ;
        }

        $vb->expects(static::once())
            ->method('addViolation')
        ;
    }
}
