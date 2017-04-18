<?php

namespace AppBundle\Utils;

use AppBundle\Entity\User;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\EntityUserProvider;
use Symfony\Component\Security\Core\Role\Role;

class OAuthAwareUserProvider extends EntityUserProvider
{
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $resourceOwnerName = $response->getResourceOwner()->getName();

        if (!isset($this->properties[$resourceOwnerName])) {
            throw new \RuntimeException(sprintf("No property defined for entity for resource owner '%s'.", $resourceOwnerName));
        }

        $username = $response->getUsername();
        $user = $this->findUser(array($this->properties[$resourceOwnerName] => $username));

        if (null == $user) {

            $user = new User();
            $user->setUsername($response->getUsername());
            $user->setEmail($response->getEmail());
            $user->setRealName($response->getRealName());
            $user->setGoogleId($response->getUsername());
            $role = new Role('ROLE_USER');

            $this->em->persist($user);
            $this->em->flush();
        }

        return $user;
    }
}
