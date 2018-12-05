<?php

namespace INFUNISA\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

//orginalmente el generador del controlador desde la línea de comando, cuando creó este archivo, lo creó incluyendo esta línea pero en nuestro caso no lo vamos a necesitar en este controlador
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

//en Symfony2, además de las dos clases de arriba, también orginalmente agrega la siguiente línea, pero tampoco la vamos a necesitar en este controlador para nuestro caso:
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

//importamos las clases que forman parte del nécleo de Symfony, para trabajar más adelante al agregar nuestra y trabajar con AJAX
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use INFUNISA\UserBundle\Entity\Task;
use INFUNISA\UserBundle\Form\TaskType;


class TaskController extends Controller
{
    
    public function indexAction(Request $request)
    {
        //esto solo es temrporalmente para que muestre que este mensaje y veamos que sí está funcioando correctamente
//        exit('Lista de tareas');
        
        //usando DQL
        $em = $this->getDoctrine()->getManager();
        $dql = "SELECT t FROM INFUNISAUserBundle:Task t ORDER BY t.id DESC";
        $tasks = $em->createQuery($dql);        //para ejecutar nuestra consulta
                
                $paginator = $this->get('knp_paginator');
                $pagination = $paginator->paginate(
                        $tasks,
                        $request->query->getInt('page', 1),
                        3       //este es el número de registros que nos va a mostrar por página
                 );
        
        return $this->render('INFUNISAUserBundle:Task:index.html.twig', array('pagination' => $pagination));
    }
    
    //Esta acción es para los usuarios que quieran ver sus propias tareas a través del menú de "Mis Tareas"
    public function customAction(Request $request)
    {
        $idUser = $this->get('security.token_storage')->getToken()->getUser()->getId();     //servicio de seguridad usando el token para obtener el usuario correspondiente a su id
        
        $em = $this->getDoctrine()->getManager();
        $dql = "SELECT t FROM INFUNISAUserBundle:Task t JOIN t.user u WHERE u.id= :idUser ORDER BY t.id DESC";     //u.id = :idUser, los : hay que dejarlo pegado al nombre de la siguiente variable para que no genere error, así como está escrito en esta línea por ejemplo

        $tasks = $em->createQuery($dql)->setParameter('idUser', $idUser);
        
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
                $tasks,
                $request->query->getInt('page', 1),
                3                
         );     //siempre tener cuidado de la identación, ya que esta línea de cierre si no está correctamente identadada entonces genera un error
        
        //Formulario para procesar la tarea que tiene asignado un usuario
        
        $updateForm = $this->createCustomForm(' :TASK_ID', 'PUT', 'infunisa_task_process');     //El comodín :TASK_ID se reemplazará con JQuery dentro de nuestro formulario en nuestra vista
        
