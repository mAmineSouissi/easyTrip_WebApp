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
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/reclamation')]
class ReclamationController extends AbstractController
{
    #[Route('/', name: 'reclamation_index', methods: ['GET'])]
    public function index(Request $request, ReclamationRepository $reclamationRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $role = $this->getUserRole($user);

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
        /** @var User $user */
        $user = $this->getUser();
        $role = $this->getUserRole($user);

        $reclamation->setDate(new \DateTime());

        if ($role === 'agent') {
            $reclamation->setStatus('En attente');
        }

        // ✅ Removed 'user_role' option
        $form = $this->createForm(ReclamationType::class, $reclamation);

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

    #[Route('/{id}', name: 'reclamation_show', methods: ['GET'])]
    public function show(Reclamation $reclamation): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $role = $this->getUserRole($user);

        return $this->render("reclamation/{$role}/show.html.twig", [
            'reclamation' => $reclamation,
        ]);
    }

    #[Route('/{id}/edit', name: 'reclamation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reclamation $reclamation, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ReclamationType::class, $reclamation, [
            'is_edit' => true, // tell the form to lock category and issue
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Only the status will be updated because the other fields are disabled
            $em->flush();

            $this->addFlash('success', '✅ Le statut a été mis à jour avec succès !');

            return $this->redirectToRoute('reclamation_index');
        }

        return $this->render('reclamation/admin/edit.html.twig', [
            'form' => $form,
            'reclamation' => $reclamation,
        ]);
    }



    #[Route('/{id}', name: 'reclamation_delete', methods: ['POST'])]
    public function delete(Request $request, Reclamation $reclamation, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $reclamation->getId(), $request->request->get('_token'))) {
            $em->remove($reclamation);
            $em->flush();

            $this->addFlash('danger', '🗑️ Réclamation supprimée avec succès.');
        }

        return $this->redirectToRoute('reclamation_index');
    }

    private function getUserRole(User $user): string
    {
        return in_array('ROLE_ADMIN', $user->getRoles(), true) ? 'admin' : 'agent';
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
