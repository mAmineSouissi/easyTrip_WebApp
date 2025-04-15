<?php

namespace App\Controller;

use App\Entity\Feedback;
use App\Entity\Hotels;
use App\Entity\Tickets;
use App\Entity\OfferTravel;
use App\Entity\User;
use App\Form\FeedbackType;
use App\Repository\FeedbackRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/feedback')]
class FeedbackController extends AbstractController
{
    private function getFakeRole(): string
    {
        return 'admin';
    }

    #[Route('/home/admin', name: 'feedback_home_admin', methods: ['GET'])]
    public function homeAdmin(): Response
    {
        return $this->render('feedback/admin/home.html.twig');
    }

    #[Route('/', name: 'feedback_index', methods: ['GET'])]
    public function index(Request $request, FeedbackRepository $feedbackRepository, EntityManagerInterface $em): Response
    {
        $role = $this->getFakeRole();
        $query = $request->query->get('q');
        $sort = $request->query->get('sort', 'date');
        $dir = $request->query->get('dir', 'DESC');
        $type = $request->query->get('type');

        if ($role === 'agent') {
            $agent = $em->getRepository(User::class)->find(13);
            if (!$agent) throw $this->createNotFoundException("Agent non trouvé.");

            $hotels = array_filter(
                $em->getRepository(Hotels::class)->findBy(['user' => $agent]),
                fn($h) => !$query || stripos($h->getName(), $query) !== false
            );

            $tickets = array_filter(
                $em->getRepository(Tickets::class)->findBy(['user' => $agent]),
                fn($t) => !$query || stripos($t->getDepartureCity(), $query) !== false || stripos($t->getArrivalCity(), $query) !== false
            );

            $travels = array_filter(
                $em->getRepository(OfferTravel::class)->findBy(['user' => $agent]),
                fn($t) => !$query || stripos($t->getDeparture(), $query) !== false || stripos($t->getDestination(), $query) !== false
            );

            $hotelCounts = [];
            foreach ($hotels as $hotel) {
                $hotelCounts[$hotel->getIdHotel()] = $feedbackRepository->countByOffer('hotel', $hotel->getIdHotel());
            }

            $ticketCounts = [];
            foreach ($tickets as $ticket) {
                $ticketCounts[$ticket->getIdTicket()] = $feedbackRepository->countByOffer('ticket', $ticket->getIdTicket());
            }

            $travelCounts = [];
            foreach ($travels as $travel) {
                $travelCounts[$travel->getId()] = $feedbackRepository->countByOffer('travel', $travel->getId());
            }

            $topOffersRaw = $feedbackRepository->getTopRatedOffersByAgent($agent->getId());
            $topOffers = [];

            foreach ($topOffersRaw as $entry) {
                $label = '';
                $entity = null;

                switch ($entry['type']) {
                    case 'hotel':
                        $entity = $em->getRepository(Hotels::class)->find($entry['offer_id']);
                        $label = $entity ? $entity->getName() : 'Hôtel inconnu';
                        break;
                    case 'ticket':
                        $entity = $em->getRepository(Tickets::class)->find($entry['offer_id']);
                        $label = $entity ? $entity->getDepartureCity() . ' ➔ ' . $entity->getArrivalCity() : 'Ticket inconnu';
                        break;
                    case 'travel':
                        $entity = $em->getRepository(OfferTravel::class)->find($entry['offer_id']);
                        $label = $entity ? $entity->getDeparture() . ' ➔ ' . $entity->getDestination() : 'Voyage inconnu';
                        break;
                }

                $topOffers[] = [
                    'type' => ucfirst($entry['type']),
                    'label' => $label,
                    'avg' => round($entry['avg_rating'], 1)
                ];
            }

            return $this->render('feedback/agent/index.html.twig', [
                'hotels' => $hotels,
                'tickets' => $tickets,
                'travels' => $travels,
                'hotelCounts' => $hotelCounts,
                'ticketCounts' => $ticketCounts,
                'travelCounts' => $travelCounts,
                'topOffers' => $topOffers,
            ]);
        }

        if ($role === 'client') {
            $user = $em->getRepository(User::class)->findOneBy(['role' => 'Client']);
            if (!$user) throw $this->createNotFoundException("Client non trouvé.");
            $feedbacks = $feedbackRepository->findByUserAndSearch($user, $query, $sort, $dir);
        } else {
            $feedbacks = $feedbackRepository->findBySearchSortAndType($query, $sort, $dir, $type)->getResult();
        }

        $template = match ($role) {
            'admin' => 'feedback/admin/index.html.twig',
            default => 'feedback/client/index.html.twig',
        };

        return $this->render($template, [
            'feedbacks' => $feedbacks,
        ]);
    }

    #[Route('/by-offer/{type}/{id}', name: 'feedback_by_offer', methods: ['GET'])]
    public function feedbacksByOffer(string $type, int $id, FeedbackRepository $feedbackRepository, Request $request): Response
    {
        $sort = $request->query->get('sort', 'date');
        $dir = $request->query->get('dir', 'DESC');
        $search = $request->query->get('q');

        $feedbacks = $feedbackRepository->findFeedbacksByOfferFiltered($type, $id, $search, $sort, $dir);

        return $this->render('feedback/agent/feedbacks_by_offer.html.twig', [
            'feedbacks' => $feedbacks,
            'offerType' => $type,
            'offerId' => $id,
            'sort' => $sort,
            'dir' => $dir,
            'q' => $search
        ]);
    }

