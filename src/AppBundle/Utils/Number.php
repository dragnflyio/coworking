<?php
namespace AppBundle\Utils; 
/**
 * helpers for get/format number against configuration
 * @author Nguyen Viet Cuong <vietcuonghn3@gmail.com>
 */
class  helper_number{    

	public static function equalNum($num1, $num2){
		$esp = 0.00001;
		if(abs($num1-$num1) < $esp) {
		    return true;
		}
		return false;
	}
	public static function formatNum($v){
		if(null === $v || ''===$v) return '';		
        return number_format($v, 0, '.', ',');
    }
	public static function getNum($tmp){
        $tmp = str_replace(',', '', $tmp);
        return $tmp;
    }
	
	// Chuyen date thanh timestamp, loai tru sai so do enable DST
	public static function DateToTimestamp($strYMD = null){
		$m = -1*date('I');
		if(is_numeric($strYMD)){
			return ($strYMD - 3600*$m);
		}
		if(null == $strYMD){
			return mktime();
			//$a = strtotime(date('Y-m-d H:i:s'));
		} else 
        	$a = strtotime($strYMD);
        return ($a - 3600*$m);
	}
	// Chuyen timestamp thanh chuoi ngay thang theo dinh dang
	public static function TimestampToDate($format, $timestamp = null){
		if(null == $timestamp) $timestamp = mktime();
		$m = date('I');		
		return date($format, $timestamp - 3600*$m);
	}
	//Chuyen date thanh UTC timestamp
	public static function DateToTimestampUTC($strYMD = null){
		if(null == $strYMD)	return time();			
		$m = 2*date('I') - 1*substr(date('P'),0,3);
		return strtotime($strYMD) + 3600*$m;
	}
	//Chuyen thoi gian timestamp UTC thanh local date
	public static function TimestampUTCToDate($format, $timestamp = null){
		if(null == $timestamp) $timestamp = mktime();
		$m = 2*date('I') - 1*substr(date('P'),0,3);		
		return date($format, $timestamp + 3600*$m);
	}
	// Tinh toan sai khac (theo giay), date 1 - date 2, Y-m-d H:i:s date string format
	public static function DateDiff($strDate1 = null, $strDate2 = null, $notime = true){
		$format = 'Y-m-d H:i:s';
		if($notime) $format = 'Y-m-d 00:00:00';
		if(null == $strDate1) $strDate1 = self::TimestampToDate($format);
		if(null == $strDate2) $strDate2 = self::TimestampToDate($format);
		if($notime){
			$strDate1 = substr($strDate1, 0, 10).' 00:00:00';
			$strDate2 = substr($strDate2, 0, 10).' 00:00:00';
		}
		$dt1 = self::DateToTimestamp($strDate1);
		$dt2 = self::DateToTimestamp($strDate2);		
		return ($dt1 - $dt2)/(24*3600);
	}
	
	public static function formatPhone($v){
		if(null === $v || ''===$v) return '';
		$find = array(PHP_EOL);
		$v = str_replace($find,'',$v);
        $chinfo = $_SESSION['cauhinhInfo'];
		$chdienthoai = $chinfo['dinh_dang_dien_thoai'];
		$arr = explode(',', $v);
		foreach($arr as &$num){
			if($chdienthoai == 1 || $chdienthoai == 4){
				$dau_phan_cach = ' ';
			}
			if($chdienthoai == 2 || $chdienthoai == 5){
				$dau_phan_cach = '.';
			}
			if($chdienthoai == 3 || $chdienthoai == 6){
				$dau_phan_cach = '-';
			}
			if($chdienthoai == 1 || $chdienthoai == 2 || $chdienthoai == 3){
				$nhom_1 = substr($num,0,3);
				$nhom_2 = substr($num,3,3);
				$nhom_3 = substr($num,6);
			}
			if($chdienthoai == 4 || $chdienthoai == 5 || $chdienthoai == 6){
				$nhom_1 = substr($num,0,4);
				$nhom_2 = substr($num,4,3);
				$nhom_3 = substr($num,7);
			}
			if($nhom_1 && $nhom_2 && $nhom_3){
				$num = $nhom_1.$dau_phan_cach.$nhom_2.$dau_phan_cach.$nhom_3;
			}elseif($nhom_1 && $nhom_2){
				$num = $nhom_1.$dau_phan_cach.$nhom_2;
			}
			else{
				$num = $nhom_1;
			}

		}
		return implode(',', $arr);
    }
	public static function getPhone($tmp){
		return preg_replace('/[^0-9,]/', '', strip_tags($tmp));
    }
}
