<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

use AppBundle\Utils\Services;

class CheckingController extends Controller
{
  /**
   * @Route("/checking", name = "checking_list")
   */
  public function indexAction(){
    $services = $this->get('app.services');
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
      $package = $services->getPackageByMemberId($memberid);
      $timelog['packagename'] = $package['name'];
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
        $tmp = $formbuilder->GenerateLayout('memberchecking', "col_name NOT IN ('checkout')");
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
        $tmp = $formbuilder->GenerateLayout('visitorchecking', "col_name NOT IN ('checkout')");
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
    $services = $this->get('app.services');
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
              // $package = $services->getPackageByMemberId($postdata['memberid']);
              $package = $services->getPackageByMemberId_alt($postdata['memberid']);
              $group = $services->getGroupByMemberId($postdata['memberid']);
              if (!empty($group)) {
                $postdata['grouppackageid'] = $package['grouppackageid'];
              } else {
                $postdata['memberpackageid'] = $package['memberpackageid'];
              }
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
              $package = $services->getPackageByMemberId($postdata['memberid']);
              $group = $services->getGroupByMemberId($postdata['memberid']);
              if (!empty($group)) {
                $postdata['grouppackageid'] = $package['id'];
              } else {
                $postdata['memberpackageid'] = $package['id'];
              }
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
   * @Route("/customer/{id}/history", name = "customer_history")
   */
  public function customerHistoryAction($id){
    $services = $this->get('app.services');
    $em = $this->getDoctrine()->getManager();
    $connection = $em->getConnection();
    $statement = $connection->prepare("SELECT name, phone FROM `member` WHERE id=:id and active=1");
    $statement->bindParam(':id', $id);
    $statement->execute();
    $member = $statement->fetchAll();
    $data = array();
    if (empty($member)) throw $this->createNotFoundException('Không tìm thấy thành viên hoặc thành viên này ngừng hoạt động.');
    $data['name'] = $member[0]['name'];
    $data['phone'] = $member[0]['phone'];
    // Get package.
    $package = $services->getPackageByMemberId($id);
    $data['packagename'] = $package['name'];
    $data['maxhours'] = $package['maxhours'];
    $data['maxdays'] = $package['maxdays'];
    $data['price_hour'] = 0 < $package['maxhours'] ? $package['price'] / $package['maxhours'] : 0;
    // Get effective from datetime start using package
    $statement = $connection->prepare("SELECT efffrom FROM `member_package`
    WHERE memberid=:memberid and active=1 and packageid=:packageid");
    $statement->bindParam(':memberid', $id);
    $statement->bindParam(':packageid', $package['id']);
    $statement->execute();
    $member_package = $statement->fetchAll();
    if (empty($member_package)) {
      $group = $services->getGroupByMemberId($id);
      $statement = $connection->prepare("SELECT efffrom FROM `group_package`
      WHERE groupid=:groupid and active=1 and packageid=:packageid");
      $statement->bindParam(':groupid', $group['groupid']);
      $statement->bindParam(':packageid', $package['id']);
      $statement->execute();
      $group_package = $statement->fetchAll();
      if (empty($group_package)) throw $this->createNotFoundException('Thành viên thuộc nhóm không dùng gói nào.');
      $efffrom = $group_package[0]['efffrom'];
      $members = $services->getMembersInGroup($group['groupid']);
    } else {
      $members = array($id);
      $efffrom = $member_package[0]['efffrom'];
    }
    // Get total hour is used.
    $tmp = implode(', ', $members);
    $statement = $connection->prepare("SELECT * FROM `customer_timelog`
    WHERE memberid IN ($tmp) AND $efffrom <= checkin  AND visitorname IS NULL");//Why dont use isvisitor
    $statement->execute();
    $timelogs = $statement->fetchAll();
    $totalMinutes = null;
    $logs = array();
    $idx = 0;
    foreach ($timelogs as $timelog) {
      if (null == $timelog['checkout']) {
        $current_log = ceil((time() - $timelog['checkin']) / 60);
        $totalMinutes += $current_log;
      } else {
        $totalMinutes += $timelog['visitedhours'];
      }
      $logs[++$idx] = $timelog;
    }
    $totalHours = ceil($totalMinutes/60);// Need to calculate by minutes
    // if ($totalHours > $package['maxhours']) {
    if ($totalMinutes > 60*$package['maxhours']) {
      // $totalHoursOver = abs($package['maxhours'] - $totalHours);
      $totalHoursOver = abs($package['maxhours']*60 - $totalMinutes);
    } else {
      $totalHoursOver = 0;
    }
    $data['totalhours'] = $totalMinutes;
    $data['totalhoursover'] = $totalHoursOver;
    $data['money'] = $totalHoursOver * $data['price_hour']/60;
    $data['logs'] = $logs;
    $data['efffrom'] = $efffrom;
    $data['memberid'] = $id;
    return $this->render('checking/customerhistory.html.twig', [
      'data' => $data,
    ]);
  }

  /**
   * @Route("/customer/{id}/print-history", name = "customer_print_history")
   */
  public function printHistoryAction($id){
    $services = $this->get('app.services');
    $em = $this->getDoctrine()->getManager();
    $connection = $em->getConnection();
    $statement = $connection->prepare("SELECT name, phone FROM `member` WHERE id=:id and active=1");
    $statement->bindParam(':id', $id);
    $statement->execute();
    $member = $statement->fetchAll();
    $data = array();
    if (empty($member)) throw $this->createNotFoundException('Không tìm thấy thành viên hoặc thành viên này ngừng hoạt động.');
    $data['name'] = $member[0]['name'];
    $data['phone'] = $member[0]['phone'];
    // Get package.
    $package = $services->getPackageByMemberId($id);
    $data['packagename'] = $package['name'];
    $data['maxhours'] = $package['maxhours'];
    $data['maxdays'] = $package['maxdays'];
    $data['price_hour'] = 0 < $package['maxhours'] ? $package['price'] / $package['maxhours'] : 0;
    // Get effective from datetime start using package
    $statement = $connection->prepare("SELECT efffrom FROM `member_package`
    WHERE memberid=:memberid and active=1 and packageid=:packageid");
    $statement->bindParam(':memberid', $id);
    $statement->bindParam(':packageid', $package['id']);
    $statement->execute();
    $member_package = $statement->fetchAll();
    if (empty($member_package)) {
      $group = $services->getGroupByMemberId($id);
      $statement = $connection->prepare("SELECT efffrom FROM `group_package`
      WHERE groupid=:groupid and active=1 and packageid=:packageid");
      $statement->bindParam(':groupid', $group['groupid']);
      $statement->bindParam(':packageid', $package['id']);
      $statement->execute();
      $group_package = $statement->fetchAll();
      if (empty($group_package)) throw $this->createNotFoundException('Thành viên thuộc nhóm không dùng gói nào.');
      $efffrom = $group_package[0]['efffrom'];
      $members = $services->getMembersInGroup($group['groupid']);
    } else {
      $members = array($id);
      $efffrom = $member_package[0]['efffrom'];
    }
    // Get total hour is used.
    $tmp = implode(', ', $members);
    $statement = $connection->prepare("SELECT * FROM `customer_timelog`
    WHERE memberid IN ($tmp) AND $efffrom <= checkin  AND visitorname IS NULL");//Why dont use isvisitor
    $statement->execute();
    $timelogs = $statement->fetchAll();
    $totalMinutes = null;
    $idx = 0;
    foreach ($timelogs as $timelog) {
      if (null == $timelog['checkout']) {
        $current_log = ceil((time() - $timelog['checkin']) / 60);
        $totalMinutes += $current_log;
      } else {
        $totalMinutes += $timelog['visitedhours'];
      }
    }
    $totalHours = ceil($totalMinutes/60);// Need to calculate by minutes
    // if ($totalHours > $package['maxhours']) {
    if ($totalMinutes > 60*$package['maxhours']) {
      // $totalHoursOver = abs($package['maxhours'] - $totalHours);
      $totalHoursOver = abs($package['maxhours']*60 - $totalMinutes);
    } else {
      $totalHoursOver = 0;
    }
    $data['totalhours'] = $totalMinutes;
    $data['totalhoursover'] = $totalHoursOver;
    $data['money'] = $totalHoursOver * $data['price_hour']/60;
    $data['efffrom'] = $efffrom;
    return $this->render('checking/print.html.twig', [
      'data' => $data,
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
    $services = $this->get('app.services');
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
        $package = $services->getPackageByMemberId($memberid);
        $type = '';
        if (empty($row['visitorname'])) {
          $type = 'member';
        } else {
          $type = 'visitor';
        }
        $checkout = null;
        if (!empty($row['checkout'])) {
          $checkout = date('d-m-Y H:i', $row['checkout']);
        } else {
          $checkout = null;
        }
        $tmp = array(
          'id' => $row['id'],
          'idx' => ++$idx,
          'memberid' => $row['memberid'],
          'name' => $this->getMemberName($memberid),
          'visitorname' => $row['visitorname'],
          'checkin' => date('d-m-Y H:i', $row['checkin']),
          'checkout' => $checkout,
          'packagename' => $package['name'],
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
  public function getMembersAction(Request $request){
    $txt_search = $request->query->get('search');
    $em = $this->getDoctrine()->getManager();
    $connection = $em->getConnection();
    $statement = $connection->prepare("SELECT * FROM `member` WHERE active = 1 AND name like :name");
    $txt_search = "%" . $txt_search . "%";
    $statement->bindParam(':name', $txt_search);
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
    $row['ds'] = '@/get-members';
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
}
