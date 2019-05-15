<?php
// src/Controller/SecurityController.php
namespace App\Controller;

use App\Entity\Account;
use App\Form\ResetPasswordForm;
use App\Service\MailService;
use App\Service\SecurityService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/{_locale}/login", name="login")
     * @param AuthenticationUtils $authenticationUtils
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function loginAction(AuthenticationUtils $authenticationUtils)
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', array(
            'last_username' => $lastUsername,
            'error' => $error,
        ));
    }

    /**
     * @Route("{_locale}/logout", name="logout")
     */
    public function logout()
    {
        // controller can be blank: it will never be executed!
        throw new \Exception('Don\'t forget to activate logout in security.yaml');
    }

    /**
     * @Route("/{_locale}/resetPassword", name="reset_password")
     * @param Request $request
     * @param SecurityService $securityService
     * @param MailService $mailService
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function resetPassword(Request $request, SecurityService $securityService, MailService $mailService)
    {
        // 1) build the form
        $passwordIsReset = false;
        $form = $this->createForm(ResetPasswordForm::class);
        $username = null;

        // 2) handle the submit (will only happen on POST)
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $username = $form->get('username')->getData();
            if ($form->isValid() && $securityService->captchaverify($request->get('g-recaptcha-response'))) {

                $account = $this->getDoctrine()
                    ->getRepository(Account::class)
                    ->findOneBy(array('username' => $username));
                if ($account != null) {
                    $newPassword = $securityService->generatePassword();
                    $encodedPassword = $securityService->encode($account, $newPassword);
                    $account->setPassword($encodedPassword);
                    $account->setIsTemporary(true);
                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($account);
                    $entityManager->flush();
                    $locale = $request->getLocale();
                    $mailService->sendMailNoReply($this->getMailSubject($locale), $this->getMailBody($locale, $newPassword),[$username => $username]);
                }
                $passwordIsReset = true;
            } else {
                $this->addFlash(
                    'error',
                    'Please check the \'I\'m not a robot\' checkbox'
                );
            }
        }

        return $this->render(
            'security/reset-password.html.twig',
            array('form' => $form->createView(),
                'passwordIsReset' => $passwordIsReset,
                'username' => $username)
        );
    }

    function getMailBody(String $locale, $newPassword){
        if($locale == 'fr'){
            return 'Bonjour,<br><br>Un mot de passe temporaire a été créé pour votre compte. Veuillez le trouver ci-dessous:<br><br><div style="margin:10px;font-weight:bold">'
                .$newPassword
                .'</div><br>'
                .'Vous pouvez vous connecter <a href="https://papigato.eu/login">ici</a> (https://papigato.eu/login).'
                .'<br><br>The papigato team';
        }

        return 'Hello,<br><br>A new temporary password was defined for you account. Please find this new password below:<br><br><div style="margin:10px;font-weight:bold">'
            .$newPassword
            .'</div><br>'
            .'Please log in <a href="https://papigato.eu/login">here</a>'
            .'<br><br>The papigato team';
    }

    function getMailSubject(String $locale){
        if($locale == 'fr'){
            return 'Votre mot de passe Papigato a été réinitialisé';
        }
        return 'Your Papigato password was reset';
    }

}