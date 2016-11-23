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

use Doctrine\Common\Util\ClassUtils;
use Sonatra\Component\Security\Exception\InvalidArgumentException;
use Sonatra\Component\Security\Exception\InvalidSubjectIdentityException;
use Sonatra\Component\Security\Exception\UnexpectedTypeException;

/**
 * Subject identity.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
final class SubjectIdentity implements SubjectIdentityInterface
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $identifier;

    /**
     * @var object|null
     */
    private $subject;

    /**
     * Constructor.
     *
     * @param string      $identifier The identifier
     * @param string      $type       The type
     * @param object|null $subject    The instance of subject
     *
     * @throws InvalidArgumentException When the identifier is empty
     * @throws InvalidArgumentException When the type is empty
     * @throws UnexpectedTypeException  When the subject instance is not an object
     */
    public function __construct($type, $identifier, $subject = null)
    {
        if (empty($type)) {
            throw new InvalidArgumentException('The type cannot be empty');
        }

        if ('' === $identifier) {
            throw new InvalidArgumentException('The identifier cannot be empty');
        }

        if (null !== $subject && !is_object($subject)) {
            throw new UnexpectedTypeException($subject, 'object|null');
        }

        $this->type = $type;
        $this->identifier = $identifier;
        $this->subject = $subject;
    }

    /**
     * Creates a subject identity for the given object.
     *
     * @param object $object The object
     *
     * @throws InvalidSubjectIdentityException
     *
     * @return SubjectIdentityInterface
     */
    public static function fromObject($object)
    {
        try {
            if (!is_object($object)) {
                throw new UnexpectedTypeException($object, 'object');
            }

            if ($object instanceof SubjectIdentityInterface) {
                return $object;
            } elseif ($object instanceof SubjectInterface) {
                return new self(ClassUtils::getClass($object), (string) $object->getSubjectIdentifier(), $object);
            } elseif (method_exists($object, 'getId')) {
                return new self(ClassUtils::getClass($object), (string) $object->getId(), $object);
            }
        } catch (InvalidArgumentException $e) {
            throw new InvalidSubjectIdentityException($e->getMessage(), 0, $e);
        }

        throw new InvalidSubjectIdentityException('The object must either implement the SubjectInterface, or have a method named "getId"');
    }

    /**
     * Creates a subject identity for the given class name.
     *
     * @param string $class The class name
     *
     * @return SubjectIdentity
     */
    public static function fromClassname($class)
    {
        try {
            if (!class_exists($class)) {
                throw new InvalidArgumentException(sprintf('The class "%s" does not exist', $class));
            }

            return new self(ClassUtils::getRealClass($class), 'class');
        } catch (InvalidArgumentException $e) {
            throw new InvalidSubjectIdentityException($e->getMessage(), 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * {@inheritdoc}
     */
    public function getObject()
    {
        return $this->subject;
    }

    /**
     * {@inheritdoc}
     */
    public function equals(SubjectIdentityInterface $identity)
    {
        return $this->identifier === $identity->getIdentifier()
               && $this->type === $identity->getType();
    }

    /**
     * Returns a textual representation of this object identity.
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf('SubjectIdentity(%s, %s)', $this->type, $this->identifier);
    }
}
