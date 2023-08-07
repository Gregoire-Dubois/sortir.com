<?php

namespace App\Repository;

use App\Entity\Ville;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

/**
 *
 * @method Ville|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ville|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ville[]    findAll()
 * @method Ville[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VilleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ville::class);
    }

    public function add(Ville $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Ville $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @throws Exception
     */
    public function modifierVille(int $id, string $nom, string $codePostal): Ville
    {
        $entityManager = $this->getEntityManager();
        $ville = $this->find($id);

        if (!$ville) {
            throw new Exception('Ville non trouvée.');
        }

        $ville->setNom($nom);
        $ville->setCodePostal($codePostal);

        $entityManager->flush();

        return $ville;
    }

    public function rechercheParNomVille($rechercheVille)
    {
        dump($rechercheVille);

        return $this->createQueryBuilder('v')
            ->where('v.nom LIKE :search')
            ->setParameter('search', '%'.$rechercheVille.'%')
            ->getQuery()
            ->getResult();
    }
}