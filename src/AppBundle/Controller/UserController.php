<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;

class UserController extends Controller
{
    /**
     * @Route("/user/", name="user_profile")
     */
    public function indexAction(Request $request)
    {
        if(!$this->isGranted('ROLE_USER')){
            throw $this->createAccessDeniedException();
        }
        $user = $this->getUser();
        $form = $this->createFormBuilder()
            ->add('name', TextType::class,[
                'label' => 'Имя',
                'data' => $user->getName()
            ])
            ->add('lastname', TextType::class,[
                'label' => 'Фамилия',
                'data' => $user->getLastname()
            ])->getForm()
            ;
        $form->handleRequest($request);
        if ($form->isValid()){
            $data = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $user->setName($data['name']);
            $user->setLastname($data['lastname']);
            $em->persist($user);
            $em->flush();
            return $this->redirectToRoute('user_profile');
        }
        return $this->render('AppBundle:User:index.html.twig', array(
            'form' => $form->createView()
        ));
    }

}
