<?php

namespace App\Repository;

use App\Entity\Campus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

/**
 * @extends ServiceEntityRepository<Campus>
 *
 * @method Campus|null find($id, $lockMode = null, $lockVersion = null)
 * @method Campus|null findOneBy(array $criteria, array $orderBy = null)
 * @method Campus[]    findAll()
 * @method Campus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CampusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Campus::class);
    }

    public function add(Campus $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Campus $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @throws Exception
     */
    public function modifierCampus(int $id, string $nom): Campus
    {
        $entityManager = $this->getEntityManager();
        $campus = $this->find($id);

        if (!$campus) {
            throw new Exception('Campus non trouvé.');
        }

        $campus->setNom($nom);

        $entityManager->flush();

        return $campus;
    }

    public function rechercheParNomCampus($rechercheCampus)
    {
        return $this->createQueryBuilder('v')
            ->where('v.nom LIKE :search')
            ->setParameter('search', '%'.$rechercheCampus.'%')
            ->getQuery()
            ->getResult();
    }
}
