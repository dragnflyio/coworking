<?php
namespace AppBundle\Utils;
use Symfony\Component\Config\Definition\Exception\Exception;
// require 'Number.php';
// require 'Cleanstring.php';
define('APP_URL','/');


/**
 * helper for generate template
 * @author Nguyen Viet Cuong <vietcuonghn3@gmail.com>
 */
class Formbuilder {
	/** Original Textbox */
    const TEXT = 1;
    /** Original Textarea */
    const TEXTAREA = 2;
    /** Original Checkbox */
    const CHECK = 3;
    /** Original dropdown list */
    const SELECT = 4;
    /** Original radio button */
    const RADIO = 5;
    /** Textbox support automplete and multi select */
    const TEXT_MULTI = 6;
    /** File upload control */
    const FILE = 7;
    /** Datetime picker */
    const DATETIME = 8;
    /** System field with current datetime for create only */
    const CURR_DATE_CREATE = 9;
    /** System field with current datetime */
    const CURR_DATE = 10;
    /** System field with current login user */
    const CURR_USER = 11;
    /** System field with current login user for create only */
    const CURR_USER_CREATE = 12;
    /**
     * current date time show date + time vs current date show only date
     */
    const CURR_DATETIME = 13;
    const BIRTHDAY = 14;
    /** Hidden field for special uses */
    const HIDDEN = 15;
	/* So dien thoai */
	const MOBI = 16;
	
    /**
     * scripts code must be insert at the end of body
     * @return String
     */
    public $mscript = '';
    private $_mModuleName = '';//physical module folder name
    private $_mcallib = false;
    private $_mtextboxlib = false;
    private $_mConfigList = null;
    private $_mupdateMode = false;

    private $_donviFieldName = null; //Field code don vi
    private $_nqlFieldName = null; //Field code nguoi quan ly
    private $_attributes = null; //current build row attributes
    private $_noval = '-1'; //value for "NO CHOICE" select list
    private $_prefixID = '';
    private $_mloadLib = true;
    private $_mprefillData = null; //Prefill data (array(col_code=>value))
    private $_mCRMListByType = null; //lookup up crmlist by list type

	private $_mClassIns = null;
	private $_mainID = 0;

	private $_activeFieldIds = null;
	private $_lkDatasource = null; // Lookup data source by col_name
	private $_lklkData = null;// lookup lookup array by col_name
	private $_keydecode = null;
	private $em;// doctrine entity manager
	
    function __construct($em) {
        // parent::__construct();
		$this->em = $em;
    }

    
	private function getClassIns($className){
		if(null == $this->_mClassIns) $this->_mClassIns = array();
		$x = $this->_mClassIns;
		if(isset($x[$className])) {
			return $x[$className];
		}
		$x[$className] = new $className();
		$this->_mClassIns = $x;
		return $x[$className];
	}
	//Invoke data source method
	private function invokeMethod($ds,$field = null,$table = null){
		//classname.method
		// $ds = str_replace('..','.',$datasource);
		$arr = explode('.', $ds);
		$cl = $this->getClassIns($arr[0]);
		if($field) return $cl->$arr[1]($this->_mainID,$field,$table);
		else return $cl->$arr[1]($this->_mainID);
	}

    /* set prefill values */
    public function setPrefilldata($data) {
        $this->_mprefillData = $data;
    }

    /** Set update mode for render data
     * */
    public function setUpdateMode($update = true, $id = 0) {
        $this->_mupdateMode = $update;
		$this->_mainID = $id;
    }

	/** for custom fields
	*/
	function lookupDatasource($objname, $colname, &$inlist){
		//Get data source?
		if(null === $this->_lkDatasource){
			$dtsource = array();
			$query = 'SELECT '.$this->getColName('col_name').','.$this->getColName('data_source').','.$this->getColName('data_type').' FROM '.$this->getTblName('table_conf').' WHERE 1 AND ';
			if(is_string($objname)) $query.= $this->getColName('object_name').'=\''.$objname.'\'';
			if(is_numeric($objname)) $query.= $this->getColName('module').'='.$objname;
			$res = $this->db->queryF($query);
			while($row = $this->fetchBoth($res)){
				if(empty($row[1])) continue;
				if((false !== stripos('SELECT',$row[2])) || (0 == strcasecmp('TEXT_MULTI_NEW',$row[2])) || (0 == strcasecmp('TEXT_MULTI',$row[2])) || (false !== stripos('RADIO',$row[2]))){   
					if(empty($dtsource[$row[0]])) $dtsource[$row[0]] = $row[1];					
				}
			}
			$this->_lkDatasource = $dtsource;	
			$this->_lklkData = array();			
		}
		$inlist = false;
		// ten cot nguoi dung them tu sinh
		// ten_bang.ten_cot
		if(false !== strpos('.', $colname)){
			$arr = explode('.', $colname);
			$colname = $arr[1];
		}
		if(false == isset($this->_lkDatasource[$colname])) return null;

		if(array_key_exists($colname, $this->_lklkData)){
			// already get data lookup
			$ds = $this->_lklkData[$colname];
			if(!$ds) return null;
			if(is_array($ds)){
				return $ds;
			} else {
				$inlist = true;
				return null;
			}
		}
		$datasource = $this->_lkDatasource[$colname];
		$jsonObj = json_decode($datasource);
		if (isset($jsonObj->value) || isset($jsonObj->label)) {
			if (isset($jsonObj->value)) {
				$valueArr = $jsonObj->value;
			}
			if (isset($jsonObj->label)) {
				$labelArr = $jsonObj->label;
			}
			$tmp = array();
			foreach($labelArr as $k => $v){
				$tmp[$valueArr[$k]] = $v;
			}
			$this->_lklkData[$colname] = $tmp;
			return $tmp;
		} else if (false !== strpos($datasource, '.')){
			// $tmp = $this->invokeMethod($datasource);
			$this->_lklkData[$colname] = null;
			// return $tmp;
			return null;
		} else if (false === strpos($datasource, '/')) {
			//JUST for crm_list table.
			$this->_lklkData[$colname] = 'crmlist';
			$inlist = true;
			return null;
		}
		$this->_lklkData[$colname] = null;
		return null;
	}
	
	/** Prepare data for insert/update, return array of array table data, key is table, value is array of table's columns data
     * @param Array $postData array of posting data, it the almost cases it should be $_POST
     * @param String $obj_name object_name in table_conf
     * @return Array
     * */
    public function PrepareInsert($postData, $obj_name) {
        $retVal = array();
        if (null == $this->_mConfigList) {
            $this->_mConfigList = $this->getTableConfig($obj_name, null, false, '', 1);
        }
        foreach ($this->_mConfigList as $config) {
			if(empty($config['column']) || empty($config['table'])) continue;
            if (false == isset($retVal[$config['table']]))
                $retVal[$config['table']] = array();
            $type = $config['inputType'];
            $isNumeric = 0;$isMobi = 0;
            if (isset($config['options']['isNumeric']))
                $isNumeric = $config['options']['isNumeric'];
			if (isset($config['options']['class']))
                $isMobi = 'mobif' == $config['options']['class'];
            switch ($type) {
				case Self::BIRTHDAY:
					// date
					if(isset($postData['d'.$config['id']]))
					$retVal[$config['table']][$config['column'] . '_date'] = $postData['d'.$config['id']];
					// month
					if(isset($postData['m'.$config['id']]))
						$retVal[$config['table']][$config['column'] . '_month'] = $postData['m'.$config['id']];
					// year
					if(isset($postData['y'.$config['id']]))
						$retVal[$config['table']][$config['column'] . '_year'] = $postData['y'.$config['id']];
					break;
                case Self::CURR_DATE:
                    $retVal[$config['table']][strtolower($config['column'])] = date('Y-m-d H:i:s', $this->getCurrentTimestamp());
                    break;
                case Self::CURR_DATE_CREATE:
                    //Ignore this column in update
                    if (false == $this->_mupdateMode) {
                        $retVal[$config['table']][strtolower($config['column'])] = date('Y-m-d H:i:s', $this->getCurrentTimestamp());
                    } else {
                        //remove from update list
                        unset($retVal[$config['table']][strtolower($config['column'])]);
                    }
                    break;
                case Self::CURR_USER_CREATE:
                    //Ignore this column in update
                    if (false == $this->_mupdateMode) {
                        $retVal[$config['table']][strtolower($config['column'])] = $this->getCurUid();
                    } else {
                        //remove from update list
                        unset($retVal[$config['table']][strtolower($config['column'])]);
                    }
                    break;
                case Self::CURR_USER:
                    $retVal[$config['table']][strtolower($config['column'])] = $this->getCurUid();
                    break;
                case Self::DATETIME:
                    if (isset($postData[$config['id']])) {
                        if ($config['options']['showTime']) {
                            $retVal[$config['table']][strtolower($config['column'])] = $postData[$config['id']] ? date('Y-m-d H:i:s', (int) $postData[$config['id']]) : null;
                        }
                        else
                            $retVal[$config['table']][strtolower($config['column'])] = $postData[$config['id']] ? date('Y-m-d', (int) $postData[$config['id']]) : null;
                    }
                    break;
                case Self::SELECT:
                    if (false == isset($postData[$config['id']]))
                        break;
                    $isMultiple = 0;
                    $v = $postData[$config['id']];

                    if (isset($config['options']['multiple']))
                        $isMultiple = $config['options']['multiple'];
                    if ($isMultiple) {
                        //array
                        $v = implode(',', $v);
                    } else {
                        if ($v == $this->_noval)
                            $v = null;
                    }
                    $retVal[$config['table']][strtolower($config['column'])] = $v;
                    break;
                case Self::CHECK:
                    if (isset($postData[$config['id']])) {
                        $v = $postData[$config['id']];
                        $retVal[$config['table']][strtolower($config['column'])] = $v;
                    }
                    else
                        $retVal[$config['table']][strtolower($config['column'])] = 0;
                    break;
				case Self::TEXT_MULTI:
						if(!array_key_exists($config['id'], $postData)) break;
						$v = $postData[$config['id']];
						if($v && $isMobi) $v = Gen_Library_Helper_Number::getPhone($v);
						$retVal[$config['table']][strtolower($config['column'])] = $v ? $v : null;
					break;
                default:
                    if (isset($postData[$config['id']])) {
                        $v = $postData[$config['id']];
                        if ($isNumeric){
                            $v = $this->getNum($v);
						}
                        $retVal[$config['table']][strtolower($config['column'])] = $v;
                    }
                    break;
            }
        }

        return $retVal;
    }
    
