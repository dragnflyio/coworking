<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
/**
 * @Route("/customer")
 */
class CustomerController extends Controller
{
    /**
     * @Route("/add")
     */
    public function addAction(Request $request){
        // replace this example code with whatever you need
		$formbuilder = $this->get('app.formbuilder');		
		$request = Request::createFromGlobals();
		$id = $request->query->get('id', 0);
		$id = (int)$id;
		if ($id){
			$em = $this->getDoctrine()->getEntityManager();
			$connection = $em->getConnection();
			$statement = $connection->prepare("SELECT * FROM package WHERE id = $id");
			$statement->execute();
			$row = $statement->fetchAll();
			$tmp = $formbuilder->LoadDatarowToConfig($row[0], 'obj2');
		}else {
			$tmp = $formbuilder->GenerateLayout('obj2');
		}

        return $this->render('customer/add.html.twig', [
            'base_dir' => realpath($this->container->getParameter('kernel.root_dir').'/..'),
			'form' => $tmp,
			'script' => $formbuilder->mscript

        ]);

    }
}
