<?php

namespace App\Controller;

use App\Entity\Reclamation;
use App\Entity\User;
use App\Form\ReclamationType;
use App\Repository\ReclamationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;

#[Route('/reclamation')]
class ReclamationController extends AbstractController
{
    private function getFakeRole(): string
    {
        return 'admin'; // ou 'agent' / 'client'
    }

    private function getFakeUser(EntityManagerInterface $em): ?User
    {
        return $em->getRepository(User::class)->findOneBy(['email' => 'oussema_666@outlook.fr']);
    }

    #[Route('/test', name: 'gestion-test')]
    public function test(): Response
    {
        return $this->render('reclamation/test/gestion_test.html.twig');
    }

    #[Route('/admin/home', name: 'reclamation_home_admin')]
    public function adminHome(): Response
    {
        return $this->render('reclamation/admin/home.html.twig');
    }

    #[Route('/', name: 'reclamation_index', methods: ['GET'])]
    public function index(Request $request, ReclamationRepository $reclamationRepository, EntityManagerInterface $em): Response
    {
        $role = $this->getFakeRole();
        $user = $this->getFakeUser($em);

        $keyword = $request->query->get('search');
        $sort = $request->query->get('sort', 'date');
        $order = $request->query->get('order', 'DESC');

        $query = $role === 'admin'
            ? $reclamationRepository->searchAndSortQuery($keyword, $sort, $order)
            : $reclamationRepository->searchAndSortByUserQuery($user, $keyword, $sort, $order);

        $reclamations = $query->getResult();

        return $this->render("reclamation/{$role}/index.html.twig", [
            'reclamations' => $reclamations,
            'search' => $keyword,
            'sort' => $sort,
            'order' => $order,
        ]);
    }

    #[Route('/new', name: 'reclamation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $reclamation = new Reclamation();
        $role = $this->getFakeRole();
        $user = $this->getFakeUser($em);

        $reclamation->setDate(new \DateTime());

        if ($role !== 'admin') {
            $reclamation->setStatus('En attente');
        }

        $form = $this->createForm(ReclamationType::class, $reclamation, [
            'user_role' => $role,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $reclamation->setUser($user);
            $em->persist($reclamation);
            $em->flush();

            $this->addFlash('success', '✅ Réclamation ajoutée avec succès.');
            return $this->redirectToRoute('reclamation_index');
        }

        return $this->render("reclamation/{$role}/new.html.twig", [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/show/{id}', name: 'reclamation_show', methods: ['GET'])]
    public function show(int $id, ReclamationRepository $reclamationRepository): Response
    {
        $reclamation = $reclamationRepository->find($id);
        if (!$reclamation) {
            throw $this->createNotFoundException('Réclamation non trouvée.');
        }

        $role = $this->getFakeRole();

        return $this->render("reclamation/{$role}/show.html.twig", [
            'reclamation' => $reclamation,
        ]);
    }

    #[Route('/{id}/edit', name: 'reclamation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id, ReclamationRepository $reclamationRepository, EntityManagerInterface $em, MailerInterface $mailer): Response
    {
        $reclamation = $reclamationRepository->find($id);
        if (!$reclamation) {
            throw $this->createNotFoundException('Réclamation non trouvée.');
        }

        $role = $this->getFakeRole();
        $originalStatus = $reclamation->getStatus();
        $originalCategory = $reclamation->getCategory();
        $originalIssue = $reclamation->getIssue();

        $form = $this->createForm(ReclamationType::class, $reclamation, [
            'user_role' => $role,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($role === 'admin') {
                $reclamation->setCategory($originalCategory);
                $reclamation->setIssue($originalIssue);

                if ($originalStatus !== $reclamation->getStatus()) {
                    $userEmail = $reclamation->getUser()?->getEmail() ?? 'omsehli@gmail.com';

                    $email = (new Email())
                        ->from('oussema.msehli@esprit.com')
                        ->to($userEmail)
                        ->subject('📬 Statut mis à jour')
                        ->html("
                        <p>Bonjour,</p>
                        <p>Le statut de votre réclamation a changé :</p>
                        <ul>
                        <li><strong>Problème :</strong> {$reclamation->getIssue()}</li>
                        <li><strong>Nouveau statut :</strong> {$reclamation->getStatus()}</li>
                        </ul>
                        <p>Merci pour votre confiance.<br>– EasyTrip</p>
                    ");

                    $mailer->send($email);
                    $this->addFlash('success', '✉️ Email envoyé à ' . $userEmail);
                }
            } else {
                $reclamation->setStatus($originalStatus);
            }

            $em->flush();
            $this->addFlash('success', '✅ Réclamation modifiée avec succès.');

            return $this->redirectToRoute('reclamation_edit', ['id' => $id]);
        }

        return $this->render("reclamation/{$role}/edit.html.twig", [
            'form' => $form->createView(),
            'reclamation' => $reclamation,
        ]);
    }

    #[Route('/{id}', name: 'reclamation_delete', methods: ['POST'])]
    public function delete(Request $request, int $id, ReclamationRepository $reclamationRepository, EntityManagerInterface $em): Response
    {
        $reclamation = $reclamationRepository->find($id);
        if (!$reclamation) {
            throw $this->createNotFoundException('Réclamation non trouvée.');
        }

        if ($this->isCsrfTokenValid('delete' . $reclamation->getId(), $request->request->get('_token'))) {
            $em->remove($reclamation);
            $em->flush();
            $this->addFlash('success', '🖑️ Réclamation supprimée avec succès.');
        } else {
            $this->addFlash('error', '❌ Échec de la suppression de la réclamation.');
        }

        return $this->redirectToRoute('reclamation_index');
    }

    #[Route('/mail-test', name: 'mail_test')]
    public function testMail(MailerInterface $mailer): Response
    {
        $email = (new Email())
            ->from('omsehli@gmail.com')
            ->to('oussema_666@outlook.fr')
            ->subject('🚀 Test d’envoi réel depuis Symfony')
            ->text('Ceci est un test réel envoyé via Gmail SMTP.');

        $mailer->send($email);

        $this->addFlash('success', '✅ Email réel envoyé (si la configuration Gmail est bonne)');
        return $this->redirectToRoute('reclamation_index');
    }

    #[Route('/{id}/send-mail', name: 'reclamation_send_mail', methods: ['GET'])]
    public function sendMailManual(int $id, ReclamationRepository $repo, MailerInterface $mailer): Response
    {
        $reclamation = $repo->find($id);
        if (!$reclamation) {
            throw $this->createNotFoundException('Réclamation introuvable');
        }

        $email = (new Email())
            ->from('oussema.msehli@esprit.com')
            ->to($reclamation->getUser()?->getEmail() ?? 'oussema.msehli@esprit.com')
            ->subject('📬 Suivi de votre réclamation')
            ->html("<p>Bonjour,</p><p>Un administrateur vient de vous envoyer une notification liée à votre réclamation : <strong>{$reclamation->getIssue()}</strong>.</p><p><strong>Statut actuel :</strong> {$reclamation->getStatus()}</p><hr><p>L’équipe EasyTrip</p>");

        $mailer->send($email);

        $this->addFlash('success', '✉️ Email envoyé avec succès !');
        return $this->redirectToRoute('reclamation_edit', ['id' => $id]);
    }
}
