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
     * @Route("/form/index", name="testpage")
     */
    public function indexAction(Request $request){
        // replace this example code with whatever you need
		$formbuilder = $this->get('app.formbuilder');
		// var_dump($formbuilder->fetchQuery('SELECT * FROM ddfields WHERE 1'));
		$number = rand(0, 100);
        return $this->render('default/testform.html.twig', [
            'base_dir' => realpath($this->container->getParameter('kernel.root_dir').'/..'),
			'number' => $number
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
		var_dump($_POST);
		// $request = Request::createFromGlobals();
		echo $request->getMethod();
		echo "<br>";
		echo $request->request->get('bar', 'default value');
		echo "<br>";
		echo $request->getPathInfo();
		echo "<br>";
		// var_dump($request->server->get('HTTP_HOST'));
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
		$tmp = $formbuilder->GenerateLayout('obj1');

        return $this->render('default/testform.html.twig', [
            'base_dir' => realpath($this->container->getParameter('kernel.root_dir').'/..'),
			'form' => $tmp,
			'script' => $formbuilder->mscript
			
        ]);
    }

}
