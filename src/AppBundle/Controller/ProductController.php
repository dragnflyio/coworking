<?php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Utils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

class ProductController extends Controller
{
  protected $products;

  /**
   * @Route("/product", name="product")
   */
  public function indexAction(){
    $em = $this->getDoctrine()->getManager();
    $connection = $em->getConnection();
    $statement = $connection->prepare("SELECT * FROM product");
    $statement->execute();
    $rows = $statement->fetchAll();
    $products = array();
    foreach ($rows as $row) {
      $row['unitname'] = $this->getUnitNameById($row['unit']);
      $row['catname'] = $this->getCatNameById($row['category']);
      $products[] = (object) $row;
    }
    $formbuilder = $this->get('app.formbuilder');
    $tmp = $formbuilder->GenerateLayout('tk_product');
    return $this->render('product/index.html.twig', [
        'base_dir' => realpath($this->container->getParameter('kernel.root_dir').'/..'),
        'form' => $tmp,
        'script' => $formbuilder->mscript,
        'products' => $products,
    ]);
  }

  /**
   * @Route("product/add", name = "product_add")
   */
  public function addAction(){
    $formbuilder = $this->get('app.formbuilder');
    $tmp = $formbuilder->GenerateLayout('category');
    return $this->render('product/form.html.twig', [
        'base_dir' => realpath($this->container->getParameter('kernel.root_dir').'/..'),
        'form' => $tmp,
        'script' => $formbuilder->mscript
    ]);
  }

  /**
   * @Route("/product/import", name = "product_import")
   */
  public function importAction(){
    $products = array();
    return $this->render('product/import.html.twig', array(
      'products' => $products
    ));
  }

  /**
   * Preview products.
   */
  public function previewAction(Request $request){
    $products = $request->request->get('products');
    array_shift($products);
    foreach ($products as  $product) {
      $notes = array();
      $catname = $product->F;
      $unitname = $product->D;
      $catid = $this->getCidByName($catname);
      if (null == $catid) {
        $notes[] = 'Danh mục không tồn tại';
      } else {
        $product->catid = $catid;
      }
      $unitid = $this->getUnitIdByName($unitname);
      if (null == $unitid) {
        $notes[] = 'Đơn vị không tồn tại';
      } else {
        $product->unitid = $unitid;
      }
      $product->notes = implode('/', $notes);
    }
    $session = $request->getSession();
    $session->set('products', $products);
    return $this->render('product/preview.html.twig', array(
      'products' => $products
    ));
  }

  /**
   * @Route("/product/import-preview", name = "product_import_ajax")
   */
  public function ajaxAction(Request $request){
    $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject('product_price_export_11-45-13-01-16.xls');
    $data = $phpExcelObject->getActiveSheet()->toArray(null,true,true,true);
    $products = array();
    foreach ($data as $product) {
      $products[] = (object) $product;
    }
    $request->request->set('products', $products);
    $product_preview = $this->forward('AppBundle:Product:preview')->getContent();
    $json = json_encode($product_preview);
    $response = new Response($json, 200);
    $response->headers->set('Content-Type', 'application/json');
    return $response;
  }

  /**
   * @Route("/product/import-save", name = "product_import_save")
   */
  public function saveAction(Request $request) {
    $em = $this->getDoctrine()->getManager();
    $connection = $em->getConnection();
    $session = $request->getSession();
    $products = $session->get('products');
    foreach ($products as $product) {
      if (empty($product->notes)) {
        if ($this->checkProduct($product->A) == false) {
          $statement = $connection->prepare("INSERT INTO product (code, name_en, name_vi, unit, type, price, category, status)
          VALUES (:code, :name_en, :name_vi, :unit, :type, :price, :category, 1)");
          $statement->bindParam(':code', $product->A);
          $statement->bindParam(':name_vi', $product->B);
          $statement->bindParam(':name_en', $product->C);
          $statement->bindParam(':unit', $product->unitid);
          $statement->bindParam(':type', $product->E);
          $statement->bindParam(':price', $product->G);
          $statement->bindParam(':category', $product->catid);
          // $statement->bindParam(':status', 1);
          $statement->execute();
        }
        else {
          $statement = $connection->prepare("UPDATE product SET name_en = :name_en, name_vi = :name_vi, unit = :unit, type = :type, price = :price, category = :category, status = 1
          WHERE code = :code");
          $statement->bindParam(':code', $product->A);
          $statement->bindParam(':name_vi', $product->B);
          $statement->bindParam(':name_en', $product->C);
          $statement->bindParam(':unit', $product->unitid);
          $statement->bindParam(':type', $product->E);
          $statement->bindParam(':price', $product->G);
          $statement->bindParam(':category', $product->catid);
          $statement->execute();
        }
      }
    }
    $response = new Response($json, 200);
    $response->headers->set('Content-Type', 'application/json');
    return $response;
  }

  /**
   * Check product by code.
   *
   * @param string $code
   *
   * @return array $products
   */
  public function checkProduct($code){
    $em = $this->getDoctrine()->getEntityManager();
    $connection = $em->getConnection();
    $statement = $connection->prepare("SELECT * FROM product WHERE code = '$code'");
    $statement->execute();
    $rows = $statement->fetchAll();
    if (empty($rows)) {
      return false;
    }
    else {
      return true;
    }
  }

  /**
   * Get category id by name
   *
   * @param string $name
   *
   * @return int $int
   */
  public function getCidByName($name){
    $em = $this->getDoctrine()->getEntityManager();
    $connection = $em->getConnection();
    $statement = $connection->prepare("SELECT * FROM product_category WHERE name = :name");
    $statement->bindParam(':name', $name);
    $statement->execute();
    $row = $statement->fetchAll();
    if (!empty($row)){
      return $row[0]['id'];
    }
    else {
      return NULL;
    }
  }

  /**
   * Get unit id by name
   *
   * @param $name
   *
   * @return $int
   */
  public function getUnitIdByName($name){
    $em = $this->getDoctrine()->getEntityManager();
    $connection = $em->getConnection();
    $statement = $connection->prepare("SELECT * FROM unit WHERE namevi = :name OR nameen = :name");
    $statement->bindParam(':name', $name);
    $statement->execute();
    $row = $statement->fetchAll();
    if (!empty($row)){
      return $row[0]['id'];
    }
    else {
      return NULL;
    }
  }

  /**
   * Get unit name by id
   *
   * @param int $id
   *
   * @return string $name_vi
   */
  public function getUnitNameById($id){
    $em = $this->getDoctrine()->getEntityManager();
    $connection = $em->getConnection();
    $statement = $connection->prepare("SELECT * FROM unit WHERE id = :id");
    $statement->bindParam(':id', $id);
    $statement->execute();
    $row = $statement->fetchAll();
    if (!empty($row)){
      return $row[0]['namevi'];
    }
    else {
      return NULL;
    }
  }

  /**
   * Get category name by id
   *
   * @param int $id
   *
   * @return string $name_vi
   */
  public function getCatNameById($id){
    $em = $this->getDoctrine()->getEntityManager();
    $connection = $em->getConnection();
    $statement = $connection->prepare("SELECT * FROM product_category WHERE id = :id");
    $statement->bindParam(':id', $id);
    $statement->execute();
    $row = $statement->fetchAll();
    if (!empty($row)){
      return $row[0]['name'];
    }
    else {
      return NULL;
    }
  }
}
