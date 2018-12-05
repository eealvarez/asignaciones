<?php

namespace INFUNISA\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

//orginalmente el generador del controlador desde la línea de comandos, cuando creó este archivo, lo creó incluyendo esta línea pero en nuestro caso no la vamos a necesitar en este controlador

//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

//importamos la clase u objeto Request que forma parte del nécleo de Symfony, para trabajar más adelante al agregar nuestras próximas acciones y trabajar con AJAX y 
use Symfony\Component\HttpFoundation\Request;

class SecurityController extends Controller
{
    public function loginAction()
    {
        $authenticationUtils = $this->get('security.authentication_utils');      //security.authetication_utils es el servicio de autenticación
        
        $error = $authenticationUtils->getLastAuthenticationError();        //para que Symfony maneje los errores durante la autenticación de usuarios
        
        $lastUsername = $authenticationUtils->getLastUsername();        //si el usuario se autenticó de manera errónea, entonces cuando nos regrese al formulario de autenticación va a recuperar el último nombre de usuario que se colocó en el formulario
        
        return $this->render('INFUNISAUserBundle:Security:login.html.twig', array('last_username' => $lastUsername, 'error' => $error));      
        
    }
    
    public function  loginCheckAction()
    {
        
    }
}
