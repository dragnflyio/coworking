<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use AppBundle\Utils\Validation;

/**
 * @Route("/customer")
 */
class CustomerController extends BaseController{
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
     * @Route("/editpackage/{id}", name="edit_customer_package", requirements={"id" = "\d+"})
     */
	public function editpackageAction($id){
		$formbuilder = $this->get('app.formbuilder');
		$validation = $this->get('app.validation');
		$em = $this->getDoctrine()->getEntityManager();
		$connection = $em->getConnection();
		$row = $connection->fetchAssoc('SELECT name FROM member WHERE id=?', array($id));
		if (empty ($row)) throw $this->createNotFoundException('Không tìm thấy khách hàng');
		// get active package?
		$current_package = $validation->getMemberPackage($id);
		if (empty ($current_package)) throw $this->createNotFoundException('Khách hàng hiện không dùng dịch vụ, bạn cần thêm gói dịch vụ trước');
		$error = false;
		$form_update = $formbuilder->GenerateLayout('memberpackage', "col_name NOT IN ('packageid','efffrom', 'effto')");
		$script_update = $formbuilder->mscript;

		$form_change = $formbuilder->GenerateLayout('memberpackage','','change_');
		$script_change = $formbuilder->mscript;

        return $this->render('customer/editpackage.html.twig', [
			'error' => $error,
			'form' => $form_update,
			'form_change' => $form_change,
			'row' => $row,
			'package' => $current_package,
			'memberid' => $id,
			'script' => $script_update,
			'script_change' => $script_change
        ]);
	}
	/**
     * @Route("/addpackage/{id}", name="add_customer_package", requirements={"id" = "\d+"})
     */
	public function addpackageAction($id){
		$formbuilder = $this->get('app.formbuilder');
		$validation = $this->get('app.validation');
		$em = $this->getDoctrine()->getEntityManager();
		$connection = $em->getConnection();
		$row = $connection->fetchAssoc('SELECT name FROM member WHERE id=?', array($id));
		if (empty ($row)) throw $this->createNotFoundException('Không tìm thấy khách hàng');
		$error = false;
		if ($msg = $validation->checkMemberPackage($id)){
			$tmp = $msg;
			$error = true;
		} else {
			$tmp = $formbuilder->GenerateLayout('memberpackage');
		}

        return $this->render('customer/addpackage.html.twig', [
			'error' => $error,
			'form' => $tmp,
			'row' => $row,
			'id' => $id,
			'script' => $formbuilder->mscript
        ]);
	}
	/**
     * @Route("/getpackages")
     */
	public function getpackagesAction(){
		$em = $this->getDoctrine()->getEntityManager();
		$connection = $em->getConnection();

		$results = $connection->fetchAll("SELECT id, name FROM package WHERE 1");
		$data = array();
		foreach ($results as $row) $data[] = array($row['id'], $row['name']);

		$response = new Response(
			json_encode($data),
			Response::HTTP_OK,
			array('content-type' => 'application/json')
	   	);


		return $response;
	}
	/**
     * @Route("/list", name="list_customer")
	 * @Route("/", name="list_customer")
     */
    public function listAction(Request $request){
		$formbuilder = $this->get('app.formbuilder');
		$grp = $this->getCustomerSearchForm();

		$form = $formbuilder->GenerateManualSearchControls($grp);

        return $this->render('customer/list.html.twig', [
			'form' => $form,
			'script' => $formbuilder->mscript
        ]);

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
				// do yourself
			}
        }
        return $where;
    }
	private function getCustomerSearchForm(){
		$retval = array();
		$row = array();
		$row['id'] = 'packagename';
		$row['label'] = 'Tên khách';
		$row['type'] = 'text';
		$row['colname'] = 'name';
		$row['pos'] = array('row' => 1, 'col' => 1);
		$retval[] = $row;
		$row = array();
		$row['id'] = 'email';
		$row['label'] = 'Email';
		$row['type'] = 'text';
		$row['colname'] = 'email';
		$row['pos'] = array('row' => 2, 'col' => 1);
		$retval[] = $row;

		$row = array();
		$row['id'] = 'trangthai';
		$row['label'] = 'Trạng thái';
		$row['colname'] = 'active';
		$row['type'] = 'check';
		$arr = array(
            'sameline' => 1,
            'label' => array('Hoạt động', 'Ngừng hoạt động'),
            'value' => array(1, 2)
        );
        $row['ds'] = json_encode($arr);
		$row['pos'] = array('row' => 1, 'col' => 2);
		$retval[] = $row;
		return $retval;
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
			if ($row = $connection->fetchAssoc("SELECT * FROM member WHERE id = $id")){
				$tmp = $formbuilder->LoadDatarowToConfig($row, 'customerform');
			} else throw $this->createNotFoundException('Không tìm thấy khách hàng này');


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
		    case 'extendfee':
		        $memberid = $request->query->get('id', 0);
		        $newdate = $request->query->get('d');
		        if ($memberid){
		            $validation = $this->get('app.validation');
		            if ($current_package = $validation->getMemberPackage($memberid)){
		                $extend_days = ((int)$newdate - (int)$current_package['effto'])/86400;
		                // rate per day
		                $day_price = 0;
		                if ($current_package['maxdays'])
		                    $day_price = (int)($current_package['price'] / $current_package['maxdays']);
		                $data['v'] = $extend_days * $day_price;
		            }

		        }
		        break;
			case 'memberpackage':
				$action = $request->query->get('action');
				$memberid = $request->query->get('id', 0);
				$data['m'] = $action;
				if('extend' == $action){

		            $newdate = $request->request->get('effto_extend');
		            $amount = $formbuilder->getNum($request->request->get('price_extend'));
		            $validation = $this->get('app.validation');
		            $validation->extendMemberPackage($memberid, $newdate, $amount);
		            $data['m'] = 'Đã cập nhật gia hạn tạm thời';
				}
        // Gia han goi
				if ('renew' == $action){
          $memberid = $request->query->get('id', 0);
          $validation = $this->get('app.validation');
          $services = $this->get('app.services');
          // Get current member_package
          $statement = $connection->prepare("SELECT * FROM `member_package` WHERE memberid=:memberid AND active=1");
          $statement->bindParam(':memberid', $memberid);
          $statement->execute();
          $rows = $statement->fetchAll();
				  $member_package = $rows[0];
          $package = $validation->getMemberPackage($memberid);
          // Close current package
          $newdate = $_POST['efffrom_renew'];
          $effto = (int) ($newdate-86400);
          $statement = $connection->prepare("UPDATE `member_package` SET active=0, effto=:effto WHERE active = 1 AND memberid=:memberid");
          $statement->bindParam(':effto', $effto);
          $statement->bindParam(':memberid', $memberid);
          $statement->execute();
          // Add new package
          $member_package['efffrom'] = $_POST['efffrom_renew'];
          $member_package['effto'] = $_POST['effto_renew'];
          unset($member_package['id']);
          $connection->insert('member_package', $member_package);
          // Update customer activity
          $log = array(
            'memberid' => $memberid,
            'code' => 'giahan',
            'oldvalue' => $package['packagename'],
            'newvalue' => $package['packagename'],
            'createdtime' => time(),
            'amount' => NULL,
          );
          $connection->insert('customer_activity', $log);
				}
				if ('change' == $action){
				    // Doi goi
				    if ($memberid){
				        $validation = $this->get('app.validation');
				        $dataObj = $formbuilder->PrepareInsert($_POST, 'memberpackage', 'change_');
                        $current_package = $validation->getMemberPackage($memberid);
    					foreach ($dataObj as $table => $postdata){
    						if ($postdata){
    						    // Disable current package
                                $validation->closedMemberPackage($memberid);
    							$postdata['memberid'] = $memberid;
    							$data['v'] = $connection->insert($table, $postdata);
    							// Log activity
                    	        $log = array(
                    	           'memberid' => $memberid,
                    	           'code' => 'changepackage',
                    	           'oldvalue' => $current_package['packageid'],
                    	           'newvalue' => $postdata['packageid'],
                    	           'createdtime' => time(),
                    	           'amount' => (int)$current_package['remain']
                    	        );
                    	        $data['v'] = $connection->insert('customer_activity', $log);
    						}
    					}
    					$data['m'] = 'Đổi gói thành công';
				    }
				}
				break;
			case 'addpackage':
				$customerid = $request->query->get('id', 0);
				// TODO check if this customer added package?
				// or belong to a group which added package
				// if not, do add package
				if ($customerid){
				    $validation = $this->get('app.validation');
				    if ($msg = $validation->checkMemberPackage($customerid)){
				        $data['e'] = $msg;
			        } else {
			            $dataObj = $formbuilder->PrepareInsert($_POST, 'memberpackage');
    					foreach ($dataObj as $table => $postdata){
    						if ($postdata){
    						    // Disable current package
                                // $validation = $this->get('app.validation');
                                // $validation->closedMemberPackage($customerid);
    							$postdata['memberid'] = $customerid;
    							$data['v'] = $connection->insert($table, $postdata);
    						}
    					}
    					$data['m'] = 'Thêm thành công';
			        }

				}

				break;
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
				$grp = $this->getCustomerSearchForm();
				$searchData = $formbuilder->GetSearchData($_POST, $grp);
				$filters = $this->getWhereUserSearchCondition($searchData);
				$statement = $connection->prepare("SELECT m.*, p.name AS packagename FROM member m LEFT JOIN member_package mp ON m.id = mp.memberid LEFT JOIN package p ON p.id = mp.packageid WHERE 1 $filters");
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
							'price' => $row['email'],
							'description' => $row['phone'],
							'createdname' => 'Admin',
							'package' => $row['packagename']
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
