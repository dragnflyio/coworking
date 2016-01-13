<?php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Utils;
use Symfony\Component\HttpFoundation\Response;

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
      $products[] = (object) $row;
    }
    return $this->render('product/index.html.twig', array(
      'products' => $products
    ));
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

  public function previewAction(Request $request){
    $products = $request->request->get('products');
    return $this->render('product/preview.html.twig', array(
      'products' => $products
    ));
  }

  /**
   * @Route("/product/import-ajax", name = "product_import_ajax")
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
}
