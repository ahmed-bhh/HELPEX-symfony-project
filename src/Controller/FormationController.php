<?php

namespace App\Controller;

use App\Entity\Formation;
use App\Form\FormationType;
use App\Repository\FormationRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/formation')]
class FormationController extends AbstractController
{
    #[Route('/', name: 'app_formation_index', methods: ['GET']), IsGranted('ROLE_ADMIN')]
    public function index(FormationRepository $formationRepository): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        return $this->render('formation/index.html.twig', [
            'user' => $this->getUser(),
            'formations' => $formationRepository->findAll()
        ]);
    }

    #[Route('/front', name: 'app_formation_index_front', methods: ['GET'])]
    public function indexfront(FormationRepository $formationRepository): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        return $this->render('formation/index_front.html.twig', [
            'formations' => $formationRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_formation_new', methods: ['GET', 'POST']),IsGranted('ROLE_ADMIN')]
    public function new(Request $request, FormationRepository $formationRepository , SluggerInterface $slugger): Response
    {
        $formation = new Formation();
        $form = $this->createForm(FormationType::class, $formation);
        $form->handleRequest($request);




        if ($form->isSubmitted() && $form->isValid()) {

            $pictureFile = $form->get('iamgeformation')->getData();
            if ($pictureFile) {
                $originalFilename = pathinfo($pictureFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $pictureFile->guessExtension();

                try {
                    $pictureFile->move(
                        $this->getParameter('users_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // handle exception
                }

                $formation->setIamgeformation($newFilename);
            }
                $formationRepository->save($formation, true);

                return $this->redirectToRoute('app_formation_index', [], Response::HTTP_SEE_OTHER);

        }

        return $this->renderForm('formation/new.html.twig', [
            'formation' => $formation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_formation_show', methods: ['GET']),IsGranted('ROLE_ADMIN')]
    public function show(Formation $formation): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        return $this->render('formation/show.html.twig', [
            'formation' => $formation,
        ]);
    }

    #[Route('/front/{id}', name: 'app_formation_show_front', methods: ['GET'])]
    public function show_front(Formation $formation): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        return $this->render('formation/show_front.html.twig', [
            'formation' => $formation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_formation_edit', methods: ['GET', 'POST']),IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, Formation $formation, FormationRepository $formationRepository,SluggerInterface $slugger): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        $form = $this->createForm(FormationType::class, $formation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $pictureFile = $form->get('iamgeformation')->getData();
            if ($pictureFile) {
                $originalFilename = pathinfo($pictureFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $pictureFile->guessExtension();

                try {
                    $pictureFile->move(
                        $this->getParameter('users_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // handle exception
                }

                $formation->setIamgeformation($newFilename);
            }
            $formationRepository->save($formation, true);

            return $this->redirectToRoute('app_formation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('formation/edit.html.twig', [
            'formation' => $formation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_formation_delete', methods: ['POST']),IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Formation $formation, FormationRepository $formationRepository): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        if ($this->isCsrfTokenValid('delete'.$formation->getId(), $request->request->get('_token'))) {
            $formationRepository->remove($formation, true);
        }

        return $this->redirectToRoute('app_formation_index', [], Response::HTTP_SEE_OTHER);
    }


    #[Route('/front/bycategory/{id}', name: 'app_formation_bycategory')]
    public function getByCategorie($id,FormationRepository $repo) : Response {

        $formation = $repo->getBycategory($id);
        return $this->renderForm('formation\index_front_bycategorie.html.twig', [
            'formations' => $formation,

        ]);
    }
    #[Route('/front/bycentre/{id}', name: 'app_formation_bycentre')]
    public function getByCentre($id,FormationRepository $repo) : Response {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        $formation = $repo->getBycentre($id);
        return $this->renderForm('formation\index_front_bycentre.html.twig', [
            'formations' => $formation,

        ]);
    }
}
