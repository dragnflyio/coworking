DROP TABLE IF EXISTS `product`;

CREATE TABLE IF NOT EXISTS `product` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) NOT NULL,
  `name_en` text,
  `name_vi` text NOT NULL,
  `unit` int(11) NOT NULL,
  `type` text NOT NULL,
  `price` decimal(10,0) NOT NULL,
  `category` int(11) NOT NULL,
  `status` int(1) NOT NULL DEFAULT '1',
  `showinbar` tinyint(1) NOT NULL DEFAULT '1',
  `start_date` int(11) DEFAULT NULL,
  `end_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
