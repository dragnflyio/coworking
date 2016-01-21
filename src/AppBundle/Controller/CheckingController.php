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
    $search_form = $this->getSearchForm();
    $form = $formbuilder->GenerateManualSearchControls($search_form);
    // Get connection database.
    $em = $this->getDoctrine()->getManager();
    $connection = $em->getConnection();
    $statement = $connection->prepare("SELECT * FROM `customer_timelog` ORDER BY checkin ASC");
    $statement->execute();
    $rows = $statement->fetchAll();
    $timelogs = array();
    $idx = 0;
    foreach ($rows as $timelog) {
      $memberid = $timelog['memberid'];
      // Get membername.
      $timelog['membername'] = $this->getMemberName($memberid);
      // Get package.
      $timelog['packagename'] = $this->getPackageNameByMemberId($memberid);
      $idx = ++$idx;
      $timelogs[$idx] = (object) $timelog;
    }
    return $this->render('checking/index.html.twig', [
      'timelogs' => $timelogs,
      'form' => $form,
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
        $op = 'Check out';
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
   * @Route("/checkout-all", name = "checkout_all")
   */
  public function checkOutAllFormAction(){
    $formbuilder = $this->get('app.formbuilder');
    $form = $formbuilder->BuildInputDateTimePicker('checkout', 'Checkout', true, false, null, 0);
    return $this->render('checking/checkoutall.html.twig', [
      'form' => $form,
      'script' => $formbuilder->mscript,
    ]);
  }

  /**
   * @Route("/checkout-all-ajax", name = "checkout_all_ajax")
   */
  public function checkOutAllAjaxAction(){
    $em = $this->getDoctrine()->getEntityManager();
    $connection = $em->getConnection();
    $statement = $connection->prepare("SELECT * FROM  `customer_timelog` WHERE checkout IS NULL");
    $statement->execute();
    $rows = $statement->fetchAll();
    foreach ($rows as $row) {
      $statement = $connection->prepare("UPDATE `customer_timelog` SET checkout=:checkout WHERE id=:id");
      $statement->bindParam(':checkout', $_POST['checkout']);
      $statement->bindParam(':id', $row['id']);
      $statement->execute();
      $data['m'] = 'Checkout thành công';
    }
    $response = new Response(
      json_encode($data),
      Response::HTTP_OK,
      array('content-type' => 'application/json')
    );
    return $response;
  }

  /**
   * @Route("/checking/search", name = "checking_search")
   */
  public function searchAction(){
    $formbuilder = $this->get('app.formbuilder');
    $grp = $this->getSearchForm();
    $searchData = $formbuilder->GetSearchData($_POST, $grp);
    $filters = '';
    if (!empty($searchData)){
      foreach ($searchData as $data) {
        if ('text_multi' == $data['type']) {
          $filters = $data['colname'] . ' IN (' . $data['v'] . ')';
        }
      }
    }

    $em = $this->getDoctrine()->getEntityManager();
    $connection = $em->getConnection();
    if (!empty($searchData)) {
      $statement = $connection->prepare("SELECT * FROM  `customer_timelog` WHERE $filters");
    } else {
      $statement = $connection->prepare("SELECT * FROM  `customer_timelog`");
    }
    $statement->execute();
    $rows = $statement->fetchAll();
    $ret = array();
    $idx = 0;
    $data = array();
    if (empty($rows)){
      $data['empty'] = 'Không tìm thấy bản ghi nào';
    } else {
      foreach ($rows as $row){
        $memberid = $row['memberid'];
        $type = '';
        if (empty($row['visitorname'])) {
          $type = 'member';
        } else {
          $type = 'visitor';
        }
        $tmp = array(
          'id' => $row['id'],
          'idx' => ++$idx,
          'name' => $this->getMemberName($memberid),
          'visitorname' => $row['visitorname'],
          'checkin' => date('d-m-Y H:i', $row['checkin']),
          'checkout' => date('d-m-Y H:i', $row['checkout']),
          'packagename' => $this->getPackageNameByMemberId($memberid),
          'type' => $type,
        );
        $ret[] = $tmp;
      }
      $data = $ret;
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

  /**
   * @Route("/get-member-ischeckin", name = "get_member_ischeckin")
   *
   * Get member is check in, not yet checkout.
   */
  /*public function getMemberIsCheckinAction(){
  }*/

  /**
   * Get search form.
   */
  private function getSearchForm(){
    $retval = array();
    // Member.
    $row = array();
    $row['id'] = 'memberid';
    $row['label'] = 'Thành viên';
    $row['type'] = 'text_multi';
    $row['pos'] = array('row' => 2, 'col' => 0);
    $row['colname'] = 'memberid';
    $row['pop'] = 'M';
    $row['ds'] = '/get-members';
    $row['value_maxlength'] = 1;
    $retval[] = $row;
    return $retval;
  }

  /**
   * Get member name from member id
   *
   * @param $memberid
   *
   * @return $membername
   */
  private function getMemberName($memberid){
    $em = $this->getDoctrine()->getManager();
    $connection = $em->getConnection();
    $statement = $connection->prepare("SELECT * FROM `member` WHERE id=:id");
    $statement->bindParam(':id', $memberid);
    $statement->execute();
    $rows = $statement->fetchAll();
    if (!empty($rows)) {
      return $rows[0]['name'];
    } else {
      return "Thành viên này không tồn tại.";
    }
  }

  /**
   * Get package name of member is using.
   *
   * @param $memberid
   *
   * @return $packagename
   */
  private function getPackageNameByMemberId($memberid){
    $em = $this->getDoctrine()->getManager();
    $connection = $em->getConnection();
    $statement = $connection->prepare("SELECT * FROM `member_package` where memberid = :id");
    $statement->bindParam(':id', $memberid);
    $statement->execute();
    $member_package = $statement->fetchAll();
    if (!empty($member_package)) {
      $statement = $connection->prepare("SELECT * FROM package where id = :packageid");
      $statement->bindParam(':packageid', $member_package[0]['packageid']);
      $statement->execute();
      $package = $statement->fetchAll();
      return $package[0]['name'];
    } else {
      return "Thành viên không đăng ký dùng gói nào.";
    }
  }
}
