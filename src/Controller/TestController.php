<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use EWZ\Bundle\RecaptchaBundle\Form\Type\EWZRecaptchaType;
use EWZ\Bundle\RecaptchaBundle\Validator\Constraints\IsTrue;

class TestController extends AbstractController
{
    #[Route('/test-recaptcha', name: 'test_recaptcha')]
    public function testRecaptcha(Request $request): Response
    {
        $form = $this->createFormBuilder()
            ->add('recaptcha', EWZRecaptchaType::class, [
                'label' => false,
                'mapped' => false,
                'constraints' => [new IsTrue()],
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return new Response('reCAPTCHA validated successfully!');
        }

        return $this->render('test/recaptcha.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}