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

use Sonatra\Component\Security\Exception\UnexpectedTypeException;
use Sonatra\Component\Security\Model\SharingInterface;

/**
 * Subject utils.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class SubjectUtils
{
    /**
     * Get the subject identity.
     *
     * @param SubjectIdentityInterface|object|string $subject The subject instance or classname
     *
     * @return SubjectIdentityInterface
     */
    public static function getSubjectIdentity($subject)
    {
        if ($subject instanceof SubjectIdentityInterface) {
            return $subject;
        } elseif (is_string($subject)) {
            return SubjectIdentity::fromClassname($subject);
        } elseif (is_object($subject)) {
            return SubjectIdentity::fromObject($subject);
        }

        throw new UnexpectedTypeException($subject, SubjectIdentityInterface::class.'|object|string');
    }

    /**
     * Get the cache id of subject.
     *
     * @param SubjectIdentityInterface $subject The subject
     *
     * @return string
     */
    public static function getCacheId(SubjectIdentityInterface $subject)
    {
        return $subject->getType().':'.$subject->getIdentifier();
    }

    /**
     * Get the cache id of sharing subject.
     *
     * @param SharingInterface $sharing The sharing entry
     *
     * @return string
     */
    public static function getSharingCacheId(SharingInterface $sharing)
    {
        return $sharing->getSubjectClass().':'.$sharing->getSubjectId();
    }
}