    /** get data source option for select, radio, checkbox
     * */
    private function getOptionDatasource($datasource, &$options = null, &$default = null,&$arrtmp = null){
        if (trim($datasource)){
            $jsonObj = json_decode($datasource);
            if (isset($jsonObj->value) || isset($jsonObj->label)) {
				$xoptions = array();
                if (isset($jsonObj->sameline) && $jsonObj->sameline) {
                    $xoptions['inline'] = true;
                }
                if (isset($jsonObj->value)) {
                    $xoptions['valueArr'] = $jsonObj->value;
                }
                if (isset($jsonObj->label)) {
                    $xoptions['labelArr'] = $jsonObj->label;
                }
                if (isset($xoptions['valueArr']) && false == isset($xoptions['labelArr'])) {
                    $xoptions['labelArr'] = $xoptions['valueArr'];
                }
                if (isset($xoptions['labelArr']) && false == isset($xoptions['valueArr'])) {
                    $xoptions['valueArr'] = $xoptions['labelArr'];
                }
				$tmp = array();
				for($ii = 0;$ii<count($xoptions['valueArr']); $ii++){
					$tmp[] = array($xoptions['valueArr'][$ii],$xoptions['labelArr'][$ii]);
				}
				if(is_array($options)){
					if(isset($xoptions['inline'])) $options['inline'] = $xoptions['inline'];
					$options['valueArr'] = $xoptions['valueArr'];
					$options['labelArr'] = $xoptions['labelArr'];
				}
				return $tmp;
            } else if (false !== strpos($datasource, '.')){
				if(isset($arrtmp['nguon'])) {
					$tmp = $this->invokeMethod($datasource,$arrtmp['field'],$arrtmp['table']);
				}
				else $tmp = $this->invokeMethod($datasource);
				if(is_array($options)){
					$labelArr = array();
					$valueArr = array();
					foreach ($tmp as $vvv) {
						$valueArr[] = $vvv[0];
						$labelArr[] = $vvv[1];
					}
					$options['valueArr'] = $valueArr;
					$options['labelArr'] = $labelArr;
				}
				return $tmp;
			} else if (false === strpos($datasource, '/')) {
                //JUST for crm_list table.
                $labelArr = array();
                $valueArr = array();
                $tmp = $this->getByListType($datasource, false, $default);
				if(is_array($options)){
					foreach ($tmp as $vvv) {
						$valueArr[] = $vvv[0];
						$labelArr[] = $vvv[1];
					}
					$options['valueArr'] = $valueArr;
					$options['labelArr'] = $labelArr;
				}
				return $tmp;
            } else {
                //load from action
                if(is_array($options)) $options['url'] = $datasource;
				return $datasource;
            }
        }
        return null;
    }
    /* Get control layout search form object from manual data
     * */
    private function GetConfigObjFromManualSearchRow($row) {
        $retVal = $row;
        $options = array();
		if(isset($row['defaultValue']))
		$options['defaultValue'] = $row['defaultValue'];
		if(isset($row['from']))
		$options['from'] = $row['from'];
		if(isset($row['to']))
		$options['to'] = $row['to'];
		if(isset($row['day']))
		$options['day'] = $row['day'];
		if(isset($row['month']))
		$options['month'] = $row['month'];
		if(isset($row['year']))
		$options['year'] = $row['year'];
        if (false == isset($row['label']))
            $retVal['label'] = '';
        if (isset($row['isNumeric']))
            $options['isNumeric'] = $row['isNumeric'];
        if (isset($row['lblfrom']))
            $options['lblfrom'] = $row['lblfrom'];
        if (isset($row['lblto']))
            $options['lblto'] = $row['lblto'];
        if (isset($row['zero']))
            $options['zero'] = (int) $row['zero'];
        if (isset($row['suffix']))
            $options['suffix'] = $row['suffix'];
        if (isset($row['pop']))
            $options['pop'] = $row['pop']; //instant popup search
        $retVal['data_source'] = null;
		if (isset($row['ds']))
            $retVal['data_source'] = $row['ds'];
		$upperINP = strtoupper($retVal['type']);
        switch ($upperINP) {
            case 'TEXT':
                $retVal['inputType'] = Self::TEXT;
                break;
            case 'CHECK':
                $retVal['inputType'] = Self::CHECK;
                //Fetch array value/label
                $this->getOptionDatasource($retVal['data_source'], $options);
                break;
            case 'SELECT':
                $retVal['inputType'] = Self::SELECT;
                $this->getOptionDatasource($retVal['data_source'], $options);                
                $options['multiple'] = isset($retVal['multiple']) ? $retVal['multiple'] : 0;
                break;
            case 'RADIO':
                $retVal['inputType'] = Self::RADIO;
                $this->getOptionDatasource($retVal['data_source'], $options);                
                break;
            case 'TEXT_MULTI':
                $retVal['inputType'] = Self::TEXT_MULTI;
                $options['enterNew'] = false;
                $options['url'] = $this->getOptionDatasource($retVal['data_source']);
                break;
            case 'TEXT_MULTI_NEW':	
			case 'MOBI':
                $retVal['inputType'] = Self::TEXT_MULTI;
				if($upperINP == 'MOBI') $options['class'] = 'mobif';
                $options['enterNew'] = true;
                $options['url'] = $this->getOptionDatasource($retVal['data_source']);
                break;
            case 'NUMERIC':
                $retVal['inputType'] = Self::TEXT;
                $options['isNumeric'] = true;
                break;
            case 'DATE':
                $retVal['inputType'] = Self::DATETIME;
                $options['showTime'] = false;
                break;
            case 'BIRTHDAY':
                $retVal['inputType'] = Self::BIRTHDAY;
                break;
            default:
                throw new Exception('Invalid search data type.'.$upperINP);
        }

        if (isset($retVal['maxlength']) && $retVal['maxlength'])
            $options['maxlength'] = (int) $retVal['maxlength'];

        $retVal['options'] = $options;
        return $retVal;
    }

    /* Get current date without dst */
    private function getCurrentTimestamp($strYMD = null) {
		return AppBundle\Utils\number::DateToTimestamp($strYMD);
    }
	
	/** Tim kiem trong bang voi dieu kien build tu refine */
	function SearchInTable($tbl, $arrWhere, $colget = 'id'){
		$limit = 500;
		$sql = 'SELECT '.$colget.' FROM '.$this->getTblName($tbl).' WHERE '.implode(' OR ',$arrWhere).' LIMIT 0,'.$limit;
		$res = $this->db->queryF($sql);
		$ret = array();
		while ($row = $this->fetchBoth($res)) $ret[] = $row[0];
		return $ret;
	}
	/** Tim kiem id theo ten */
	private function searchRefData($txt, $col, $tbl, $colid, $colxoa = null, $mahoa = 0){
		$limit = 500;
		if($mahoa){
			if(null === $this->_keydecode) $this->_keydecode = $this->getDbKeydecode();
			$col = 'AES_DECRYPT('.$col.',\''.$this->_keydecode.'\')';
		}
		$sql = 'SELECT '.$colid.' FROM '.$this->getTblName($tbl).' WHERE '.$this->BuildTextLikeCondition($col, $txt);
		if($colxoa) $sql .= ' AND COALESCE('.$colxoa.',0) = 0';
		$sql .= ' LIMIT 0,'.$limit;
		$res = $this->db->queryF($sql);
		$ret = array();
		while ($row = $this->fetchBoth($res)) $ret[] = $row[0];
		return $ret;
	}
    
	/**
     * Get object config from data row to build form layout
     * @param $row single fetch data row from DB
     */
    private function getConfigObjFromRow($row, $getRefData = true, $colposition = 'col_position') {
        $retVal = array();
        $retVal['rowid'] = $row['id'];
        $retVal['id'] = $row['col_code'];
		$retVal['name'] = $row['col_name'];
		$retVal['active'] = intval($row['col_active']);
        $retVal['hidden'] = $row['hidden'];
        $retVal['attributes'] = $row['attributes'];

        $retVal['label'] = defined($row['col_label']) ? constant($row['col_label']) : $row['col_label'];
		$nguon = null;
		
		$dmtype = preg_replace("/[^0-9]/","",$row['data_source']); 
		if($dmtype) $nguon = 'dm';
		$retVal['nguon'] = $nguon;
		if(false == empty($row['col_label_cus']))
			$retVal['label'] = defined($row['col_label_cus']) ? constant($row['col_label_cus']) : $row['col_label_cus'];
		$default_listtype = null;

        $options = array();
		$options['field'] = $row['col_name'];
		$options['table'] = $row['table_name'];
		$options['nguon'] = $nguon;
		if(-1 == $row['hidden']) $options['hiddenGrp'] = 1;
		
        if (isset($row['search_opt']))
            $options['pop'] = $row['search_opt']; //instant popup search?
		$upperINP = strtoupper($row['data_type']);
        switch ($upperINP) {
            case 'TEXT':
                $retVal['inputType'] = Self::TEXT;
                break;
            case 'CURRENT_USER':
                $retVal['inputType'] = Self::CURR_USER;
                $options['readonly'] = true;
                break;
            case 'CURRENT_USER_CREATE':
                $retVal['inputType'] = Self::CURR_USER_CREATE;
                $options['readonly'] = true;
                break;
            case 'MONEY':
                $retVal['inputType'] = Self::TEXT;
                $options['suffix'] = 'VND';
                $options['isNumeric'] = true;
                break;
            case 'PERCENTAGE':
                $retVal['inputType'] = Self::TEXT;
                $options['suffix'] = '%';
                $options['isNumeric'] = true;
                break;
            case 'CHECK':
                $retVal['inputType'] = Self::CHECK;
                if (false == $getRefData)
                    break;
                $this->getOptionDatasource($row['data_source'], $options, $default_listtype);                
                break;
            case 'SELECT':
            case 'SELECT_MULTI':
                $retVal['inputType'] = Self::SELECT;
                $options['multiple'] = $upperINP == 'SELECT_MULTI';
                if (false == $getRefData)
                    break;
                $this->getOptionDatasource($row['data_source'], $options, $default_listtype);                
                break;
            case 'RADIO':
                $retVal['inputType'] = Self::RADIO;
                if (false == $getRefData)
                    break;
                $this->getOptionDatasource($row['data_source'], $options, $default_listtype);
                break;
            case 'TEXT_MULTI':
                $retVal['inputType'] = Self::TEXT_MULTI;
                $options['enterNew'] = false;
				if (false == $getRefData)
                    break;
				$tmp = null;
				$arrtmp = array();
				$arrtmp['field'] = $options['field'];
				$arrtmp['table'] = $options['table'];
				$arrtmp['nguon'] = $options['nguon'];
                $options['url'] = $this->getOptionDatasource($row['data_source'], $tmp, $default_listtype,$arrtmp);
                break;
            case 'TEXT_MULTI_NEW':
			case 'MOBI':
                $retVal['inputType'] = Self::TEXT_MULTI;
				if($upperINP == 'MOBI') $options['class'] = 'mobif';
                $options['enterNew'] = true;
				if (false == $getRefData)
                    break;
				$tmp = null;				
                $options['url'] = $this->getOptionDatasource($row['data_source'], $tmp, $default_listtype);
                break;
            case 'TEXTAREA':
                $retVal['inputType'] = Self::TEXTAREA;
                break;
            case 'FILE':
                $retVal['inputType'] = Self::FILE;
                break;
            case 'TEXT_NUMERIC':
			case 'NUMERIC':
                $retVal['inputType'] = Self::TEXT;
                $options['isNumeric'] = true;
                break;
            case 'DATETIME':
                $retVal['inputType'] = Self::DATETIME;
                $options['showTime'] = true;
                break;
            case 'DATE':
                $retVal['inputType'] = Self::DATETIME;
                $options['showTime'] = false;
                break;
            case 'CURRENT_DATE':
                $retVal['inputType'] = Self::CURR_DATE;
                $options['showTime'] = false;
                break;
            case 'CURRENT_DATE_CREATE':
                $retVal['inputType'] = Self::CURR_DATE_CREATE;
                $options['showTime'] = false;
                break;
            case 'CURRENT_DATETIME':
                $retVal['inputType'] = Self::CURR_DATETIME;
                $options['showTime'] = false;
                break;
            case 'BIRTHDAY':
                $retVal['inputType'] = Self::BIRTHDAY;
                break;
            case 'HIDDEN':
                $retVal['inputType'] = Self::HIDDEN;
				// $retVal['hidden'] = 1;
                break;
            default:
                throw new Exception('Invalid data_type: '.$row['data_type']);
        }
        if (isset($row['zero']))
            $options['zero'] = (int) $row['zero'];
        if (isset($row['value_required']) && $row['value_required'])
            $options['required'] = true;
        if (isset($row['value_maxlength']) && $row['value_maxlength'])
            $options['maxlength'] = (int) $row['value_maxlength'];
        if (isset($row['value_readonly']) && $row['value_readonly']) {
            $options['readonly'] = $row['value_readonly'];
        }
        //have prefill?
        if (null != $this->_mprefillData && isset($this->_mprefillData[$row['col_code']])) {
            $options['defaultValue'] = $this->_mprefillData[$row['col_code']];
        } else if (isset($row['value_default']) && $row['value_default']) {
            if (!$this->_mupdateMode) {
                if ($retVal['inputType'] == Self::SELECT) {
                    $options['defaultValue'] = explode(',', $row['value_default']);
                } else if (isset($row['value_default']) && strtolower($row['value_default']) == 'now') {
                    $options['defaultValue'] = 'now';
                } else {
                    $options['defaultValue'] = $row['value_default'];
                }
            }
        } else if($default_listtype){
			//Default by list default
			$options['defaultValue'] = $default_listtype;
		}
        if (isset($row['validator']) && $row['validator']) {
            $validatorArr = array();
            //$validatorArr['message'] = $row['validation_message'];
            $validatorArr['message'] = defined($row['validation_message']) ? constant($row['validation_message']) : $row['validation_message'];
            $validatorArr['minlength'] = $row['minlength'];
            $validatorArr['maxlength'] = $row['maxlength'];
            $validatorArr['minvalue'] = $row['minvalue'];
            $validatorArr['maxvalue'] = $row['maxvalue'];
            $validatorArr['pattern'] = str_replace('\\','\\\\',$row['pattern']);
            $validatorArr['jsfunction'] = $row['jsfunction'];
            $options['validator'] = $validatorArr;
        }
        if (isset($row['trigger_url']) && $row['trigger_url'])
            $options['trigger_url'] = $row['trigger_url'];
        if (isset($row['trigger_target']) && $row['trigger_target'])
            $options['trigger_target'] = $row['trigger_target'];

        $retVal['pos'] = $this->extractColumnPostion($row[$colposition]);
        $retVal['options'] = $options;
        if (isset($row['table_name']) && $row['table_name'])
            $retVal['table'] = $row['table_name'];
        if (isset($row['col_name']) && $row['col_name'])
            $retVal['column'] = $row['col_name'];

        return $retVal;
    }

