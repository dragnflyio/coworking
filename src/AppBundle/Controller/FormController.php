<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use AppBundle\Utils;

class FormController extends Controller
{
  /**
  * @Route("/form/upload", name="form_upload")
  */
  public function uploadAction(Request $request){
    $objfile = $request->files->get('testfile');
    $data = array();
    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();

    if ($objfile){
      if (is_array($objfile)) {
        // mutiple files upload
        $data['ext'] = '';
        $data['valid'] = '';
        foreach ($objfile as $file){
          $data['ext'] .=';' . $file->guessExtension();
          $data['valid'] .=';'. $file->isValid();
          $filename = uniqid().'.'.$file->guessExtension();
          $file->move($this->container->getParameter('kernel.root_dir').'/../web/files', $filename );
          $data['url'] = $baseurl.'/files/'.$filename;
        }
      } else {
        $data['ext'] = $objfile->guessExtension();
        $data['valid'] = $objfile->isValid();
        // $filename = $objfile->getClientOriginalName();
        $filename = uniqid().'.'.$file->guessExtension();
        $objfile->move($this->container->getParameter('kernel.root_dir').'/../web/files', $filename );
        // $data['msg'] = $objfile->getErrorMessage();
      }


    }
    return new Response(
      json_encode($data),
      Response::HTTP_OK,
      array('content-type' => 'application/json')
      );
  }

  /**
  * @Route("/form/index", name="testpage")
  */
  public function indexAction(Request $request){
    // replace this example code with whatever you need
    $formbuilder = $this->get('app.formbuilder');
    // var_dump($formbuilder->fetchQuery('SELECT * FROM ddfields WHERE 1'));
    $number = rand(0, 100);
    return $this->render('default/testform.html.twig', [
      'base_dir' => realpath($this->container->getParameter('kernel.root_dir').'/../web'),

      'base_dir2' => realpath($this->container->getParameter('kernel.root_dir').'/..'),
      'form' => $number
      ]);
  }
  /**
  * @Route("/form/json")
  */
  public function jsonAction(Request $request){
    $em = $this->getDoctrine()->getEntityManager();
    $connection = $em->getConnection();
    $statement = $connection->prepare("SELECT * FROM ddfields WHERE 1");
    // $statement->bindValue('id', 123);
    $statement->execute();
    $results = $statement->fetchAll();
    $array = array(
      array(1, 'name 1'),
      array(2, 'name 3')
      );
    $response = new Response(
      json_encode($array),
      Response::HTTP_OK,
      array('content-type' => 'application/json')
      );


    return $response;
  }
  /**
  * @Route("/form/formapi")
  */
  public function formapiAction(Request $request){
    // var_dump($_POST);
    // $request = Request::createFromGlobals();
    // echo $request->getMethod();
    // echo "<br>";
    // echo $request->request->get('bar', 'default value');
    // echo "<br>";
    // echo $request->getPathInfo();
    // echo "<br>";
    $formbuilder = $this->get('app.formbuilder');
    $dataObj = $formbuilder->PrepareInsert($_POST, 'obj2');
    var_dump($dataObj);
    $response = new Response(
      '',
      Response::HTTP_OK,
      array('content-type' => 'text/html')
      );


    return $response;
  }
  /**
  * @Route("/form/newform")
  */
  public function newformAction(){
    $formbuilder = $this->get('app.formbuilder');
    $request = Request::createFromGlobals();
    $id = $request->query->get('id', 0);
    $id = (int)$id;
    if ($id){
      $em = $this->getDoctrine()->getEntityManager();
      $connection = $em->getConnection();
      $statement = $connection->prepare("SELECT * FROM newtable WHERE id = $id");
      $statement->execute();
      $row = $statement->fetchAll();
      $tmp = $formbuilder->LoadDatarowToConfig($row[0], 'obj2');
    }else {
      $tmp = $formbuilder->GenerateLayout('obj2');
    }

    return $this->render('default/testform.html.twig', [
      'base_dir' => realpath($this->container->getParameter('kernel.root_dir').'/..'),
      'form' => $tmp,
      'script' => $formbuilder->mscript

      ]);
  }

}