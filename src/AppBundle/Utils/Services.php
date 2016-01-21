<?php
namespace AppBundle\Utils;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Services{
  private $em = null;
  function __construct($em) {
    $this->em = $em->getConnection();
  }

  /**
   * Get package is used of a member
   *
   * @param int $memberid
   *
   * @return array $package
   */
  function getPackageByMemberId($memberid){
    $statement = $this->em->prepare("SELECT packageid FROM `member_package` WHERE memberid=:memberid");
    $statement->bindParam(':memberid', $memberid);
    $statement->execute();
    $member_package = $statement->fetchAll();
    if (!empty($member_package)){
      $statement = $this->em->prepare("SELECT * FROM `package` WHERE id=:packageid");
      $statement->bindParam(':packageid', $member_package[0]['packageid']);
      $statement->execute();
      $package = $statement->fetchAll();
      return $package[0];
    } else {
      $statement = $this->em->prepare("SELECT groupid FROM `group_member` WHERE memberid=:memberid");
      $statement->bindParam(':memberid', $memberid);
      $statement->execute();
      $group_member = $statement->fetchAll();
      if (!empty($group_member)) {
        $statement = $this->em->prepare("SELECT packageid FROM `group_package` WHERE groupid=:groupid");
        $statement->bindParam(':groupid', $group_member[0]['groupid']);
        $statement->execute();
        $group_package = $statement->fetchAll();
        if (empty($group_package)) throw new Exception('Thành viên này không dùng gói nào.');
        $statement = $this->em->prepare("SELECT * FROM `package` WHERE id=:packageid");
        $statement->bindParam(':packageid', $group_package[0]['packageid']);
        $statement->execute();
        $package = $statement->fetchAll();
        return $package[0];
      }
    }
  }

  /**
   * Get group of member
   *
   * @param int $memberid
   *
   * @return array $group
   */
  function getGroupByMemberId($memberid){
    $statement = $this->em->prepare("SELECT groupid FROM `group_member` WHERE memberid=:memberid");
    $statement->bindParam(':memberid', $memberid);
    $statement->execute();
    $group_member = $statement->fetchAll();
    if (empty($group_member)) throw new Exception('Thành viên này không thuộc nhóm nào.');
    return $group_member[0];
  }

  /**
   * Get member of group
   *
   * @param int $groupid
   *
   * @return array $member
   */
  function getMembersInGroup($groupid){
    $statement = $this->em->prepare("SELECT memberid FROM `group_member` WHERE groupid=:groupid");
    $statement->bindParam(':groupid', $groupid);
    $statement->execute();
    $rows = $statement->fetchAll();
    if (empty($rows)) throw new Exception('Nhóm này không có thành viên nào.');
    $members = array();
    foreach ($rows as $row) {
      $members[] = $row['memberid'];
    }
    return $members;
  }
}
