DROP TABLE IF EXISTS `group_activity`;
CREATE TABLE IF NOT EXISTS `group_activity` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupid` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `oldvalue` int(50) NULL,
  `newvalue` int(50) NOT NULL,
  `note` varchar(1000) NULL,
  `createdtime` int(11) NOT NULL,
  `amount` int(11) NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
