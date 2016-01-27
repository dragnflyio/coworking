CREATE TABLE `card_infomation` (
  `id` int(11) NOT NULL,
  `cardid` varchar(1000) DEFAULT NULL,
  `cardcontent` varchar(255) DEFAULT NULL,
  `createdtime` int(11) NOT NULL,
  `regionid` int(11) DEFAULT NULL,
  `sysdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `card_infomation`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `card_infomation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;