<?php
/**
 * Created by PhpStorm.
 * User: sergey
 * Date: 13.12.17
 * Time: 23:55
 */

namespace AppBundle\Security;

use Facebook\Facebook;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class ApiKeyAuthenticator extends AbstractGuardAuthenticator
{
    protected $fb;

    public function __construct(Facebook $facebook)
    {
        $this->fb = $facebook;
    }

    /**
     * Called on every request. Return whatever credentials you want to
     * be passed to getUser(). Returning null will cause this authenticator
     * to be skipped.
     */
    public function getCredentials(Request $request)
    {
        $url = $request->getPathInfo();
        if ($url != '/login') {
            return;
        }

        $helper = $this->fb->getRedirectLoginHelper();
        $accessToken = $helper->getAccessToken()->getValue();
        $user = $this->fb->get('/me?fields=id,name,email', $accessToken)->getGraphUser();
        $token = $user->getId();
        // What you return here will be passed to getUser() as $credentials
        return array(
            'facebookId' => $token,
            'access_token' => $accessToken
        );
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $apiKey = $credentials['facebookId'];
        $accessToken = $credentials['access_token'];

        if (null === $apiKey) {
            return;
        }

        // if a User object, checkCredentials() is called
        foreach ($userProvider->getProviders() as $item) {
            if ($item instanceof ApiKeyUserProvider) {
                $userProvider = $item;
            }
        }
        $su = $userProvider->getUsernameForApiKey($apiKey, $accessToken);
        return $su;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        // check credentials - e.g. make sure the password is valid
        // no credential check is needed in this case

        // return true to cause authentication success
        return true;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // on success, let the request continue
        return new RedirectResponse('/dashboard');
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $data = array(
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())

            // or to translate this message
            // $this->translator->trans($exception->getMessageKey(), $exception->getMessageData())
        );

        return new JsonResponse($data, Response::HTTP_FORBIDDEN);
    }

    /**
     * Called when authentication is needed, but it's not sent
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $data = array(
            // you might translate this message
            'message' => 'Authentication Required'
        );

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    public function supportsRememberMe()
    {
        return false;
    }
}
