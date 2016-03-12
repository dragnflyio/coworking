DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  `avatar` varchar(1000) CHARACTER SET utf8 DEFAULT NULL,
  `createdtime` int(11) NOT NULL,
  `lastlogintime` int(11) NOT NULL,
  `regionid` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_1483A5E9F85E0677` (`username`),
  UNIQUE KEY `UNIQ_1483A5E9E7927C74` (`email`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

ALTER TABLE `users` CHANGE regionid loggedregionid INT;
ALTER TABLE `users` ADD `roles` LONGTEXT NULL;


INSERT INTO `users` (`id`, `username`, `password`, `email`, `is_active`, `avatar`, `createdtime`, `lastlogintime`, `loggedregionid`) VALUES
(1, 'admin', '$2a$08$jHZj/wJfcVKlIwr5AvR78euJxYK7Ku5kURNhNx.7.CSIJ3Pq6LEPC', 'admin@webmaster.com', 1, NULL, 0, 1456387257, 1),
(2, 'vananh', '$2a$08$jHZj/wJfcVKlIwr5AvR78euJxYK7Ku5kURNhNx.7.CSIJ3Pq6LEPC', 'vananh@webmaster.com', 1, NULL, 0, 0, 0);

