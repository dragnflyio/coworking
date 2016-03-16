<?php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Utils;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Form\UserType;
use AppBundle\Entity\User;

class SecurityController extends Controller
{
  /**
   * @Route("/login", name="login_route")
   */
  public function loginAction(Request $request){
    $authenticationUtils = $this->get('security.authentication_utils');

    // get the login error if there is one
    $error = $authenticationUtils->getLastAuthenticationError();

    // last username entered by the user
    $lastUsername = $authenticationUtils->getLastUsername();

    return $this->render(
      'security/login.html.twig',
      array(
          // last username entered by the user
          'last_username' => $lastUsername,
          'error'         => $error,
      )
    );
  }
  /**
   * @Route("/adduser", name="add_user")
   */
  function adduserAction(Request $request){
    // 1) build the form
    $user = new User();
    $form = $this->createForm(UserType::class, $user);

        // 2) handle the submit (will only happen on POST)
    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {

            // 3) Encode the password (you could also do this via Doctrine listener)
      $password = $this->get('security.password_encoder')
      ->encodePassword($user, $user->getPassword());
      $user->setPassword($password);
      $user->setRoles('ROLE_USER');
            // $user->setCreated(new \DateTime());


            // 4) save the User!
      $em = $this->getDoctrine()->getManager();
      $em->persist($user);
      $em->flush();

            // ... do any other work - like send them an email, etc
            // maybe set a "flash" success message for the user

      return $this->redirectToRoute('login_route');
    }

    return $this->render(
      'security/adduser.html.twig',
      array('form' => $form->createView())
      );
  }
  /**
   * @Route("/logout", name="logout")
   */
  public function logoutAction(){

  }

  /**
   * @Route("/login_check", name="login_check")
   */
  public function loginCheckAction(){
    // this controller will not be executed,
    // as the route is handled by the Security system
  }

}
