<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Ville;
use App\Form\LieuType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LieuController extends AbstractController
{
    /**
     * @Route("/sorties/lieu/afficher", name="lieu_afficher", methods={"GET"})
     */

    //Sert à l'affichage du formulaire dans la pop-up
    public function create(Request $request, EntityManagerInterface $em)
    {
        // Créer une instance du formulaire LieuType
        $lieu = new Lieu();
        $lieuType = $this->createForm(LieuType::class, $lieu);

        return $this->render('lieu/creation.html.twig', [
            'LieuType' => $lieuType->createView()
        ]);
    }

    /**
     * @Route("/sorties/lieu/creation", name="lieu_creation_submit", methods={"POST"})
     */

    //Sert à la soumission du formulaire dans la pop-up
    public function createSubmit(Request $request, EntityManagerInterface $em)
    {
        // Créer une instance du formulaire LieuType
        $lieu = new Lieu();
        $lieuType = $this->createForm(LieuType::class, $lieu);

        // Gérer la soumission du formulaire
        $lieuType->handleRequest($request);

        // Vérifier si le formulaire est soumis et valide
        if ($lieuType->isSubmitted() && $lieuType->isValid()) {
            // Enregistrer les données en base de données
            $em->persist($lieu);
            $em->flush();

            // Réponse JSON pour indiquer le succès de l'enregistrement
            return $this->json([
                'success' => true,
                'message' => 'Le lieu a été créé avec succès!',
            ]);
        } else {
            // Réponse JSON pour renvoyer les erreurs en cas d'échec de validation du formulaire
            $errors = $this->getFormErrors($lieuType);
            return $this->json([
                'success' => false,
                'errors' => $errors,
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    private function getFormErrors($form)
    {
        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $errors[] = $error->getMessage();
        }
        return $errors;
    }


    /**
     * Méthode appelée en AJAX pour retourner le CP d'une ville donnée.
     * @Route("/get-code-postal/{id}", name="get_cp_par_ville", methods={"GET"})
     */
    public function findCPParVille(Ville $ville): JsonResponse
    {
        $codePostal = $ville->getCodePostal();

        return new JsonResponse($codePostal);
    }

    /**
     * Méthode appelée en AJAX pour retourner la rue d'un lieu donné.
     * @Route("/get-rue/{id}", name="get_rue_lieu", methods={"GET"})
     */
    public
    function findStreetByLocation(Lieu $lieu): JsonResponse
    {
        $rue = $lieu->getRue();
        $latitude = $lieu->getLatitude();
        $longitude = $lieu->getLongitude();

        $data = [
            'rue' => $rue,
            'latitude' => $latitude,
            'longitude' => $longitude,
        ];


        return new JsonResponse($data);
    }
}
