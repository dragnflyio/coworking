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
    if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
      throw $this->createAccessDeniedException();
    }
		$formbuilder = $this->get('app.formbuilder');
		$tmp = $formbuilder->GenerateLayout('customerform');

    return $this->render('customer/add.html.twig', [
	    'form' => $tmp,
	    'script' => $formbuilder->mscript
    ]);
  }
  /**
   * Add customer and package
   * @Route("/addpackagecustomer", name="add_package_customer")
   */
  public function addpackagecustomerAction(){
    $formbuilder = $this->get('app.formbuilder');
    $validation = $this->get('app.validation');
    $em = $this->getDoctrine()->getEntityManager();
    $connection = $em->getConnection();
    
    $form_update = $formbuilder->GenerateLayout('customerform');
    $script_customer = $formbuilder->mscript;

    $form_package = $formbuilder->GenerateLayout('memberpackage','','package_');
    $script_package = $formbuilder->mscript;

    return $this->render('customer/addpackagecustomer.html.twig', [
      'form' => $form_update,
      'form_package' => $form_package,
      'script' => $script_customer,
      'script_package' => $script_package
    ]);
  }
	/**
   * @Route("/editpackage/{id}", name="edit_customer_package", requirements={"id" = "\d+"})
   */
	public function editpackageAction($id){
    if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
      throw $this->createAccessDeniedException();
    }
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
    if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
      throw $this->createAccessDeniedException();
    }
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
  //public function
	/**
   * @Route("/list", name="list_customer")
	 * @Route("/", name="list_customer")
   */
  public function listAction(Request $request){
    if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
      throw $this->createAccessDeniedException();
    }
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
		$row['pos'] = array('row' => 1, 'col' => 2);
		$retval[] = $row;

		/*$row = array();
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
		$retval[] = $row;*/
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
    $user = $this->getUser();
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
          /*$effto = (int) ($newdate-86400);*/
          $statement = $connection->prepare("UPDATE `member_package` SET active=0 WHERE active = 1 AND memberid=:memberid");
          // $statement->bindParam(':effto', $effto);
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
                $connection->insert($table, $postdata);
                $data['v'] = $connection->lastInsertId();
                // TODO: Update ALL check in but not check out session to new package
                $validation->updateSessionNewPackage($current_package['id'], $data['v']);
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
        // TODO check if this customer has package?
        // or belong to a group which has package
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
                // Update customer activity
                $log = array(
                  'memberid' => $customerid,
                  'code' => 'taomoi',
                  'oldvalue' => NULL,
                  'newvalue' => $postdata['packageid'],
                  'createdtime' => time(),
                  'amount' => NULL,
                );
                $connection->insert('customer_activity', $log);
              }
            }
            $data['m'] = 'Thêm thành công';
          }

        }

				break;
      case 'cus_activity':
        $validation = $this->get('app.validation');
        $customerid = $request->query->get('id', 0);

        $data = $validation->getMemberActivities($customerid);
      break;
      case 'addpackagecustomer':
        // insert customer and package
        $customerid = 0;
        $dataCustomer = $formbuilder->PrepareInsert($_POST, 'customerform');

        $dataPackage = $formbuilder->PrepareInsert($_POST, 'memberpackage', 'package_', true);

        foreach ($dataCustomer as $table => $postdata){
          if ($postdata){
            // insert customer first
            $connection->insert($table, $postdata);
            $customerid = $connection->lastInsertId();
            // then insert member package
            if ($dataPackage['member_package']){
              $dataPackage['member_package']['memberid'] = $customerid;
            }
            $connection->insert('member_package', $dataPackage['member_package']);
            // Update customer activity
            $log = array(
              'memberid' => $customerid,
              'code' => 'taomoi',
              'oldvalue' => NULL,
              'newvalue' => $dataPackage['member_package']['packageid'],
              'createdtime' => time(),
              'amount' => NULL,
            );
            $connection->insert('customer_activity', $log);
          }
        }
        $data['m'] = 'Thêm thành công';

      break;
			case 'create':
				$dataObj = $formbuilder->PrepareInsert($_POST, 'customerform');
				foreach ($dataObj as $table => $postdata){
					if ($postdata){
            $postdata['createdby'] = $user->getId();
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
              $postdata['updatedby'] = $user->getId();
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
        $services = $this->get('app.services');
        $validation = $this->get('app.validation');
				$grp = $this->getCustomerSearchForm();
				$searchData = $formbuilder->GetSearchData($_POST, $grp);
				$filters = $this->getWhereUserSearchCondition($searchData);
				$statement = $connection->prepare("SELECT * FROM member WHERE 1 $filters");
				$statement->execute();
				$all_rows = $statement->fetchAll();
				$ret = array();
				$idx = 0;
				if (empty($all_rows)){
					$data['empty'] = 'Không tìm thấy bản ghi nào';
				} else {
					foreach ($all_rows as $row){
            $region = $services->getRegionbyId($row['regionid']);
            $region_name = (!empty($region) ? $region['name'] : '');
            $current_package = $validation->getMemberPackage($row['id']);
            $usage = '';
            if ($current_package){
              $usage = $current_package['packagename'];
              $usage .= '<br>'.date('d/M/Y', $current_package['efffrom']);
              $usage .= ' - '.date('d/M/Y', $current_package['effto']);
            }
						$tmp = array(
							'id' => $row['id'],
							'idx' => ++$idx,
							'name' => $row['name'],
              'company' => $row['company'],
              'job' => $row['job'],
              'postaladdress' => $row['postaladdress'],
              'region' => $region_name,
							'email' => $row['email'],
							'description' => $row['phone'],
							'createdname' => 'Admin',
							'package' => $usage,
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

  /**
   * @Route("/json", name = "member_json")
   */
  public function jsonAction(Request $request){
    $em = $this->getDoctrine()->getManager();
    $connection = $em->getConnection();
    $txt_search = $request->query->get('search', '');

    if (empty($txt_search)) {
      $statement = $connection->prepare("SELECT * FROM member where active = 1 LIMIT 30");
    } else {
      $statement = $connection->prepare("SELECT * FROM member where active = 1 and name LIKE :name LIMIT 30");
      $txt_search = "%" . $txt_search . "%";
      $statement->bindParam(':name', $txt_search);
    }
    $statement->execute();
    $rows = $statement->fetchAll();

    // Statement with members table.
    $members = array();
    foreach ($rows as $member) {
      $members[] = array($member['id'], $member['name']);
    }

    $response = new Response(
      json_encode($members),
      Response::HTTP_OK,
      array('content-type' => 'application/json')
    );
    return $response;
  }

}
