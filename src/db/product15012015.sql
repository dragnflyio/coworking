DROP TABLE `product`;

CREATE TABLE IF NOT EXISTS `product` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) CHARACTER SET utf8 NOT NULL,
  `name_en` text CHARACTER SET utf8 NOT NULL,
  `name_vi` text CHARACTER SET utf8 NOT NULL,
  `unit` int(11) NOT NULL,
  `type` text CHARACTER SET utf8 NOT NULL,
  `price` decimal(10,0) NOT NULL,
  `category` int(11) NOT NULL,
  `status` int(1) NOT NULL,
  `showinbar` tinyint(1) NOT NULL DEFAULT '1',
  `start_date` int(11) NOT NULL,
  `end_date` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=100 ;
