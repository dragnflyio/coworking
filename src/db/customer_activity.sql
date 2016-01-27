DROP TABLE `customer_activity` IF EXISTS;
CREATE TABLE `customer_activity` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `memberid` int(10) UNSIGNED NOT NULL,
  `code` varchar(50) DEFAULT NULL,
  `oldvalue` varchar(50) DEFAULT NULL,
  `newvalue` varchar(50) DEFAULT NULL,
  `note` varchar(1000) DEFAULT NULL,
  `createdtime` int(11) DEFAULT NULL,
  `amount` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `customer_activity`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `customer_activity`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
