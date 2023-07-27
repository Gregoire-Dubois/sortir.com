<?php

namespace App\Event;

use App\Entity\Etat;
use App\Entity\Sortie;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\EventDispatcher\Event;

/**
 *
 * Ce service permet de gérer les états de sortie
 *
 */
class SortieEvent extends Event
{
    private $doctrine;

    // Définition des constantes pour les différents états
    const ETAT_CREEE = 'Créée';
    const ETAT_OUVERTE = 'Ouverte';
    const ETAT_CLOTUREE = 'Clôturée';
    const ETAT_ACTIVITE_EN_COURS = 'Activité en cours';
    const ETAT_PASSEE = 'Passée';
    const ETAT_ANNULEE = 'Annulée';
    const ETAT_ARCHIVEE = 'Archivée';

    /**
     * @param ManagerRegistry $entityManager
     */
    public function __construct(ManagerRegistry $entityManager)
    {
        $this->doctrine = $entityManager;
    }

    /**
     * Retourne  un objet Etat en fonction de son nom
     *
     * @param string $libelle
     * @return SortieEvent|object|null
     */
    public function getEtatByLibelle(string $libelle): ?Etat
    {
        $etat = $this->doctrine->getRepository(Etat::class)->findOneBy(['libelle' => $libelle]);

        return $etat;
    }

    /**
     * Change l'état d'un événement en bdd
     *
     * @param Sortie $sortie
     * @param string $newLibelleEtat
     */
    public function changeEtatSortie(Sortie $sortie, string $newLibelleEtat)
    {
        $newEtat = $this->getEtatByLibelle($newLibelleEtat);
        $sortie->setEtat($newEtat);

        $em = $this->doctrine->getManager();
        $em->persist($sortie);
        $em->flush();
    }

    /**
     *
     * Retourne true si la sortie peut être "Ouverte"
     *
     * @param Sortie $sortie
     * @return bool
     */
    public function changementEtatSortieOuverte(Sortie $sortie): bool
    {
        //doit être en statut "Créée" pour retourner true
        return $sortie->getEtat()->getLibelle() === self::ETAT_OUVERTE;
    }

    /**
     *
     * Retourne un booléen en fonction de si la sortie devrait être classée comme "Clôturée"
     *
     * @param Sortie $sortie
     * @return bool
     */
    public function changementEtatSortieCloturee(Sortie $sortie): bool
    {
        $now = new \DateTime();
        // Vérifie si la sortie est en état "Ouverte" et n'est pas déjà en état "Clôturée"
        if (
            $sortie->getEtat()->getLibelle() === self::ETAT_OUVERTE
            && $sortie->getEtat()->getLibelle() !== self::ETAT_CLOTUREE
        ) {
            // Vérifie si la date du jour est inférieure à la date de début de la sortie
            if ($now < $sortie->getDateDebut()) {
                // Vérifie si la date du jour est supérieure ou égale à la date limite d'inscription
                // ou si le nombre de participants atteint le nombre maximum d'inscriptions
                if (
                    $now >= $sortie->getDateLimiteInscription()
                    || $sortie->getParticipants()->count() >= $sortie->getNbInscritptionMax()
                ) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     *
     * Retourne true si la sortie peut être "Annulée""
     *
     * @param Sortie $sortie
     * @return bool
     */
    public function changementEtatSortieAnnulee(Sortie $sortie): bool
    {
        //doit être en statut "Ouverte" ou "Clôturée" pour retourner true
        return $sortie->getEtat()->getLibelle() === self::ETAT_OUVERTE
            || $sortie->getEtat()->getLibelle() === self::ETAT_CLOTUREE;
    }

    /**
     * Retourne un booléen en fonction de si la sortie devrait être classée comme "Activité En Cours"
     *
     * @param Sortie $sortie
     * @return bool
     */
    public function changementEtatSortieEnCours(Sortie $sortie): bool
    {
        $now = new \DateTime();
        if (
            $sortie->getEtat()->getLibelle() === self::ETAT_CLOTUREE
            && $sortie->getDateDebut() < $now
            && $sortie->getDateFin() > $now
            && $sortie->getEtat()->getLibelle() !== self::ETAT_ACTIVITE_EN_COURS
        ){
            return true;
        }
        return false;
    }

    /**
     *
     * Retourne un booléen en fonction de si la sortie devrait être classée comme "Passée"
     *
     * @param Sortie $sortie
     * @return bool
     */
    public function changementEtatSortiePassee(Sortie $sortie): bool
    {
        $now = new \DateTime();
        $dateNowMoinsUnMois = new \DateTime("-1 month");
        if (
            $sortie->getEtat()->getLibelle() === self::ETAT_ACTIVITE_EN_COURS
            && $sortie->getDateFin() >= $dateNowMoinsUnMois
            && $sortie->getDateFin() <= $now
            && $sortie->getEtat()->getLibelle() !== self::ETAT_PASSEE
        ){
            return true;
        }
        return false;
    }

    /**
     * Retourne un booléen en fonction de si la sortie devrait être classée comme "Archivée"
     *
     * @param Sortie $sortie
     * @return bool
     */
    public function changementEtatSortieArchivee(Sortie $sortie): bool
    {
        $dateNowMoinsUnMois = new \DateTime("-1 month");
        if (
            $sortie->getDateFin() < $dateNowMoinsUnMois
            && $sortie->getEtat()->getLibelle() !== self::ETAT_ARCHIVEE
        ){
            return true;
        }
        return false;
    }

    /**
     * Déduit l'état d'une sortie, utile pour les fixtures
     * grosse duplication des méthode ci-dessus, mais pas trop le choix
     */
    public function getEtatSortie(Sortie $sortie): string
    {
        $now = new \DateTime();
        $dateNowMoinsUnMois = new \DateTime("-1 month");

        if ($sortie->getDateFin() < $dateNowMoinsUnMois){
            return "Archivée";
        }

        if ($sortie->getDateFin() >= $dateNowMoinsUnMois &&
            $sortie->getDateFin() <= $now){
            return "Passée";
        }

        if ($sortie->getDateDebut() < $now &&
            $sortie->getDateFin() > $now){
            return "Activité en cours";
        }

        if ($sortie->getDateLimiteInscription() <= $now &&
            $sortie->getDateDebut() > $now){
            return "Clôturée";
        }

        if ($sortie->getDateDebut() > $now &&
            $sortie->getDateLimiteInscription() > $now){
            return "Ouverte";
        }

        return "Créée";
    }
}