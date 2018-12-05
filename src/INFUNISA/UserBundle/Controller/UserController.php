<?php

namespace INFUNISA\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request; //Este objeto Request nos va a servir para ir recibiendo la petición desde nuestro formulario para guardarlo a parir de nuestra entidad en BD. Esto para comenzar a codificar la función createAction(Request $request)
use Symfony\Component\HttpFoundation\Response;  //este objeto Response también lo usa el botón Eliminar con AJAX
use Symfony\Component\Validator\Constraints as Assert;  //este para evitar crear un usuario con password vacío
use Symfony\Component\Form\FormError;   //este para evitar crear un usuario con password vacío
use INFUNISA\UserBundle\Entity\User;
use INFUNISA\UserBundle\Form\UserType;

class UserController extends Controller {
    
    //definimos nuestra acción para el módulo de seguridad de autenticación de usuarios
    public function homeAction()
    {
        return $this->render('INFUNISAUserBundle:User:home.html.twig');
    }

    //public function indexAction() ----- Esto era antes de utilizar dql
    public function indexAction(Request $request) {
        
        
        $searchQuery = $request->get('query');
        
//        esto solo es para mostrar la recuperación del campo de nuestro controlador
//        print_r($searchQuery);
//        exit();
        
        if(!empty($searchQuery))
        {
            //estas 2 líneas la obtuve de: https://github.com/FriendsOfSymfony/FOSElasticaBundle/blob/master/doc/usage.md
            $finder = $this->container->get('fos_elastica.finder.app.user');            
            $users = $finder->createPaginatorAdapter($searchQuery);
        }
        else
        {            
               
            $em = $this->getDoctrine()->getManager();
            $dql = "SELECT u FROM INFUNISAUserBundle:User u ORDER BY u.id DESC";
            $users = $em->createQuery($dql);
        
        }

        //$users = $em->getRepository('INFUNISAUserBundle:User')->findAll(); ----- Esto era antes de usar dql

        /*
          $res = 'Lista de usuarios: <br />';

          foreach($users  as $user)
          {
          $res .= 'Usuario: ' . $user->getUsername() . ' - Email: ' . $user->getEmail() . '<br />';
          }

          return new Response($res);
         */



        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
                $users, $request->query->getInt('page', 1), //esto va hacer el número de página en el cual se va a situar nuestra paginación, que inicialmente va a ser en la página 1 (esto es configurable)
                10 // este tercer parámetro es el límite por página, es decir cuántos registros nos va a mostrar por cada página de nuestra vista
        );

        // return $this->render('INFUNISAUserBundle:User:index.html.twig', array('users' => $users)); --Esto era antes de usar dql

        $deleteFormAjax = $this->createCustomForm(' :USER_ID', 'DELETE', 'infunisa_user_delete');   //este método con el fin de hacerlo reutilizable entonces le definimos parámetros, el primer parámetro va a corresponder al id del usuario pero vamos a usar un comdín llamado :USER_ID, seguido de esto el método llamado DELETE, y seguido de esto enviaremos la ruta a la cual va a redirigir el formulario, la ruta es infunisa_user_delete
        //return $this->render('INFUNISAUserBundle:User:index.html.twig', array('pagination' => $pagination));  ASÍ ESTABA ANTES DE USAR AJAX PARA EL BOTÓN ELIMINAR DE LA VISTA INDEX

