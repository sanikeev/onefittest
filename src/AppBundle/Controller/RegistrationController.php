<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Form\UserType;
use Facebook\Facebook;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegistrationController extends Controller
{
    /**
     * @Route("/register", name="app_register")
     */
    public function registerAction(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {

        /** @var Facebook $fb */
        $fb = $this->get('facebook');
        $helper = $fb->getRedirectLoginHelper();
        $serverName = $request->server->get('SERVER_NAME');
        $redirectUrl = 'http://' . $serverName . '/register/facebook';
        $scope = ['email'];

        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('login');
        }
        return $this->render('AppBundle:Registration:register.html.twig', array(
            'form' => $form->createView(),
            'url' => $helper->getLoginUrl($redirectUrl, $scope),
        ));
    }

    /**
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/register/facebook", name="app_registration_register_facebook")
     */
    public function registerFacebookAction(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        /** @var Facebook $fb */
        $fb = $this->get('facebook');
        $helper = $fb->getRedirectLoginHelper();
        $serverName = $request->server->get('SERVER_NAME');
        $redirectUrl = 'http://' . $serverName . '/register/facebook';
        $scope = ['email'];

        $accessToken = $helper->getAccessToken($redirectUrl);
        if ($accessToken) {
            $data = $fb->get('/me?fields=id,name,email', $accessToken->getValue())->getGraphUser();
            $user = new User();
            $user->setEmail($data->getEmail());
            $user->setFacebookAccessToken($accessToken->getValue());
            $user->setFacebookId($data->getId());
            $user->setUsername($data->getName());
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            return $this->redirectToRoute('dashboard');
        }

        return $this->render('AppBundle:Registration:registerFacebook.html.twig', [
            'url' => $helper->getLoginUrl($redirectUrl, $scope),
        ]);
    }

}