    #[Route('/new/{type}/{id}', name: 'feedback_new', methods: ['GET', 'POST'])]
    public function new(string $type, int $id, Request $request, EntityManagerInterface $em): Response
    {
        $feedback = new Feedback();
        $user = $em->getRepository(User::class)->findOneBy(['role' => 'Client']);
        if (!$user) throw $this->createNotFoundException("Client non trouvé.");
        $feedback->setUserid($user);

        switch ($type) {
            case 'hotel':
                $entity = $em->getRepository(Hotels::class)->find($id);
                if (!$entity) throw $this->createNotFoundException("Hôtel introuvable.");
                $feedback->setHotel($entity);
                break;
            case 'ticket':
                $entity = $em->getRepository(Tickets::class)->find($id);
                if (!$entity) throw $this->createNotFoundException("Ticket introuvable.");
                $feedback->setTicket($entity);
                break;
            case 'travel':
                $entity = $em->getRepository(OfferTravel::class)->find($id);
                if (!$entity) throw $this->createNotFoundException("Voyage introuvable.");
                $feedback->setTravel($entity);
                break;
            default:
                throw $this->createNotFoundException("Type d'offre invalide.");
        }

        $form = $this->createForm(FeedbackType::class, $feedback);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($feedback);
            $em->flush();
            $this->addFlash('success', '✅ Feedback ajouté avec succès.');
            return $this->redirectToRoute('feedback_new', [
                'type' => $type,
                'id' => $id
            ]);
        }

        return $this->render('feedback/client/new.html.twig', [
            'form' => $form->createView(),
            'feedback' => $feedback,
        ]);
    }

    #[Route('/show/{id}', name: 'feedback_show', methods: ['GET'])]
    public function show(int $id, FeedbackRepository $feedbackRepository): Response
    {
        $feedback = $feedbackRepository->find($id);
        if (!$feedback) throw $this->createNotFoundException("Feedback introuvable.");

        $template = match ($this->getFakeRole()) {
            'admin' => 'feedback/admin/show.html.twig',
            'agent' => 'feedback/agent/show.html.twig',
            default => 'feedback/client/show.html.twig',
        };

        return $this->render($template, [
            'feedback' => $feedback,
        ]);
    }

    #[Route('/{id}/edit', name: 'feedback_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id, FeedbackRepository $feedbackRepository, EntityManagerInterface $em): Response
    {
        $feedback = $feedbackRepository->find($id);
        if (!$feedback) throw $this->createNotFoundException("Feedback introuvable.");

        $form = $this->createForm(FeedbackType::class, $feedback);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', '✅ Feedback modifié avec succès.');
            return $this->redirectToRoute('feedback_index');
        }

        $template = match ($this->getFakeRole()) {
            'admin' => 'feedback/admin/edit.html.twig',
            'agent' => 'feedback/agent/edit.html.twig',
            default => 'feedback/client/edit.html.twig',
        };

        return $this->render($template, [
            'form' => $form->createView(),
            'feedback' => $feedback,
        ]);
    }

    #[Route('/{id}', name: 'feedback_delete', methods: ['POST'])]
    public function delete(Request $request, int $id, FeedbackRepository $feedbackRepository, EntityManagerInterface $em): Response
    {
        $feedback = $feedbackRepository->find($id);
        if (!$feedback) throw $this->createNotFoundException('Feedback introuvable.');

        if ($this->isCsrfTokenValid('delete' . $feedback->getId(), $request->request->get('_token'))) {
            $em->remove($feedback);
            $em->flush();
            $this->addFlash('success', '🖑️ Feedback supprimé avec succès.');
        } else {
            $this->addFlash('error', '❌ Échec de la suppression du feedback.');
        }

        return $this->redirectToRoute('feedback_index');
    }

    #[Route('/admin/statistics/evolution', name: 'feedback_evolution', methods: ['GET'])]
public function evolution(FeedbackRepository $feedbackRepository): Response
{
    $data = $feedbackRepository->getFeedbackCountByDate();

    return $this->render('feedback/admin/evolution.html.twig', [
        'data' => $data,
    ]);
}

#[Route('/admin/statistics/comparison', name: 'feedback_month_comparison', methods: ['GET'])]
public function monthlyComparison(FeedbackRepository $feedbackRepository): Response
{
    $data = $feedbackRepository->getMonthlyComparison();

    // Parser les deux mois (si dispo)
    $current = $data[0] ?? null;
    $previous = $data[1] ?? null;

    return $this->render('feedback/admin/comparison.html.twig', [
        'current' => $current,
        'previous' => $previous,
    ]);
}

#[Route('/admin/statistics/negative', name: 'feedback_negative', methods: ['GET'])]
public function negativeFeedbacks(FeedbackRepository $feedbackRepository): Response
{
    $feedbacks = $feedbackRepository->findNegativeFeedbacks();

    return $this->render('feedback/admin/negative.html.twig', [
        'feedbacks' => $feedbacks,
    ]);
}

}