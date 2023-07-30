<?php

namespace App\Repository;
use App\Entity\Sortie;
use App\Form\SearchSortie;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;

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

    public function selectAllSorties(SearchSortie $data)
    {

        // Récupérer les valeurs des filtres depuis le formulaire
        $campus = $data->getCampus();
        $nomSortie = $data->getName();
        $dateDebut = $data->getFrom();
        $dateFin = $data->getTo();
        $organisateur = $data->isOrganized();
        //$nonOrganisateur = $data->is['non_organisateur'];
        $nonInscrit = $data->isSubscribed();
        $sortiesPassees = $data->isOver();

        $queryBuilder = $this->createQueryBuilder('s');

        $queryBuilder->select('s')
            ->innerJoin('s.etat', 'e')
            ->innerJoin('s.organisateur', 'p')
            ->leftJoin('s.participants', 'part') // assignation d'un nouvel alias à participant pour le join
            ->leftJoin('s.campus', 'c')
            ->leftJoin('s.lieu', 'l')
            ->leftJoin('l.ville', 'v')
            ->where('s.dateLimiteInscription < :currentDate or s.dateLimiteInscription > :currentDate')
            ->addOrderBy('s.dateDebut', 'DESC')
            ->setParameter('currentDate', new \DateTime(), \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE);


        if ($campus) {
            $queryBuilder->andWhere('s.campus = :campus')
                ->setParameter('campus', $campus);
        }

        if ($nomSortie) {
            $queryBuilder->andWhere('s.nom LIKE :nomSortie')
                ->setParameter('nomSortie', '%' . $nomSortie . '%');
        }

        if ($nomSortie) {
            $queryBuilder->andWhere('s.nom LIKE :nomSortie')
                ->setParameter('nomSortie', '%' . $nomSortie . '%');
        }

        if ($dateDebut) {
            $queryBuilder->andWhere('s.dateDebut >= :date_debut')
                ->setParameter('date_debut', $dateDebut);
        }

        if ($dateFin) {
            $queryBuilder->andWhere('s.dateDebut <= :date_fin')
                ->setParameter('date_fin', $dateFin);
        }


/*
        if ($dateDebut) {
            $queryBuilder->andWhere('s.dateDebut >= :date_debut')
                ->setParameter('date_debut', $dateDebut);
        }



        if ($dateFin) {
            $queryBuilder->andWhere('s.dateLimiteInscription <= :date_fin')
                ->setParameter('date_fin', $dateFin);
        }

*/

        if ($organisateur) {
            $queryBuilder->andWhere('s.organisateur = :organisateur')
                ->setParameter('organisateur', $this->getUser());
        }
/*
        if ($nonOrganisateur) {
            $queryBuilder->andWhere('s.organisateur != :nonOrganisateur')
                ->setParameter('nonOrganisateur', $this->getUser());
        }
*/

        if ($nonInscrit) {
            $queryBuilder->andWhere(':user NOT MEMBER OF s.participants')
                ->setParameter('user', $this->getUser());
        }

        if ($sortiesPassees) {
            $queryBuilder->andWhere('s.dateDebut < :now')
                ->setParameter('now', new \DateTime());
        }

        $query = $queryBuilder->getQuery();
        $results = $query->getResult();

        return $results;

    }


}


/*
 * rqt initiale teste
 *    $queryBuilder = $this->createQueryBuilder('s');
        $queryBuilder->innerJoin('s.etat', 'e'); // Utilisez l'alias 's' pour la jointure avec 'etat'
        $queryBuilder->innerJoin('s.organisateur', 'p'); // Utilisez l'alias 's' pour la jointure avec 'organisateur'
        $queryBuilder->where('s.dateLimiteInscription < :currentDate'); // Utilisez 's' pour faire référence à la colonne "dateLimiteInscription"
        $queryBuilder->setParameter('currentDate', new \DateTime(), \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE);        $query = $queryBuilder->getQuery();
        $query->setMaxResults(10);
        $results = $query->getResult();
        return $results;
 *
 *
 requete pour sorties passées

SELECT sortie.nom, sortie.date_debut, sortie.date_limite_inscription,sortie.date_debut ,participant.nom, etat.libelle
FROM sortie
INNER JOIN etat ON sortie.etat_id = etat.id
INNER JOIN participant ON sortie.organisateur_id = participant.id
WHERE sortie.date_debut < CURDATE();

requete selon le campus

SELECT sortie.nom, sortie.date_debut, sortie.date_limite_inscription,sortie.date_debut ,participant.nom, etat.libelle, campus.nom
FROM sortie
INNER JOIN etat ON sortie.etat_id = etat.id
INNER JOIN participant ON sortie.organisateur_id = participant.id
INNER JOIN campus ON sortie.campus_id = campus.id
WHERE campus.nom ='SAINT HERBLAIN';




requete entre 2 dates

SELECT sortie.nom, sortie.date_debut, sortie.date_limite_inscription,sortie.date_debut ,participant.nom, etat.libelle
FROM sortie
INNER JOIN etat ON sortie.etat_id = etat.id
INNER JOIN participant ON sortie.organisateur_id = participant.id
WHERE sortie.date_debut >= '2023-06-01' AND sortie.date_debut <= '2023-06-28';
 */