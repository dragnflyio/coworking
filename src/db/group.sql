DROP TABLE `groups` IF EXISTS;

CREATE TABLE IF NOT EXISTS `groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `address` text,
  `phone` varchar(20) DEFAULT NULL,
  `taxcode` varchar(255) DEFAULT NULL,
  `taxaddress` text,
  `description` text,
  `members` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
