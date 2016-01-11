<?php
class Geek_Helper_Cleanstring
{
	function cleanstring($str) {
		$str = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ|À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ|A)/", 'a', $str);
		$str = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ|È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/", 'e', $str);
		$str = preg_replace("/(ì|í|ị|ỉ|ĩ|Ì|Í|Ị|Ỉ|Ĩ|I)/", 'i', $str);
		$str = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ|Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/", 'o', $str);
		$str = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ|Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ|U)/", 'u', $str);
		$str = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ|Ỳ|Ý|Ỵ|Ỷ|Ỹ)/", 'y', $str);
		$str = preg_replace("/(đ|Đ|D)/", 'd', $str);
		$str = preg_replace("/(B)/", 'b', $str);
		$str = preg_replace("/(C)/", 'c', $str);
		$str = preg_replace("/(G)/", 'g', $str);
		$str = preg_replace("/(L)/", 'l', $str);
		$str = preg_replace("/(M)/", 'm', $str);
		$str = preg_replace("/(N)/", 'n', $str);
		$str = preg_replace("/(H)/", 'h', $str);
		$str = preg_replace("/(T)/", 't', $str);
		$str = preg_replace("/(K)/", 'k', $str);
		$str = preg_replace("/(S)/", 's', $str);
		$str = preg_replace("/(R)/", 'r', $str);
		$str = preg_replace("/(V)/", 'v', $str);
		$str = preg_replace("/(Y)/", 'y', $str);
		$str = preg_replace("/(W)/", 'w', $str);
		$str = preg_replace("/(P)/", 'p', $str);
		$str = str_replace(" ", "-", $str);
		$str = str_replace("--", "-", $str);
		$str = str_replace("?", "", $str);
		$str = str_replace("(", "", $str);
		$str = str_replace(")", "", $str);
		$str = str_replace("%", "", $str);
		return $str;
	}
	/**
	 * loai bo dau cua chuoi
	 */
	public static function utf8_to_ascii($fragment){
		$fragment = mb_convert_encoding($fragment,'HTML-ENTITIES', 'UTF-8');
		$find = array('&#768;','&#769;','&#770;','&#771;','&#772;','&#773;','&#774;','&#775;','&#803;','&#777;');
		$fragment = str_replace($find,'',$fragment);
		$find = array('&#232;','&#233;','&#7865;','&#7867;','&#7869;','&#234;','&#7873;','&#7871;','&#7879;','&#7875;','&#7877;','&eacute;','&egrave;','&ecirc;');
		$fragment = str_replace($find,'e',$fragment);
		
		$find = array('&#224;','&#225;','&#7841;','&#7843;','&#227;','&#226;','&#7847;','&#7845;','&#7853;','&#7849;','&#7851;','&#259;','&#7857;','&#7855;','&#7863;','&#7859;','&#7861;','&aacute;','&agrave;','&acirc;','&atilde;','&aring;');
		$fragment = str_replace($find,'a',$fragment);
		
		$find = array('&#236;','&#237;','&#7883;','&#7881;','&#297;','&iacute;','&igrave;');
		$fragment = str_replace($find,'i',$fragment);
	   
		$find = array('&#242;','&#243;','&#7885;','&#7887;','&#245;','&#244;','&#7891;','&#7889;','&#7897;','&#7893;','&#7895;','&#417;','&#7901;','&#7899;','&#7907;','&#7903;','&#7905;','&oacute;','&ograve;','&ocirc;','&otilde;');
		$fragment = str_replace($find,'o',$fragment);
		
		$find = array('&#249;','&#250;','&#7909;','&#7911;','&#361;','&#432;','&#7915;','&#7913;','&#7921;','&#7917;','&#7919;','&uacute;','&ugrave;');
		$fragment = str_replace($find,'u',$fragment);
		
		$find = array('&#7923;','&#253;','&#7925;','&#7927;','&#7929;','&yacute;');
		$fragment = str_replace($find,'y',$fragment);
		
		$find = array('&#273;');
		$fragment = str_replace($find,'d',$fragment);
		
		$find = array('&#192;','&#193;','&#7840;','&#7842;','&#195;','&#194;','&#7846;','&#7844;','&#7852;','&#7848;','&#7850;','&#258;','&#7856;','&#7854;','&#7862;','&#7858;','&#7860;','&Aacute;','&Agrave;','&Acirc;','&Atilde;','&Aring;');
		$fragment = str_replace($find,'A',$fragment);
		
		$find = array('&#200;','&#201;','&#7864;','&#7866;','&#7868;','&#202;','&#7872;','&#7870;','&#7878;','&#7874;','&#7876;','&Ecirc;','&Egrave;','&Eacute;');
		$fragment = str_replace($find,'E',$fragment);
		
		$find = array('&#204;','&#205;','&#7882;','&#7880;','&#296;','&Iacute;','&Igrave;');
		$fragment = str_replace($find,'I',$fragment);
		
		$find = array('&#210;','&#211;','&#7884;','&#7886;','&#213;','&#212;','&#7890;','&#7888;','&#7896;','&#7892;','&#7894;','&#416;','&#7900;','&#7898;','&#7906;','&#7902;','&#7904;','&Oacute;','&Ograve;','&Ocirc;','&otilde;');
		$fragment = str_replace($find,'O',$fragment);
		
		$find = array('&#217;','&#218;','&#7908;','&#7910;','&#360;','&#431;','&#7914;','&#7912;','&#7920;','&#7916;','&#7918;','&Uacute;','&Ugrave;');
		$replace = 'U';
		$fragment = str_replace($find,'U',$fragment);
		
		$find = array('&#7922;','&#221;','&#7924;','&#7926;','&#7928;','&Yacute;');
		$fragment = str_replace($find,'Y',$fragment);
		
		$find = array('&#272;');
		$fragment = str_replace($find,'D',$fragment);
		return $fragment;
	}
}
?>