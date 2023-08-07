<?php

namespace App\Repository;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\SearchSortie;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 *
 * @method Sortie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sortie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sortie[]    findAll()
 * @method Sortie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortieRepository extends ServiceEntityRepository
{
    private Security $security;
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

    /**
     * @throws NonUniqueResultException
     */
    public function selectAllSorties(?SearchSortie $data)
    {
            // Récupérer les valeurs des filtres depuis le formulaire
            $campus = $data->getCampus();
            $nomSortie = $data->getName();
            $dateDebut = $data->getFrom();
            $dateFin = $data->getTo();
            $organisateur = $data->isOrganized();
            $nonInscrit = $data->isNotSubscribed();
            $inscrit = $data->isSubscribed();
            $sortiesPassees = $data->isOver();
            $etatArchive = $this->etatRepository->findbyLibelle("Archivée");
            $etatOuvert = $this->etatRepository->findbyLibelle("Ouverte");
            $etatCreee = $this->etatRepository->findbyLibelle("Créée");

            $queryBuilder = $this->createQueryBuilder('s');
            $oneMonthAgo = new DateTime();
            $oneMonthAgo->modify('-1 month');
            $queryBuilder->select('DISTINCT s');
            $queryBuilder->join('s.campus', 'c');
            $queryBuilder->leftJoin('s.participants', 'part');
            //Pour ne pas afficher les sorties archivées
            $queryBuilder->where('s.etat != :etatArchive');
            $queryBuilder->setParameter(':etatArchive', $etatArchive);

            //Pour afficher les sorties créées uniquement si c'est l'organisateur qui est connecté
            $orConditions = $queryBuilder->expr()->orX();
            $andConditions = $queryBuilder->expr()->andX();

            $orConditions->add('s.etat != :etatCreee');
            $andConditions->add('s.etat = :etatCreee');
            $queryBuilder->setParameter(':etatCreee', $etatCreee);
            $andConditions->add('s.organisateur = :participantConnecte');
            $queryBuilder->setParameter(':participantConnecte', $this->getUser());
            $orConditions->add($andConditions);

            $queryBuilder->andWhere($orConditions);

            //Début des filtres
            if ($campus) {
                $queryBuilder->andWhere('s.campus = :campus')
                    ->setParameter(':campus', $campus);
            }
            if ($nomSortie) {
                $queryBuilder->andWhere('s.nom LIKE :nomSortie')
                    ->setParameter(':nomSortie', '%' . $nomSortie . '%');
            }
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
            }
            if ($inscrit) {
                $orConditions->add(':user MEMBER OF s.participants');
                if(!$organisateur) {
                    $orConditions->add('s.organisateur = :user');
                }
            }
            if ($nonInscrit) {
                $andConditions2 = $queryBuilder->expr()->andX();
                $andConditions2->add(':user NOT MEMBER OF s.participants');
                $andConditions2->add('s.etat = :etatOuvert');
                $queryBuilder->setParameter(':etatOuvert', $etatOuvert);
                $andConditions2->add('s.organisateur != :user');
                $orConditions->add($andConditions2);
            }

            if ($sortiesPassees) {
                $oneMonthAgo = new DateTime();
                $oneMonthAgo->modify('-1 month');
                $andConditions = $queryBuilder->expr()->andX();
                $andConditions->add('s.dateDebut > :oneMonthAgo');
                $andConditions->add('s.dateDebut < :now');
                $orConditions->add($andConditions);
                $queryBuilder->setParameter(':oneMonthAgo', $oneMonthAgo);
                $queryBuilder->setParameter(':now', new DateTime());
            }
            $queryBuilder->andWhere($orConditions);
            if ($organisateur || $nonInscrit || $inscrit) {
                $queryBuilder->setParameter(':user', $this->getUser());
            }
            $query = $queryBuilder->getQuery();

        return $query->getResult();
    }

    private function getUser(): ?UserInterface
    {
        return $this->security->getUser();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function getSortiesCampus($campusParticipant, $participantConnecte)
    {
        $etatCreee = $this->etatRepository->findbyLibelle("Créée");
        $etatArchive = $this->etatRepository->findbyLibelle("Archivée");

        $queryBuilder = $this->createQueryBuilder('sortie');
        $queryBuilder->select('DISTINCT sortie');
        $queryBuilder->join('sortie.campus', 'camp');
        $queryBuilder->where('sortie.etat != :etatArchive');
        $queryBuilder->setParameter(':etatArchive', $etatArchive);
        $andConditions2= $queryBuilder->expr()->andX();
        $andConditions2->add('camp = :filtreCampus');
        $queryBuilder->setParameter(':filtreCampus', $campusParticipant);
        $orConditions = $queryBuilder->expr()->orX();
        $andConditions = $queryBuilder->expr()->andX();
        $orConditions->add('sortie.etat != :etatCreee');
        $andConditions->add('sortie.etat = :etatCreee');
        $queryBuilder->setParameter(':etatCreee', $etatCreee);
        $andConditions->add('sortie.organisateur = :participantConnecte');
        $queryBuilder->setParameter(':participantConnecte', $participantConnecte);
        $orConditions->add($andConditions);
        $andConditions2->add($orConditions);
        $queryBuilder->andWhere($andConditions2);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function selectSortiesOuvertesEtCreeesCloturee(Participant $participant)
    {
        $etatCree = $this->etatRepository->findbyLibelle("Créée");
        $etatOuvert = $this->etatRepository->findbyLibelle("Ouverte");
        $etatCloture = $this->etatRepository->findbyLibelle("Clôturée");

        $queryBuilder = $this->createQueryBuilder('sortie');
        $queryBuilder->select('DISTINCT sortie');
        $orConditions = $queryBuilder->expr()->orX();
        $orConditions->add('sortie.etat = :etatCree');
        $orConditions->add('sortie.etat = :etatOuvert');
        $orConditions->add('sortie.etat = :etatCloture');
        $queryBuilder->setParameter(':etatCree', $etatCree);
        $queryBuilder->setParameter(':etatOuvert', $etatOuvert);
        $queryBuilder->setParameter(':etatCloture', $etatCloture);
        $queryBuilder->andWhere($orConditions);
        $queryBuilder->andWhere('sortie.organisateur = :participant');
        $queryBuilder->setParameter(':participant', $participant);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function selectSortiesPassees()
    {
        $etatEnCours = $this->etatRepository->findbyLibelle("Activité en cours");
        $etatPasse = $this->etatRepository->findbyLibelle("Passée");
        $etatAnnule = $this->etatRepository->findbyLibelle("Annulée");
        $etatArchive = $this->etatRepository->findbyLibelle("Archivée");

        $queryBuilder = $this->createQueryBuilder('sortie');
        $queryBuilder->select('DISTINCT sortie');
        $orConditions = $queryBuilder->expr()->orX();
        $orConditions->add('sortie.etat = :etatEnCours');
        $orConditions->add('sortie.etat = :etatPasse');
        $orConditions->add('sortie.etat = :etatAnnule');
        $orConditions->add('sortie.etat = :etatArchive');
        $queryBuilder->setParameter(':etatEnCours', $etatEnCours);
        $queryBuilder->setParameter(':etatPasse', $etatPasse);
        $queryBuilder->setParameter(':etatAnnule', $etatAnnule);
        $queryBuilder->setParameter(':etatArchive', $etatArchive);
        $queryBuilder->andWhere($orConditions);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function selectSortiesOuverte($participant)
    {
        $etatOuvert = $this->etatRepository->findbyLibelle("Ouverte");

        $queryBuilder = $this->createQueryBuilder('sortie');
        $queryBuilder->select('DISTINCT sortie');
        $queryBuilder->where('sortie.etat = :etatOuvert');
        $queryBuilder->andWhere(':participant MEMBER OF sortie.participants');
        $queryBuilder->setParameter(':etatOuvert', $etatOuvert);
        $queryBuilder->setParameter(':participant', $participant);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Récupérer les sorties associées à un participant donné.
     *
     * @param Participant $participant
     * @return Sortie[]
     */
    public function findSortiesByParticipant(Participant $participant): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere(':participant MEMBER OF s.participants OR s.organisateur = :participant')
            ->setParameter('participant', $participant)
            ->getQuery()
            ->getResult();
    }
}

