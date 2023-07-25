<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;


class testController extends AbstractController
{
    /**
     * @Route("/",name="main_testHome")
     */
    public function testHome(){

        echo "hello";
        die();
    }

}