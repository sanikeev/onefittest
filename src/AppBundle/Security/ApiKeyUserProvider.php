<?php
/**
 * Created by PhpStorm.
 * User: sergey
 * Date: 13.12.17
 * Time: 23:56
 */

namespace AppBundle\Security;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

class ApiKeyUserProvider implements UserProviderInterface
{
    protected $em;

    public function __construct(ObjectManager $em)
    {
        $this->em = $em;
    }

    public function getUsernameForApiKey($apiKey, $accessToken)
    {
        // Look up the username based on the token in the database, via
        // an API call, or do something entirely different
        /** @var \AppBundle\Entity\User $username */
        $username = $this->em->getRepository(\AppBundle\Entity\User::class)->findOneByFacebookId($apiKey);
        if($username){
            $username->setFacebookAccessToken($accessToken);
            $this->em->persist($username);
            $this->em->flush();
        }
        return $username;
    }

    public function loadUserByUsername($username)
    {
        return new User(
            $username,
            null,
            // the roles for the user - you may choose to determine
            // these dynamically somehow based on the user
            array('ROLE_USER')
        );
    }

    public function refreshUser(UserInterface $user)
    {
        // this is used for storing authentication in the session
        // but in this example, the token is sent in each request,
        // so authentication can be stateless. Throwing this exception
        // is proper to make things stateless
        throw new UnsupportedUserException();
    }

    public function supportsClass($class)
    {
        return User::class === $class;
    }
}