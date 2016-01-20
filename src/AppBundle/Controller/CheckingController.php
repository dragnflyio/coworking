<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

use AppBundle\Utils;

class CheckingController extends Controller
{

  /**
   * @Route("/member/checking", name = "member_checking")
   */
  public function memberCheckingAction(){
    $formbuilder = $this->get('app.formbuilder');
    $tmp = $formbuilder->GenerateLayout('memberchecking');
    return $this->render('checking/member.html.twig', [
      'base_dir' => realpath($this->container->getParameter('kernel.root_dir').'/..'),
      'form' => $tmp,
      'script' => $formbuilder->mscript
    ]);
  }

  /**
   * @Route("/visitor/checking", name = "visitor_checking")
   */
  public function visitorCheckingAction(){
    $formbuilder = $this->get('app.formbuilder');
    $tmp = $formbuilder->GenerateLayout('visitorchecking');
    return $this->render('checking/visitor.html.twig', [
      'base_dir' => realpath($this->container->getParameter('kernel.root_dir').'/..'),
      'form' => $tmp,
      'script' => $formbuilder->mscript
    ]);
  }

  /**
   * @Route("/checking", name = "checking")
   *
   * Store checkin/checkout of member(or visitor) into database using ajax.
   */
  public function checkingAction(Request $request){
    if (false == $request->isXmlHttpRequest()){
      throw new HTTPException(403, 'Request forbidden');
    }
    // Get connection.
    $data = array();
    $em = $this->getDoctrine()->getEntityManager();
    $connection = $em->getConnection();
    // Get form builder.
    $formbuilder = $this->get('app.formbuilder');

    $op = $request->query->get('type', 'member');
    switch ($op) {
      case 'member':
        $dataObj = $formbuilder->PrepareInsert($_POST, 'memberchecking');
        foreach ($dataObj as $table => $postdata){
          if ($postdata){
            $data['v'] = $connection->insert($table, $postdata);
          }
        }
        $data['m'] = 'Thêm thành công';
        break;

      case 'visitor':
        $dataObj = $formbuilder->PrepareInsert($_POST, 'visitorchecking');
        foreach ($dataObj as $table => $postdata){
          if ($postdata){
            $postdata['isvisitor'] = 1;
            $data['v'] = $connection->insert($table, $postdata);
          }
        }
        $data['m'] = 'Thêm thành công';
        break;
    }

    $response = new Response(
      json_encode($data),
      Response::HTTP_OK,
      array('content-type' => 'application/json')
    );
    return $response;
  }

  /**
   * @Route("/get-members", name = "get_members")
   */
  public function getMembersAction(){
    $em = $this->getDoctrine()->getManager();
    $connection = $em->getConnection();
    $statement = $connection->prepare("SELECT * FROM `member` WHERE active = 1");
    $statement->execute();
    $rows = $statement->fetchAll();
    $members = array();
    foreach ($rows as $member) {
      $members[] = array($member['id'], $member['name']);
    }
    $response = new Response(
      json_encode($members),
      Response::HTTP_OK,
      array('content-type' => 'application/json')
    );
    return $response;
  }
}
