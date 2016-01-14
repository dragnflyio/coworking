<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
// use AppBundle\Utils\helper_number;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
/**
 * @Route("/package")
 */
class PackageController extends Controller
{
	
	/**
     * @Route("/list", name="list_package")
	 * @Route("/", name="list_package")
     */
    public function listAction(Request $request){

        return $this->render('package/list.html.twig', [

        ]);

    }
    /**
     * @Route("/add", name="add_package")
     */
    public function addAction(Request $request){
		$formbuilder = $this->get('app.formbuilder');
		$tmp = $formbuilder->GenerateLayout('packageform');

        return $this->render('package/add.html.twig', [
			'form' => $tmp,
			'script' => $formbuilder->mscript
        ]);

    }
	/**
     * @Route("/edit/{id}", requirements={"id" = "\d+"})
     */
    public function editAction($id, Request $request){
		$formbuilder = $this->get('app.formbuilder');		
		$request = Request::createFromGlobals();

		$id = (int)$id;
		$tmp = '';
		if ($id){
			$em = $this->getDoctrine()->getEntityManager();
			$connection = $em->getConnection();
			$statement = $connection->prepare("SELECT * FROM package WHERE id = $id");
			$statement->execute();
			$row = $statement->fetchAll();
			if (empty ($row)) throw $this->createNotFoundException('Không tìm thấy gói dịch vụ này');
			$tmp = $formbuilder->LoadDatarowToConfig($row[0], 'packageform');
		} else {
			throw $this->createNotFoundException('Không tìm thấy trang này');
		}
		

        return $this->render('package/edit.html.twig', [
			'form' => $tmp,
			'script' => $formbuilder->mscript
        ]);

    }
	/**
	 * @Route("/formapi")
	 */
	public function formapiAction(Request $request){
		
		$request = Request::createFromGlobals();
		// Only process ajax request
		if (false == $request->isXmlHttpRequest()){
			// throw $this->createAccessDeniedException('Request forbidden');
			throw new HTTPException(403, 'Request forbidden');
		}
		// echo $request->getMethod();
		// echo "<br>";
		// echo $request->request->get('bar', 'default value');
		// echo "<br>";
		// echo $request->getPathInfo();
		// echo "<br>";
		$op = $request->query->get('op', 'insert');
		
		$formbuilder = $this->get('app.formbuilder');
		$dataObj = $formbuilder->PrepareInsert($_POST, 'packageform');
		$data = array();
		$response = new Response(
			json_encode($data),
			Response::HTTP_OK,
			array('content-type' => 'application/json')
		);
		return $response;
	}
}
