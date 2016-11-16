<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Doctrine\ORM\Filter;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Filter\SQLFilter;
use Doctrine\ORM\Mapping\ClassMetadata;
use Sonatra\Component\Security\Doctrine\ORM\Listener\SharingListener;
use Sonatra\Component\Security\Exception\RuntimeException;

/**
 * Sharing filter.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class SharingFilter extends SQLFilter
{
    /**
     * @var SharingListener|null
     */
    protected $listener;

    /**
     * @var EntityManagerInterface|null
     */
    protected $em;

    /**
     * {@inheritdoc}
     */
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
    {
        $this->getListener();

        return '';
    }

    /**
     * Get the Doctrine ORM Sharing Listener.
     *
     * @return SharingListener
     *
     * @throws RuntimeException
     */
    protected function getListener()
    {
        if (null === $this->listener) {
            $em = $this->getEntityManager();
            $evm = $em->getEventManager();

            foreach ($evm->getListeners() as $listeners) {
                foreach ($listeners as $listener) {
                    if ($listener instanceof SharingListener) {
                        $this->listener = $listener;
                        break 2;
                    }
                }
            }

            if (null === $this->listener) {
                $msg = 'The listener "Sonatra\Component\Security\Doctrine\ORM\SharingListener" was not added to the Doctrine ORM Event Manager';
                throw new RuntimeException($msg);
            }
        }

        return $this->listener;
    }

    /**
     * Get the entity manager in parent class.
     *
     * @return EntityManagerInterface
     */
    protected function getEntityManager()
    {
        if (null === $this->em) {
            $refl = new \ReflectionProperty('Doctrine\ORM\Query\Filter\SQLFilter', 'em');
            $refl->setAccessible(true);
            $this->em = $refl->getValue($this);
        }

        return $this->em;
    }
}