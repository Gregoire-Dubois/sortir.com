<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\SortiesFilterType;
use App\Form\SortieType;
use App\Repository\SortieRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SortieController extends AbstractController
{
    /**
     * @Route("/", name="sortie_listeSortie")
     */
    public function listeSortie(SortieRepository $sortieRepository, Request $request): Response
    {

        $sortieForm = $this->createForm(SortiesFilterType::class);

        $sortieForm->handleRequest($request);
        if ($sortieForm->isSubmitted())
        {
            dump($sortieForm);
        }

        $sortiesAll = $sortieRepository-> selectAllSorties();

        return $this->render('sortie/liste.html.twig', [
            'sorties' => $sortiesAll,
            'sortieForm' => $sortieForm->createView(),

        ]);

    }

    /**
     * @Route("/sorties/detail/{id}", name="sortie_detailSortie")
     * @throws NonUniqueResultException
     */
    public function detailSortie(int $id, SortieRepository $sortieRepository): Response
    {

        $sortie = $sortieRepository->findSortieByIdWithDetails($id);

        if (!$sortie) {
            throw $this->createNotFoundException('Sortie non trouvée.');
        }

        return $this->render('sortie/detail.html.twig', [
            'sortie' => $sortie,
        ]);

    }

    /**
     * @Route("/sorties/creation", name="sortie_creerSortie")
     */
    public function creerSortie(): Response
    {
        $sortie = New Sortie();
        $sortieType = $this->createForm(SortieType::class, $sortie);
        return $this->render('sortie/test/creation.html.twig', [
            'SortieType' => $sortieType->createView()
        ]);
    }

    /**
     * @Route("/sorties/publier/{id}", name="sortie_publierSortie")
     */
    public function publierSortie(int $id): Response
    {
        return $this->redirectToRoute('sortie_listeSortie');
    }

    /**
     * @Route("/sorties/annuler/{id}", name="sortie_annulerSortie")
     */
    public function annulerSortie(int $id): Response
    {
        return $this->redirectToRoute('sortie_listeSortie');
    }
}
