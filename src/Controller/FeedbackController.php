<?php

namespace App\Controller;

use App\Entity\Feedback;
use App\Entity\Hotels;
use App\Entity\OfferTravel;
use App\Entity\Tickets;
use App\Entity\User;
use App\Form\FeedbackType;
use App\Repository\FeedbackRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Dompdf;
use Dompdf\Options;


#[Route('/feedback')]
class FeedbackController extends AbstractController
{
    #[Route('/home/admin', name: 'feedback_home_admin', methods: ['GET'])]
    public function homeAdmin(): Response
    {
        return $this->render('feedback/admin/home.html.twig');
    }

    #[Route('/', name: 'feedback_index', methods: ['GET'])]
    public function index(Request $request, FeedbackRepository $feedbackRepository, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $role = $this->getUserRole($user);

        $query = $request->query->get('q');
        $sort = $request->query->get('sort', 'date');
        $dir = $request->query->get('dir', 'DESC');
        $type = $request->query->get('type');

        if ($role === 'agent') {

            $hotels = $em->getRepository(Hotels::class)->findBy(['user' => $user]);
            $tickets = $em->getRepository(Tickets::class)->findBy(['user' => $user]);
            $travels = $em->getRepository(OfferTravel::class)->findBy(['user' => $user]);

            return $this->render('feedback/agent/index.html.twig', [
                'hotels' => $hotels,
                'tickets' => $tickets,
                'travels' => $travels,
            ]);
        }

        if ($role === 'client') {
            $feedbacks = $feedbackRepository->findByUserAndSearch($user, $query, $sort, $dir);
        } else {
            $feedbacks = $feedbackRepository->findBySearchSortAndType($query, $sort, $dir, $type)->getResult();
        }

        return $this->render("feedback/{$role}/index.html.twig", [
            'feedbacks' => $feedbacks,
        ]);
    }

    #[Route('/search', name: 'feedback_search', methods: ['GET'])]
    public function search(Request $request, FeedbackRepository $feedbackRepository): Response
{
    $search = $request->query->get('q'); 
    $sort = $request->query->get('sort', 'date');
    $dir = $request->query->get('dir', 'DESC');
    $type = $request->query->get('type');

    $feedbacks = $feedbackRepository->findBySearchSortAndType($search, $sort, $dir, $type)->getResult();

    return $this->render('feedback/admin/_feedbacks_list.html.twig', [
        'feedbacks' => $feedbacks
    ]);
}

    


    #[Route('/new/{type}/{id}', name: 'feedback_new', methods: ['GET', 'POST'])]
    public function new(string $type, int $id, Request $request, EntityManagerInterface $em): Response
    {
        $feedback = new Feedback();
        $user = $this->getUser();
        $feedback->setUser($user);
        $entity = match ($type) {
            'hotel' => $em->getRepository(Hotels::class)->find($id),
            'ticket' => $em->getRepository(Tickets::class)->find($id),
            'travel' => $em->getRepository(OfferTravel::class)->find($id),
            default => null,
        };

        if (!$entity) throw $this->createNotFoundException("Offre introuvable.");

        match ($type) {
            'hotel' => $feedback->setHotel($entity),
            'ticket' => $feedback->setTicket($entity),
            'travel' => $feedback->setTravel($entity),
        };

        $form = $this->createForm(FeedbackType::class, $feedback);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($feedback);
            $em->flush();
            $this->addFlash('success', '✅ Feedback ajouté avec succès.');
            return $this->redirectToRoute('feedback_index');
        }

