<?php

namespace App\Controller;

use App\Entity\Reclamation;
use App\Entity\User;
use App\Form\ReclamationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/')]
final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
    #[Route('/about', name: 'app_about')]
    public function aboutUs(): Response
    {
        return $this->render('home/aboutUs.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
    #[Route('/blog', name: 'app_blog')]
    public function blog(): Response
    {
        return $this->render('home/blog.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
    #[Route('/contact', name: 'app_contact', methods: ['GET', 'POST'])]
    public function contact(Request $request, EntityManagerInterface $em): Response
    {
        $reclamation = new Reclamation();
    
        /** @var User $user */
        $user = $this->getUser();
        $role = $user->getRoles();
    
        $reclamation->setDate(new \DateTime());
    
        if ($role === 'agent') {
            $reclamation->setStatus('En attente');
        }
    
        // Création du formulaire (is_edit = false → création)
        $form = $this->createForm(ReclamationType::class, $reclamation, [
            'is_edit' => false
        ]);
    
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // 🔒 Liste des mots interdits
            $forbiddenWords = [
                'fuck', 'pute', 'con', 'spam', 'arnaque', 'merde', 'salope', 'idiot', 'stupid'
            ];
    
            // 🔍 Nettoyage du texte
            $issueText = strtolower(trim($reclamation->getIssue()));
            $issueText = preg_replace('/[^\p{L}\p{N}\s]/u', '', $issueText);
    
            foreach ($forbiddenWords as $word) {
                $pattern = '/\b' . preg_quote($word, '/') . '\b/i';
                if (preg_match($pattern, $issueText)) {
                    $this->addFlash('error', '❌ Votre message contient des mots interdits.');
                    return $this->redirectToRoute('app_contact');
                }
            }
    
            $reclamation->setUser($user);
            $em->persist($reclamation);
            $em->flush();
    
            $this->addFlash('success', '✅ Réclamation envoyée avec succès.');
            return $this->redirectToRoute('app_contact');
        }
    
        return $this->render('home/contact.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
