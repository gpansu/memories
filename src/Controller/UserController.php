<?php
// src/Controller/UserController.php
namespace App\Controller;

use App\Entity\Account;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class UserController extends AbstractController
{
    /**
     * @Route("/{_locale}/admin/users", name="admin")
     * @param Request $request
     * @param LoggerInterface $logger
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function adminAction(Request $request, LoggerInterface $logger)
    {
        $this->get('session')->set('currentPage', 'admin/users');
        $user = $this->getUser();
        $users = $this->getDoctrine()
            ->getRepository(Account::class)
            ->findAll();
        return $this->render('users.html.twig', array(
            'currentUser' => $user,
            'users' => $users));
    }
}