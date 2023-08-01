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
use Symfony\Component\Security\Core\Security;

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
    private $security;
    private EtatRepository $etatRepository;

    public function __construct(ManagerRegistry $registry, Security $security, EtatRepository $etatRepository)
    {
        $this->security = $security;
        parent::__construct($registry, Sortie::class);
        $this->etatRepository=$etatRepository;
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
        dump($data);
        dump($this->getUser());

        $etatArchive = $this->etatRepository->findbyLibelle("Archivée");
        $etatOuvert = $this->etatRepository->findbyLibelle("Ouverte");

        // Récupérer les valeurs des filtres depuis le formulaire
        $campus = $data->getCampus();
        $nomSortie = $data->getName();
        $dateDebut = $data->getFrom();
        $dateFin = $data->getTo();
        $organisateur = $data->isOrganized();
        //$nonOrganisateur = $data->is['non_organisateur'];
        $nonInscrit = $data->isNotSubscribed();
        $inscrit = $data->isSubscribed();
        $sortiesPassees = $data->isOver();
        //$sortiesOuvertes = $data->isOpen();

        $queryBuilder = $this->createQueryBuilder('s');

        $oneMonthAgo = new \DateTime();
        $oneMonthAgo->modify('-1 month');

        $queryBuilder->select('DISTINCT s');
           // ->innerJoin('s.etat', 'e')
           // ->innerJoin('s.organisateur', 'p')
           $queryBuilder->join('s.campus', 'c');
            $queryBuilder->leftJoin('s.participants', 'part');
            $queryBuilder->where('s.etat != :etatArchive');
            $queryBuilder->setParameter(':etatArchive', $etatArchive);

           // ->leftJoin('s.lieu', 'l')
           // ->leftJoin('l.ville', 'v')
           /* ->where(
                $queryBuilder->expr()->orX(
                    's.dateDebut >= :oneMonthAgo',
                    's.dateLimiteInscription > :currentDate'
                )
            )*/
            //->addOrderBy('s.dateDebut', 'DESC')
            //->setParameter('oneMonthAgo', $oneMonthAgo, \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE)
            //->setParameter('currentDate', new \DateTime(), \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE);


        if ($campus) {
            $queryBuilder->andWhere('s.campus = :campus')
                ->setParameter(':campus', $campus);
        }

        if ($nomSortie) {
            $queryBuilder->andWhere('s.nom LIKE :nomSortie')
                ->setParameter(':nomSortie', '%' . $nomSortie . '%');
        }

      /*  if ($nomSortie) {
            $queryBuilder->andWhere('s.nom LIKE :nomSortie')
                ->setParameter('nomSortie', '%' . $nomSortie . '%');
        }*/

        if ($dateDebut) {
            $queryBuilder->andWhere('s.dateDebut >= :date_debut')
                ->setParameter(':date_debut', $dateDebut);
        }

        if ($dateFin) {
            $queryBuilder->andWhere('s.dateDebut <= :date_fin')
                ->setParameter(':date_fin', $dateFin);
        }

        $orConditions = $queryBuilder->expr()->orX();

        if ($organisateur) {
            //$queryBuilder->andWhere('s.organisateur = :organisateur')
            $orConditions->add('s.organisateur = :user');

           // $queryBuilder->setParameter(':organisateur', $this->getUser());
        }

        if ($inscrit){
            //$queryBuilder->andWhere(':user MEMBER OF s.participants')
            $orConditions->add(':user MEMBER OF s.participants');

        }

        if ($nonInscrit) {
            //$queryBuilder->andWhere(':user NOT MEMBER OF s.participants')
            $andConditions2=$queryBuilder->expr()->andX();
            $andConditions2->add(':user NOT MEMBER OF s.participants');
            $andConditions2->add('s.etat = :etatOuvert');
            $queryBuilder->setParameter(':etatOuvert', $etatOuvert);

            $orConditions->add($andConditions2);
            //$queryBuilder->setParameter(':user', $this->getUser());

        }

        if ($sortiesPassees) {
            $oneMonthAgo = new \DateTime();
            $oneMonthAgo->modify('-1 month');

            dump($oneMonthAgo);

            //$queryBuilder->andWhere('s.dateDebut >= :oneMonthAgo')
            //    ->andWhere('s.dateDebut <= :now')
            $andConditions = $queryBuilder->expr()->andX();
            //$andConditions->add('DATE_ADD(s.dateDebut, s.duree, \'MINUTE\') > :oneMonthAgo');
            $andConditions->add('s.dateDebut > :oneMonthAgo');
            //$andConditions->add('DATE_ADD(s.dateDebut, s.duree, \'MINUTE\') < :now');
            $andConditions->add('s.dateDebut < :now');
            //dump('DATE_ADD(s.dateDebut, s.duree, \'MINUTE\')');

            $orConditions->add($andConditions);
            $queryBuilder->setParameter(':oneMonthAgo', $oneMonthAgo);
            $queryBuilder->setParameter(':now', new \DateTime());
        }

/*
        if($sortiesOuvertes){
            $queryBuilder->andWhere('s.dateLimiteInscription >= :now')
            ->setParameter('now', new  \DateTime());
        }
*/


        //dump($queryBuilder->getDQL());

        $queryBuilder->andWhere($orConditions);

        if($organisateur || $nonInscrit || $inscrit) {
            $queryBuilder->setParameter(':user', $this->getUser());
        }
        $query = $queryBuilder->getQuery();
        $results = $query->getResult();

        return $results;

    }

    private function getUser()
    {
        return $this->security->getUser();
    }

}