    /** Refine column position in form layout
     *
     */
    function extractColumnPostion($pos) {
        $retVal = array();
        //Format position: 0000    0000000000   00
        //                 group#   row#        col#
        $grpmask = bindec('1111000000000000');
        $retVal['group'] = ($grpmask & $pos) >> 12;
        $rowmask = bindec('0000111111111100');
        $retVal['row'] = ($rowmask & $pos) >> 2;
        $colmask = bindec('0000000000000011');
        $retVal['col'] = ($colmask & $pos);

        return $retVal;
    }

    private function formatNum($v) {
		if(null === $v || ''===$v) return '';		
        return number_format($v, 0, '.', ',');		
    }
	private function getNum($tmp) {
		$tmp = str_replace(',', '', $tmp);
        return $tmp;
    }

    private function buildAttributeForControl() {
        if (false == empty($this->_attributes)) {
            $retVal = array();
            $arr = json_decode($this->_attributes);
            foreach ($arr as $key => $value) {
                $retVal[] = $key . '="' . $value . '"';
            }
            return implode(' ', $retVal);
        }
        return '';
    }
	/**
	 * Check element arg in array arr, loose comparsion
	 */
    private function inarr_intstr($arg, $arr) {
        for ($i = 0; $i < count($arr); $i++) {
            if (strtolower((string) $arg) === strtolower((string) $arr[$i]))
                return true;
        }
        return false;
    }
	
	public function fetchQuery($query) {
		$conn = $this->em->getConnection();
		$statement = $conn->prepare($query);
		$statement->execute();
        $result = $statement->fetchAll();
        return $result;
    }

    private function getTableConfig($objName, $tableName = '', $getRefData = true, $whereCond = '', $getsysfield = 0) {
        $whereTbl = '';
        if ($tableName)
            $whereTbl .= ' AND \',' . $tableName . ',\' LIKE CONCAT("%,",table_name,",%")';
		if($whereCond){
			$whereTbl .= ' AND ('.$whereCond.')';
		}

        $query = 'SELECT * FROM ddfields'
                . ' WHERE col_active = 1 AND object_name = "' . $objName . '"'
                . $whereTbl
                . ' ORDER BY col_position,id';
        $result = $this->fetchQuery($query);
        $objList = array();
		$this->_activeFieldIds = array();
        foreach ($result as $row) {
            $objConfig = $this->getConfigObjFromRow($row, $getRefData);
			$this->_activeFieldIds[] = $objConfig['id'];
            array_push($objList, $objConfig);
        }
        return $objList;
    }
	/** Text multi input
     **/
    function BuildTextMultiInput($id, $label, $url, $allowNew = false, $maxlength = null, $required = false, $defaultValue = null, $readonly = false, $popcode, $trigger_target = null, $trigger_url = null, $searchOpt = null, $class = null,$nguon = null) {
        $this->_mtextboxlib = true;
        $instantPopSearch = null;
        $noSearch = false;
		
        if ($searchOpt) {
            //{"pop":0, "ins":"S"|"M"}
            if (property_exists($searchOpt, 'pop') && $searchOpt->pop < 1) {
                $noSearch = true;
            } else {
                $instantPopSearch = $searchOpt->ins;
            }
        }
        $max = 0;
        if ($maxlength)
            $max = $maxlength;
        if ($readonly) {
            //Count default value and set to max
            $max = max(count($defaultValue), $max);
            //TODO make another control mimic this
        }
        $allEnterNew = ($allowNew) ? 'true' : 'false';
		$retVal = '<div class="col-md-10">';
        $retVal .= '<input t="tm" type="text" id="' . $id . '" name="' . $id . '" class="col-md-10 form-control '.$class.'" ' . $this->buildAttributeForControl() . '/>';

        $retVal .= '<span class="help-inline">';
        if ($required)
            $retVal .='<span class="redtext">*</span>';
        if (false == $noSearch)
            $retVal .= '<i id="_s' . $id . '" class="glyphicon glyphicon-search click"></i>';
        $retVal .= '<i id="_r' . $id . '" class="glyphicon glyphicon-erase click"></i></span>';

        $value = 'null';
        if ($allowNew && $defaultValue) {
            $arr = explode(',', $defaultValue);
            $defaultValue = '"' . implode('","', $arr) . '"';
        }
        if ($defaultValue)
            $value = '[' . $defaultValue . ']';
        $maxstr = 'null';
        if ($max)
            $maxstr = $max;

        $selTarget = ',""';
        $istPopSearchStr = 'null';
		$multi = 1;
        if (null != $instantPopSearch) {
            $objarr = array('L' => $label, 'M' => 1);
            if ('s' == strtolower($instantPopSearch)){
                $objarr['M'] = 0;
			}
            $istPopSearchStr = json_encode($objarr);
        }
		if($maxlength == 1) $multi = 0;
        $urljs = '"' . $url . '"';
        if(is_array($url)) $urljs = json_encode($url);
        $this->mscript .= 'tk' . $id . '=new Geekutil.TM("' . $id . '",' . $allEnterNew . ',' . $urljs . ',' . $value . ',' . $maxstr . ',' . $istPopSearchStr . ');';
		
												
        if ($trigger_target) {
            $targetArr = explode(',', $trigger_target);
            $selTarget = '';
            foreach ($targetArr as $target) {
                $selTarget .= ',"' . $this->getDefaultValue($target) . '"';
            }
            $selTarget = ',[' . substr($selTarget, 1) . ']';
            $jstargetArr = implode('","', $targetArr);
            $triggerURLArr = explode(',', $trigger_url);
            $jstriggerURLArr = implode('","', $triggerURLArr);
            $this->mscript .= 'tk' . $id . '.trigger(["' . $jstargetArr . '"],["' . $jstriggerURLArr . '"]' . $selTarget . ');';
        }
        return $retVal.'</div>';
    }

    function BuildDateTimePicker($id, $label, $showTime = false, $required = false, $defaultValue = null, $readonly = false) {
        $plh = 'year/month/day';
        $timestamp = 0;
        $class2 = '';
        if ($readonly)
            $class2 = 'readonly';
        if ($defaultValue) {
            //Date time in Y-m-d H:m:s
            if (is_string($defaultValue)) {
                $timestamp = strtotime($defaultValue);
            } else {
                $timestamp = $defaultValue;
            }
        }
		if(!$timestamp) $timestamp = '0';
		$retVal = '<div class="col-md-10">';
        if ($showTime) {			
            $retVal .= '<input type="text" placeholder="' . $plh . '" autocomplete="off" id="' . $id . '_d" name="' . $id . '_d" maxlength="10"';
            $retVal .= 'class="col-md-4 form-control ' . $class2 . '"';
            if ($readonly) {
                $retVal .= ' readonly';
            };

            $retVal .= '/>';
            $retVal .= '<input type="text" autocomplete="off" id="' . $id . '_h" name="' . $id . '_h" class="col-md-3 form-control ' . $class2 . '" maxlength="5"';
            if ($readonly)
                $retVal .= ' readonly';
            $retVal .= '/>';
            if ($readonly) {
                $retVal .= '<a class="btn readonly" href="#" id="' . $id . '_p"></a>';
            }
            else
                $retVal .= '<a class="btn btn-default btncak" href="#" id="' . $id . '_p"></a>';

            $this->mscript .= 'cak' . $id . '=new Geekutil.Cal("' . $id . '",false,' . $timestamp . ',' . $readonly . ');';
        }else {
            $retVal .= '<input type="text" placeholder="' . $plh . '" autocomplete="off" id="' . $id . '_d" name="' . $id . '_d" class="form-control ' . ($readonly ? 'col-md-10 readonly' : 'col-md-9') . '" maxlength="10"';
            if ($readonly)
                $retVal .= ' readonly';
            $retVal .= '/>';
            $this->mscript .= 'cak' . $id . '=new Geekutil.Cal("' . $id . '",true,' . $timestamp . ',' . $readonly . ');';
        }
        $retVal .= '<input t="d" type="hidden" id="' . $id . '" name="' . $id . '" ' . $this->buildAttributeForControl() . '/>';
        $retVal .= '<span class="help-inline">';
        if ($required)
            $retVal .='<span class="redtext">*</span>';
        if (!$readonly)
            $retVal .= '<i class="glyphicon glyphicon-calendar click" id="' . $id . '_i"></i><i id="' . $id . '_r" class="glyphicon glyphicon-erase click"></i></span>';

        $this->_mcallib = true;
        return $retVal.'</div>';
    }

