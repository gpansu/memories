<?php
// src/Controller/TestimoniesController.php
namespace App\Controller;

use App\Entity\Account;
use App\Entity\Testimony;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TestimoniesController extends Controller
{
    /**
     * @Route("/testimonies", name="testimonies")
     * @param Request $request
     * @param LoggerInterface $logger
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function loadAction(Request $request, LoggerInterface $logger)
    {
        $this->get('session')->set('currentPage', 'testimonies');
        $user = $this->getUser();
        $testimony = $this->getDoctrine()
            ->getRepository(Account::class)
            ->find($user->getId())->getTestimony();
        if($testimony == null){
            $testimony = new Testimony();
        }
        $form = $this->createFormBuilder($testimony)
            ->add('name', TextType::class, array('label' => 'My name'))
            ->add('address', TextareaType::class, array('label' => 'My postal address'))
            ->add('testimony', TextareaType::class, array('label' => 'How I met/worked/travelled/did stuff with Pierre'))
            ->getForm();


        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $testimony = $form->getData();
            $logger->info($testimony);
            $user->setTestimony($testimony);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->merge($user);
            $entityManager->flush();
            $this->addFlash('info', 'Testimony saved');
        }
		return $this->render('memories.html.twig', array(
            'currentUser' => $user,
            'form' => $form->createView(),
        ));
    }
}