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
    if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
      throw $this->createAccessDeniedException();
    }
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
      // Get Region
      $region = $services->getRegionbyId($timelog['regionid']);
      $regionname = !empty($region) ? $region['name'] : '';
      $timelog['regionname'] = $regionname;
      // Get User check in
<<<<<<< HEAD
      $checkinuser = $services->loadUserById($timelog['checkinby']);
      $checkoutuser = $services->loadUserById($timelog['checkoutby']);
      $timelog['checkin_user'] = $checkinuser ? $checkinuser['username']: '';
      $timelog['checkout_user'] = $checkoutuser ? $checkoutuser['username']: '';
=======
      if ($timelog['checkinby'] != 0) {
        $checkinuser = $services->loadUserById($timelog['checkinby']);
      }
      if ($timelog['checkoutby'] != 0) {
        $checkoutuser = $services->loadUserById($timelog['checkoutby']);
      }
      $timelog['checkin_user'] = isset($checkinuser) ? $checkinuser['username'] : '';
      $timelog['checkout_user'] = isset($checkoutuser) ? $checkoutuser['username'] : '';
>>>>>>> 09bdec25e38dbfc74e3f1ecf1e725bc03f088df4
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
    if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
      throw $this->createAccessDeniedException();
    }
    // Get connection.
    $em = $this->getDoctrine()->getEntityManager();
    $connection = $em->getConnection();
    // Get form builder.
    $formbuilder = $this->get('app.formbuilder');

    $op = $request->query->get('op');
    switch ($op) {
      case 'checkin':
        $tmp = $formbuilder->GenerateLayout('memberchecking', "col_name NOT IN ('checkout', 'printedpapers')");
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
    if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
      throw $this->createAccessDeniedException();
    }
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
    // Get current location
    $user = $this->getUser();
    $regionid = $user->getLoggedregionId();
    $regioninfo = $services->getRegionbyId($regionid);

    $op = $request->query->get('type', 'member');
    switch ($op) {
      case 'member':
        if (empty($_POST['id'])) {
          $dataObj = $formbuilder->PrepareInsert($_POST, 'memberchecking');
          foreach ($dataObj as $table => $postdata){
            if ($postdata){
              $postdata['regionid'] = $regionid;
              // $package = $services->getPackageByMemberId($postdata['memberid']);
              $package = $services->getPackageByMemberId_alt($postdata['memberid']);
<<<<<<< HEAD
              $package_regions = explode(',', trim($package['regionid']));
              $package_regions = array_filter($package_regions);
=======
              if (isset($package['regionid'])) {
                $package_regions = explode(',', $package['regionid']);
              } else {
                $package_regions = array();
              }
>>>>>>> 09bdec25e38dbfc74e3f1ecf1e725bc03f088df4
              $group = $services->getGroupByMemberId($postdata['memberid']);
              if (!empty($group)) {
                $postdata['grouppackageid'] = $package['grouppackageid'];
                $data['v'] = $connection->insert($table, $postdata);
              } else {
                if (!empty($package)) {
                  if (in_array($regionid, $package_regions) || empty($package_regions)) {
                    $postdata['checkinby'] = $user->getId();
                    $postdata['memberpackageid'] = $package['memberpackageid'];
                    $data['v'] = $connection->insert($table, $postdata);
                  } else {
                    $data['e'] = 'Thành viên này không đăng ký tại ' . $regioninfo['name'];
                  }
                } else {
                  $data['e'] = 'Thành viên này chưa dùng gói nào.';
                }
              }
            }
          }
          $data['m'] = 'Thêm thành công.';
        } else {
          $formbuilder->setUpdateMode(true);
          $dataObj = $formbuilder->PrepareInsert($_POST, 'memberchecking');
          foreach ($dataObj as $table => $postdata){
            if ($postdata){
              $package = $services->getPackageByMemberId_alt($postdata['memberid']);
              if (isset($package['regionid'])) {
                $package_regions = explode(',', $package['regionid']);
              } else {
                $package_regions = array();
              }
              $postdata['visitedhours'] = ($postdata['checkout'] - $postdata['checkin']) / 60;
              $postdata['regionid'] = $regionid;
              $postdata['checkoutby'] = $user->getId();
              $package_regions = array_filter($package_regions);
              if (in_array($regionid, $package_regions) || empty($package_regions)) {
                $data['v'] = $connection->update($table, $postdata, array('id' => $_POST['id']));
              } else {
                $data['e'] = 'Thành viên này không đăng ký tại ' . $regioninfo['name'];
              }
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
              $postdata['regionid'] = $regionid;
              $package = $services->getPackageByMemberId_alt($postdata['memberid']);
              if (isset($package['regionid'])) {
                $package_regions = explode(',', $package['regionid']);
              } else {
                $package_regions = array();
              }
              $group = $services->getGroupByMemberId($postdata['memberid']);
              if (!empty($group)) {
                $postdata['grouppackageid'] = $package['grouppackageid'];
              } else {
                $postdata['memberpackageid'] = $package['memberpackageid'];
              }
              $postdata['isvisitor'] = 1;
              if (in_array($regionid, $package_regions) || empty($package_regions)) {
                $postdata['checkinby'] = $user->getId();
                $data['v'] = $connection->insert($table, $postdata);
              } else {
                $data['e'] = 'Thành viên này không đăng ký tại ' . $regioninfo['name'];
              }
            }
          }
          $data['m'] = 'Thêm thành công';
        } else {
          $formbuilder->setUpdateMode(true);
          $dataObj = $formbuilder->PrepareInsert($_POST, 'visitorchecking');
          foreach ($dataObj as $table => $postdata){
            if ($postdata){
              $package = $services->getPackageByMemberId_alt($postdata['memberid']);
              if (isset($package['regionid'])) {
                $package_regions = explode(',', $package['regionid']);
              } else {
                $package_regions = array();
              }
              $postdata['regionid'] = $regionid;
              $postdata['checkoutby'] = $user->getId();
              $postdata['visitedhours'] = ($postdata['checkout'] - $postdata['checkin']) / 60;
              if (in_array($regionid, $package_regions) || empty($package_regions)) {
                $data['v'] = $connection->update($table, $postdata, array('id' => $_POST['id']));
              } else {
                $data['e'] = 'Thành viên này không đăng ký tại ' . $regioninfo['name'];
              }
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
    if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
      throw $this->createAccessDeniedException();
    }
    // Get services.
    $services = $this->get('app.services');
    $validation = $this->get('app.validation');
    // Get connection.
    $em = $this->getDoctrine()->getManager();
    $connection = $em->getConnection();
    //  Get member by id.
    $member = $services->loadMember($id);
    $data = array();
    if (empty($member)) throw $this->createNotFoundException('Không tìm thấy thành viên hoặc thành viên này ngừng hoạt động.');
    $data['name'] = $member['name'];
    $data['phone'] = $member['phone'];
    // Get package.
    $package = $services->getPackageByMemberId($id);
    $data['packagename'] = $package['name'];
    $data['maxhours'] = $package['maxhours'];
    $data['maxdays'] = $package['maxdays'];
    $data['price_hour'] = 0 < $package['maxhours'] ? $package['price'] / $package['maxhours'] : 0;
    // Get effective from datetime start using package
    $statement = $connection->prepare("SELECT * FROM `member_package`
    WHERE memberid=:memberid and active=1 and packageid=:packageid");
    $statement->bindParam(':memberid', $id);
    $statement->bindParam(':packageid', $package['id']);
    $statement->execute();
    $member_package = $statement->fetchAll();

    if (empty($member_package)) {
      $group = $services->getGroupByMemberId($id);
      $statement = $connection->prepare("SELECT * FROM `group_package`
      WHERE groupid=:groupid and active=1 and packageid=:packageid");
      $statement->bindParam(':groupid', $group['groupid']);
      $statement->bindParam(':packageid', $package['id']);
      $statement->execute();
      $group_package = $statement->fetchAll();
      if (empty($group_package)) throw $this->createNotFoundException('Thành viên thuộc nhóm không dùng gói nào.');
      $efffrom = $group_package[0]['efffrom'];
      $max_printedpaper = $group_package[0]['maxprintpapers'];
      $members = $services->getMembersInGroup($group['groupid']);
      $visitor_hours = $validation->getVisitorHoursOfGroup($group_package[0]['id']);
      $data['group_name'] = $group['name'];
      $price = $group_package[0]['price'];
    } else {
      $members = array($id);
      $max_printedpaper = $member_package[0]['maxprintpapers'];
      $efffrom = $member_package[0]['efffrom'];
      $visitor_hours = $validation->getVisitorHours($member_package[0]['id']);
      $price = $member_package[0]['price'];
    }
    // Get total hour is used.
    $tmp = implode(', ', $members);
    $statement = $connection->prepare("SELECT * FROM `customer_timelog`
    WHERE memberid IN ($tmp) AND $efffrom <= checkin  AND 0=isvisitor AND 1=status");
    $statement->execute();
    $timelogs = $statement->fetchAll();
    $totalMinutes = null;
    $totalPritedPaper = null;
    $logs = array();
    $idx = 0;
    foreach ($timelogs as $timelog) {
      $tmp = $services->loadMember($timelog['memberid']);
      if (null == $timelog['checkout']) {
        $current_log = ceil((time() - $timelog['checkin']) / 60);
        $totalMinutes += $current_log;
      } else {
        $totalMinutes += $timelog['visitedhours'];
      }
      $totalPritedPaper += $timelog['printedpapers'];
      $timelog['member_name'] = $tmp['name'];
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
    // Printed paper.
    $data['price'] = $price;
    $data['maxprintpapers'] = $max_printedpaper;
    $data['used_pritedpapers'] = $totalPritedPaper;
    $data['over_printedpapers'] = abs(min(0, $max_printedpaper - $totalPritedPaper));
    $data['money_printedpaper'] = $data['over_printedpapers'] * 1000;
    // Total hours that member used.
    $data['totalhours'] = $totalMinutes;
    $data['totalhoursover'] = $totalHoursOver;
    // Total hours of visitors.
    $totalvisitorhours = null;
    foreach ($visitor_hours as $value) {
      $totalvisitorhours += $value['visit'];
    }
    $data['visitor_hours'] = max(0, $totalvisitorhours);

    $data['money'] = $price + $totalHoursOver * $data['price_hour']/60 + $data['money_printedpaper'];
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
    if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
      throw $this->createAccessDeniedException();
    }
    // Get services.
    $services = $this->get('app.services');
    $validation = $this->get('app.validation');
    // Get connection.
    $em = $this->getDoctrine()->getManager();
    $connection = $em->getConnection();
    //  Get member by id.
    $member = $services->loadMember($id);
    $data = array();
    if (empty($member)) throw $this->createNotFoundException('Không tìm thấy thành viên hoặc thành viên này ngừng hoạt động.');
    $data['name'] = $member['name'];
    $data['phone'] = $member['phone'];
    // Get package.
    $package = $services->getPackageByMemberId($id);
    $data['packagename'] = $package['name'];
    $data['maxhours'] = $package['maxhours'];
    $data['maxdays'] = $package['maxdays'];
    $data['price_hour'] = 0 < $package['maxhours'] ? $package['price'] / $package['maxhours'] : 0;
    // Get effective from datetime start using package
    $statement = $connection->prepare("SELECT * FROM `member_package`
    WHERE memberid=:memberid and active=1 and packageid=:packageid");
    $statement->bindParam(':memberid', $id);
    $statement->bindParam(':packageid', $package['id']);
    $statement->execute();
    $member_package = $statement->fetchAll();

    if (empty($member_package)) {
      $group = $services->getGroupByMemberId($id);
      $statement = $connection->prepare("SELECT * FROM `group_package`
      WHERE groupid=:groupid and active=1 and packageid=:packageid");
      $statement->bindParam(':groupid', $group['groupid']);
      $statement->bindParam(':packageid', $package['id']);
      $statement->execute();
      $group_package = $statement->fetchAll();
      if (empty($group_package)) throw $this->createNotFoundException('Thành viên thuộc nhóm không dùng gói nào.');
      $efffrom = $group_package[0]['efffrom'];
      $max_printedpaper = $group_package[0]['maxprintpapers'];
      $members = $services->getMembersInGroup($group['groupid']);
      $visitor_hours = $validation->getVisitorHoursOfGroup($group_package[0]['id']);
      $data['group_name'] = $group['name'];
      $price = $group_package[0]['price'];
    } else {
      $members = array($id);
      $max_printedpaper = $member_package[0]['maxprintpapers'];
      $efffrom = $member_package[0]['efffrom'];
      $visitor_hours = $validation->getVisitorHours($member_package[0]['id']);
      $price = $member_package[0]['price'];
    }
    // Get total hour is used.
    $tmp = implode(', ', $members);
    $statement = $connection->prepare("SELECT * FROM `customer_timelog`
    WHERE memberid IN ($tmp) AND $efffrom <= checkin  AND 0=isvisitor AND 1=status");
    $statement->execute();
    $timelogs = $statement->fetchAll();
    $totalMinutes = null;
    $totalPritedPaper = null;
    $idx = 0;
    foreach ($timelogs as $timelog) {
      $tmp = $services->loadMember($timelog['memberid']);
      if (null == $timelog['checkout']) {
        $current_log = ceil((time() - $timelog['checkin']) / 60);
        $totalMinutes += $current_log;
      } else {
        $totalMinutes += $timelog['visitedhours'];
      }
      $totalPritedPaper += $timelog['printedpapers'];
      $timelog['member_name'] = $tmp['name'];
    }
    $totalHours = ceil($totalMinutes/60);// Need to calculate by minutes
    // if ($totalHours > $package['maxhours']) {
    if ($totalMinutes > 60*$package['maxhours']) {
      // $totalHoursOver = abs($package['maxhours'] - $totalHours);
      $totalHoursOver = abs($package['maxhours']*60 - $totalMinutes);
    } else {
      $totalHoursOver = 0;
    }
    // Printed paper.
    $data['price'] = $price;
    $data['maxprintpapers'] = $max_printedpaper;
    $data['used_pritedpapers'] = $totalPritedPaper;
    $data['over_printedpapers'] = abs(min(0, $max_printedpaper - $totalPritedPaper));
    $data['money_printedpaper'] = $data['over_printedpapers'] * 1000;
    // Total hours that member used.
    $data['totalhours'] = $totalMinutes;
    $data['totalhoursover'] = $totalHoursOver;
    // Total hours of visitors.
    $totalvisitorhours = null;
    foreach ($visitor_hours as $value) {
      $totalvisitorhours += $value['visit'];
    }
    $data['visitor_hours'] = max(0, $totalvisitorhours);

    $data['money'] = $price + $totalHoursOver * $data['price_hour']/60 + $data['money_printedpaper'];
    $data['efffrom'] = $efffrom;
    $data['memberid'] = $id;
    return $this->render('checking/print.html.twig', [
      'data' => $data,
    ]);
  }

  /**
   * @Route("/customer/{id}/payment", name = "customer_payment")
   */
  public function customerPaymentAction($id){
    $services = $this->get('app.services');
    $validation = $this->get('app.validation');
    // Get connection.
    $em = $this->getDoctrine()->getManager();
    $connection = $em->getConnection();

    $group = $services->getGroupByMemberId($id);
    if (!empty($group)){
      $members = $services->getMembersInGroup($group['groupid']);
      $tmp = implode(', ', $members);
      $statement = $connection->prepare("SELECT * FROM `customer_timelog`
      WHERE memberid IN ($tmp) AND 0=isvisitor AND 1=status");
      $statement->execute();
      $timelogs = $statement->fetchAll();
      foreach ($timelogs as $log) {
        $log['status'] = 2;
        $data['v'] = $connection->update('customer_timelog', $log, array('id' => $log['id']));
      }
      $data['m'] = 'Đã thanh toán thành công';
    } else {
      $statement = $connection->prepare("SELECT * FROM `customer_timelog`
      WHERE memberid = $id AND 0=isvisitor AND 1=status");
      $statement->execute();
      $timelogs = $statement->fetchAll();
      foreach ($timelogs as $log) {
        $log['status'] = 2;
        $data['v'] = $connection->update('customer_timelog', $log, array('id' => $log['id']));
      }
      $data['m'] = 'Đã thanh toán thành công';
    }

    $response = new Response(
      json_encode($data),
      Response::HTTP_OK,
      array('content-type' => 'application/json')
    );
    return $response;
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
        $region = $services->getRegionbyId($row['regionid']);
        $regionname = !empty($region) ? $region['name'] : '';
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
        $checkinuser = $services->loadUserById($row['checkinby']);
        $checkoutuser = $services->loadUserById($row['checkoutby']);
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
          'regionname' => $regionname,
          'checkin_user' => $checkinuser['username'],
          'checkout_user' => $checkoutuser['username'],
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
    $statement = $connection->prepare("SELECT * FROM `member` WHERE active = 1 AND name like :name LIMIT 30");
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
