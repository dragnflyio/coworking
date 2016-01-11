
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE IF NOT EXISTS `ddfields` (
  `id` int(11) NOT NULL,
  `object_name` varchar(15) NOT NULL,
  `table_name` varchar(20) DEFAULT NULL,
  `col_name` varchar(20) NOT NULL,
  `col_code` varchar(20) NOT NULL,
  `col_label` varchar(100) NOT NULL,
  `data_type` varchar(10) NOT NULL,
  `data_source` varchar(1000) NOT NULL,
  `value_default` int(11) DEFAULT NULL,
  `col_position` int(11) NOT NULL,
  `value_readonly` tinyint(4) DEFAULT NULL,
  `trigger_url` varchar(500) DEFAULT NULL,
  `trigger_target` varchar(500) DEFAULT NULL,
  `value_maxlength` int(11) DEFAULT NULL,
  `col_active` tinyint(4) NOT NULL DEFAULT '1',
  `search_opt` varchar(500) DEFAULT NULL,
  `zero` varchar(500) DEFAULT NULL,
  `sysdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `hidden` tinyint(4) DEFAULT NULL,
  `attributes` varchar(500) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `ddfields`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `ddfields`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
INSERT INTO `ddfields` (`id`, `object_name`, `table_name`, `col_name`, `col_code`, `col_label`, `data_type`, `data_source`, `value_default`, `col_position`, `value_readonly`, `trigger_url`, `trigger_target`, `value_maxlength`, `col_active`, `search_opt`, `zero`, `sysdate`, `hidden`, `attributes`) VALUES
(1, 'obj1', 'newtable', 'text', 'idtext', 'text field', 'textarea', '', 0, 4101, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-08 09:25:59', NULL, NULL),
(2, 'obj1', 'newtable', 'number', 'idnumber', 'text field', 'NUMERIC', '', 0, 4102, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-07 08:42:33', NULL, NULL),
(3, 'obj1', 'newtable', 'ngaytao', 'iddate', 'date field', 'date', '', 0, 4105, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-07 08:42:33', NULL, NULL),
(4, 'obj1', 'newtable', 'ngaytao', 'iddatetime', 'date time field', 'datetime', '', 0, 4104, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-08 09:44:25', NULL, NULL),
(5, 'obj1', 'newtable', 'text', 'idtextmulti', 'text field multi', 'TEXT_MULTI', '/form/json', 0, 4105, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-08 09:39:11', NULL, NULL),
(6, 'obj1', 'newtable', 'checkbox', 'idcheckbox', 'checkbox field', 'CHECK', '', 0, 4102, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-07 08:42:33', NULL, NULL),
(7, 'obj1', 'newtable', 'radio', 'radio', 'radio field', 'RADIO', '{\r\n  "value":[1,2,3],\r\n  "label":["val1", "val2", "val3"]\r\n}', 0, 4102, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-07 08:42:33', NULL, NULL),
(8, 'obj1', 'newtable', 'radio', 'radio', 'radio field inline', 'RADIO', '{\n  "value":[1,2,3],\n  "label":["val1", "val2", "val3"],\n  "sameline": 1\n}', 0, 4102, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-10 16:52:35', NULL, NULL);
