<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;


class testController extends AbstractController
{
    /**
     * @Route("/user",name="utilisateur_main_testHome")
     */
    public function testHome(){

        echo "hello tout le monde";
        die();
    }

    /**
     * @Route("/admin",name="admin_main_testHome")
     */
    public function testHomeAdmin(){

        echo "hello admin";
        die();
    }

}