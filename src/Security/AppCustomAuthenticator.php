<?php

namespace App\Security;

use App\Entity\Log;  // Assurez-vous d'importer l'entité Log
use App\Repository\LogRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;
use GuzzleHttp\Client;
use Doctrine\ORM\EntityManagerInterface;

class AppCustomAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    private $entityManager;
    private $logRepository;
    private $translator;

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly RequestStack $requestStack,
        EntityManagerInterface $entityManager,  // Ajout de l'EntityManager
        TranslatorInterface $translator  // Ajout du Translator pour gérer les messages traduits
    ) {
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }

    private function getSession()
    {
        return $this->requestStack->getSession();
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('email');
        $this->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $email);

        // Enregistrement de la tentative de connexion
        $this->logAction('Tentative de connexion', $email, 'Tentative de connexion');

        // Vérification de reCAPTCHA après plusieurs tentatives infructueuses
        $attempts = $this->getSession()->get('login_attempts', 0);
        if ($attempts > 3) {
            $recaptchaResponse = $request->request->get('g-recaptcha-response');
            $secretKey = $_ENV['GOOGLE_RECAPTCHA_SECRET_KEY'];

            // Envoi de la requête de validation du reCAPTCHA
            $recaptchaValidation = $this->validateRecaptcha($recaptchaResponse, $secretKey);

            if (!$recaptchaValidation) {
                $this->logAction('Échec de reCAPTCHA', $email, 'Validation de reCAPTCHA échouée');
                throw new \Exception('reCAPTCHA validation failed.');
            }
        }

        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($request->request->get('password')),
            [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
                new RememberMeBadge(),
            ]
        );
    }

    private function validateRecaptcha($recaptchaResponse, $secretKey): bool
    {
        $client = new Client();
        $response = $client->post('https://www.google.com/recaptcha/api/siteverify', [
            'form_params' => [
                'secret' => $secretKey,
                'response' => $recaptchaResponse,
            ],
        ]);

        $body = json_decode($response->getBody()->getContents());

        return $body->success;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // Log de la connexion réussie
        $this->logAction('Connexion réussie', $token->getUserIdentifier(), 'Connexion réussie');

        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->urlGenerator->generate('app_accueil'));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }

    // Méthode pour ajouter un log dans la base de données
    private function logAction(string $action, string $username, ?string $details = null): void
    {
        $log = new Log();
        $log->setAction($action);
        $log->setUsername($username);
        $log->setTimestamp(new \DateTime());
        $log->setDetails($details);
        
        // Enregistrement dans la base de données
        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }
}
