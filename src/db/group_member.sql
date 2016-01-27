DROP TABLE `group_member` IF EXISTS `customer_timelog`;

CREATE TABLE IF NOT EXISTS `group_member` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupid` int(11) NOT NULL,
  `memberid` int(11) NOT NULL,
  `isdeleted` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
