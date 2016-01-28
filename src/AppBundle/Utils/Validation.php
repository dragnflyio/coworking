<?php
namespace AppBundle\Utils;
use Symfony\Component\Config\Definition\Exception\Exception;
class Validation{
	private $em = null;
	function __construct($em) {
		$this->em = $em->getConnection();
  }
	/**
	 * Check if member has active package or not,
	 * or this member belongs to a group which have active package
	 * @return empty string or error string
	 */
	function checkMemberPackage($memberid){
		// Has active package

		if ($row = $this->em->fetchAssoc('SELECT packageid FROM member_package WHERE memberid = ? AND 1 = active', array($memberid))){
			if ($package_name = $this->em->fetchColumn('SELECT name FROM package WHERE id = '. $row['packageid'])){
				return "Khách hàng này đang dùng gói {$package_name}, đóng gói hiện tại trước khi đăng kí gói mới.";
			}
		}
		// Belong a group?
		if ($row = $this->em->fetchAssoc('SELECT groupid FROM group_member WHERE memberid = ?', array($memberid))){
			// group has active package?
			if ($group_package = $this->em->fetchAssoc('SELECT packageid FROM group_package WHERE 1 = active AND groupid = '. $row['groupid'])){
				if ($package_name = $this->em->fetchColumn('SELECT name FROM package WHERE id = '. $group_package['packageid'])){
					return "Khách hàng này nằm trong một nhóm đang dùng gói {$package_name}, đóng gói hiện tại trước khi đăng kí gói mới";
				}
			}
		}
		return '';
	}
	/**
	 * Update new member package to opening checked in session
	 */
	function updateSessionNewPackage($old_memberpackageid, $new_memberpackageid){
	  return $this->em->executeUpdate('UPDATE customer_timelog SET memberpackageid = ? WHERE memberpackageid = ? AND 1 = status', array($new_memberpackageid, $old_memberpackageid));
	}
	/**
	 * Get current active package for a member
	 */
	function getMemberPackage($memberid){
		$ret = array();
		if ($row = $this->em->fetchAssoc('SELECT mp.id, mp.efftoextend,mp.price, mp.maxhours, mp.maxdays, packageid, p.name AS packagename, mp.efffrom, mp.effto FROM member_package mp LEFT JOIN package p ON mp.packageid = p.id WHERE memberid = ? AND 1 = mp.active', array($memberid))){
		    // remainder, so tien con du?
        $row['remain'] = $row['price'];
        $row['print'] = $this->getPrintedPaper($row['id']);
		    if (0 < $row['maxhours']){
		        // Goi tinh gio, se tinh toan so gio da dung
		        $used_minutes = $this->getUsedHours($row['id']);
		        if ($used_minutes) {
		            // so tien tuong ung
		            $fee = $row['price'] / ($row['maxhours'] * 60) * $used_minutes;
		            // so du
		            $row['remain'] = $row['price'] - $fee;
		        }
		    } else {
		        // Goi tinh ngay, count up to today
		        $today = mktime(0,0,0);
		        $diff = max(0, $today - $row['efffrom'])/ 86400;// convert second to day
		        $fee = $row['price'] / $row['maxdays'] * $diff;
		        // so du
	          $row['remain'] = $row['price'] - $fee;
		    }
			$ret = $row;
		}
		return $ret;
	}
	/**
	 * Get used visitor hours of a member in a package
	 */
	function getVisitorHours($memberpackageid){
	  // group by day
	  $log = $this->em->fetchAll('SELECT DATE(FROM_UNIXTIME(checkin)) AS datecheckin, checkin, checkout FROM customer_timelog WHERE 1=status AND isvisitor = 1 AND memberpackageid = ? ORDER BY checkin', array($memberpackageid));
	  $max_hour = 3;
	  $max_visitor = 2;
	  $retval = $log_data = array();
    if ($log){
      foreach($log as $check){
        if ($check['checkout']){
          $date = $check['datecheckin'];
          if (false == isset($log_data[$date])) $log_data[$date] = array();
          $log_data[$date][] = array($check['checkin'], $check['checkout']);
        }
      }
      foreach ($log_data as $date => $day_log){
        // moi ngay, tinh thoi gian visitor
        $all_segments = array();
        foreach ($day_log as $check){
          $all_segments[] = $check[0];
          $all_segments[] = $check[1];
        }
        $all_segments = array_unique($all_segments);
        sort($all_segments, SORT_NUMERIC);
        $count = count($all_segments);
        $retval[$date] = array();
        for($ii = 0; $ii < $count; $ii++){
          if ($ii < $count - 1){
            $start = $all_segments[$ii];
            $end = $all_segments[$ii + 1];
            $key = $start.'-'.$end;
            $retval[$date][$key] = 0;
            // Tinh tat ca cac doan thoi gian, dem so khach voi tung doan
            foreach ($day_log as $check){
              if($check[0] <= $start && $start <= $check[1] && $check[0] <= $end && $end <= $check[1]){
                $retval[$date][$key]++;
              }
            }
          }
        }
      }// End for log_data      
    }
    // Summarize 
    $member_package = $this->em->fetchAssoc('SELECT maxvisitors,visitorprice FROM member_package WHERE id = ?', array($memberpackageid));
    $over_price_visitor = (int)$member_package['visitorprice'];// price over visitor per hour
    $quota = $max_hour * $max_visitor;// quota per day
    $date_visitor_minutes = array();
    foreach ($retval as $date => $visitor_data){
      $total_visitor = 0;
      $over = $charge = 0;
      foreach ($visitor_data as $time_range => $number_visitor){
        $arr = explode('-', $time_range);
        $time = ceil(((int)$arr[1] - (int)$arr[0]) / 60);
        if ($max_visitor < $number_visitor){
          // Vuot qua so khach cho phep
          $total_visitor += $max_visitor * $time;
          $over += ($number_visitor - $max_visitor) * $time;
        } else {
          $total_visitor += $number_visitor * $time;
        }
      }
      $charge = $over * $over_price_visitor;
      $date_visitor_minutes[$date] = array('visit' => $total_visitor, 'over' => $over, 'charge' => $charge);
    }
    return $date_visitor_minutes;
    // return $retval;
	}
	/**
	 * Get used visitor hours of all members of a group in a package
	 */
	function getVisitorHoursOfGroup($grouppackageid){
	  // group by day
	  $log = $this->em->fetchAll('SELECT DATE(FROM_UNIXTIME(checkin)) AS datecheckin, checkin, checkout FROM customer_timelog WHERE 1=status AND isvisitor = 1 AND grouppackageid = ? ORDER BY checkin', array($grouppackageid));
	  
	  $retval = $log_data = array();
    if ($log){
      foreach($log as $check){
        if ($check['checkout']){
          $date = $check['datecheckin'];
          if (false == isset($log_data[$date])) $log_data[$date] = array();
          $log_data[$date][] = array($check['checkin'], $check['checkout']);
        }
      }
      foreach ($log_data as $date => $day_log){
        // moi ngay, tinh thoi gian visitor
        $all_segments = array();
        foreach ($day_log as $check){
          $all_segments[] = $check[0];
          $all_segments[] = $check[1];
        }
        $all_segments = array_unique($all_segments);
        sort($all_segments, SORT_NUMERIC);
        $count = count($all_segments);
        $retval[$date] = array();
        for($ii = 0; $ii < $count; $ii++){
          if ($ii < $count - 1){
            $start = $all_segments[$ii];
            $end = $all_segments[$ii + 1];
            $key = $start.'-'.$end;
            $retval[$date][$key] = 0;
            // Tinh tat ca cac doan thoi gian, dem so khach voi tung doan
            foreach ($day_log as $check){
              if($check[0] <= $start && $start <= $check[1] && $check[0] <= $end && $end <= $check[1]){
                $retval[$date][$key]++;
              }
            }
          }
        }
      }// End for log_data      
    }
    // Summarize
    $group_package = $this->em->fetchAssoc('SELECT maxvisitors,visitorprice FROM group_package WHERE id = ?', array($grouppackageid));
    $quota = (int)$group_package['maxvisitors'] * 60;// quota per day, minute x visitor
    $over_price_visitor = (int)$group_package['visitorprice'];// price over visitor per hour
    $date_visitor_minutes = array();
    foreach ($retval as $date => $visitor_data){
      $total_visitor = 0;
      $over = $charge = 0;
      foreach ($visitor_data as $time_range => $number_visitor){
        $arr = explode('-', $time_range);
        $time = ceil(((int)$arr[1] - (int)$arr[0]) / 60);        
        $total_visitor += $number_visitor * $time;        
      }
      if ($quota < $total_visitor){
        $over = $total_visitor - $quota;
        $charge = $over * $over_price_visitor;
      }
      
      $date_visitor_minutes[$date] = array('visit' => $total_visitor, 'over' => $over, 'charge' => $charge);
    }
    return $date_visitor_minutes;
	  
	}
	/**
	 * Get used hours of member in a package
	 * @return used hours in minutes
	 */
	function getUsedHours($member_packageid){
	    $retval = 0;
	    $log = $this->em->fetchAll('SELECT checkin, checkout FROM customer_timelog WHERE 1=status AND isvisitor = 0 AND memberpackageid = ?', array($member_packageid));
	    if ($log){
	        foreach($log as $check){
	            if ($check['checkout']){
	                $retval += max(0, $check['checkout'] - $check['checkin']) / 60;// Convert second to minute
	            }
	        }
	    }
	    return $retval;
	}
	/**
	 * Get printed papers of member in a package
	 * @return int
	 */
	function getPrintedPaper($member_packageid){
	    $retval = 0;
	    $log = $this->em->fetchAll('SELECT printedpapers FROM customer_timelog WHERE 1=status AND isvisitor = 0 AND memberpackageid = ?', array($member_packageid));
	    if ($log){
	        foreach($log as $check){
	            if ($check['printedpapers']){
	                $retval += max(0, $check['printedpapers']);// Convert second to minute
	            }
	        }
	    }
	    return $retval;
	}
	function extendMemberPackage($memberid, $extend_day, $amount){
	    if ($current_package = $this->getMemberPackage($memberid)){
	        // extend
	        $this->em->executeUpdate('UPDATE member_package SET efftoextend = ? WHERE active = 1 AND memberid = ?', array($extend_day, $memberid));
	        // log activity
	        $log = array(
	           'memberid' => $memberid,
	           'code' => 'giahantam',
	           'oldvalue' => $current_package['effto'],
	           'newvalue' => $extend_day,
	           'createdtime' => time(),
	           'amount' => (int)$amount
	        );
	        $this->em->insert('customer_activity', $log);
	    }

	}

