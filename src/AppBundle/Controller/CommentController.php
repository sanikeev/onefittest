<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Comment;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * Class CommentController
 * @package AppBundle\Controller
 * @Route("/comment")
 */
class CommentController extends Controller
{

    /**
     * @Route("/", name="comments_list")
     * @Method("GET")
     */
    public function getAllAction()
    {
        return $this->render('AppBundle:User:comment.html.twig');
    }

    /**
     * @param Comment $comment
     * @Route("/{id}", name="comment")
     * @Method("GET")
     */
    public function getAction(Comment $comment)
    {

    }

    /**
     * @Route("/", name="comment_add")
     * @Method("POST")
     */
    public function postAction()
    {

    }

    /**
     * @Route("/{id}", name="comment_edit")
     * @Method("PUT")
     */
    public function putAction(Comment $comment){

    }
}
