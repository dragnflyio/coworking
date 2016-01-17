<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @Route("/customer")
 */
class CustomerController extends Controller{
	/**
     * @Route("/add", name="add_customer")
     */
    public function addAction(Request $request){
		$formbuilder = $this->get('app.formbuilder');
		$tmp = $formbuilder->GenerateLayout('customerform');

        return $this->render('customer/add.html.twig', [
			'form' => $tmp,
			'script' => $formbuilder->mscript
        ]);

    }
	/**
     * @Route("/list", name="list_customer")
	 * @Route("/", name="list_customer")
     */
    public function listAction(Request $request){
		$formbuilder = $this->get('app.formbuilder');
		$grp = $this->getPackageSearchForm();
		
		$form = $formbuilder->GenerateManualSearchControls($grp);
		
        return $this->render('package/list.html.twig', [
			'form' => $form,
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
			// Get current package by id
			$statement = $connection->prepare("SELECT * FROM member WHERE id = $id");
			$statement->execute();
			$row = $statement->fetchAll();
			if (empty ($row)) throw $this->createNotFoundException('Không tìm thấy khách hàng này');
			$tmp = $formbuilder->LoadDatarowToConfig($row[0], 'customerform');
		} else {
			throw $this->createNotFoundException('Không tìm thấy trang này');
		}
		

        return $this->render('customer/edit.html.twig', [
			'form' => $tmp,
			'id' => $id,
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

		$op = $request->query->get('op', 'create');
		
		$formbuilder = $this->get('app.formbuilder');
		
		$data = array();
		$em = $this->getDoctrine()->getEntityManager();
		$connection = $em->getConnection();
		
		switch($op){
			case 'create':
				$dataObj = $formbuilder->PrepareInsert($_POST, 'customerform');
				foreach ($dataObj as $table => $postdata){
					if ($postdata){
						$data['v'] = $connection->insert($table, $postdata);
					}
				}
				$data['m'] = 'Thêm thành công';
				break;
			case 'update':
				$id = $request->request->get('id', 0);
				$id = (int)$id;// force to int to prevent sql injection
				if ($id){
					$formbuilder->setUpdateMode(true);
					$dataObj = $formbuilder->PrepareInsert($_POST, 'customerform');
					foreach ($dataObj as $table => $postdata){
						if ($postdata){
							$data['v'] = $connection->update($table, $postdata, array('id' => $id));
						}
					}
					$data['m'] = 'Cập nhật thành công';
				}
				break;
			
			case 'deactivate':
				$id = $request->request->get('id', 0);
				if ($id < 1) throw $this->createAccessDeniedException('Invalid parameters');
				if ($id){
					$connection->update('customer',array('active' => 2), array('id' => $id));
					$data['m'] = 'Đã vô hiệu hoá khách hàng';
				}
				break;
			case 'search':
				$grp = $this->getPackageSearchForm();
				$searchData = $formbuilder->GetSearchData($_POST, $grp);
				$filters = $this->getWhereUserSearchCondition($searchData);
				$statement = $connection->prepare("SELECT * FROM customer WHERE 1=status $filters");
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
							'price' => $formbuilder->formatNum($row['price']),
							'description' => $row['description'],
							'createdname' => 'Admin'
						);
						$ret[] = $tmp;
					}
					$data = $ret;
				}				
				
				break;
			default:
				throw $this->createAccessDeniedException('Invalid operator');
		}
		
		$response = new Response(
			json_encode($data),
			Response::HTTP_OK,
			array('content-type' => 'application/json')
		);
		return $response;
	}
    
}