	/**
   * Extend package for a group.
	 */
	function extendGroupPackage($groupid, $extend_day, $amount){
		$effto = $this->em->fetchAssoc('SELECT effto FROM group_package WHERE groupid = ? AND 1 = active', array($groupid));
    if (!empty($effto)){
      // extend
      $this->em->executeUpdate('UPDATE group_package SET efftoextend = ? WHERE active = 1 AND groupid = ?', array($extend_day, $groupid));
      // log activity
      $log = array(
        'groupid' => $groupid,
        'code' => 'giahantam',
        'oldvalue' => $effto['effto'],
        'newvalue' => $extend_day,
        'createdtime' => time(),
        'amount' => (int)$amount
      );
      $this->em->insert('group_activity', $log);
    }
	}

	/**
	 * Closed current active package if it has
	 */
	function closedMemberPackage($memberid){
	    $count = $this->em->executeUpdate('UPDATE member_package SET active = 0 WHERE active = 1 AND memberid = ?', array($memberid));
	    return $count;
	}

	/**
   * Check if group has active package or not
   * or this group belongs to a group which have active package
   * @return empty string or error string
	 */
	function checkGroupPackage($groupid, $efffrom = null, $effto = null){
		$row = $this->em->fetchAssoc('SELECT * FROM `group_package` WHERE groupid=?', array($groupid));
		if (!empty($row)) {
			if ($package_name = $this->em->fetchColumn('SELECT name FROM package WHERE id = '. $row['packageid'])){
				return "Nhóm này nằm trong một nhóm đang dùng gói {$package_name}, đóng gói hiện tại trước khi đăng kí gói mới";
			}
		}
		return '';
	}
}
