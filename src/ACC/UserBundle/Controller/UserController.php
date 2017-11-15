<?php

namespace ACC\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use ACC\UserBundle\Entity\User;
use ACC\UserBundle\Form\UserType;

class UserController extends Controller
{
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $users = $em->getRepository('ACCUserBundle:User')->findAll();

        /*

        $res = 'Lista de usuarios: <br />';

        foreach($users as $user)
        {
        	$res .= 'Usuario: ' . $user->getUsername() . ' - Email: ' . $user->getEmail() . '<br />';
        }

        return new Response($res);
        */

        return $this->render('ACCUserBundle:User:index.html.twig', array('users' => $users));
    }

    public function addAction()
    {   
        $user = new User();
        $form = $this->createCreateForm($user);

        return $this->render('ACCUserBundle:User:add.html.twig', array('form' => $form->createView()));
    }

    private function createCreateForm(User $entity)
    {
        $form = $this->createForm(UserType::class, $entity, array(
                'action' => $this->generateUrl('acc_user_create'),
                'method' => 'POST'
            ));
        return $form;
    }

    public function createAction(Request $request)
    {
        $user = new User();
        $form = $this->createCreateForm($user);
        $form->handleRequest($request);

        if($form->isValid())
        {
            $password = $form->get('password')->getData();

            $encoder = $this->container->get('security.password_encoder');
            $encoded = $encoder->encodePassword($user, $password);

            $user->setPassword($encoded);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('acc_user_index');
        }

        return $this->render('ACCUserBundle:User:add.html.twig', array('form' => $form->createView()));    
    }

    public function viewAction($id)
    {   
        $repository = $this->getDoctrine()->getRepository('ACCUserBundle:User');

        $user = $repository->find($id);
        // $user = $repository->findOneById($id);
        // $user = $repository->findOneByUsername($id);

        return new Response('Usuario: ' . $user->getUsername() . ' con Email: ' . $user->getEmail());
    }


}
