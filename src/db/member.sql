CREATE TABLE `member` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `idno` varchar(20) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `birthdayday` tinyint(4) DEFAULT NULL,
  `birthdaymonth` tinyint(4) DEFAULT NULL,
  `birthdayyear` smallint(6) DEFAULT NULL,
  `createdtime` int(11) DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '1',
  `level` smallint(6) DEFAULT NULL,
  `membersince` int(11) DEFAULT NULL,
  `memberto` int(11) DEFAULT NULL,
  `sysdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `administrators` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE `member`  ADD `regionid` INT NULL DEFAULT NULL ;
ALTER TABLE `member` ADD `company` varchar(255) DEFAULT NULL;
ALTER TABLE `member` ADD `job` varchar(255) DEFAULT NULL;
ALTER TABLE `member` ADD `postaladdress` text DEFAULT NULL;
--
-- Table structure for table `member_package`
--

CREATE TABLE `member_package` (
  `id` int(11) NOT NULL,
  `packageid` int(50) NOT NULL,
  `memberid` int(11) DEFAULT NULL,
	`efffrom` int(11) DEFAULT NULL,
  `effto` int(11) DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '1',
  `createdby` int(11) DEFAULT NULL,
  `updatedby` int(11) DEFAULT NULL,
  `createdtime` int(11) DEFAULT NULL,
  `updatedtime` int(11) DEFAULT NULL,
  `sysdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `maxhours` int(11) DEFAULT NULL,
  `maxvisitors` int(11) DEFAULT NULL,
  `maxdays` int(11) DEFAULT NULL,
  `maxprintpapers` int(11) DEFAULT NULL,
  `maxmeetinghours` int(11) DEFAULT NULL,
  `allowcredit` tinyint(4) NOT NULL DEFAULT '0',
	`discountbar` tinyint(3) UNSIGNED DEFAULT NULL,
  `price` int(10) UNSIGNED NOT NULL,
  `locker` tinyint(4) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `member`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `member_package`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `member`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `member_package`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `member_package`  ADD `visitorprice` INT NULL DEFAULT NULL ;
ALTER TABLE `member_package`  ADD `efftoextend` INT NULL DEFAULT NULL ;
ALTER TABLE `member_package`  ADD `printedpapers` INT NULL DEFAULT NULL  AFTER `efftoextend`;

