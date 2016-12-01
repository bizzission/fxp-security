<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\Security\Doctrine\ORM\Listener;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Sonatra\Component\Security\Doctrine\DoctrineUtils;
use Sonatra\Component\Security\Doctrine\ORM\Event\GetFilterEvent;
use Sonatra\Component\Security\Identity\SecurityIdentityInterface;
use Sonatra\Component\Security\Sharing\SharingManagerInterface;
use Sonatra\Component\Security\SharingFilterEvents;
use Sonatra\Component\Security\SharingVisibilities;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Sharing filter subscriber of Doctrine ORM SQL Filter to filter
 * the private sharing records.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class PrivateSharingSubscriber implements EventSubscriberInterface
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var ClassMetadata
     */
    protected $meta;

    /**
     * Constructor.
     *
     * @param EntityManagerInterface $em           The entity manager
     * @param string                 $sharingClass The classname of sharing model
     */
    public function __construct(EntityManagerInterface $em, $sharingClass)
    {
        $this->em = $em;
        $this->meta = $em->getClassMetadata($sharingClass);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            SharingFilterEvents::DOCTRINE_ORM_FILTER => array('getFilter', 0),
        );
    }

    /**
     * Get the sharing filter.
     *
     * @param GetFilterEvent $event The event
     */
    public function getFilter(GetFilterEvent $event)
    {
        $sids = $event->getSecurityIdentities();

        if (SharingVisibilities::TYPE_PRIVATE !== $event->getSharingVisibility() || empty($sids)) {
            return;
        }

        $connection = $this->em->getConnection();
        $classname = $connection->quote($event->getTargetEntity()->getName());
        $tableAlias = $event->getTargetTableAlias();
        $groupSids = $this->groupSecurityIdentities($event->getSharingManager(), $connection, $sids);
        $identifier = DoctrineUtils::castIdentifier($event->getTargetEntity(), $connection);
        $now = $this->getNow($connection);

        $filter = <<<SELECTCLAUSE
{$tableAlias}.{$this->meta->getColumnName('id')} IN (SELECT
    s.{$this->meta->getColumnName('subjectId')}{$identifier}
FROM
    {$this->meta->getTableName()} s
WHERE
    s.{$this->meta->getColumnName('subjectClass')} = {$classname}
    AND s.{$this->meta->getColumnName('enabled')} IS TRUE
    AND (s.{$this->meta->getColumnName('startedAt')} IS NULL OR s.{$this->meta->getColumnName('startedAt')} <= {$now})
    AND (s.{$this->meta->getColumnName('endedAt')} IS NULL OR s.{$this->meta->getColumnName('endedAt')} >= {$now})
    AND ({$this->addWhereSecurityIdentitiesForSharing($connection, $groupSids)})
GROUP BY
    s.{$this->meta->getColumnName('subjectId')})
SELECTCLAUSE;

        $event->setFilter($filter);
    }

    /**
     * Add the where condition of security identities.
     *
     * @param Connection $connection The database connection
     * @param array      $groupSids  The group map of security identities
     *
     * @return string
     */
    private function addWhereSecurityIdentitiesForSharing(Connection $connection, array $groupSids)
    {
        $where = '';

        foreach ($groupSids as $type => $ids) {
            $where .= '' === $where ? '' : ' OR ';
            $where .= sprintf('(s.%s = %s AND s.%s IN (%s))',
                $this->meta->getColumnName('identityClass'),
                $connection->quote($type),
                $this->meta->getColumnName('identityName'),
                implode(', ', $ids));
        }

        return $where;
    }

    /**
     * Group the security identities definition.
     *
     * @param SharingManagerInterface     $sharingManager The sharing manager
     * @param Connection                  $connection     The database connection
     * @param SecurityIdentityInterface[] $sids           The security identities
     *
     * @return array
     */
    private function groupSecurityIdentities(SharingManagerInterface $sharingManager,
                                             Connection $connection,
                                             array $sids)
    {
        $groupSids = array();

        foreach ($sids as $sid) {
            $type = $sharingManager->getIdentityConfig($sid->getType())->getType();
            $groupSids[$type][] = $connection->quote($sid->getIdentifier());
        }

        return $groupSids;
    }

    /**
     * Get the datetime now.
     *
     * @param Connection $connection The doctrine connection
     *
     * @return string
     */
    private function getNow(Connection $connection)
    {
        $now = new \DateTime('now');
        $now->setTimezone(new \DateTimeZone('UTC'));
        $format = $now->format($connection->getDatabasePlatform()->getDateTimeFormatString());

        return $connection->quote($format);
    }
}
