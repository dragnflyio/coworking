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
    $statement = $connection->prepare("SELECT * FROM `groups`");
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
   * @Route("/search", name = "group_search")
   */
  public function searchAction(){
    $formbuilder = $this->get('app.formbuilder');
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
      $message = "Bạn đã cập nhật nhóm thành công";
    }
    else {
      $statement = $connection->prepare("INSERT INTO `groups` (name, address, phone, taxcode, taxaddress, description, members, status)
      VALUES (:name, :address, :phone, :taxcode, :taxaddress, :description, :members, 1)");
      $message = "Bạn đã thêm mới nhóm thành công";
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
      json_encode(array('message' => $message)),
      Response::HTTP_OK,
      array('content-type' => 'application/json')
    );
    return $response;
  }

  /**
   * @Route("/add-member", name = "group_add_member_form")
   *
   * Render form for user add member into group
   */
  public function addmemberformAction(){
    $formbuilder = $this->get('app.formbuilder');
    $tmp = $formbuilder->GenerateLayout('group_member');
    return $this->render('group/formaddmember.html.twig', [
      'base_dir' => realpath($this->container->getParameter('kernel.root_dir').'/..'),
      'form' => $tmp,
      'script' => $formbuilder->mscript
    ]);
  }

  /**
   * @Route("/add-member-ajax", name = "group_add_member_ajax")
   *
   * Using ajax to add member into groups.
   */
  public function addmemberajaxAction(){
    $gid = $_POST['gid'];
    $members = $_POST['members'];
    $members = explode(',', $members);

    $em = $this->getDoctrine()->getManager();
    $connection = $em->getConnection();
    foreach ($members as $mid) {
      $statement = $connection->prepare("INSERT INTO `group_member` (gid, mid, isdeleted)
      VALUES (:gid, :mid, 0)");
      $statement->bindParam(':gid', $gid);
      $statement->bindParam(':mid', $mid);
      $statement->execute();
    }
    $message = 'Đã thêm thành viên vào nhóm!';
    $response = new Response(
      json_encode(array('message' => $message)),
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
  public function memberJsonNotInGroupAction(){
    $em = $this->getDoctrine()->getManager();
    $connection = $em->getConnection();
    $statement = $connection->prepare("SELECT mid FROM group_member");
    $statement->execute();
    $rows = $statement->fetchAll();
    // Statement with members table.
    if (empty($rows)) {
      $statement2 = $connection->prepare("SELECT * FROM member");
    }
    else {
      $mids = array();
      foreach ($rows as $value) {
        $mids[] = $value['mid'];
      }
      $membersInGroup = implode(', ', $mids);
      $statement2 = $connection->prepare("SELECT * FROM member WHERE id NOT IN ($membersInGroup) ");
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
    $statement = $connection->prepare("SELECT * FROM `groups`");
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
    if (isset($_POST['gid'])) {
      $gid = $_POST['gid'];
      $em = $this->getDoctrine()->getManager();
      $connection = $em->getConnection();
      $statement = $connection->prepare("SELECT mid FROM group_member where gid=:gid AND isdeleted = 0");
      $statement->bindParam(':gid', $gid);
      $statement->execute();
      $rows = $statement->fetchAll();
      $mids = array();
      foreach ($rows as $row) {
        $mids[] = $row['mid'];
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
    $statement = $connection->prepare("UPDATE group_member SET isdeleted = 1 where mid=:id");
    $statement->bindParam(':id', $id);
    $statement->execute();
    $response = new Response(
      json_encode(array('message')),
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
    $tmp = $formbuilder->GenerateLayout('group_package');
    $em = $this->getDoctrine()->getEntityManager();
    $connection = $em->getConnection();
    $row = $connection->fetchAssoc('SELECT * FROM `groups` WHERE id=?', array($id));
    if (empty ($row)) throw $this->createNotFoundException('Không tìm thấy nhóm!');
    return $this->render('group/addpackage.html.twig', [
      'form' => $tmp,
      'row' => $row,
      'id' => $id,
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
