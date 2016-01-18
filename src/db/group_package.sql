CREATE TABLE `group_package` (
  `id` int(11) NOT NULL,
  `packageid` int(50) NOT NULL,
  `groupid` int(11) DEFAULT NULL,
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
  `allowcredit` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `group_package`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `group_package`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;