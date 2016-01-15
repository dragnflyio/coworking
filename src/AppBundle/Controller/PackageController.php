<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
		$formbuilder = $this->get('app.formbuilder');
		$grp = $this->getPackageSearchForm();
		
		$form = $formbuilder->GenerateManualSearchControls($grp);
		
        return $this->render('package/list.html.twig', [
			'form' => $form,
			'script' => $formbuilder->mscript
        ]);

    }
	private function getPackageSearchForm(){
		
		$retval = array();
		// ten app
		/*$row = array();
		$row['id'] = 'appname';
		$row['label'] = 'Tên App';
		$row['type'] = 'text_multi';
		$row['pos'] = array('row' => 1, 'col' => 1);
		$row['colname'] = 'applicationId';
		$row['pop'] = 'M';
		$row['ds'] = 'admin/cheatax/axhandler?op=lstapplk';
		$retval[] = $row;
		// partner
		$row = array();
		$row['id'] = 'partnername';
		$row['label'] = 'Partner';
		$row['type'] = 'text_multi';
		$row['pos'] = array('row' => 2, 'col' => 1);
		$row['colname'] = 'partnerId';
		$row['pop'] = 'M';
		$row['ds'] = 'admin/cheatax/axhandler?op=lstpartnerlk';
		$retval[] = $row; */
		// Ip
		$row = array();
		$row['id'] = 'ipinstall';
		$row['label'] = 'Ips';
		$row['type'] = 'text';
		$row['pos'] = array('row' => 3, 'col' => 1);
		$retval[] = $row;		
		// nguon ip
		$row = array();
		$row['id'] = 'ipsource';
		$row['label'] = 'Chung nguồn IP';
		$row['type'] = 'check';		
		$row['pos'] = array('row' => 3, 'col' => 2);
		$retval[] = $row;
		//ngay tao
        $row = array();
        $row['id'] = 'ngaytao';
        $row['label'] = 'Ngày tạo';
        $row['type'] = 'date';
        $row['colname'] = 'created';
        $row['pos'] = array('row' => 3, 'col' => 2);
        $retval[] = $row;
		
		// Campaign type, incentive/Nonincent
		$row = array();
		$row['id'] = 'incentive';
		$row['label'] = 'Campaign';
		$row['colname'] = 'type';
		$row['type'] = 'check';
		$arr = array(
            'sameline' => 1,
            'label' => array('Incentive', 'Nonincent'),
            'value' => array(1, 2)
        );
        $row['ds'] = json_encode($arr);
		$row['pos'] = array('row' => 6, 'col' => 1);
		$retval[] = $row;
		// Number of open app
		$row = array();
		$row['id'] = 'openapp';
		$row['type'] = 'numeric';
		$row['isNumeric'] = 1;
		$row['lblfrom'] = 'Lượt mở từ';
		$row['lblto'] = ' đến ';
		$row['pos'] = array('row' => 7, 'col' => 1);
		$row['colname'] = 'opentimes';
		$retval[] = $row;
		
		$row = array();
		$row['id'] = 'cvirate';
		$row['type'] = 'numeric';
		$row['isNumeric'] = 1;
		$row['lblfrom'] = 'Tỉ lệ cài đặt từ';
		$row['lblto'] = ' đến ';
		$row['pos'] = array('row' => 4, 'col' => 2);
		$row['suffix'] = '%';
		$row['colname'] = 'rate';
		$retval[] = $row;

		return $retval;
	}
	/**
	 * Where search for user search
	 */
	private function getWhereUserSearchCondition($searchData) {
		$formbuilder = $this->get('app.formbuilder');
        $where = '';
        foreach ($searchData as $data) {
            // normal fields - colname already there and differ from 'x'
            if ($data['colname'] != 'x') {
                $where .= $formbuilder->buildSingleCondition($data);
            } else {								
			}
        }
        return array('w' => $where);
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
			// Get current package by id
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
				$dataObj = $formbuilder->PrepareInsert($_POST, 'packageform');
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
					$dataObj = $formbuilder->PrepareInsert($_POST, 'packageform');
					foreach ($dataObj as $table => $postdata){
						if ($postdata){
							$data['v'] = $connection->update($table, $postdata, array('id' => $id));
						}
					}
					$data['m'] = 'Cập nhật thành công';
				}
				break;
			case 'testsearch':
				$grp = $this->getPackageSearchForm();
        		$data['d'] = $formbuilder->GetSearchData($_POST, $grp);
				$data['w'] = $this->getWhereUserSearchCondition($data['d']);
				
				break;
			case 'search':				
				$statement = $connection->prepare("SELECT * FROM package WHERE 1");
				$statement->execute();
				$all_rows = $statement->fetchAll();
				$ret = array();
				$idx = 0;
				if (empty($all_rows)){
					$data['empty'] = 'Không tìm thấy bản ghi nào';
				} else {
					foreach ($all_rows as $row){
						$tmp = array(
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
