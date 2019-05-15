<?php
// src/Controller/RegistrationController.php
namespace App\Controller;

use App\Entity\Account;
use App\Form\RegistrationForm;
use App\Service\SecurityService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class RegistrationController extends Controller
{
    /**
     * @Route("/{_locale}/register", name="user_registration")
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param SecurityService $securityService
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function registerAction(Request $request, SecurityService $securityService)
    {
        // 1) build the form
        $user = new Account();
        $form = $this->createForm(RegistrationForm::class, $user);

        // 2) handle the submit (will only happen on POST)
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            if ($securityService->captchaverify($request->get('g-recaptcha-response'))) {

                $account = $this->getDoctrine()
                    ->getRepository(Account::class)
                    ->findBy(array('username' => $user->getUsername()));
                if ($account != null) {
                    $this->addFlash(
                        'error',
                        'This account already exists'
                    );
                } else {

                    // 3) Encode the password (you could also do this via Doctrine listener)
                    $password = $securityService->encode($user, $user->getPlainPassword());
                    $user->setPassword($password);

                    // 4) save the User!
                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($user);
                    $entityManager->flush();
                    $this->autologin($request, $user);
                    return $this->redirectToRoute('home');
                }
            } else {
                $this->addFlash(
                    'error',
                    'Please check the \'I\'m not a robot\' checkbox'
                );
            }
        }

        return $this->render(
            'registration/register.html.twig',
            array('form' => $form->createView())
        );
    }

    function autologin($request, $user)
    {
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->get('security.token_storage')->setToken($token);

        // If the firewall name is not main, then the set value would be instead:
        // $this->get('session')->set('_security_XXXFIREWALLNAMEXXX', serialize($token));
        $this->get('session')->set('_security_main', serialize($token));

        // Fire the login event manually
        $event = new InteractiveLoginEvent($request, $token);
        $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);
    }
}