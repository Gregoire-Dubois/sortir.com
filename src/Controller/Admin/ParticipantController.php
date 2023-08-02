<?php

namespace App\Controller\Admin;

use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ParticipantController extends AbstractController
{
    /**
     * @Route("/admin/utilisateurs/creation", name="admin_creerUtilisateur")
     */
    public function creerUtilisateur(): Response
    {
        return $this->render('admin/participant/creation.html.twig', [

        ]);
    }

    /**
     * @Route ("/admin/utilisateurs/charger-un-fichier-csv", name="admin_chargerFichierCsv")
     */
    public function chargerFichierCsv(): Response
    {
        return $this->render('admin/participant/charger_fichier_csv.html.twig', [

        ]);
    }

    /**
     * @Route ("/admin/utilisateurs", name="admin_listeUtilisateur")
     */
    public function listeUtilisateur(
        ParticipantRepository $participantRepository,
        SortieRepository $sortieRepository
    ): Response {

        $participants = $participantRepository->findAll();

        foreach ($participants as $participant) {
            // Récupérer les sorties associées à chaque participant
            $sorties = $sortieRepository->findSortiesByParticipant($participant);
            $participant->sorties = $sorties;
        }

        return $this->render('admin/participant/gestionParticipants.html.twig', [
            'participants' => $participants,
        ]);
    }

    /**
     * @Route ("/admin/utilisateurs/bannir/{id}", name="admin_bannirUtilisateur")
     */
    public function bannirUtilisateur(int $id): Response
    {
        return $this->redirectToRoute('admin_listeUtilisateur');
    }

    /**
     * @Route ("/admin/utilisateurs/supprimer/{id}", name="admin_supprimerUtilisateur")
     */
    public function supprimerUtilisateur(int $id): Response
    {
        return $this->redirectToRoute('admin_listeUtilisateur');
    }
}
