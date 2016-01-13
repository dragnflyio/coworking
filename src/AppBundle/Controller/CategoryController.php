<?php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Utils;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends Controller
{

  /**
   * @Route("/category", name="category")
   */
  public function indexAction(){
    $em = $this->getDoctrine()->getManager();
    $connection = $em->getConnection();
    $statement = $connection->prepare("SELECT * FROM product_category");
    $statement->execute();
    $rows = $statement->fetchAll();
    $categories = array();
    foreach ($rows as $row) {
      $categories[] = (object) $row;
    }
    return $this->render('category/category.html.twig', array(
        'categories' => $categories
    ));
  }

  /**
   * @Route("/category/add", name = "category_add")
   */
  public function addAction(){
    $formbuilder = $this->get('app.formbuilder');
    $tmp = $formbuilder->GenerateLayout('category');
    return $this->render('category/form.html.twig', [
        'base_dir' => realpath($this->container->getParameter('kernel.root_dir').'/..'),
        'form' => $tmp,
        'script' => $formbuilder->mscript
    ]);
  }

  /**
   * @Route("/category/update", name = "category_update")
   */
  public function updateAction() {
    $formbuilder = $this->get('app.formbuilder');
    $request = Request::createFromGlobals();
    $id = $request->query->get('id', 0);
    $id = (int)$id;
    $em = $this->getDoctrine()->getEntityManager();
    $connection = $em->getConnection();
    $statement = $connection->prepare("SELECT * FROM product_category WHERE id = $id");
    $statement->execute();
    $row = $statement->fetchAll();
    $tmp = $formbuilder->LoadDatarowToConfig($row[0], 'category');
    return $this->render('category/form.html.twig', [
        'base_dir' => realpath($this->container->getParameter('kernel.root_dir').'/..'),
        'form' => $tmp,
        'script' => $formbuilder->mscript
    ]);
  }

  /**
   * @Route("/category/json", name = "category_json")
   */
  public function jsonAction(Request $request){
    $em = $this->getDoctrine()->getManager();
    $connection = $em->getConnection();
    $statement = $connection->prepare("SELECT * FROM product_category where parent_id IS NULL");
    $statement->execute();
    $rows = $statement->fetchAll();
    $categories = array();
    foreach ($rows as $value) {
      $categories[] = $value;
      $this->getChilds($categories, $value['id']);
    }

    $options = array();
    foreach ($categories as $category) {
      $options[] = array($category['id'], str_repeat('-', $category['depth']) . $category['name']);
    }

    $response = new Response(
      json_encode($options),
      Response::HTTP_OK,
      array('content-type' => 'application/json')
    );
    return $response;
  }

  /**
   * @Route("category/ajax-action", name = "category_ajax")
   */
  public function ajaxAction(){
    $parent_id = $_POST['parent_id'] != -1 ? $_POST['parent_id'] : null;
    $name = $_POST['name'];
    $code = $_POST['code'];
    if ($_POST['parent_id'] == -1) {
      $depth = 0;
    } else {
      $depth = $this->getDepth($_POST['parent_id']) + 1;
    }
    $weight = $this->getMaxWeight($parent_id) + 1;

    $em = $this->getDoctrine()->getManager();
    $connection = $em->getConnection();
    if (!empty($_POST['id'])) {
      $statement = $connection->prepare("UPDATE product_category SET parent_id=:parent_id, name=:name, code =:code, weight=:weight, depth=:depth where id =:id");
      $statement->bindParam(':id', $_POST['id']);
    }
    else {
      $statement = $connection->prepare("INSERT INTO product_category (parent_id, name, code, weight, depth) VALUES (:parent_id, :name, :code, :weight, :depth)");
    }
    $statement->bindParam(':parent_id', $parent_id);
    $statement->bindParam(':name', $name);
    $statement->bindParam(':code', $code);
    $statement->bindParam(':weight', $weight);
    $statement->bindParam(':depth', $depth);
    $statement->execute();
    $response = new Response(
      json_encode(array($name, $code)),
      Response::HTTP_OK,
      array('content-type' => 'application/json')
    );
    return $response;
  }

  /**
   * Get depth of category.
   *
   * @param $id
   *
   * @return int $depth
   */
  private function getDepth($id){
    $em = $this->getDoctrine()->getEntityManager();
    $connection = $em->getConnection();
    $statement = $connection->prepare("SELECT * FROM product_category WHERE id = $id");
    $statement->execute();
    $row = $statement->fetchAll();
    return $row[0]['depth'];
  }

  /**
   * Get maximum weight in a category
   *
   * @param int $id;
   *
   * @return decimal $weight
   */
  private function getMaxWeight($id){
    $em = $this->getDoctrine()->getEntityManager();
    $connection = $em->getConnection();
    if ($id != null) {
      $statement = $connection->prepare("SELECT MAX(weight) AS MaxWeight FROM product_category WHERE parent_id = $id ");
    }
    else {
      $statement = $connection->prepare("SELECT MAX(weight) AS MaxWeight FROM product_category WHERE parent_id IS NULL");
    }
    $statement->execute();
    $row = $statement->fetchAll();
    return $row[0]['MaxWeight'];
  }

  /**
   * Get category child by parent id
   * @param int $pid
   *
   * @return array $childs
   */
  private function getChilds(&$childs, $pid){
    $em = $this->getDoctrine()->getEntityManager();
    $connection = $em->getConnection();
    $statement = $connection->prepare("SELECT * FROM product_category WHERE parent_id = $pid");
    $statement->execute();
    $rows = $statement->fetchAll();
    foreach ($rows as $row) {
      $childs[] = $row;
      if ($this->hasChild($row['id'])) {
        $this->getChilds($childs, $row['id']);
      }
    }
  }

  /**
   * Check category has childs
   *
   * @param int $pid
   *
   * @return boolean $flag
   */
  private function hasChild($pid){
    $em = $this->getDoctrine()->getEntityManager();
    $connection = $em->getConnection();
    $statement = $connection->prepare("SELECT * FROM product_category WHERE parent_id = $pid");
    $statement->execute();
    $row = $statement->fetchAll();
    if (empty($row)) {
      $flag = FALSE;
    }
    else {
      $flag = TRUE;
    }
    return $flag;
  }
}
