<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use Facebook\Facebook;
use Facebook\GraphNodes\GraphNode;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        /** @var Facebook $fb */
        $fb = $this->get('facebook');
        $helper = $fb->getRedirectLoginHelper();
        $redirectUri = 'http://onefit.dev/login';
        $scope = ['email', 'user_friends'];
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'fbUrl' => $helper->getLoginUrl($redirectUri, $scope)
        ]);
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route("/dashboard",name="dashboard")
     */
    public function dashboardAction(Request $request)
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('homepage');
        }
//        /** @var Facebook $fb */
//        $fb = $this->get('facebook');
//        $friendsEdge = $fb->get('/me/friends', $user->getFacebookAccessToken())->getGraphEdge()->asArray();
//        dump($friendsEdge);die;
//        /** @var GraphNode $node */
//        foreach ($friendsEdge as $node) {
//            dump($node->asArray());
//        }
//        die;

        $em = $this->getDoctrine()->getManager();

        $userList = $em->getRepository(User::class)->findByNotEmail($user->getEmail());

        return $this->render('default/dashboard.html.twig', [
            'friends' => $userList,
        ]);
    }
}
