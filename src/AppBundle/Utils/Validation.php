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
	function checkMemberPackage($memberid, $efffrom = null, $effto = null){
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
	 * Get current active package for a member
	 */
	function getMemberPackage($memberid){
		$ret = array();
		if ($row = $this->em->fetchAssoc('SELECT packageid, p.name AS packagename, mp.efffrom, mp.effto FROM member_package mp LEFT JOIN package p ON mp.packageid = p.id WHERE memberid = ? AND 1 = mp.active', array($memberid))){
			$ret = $row;
		}
		return $ret;
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
