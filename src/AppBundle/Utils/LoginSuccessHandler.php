<?php
namespace AppBundle\Utils;

use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
  private $em = null;
  function __construct($em) {
    $this->em = $em->getConnection();
  }
  public function onAuthenticationSuccess(Request $request, TokenInterface $token){
    $currenttime = time();
    $regionid = $_POST['_location'];
    $redirect_url = $request->headers->get('host');
    $count = $this->em->executeUpdate('UPDATE `users` SET lastlogintime = ' . $currenttime . ', regionid = ' . $regionid . ' WHERE username = ?', array($_POST['_username']));
    $response = new RedirectResponse('/');
    return $response;
  }
}