    /* Build hidden field */
    function BuildInputHidden($id, $defaultValue = null) {
        $retVal = '';
        $retVal .= '<input type="hidden" id="' . $id . '" name="' . $id . '"';
        if ($defaultValue)
            $retVal .= ' value="' . htmlspecialchars($defaultValue) . '"';
        $retVal .= ' ' . $this->buildAttributeForControl() . '/>';
        return $retVal;
    }

    function BuildInputText($id, $label, $required = false, $maxlength = null, $defaultValue = null, $readonly = false, $isNumeric = false, $suffix = null) {
        if ($defaultValue && $isNumeric)
            $defaultValue = $this->formatNum($defaultValue);
        $retVal = '<div class="col-md-10">';
        if ($suffix)
            $retVal .= '<div class="input-group">';
        $retVal .= '<input type="text" autocomplete="off" id="' . $id . '" name="' . $id . '" class="form-control' . ($isNumeric ? ' numeric' : '') . '"';
        if ($maxlength)
            $retVal.= ' maxlength="' . $maxlength . '"';
        if ($readonly)
            $retVal .= ' readonly';
        if ($defaultValue)
            $retVal .= ' value="' . htmlspecialchars($defaultValue) . '"';
        $retVal .= ' ' . $this->buildAttributeForControl() . '/>';
        if ($required) {
            if ($suffix) {
                $retVal .= '<span class="input-group-addon">' . $suffix . '</span><span class="redtext help-inline">*</span></div>';
            }
            else
                $retVal .= '<span class="redtext help-inline">*</span>';
        }else {
            if ($suffix) {
                $retVal .= '<span class="input-group-addon">' . $suffix . '</span></div>';
            }
        }
        return $retVal.'</div>';
    }

    function BuildInputCheck($id, $label, $defaultValue = null, $readonly = false) {
        $retVal = '<div class="col-md-10"><div class="checkbox">';
		$retVal .= '<label><input type="checkbox" id="' . $id . '" name="' . $id . '" value="1"';
        if ($defaultValue && strtolower($defaultValue) != 'n')
            $retVal .= 'checked';
        $retVal .= ' ' . $this->buildAttributeForControl();

        return $retVal . '/></label></div></div>';
    }

    function BuildInputTextArea($id, $label, $required = false, $rows = 3, $defaultValue = null, $readonly = false, $isNumeric = false, $class = 'form-control') {
        if ($defaultValue && $isNumeric)
            $defaultValue = $this->formatNum($defaultValue);
		$retVal = '<div class="col-md-10">';
        $retVal .= '<textarea id="' . $id . '" name="' . $id . '" class="' . $class . ($isNumeric ? ' numeric' : '') . '"';
        if ($rows)
            $retVal.= ' rows="' . $rows . '"';
        if ($readonly)
            $retVal .= ' readonly';
        $retVal .= ' ' . $this->buildAttributeForControl() . '>';
        if ($defaultValue)
            $retVal .= $defaultValue;
        $retVal .= '</textarea>';
        if ($required)
            $retVal .='<span class="redtext help-inline">*</span>';
        return $retVal.'</div>';
    }

    function BuildInputRadio($id, $labelArr, $valueArr, $inline = true, $defaultValue = null, $url) {
        if (is_array($labelArr)) {
			$retVal = '<div class="col-md-10">';
			if($inline){
				$retVal .= '<div class="radio">';
				for ($i = 0; $i < count($labelArr); $i++) {
	                $retVal .= '<label class="radio-inline">';
	                $retVal .= '<input type="radio" name="' . $id . '" id="' . $id . '_' . $i . '" value="' . $valueArr[$i] . '"';
	                if ($defaultValue && $defaultValue == $valueArr[$i])
	                    $retVal .= ' checked';
                	$retVal .= '/>' . $labelArr[$i] . '</label>';
            	}
				$retVal .= '</div>';
			}else{
				for ($i = 0; $i < count($labelArr); $i++) {
	                $retVal .= '<div class="radio"><label>';
	                $retVal .= '<input type="radio" name="' . $id . '" id="' . $id . '_' . $i . '" value="' . $valueArr[$i] . '"';
	                if ($defaultValue && $defaultValue == $valueArr[$i])
	                    $retVal .= ' checked';
                	$retVal .= '/>' . $labelArr[$i] . '</label></div>';
            	}
			}
            
            return $retVal.'</div>';
        }
        throw new Exception('Wrong RADIO data. Missing data source.');
    }
	/* Goi sau khi load toan bo table config object
	*/
    private function getDefaultValue($id) {
        if ('' != $this->_prefixID)
            $id = substr($id, strlen($this->_prefixID));
        
        if (!$this->_mConfigList)
            throw new Exception('Wrong call - must have config list.');
        foreach ($this->_mConfigList as $config) {
            if ($config['id'] == $id) {
                if (isset($config['options']['defaultValue']))
                    return $config['options']['defaultValue'];
                return '';
            }
        }
        return '';
    }
	function BuildInputSelect($id, $labelArr, $valueArr, $required = null, $defaultValue = null, $multiple = false, $url = null, $trigger_target = null, $trigger_url = null, $zero = 1) {
        
        if ($trigger_target) {
            $targetArr = explode(',', $trigger_target);
            $selTarget = '';
            foreach ($targetArr as $target) {
                $selTarget .= ',"' . $this->getDefaultValue($target) . '"';
            }
            $selTarget = ',[' . substr($selTarget, 1) . ']';
            $jstargetArr = implode('","', $targetArr);
            $triggerURLArr = explode(',', $trigger_url);
            $jstriggerURLArr = implode('","', $triggerURLArr);
            $this->mscript .= 'triggerSelect("' . $id . '",["' . $jstriggerURLArr . '"],["' . $jstargetArr . '"]' . $selTarget . ');';
        }
        $nameM = '';
        if ($multiple)
            $nameM = '[]';
		$retVal = '<div class="col-md-10">';
        if (is_array($labelArr)) {
            $retVal .= '<select class="form-control" name="' . $id . $nameM . '" id="' . $id . '"';
            if ($multiple)
                $retVal .= ' multiple';
            $retVal .= ' ' . $this->buildAttributeForControl() . '>';
            if ($zero)
                $retVal .= '<option value="' . $this->_noval . '">' . (defined('_DDL_LC') ? constant('_DDL_LC') : '...Select...') . '</option>';
            for ($i = 0; $i < count($labelArr); $i++) {
                $retVal .= '<option value="' . $valueArr[$i] . '"';
                if (is_array($defaultValue)) {
					if ($this->inarr_intstr($valueArr[$i], $defaultValue))
                    	$retVal .= ' selected';

                } elseif (false !== stripos(',' . $defaultValue . ',', ','.$valueArr[$i].',')) {
                    $retVal .= ' selected';
                }

                $retVal .= '>' . $labelArr[$i] . '</option>';
            }
            $retVal .= '</select>';
            if ($required)
                $retVal .='<span class="redtext help-inline">*</span>';
            return $retVal.'</div>';
        }
        if ($url) {
            if ($defaultValue) {
                if (is_array($defaultValue))
                    $defaultValue = implode(',', $defaultValue);
                $this->mscript .= 'loadAndFillSelect("' . $id . '","' . $url . '","' . $defaultValue . '",' . $zero . ');';
            }
            else
                $this->mscript .= 'loadAndFillSelect("' . $id . '","' . $url . '","",' . $zero . ');';
            $retVal = '<select class="form-control" name="' . $id . '" id="' . $id . '"';
            if ($multiple)
                $retVal .= ' multiple="multiple"';
            $retVal .= ' ' . $this->buildAttributeForControl() . '>';
            $retVal .= '</select>';
            if ($required)
                $retVal .='<span class="redtext help-inline">*</span>';
            return $retVal.'</div>';
        }

        if ($required)
            return '<select ' . $this->buildAttributeForControl() . ' class="form-control" name="' . $id . '" id="' . $id . '"></select> <span class="redtext">*</span></div>';
        return '<select ' . $this->buildAttributeForControl() . ' class="form-control" name="' . $id . '" id="' . $id . '"></select></div>';
    }
    /** Build single control
     */
    private function BuildInputHTMLControls($id, $label, $inputType, $options = null) {
        $hiddenClass = '';
		$nguon = $options['nguon'];
        if ($inputType == Self::HIDDEN)
            $hiddenClass = 'hidden';
		if(isset($options['hiddenGrp']) && $options['hiddenGrp']) $hiddenClass = 'hidden';
        $id = $this->_prefixID . $id;
        if ($inputType == Self::DATETIME || $inputType == Self::CURR_DATE || $inputType == Self::CURR_DATE_CREATE || $inputType == Self::CURR_DATETIME) {
            $retVal = '<div class="form-group ' . $hiddenClass . '"><label for="' . $id . '_d" class="control-label col-md-2">' . $label . '</label>';
        } else {
			if($inputType == Self::RADIO){
				$retVal = '<div class="form-group ' . $hiddenClass . '"><label for="' . $id . '_1" class="control-label col-md-2">' . $label . '</label>';
			}else
            	$retVal = '<div class="form-group ' . $hiddenClass . '"><label for="' . $id . '" class="control-label col-md-2">' . $label . '</label>';
        }

        $required = false;
        $maxlength = null;
        $defaultValue = null;
        $readonly = 0;
        $isNumeric = false;
        $rows = 3;
        $valueArr = null;
        $labelArr = null;
        $multiple = false;
        $inline = false;
        $showTime = false;
        $textMultiEnterNew = false;
        $url = null;
        $validatorObj = null;
        $suffix = null;
        $trigger_url = null;
        $trigger_target = null;
        $zero = 1;
        $searchOpt = null;
		$class = null;
		$isMobi = 0;
		if (isset($options['class']))
            $isMobi = 'mobif' == $options['class'];
        if ($options) {
			if (isset($options['class']))
                $class = $options['class'];
            if (isset($options['zero']))
                $zero = $options['zero'];
            if (isset($options['required']))
                $required = $options['required'];
            if (isset($options['maxlength']))
                $maxlength = $options['maxlength'];
            if (isset($options['defaultValue']))
                $defaultValue = $options['defaultValue'];
            if (isset($options['readonly']))
                $readonly = $options['readonly'];
            if (isset($options['isNumeric']))
                $isNumeric = $options['isNumeric'];
            if (isset($options['rows']))
                $rows = $options['rows'];
            if (isset($options['labelArr']))
                $labelArr = $options['labelArr'];
            if (isset($options['valueArr']))
                $valueArr = $options['valueArr'];
            if (isset($options['multiple']))
                $multiple = $options['multiple'];
            if (isset($options['inline']))
                $inline = $options['inline'];
            if (isset($options['showTime']))
                $showTime = $options['showTime'];
            if (isset($options['enterNew']))
                $textMultiEnterNew = $options['enterNew'];
            if (isset($options['url'])){
                if(is_array($options['url'])) {
                    $url = $options['url'];
                }else{
					$url = $options['url'];
					// if($options['url']) $this->_gen_req[] = $url;
				}
            }
                
            if (isset($options['validator']))
                $validatorObj = $options['validator'];
            if (isset($options['suffix']))
                $suffix = $options['suffix'];
            if (isset($options['pop']))
                $searchOpt = json_decode($options['pop']); //{"pop":0, "ins":"S"|"M"}
			$inLstConfig = ',';
			if (isset($options['trigger_target'])) {
                $trigger_target = $options['trigger_target'];
                if ($trigger_target) {
                    $tmp = explode(',', $trigger_target);
                    foreach ($tmp as $idx => &$val) {
						if(in_array($val, $this->_activeFieldIds)) $inLstConfig .= $idx.',';
						$val = $this->_prefixID . $val;
                    }
                    $trigger_target = implode(',', $tmp);
                }
            }
            if (isset($options['trigger_url'])) {
                $triggerURLArr = explode(',', $options['trigger_url']);
                foreach ($triggerURLArr as $idx => &$trigURL){					
					$b = trim($trigURL);
					$trigURL = $trigURL;
					// if($b && $defaultValue && false !== stripos($inLstConfig, ','.$idx.',')) $this->_gen_req[] = $trigURL;//
				}
                $trigger_url = implode(',', $triggerURLArr);
            }
            
        }
		$arrInfos['fullname'] = 'full name';
		// TODO: optimize - load users info only once
        switch ($inputType) {
            case Self::TEXT:
                $retVal .= $this->BuildInputText($id, $label, $required, $maxlength, $defaultValue, $readonly, $isNumeric, $suffix);
                break;
            case Self::CURR_USER:
                if ($this->_mupdateMode) {
                    // $query = 'SELECT ' . $this->getColName('ho_va_ten') . ' FROM ' . $this->getTblName('crm_user') . ' WHERE ' . $this->getColName('user_id') . ' = ' . $defaultValue;
                    // $result = $this->fetchQuery($query);
                    // $row = $this->fetchBoth($result);
                    $defaultValue = '';//$row[0];
                    $retVal .= $this->BuildInputText($id, $label, $required, $maxlength, $defaultValue, $readonly, false, null);
                }
                else
                    $retVal .= $this->BuildInputText($id, $label, $required, $maxlength, $arrInfos['fullname'], $readonly, false, null);
                break;
            case Self::CURR_USER_CREATE:
                //update mode - get name from DB;
                if ($this->_mupdateMode) {
                    // $query = 'SELECT ' . $this->getColName('ho_va_ten') . ' FROM ' . $this->getTblName('crm_user') . ' WHERE ' . $this->getColName('user_id') . ' = ' . $defaultValue;
                    // $result = $this->fetchQuery($query);
                    // $row = $this->fetchBoth($result);
                    $defaultValue = '';//$row[0];
                    $retVal .= $this->BuildInputText($id, $label, $required, $maxlength, $defaultValue, $readonly, false, null);
                }
                else
                    $retVal .= $this->BuildInputText($id, $label, $required, $maxlength, $arrInfos['fullname'], $readonly, false, null);
                break;
            case Self::CHECK:
                $retVal .= $this->BuildInputCheck($id, $label, $defaultValue, $readonly);
                break;
            case Self::RADIO:
                $retVal .= $this->BuildInputRadio($id, $labelArr, $valueArr, $inline, $defaultValue, $url);
                break;
            case Self::TEXTAREA:
                $retVal .= $this->BuildInputTextArea($id, $label, $required, $rows, $defaultValue, $readonly, $isNumeric);
                break;
            case Self::SELECT:
                $retVal .= $this->BuildInputSelect($id, $labelArr, $valueArr, $required, $defaultValue, $multiple, $url, $trigger_target, $trigger_url, $zero);
                break;
            case Self::DATETIME:
                $retVal .= $this->BuildDateTimePicker($id, $label, $showTime, $required, $defaultValue, $readonly);
                break;
            case Self::CURR_DATE:
				if ($this->_mupdateMode) {
					$retVal .= $this->BuildDateTimePicker($id, $label, false, false, $defaultValue, true);
				} else
                	$retVal .= $this->BuildDateTimePicker($id, $label, false, false, $this->getCurrentTimestamp(), true);
                break;
            case Self::CURR_DATETIME:
				if ($this->_mupdateMode) {
                    $retVal .= $this->BuildDateTimePicker($id, $label, true, false, $defaultValue, true);
                }else
                	$retVal .= $this->BuildDateTimePicker($id, $label, true, false, $this->getCurrentTimestamp(), true);
                break;
            case Self::CURR_DATE_CREATE:
                if ($this->_mupdateMode) {
                    $retVal .= $this->BuildDateTimePicker($id, $label, false, false, $defaultValue, true);
                }else
                    $retVal .= $this->BuildDateTimePicker($id, $label, false, false, $this->getCurrentTimestamp(), true);
                break;
            case Self::TEXT_MULTI:
                $retVal .= $this->BuildTextMultiInput($id, $label, $url, $textMultiEnterNew, $maxlength, $required, $defaultValue, $readonly, null, $trigger_target, $trigger_url, $searchOpt, $class,$nguon);
                break;
            case Self::BIRTHDAY:
                $retVal .= $this->BuildInputBirthday($id, $label, $defaultValue);
                break;
            case Self::HIDDEN:
                $retVal .= $this->BuildInputHidden($id, $defaultValue);
                break;
            default:
                throw new Exception('INPUT TYPE Not implemented.');
        }

        
        return $retVal . '</div>';
    }