        return $this->render('INFUNISAUserBundle:User:index.html.twig', array('pagination' => $pagination, 'delete_form_ajax' => $deleteFormAjax->createView()));
    
    }
    public function addAction() {
        $user = new User(); //$user es el objeto, y User() es la insancia del objeto $user
        $form = $this->createCreateForm($user); //createCreateForm es un método que estamos llamando para la variable $form

        return $this->render('INFUNISAUserBundle:User:add.html.twig', array('form' => $form->createView())); //Bundle = INFUNISAUserBundle, Directorio = User y la acción es add.html.twig //que es la vista //el método createView() va a contener todo nuestro formulario
    }

    private function createCreateForm(User $entity) {
        //esto era para Symfony2.7.3
        //$form = $this->createForm(new UserType(), $entity, array(            
        //pero para Symfony3.4.15 ahora es:
        $form = $this->createForm(UserType::class, $entity, array(
            'action' => $this->generateUrl('infunisa_user_create'),
            'method' => 'POST'
        ));

        return $form;
    }

    public function createAction(Request $request) {
        $user = new User();
        $form = $this->createCreateForm($user);
        $form->handleRequest($request); //la variable form va a contener lo que está después de ->

        if ($form->isValid()) {
            $password = $form->get('password')->getData();

            //esto para impedir que se cree un usuario con password vacío
            $passwordConstraint = new Assert\NotBlank();
            $errorList = $this->get('validator')->validate($password, $passwordConstraint);     //el primer parámetro es el password y el segundo parámetro es nuestra regla de validación $passwordConstraint

            if (count($errorList) == 0) {
                $encoder = $this->container->get('security.password_encoder');
                $encoded = $encoder->encodePassword($user, $password);

                $user->setPassword($encoded);

                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush(); //método flush para realizar la persistencia de datos, guardando en la BD            

                $successMessage = $this->get('translator')->trans('The user has been created.'); //método get llamando al servicio translator y seguido de esto al método trans
                $this->addFlash('mensaje', $successMessage);
                //$this->addFlash('mensaje', 'The user has been created.'); //así fue escrito inicialmente sin agregar la variable $succesMessage

                return $this->redirectToRoute('infunisa_user_index');
            } else {
                $errorMessage = new FormError($errorList[0]->getMessage());
                $form->get('password')->addError($errorMessage);
            }
        }

        return $this->render('INFUNISAUserBundle:User:add.html.twig', array('form' => $form->createView())); //Bundle = INFUNISAUserBundle, Directorio = User y la acción es add.html.twig //que es la vista //el método createView() va a contener todo nuestro formulario
    }

    public function editAction($id) {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('INFUNISAUserBundle:User')->find($id);

        if (!$user) {
            $messageException = $this->get('translator')->trans('User not found.');
            throw $this->createNotFoundException('$messageException');     //si el registro no ha sido encontrado nos va a enviar a una página 404 con la excepción User not found         
        }

        $form = $this->createEditForm($user);

        return $this->render('INFUNISAUserBundle:User:edit.html.twig', array('user' => $user, 'form' => $form->createView()));
    }

    private function createEditForm(User $entity) {
//                $form = $this->createForm(UserType::class, $entity, array('action' => $this->generateUrl('infunisa_user_update', array('id' => $entity->getId())), 'method' => 'PUT'));       //esta línea era para Symfony 2.7.3, pero ahora en Symfony 3.4.15 es:
        $form = $this->createForm(UserType::class, $entity, array('action' => $this->generateUrl('infunisa_user_update', array('id' => $entity->getId())), 'method' => 'PUT'));

        return $form;
    }

    public function updateAction($id, Request $request) {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('INFUNISAUserBundle:User')->find($id);

        if (!$user) {
            $messageException = $this->get('translator')->trans('User not found.');
            throw $this->createNotFoundException('$messageException');     //si el registro no ha sido encontrado nos va a enviar a una página 404 con la excepción User not found         
        }

        $form = $this->createEditForm($user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {        //esto para comprobar si se ha guardado correcto el formulario
            $password = $form->get('password')->getData();  //esto para recuperar el password y evitar que se guarde el capo vacío dentro de la BD
            //estas 2 siguientes líneas es solo para mostrar que se está recuperando el password
//            print_r($password);
//            exit();

            if (!empty($password)) {
                $encoder = $this->container->get('security.password_encoder');
                $encoded = $encoder->encodePassword($user, $password);
                $user->setPassword($encoded);
            } else {
                $recoverPass = $this->recoverPass($id);
                $user->setPassword($recoverPass[0]['password']);    //esto porque dentro de un array está recuperando el password. Esto se puede verificar ejecutando las línas siguiente de print_r($recoverPass); exit();
                //estas 2 siguientes líneas es solo para mostrar que se está recuperando el recoverPass
//            print_r($recoverPass);
//            exit();
            }

            if ($form->get('role')->getData() == 'ROLE_ADMIN') {
                $user->setIsActive(1);
            }

            $em->flush();

            $successMessage = $this->get('translator')->trans('The user has been modified.');
            $this->addFlash('mensaje', $successMessage);
            return $this->redirectToRoute('infunisa_user_edit', array('id' => $user->getId()));
        }

        return $this->render('INFUNISAUserBundle:User:edit.html.twig', array('user' => $user, 'form' => $form->createView()));
    }

    private function recoverPass($id) {
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
                        'SELECT u.password
                FROM INFUNISAUserBundle:User u
                WHERE u.id= :id'
                )->setParameter('id', $id);

        $currentPass = $query->getResult();

        return $currentPass;
    }

    public function viewAction($id) {
        $repository = $this->getDoctrine()->getRepository('INFUNISAUserBundle:User');

        $user = $repository->find($id);

        // $user = $repository->findOneById($id);
        // $user = $repository->findOneByUsername($nombre);

        if (!$user) {
            $messageException = $this->get('translator')->trans('User not found.');
            throw $this->createNotFoundException('$messageException');     //si el registro no ha sido encontrado nos va a enviar a una página 404 con la excepción User not found         
        }

        $deleteForm = $this->createCustomForm($user->getId(), 'DELETE', 'infunisa_user_delete');

        //esto fue inicialmente
        //return new Response('Usuario: ' . $user->getUsername() . ' con email: ' . $user->getEmail());

        return $this->render('INFUNISAUserBundle:User:view.html.twig', array('user' => $user, 'delete_form' => $deleteForm->createView()));
    }
    
