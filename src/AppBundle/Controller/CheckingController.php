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
   * @Route("/checking", name = "checking_list")
   */
  public function indexAction(){
    // Get form builder.
    $formbuilder = $this->get('app.formbuilder');

    // Get connection database.
    $em = $this->getDoctrine()->getManager();
    $connection = $em->getConnection();
    $statement = $connection->prepare("SELECT * FROM `customer_timelog`");
    $statement->execute();
    $rows = $statement->fetchAll();
    $timelogs = array();
    foreach ($rows as $timelog) {
      $memberid = $timelog['memberid'];
      // Get member name.
      $statement = $connection->prepare("SELECT * FROM `member` where id = :id");
      $statement->bindParam(':id', $memberid);
      $statement->execute();
      $member = $statement->fetchAll();
      $timelog['membername'] = $member[0]['name'];
      // Get package.
      $statement = $connection->prepare("SELECT * FROM `member_package` where memberid = :id");
      $statement->bindParam(':id', $memberid);
      $statement->execute();
      $member_package = $statement->fetchAll();
      if (!empty($package)) {
        $statement = $connection->prepare("SELECT * FROM `package` where id = :id");
        $statement->bindParam(':id', $member_package[0]['packageid']);
        $statement->execute();
        $package = $statement->fetchAll();
        $timelog['packagename'] = $package[0]['name'];
      } else {
        $timelog['packagename'] = 'Thành viên không đăng ký dùng gói nào.';
      }
      $timelogs[] = (object) $timelog;
    }
    return $this->render('checking/index.html.twig', [
      'timelogs' => $timelogs,
      // 'form' => $form,
      'script' => $formbuilder->mscript,
    ]);
  }

  /**
   * @Route("/member/checking", name = "member_checking")
   */
  public function memberCheckingAction(Request $request){
    // Get connection.
    $em = $this->getDoctrine()->getEntityManager();
    $connection = $em->getConnection();
    // Get form builder.
    $formbuilder = $this->get('app.formbuilder');

    $op = $request->query->get('op', 'member');
    switch ($op) {
      case 'checkin':
        $tmp = $formbuilder->GenerateLayout('memberchecking');
        $op = 'Check in';
        break;

      case 'checkout' :
        $id = $request->query->get('id');
        $statement = $connection->prepare("SELECT * FROM `customer_timelog` where id=:id");
        $statement->bindParam(':id', $id);
        $statement->execute();
        $rows = $statement->fetchAll();
        if (empty ($rows)) throw $this->createNotFoundException('Không tìm thấy đăng ký này');
        $tmp = $formbuilder->LoadDatarowToConfig($rows[0], 'memberchecking');
        $op = 'Check in';
        break;
    }

    return $this->render('checking/member.html.twig', [
      'base_dir' => realpath($this->container->getParameter('kernel.root_dir').'/..'),
      'form' => $tmp,
      'script' => $formbuilder->mscript,
      'op' => $op,
    ]);
  }

  /**
   * @Route("/visitor/checking", name = "visitor_checking")
   */
  public function visitorCheckinAction(Request $request){
    // Get connection.
    $em = $this->getDoctrine()->getEntityManager();
    $connection = $em->getConnection();
    // Get formbuilder
    $formbuilder = $this->get('app.formbuilder');
    $op = $request->query->get('op', 'member');
    switch ($op) {
      case 'checkin':
        $tmp = $formbuilder->GenerateLayout('visitorchecking');
        $op = 'Check in';
        break;

      default:
        $id = $request->query->get('id');
        $statement = $connection->prepare("SELECT * FROM `customer_timelog` where id=:id");
        $statement->bindParam(':id', $id);
        $statement->execute();
        $rows = $statement->fetchAll();
        if (empty ($rows)) throw $this->createNotFoundException('Không tìm thấy đăng ký này');
        $tmp = $formbuilder->LoadDatarowToConfig($rows[0], 'visitorchecking');
        $op = 'Check out';
        break;
    }
    return $this->render('checking/visitor.html.twig', [
      'base_dir' => realpath($this->container->getParameter('kernel.root_dir').'/..'),
      'form' => $tmp,
      'script' => $formbuilder->mscript,
      'op' => $op,
    ]);
  }

  /**
   * @Route("/checking-ajax", name = "checking_ajax")
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
        if (empty($_POST['id'])) {
          $dataObj = $formbuilder->PrepareInsert($_POST, 'memberchecking');
          foreach ($dataObj as $table => $postdata){
            if ($postdata){
              $data['v'] = $connection->insert($table, $postdata);
            }
          }
          $data['m'] = 'Thêm thành công';
        } else {
          $formbuilder->setUpdateMode(true);
          $dataObj = $formbuilder->PrepareInsert($_POST, 'memberchecking');
          foreach ($dataObj as $table => $postdata){
            if ($postdata){
              $postdata['visitedhours'] = ($postdata['checkout'] - $postdata['checkin']) / 60;
              $data['v'] = $connection->update($table, $postdata, array('id' => $_POST['id']));
            }
          }
          $data['m'] = 'Check out thành công';
        }
        break;

      case 'visitor':
        if (empty($_POST['id'])) {
          $dataObj = $formbuilder->PrepareInsert($_POST, 'visitorchecking');
          foreach ($dataObj as $table => $postdata){
            if ($postdata){
              $postdata['isvisitor'] = 1;
              $data['v'] = $connection->insert($table, $postdata);
            }
          }
          $data['m'] = 'Thêm thành công';
        } else {
          $formbuilder->setUpdateMode(true);
          $dataObj = $formbuilder->PrepareInsert($_POST, 'visitorchecking');
          foreach ($dataObj as $table => $postdata){
            if ($postdata){
              $postdata['visitedhours'] = ($postdata['checkout'] - $postdata['checkin']) / 60;
              $data['v'] = $connection->update($table, $postdata, array('id' => $_POST['id']));
            }
          }
          $data['m'] = 'Check out thành công';
        }
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