        return $this->render('INFUNISAUserBundle:Task:custom.html.twig', array ('pagination' => $pagination, 'update_form' => $updateForm->createView()));
    }
    
    //procesar tareas
    public function processAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        
        $task = $em->getRepository('INFUNISAUserBundle:Task')->find($id);
        
        if(!$task)
        {
            throw $this->createNotFoundException('task not found');
        }
        
        $form = $this->createCustomForm($task->getId(), 'PUT', 'infunisa_task_process');
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid())
        {
            
            $successMessage = $this->get('translator')->trans('The task has been processed.');
            $warningMessage = $this->get('translator')->trans('The task has already been processed.');
            
            if($task->getStatus()==0)
            {
                $task->setStatus(1);
                $em->flush();
                
                if($request->isXMLHttpRequest())
                {
                    return new Response(
                            json_encode(array('processed' => 1, 'success' => $successMessage)),        //método json_encode, => 1, indicando que nuestra tarea se ha finalizado correctamente
                            200,    //estado de la petición
                            array('Content-Type' => 'application/json')     //cabecera
                    );
                }
            }
            else        //en el caso de que la tarea ya ha sido finalizada no la vuelva a finalizar cuando se presione el botón Finalizar
            {
                if($request->isXMLHttpRequest())
                {
                    return new Response(
                            json_encode(array('processed' => 0, 'warning' => $warningMessage)),        //método json_encode, => 1, indicando que nuestra tarea se ha finalizado correctamente
                            200,    //estado de la petición
                            array('Content-Type' => 'application/json')     //cabecera
                    );
                }
            }
        }
    }

    public function addAction()
    {
        $task = new Task();
        $form = $this->createCreateForm($task);
        
        return $this->render('INFUNISAUserBundle:Task:add.html.twig', array('form' => $form->createView()));
    }
    
    private function  createCreateForm(Task $entity)
    {
        //esto era para Symfony2.7.3
        //$form = $this->createForm(new TaskType(), $entity, array(            
        //pero para Symfony3.4.15 ahora es:
        $form = $this->createForm(TaskType::class, $entity, array(        
            'action' => $this->generateUrl('infunisa_task_create'),
            'method' => 'POST'
        ));
        
        return $form;
    }
    
    //(Request $request), para procesar nuestro formulario
    public function createAction(Request $request)
    {
        $task = new Task();        
        $form = $this->createCreateForm($task);
        $form->handleRequest($request);
        
        if($form->isValid())
        {
            //esto para que nuestras tareas no sean procedas por el momento o inicialmente, por eso lo establecemos a cero. Cuando el ususario en la GUI finalice una tarea entonces dentro de la BD a ese campo se fija 1, que indica que la tarea está completada:
            $task->setStatus(0);
            $em = $this->getDoctrine()->getManager();
            $em->persist($task);
            $em->flush();       //para la persistencia de los datos
            
            //así era antes de aplicar la traducción
            //$this->addFlash('mensaje', 'The task has been created.');
            
            //y con aplicar traducción es:
            $successMessage = $this->get('translator')->trans('The task has been created.');
            $this->addFlash('mensaje', $successMessage);
            
            return $this->redirectToRoute('infunisa_task_index');            
        }
        
        return $this->render('INFUNISAUserBundle:Task:add.html.twig', array('form' => $form->createView()));
    }
    
    public function viewAction($id)
    {
        $task = $this->getDoctrine()->getRepository('INFUNISAUserBundle:Task')->find($id);      //Task es la entidad
        
        if(!$task)      //si la tarea no existe
        {
            throw $this->createNotFoundException('The task does not exist.');
        }
        
        //creando nuestro formulario para eliminar tareas dentro de la vista view.html.twig
        $deleteForm = $this->createCustomForm($task->getId(), 'DELETE', 'infunisa_task_delete');
        
        $user = $task->getUser();
        
        return $this->render('INFUNISAUserBundle:Task:view.html.twig', array('task' => $task, 'user' => $user, 'delete_form' => $deleteForm->createView()));    //'delete_form' => $deleteForm->createView(), lo utilizamos para enviar dentro del método render la creación de nuestro formulario contenido dentro de la variable $deleteForm
    }
    
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        
        $task = $em->getRepository('INFUNISAUserBundle:Task')->find($id);
        
        if(!$task)
        {
            throw $this->createNotFoundException('task not found');      //Excepción
        }
        
        $form = $this->createEditForm($task);       //vamos a crear nuestro formulario para editar nuestra respectiva tarea
        
        return $this->render('INFUNISAUserBundle:Task:edit.html.twig', array('task' => $task, 'form' => $form->createView()));        //carpeta Task que contiene edit.html.twig
    }
    
    private function createEditForm(Task $entity)       //recibiendo nuestra entidad de tarea
    {
        
        //esto era para Symfony2.7.3
        //$form = $this->createForm(new TaskType, $entity, array(     
        //pero para Symfony3.4.15 ahora es:        
        $form = $this->createForm(TaskType::class, $entity, array(
            'action' => $this->generateUrl('infunisa_task_update', array('id' => $entity->getId())),
            'method' => 'PUT'      //como estamos editando la tarea usamos PUT
        ));
        
        return $form;
    }
    
    public function updateAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        
        $task = $em->getRepository('INFUNISAUserBundle:Task')->find($id);
        
        if(!$task)
        {
            throw $this->createNotFoundException('task not found');      //Excepción
        }
        
        $form = $this->createEditForm($task);       //definir nuestro formulario vamos a enviar nuestra tarea con el método createEditForm
        $form->handleRequest($request);     //para procesar nuestra petición a partir del formulario que ya lo tenemos definido
        
        if($form->isSubmitted() and $form->isValid())
        {
            $task->setStatus(0);
            $em->flush();
            //así estaba inicialmente esta línea
            //$this->addFlash('mensaje', 'The task has been modified.');
            $successMessage = $this->get('translator')->trans('The task has been modified.');
            $this->addFlash('mensaje', $successMessage); 
            return $this->redirectToRoute('infunisa_task_edit', array('id' => $task->getId()));
        }
        
        return $this->render('INFUNISAUserBundle:Task:edit.html.twig', array('task' => $task, 'form' => $form->createView()));        //carpeta Task que contiene edit.html.twig
    }
    
    public function deleteAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        
        $task = $em->getRepository('INFUNISAUserBundle:Task')->find($id);
        
                if(!$task)
        {
            throw $this->createNotFoundException('task not found');      //Excepción
        }
        
        $form = $this->createCustomForm($task->getId(), 'DELETE', 'infunisa_task_delete');        
        $form->handleRequest($request);
        
        if($form->isSubmitted() and $form->isValid())
        {
            $em->remove($task);
            $em->flush();
            
            //así estaba inicialmente esta línea
            //$this->addFlash('mensaje', 'The task has been deleted.');
            
            $successMessage = $this->get('translator')->trans('The task has been deleted.');
            $this->addFlash('mensaje', $successMessage); 
            
            return $this->redirectToRoute('infunisa_task_index');
        }
    }

        public function createCustomForm ($id, $method, $route)
    {
        return $this->createFormBuilder()
                ->setAction($this->generateUrl($route, array('id' => $id)))
                ->setMethod($method)
                ->getForm();
    }
}