        return $this->render('feedback/client/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/show/{id}', name: 'feedback_show', methods: ['GET'])]
    public function show(Feedback $feedback): Response
    {
        $role = $this->getUserRole($this->getUser());

        return $this->render("feedback/{$role}/show.html.twig", [
            'feedback' => $feedback,
        ]);
    }

    #[Route('/{id}/edit', name: 'feedback_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Feedback $feedback, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(FeedbackType::class, $feedback);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', '✅ Feedback modifié avec succès.');
            return $this->redirectToRoute('feedback_index');
        }

        $role = $this->getUserRole($this->getUser());

        return $this->render("feedback/{$role}/edit.html.twig", [
            'form' => $form->createView(),
            'feedback' => $feedback,
        ]);
    }

    #[Route('/{id}', name: 'feedback_delete', methods: ['POST'])]
    public function delete(Request $request, Feedback $feedback, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $feedback->getId(), $request->request->get('_token'))) {
            $em->remove($feedback);
            $em->flush();
            $this->addFlash('success', '🗑️ Feedback supprimé avec succès.');
        }

        return $this->redirectToRoute('feedback_index');
    }

    #[Route('/by-offer/{type}/{id}', name: 'feedback_by_offer', methods: ['GET'])]
    public function feedbacksByOffer(string $type, int $id, FeedbackRepository $repo, Request $request): Response
    {
        $search = $request->query->get('q');
        $sort = $request->query->get('sort', 'date');
        $dir = $request->query->get('dir', 'DESC');

        $feedbacks = $repo->findFeedbacksByOfferFiltered($type, $id, $search, $sort, $dir);

        return $this->render('feedback/agent/feedbacks_by_offer.html.twig', [
            'feedbacks' => $feedbacks,
            'offerType' => $type,
            'offerId' => $id,
            'q' => $search,
            'sort' => $sort,
            'dir' => $dir,
        ]);
    }

    #[Route('/admin/statistics/evolution', name: 'feedback_evolution')]
    public function evolution(FeedbackRepository $repo): Response
    {
        $data = $repo->getFeedbackCountByDate();

        return $this->render('feedback/admin/evolution.html.twig', [
            'data' => $data,
        ]);
    }

    #[Route('/admin/statistics/comparison', name: 'feedback_month_comparison')]
    public function monthlyComparison(FeedbackRepository $repo): Response
    {
        $data = $repo->getMonthlyComparison();
        $current = $data[0] ?? null;
        $previous = $data[1] ?? null;

        return $this->render('feedback/admin/comparison.html.twig', [
            'current' => $current,
            'previous' => $previous,
        ]);
    }

    #[Route('/admin/statistics/negative', name: 'feedback_negative')]
    public function negativeFeedbacks(FeedbackRepository $repo): Response
    {
        $feedbacks = $repo->findNegativeFeedbacks();

        return $this->render('feedback/admin/negative.html.twig', [
            'feedbacks' => $feedbacks,
        ]);
    }

    #[Route('/admin/pdf/evaluation', name: 'feedback_pdf_evaluation')]
    public function exportEvaluationPdf(FeedbackRepository $repo): Response
    {
        $stats = $repo->getEvaluationStats();
        $feedbacks = $repo->findAll();
        $negatifs = $repo->findNegativeFeedbacks();

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $dompdf = new Dompdf($options);

        $html = $this->renderView('feedback/admin/pdf_base.html.twig', [
            'total' => $stats['total_feedbacks'],
            'moyenne' => $stats['average_rating'],
            'feedbacks' => $feedbacks,
            'negatifs' => $negatifs,
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="rapport_feedbacks.pdf"',
        ]);
        
    }

    #[Route('/admin/pdf/evolution', name: 'feedback_pdf_evolution')]
public function exportEvolutionPdf(FeedbackRepository $repo): Response
{
    $data = $repo->getFeedbackCountByDate();

    $options = new Options();
    $options->set('defaultFont', 'DejaVu Sans');
    $dompdf = new Dompdf($options);

    $html = $this->renderView('feedback/admin/pdf_base.html.twig', [
        'type' => 'evolution',
        'data' => $data,
    ]);

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    return new Response($dompdf->output(), 200, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'inline; filename="evolution_feedbacks.pdf"',
    ]);
}

#[Route('/admin/pdf/comparison', name: 'feedback_pdf_comparison')]
public function exportComparisonPdf(FeedbackRepository $repo): Response
{
    $data = $repo->getMonthlyComparison();
    $current = $data[0] ?? null;
    $previous = $data[1] ?? null;

    $options = new Options();
    $options->set('defaultFont', 'DejaVu Sans');
    $dompdf = new Dompdf($options);

    $html = $this->renderView('feedback/admin/pdf_base.html.twig', [
        'type' => 'comparison',
        'current' => $current,
        'previous' => $previous,
    ]);

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    return new Response($dompdf->output(), 200, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'inline; filename="comparaison_feedbacks.pdf"',
    ]);
}

#[Route('/admin/pdf/negative', name: 'feedback_pdf_negative')]
public function exportNegativeFeedbacksPdf(FeedbackRepository $repo): Response
{
    $feedbacks = $repo->findNegativeFeedbacks();

    $options = new Options();
    $options->set('defaultFont', 'DejaVu Sans');
    $dompdf = new Dompdf($options);

    $html = $this->renderView('feedback/admin/pdf_base.html.twig', [
        'type' => 'negative',
        'feedbacks' => $feedbacks,
    ]);

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    return new Response($dompdf->output(), 200, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'inline; filename="feedbacks_negatifs.pdf"',
    ]);
}




    private function getUserRole(User $user): string
    {
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) return 'admin';
        if (in_array('ROLE_AGENT', $user->getRoles(), true)) return 'agent';
        return 'client';
    }
}