	/** Layout for insert form
     * @param String $obj_name value of object_name in table config
     * @param Array $arrGrpName array of group name, ex: ['ten nhom 1', 'ten nhom 2', 'ten nhom 3']
     * @return String
     * */
    public function GenerateLayout($obj_name, $arrGrpName = null) {
        if (!$this->_mConfigList)
            $this->_mConfigList = $this->getTableConfig($obj_name);
		// if(null === $arrGrpName) $arrGrpName = $this->getGroupName($obj_name);
		$retVal = $this->BuildLayout($this->_mConfigList, $obj_name, $arrGrpName);        
		
        return $retVal;
    }
	
    /** Build layout from table config values
     * @param $objconfigList - collection of row config data
     */
    function BuildLayout($objconfigList, $formid, $arrGrpName) {
        $currentGrp = 0;
        //Total controls in form layout
        $total = count($objconfigList);
        $loop = 0;
        $retVal = '';
        $currentRow = 1;
        $inrow = false; //Open div row
        $col1 = '';
        $col2 = '';

        while ($loop < $total) {
            $objConfig = $objconfigList[$loop];
			$option = $objConfig['options'];
			$option['nguon'] = $objConfig['nguon'];
            //ignore hidden field??
            $hidden = $objConfig['hidden'];
            if (0 < $hidden) {
                $loop++;
                continue;
            }
            $this->_attributes = $objConfig['attributes'];
            $pos = $objConfig['pos'];
            //New group?
            if ($currentGrp < $pos['group']) {
                $currentGrp = $pos['group'];
                //Begin new group - first, close current group
                if (true === $inrow) {
                    if ($col1 || $col2) {
                        $retVal .= '<div class="col-md-6">' . $col1 . '</div>';
                        $retVal .= '<div class="col-md-6">' . $col2 . '</div>';
                        $retVal .= '</div>';
                        $inrow = false;
                        $col1 = '';
                        $col2 = '';
                    }
                }
                //ignore custom build
                if ('' === $this->_prefixID) {
                    if (isset($arrGrpName) && isset($arrGrpName[$currentGrp - 1])) {
                        // $retVal .= '<legend id="_lg_' . $currentGrp . '">' . $arrGrpName[$currentGrp - 1] . '</legend>';
                    } else {
                        // $retVal .= '<legend id="_lg_' . $currentGrp . '">Thong tin grp ' . $currentGrp . '</legend>';
                    }
                }

                $currentRow = 1; //Reset row for new group
            }
            if ($pos['col'] == 0) {
                //Colspan = 2
                if (true === $inrow) {
                    //close current row
                    if ($col1 || $col2) {
                        $retVal .= '<div class="col-md-6">' . $col1 . '</div>';
                        $retVal .= '<div class="col-md-6">' . $col2 . '</div>';
                        $retVal .= '</div>';
                        $inrow = false;
                        $col1 = '';
                        $col2 = '';
                    }
                }
                //A whole new rows span this control.
                $retVal .= '<div class="row"><div class="col-md-12"> ';
                $retVal .= $this->BuildInputHTMLControls($objConfig['id'], $objConfig['label'], $objConfig['inputType'], $option);
                $retVal .= '</div></div>';
                $inrow = false;
                $currentRow = $pos['row'];
            }
            if ($pos['col'] == 1) {
                if (false === $inrow) {
                    $retVal .= '<div class="row"> ';
                    $inrow = true;
                } else if ($currentRow < $pos['row']) {
                    //New row for sure.
                    $currentRow = $pos['row'];
                }
                $col1 .= $this->BuildInputHTMLControls($objConfig['id'], $objConfig['label'], $objConfig['inputType'], $option);
            }
            if ($pos['col'] == 2) {
                if (false === $inrow) {
                    $retVal .= '<div class="row"> ';
                    $inrow = true;
                } else if ($currentRow < $pos['row']) {
                    //New row for sure.
                    $currentRow = $pos['row'];
                }
                $col2 .= $this->BuildInputHTMLControls($objConfig['id'], $objConfig['label'], $objConfig['inputType'], $option);
            }
            $loop++;
        }
        if (true === $inrow) {
            $retVal .= '<div class="col-md-6">' . $col1 . '</div> ';
            $retVal .= '<div class="col-md-6">' . $col2 . '</div> ';
            $retVal .= '</div>';
        }
        if ('' !== $this->_prefixID)
            return $retVal;
        return '<form class="form-horizontal" method="post" id="f_' . $formid . '">' . $retVal . '</form>';
    }
	/** Load data and render update form
     * @param Array $row single row data fetch from tables data
     * @param String $obj_name object_name in table_config
     * @param Array $arrGrpName group name ex:['ten nhom 1', 'ten nhom 2']
     * @param Integer $forview generate layout for view or not (for update on default)
     * @return String
     */
    public function LoadDatarowToConfig($row, $obj_name, $arrGrpName = null, $forview = 0) {
        $this->_mforview = $forview;
        $this->_mupdateMode = true;
		$this->_mainID = $row['id'];

        if (!$this->_mConfigList)
            $this->_mConfigList = $this->getTableConfig($obj_name);

        foreach ($this->_mConfigList as &$config) {
            $config['options']['defaultValue'] = null;
			$type = $config['inputType'];
            if (Self::BIRTHDAY != $type && isset($row[$config['column']])){
                $config['options']['defaultValue'] = $row[$config['column']];				
                if (Self::DATETIME == $type){
                    $config['options']['defaultValue'] = '0000-00-00 00:00:00' === ($row[$config['column']]) ? '' : $row[$config['column']];                        
                }
            }
			if (Self::BIRTHDAY == $type){
				$date = $row[$config['column'] . '_date'];
				$month = $row[$config['column'] . '_month'];
				$year = $row[$config['column'] . '_year'];
				if($date || $month || $year){
					$config['options']['defaultValue'] = array(
					'date' => $date,
					'month' => $month,
					'year' => $year);
				}
			}
        }
        return $this->GenerateLayout($obj_name, $arrGrpName);
    }


