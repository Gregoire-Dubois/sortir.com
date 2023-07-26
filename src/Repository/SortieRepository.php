<?php

namespace App\Repository;

use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sortie>
 *
 * @method Sortie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sortie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sortie[]    findAll()
 * @method Sortie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    public function add(Sortie $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Sortie $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findSortieByIdWithDetails(int $id) {
        $queryBuilder = $this->createQueryBuilder('s');
        $queryBuilder->andWhere('s.id =:id')->setParameter(':id', $id);
        $queryBuilder->leftJoin('s.etat', 'e')->addSelect('e');
        $queryBuilder->leftJoin('s.organisateur', 'o')->addSelect('o');
        $queryBuilder->leftJoin('s.participants', 'p')->addSelect('p');
        $queryBuilder->leftJoin('s.campus','c')->addSelect('c');
        $queryBuilder->leftJoin('s.lieu', 'l')->addSelect('l');
        $queryBuilder->leftJoin('l.ville','v')->addSelect('v');


        return $queryBuilder->getQuery()->getOneOrNullResult();

    }

    public function selectAllSorties()
    {

        $queryBuilder = $this->createQueryBuilder('s');
        $queryBuilder->innerJoin('s.etat', 'e'); // Utilisez l'alias 's' pour la jointure avec 'etat'
        $queryBuilder->innerJoin('s.organisateur', 'p'); // Utilisez l'alias 's' pour la jointure avec 'organisateur'
        $queryBuilder->where('s.dateLimiteInscription > :currentDate'); // Utilisez 's' pour faire référence à la colonne "dateLimiteInscription"
        $queryBuilder->setParameter('currentDate', new \DateTime(), \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE);        $query = $queryBuilder->getQuery();
        $query->setMaxResults(10);
        $results = $query->getResult();
        return $results;

    }


}


/*
 *    $dql = 'SELECT s.nom, s.dateDebut, s.dateLimiteInscription, p.nom AS participantNom, e.libelle AS etatLibelle
                FROM App\Entity\Sortie s
                INNER JOIN s.etat e
                INNER JOIN s.organisateur p';
 */