//este método createDeleteForm era de usar AJAX en el botón Eliminar de la vista indez

//    private function createDeleteForm($user) {
//        return $this->createFormBuilder()   //con este método vamos a poder ir creando nuestro formulario
//                        ->setAction($this->generateURl('infunisa_user_delete', array('id' => $user->getId())))
//                        ->setMethod('DELETE')
//                        ->getForm();
//    }

    public function deleteAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('INFUNISAUserBundle:User')->find($id);

        if (!$user) {
            $messageException = $this->get('translator')->trans('User not found.');
            throw $this->createNotFoundException('$messageException');     //si el registro no ha sido encontrado nos va a enviar a una página 404 con la excepción User not found         
        }
        
        $allUsers = $em->getRepository('INFUNISAUserBundle:User')->findAll();
        $countUsers = count($allUsers); //métdo count de php

        //$form = $this->createDeleteForm($user);   ESTO ERA ANTES DE USAR AJAX PARA EL BOTÓN ELIMINAR DE LA VISTA INDEX

        $form = $this->createCustomForm($user->getId(), 'DELETE', 'infunisa_user_delete');

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            if($request->isXMLHttpRequest())  //esta action deleteAction tiene que servir tanto para las peticiones con AJAX como cuando realizamos nuestras peticiones desde la vista view, entonces para trabajar con ajax debemos a partir del objeto request llamar al método isXMLHttpRequest(), lo que se escriba aquí adentro ya estamos trabajando con AJAX
            {
                $res = $this->deleteUser($user->getRole(), $em, $user); //vamos a crear este método deleteUser() con el fin de reutilizarlo cuando nosotros hagamos la eliminación del usuario fuera de una petición AJAX, pero también tiene que servir esta acción cuando eliminemos desde nuestra vist view, mediante una petición POST, DELETE normal                       
                
                return new Response
                (
                        
                json_encode(array('removed' => $res['removed'], 'message' => $res['message'], 'countUsers' => $countUsers)),
                        200,    //estado de nuestra respuesta
                        array('Content-Type' => 'application/json') //este es el tipo de encabezado
                        );
            }
            
//ESTAS 3 LÍNEAS ERAN ANTES DE USAR AJAX PARA EL BOTÓN ELIMINAR DE LA VISTA INDEX
//            $em->remove($user);
//            $em->flush();//
//            $successMessage = $this->get('translator')->trans('The user has been deleted.');
            
            $res = $this->deleteUser($user->getRole(), $em, $user);
            
//            $this->addFlash('mensaje', $successMessage);  ESTO ERA ANTES DE USAR AJAX PARA EL BOTÓN ELIMINAR DE LA VISTA INDEX
            $this->addFlash($res['alert'], $res['message']);
            return $this->redirectToRoute('infunisa_user_index');
        }
    }
    
    private function deleteUser($role, $em, $user)
    {
        if($role == 'ROLE_USER')
        {
            $em->remove($user);
            $em->flush();
            
            $message = $this->get('translator')->trans('The user has been deleted.');
            $removed = 1;    //para indicar que el usuario sí ha sido eliminado
            $alert = 'mensaje';
        }
        elseif($role == 'ROLE_ADMIN')
        {
            $message = $this->get('translator')->trans('The user could not be deleted.');
            $removed = 0;
            $alert = 'error';
        }
        
        return array('removed' => $removed, 'message' => $message, 'alert' => $alert);
    }

    private function createCustomForm($id, $method, $route) {

        return $this->createFormBuilder()
                        ->setAction($this->generateUrl($route, array('id' => $id)))
                        ->setMethod($method)
                        ->getForm();
    }

}