    /* get search data from POST data
     * */
    public function GetSearchData($postdata, $layoutdata) {
        $retVal = array();
        foreach ($layoutdata as $v) {
            $tmp = array();
            switch (strtoupper($v['type'])) {
                case 'DATE':
                    if (isset($postdata[$v['id']]) && $postdata[$v['id']] != '0') {
                        $tmp['v'] = $postdata[$v['id']];
                    }
                    if (isset($postdata['f' . $v['id']]) && $postdata['f' . $v['id']]) {
                        $tmp['from'] = $postdata['f' . $v['id']];
                    }
                    if (isset($postdata['t' . $v['id']]) && $postdata['t' . $v['id']]) {
                        $tmp['to'] = $postdata['t' . $v['id']];
                    }
                    break;
                case 'NUMERIC':
                    if (isset($postdata['f' . $v['id']]) && $postdata['f' . $v['id']] != '') {
                        $tmp['from'] = $this->getNum($postdata['f' . $v['id']]);
                    }
                    if (isset($postdata['t' . $v['id']]) && $postdata['t' . $v['id']] != '') {
                        $tmp['to'] = $this->getNum($postdata['t' . $v['id']]);
                    } 
                    break;
                case 'SELECT':
                    if (isset($postdata[$v['id']]) && $postdata[$v['id']] != '-1') {
                        $tmp['v'] = $postdata[$v['id']];
                    }
                    break;
                case 'CHECK':
                    if (isset($postdata[$v['id']]) && $postdata[$v['id']] != '') {
                        if (is_array($postdata[$v['id']])) {
                            $tmp['v'] = implode(',', $postdata[$v['id']]);
                        }
                        else
                            $tmp['v'] = $postdata[$v['id']];
                    }
                    break;
                case 'BIRTHDAY':
                    if (isset($postdata['d' . $v['id']]) && $postdata['d' . $v['id']] != '0') {
                        $tmp['day'] = $postdata['d' . $v['id']];
                    }
                    if (isset($postdata['m' . $v['id']]) && $postdata['m' . $v['id']] != '0') {
                        $tmp['month'] = $postdata['m' . $v['id']];
                    }
                    if (isset($postdata['y' . $v['id']]) && $postdata['y' . $v['id']] != '0') {
                        $tmp['year'] = $postdata['y' . $v['id']];
                    }
                    break;
                default:
                    if (isset($postdata[$v['id']]) && $postdata[$v['id']] != '') {
                        $tmp['v'] = $postdata[$v['id']];
                    }
                    break;
            }
            if (count($tmp)) {
                $tmp['id'] = $v['id'];
                $tmp['type'] = $v['type'];
                $tmp['op'] = 'AND';
                $tmp['colname'] = isset($v['colname']) ? $v['colname'] : 'x';
                $retVal[] = $tmp;
            }
        }
        return $retVal;
    }

    public function GenerateManualSearchControls($data,$row = null) {
        $objconfigList = array();
        foreach ($data as $v) {
			if($row){
				foreach ($row as $vl) {
					if($vl['id'] == $v['id']){
						if(isset($vl['v']))
							$v['defaultValue'] = $vl['v']; 
						if(isset($vl['from']))
							$v['from'] = $vl['from']; 
						if(isset($vl['to']))
							$v['to'] = $vl['to']; 
						if(isset($vl['day']))
							$v['day'] = $vl['day']; 
						if(isset($vl['month']))
							$v['month'] = $vl['month']; 
						if(isset($vl['year']))
							$v['year'] = $vl['year']; 
					}
				}
			}
            $objconfigList[] = $this->GetConfigObjFromManualSearchRow($v);
        }
        $retVal = $this->BuildLayoutSearch($objconfigList);
        return $retVal;
    }

    /**
     * Layout for search form manual controls
     */
    private function BuildLayoutSearch($objconfigList) {
        $currentGrp = 0;
        //Total controls in form layout
        $total = count($objconfigList);
        $loop = 0;
        $retVal = '';
        $currentRow = 1;
        $inrow = false; //Open div row
        $col1 = '';
        $col2 = '';
		$nguon = null;
		usort($objconfigList, function($a, $b){
				return intval($a['pos']['row'] * 4 + $a['pos']['col']) - intval($b['pos']['row'] * 4 + $b['pos']['col']);
			}
		);
        while ($loop < $total) {
            $objConfig = $objconfigList[$loop];
			// $nguon = $objConfig['nguon'];
            //$this->_attributes = $objConfig['attributes'];
            $pos = $objConfig['pos'];

            //New group?
            if ($currentGrp < 1) {
                $currentGrp = 1;
                //Begin new group - first, close current group
                if (true === $inrow) {
                    if ($col1 || $col2) {
                        $retVal .= '<div class="col-sm-6">' . $col1 . '</div>';
                        $retVal .= '<div class="col-sm-6">' . $col2 . '</div>';
                        $retVal .= '</div>';
                        $inrow = false;
                        $col1 = '';
                        $col2 = '';
                    }
                }

                $currentRow = 1; //Reset row for new group
            }
            if ($pos['col'] == 0) {
                //Colspan = 2
                if (true === $inrow) {
                    //close current row
                    if ($col1 || $col2) {
                        $retVal .= '<div class="col-sm-6">' . $col1 . '</div>';
                        $retVal .= '<div class="col-sm-6">' . $col2 . '</div>';
                        $retVal .= '</div>';
                        $inrow = false;
                        $col1 = '';
                        $col2 = '';
                    }
                }
                //A whole new rows span this control.
                $retVal .= '<div class="row"><div class="col-sm-12"> ';
                $retVal .= $this->BuildSearchControls($objConfig['id'], $objConfig['label'], $objConfig['inputType'], $objConfig['options'],$nguon);
                $retVal .= '</div></div>';
                $inrow = false;
                $currentRow = $pos['row'];
            }
            if ($pos['col'] == 1) {
                if (false === $inrow) {
                    $retVal .= '<div class="row">';
                    $inrow = true;
                } else if ($currentRow < $pos['row']) {
                    //New row for sure.
                    $currentRow = $pos['row'];
                }
                $col1 .= $this->BuildSearchControls($objConfig['id'], $objConfig['label'], $objConfig['inputType'], $objConfig['options']);
            }
            if ($pos['col'] == 2) {
                if (false === $inrow) {
                    $retVal .= '<div class="row"> ';
                    $inrow = true;
                } else if ($currentRow < $pos['row']) {
                    //New row for sure.
                    $currentRow = $pos['row'];
                }
                $col2 .= $this->BuildSearchControls($objConfig['id'], $objConfig['label'], $objConfig['inputType'], $objConfig['options']);
            }
            $loop++;
        }
        if (true === $inrow) {
            $retVal .= '<div class="col-sm-6">' . $col1 . '</div> ';
            $retVal .= '<div class="col-sm-6">' . $col2 . '</div> ';
            $retVal .= '</div>';
        }

        return $retVal;
    }

    /** Build control for search form
     * */
    private function BuildSearchControls($id, $label, $inputType, $options) {
        $maxlength = null;
        $defaultValue = null;
		$from  = null;
		$to = null;
		$day  = null;
		$month = null;
		$year = null;
        $isNumeric = false;
        $valueArr = null;
        $labelArr = null;
        $multiple = false;
        $inline = false;
        $showTime = false;
        $textMultiEnterNew = false;
        $url = null;
        $suffix = null;
        $instantPopSearch = null;
        $zero = 1;
        //range labels
        $label1 = $label . ' from';
        $label2 = 'to';
        if ($options) {
            if (isset($options['maxlength']))
                $maxlength = $options['maxlength'];
            if (isset($options['defaultValue']))
                $defaultValue = $options['defaultValue'];
			 if (isset($options['from']))
                $from = $options['from'];
			if (isset($options['to']))
                $to = $options['to'];
			if (isset($options['day']))
                $day = $options['day'];
			if (isset($options['month']))
                $month = $options['month'];
			if (isset($options['year']))
                $year = $options['year'];
            if (isset($options['isNumeric']))
                $isNumeric = $options['isNumeric'];
            if (isset($options['labelArr']))
                $labelArr = $options['labelArr'];
            if (isset($options['valueArr']))
                $valueArr = $options['valueArr'];
            if (isset($options['multiple']))
                $multiple = $options['multiple'];
            if (isset($options['inline']))
                $inline = $options['inline'];
            if (isset($options['lblfrom']))
                $label1 = $options['lblfrom'];
            if (isset($options['lblto']))
                $label2 = $options['lblto'];
            if (isset($options['zero']))
                $zero = $options['zero'];
            if (isset($options['enterNew']))
                $textMultiEnterNew = $options['enterNew'];
            if (isset($options['url'])){
                if(is_array($options['url'])) {
                    $url = $options['url'];
                }else        
                    $url = APP_URL . $options['url'];
            }
            if (isset($options['suffix']))
                $suffix = $options['suffix'];
            if (isset($options['pop']))
                $instantPopSearch = $options['pop'];
        }
        $retVal = '';
        switch ($inputType) {
            case Self::TEXT:
				if($defaultValue){
				$retVal .= $this->BuildInputTextSearch($id, $label, $suffix,null,$defaultValue);
                break;
				}
            case Self::TEXTAREA:
                if ($isNumeric) {
                    $retVal .= $this->BuildRangeTextSearch($id, $label1, $label2, $suffix,null,$from,$to);
                }
                else
                    $retVal .= $this->BuildInputTextSearch($id, $label, $suffix,$defaultValue);
                break;
            case Self::CHECK:
                $retVal .= $this->BuildInputCheckSearch($id, $label, $labelArr, $valueArr, $inline,$defaultValue);
                break;
            case Self::RADIO:
                $retVal .= $this->BuildInputRadioSearch($id, $label, $labelArr, $valueArr, $inline,$defaultValue);
                break;
            case Self::SELECT:
                $retVal .= $this->BuildInputSelectSearch($id, $label, $labelArr, $valueArr, $multiple, $url, $zero,$defaultValue);
                break;
            case Self::DATETIME:
                $retVal .= $this->BuildDateSearch($id, $label,$defaultValue,$from,$to,$defaultValue);
                break;
            case Self::TEXT_MULTI:
                $retVal .= $this->BuildTextMultiInputSearch($id, $label, $url, $textMultiEnterNew, $maxlength, $instantPopSearch,$defaultValue);
                break;
            case Self::BIRTHDAY:
                $retVal .= $this->BuildBirthdaySearch($id, $label,$day,$month,$year);
                break;
            default:
                throw new Exception('INPUT TYPE SEARCH Not implemented.'.$inputType);
        }
        return $retVal;
    }

