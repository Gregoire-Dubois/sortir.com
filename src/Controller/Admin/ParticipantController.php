<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
        return $this->render('admin/utilisateur/charger_fichier_csv.html.twig', [

        ]);
    }

    /**
     * @Route ("/admin/utilisateurs", name="admin_listeUtilisateur")
     */
    public function listeUtilisateur(): Response
    {
        return $this->render('admin/utilisateur/liste.html.twig', [

        ]);
    }

    /**
     * @Route ("/admin/utilisateurs/bannir/{id}", name="admin_bannirUtilisateur")
     */
    public function bannirUtilisateur(int $id): Response
    {
        return $this->render( []);
    }

    /**
     * @Route ("/admin/utilisateurs/supprimer/{id}", name="admin_supprimerUtilisateur")
     */
    public function supprimerUtilisateur(int $id): Response
    {
        return $this->render( []);
    }
}
