<?php

namespace App\Controller\Admin;

use App\Entity\Ville;
use App\Entity\Campus;
use App\Form\Admin\RechercheCampusType;
use App\Form\Admin\RechercheVilleType;
use App\Form\Admin\VilleType;
use App\Form\Admin\CampusType;
use App\Repository\CampusRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin", name="admin_")
 */
class VilleEtCampusController extends AbstractController
{
    /**
     * @Route("/villes_et_campus", name="villes_et_campus")
     */
    public function listeVillesEtCampus(
        Request $request,
        EntityManagerInterface $entityManager,
        VilleRepository $villeRepository,
        CampusRepository $campusRepository
    ): Response

    {
        // Récupérer toutes les villes par défaut
        $listeVilles = $villeRepository->findAll();

        // Partie Recherche par le nom de la ville
        $rechercheVilleForm = $this->createForm(RechercheVilleType::class);
        $rechercheVilleForm->handleRequest($request);

        $villes = [];

        if ($rechercheVilleForm->isSubmitted() && $rechercheVilleForm->isValid()) {
            // Obtenez les données du formulaire
            $dataVille = $rechercheVilleForm->getData();

            // Effectuez la recherche des villes par le nom
            $rechercheVille = $dataVille['rechercheNom'];
            if ($rechercheVille) {
                $villes = $villeRepository->rechercheParNomVille($rechercheVille);
            }
        }


        // Partie formulaire pour la création d'une nouvelle ville
        $newVille = new Ville();
        $villeForm = $this->createForm(VilleType::class, $newVille);
        $villeForm->handleRequest($request);
        if ($villeForm->isSubmitted() && $villeForm->isValid()) {
            $entityManager->persist($newVille);
            $entityManager->flush();

            return $this->redirectToRoute('admin_villes_et_campus');
        }

        // Récupérer tous les campus par défaut
        $listeCampus = $campusRepository->findAll();

        // Partie Recherche par le nom du campus
        $rechercheCampusForm = $this->createForm(RechercheCampusType::class);
        $rechercheCampusForm->handleRequest($request);

        $campus = [];

        if ($rechercheCampusForm->isSubmitted() && $rechercheCampusForm->isValid()) {
            // Obtenez les données du formulaire
            $dataCampus = $rechercheCampusForm->getData();

            // Effectuez la recherche des villes par le nom
            $rechercheCampus = $dataCampus['rechercheNom'];
            if ($rechercheCampus) {
                $campus = $campusRepository->rechercheParNomCampus($rechercheCampus);
            }
        }

        // Partie formulaire pour la création d'une nouvelle ville
        $newCampus = new Campus();
        $campusForm = $this->createForm(CampusType::class, $newCampus);
        $campusForm->handleRequest($request);
        if ($campusForm->isSubmitted() && $campusForm->isValid()) {
            $entityManager->persist($newCampus);
            $entityManager->flush();

            return $this->redirectToRoute('admin_villes_et_campus');
        }

        return $this->render('admin/gestionVillesEtCampus.html.twig', [
            'listeVilles' => $listeVilles,
            'villes' => $villes,
            'rechercheVilleForm' => $rechercheVilleForm->createView(),
            'villeForm' => $villeForm->createView(),
            'listeCampus' => $listeCampus,
            'campus' => $campus,
            'rechercheCampusForm' => $rechercheCampusForm->createView(),
            'campusForm' => $campusForm->createView(),
        ]);

    }
}

