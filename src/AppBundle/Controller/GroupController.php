<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

use AppBundle\Utils;

/**
 * @Route("/group")
 */
class GroupController extends Controller
{

  /**
   * @Route("/", name = "group_list")
   */
  public function indexAction(Request $request){
    // Get form builder.
    $formbuilder = $this->get('app.formbuilder');
    $search_form = $this->getGroupSearchForm();
    $form = $formbuilder->GenerateManualSearchControls($search_form);
    // Get connection database.
    $em = $this->getDoctrine()->getManager();
    $connection = $em->getConnection();
    $statement = $connection->prepare("SELECT * FROM `groups` WHERE status = 1");
    $statement->execute();
    $rows = $statement->fetchAll();
    $groups = array();
    foreach ($rows as $row) {
      $groups[] = (object) $row;
    }
    return $this->render('group/index.html.twig', [
      'groups' => $groups,
      'form' => $form,
      'script' => $formbuilder->mscript,
    ]);
  }

  /**
   * @Route("/add", name ="group_add")
   */
  public function addAction(){
    $formbuilder = $this->get('app.formbuilder');
    $tmp = $formbuilder->GenerateLayout('group');
    return $this->render('group/form.html.twig', [
      'base_dir' => realpath($this->container->getParameter('kernel.root_dir').'/..'),
      'form' => $tmp,
      'script' => $formbuilder->mscript
    ]);
  }

  /**
   * @Route("/edit/{id}", requirements={"id" = "\d+"}, name = "group_edit")
   */
  public function editAction($id, Request $request){
    $formbuilder = $this->get('app.formbuilder');
    $request = Request::createFromGlobals();
    $gid = (int)$id;
    $em = $this->getDoctrine()->getEntityManager();
    $connection = $em->getConnection();
    $statement = $connection->prepare("SELECT * FROM `groups` WHERE id = $gid");
    $statement->execute();
    $row = $statement->fetchAll();
    $tmp = $formbuilder->LoadDatarowToConfig($row[0], 'group');
    return $this->render('group/form.html.twig', [
        'base_dir' => realpath($this->container->getParameter('kernel.root_dir').'/..'),
        'form' => $tmp,
        'script' => $formbuilder->mscript
    ]);
  }

  /**
   * @Route("/delete", name = "group_delete")
   */
  public function deleteGroupAction(){
    $id = $_POST['id'];
    $em = $this->getDoctrine()->getManager();
    $connection = $em->getConnection();
    $statement = $connection->prepare("UPDATE groups SET status = 0 where id=:id");
    $statement->bindParam(':id', $id);
    $statement->execute();
    $data['m'] = 'Đã vô hiệu nhóm này!';
    $response = new Response(
      json_encode($data),
      Response::HTTP_OK,
      array('content-type' => 'application/json')
    );
    return $response;
  }

