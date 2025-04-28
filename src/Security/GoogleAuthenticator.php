<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use League\OAuth2\Client\Provider\GoogleUser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class GoogleAuthenticator extends AbstractAuthenticator
{
    private EntityManagerInterface $em;
    private RouterInterface $router;
    private ClientRegistry $clientRegistry;
    private UserPasswordHasherInterface $passwordHasher;
    private const DEFAULT_PASSWORD = 'record25';
    private const DEFAULT_ROLE = 'ROLE_CLIENT';

    public function __construct(
        ClientRegistry $clientRegistry,
        EntityManagerInterface $em,
        RouterInterface $router,
        UserPasswordHasherInterface $passwordHasher
    ) {
        $this->em = $em;
        $this->clientRegistry = $clientRegistry;
        $this->router = $router;
        $this->passwordHasher = $passwordHasher;
    }

    public function supports(Request $request): bool
    {
        return $request->attributes->get('_route') === 'connect_google_check';
    }

    public function authenticate(Request $request): SelfValidatingPassport
    {
        $client = $this->clientRegistry->getClient('google');
        $accessToken = $client->getAccessToken();

        /** @var GoogleUser $googleUser */
        $googleUser = $client->fetchUserFromToken($accessToken);
        $email = $googleUser->getEmail();
        $fullName = $googleUser->getName();

        return new SelfValidatingPassport(
            new UserBadge($email, function () use ($email, $fullName): UserInterface {
                $user = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);

                if (!$user) {
                    $user = new User();
                    $user->setEmail($email);

                    // Hash the default password
                    $hashedPassword = $this->passwordHasher->hashPassword($user, 'record25');
                    $user->setPassword($hashedPassword);

                    // Name & Surname
                    $names = explode(' ', $fullName, 2);
                    $user->setName($names[0] ?? 'Google');
                    $user->setSurname($names[1] ?? 'User');

                    $user->setPhone('CHANGE ME');
                    $user->setAddresse('CHANGE ME');
                    $user->setProfilePhoto(value: 'CHANGE ME');
                    $user->setRole('Client');

                    $this->em->persist($user);
                    $this->em->flush();
                }

                return $user;
            })
        );
    }


    public function onAuthenticationSuccess(Request $request, $token, string $firewallName): RedirectResponse
    {
    
        return new RedirectResponse($this->router->generate('app_home'));
    }
    public function onAuthenticationFailure(Request $request, \Throwable $exception): RedirectResponse
    {
        return new RedirectResponse($this->router->generate('app_login'));
    }
}
