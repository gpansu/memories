<?php
// src/Controller/HomeController.php
namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends Controller
{
    /**
     * @Route("/{_locale}/home", name="home")
     * @param LoggerInterface $logger
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function homeAction(LoggerInterface $logger)
    {
        $this->get('session')->set('currentPage', 'home');
        $user = $this->getUser();
        if($user->getIsTemporary()){
            return $this->redirect('/changePassword');
        } else {
            $this->get('session')->set('currentPage', 'home');
            return $this->render('home.html.twig', array(
                'currentUser' => $user
            ));
        }
    }

    /**
     * @Route("/", name="default")
     */
    public function redirectToHome()
    {
        return $this->redirectToRoute('home');
    }
}