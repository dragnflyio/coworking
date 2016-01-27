DROP TABLE IF EXISTS `region`;

CREATE TABLE IF NOT EXISTS `region` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(160) NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `region`
--

INSERT INTO `region` (`id`, `code`, `name`) VALUES
(1, 'TRANGTHI', 'Tràng Thi'),
(2, 'TNV', 'Tô Ngọc Vân');
