<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

class MainController extends AbstractController
{
    public function index(Session $session) : Response
    {   
        return $this->render('main/index.html.twig');
    }
}