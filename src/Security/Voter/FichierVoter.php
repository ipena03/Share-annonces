<?php

namespace App\Security\Voter;

use App\Entity\Fichier;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Security;


class FichierVoter extends Voter
{
    const FILE_DOWNLOAD = 'FILE_DOWNLOAD';
    const FILE_DELETE = 'FILE_DELETE';
    
    private $security;
    
    public function __construct(Security $security)
    {
        $this->security = $security;
    }
    
    protected function supports(string $attribute, mixed $subject): bool
    {
        // Vérifie si l'attribut est l'un des attributs que ce voter gère
        // et si le sujet est une instance de Fichier
        return in_array($attribute, [self::FILE_DOWNLOAD, self::FILE_DELETE])
            && $subject instanceof Fichier;
    }
    
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        
        // Si l'utilisateur n'est pas connecté, refuser l'accès
        if (!$user instanceof UserInterface) {
            return false;
        }
        
        // Si l'utilisateur est un admin, autoriser l'accès
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }
        
        /** @var Fichier $fichier */
        $fichier = $subject;
        
        // Vérifier si l'utilisateur est le propriétaire du fichier
        return $this->isOwner($fichier, $user);
    }
    
    private function isOwner(Fichier $fichier, UserInterface $user): bool
    {
        return $user instanceof User && $fichier->getUser() !== null && $fichier->getUser()->getId() === $user->getId();
    }
}