    //**************** Search form helper *******************/

    private function BuildInputTextSearch($id, $label, $suffix, $attr = null,$defaultValue = '') {
        $retVal = '<div class="form-group"><label for="' . $id . '" class="col-sm-2 control-label">' . $label . '</label><div class="col-sm-8">';
        $retVal .= '<input type="text" id="' . $id . '" name="' . $id . '" class="form-control" maxlength="1000"';
		if ($defaultValue)
            $retVal .= ' value="' . htmlspecialchars($defaultValue) . '"';
        $retVal .= '/>';
		
        if ($suffix)
            $retVal .= '<span class="midsearch">' . $suffix . '</span>';
        return $retVal . '</div></div>';
    }

    private function BuildRangeTextSearch($id, $label1, $label2, $suffix = null, $attr = null,$from = null,$to = null) {
		if ($from)  $from = $this->formatNum($from);
		if ($to)  $to = $this->formatNum($to);
        $retVal = '<div class="form-group"><label for="f' . $id . '" class="col-sm-2 control-label">' . $label1 . '</label><div class="col-sm-8">';
        $retVal .= '<div class="col-sm-4 nopaddingleft"><input type="text" id="f' . $id . '" name="f' . $id . '" class="rangesearch numeric form-control" maxlength="1000" value= "'.$from.'" /></div>';
        $retVal .= '<span class="midsearch">' . $label2 . '</span>';
        $retVal .= '<div class="col-sm-4 nopaddingright"><input type="text" id="t' . $id . '" name="t' . $id . '" class="rangesearch numeric form-control" maxlength="1000" value= "'.$to.'" /></div>';
        if ($suffix)
            $retVal .= '<span class="midsearch">' . $suffix . '</span>';
        return $retVal . '</div></div>';
    }

    private function BuildBirthdaySearch($id, $label,$day,$month,$year) {
        $retVal = '<div class="form-group"><label for="d' . $id . '" class="col-sm-2 control-label">' . $label . '</label><div class="controls">';
        //select ngay
        $retVal .= '<select class="span s2s" id="d' . $id . '" name="d' . $id . '">';
        $retVal .= '<option value="0">' . (defined('_S_C_NGAY') ? constant('_S_C_NGAY') : '_S_C_NGAY') . '</option>';
        for ($ii = 1; $ii < 32; $ii++) {
            $retVal .= '<option value="' . $ii .'"';
			if($ii == $day) $retVal .= ' selected';
			$retVal .= '>' . $ii . '</option>';
        }
        $retVal .= '</select>';
        //select thang
        $retVal .= '<select class="span s2s" id="m' . $id . '" name="m' . $id . '">';
        $retVal .= '<option value="0">' . (defined('_S_C_THANG') ? constant('_S_C_THANG') : '_S_C_THANG') . '</option>';
        for ($ii = 1; $ii < 13; $ii++) {
            $retVal .= '<option value="' . $ii.'"';
			if($ii == $month)
			$retVal .= ' selected';
			$retVal .= '>' . $ii . '</option>';
        }
        $retVal .= '</select>';
        //select nam
        $currY = date('Y');
        $maxY = 100;
        $retVal .= '<select class="span s2s" id="y' . $id . '" name="y' . $id . '">';
        $retVal .= '<option value="0">' . (defined('_S_C_NAM') ? constant('_S_C_NAM') : '_S_C_NAM') . '</option>';
        for ($ii = $currY - $maxY; $ii <= $currY; $ii++) {
            $retVal .= '<option value="' . $ii.'"';
			if($ii == $year)
			$retVal .= ' selected';
			$retVal .= '>' . $ii . '</option>';
        }
        $retVal .= '</select>';
        return $retVal . '</div></div>';
    }

    private function BuildDateSearch($id, $label, $attr = null,$from=0,$to=0,$defaultValue=null) { 

		if(!$from) $from = '0';
		if(!$to) $to = '0';
        $retVal = '<div class="form-group"><label for="f' . $id . '_d" class="col-sm-2 control-label">' . $label . '</label><div class="daterange">';
        //select
        $retVal .= '<select class="form-control col-sm-3 s1s" id="' . $id . '" name="' . $id . '">';
        $retVal .= '<option value="0">' . (defined('_S_FT') ? constant('_S_FT') : '_S_FT') . '</option>';
        $retVal .= '<option value="currD"';
		if ($defaultValue == 'currD')  $retVal .= ' selected';
		$retVal .='>' . _S_CR_D . '</option>';
		
		$retVal .= '<option value="currD7"';
		if ($defaultValue == 'currD7')  $retVal .= ' selected';
		$retVal .='>' . _S_CR_D7 . '</option>';                 
		
		$retVal .= '<option value="currD30"';
		if ($defaultValue == 'currD30')  $retVal .= ' selected';
		$retVal .='>' . _S_CR_D30 . '</option>';
		
		$retVal .= '<option value="currW"';
		if ($defaultValue == 'currW')  $retVal .= ' selected';
		$retVal .='>' . _S_CR_W . '</option>';
		
		$retVal .= '<option value="currM"';
		if ($defaultValue == 'currM')  $retVal .= ' selected';
		$retVal .='>' . _S_CR_M . '</option>';
		
		$retVal .= '<option value="currQ"';
		if ($defaultValue == 'currQ')  $retVal .= ' selected';
		$retVal .='>' . _S_CR_Q . '</option>';
		
		$retVal .= '<option value="currY"';
		if ($defaultValue == 'currY')  $retVal .= ' selected';
		$retVal .='>' . _S_CR_Y . '</option>';
        $retVal .= '</select>';
        //from
        $retVal .= '<input type="text" id="f' . $id . '_d" name="f' . $id . '_d" class="col-sm-2 dtsearch" maxlength="10"/>';
        $this->mscript .= 'cakf' . $id . '=new Geekutil.Cal("f' . $id . '",true,' . $from . ',0);';
        $retVal .= '<input t="d" type="hidden" id="f' . $id . '" name="f' . $id . '"/>';
        $retVal .= '<span class="help-inline">';
        $retVal .= '<i class="glyphicon glyphicon-calendar click" id="f' . $id . '_i"></i></span><span class="midsearch"><i class="icon-minus"></i></span>';
        //to
        $retVal .= '<input type="text" id="t' . $id . '_d" name="t' . $id . '_d" class="col-sm-2 dtsearch" maxlength="10"/>';
        $this->mscript .= 'cakt' . $id . '=new Geekutil.Cal("t' . $id . '",true,'. $to.',0);';
        $this->mscript .= 'sCak("' . $id . '");';
		if (false == in_array($defaultValue,array('currD', 'currM', 'currQ', 'currY', 'currW', 'currD7', 'currD30'), true)) {
			$this->mscript .= 'window["cakf'.$id.'"].set('.$from.');';
			$this->mscript .= 'window["cakt'.$id.'"].set('.$to.');';
		}		
        $retVal .= '<input t="d" type="hidden" id="t' . $id . '" name="t' . $id . '"/>';
        $retVal .= '<span class="help-inline">';
        $retVal .= '<i class="glyphicon glyphicon-calendar" id="t' . $id . '_i"></i></span>';
        $this->_mcallib = true;
        return $retVal . '</div></div>';
    }

    private function BuildInputSelectSearch($id, $label, $labelArr, $valueArr, $multiple = false, $url = null, $zero = 1,$defaultValue=null) {
        $retVal = '<div class="form-group"><label for="' . $id . '" class="col-sm-2 control-label">' . $label . '</label><div class="col-sm-8">';
        if (is_array($labelArr)) {
            $retVal .= '<select class="form-control" name="' . $id . '" id="' . $id . '"';
            if ($multiple)
                $retVal .= ' multiple="multiple"';
            $retVal .= '>';
            if ($zero)
                $retVal .= '<option value="' . $this->_noval . '"> Chọn </option>';
				 for ($i = 0; $i < count($labelArr); $i++) {
					$retVal .= '<option value="' . $valueArr[$i] . '"';
					if(null !== $defaultValue && $defaultValue == $valueArr[$i]) $retVal .= ' selected';
					$retVal .= '>' . $labelArr[$i] . '</option>';
				}
            $retVal .= '</select>';
            return $retVal . '</div></div>';
        }
        if ($url) {
			if ($defaultValue) {
                if (is_array($defaultValue))
                    $defaultValue = implode(',', $defaultValue);
                $this->mscript .= 'loadAndFillSelect("' . $id . '","' . $url . '","' . $defaultValue . '",' . $zero . ');';
            }
            else
                $this->mscript .= 'loadAndFillSelect("' . $id . '","' . $url . '","",' . $zero . ');';
            $retVal .= '<select class="form-control" name="' . $id . '" id="' . $id . '"';
            if ($multiple)
                $retVal .= ' multiple="multiple"';
            $retVal .= '></select>';
            return $retVal . '</div></div>';
        }
        $retVal .= '<select class="form-control" name="' . $id . '" id="' . $id . '"></select>';
        return $retVal . '</div></div>';
    }

    private function BuildInputCheckSearch($id, $label, $labelArr, $valueArr, $inline = false,$defaultValue = null) {
        if (is_array($labelArr)) {
            $retVal = '<div class="form-group"><label for="' . $id . '" class="col-sm-2 control-label">' . $label . '</label><div class="col-sm-8"><div class="checkbox"><ul class="col2">';
            for ($i = 0; $i < count($labelArr); $i++) {
                $retVal .= '<li><label class="' . ($inline ? 'checkbox-inline' : '') . '">';
                $retVal .= '<input type="checkbox" name="' . $id . '[]" id="' . $id . '_' . $i . '" value="' . $valueArr[$i] . '"';
                 if (null !== $defaultValue && $defaultValue == $valueArr[$i] && strtolower($defaultValue) != 'n')
				$retVal .= 'checked';
				$retVal .= '/>' . $labelArr[$i] . '</label></li>';
            }
            return $retVal . '</ul></div></div></div>';
        }
        $retVal = '<div class="form-group"><div class="col-sm-offset-2 col-sm-8"><div class="checkbox">';
        $retVal .= '<label><input value="1" type="checkbox" id="' . $id . '" name="' . $id.'"';
		if (null !== $defaultValue && strtolower($defaultValue) != 'n')
				$retVal .= 'checked';
        return $retVal .'/>'.$label.'</label></div></div></div>';
    }

