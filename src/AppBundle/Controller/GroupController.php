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
    return $this->render('group/index.html.twig', []);
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
    $statement = $connection->prepare("SELECT * FROM `group` WHERE id = $gid");
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
      $statement = $connection->prepare("UPDATE `group`
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
      $statement = $connection->prepare("INSERT INTO `group` (name, address, phone, taxcode, taxaddress, description, members)
      VALUES (:name, :address, :phone, :taxcode, :taxaddress, :description, :members)");
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
}