  /**
   * @Route("/search", name = "group_search")
   */
  public function searchAction(){
    $formbuilder = $this->get('app.formbuilder');
    $services = $this->get('app.services');
    $grp = $this->getGroupSearchForm();
    $searchData = $formbuilder->GetSearchData($_POST, $grp);
    $filters = $this->getWhereUserSearchCondition($searchData);
    $em = $this->getDoctrine()->getEntityManager();
    $connection = $em->getConnection();
    $statement = $connection->prepare("SELECT * FROM `groups` WHERE 1=status $filters");
    $statement->execute();
    $all_rows = $statement->fetchAll();
    $ret = array();
    $idx = 0;
    if (empty($all_rows)){
      $data['empty'] = 'Không tìm thấy bản ghi nào';
    } else {
      foreach ($all_rows as $row){
        $packageid = $services->getPackageGroupUsing($row['id']);
        $tmp = array(
          'id' => $row['id'],
          'idx' => ++$idx,
          'name' => $row['name'],
          'address' => $row['address'],
          'phone' => $row['phone'],
          'taxcode' => $row['taxcode'],
          'taxaddress' => $row['taxaddress'],
          'description' => $row['description'],
          'members' => $row['members'],
          'packageid' => $packageid,
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
   * @Route("/ajax-action", name = "ajax_action")
   */
  public function ajaxAction(){
    $name = $_POST['name'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $taxcode = $_POST['taxcode'];
    $taxaddress = $_POST['taxaddress'];
    $description = $_POST['description'];
    $members = $_POST['members'];

    $em = $this->getDoctrine()->getManager();
    $connection = $em->getConnection();
    if (!empty($_POST['id'])) {
      $statement = $connection->prepare("UPDATE `groups`
        SET name = :name,
        address = :address,
        phone = :phone,
        taxcode = :taxcode,
        taxaddress = :taxaddress,
        description = :description,
        members = :members
        where id =:id");
      $statement->bindParam(':id', $_POST['id']);
      $data['m'] = "Bạn đã cập nhật nhóm thành công";
    }
    else {
      $statement = $connection->prepare("INSERT INTO `groups` (name, address, phone, taxcode, taxaddress, description, members, status)
      VALUES (:name, :address, :phone, :taxcode, :taxaddress, :description, :members, 1)");
      $data['m'] = "Bạn đã thêm mới nhóm thành công";
    }
    $statement->bindParam(':name', $name);
    $statement->bindParam(':address', $address);
    $statement->bindParam(':phone', $phone);
    $statement->bindParam(':taxcode', $taxcode);
    $statement->bindParam(':taxaddress', $taxaddress);
    $statement->bindParam(':description', $description);
    $statement->bindParam(':members', $members);
    $statement->execute();
    $response = new Response(
      json_encode($data),
      Response::HTTP_OK,
      array('content-type' => 'application/json')
    );
    return $response;
  }

  /**
   * @Route("/{id}/add-member", name = "group_add_member_form", requirements={"id" = "\d+"})
   *
   * Render form for user add member into group
   */
  public function addmemberformAction($id){
    $formbuilder = $this->get('app.formbuilder');
    $em = $this->getDoctrine()->getManager();
    $connection = $em->getConnection();
    $statement = $connection->prepare("SELECT * FROM `groups` WHERE 1=status AND id =:id");
    $statement->bindParam(':id', $id);
    $statement->execute();
    $rows = $statement->fetchAll();
    if (empty ($rows)) throw $this->createNotFoundException('Không tìm thấy nhóm!');
    $tmp = $formbuilder->GenerateLayout('group_member');
    return $this->render('group/formaddmember.html.twig', [
      'base_dir' => realpath($this->container->getParameter('kernel.root_dir').'/..'),
      'form' => $tmp,
      'script' => $formbuilder->mscript,
      'id' => $id,
      'group' => $rows[0],
    ]);
  }

  /**
   * @Route("/add-member-ajax", name = "group_add_member_ajax")
   *
   * Using ajax to add member into groups.
   */
  public function addmemberajaxAction(){
    $gid = $_POST['groupid'];
    $members = $_POST['members'];
    $members = explode(',', $members);

    $em = $this->getDoctrine()->getManager();
    $connection = $em->getConnection();
    foreach ($members as $mid) {
      $statement = $connection->prepare("SELECT * FROM group_member where memberid =:memberid AND groupid=:groupid");
      $statement->bindParam(':memberid', $mid);
      $statement->bindParam(':groupid', $gid);
      $statement->execute();
      $rows = $statement->fetchAll();
      if (!empty($rows)) {
        $statement = $connection->prepare("UPDATE group_member SET isdeleted = 0 where memberid=:mid");
      } else {
        $statement = $connection->prepare("INSERT INTO `group_member` (groupid, memberid, isdeleted)
      VALUES (:gid, :mid, 0)");
        $statement->bindParam(':gid', $gid);
      }
      $statement->bindParam(':mid', $mid);
      $statement->execute();
    }
    $data['m'] = 'Đã thêm thành viên vào nhóm!';
    $data['rdr'] = $this->generateUrl('group_list');

    $response = new Response(
      json_encode($data),
      Response::HTTP_OK,
      array('content-type' => 'application/json')
    );
    return $response;
  }

  /**
   * @Route("/member-json", name = "group_member_json")
   *
   * Get members not in any groups.
   */
  public function memberJsonNotInGroupAction(Request $request){
    $em = $this->getDoctrine()->getManager();
    $connection = $em->getConnection();
    $statement = $connection->prepare("SELECT memberid FROM group_member where isdeleted = 0");
    $statement->execute();
    $rows = $statement->fetchAll();
    $txt_search = $request->query->get('search');
    // Statement with members table.
    if (empty($rows)) {
      $statement2 = $connection->prepare("SELECT * FROM member");
    }
    else {
      $mids = array();
      foreach ($rows as $value) {
        $mids[] = $value['memberid'];
      }
      $membersInGroup = implode(', ', $mids);
      $statement2 = $connection->prepare("SELECT * FROM member WHERE id NOT IN ($membersInGroup) AND name LIKE :name LIMIT 30");
      $txt_search = "%" . $txt_search . "%";
      $statement2->bindParam(':name', $txt_search);
    }
    $statement2->execute();
    $rows2 = $statement2->fetchAll();

    $members = array();
    foreach ($rows2 as $member) {
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
   * @Route("/json", name = "group_json")
   */
  public function jsonAction(){
    $em = $this->getDoctrine()->getManager();
    $connection = $em->getConnection();
    $statement = $connection->prepare("SELECT * FROM `groups` WHERE status = 1");
    $statement->execute();
    $rows = $statement->fetchAll();
    $groups = array();
    foreach ($rows as $group) {
      $groups[] = array($group['id'], $group['name']);
    }
    $response = new Response(
      json_encode($groups),
      Response::HTTP_OK,
      array('content-type' => 'application/json')
    );
    return $response;
  }

  /**
   * @Route("/load-member", name = "group_load_member")
   */
  public function loadMembersInGroupAction(){
    if (isset($_POST['groupid'])) {
      $gid = $_POST['groupid'];
      $em = $this->getDoctrine()->getManager();
      $connection = $em->getConnection();
      $statement = $connection->prepare("SELECT memberid FROM group_member where groupid=:gid AND isdeleted = 0");
      $statement->bindParam(':gid', $gid);
      $statement->execute();
      $rows = $statement->fetchAll();
      $mids = array();
      foreach ($rows as $row) {
        $mids[] = $row['memberid'];
      }
      $tmp = implode(', ', $mids);
      if (!empty($tmp)) {
        $statement2 = $connection->prepare("SELECT * FROM member WHERE id IN ($tmp) ");
        $statement2->execute();
        $members = $statement2->fetchAll();
        $data = array();
        $idx = 0;
        foreach ($members as $member) {
          $data[] = array(
            'id' => $member['id'],
            'idx' => ++$idx,
            'name' => $member['name'],
            'phone' => $member['phone'],
            'email' => $member['email'],
          );
        }
      }
      else {
        $data['empty'] = 'Không tìm thấy bản ghi nào';
      }
    }
    else {
      $data['empty'] = 'Không tìm thấy bản ghi nào';
    }
    $response = new Response(
      json_encode($data),
      Response::HTTP_OK,
      array('content-type' => 'application/json')
    );
    return $response;
  }

  /**
   * @Route("/delete-member", name = "group_delete_member")
   */
  public function deleteMemeberInGroupAction(Request $request){
    $id = $_POST['id'];
    $em = $this->getDoctrine()->getManager();
    $connection = $em->getConnection();
    $statement = $connection->prepare("UPDATE group_member SET isdeleted = 1 where memberid=:id");
    $statement->bindParam(':id', $id);
    $statement->execute();
    $data['m'] = 'Đã xóa thành viên.';
    $response = new Response(
      json_encode($data),
      Response::HTTP_OK,
      array('content-type' => 'application/json')
    );
    return $response;
  }

  /**
   * @Route("/addpackage/{id}", name = "group_add_package")
   */
  public function addPackageFormAction($id){
    $formbuilder = $this->get('app.formbuilder');
    $validation = $this->get('app.validation');
    $tmp = $formbuilder->GenerateLayout('group_package');
    $em = $this->getDoctrine()->getEntityManager();
    $connection = $em->getConnection();
    $row = $connection->fetchAssoc('SELECT * FROM `groups` WHERE id=?', array($id));
    if (empty ($row)) throw $this->createNotFoundException('Không tìm thấy nhóm!');
    $error = false;
    if ($msg = $validation->checkGroupPackage($id)){
      $tmp = $msg;
      $error = true;
    } else {
      $tmp = $formbuilder->GenerateLayout('group_package');
    }
    return $this->render('group/addpackage.html.twig', [
      'form' => $tmp,
      'row' => $row,
      'id' => $id,
      'error' => $error,
      'script' => $formbuilder->mscript
    ]);
  }

  /**
   * @Route("/addpackage-ajax", name = "group_add_package_ajax")
   */
  public function addPackageAjaxAction(Request $request){
    $formbuilder = $this->get('app.formbuilder');
    $em = $this->getDoctrine()->getEntityManager();
    $connection = $em->getConnection();
    $groupid = $request->query->get('group', 0);
    if ($groupid){
      $dataObj = $formbuilder->PrepareInsert($_POST, 'group_package');
      foreach ($dataObj as $table => $postdata){
        if ($postdata){
          $postdata['groupid'] = $groupid;
          $data['v'] = $connection->insert($table, $postdata);
        }
      }
      $data['m'] = 'Thêm thành công';
    }

    $response = new Response(
      json_encode($data),
      Response::HTTP_OK,
      array('content-type' => 'application/json')
    );
    return $response;
  }

  /**
   * @Route("/editpackage/{id}", name = "group_edit_package", requirements={"id" = "\d+"})
   */
  public function editPackageFormAction($id){
    $formbuilder = $this->get('app.formbuilder');
    $services = $this->get('app.services');
    $em = $this->getDoctrine()->getEntityManager();
    $connection = $em->getConnection();
    $group = $connection->fetchAssoc('SELECT * FROM groups WHERE id=?', array($id));
    if (empty ($group)) throw $this->createNotFoundException('Không tìm thấy nhóm.');
    // get active package?
    $row = $connection->fetchAssoc('SELECT packageid FROM group_package WHERE active=1 AND groupid=?', array($id));
    if (empty ($row)) throw $this->createNotFoundException('Nhóm này chưa dùng gói nào, bạn cần thêm gói dịch vụ trước.');
    $current_package = $services->loadPackage($row['packageid']);
    $error = false;
    $form_update = $formbuilder->GenerateLayout('group_package', "col_name NOT IN ('packageid','efffrom', 'effto')");
    $script_update = $formbuilder->mscript;

    $form_change = $formbuilder->GenerateLayout('group_package','','change_');
    $script_change = $formbuilder->mscript;

    return $this->render('group/editpackage.html.twig', [
      'error' => $error,
      'form' => $form_update,
      'form_change' => $form_change,
      'group' => $group,
      'package' => $current_package,
      'groupid' => $id,
      'script' => $script_update,
      'script_change' => $script_change
    ]);
  }

  /**
   * @Route("/edit-package/ajax", name = "group_edit_package_ajax")
   */
  public function editPackageAjaxAction(Request $request){
    $formbuilder = $this->get('app.formbuilder');
    $em = $this->getDoctrine()->getEntityManager();
    $validation = $this->get('app.validation');
    $services = $this->get('app.services');
    $connection = $em->getConnection();
    $action = $request->query->get('action');
    $groupid = $request->query->get('id', 0);
    $data = array();
    switch ($action) {
      case 'extend':
        $newdate = $request->request->get('effto_extend');
        $amount = $formbuilder->getNum($request->request->get('price_extend'));
        $validation->extendGroupPackage($groupid, $newdate, $amount);
        $data['m'] = 'Đã cập nhật gia hạn tạm thời';
        break;

      case 'renew':
        // Get current package
        $statement = $connection->prepare("SELECT * FROM `group_package` WHERE groupid=:groupid AND active=1");
        $statement->bindParam(':groupid', $groupid);
        $statement->execute();
        $rows = $statement->fetchAll();
        $group_package = $rows[0];
        $package = $services->loadPackage($group_package['packageid']);
        // Close current package
        $newdate = $_POST['efffrom_renew'];
        $effto = (int) ($newdate-86400);
        $statement = $connection->prepare("UPDATE `group_package` SET active=0, effto=:effto WHERE active = 1 AND groupid=:groupid");
        $statement->bindParam(':effto', $effto);
        $statement->bindParam(':groupid', $groupid);
        $statement->execute();
        // Add new package for group
        $group_package['efffrom'] = $_POST['efffrom_renew'];
        $group_package['effto'] = $_POST['effto_renew'];
        unset($group_package['id']);
        $connection->insert('group_package', $group_package);
        // Update customer activity
        $log = array(
          'memberid' => $groupid,
          'code' => 'giahan',
          'oldvalue' => $package['name'],
          'newvalue' => $package['name'],
          'createdtime' => time(),
          'amount' => NULL,
        );
        $connection->insert('customer_activity', $log);
        $data['m'] = 'Gia hạn gói thành công';
        break;

      case 'change':
        if ($groupid){
          $validation = $this->get('app.validation');
          $dataObj = $formbuilder->PrepareInsert($_POST, 'group_package', 'change_');
          $group_package_current = $services->getPackageGroupUsing($groupid);
          foreach ($dataObj as $table => $postdata){
            if ($postdata){
              // Disable current package
              $services->closedGroupPackage($groupid);
              $postdata['groupid'] = $groupid;
              $data['v'] = $connection->insert($table, $postdata);
              // Log activity
              $used_minutes = $services->getUsedHoursInGroup($group_package_current['id']);
              // Get current package
              $fee = $group_package_current['price'] / ($group_package_current['maxhours'] * 60) * $used_minutes;
              // so du
              $remain = $group_package_current['price'] - $fee;
              $log = array(
                'groupid' => $groupid,
                'code' => 'changepackage',
                'oldvalue' => $group_package_current['packageid'],
                'newvalue' => $postdata['packageid'],
                'createdtime' => time(),
                'amount' => (int)$remain,
              );
              $data['v'] = $connection->insert('group_activity', $log);
            }
          }
          $data['m'] = 'Đổi gói thành công';
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
   * Build search group.
   *
   * @return array $retval
   */
  private function getGroupSearchForm() {
    $retval = array();
    // Group name.
    $row = array();
    $row['id'] = 'name';
    $row['label'] = 'Tên nhóm';
    $row['type'] = 'text';
    $row['colname'] = 'name';
    $row['pos'] = array('row' => 1, 'col' => 1);
    $retval[] = $row;

    // Phone number.
    $row = array();
    $row['id'] = 'phone';
    $row['label'] = 'Điện thoại';
    $row['type'] = 'text';
    $row['colname'] = 'phone';
    $row['pos'] = array('row' => 1, 'col' => 2);
    $retval[] = $row;

    // Taxcode.
    $row = array();
    $row['id'] = 'taxcode';
    $row['label'] = 'Mã số thuế';
    $row['type'] = 'text';
    $row['colname'] = 'taxcode';
    $row['pos'] = array('row' => 2, 'col' => 1);
    $retval[] = $row;

    // price
    $row = array();
    $row['id'] = 'members';
    $row['type'] = 'numeric';
    $row['lblfrom'] = 'Số thành viên từ';
    $row['lblto'] = ' đến ';
    $row['pos'] = array('row' => 2, 'col' => 2);
    $row['colname'] = 'members';
    $retval[] = $row;
    return $retval;
  }

  /**
   * Build condition where for user search
   */
  private function getWhereUserSearchCondition($searchData) {
    $formbuilder = $this->get('app.formbuilder');
    $where = '';
    foreach ($searchData as $data) {
      if ($data['colname'] != 'x') {
        $where .= $formbuilder->buildSingleCondition($data);
      } else {
      // custom condition
      }
    }
    return $where;
  }
}
