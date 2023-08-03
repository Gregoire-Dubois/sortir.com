<?php

namespace App\Controller\Admin;

use App\Entity\Ville;
use App\Entity\Campus;
use App\Form\Admin\RechercheCampusType;
use App\Form\Admin\RechercheVilleType;
use App\Form\Admin\VilleType;
use App\Form\Admin\CampusType;
use App\Repository\CampusRepository;
use App\Repository\ParticipantRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
    ): Response {
        // Partie Recherche par le nom de la ville
        $rechercheVilleForm = $this->createForm(RechercheVilleType::class);
        $rechercheVilleForm->handleRequest($request);

        $villes = $this->handleRechercheVilleForm($rechercheVilleForm, $villeRepository);

        // Partie Recherche par le nom du campus
        $rechercheCampusForm = $this->createForm(RechercheCampusType::class);
        $rechercheCampusForm->handleRequest($request);

        $campus = $this->handleRechercheCampusForm($rechercheCampusForm, $campusRepository);

        // Partie formulaire pour la création d'une nouvelle ville
        $newVille = new Ville();
        $villeForm = $this->createForm(VilleType::class, $newVille);
        $villeForm->handleRequest($request);
        if ($villeForm->isSubmitted() && $villeForm->isValid()) {
            $entityManager->persist($newVille);
            $entityManager->flush();
            $this->addFlash('success_ajout_ville', $newVille. ' a bien été ajouté à la liste des ville');

            return $this->redirectToRoute('admin_villes_et_campus');
        }

        // Partie formulaire pour la création d'un nouveau campus
        $newCampus = new Campus();
        $campusForm = $this->createForm(CampusType::class, $newCampus);
        $campusForm->handleRequest($request);
        if ($campusForm->isSubmitted() && $campusForm->isValid()) {
            $entityManager->persist($newCampus);
            $entityManager->flush();
            $this->addFlash('success_ajout_campus', $newCampus. ' a bien été ajouté à la liste des campus');

            return $this->redirectToRoute('admin_villes_et_campus');
        }

        $listeVilles = $villeRepository->findAll();
        $listeCampus = $campusRepository->findAll();

        return $this->render('admin/gestionVillesEtCampus.html.twig', [
            'listeVilles' => $listeVilles,
            'listeCampus' => $listeCampus,
            'villes' => $villes,
            'rechercheVilleForm' => $rechercheVilleForm->createView(),
            'villeForm' => $villeForm->createView(),
            'campus' => $campus,
            'rechercheCampusForm' => $rechercheCampusForm->createView(),
            'campusForm' => $campusForm->createView(),
        ]);
    }

    private function handleRechercheVilleForm($form, $repository)
    {
        if ($form->isSubmitted() && $form->isValid()) {
            $dataVille = $form->getData();
            $rechercheVille = $dataVille['rechercheNomVille'];

            if ($rechercheVille) {
                $villes = $repository->rechercheParNomVille($rechercheVille);

                if (empty($villes)) {
                    $this->addFlash('error_recherche_ville', 'Aucune ville trouvée pour la recherche : ' . $rechercheVille);
                    $villes = $repository->findAll();
                }
            } else {
                $villes = $repository->findAll();
            }
        } else {
            $villes = $repository->findAll();
        }

        return $villes;
    }

    private function handleRechercheCampusForm($form, $repository)
    {
        if ($form->isSubmitted() && $form->isValid()) {
            $dataCampus = $form->getData();
            $rechercheCampus = $dataCampus['rechercheNomCampus'];

            if ($rechercheCampus) {
                $campus = $repository->rechercheParNomCampus($rechercheCampus);

                if (empty($campus)) {
                    $this->addFlash('error_recherche_campus', 'Aucun campus trouvé pour la recherche : ' . $rechercheCampus);
                    $campus = $repository->findAll();
                }
            } else {
                $campus = $repository->findAll();
            }
        } else {
            $campus = $repository->findAll();
        }

        return $campus;
    }

    /**
     * @Route("/ville/{id}/supprimer", name="supprimer_ville")
     */
    public function supprimerVille(Request $request, EntityManagerInterface $entityManager, Ville $ville): Response
    {
        $entityManager->remove($ville);
        $entityManager->flush();

        $this->addFlash('success_suppression_ville', $ville .' a été supprimée de la liste des villes.');

        // Redirigez vers la même page après la suppression
        return $this->redirectToRoute('admin_villes_et_campus');
    }

    /**
     * @Route("/campus/{id}/supprimer", name="supprimer_campus")
     */
    public function supprimerCampus(int $id, EntityManagerInterface $entityManager, CampusRepository $campusRepository, ParticipantRepository $participantRepository): Response
    {
        $campus = $campusRepository->find($id);
        $countParticipant = $participantRepository->countByCampus($id);
        if ($countParticipant === 0) {
            $entityManager->remove($campus);
            $entityManager->flush();

            $this->addFlash('success_suppression_campus', $campus . ' a été supprimée de la liste des campus.');
            // Redirigez vers la même page après la suppression
            return $this->redirectToRoute('admin_villes_et_campus');
        } else {
            $this->addFlash('error', 'Impossible de supprimer le campus car il a des participants associés');
            return $this->redirectToRoute('admin_villes_et_campus');
        }
    }

    /**
     * @Route("/ville/{id}/modifier", name="modifier_ville", methods={"POST"})
     */
    public function modifierVille(int $id, Request $request, VilleRepository $villeRepository, EntityManagerInterface $em): Response
    {

        $ville = $em->getRepository(Ville::class)->find($id);
        $nom = $request->request->get('edit_nom_' . $id);
        $codePostal = $request->get('edit_code_postal_' . $id);
        $villeRepository->modifierVille($id, $nom, $codePostal);

        $this->addFlash('success_modification_ville', $ville .' a été modifiée.');

        // Redirigez vers la même page après la suppression
        return $this->redirectToRoute('admin_villes_et_campus');

        /*
        $form = $this->createForm(VilleType::class, $ville)
            ->add('nom', TextType::class)
            ->add('codePostal', TextType::class);


        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Enregistrez les modifications dans la base de données
            $entityManager->flush();

            $this->addFlash('success_modification_ville', $ville .' a été modifiée avec succès.');

            return $this->redirectToRoute('admin_villes_et_campus');
        }

        // Affichez le formulaire dans le modal
        return $this->render('admin/popup/modifierVilleEtCampus.html.twig', [
            'villeForm' => $form->createView(),
            'is_ville' => true,
            'entity' => $ville,
        ]);
        */
    }

    /**
     * @Route("/campus/{id}/modifier", name="modifier_campus", methods={"POST"})
     * @throws Exception
     */
    public function modifierCampus(Request $request, EntityManagerInterface $entityManager, Campus $campus, int $id, CampusRepository $campusRepository): Response
    {

        $campus = $entityManager->getRepository(Campus::class)->find($id);
        $nom = $request->request->get('edit_nom_' . $id );
        var_dump($nom . $id);
        $campusRepository -> modifierCampus($id, $nom);

        $this -> addFlash('modifier_campus', $campus .' a été modifiée.');

        return $this->redirectToRoute('admin_villes_et_campus');

/*
        // Créez le formulaire VilleType avec "data_class" défini à null
        $form = $this->createForm(VilleType::class, null, ['data_class' => null])
            ->add('nom', TextType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Enregistrez les modifications dans la base de données
            $entityManager->flush();

            $this->addFlash('success_modification_campus', $campus .' a été modifié avec succès.');

            return $this->redirectToRoute('admin_villes_et_campus');
        }

        // Affichez le formulaire dans le modal
        return $this->render('admin/popup/modifierVilleEtCampus.html.twig', [
            'campusForm' => $form->createView(),
            'is_ville' => false,
            'entity' => $campus,
        ]);
*/

    }

    /**
     * @Route("/{type}/{id}/modifier", name="modifier_entity")
     * @throws Exception
     */
    public function modifierEntity(Request $request, EntityManagerInterface $entityManager, $type, $id): Response
    {
        // Vérifiez le type d'entité (Ville ou Campus)
        if ($type === 'ville') {
            $repository = $entityManager->getRepository(Ville::class);
            $isVille = true;
        } elseif ($type === 'campus') {
            $repository = $entityManager->getRepository(Campus::class);
            $isVille = false;
        } else {
            throw new Exception("Type d'entité non pris en charge.");
        }

        $entity = $repository->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Entité non trouvée.');
        }

        // Créez le formulaire en fonction de l'entité
        if ($type === 'ville') {
            $form = $this->createForm(VilleType::class, $entity)
                ->add('nom', TextType::class)
                ->add('codePostal', TextType::class);
        } else {
            $form = $this->createForm(CampusType::class, $entity)
                ->add('nom', TextType::class);
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Enregistrez les modifications dans la base de données
            $entityManager->flush();

            $this->addFlash('success_modification', 'Modification réussie.');

            return $this->redirectToRoute('admin_villes_et_campus');
        }

        // Affichez le formulaire dans le modal
        dump($isVille);
        dump($type);
        dump($id);
        return $this->render('admin/gestionVillesEtCampus.html.twig', [
            'form' => $form->createView(),
            'is_ville' => $isVille,
            'entity_type' => $type,
            'entity_id' => $id
        ]);
	}
}

