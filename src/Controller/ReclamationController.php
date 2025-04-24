<?php

namespace App\Controller;

use App\Entity\Reclamation;
use App\Entity\User;
use App\Form\ReclamationType;
use App\Repository\ReclamationRepository;
use App\Service\SmsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;





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


    #[Route('/contact', name: 'reclamation_contact', methods: ['GET', 'POST'])]
public function contact(Request $request, EntityManagerInterface $em): Response
{
    $reclamation = new Reclamation();

    /** @var User $user */
    $user = $this->getUser();
    $role = $this->getUserRole($user);

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
                return $this->redirectToRoute('reclamation_contact');
            }
        }

        $reclamation->setUser($user);
        $em->persist($reclamation);
        $em->flush();

        $this->addFlash('success', '✅ Réclamation envoyée avec succès.');
        return $this->redirectToRoute('reclamation_contact');
    }

    return $this->render('home/contact.html.twig', [
        'form' => $form->createView(),
    ]);
}


    #[Route('/new', name: 'reclamation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $reclamation = new Reclamation();
        $user = $this->getUser();
        $role = $this->getUserRole($user);
        $reclamation->setDate(new \DateTime());
    
        if ($role === 'agent') {
            $reclamation->setStatus('En attente');
        }
    
        // 💥 Ici on ignore le form handleRequest, on prend brut
        if ($request->isMethod('POST')) {
            $data = $request->request->all();
    
            // 🔥 Accès direct au champ texte
            $issueText = strtolower(trim($data['reclamation']['issue'] ?? ''));
            $issueText = preg_replace('/[^\p{L}\p{N}\s]/u', '', $issueText);
    
            $forbiddenWords = ['fuck', 'pute', 'merde', 'con', 'arnaque', 'spam'];
    
            foreach ($forbiddenWords as $word) {
                if (preg_match('/\b' . preg_quote($word, '/') . '\b/i', $issueText)) {
                    $this->addFlash('danger', '❌ Message bloqué : mot interdit détecté.');
                    return $this->redirectToRoute('reclamation_new');
                }
            }
    
            // Si tout est bon, traitement normal avec formulaire
            $form = $this->createForm(ReclamationType::class, $reclamation, [
                'is_edit' => false
            ]);
            $form->handleRequest($request);
    
            if ($form->isSubmitted() && $form->isValid()) {
                $reclamation->setUser($user);
                $em->persist($reclamation);
                $em->flush();
    
                $this->addFlash('success', '✅ Réclamation enregistrée sans mot interdit.');
                return $this->redirectToRoute('reclamation_index');
            }
        }
    
        $form = $this->createForm(ReclamationType::class, $reclamation, [
            'is_edit' => false
        ]);
    
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
    public function edit(Request $request, Reclamation $reclamation, EntityManagerInterface $em, SmsService $smsService): Response
    {
        $form = $this->createForm(ReclamationType::class, $reclamation, [
            'is_edit' => true,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $user = $reclamation->getUser();
            if ($user && method_exists($user, 'getPhone')) {
                $smsService->send($user->getPhone(), "📢 Votre réclamation #{$reclamation->getId()} a été mise à jour. Nouveau statut : {$reclamation->getStatus()}");
            }

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

    #[Route('/{id}/send-sms', name: 'reclamation_send_sms', methods: ['GET'])]
    public function sendSmsManual(int $id, ReclamationRepository $repo, SmsService $smsService): Response
    {
        $reclamation = $repo->find($id);
        if (!$reclamation) {
            throw $this->createNotFoundException('Réclamation introuvable');
        }

        $user = $reclamation->getUser();
        if ($user && method_exists($user, 'getPhone')) {
            $smsService->send($user->getPhone(), "📢 Mise à jour manuelle : votre réclamation #{$reclamation->getId()} est en statut : {$reclamation->getStatus()}");
            $this->addFlash('success', '📲 SMS envoyé avec succès !');
        } else {
            $this->addFlash('warning', '⚠️ Aucun numéro de téléphone associé à cet utilisateur.');
        }

        return $this->redirectToRoute('reclamation_edit', ['id' => $id]);
    }

    private function getUserRole(User $user): string
    {
        return in_array('ROLE_ADMIN', $user->getRoles(), true) ? 'admin' : 'agent';
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
            ->from('oussema.msehli@esprit.tn')
            ->to($reclamation->getUser()?->getEmail() ?? 'oussema.msehli@esprit.tn')
            ->subject('📬 Suivi de votre réclamation')
            ->html("<p>Bonjour,</p><p>Un administrateur vient de vous envoyer une notification liée à votre réclamation : <strong>{$reclamation->getIssue()}</strong>.</p><p><strong>Statut actuel :</strong> {$reclamation->getStatus()}</p><hr><p>L’équipe EasyTrip</p>");

        $mailer->send($email);

        $this->addFlash('success', '✉️ Email envoyé avec succès !');
        return $this->redirectToRoute('reclamation_edit', ['id' => $id]);
    }


    #[Route('/reclamation/admin/export/excel', name: 'reclamation_export_excel')]
public function exportExcel(ReclamationRepository $repo): Response
{
    $reclamations = $repo->findAll();

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Titres des colonnes
    $sheet->setCellValue('A1', 'ID');
    $sheet->setCellValue('B1', 'Catégorie');
    $sheet->setCellValue('C1', 'Problème');
    $sheet->setCellValue('D1', 'Statut');
    $sheet->setCellValue('E1', 'Date');
    $sheet->setCellValue('F1', 'Utilisateur');

    $row = 2;

    foreach ($reclamations as $rec) {
        $sheet->setCellValue('A' . $row, $rec->getId());
        $sheet->setCellValue('B' . $row, $rec->getCategory());
        $sheet->setCellValue('C' . $row, $rec->getIssue());
        $sheet->setCellValue('D' . $row, $rec->getStatus());
        $sheet->setCellValue('E' . $row, $rec->getDate()->format('Y-m-d'));
        $sheet->setCellValue('F' . $row, $rec->getUser()?->getEmail() ?? 'N/A');
        $row++;
    }

    $writer = new Xlsx($spreadsheet);
    $fileName = 'reclamations_export.xlsx';

    $response = new StreamedResponse(function () use ($writer) {
        $writer->save('php://output');
    });

    $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
    $response->headers->set('Cache-Control', 'max-age=0');

    return $response;
}

#[Route('/reclamation/stats', name: 'reclamation_stats')]
public function stats(ReclamationRepository $reclamationRepository): Response
{
    $counts = [
        'En attente' => $reclamationRepository->count(['status' => 'En attente']),
        'En cours' => $reclamationRepository->count(['status' => 'En cours']),
        'Fermée' => $reclamationRepository->count(['status' => 'Fermée']),
    ];

    return $this->render('reclamation/admin/stats.html.twig', [
        'counts' => $counts
    ]);
}


#[Route('/admin/reclamations/stats/pdf', name: 'reclamation_stats_pdf')]
public function statsPdf(ReclamationRepository $reclamationRepository): Response
{
    $counts = [
        'En attente' => $reclamationRepository->count(['status' => 'En attente']),
        'En cours' => $reclamationRepository->count(['status' => 'En cours']),
        'Fermée' => $reclamationRepository->count(['status' => 'Fermée']),
    ];

    $html = $this->renderView('reclamation/admin/stats_pdf.html.twig', [
        'counts' => $counts,
    ]);

    $dompdf = new \Dompdf\Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    return new Response($dompdf->output(), 200, [
    'Content-Type' => 'application/pdf',
    'Content-Disposition' => 'attachment; filename="stats_reclamations.pdf"'
]);

}

}
