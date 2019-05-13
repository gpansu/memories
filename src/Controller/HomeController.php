<?php
// src/Controller/HomeController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends Controller
{
    /**
     * @Route("/home", name="home")
     */
    public function homeAction()
    {
        $this->get('session')->set('currentPage', 'home');
        $user = $this->getUser();
        /*$accounts = $this->getDoctrine()
            ->getRepository(Account::class)
            ->findAll();*/
		return $this->render('home.html.twig', array(
            'currentUser' => $user
        ));
    }

    /**
     * @Route("/", name="default")
     */
    public function redirectToHome()
    {
        return $this->redirectToRoute('home');
    }
}