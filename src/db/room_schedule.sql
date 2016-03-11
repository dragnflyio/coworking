DROP TABLE IF EXISTS `room_schedule`;
CREATE TABLE IF NOT EXISTS `room_schedule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `roomid` int(11) NOT NULL,
  `state` int(11) NOT NULL,
  `fromtime` int(11) NOT NULL,
  `totime` int(11) NOT NULL,
  `relatedusers` int(11) NOT NULL,
  `createdby` int(11) NOT NULL,
  `createdtime` int(11) NOT NULL,
  `sysdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `note` varchar(1000) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;
ALTER TABLE `room_schedule`  ADD `deposit` INT UNSIGNED NULL DEFAULT NULL;
