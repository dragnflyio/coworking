<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @Route("/group")
 */
class GroupController extends Controller
{

  /**
   * @Route("/", name = "group_list")
   */
  public function indexAction(Request $request){
    return $this->render('group/index.html.twig', []);
  }

  /**
   * @Route("/add", name ="group_add")
   */
  public function addAction(){

  }
  /**
   * @Route("/edit/{gid}", requirements={"gid" = "\d+"}, name = "group_edit")
   */
  public function editAction(){

  }
}
