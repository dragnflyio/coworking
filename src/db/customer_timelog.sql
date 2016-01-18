CREATE TABLE `customer_timelog` (
  `id` int(11) NOT NULL,
  `memberid` int(11) NOT NULL,
  `visitorname` varchar(50) DEFAULT NULL,
  `visitoremail` varchar(50) DEFAULT NULL,
  `visitorphone` varchar(32) DEFAULT NULL,
  `checkin` int(11) DEFAULT NULL,
  `checkout` int(11) DEFAULT NULL,
  `checkinby` int(11) NOT NULL,
  `checkoutby` int(11) NOT NULL,
  `isvisitor` tinyint(4) NOT NULL DEFAULT '0',
  `sysdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `customer_timelog`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `customer_timelog`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;