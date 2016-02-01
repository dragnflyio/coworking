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

--
-- Dumping data for table `room_schedule`
--

INSERT INTO `room_schedule` (`id`, `roomid`, `state`, `fromtime`, `totime`, `relatedusers`, `createdby`, `createdtime`, `sysdate`, `note`) VALUES
(1, 2, 1, 1454315700, 1454319300, 7, 0, 0, '2016-02-01 08:40:08', 'Đặt phòng họp 1'),
(2, 3, 1, 1454316900, 1454320500, 8, 0, 0, '2016-02-01 08:57:26', 'Drupal');
