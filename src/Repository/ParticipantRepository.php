<?php

namespace App\Repository;

use App\Entity\Participant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use function get_class;

/**
 *
 * @method Participant|null find($id, $lockMode = null, $lockVersion = null)
 * @method Participant|null findOneBy(array $criteria, array $orderBy = null)
 * @method Participant[]    findAll()
 * @method Participant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ParticipantRepository extends ServiceEntityRepository implements PasswordUpgraderInterface, UserLoaderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Participant::class);
    }

    public function add(Participant $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Participant $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof Participant) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        $user->setPassword($newHashedPassword);

        $this->add($user, true);
    }

    # Utilisation d'une requête personnalisée pour charger l'utilisateur

    /**
     * @throws NonUniqueResultException
     */
    public function loadUserByIdentifier(string $usernameOrEmail): ?Participant
    {
        $entityManager = $this->getEntityManager();

        return $entityManager->createQuery(
            'SELECT u
                FROM App\Entity\Participant u
                WHERE u.pseudo = :query
                OR u.email = :query'
        )
            ->setParameter('query', $usernameOrEmail)
            ->getOneOrNullResult();
    }

    /**
     * @param string $usernameOrEmail
     * @return ?UserInterface
     * @throws NonUniqueResultException
     */
    public function loadUserByUsername(string $usernameOrEmail)
    {
        return $this->loadUserByIdentifier($usernameOrEmail);
    }

    public function selectParticipantsActifs(UserInterface $participantConnecte)
    {
        $queryBuilder = $this->createQueryBuilder('p');
        $queryBuilder->select('DISTINCT p');
        $queryBuilder->where('p.actif = true');
        $queryBuilder->andWhere('p != :participantConnecte');
        $queryBuilder->setParameter(':participantConnecte', $participantConnecte);
        $queryBuilder->andWhere('p.email != :email');
        $queryBuilder->setParameter(':email', 'testanonyme@test.com');

        return $queryBuilder->getQuery()->getResult();

    }

    public function selectParticipantsInactifs()
    {
        $queryBuilder = $this->createQueryBuilder('p');
        $queryBuilder->select('DISTINCT p');
        $queryBuilder->where('p.actif = false');
        $queryBuilder->andWhere('p.email != :email');
        $queryBuilder->setParameter(':email', 'testanonyme@test.com');

        return $queryBuilder->getQuery()->getResult();

    }

    public function selectParticipants(UserInterface $participantConnecte)
    {
        $queryBuilder = $this->createQueryBuilder('p');
        $queryBuilder->select('DISTINCT p');
        $queryBuilder->andWhere('p != :participantConnecte');
        $queryBuilder->setParameter(':participantConnecte', $participantConnecte);
        $queryBuilder->andWhere('p.email != :email');
        $queryBuilder->setParameter(':email', 'testanonyme@test.com');

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findOneByEmail($email)
    {
        $queryBuilder = $this->createQueryBuilder('p');
        $queryBuilder->select('DISTINCT p');
        $queryBuilder->andWhere('p.email = :email');
        $queryBuilder->setParameter(':email', $email);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function countByCampus(int $campusId): int
    {
        return $this->createQueryBuilder('participant')
            ->select('COUNT(participant.id)')
            ->join('participant.campus', 'campus')
            ->where('campus.id = :campusId')
            ->setParameter('campusId', $campusId)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