    private function BuildInputRadioSearch($id, $label, $labelArr, $valueArr, $inline = false,$defaultValue=null) {
        if (is_array($labelArr)) {
            $retVal = '<div class="form-group"><div class="col-sm-offset-2 col-sm-8"><div class="radio">';
            for ($i = 0; $i < count($labelArr); $i++) {
                $retVal .= '<label class="' . ($inline ? 'radio-inline' : '') . '">';
                $retVal .= '<input type="radio" name="' . $id . '" id="' . $id . '_' . $i . '" value="' . $valueArr[$i] . '"';
				if (null !== $defaultValue && $defaultValue == $valueArr[$i])
                    $retVal .= ' checked';
				$retVal .= '/>' . $labelArr[$i] . '</label>';
            }
            return $retVal . '</div></div>';
        }
        throw new Exception('Wrong RADIO Search data. Missing data source.');
    }

    /** Text multi input
     * */
    private function BuildTextMultiInputSearch($id, $label, $url, $allowNew = false, $maxlength = null, $instantPopSearch = null,$defaultValue = null) {
        $this->_mtextboxlib = true;
        $retVal = '<div class="form-group"><label for="' . $id . '" class="col-sm-2 control-label">' . $label . '</label><div class="col-sm-8">';
        $max = 0;
        if ($maxlength)
            $max = $maxlength;

        $allEnterNew = ($allowNew) ? 'true' : 'false';
		$value = 'null';
		if ($allowNew && $defaultValue) {
            $arr = explode(',', $defaultValue);
            $defaultValue = '"' . implode('","', $arr) . '"';
        }
        
        $retVal .= '<input t="tm" type="text" id="' . $id . '" name="' . $id . '" class="form-control tm" value="'.$defaultValue.'"/>';
        $retVal .= '<span class="help-inline">';
        $retVal .= '<i id="_s' . $id . '" class="icon-search click"></i>';
        $retVal .= '<i id="_r' . $id . '" class="icon-eraser click"></i></span>';

        if ($defaultValue)
            $value = '[' . $defaultValue . ']';
        $maxstr = 'null';
        if ($max)
            $maxstr = $max;
        $istPopSearchStr = 'null';
        if (null != $instantPopSearch) {
            $objarr = array('L' => $label, 'M' => 1);
            if ('s' == strtolower($instantPopSearch))
                $objarr['M'] = 0;
            $istPopSearchStr = json_encode($objarr);
        }
        $urljs = '"' . $url . '"';
        if(is_array($url)) $urljs = json_encode($url);
        $this->mscript .= 'tk' . $id . '=new Geekutil.TM("' . $id . '",' . $allEnterNew . ',' . $urljs . ',' . $value . ',' . $maxstr . ',' . $istPopSearchStr . ');';
		$multi = 1;
		if($maxlength == 1) $multi = 0;
		
        return $retVal . '</div></div>';
    }
    
    
    /************************************************* SEARCH HELPER ****************************** */
    /* Get time with zeros hour minute second
     * */
    private function getStartTime($timestamp = null, $prev = 0) {
        $prevtimestamp = 0;
		if (0 < $prev) $prevtimestamp = ($prev - 1) * 86400;
		if ($timestamp)
            return  strtotime(date('Y-m-d 00:00:00', $timestamp)) - $prevtimestamp;
        return  strtotime(date('Y-m-d 00:00:00')) - $prevtimestamp;
    }

    /* Get end time of given day - 23:59:59
     * */

    private function getEndTime($timestamp = null) {
        if ($timestamp)
            return strtotime(date('Y-m-d 23:59:59', $timestamp));
        return strtotime(date('Y-m-d 23:59:59'));
    }
	/* Get start time of current year
     * */
    private function getStartYear() {
        return strtotime(date('Y-01-01 00:00:00'));
    }
	/* Get end time of current year
    */
    private function getEndYear() {
        return strtotime(date('Y-12-31 23:59:59'));
    }
    /* Get start time of current month
     * */
    private function getStartMonth() {
        return strtotime(date('Y-m-01 00:00:00'));
    }

    /* Get end time of current month
     * */
    private function getEndMonth() {
        return strtotime(date('Y-m-t 23:59:59'));
    }

    /* Get start time of current week
     * */
    private function getStartWeek() {
        if (date('w') == 1)
            return strtotime(date('Y-m-d 00:00:00'));
        return strtotime( date('Y-m-d 00:00:00', strtotime('Last Monday')) );
    }

    /* Get end time of current week
     * */
    private function getEndWeek() {
        if (date('w') == 0)
            return strtotime(date('Y-m-d 23:59:59'));
        return strtotime(date('Y-m-d 23:59:59', strtotime('Next Sunday')));
    }

    /* Get start time of current week
     * */
    private function getStartQuarter() {
        $currentY = date('Y');
        $currentQ = ceil(date('m') / 3);
        $q1 = gmmktime(0, 0, 0, 1, 1, $currentY);
        $q2 = gmmktime(0, 0, 0, 4, 1, $currentY);
        $q3 = gmmktime(0, 0, 0, 7, 1, $currentY);
        $q4 = gmmktime(0, 0, 0, 10, 1, $currentY);
        switch ($currentQ) {
            case 1: return $q1;
            case 2: return $q2;
            case 3: return $q3;
            case 4: return $q4;
        }
    }

    /* Get end time of current week
     * */
    private function getEndQuarter() {
        $currentY = date('Y');
        $currentQ = ceil(date('m') / 3);
        $q1 = gmmktime(0, 0, 0, 3, 31, $currentY);
        $q2 = gmmktime(0, 0, 0, 6, 30, $currentY);
        $q3 = gmmktime(0, 0, 0, 9, 30, $currentY);
        $q4 = gmmktime(0, 0, 0, 12, 31, $currentY);
        switch ($currentQ) {
            case 1: return $q1;
            case 2: return $q2;
            case 3: return $q3;
            case 4: return $q4;
        }
    }

    /* remove comments, special characters before using in query
     * */
    private function getSQLText($text) {
        return  addcslashes( $text, '_%\\\'' );
    }

    private function BuildTextLikeCondition($colname, $text) {
		$textN = '';
		if($text) $textN = str_replace('d', 'đ', $text);
		if($textN == $text) return $colname . ' LIKE \'%' . $this->getSQLText($text).'%\'';
        return $colname . ' LIKE \'%' . $this->getSQLText($text) . '%\' OR '.$colname . ' LIKE \'%' . $this->getSQLText($textN) . '%\'';
    }

    function BuildSingleCondition($data) {
        $retVal = ' AND'; //$data['op'];
        $type = strtoupper($data['type']);
		
        //date
        if ('DATE' == $type) {
            $retVal .= ' (1';
            $colname = ($data['colname']);
            //handle special code
            if (isset($data['v'])) {
                if ('currM' == $data['v']) {
                    //current month
                    $retVal .= ' AND ' . $this->getStartMonth() . '<=' . $colname . ' AND ' . $colname . '<=' . $this->getEndMonth();
                }
                if ('currY' == $data['v']) {
                    //current year
					$retVal .= ' AND ' . $this->getStartYear() . '<=' . $colname . ' AND ' . $colname . '<=' . $this->getEndYear();
                }
                if ('currD' == $data['v']) {
                    //current day
                    $retVal .= ' AND ' . $this->getStartTime() . '<=' . $colname . ' AND ' . $colname . '<=' . $this->getEndTime();
                }
				if ('currD7' == $data['v']) {
                    //within 7 days
                    $retVal .= ' AND ' . $this->getStartTime(null, 7) . '<=' . $colname . ' AND ' . $colname . '<=' . $this->getEndTime();
                }
				if ('currD30' == $data['v']) {
                    //within 30 days
                    $retVal .= ' AND ' . $this->getStartTime(null, 30) . '<=' . $colname . ' AND ' . $colname . '<=' . $this->getEndTime();
                }
                if ('currW' == $data['v']) {
                    //current week
                    $retVal .= ' AND ' . $this->getStartWeek() . '<=' . $colname . ' AND ' . $colname . '<=' . $this->getEndWeek();
                }
                if ('currQ' == $data['v']) {
                    //current quarter
                    $retVal .= ' AND ' . $this->getStartQuarter() . '<=' . $colname . ' AND ' . $colname . '<=' . $this->getEndQuarter();
                }
            } else {
                //normal
                if (isset($data['from'])) {
                    $retVal .= ' AND ' . $this->getStartTime($data['from']) . '<=' . $colname;
                }
                if (isset($data['to'])) {
                    $retVal .= ' AND ' . $colname . '<=' . $this->getEndTime($data['to']);
                }
            }
            $retVal .= ')';
        }
        //text
        if ('TEXT' == $type) {
            $arrColname = explode(',', $data['colname']);
            $arrColCond = array();
            //Merged data?
            if (is_array($data['v'])) {
                //merged data
                $retVal .= ' 1';
                foreach ($data['v'] as $vsearch) {
                    $retVal .= ' AND (1';
                    $arrColCond = array();
                    foreach ($arrColname as $colname) {
						
                        $arrColCond[] = $this->BuildTextLikeCondition(($colname), $vsearch);
                    }
                    $retVal .= ' AND (' . implode(' OR ', $arrColCond) . ')';
                    $retVal .= ')';
                }
            } else {
                //single data
                foreach ($arrColname as $colname) {
					
                    $arrColCond[] = $this->BuildTextLikeCondition(($colname), $data['v']);
                }
                $retVal .= ' (' . implode(' OR ', $arrColCond) . ')';
            }
        }
        //range
        if ('NUMERIC' == $type) {
            $retVal .= ' (1';
            $colname = ($data['colname']);
			
            if (isset($data['from'])) {
                $retVal .= ' AND ' . $data['from'] . '<=' . $colname;
            }
            if (isset($data['to'])) {
                $retVal .= ' AND ' . $colname . '<=' . $data['to'];
            }
            $retVal .= ')';
        }
        //select/text_multi
        if ('RADIO' == $type || 'SELECT' == $type || 'TEXT_MULTI' == $type || 'CHECK' == $type) {
            $colname = ($data['colname']);

			if('TEXT_MULTI' == $type || (isset($data['many']) && intval($data['many']))){
				$retVal .= ' (0';
				$arrvalue = explode(',', $data['v']);
				foreach ($arrvalue as $v) {
					$retVal .= ' OR ' . $colname . ' = \'' . $v . '\'';
				}
				$retVal .= ')';
			}else
            	$retVal .= ' (' . $colname . ' IN (' . $data['v'] . '))';
        }
        return $retVal;
    }

    /************************************************* END OF SEARCH HELPER *******************************/
	
}