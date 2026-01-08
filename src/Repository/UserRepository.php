<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * find all users sorted by created_at DESC with optional status filter
     *
     * @param string|null $status optional status filter ('active' or 'inactive')
     * @return User[]
     */
    public function findAllSortedByCreatedAt(?string $status = null): array
    {
        $qb = $this->createQueryBuilder('u')
            ->orderBy('u.createdAt', 'DESC');

        if ($status !== null) {
            $qb->where('u.status = :status')
               ->setParameter('status', $status);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * count total number of users
     *
     * @return int
     */
    public function countAll(): int
    {
        return (int) $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * count users created within the last N days
     *
     * @param int $days number of days to look back
     * @return int
     */
    public function countCreatedInLastDays(int $days): int
    {
        $date = new \DateTimeImmutable("-{$days} days");

        return (int) $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.createdAt >= :date')
            ->setParameter('date', $date)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * calculate average new users per day over the last N days
     *
     * @param int $days number of days to calculate average over
     * @return float
     */
    public function getAverageNewUsersPerDay(int $days): float
    {
        $count = $this->countCreatedInLastDays($days);

        return $days > 0 ? round($count / $days, 2) : 0.00;
    }
}
