DROP TABLE IF EXISTS `ddfields`;
CREATE TABLE IF NOT EXISTS `ddfields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `attributes` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=154 ;

ALTER TABLE `ddfields` CHANGE `data_type` `data_type` VARCHAR(20) NULL DEFAULT NULL;
ALTER TABLE `ddfields` CHANGE `value_default` `value_default` VARCHAR(255) NULL DEFAULT NULL;

delete from ddfields where table_name = 'product_category' and object_name = 'category';

INSERT INTO `ddfields` (`id`, `object_name`, `table_name`, `col_name`, `col_code`, `col_label`, `data_type`, `data_source`, `value_default`, `col_position`, `value_readonly`, `trigger_url`, `trigger_target`, `value_maxlength`, `col_active`, `search_opt`, `zero`, `sysdate`, `hidden`, `attributes`) VALUES

(null, 'category', 'product_category', 'parent_id', 'parent_id', 'Danh mục cha', 'SELECT', '/category/json', NULL, 4104, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-12 07:30:34', NULL, NULL),
(null, 'category', 'product_category', 'name', 'name', 'Tên', 'text', '', NULL, 4104, NULL, NULL, NULL, 255, 1, NULL, NULL, '2016-01-11 08:29:23', NULL, NULL),
(null, 'category', 'product_category', 'code', 'code', 'Mã', 'text', '', NULL, 4104, NULL, NULL, NULL, 255, 1, NULL, NULL, '2016-01-11 08:29:23', NULL, NULL),
(null, 'category', 'product_category', 'id', 'id', '', 'hidden', '', NULL, 4104, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-13 03:57:27', NULL, NULL);

DELETE FROM ddfields WHERE table_name = 'package' and object_name = 'packageform';

INSERT INTO `ddfields` (`id`, `object_name`, `table_name`, `col_name`, `col_code`, `col_label`, `data_type`, `data_source`, `value_default`, `col_position`, `value_readonly`, `trigger_url`, `trigger_target`, `value_maxlength`, `col_active`, `search_opt`, `zero`, `sysdate`, `hidden`, `attributes`) VALUES
(null, 'packageform', 'package', 'name', 'name', 'Tên gói', 'text', '', NULL, 4101, NULL, NULL, NULL, 50, 1, NULL, NULL, '2016-01-13 02:30:40', NULL, NULL),
(null, 'packageform', 'package', 'description', 'description', 'Mô tả', 'textarea', '', NULL, 4102, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-13 02:30:40', NULL, NULL),
(null, 'packageform', 'package', 'maxhours', 'maxhours', 'Số giờ', 'numeric', '', NULL, 4101, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-14 17:05:47', NULL, NULL),
(null, 'packageform', 'package', 'maxdays', 'maxdays', 'Số ngày', 'numeric', '', NULL, 4101, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-14 03:50:46', NULL, NULL),
(null, 'packageform', 'package', 'price', 'price', 'Giá', 'numeric', '', NULL, 4105, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-13 02:30:40', NULL, NULL),
(null, 'packageform', 'package', 'maxprintpapers', 'maxprintpapers', 'Số tờ in', 'numeric', '', NULL, 4106, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-13 02:30:40', NULL, NULL),
(null, 'packageform', 'package', 'maxmeetinghours', 'maxmeetinghours', 'Giờ phòng họp', 'numeric', '', NULL, 4107, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-13 02:30:40', NULL, NULL),
(null, 'packageform', 'package', 'visitorprice', 'visitorprice', 'Giá khách/giờ', 'numeric', '', NULL, 4107, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-13 02:30:40', NULL, NULL),
(null, 'packageform', 'package', 'allowcredit', 'allowcredit', 'Cho phép nợ', 'radio', '{"label":["Có", "Không"],"value":[1,2],"sameline":1}', 1, 4109, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-14 16:40:22', NULL, NULL),
(null, 'packageform', 'package', 'discountbar', 'discountbar', 'Giảm giá bar', 'percentage', '', NULL, 4110, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-14 04:43:28', NULL, NULL),
(null, 'packageform', 'package', 'locker', 'locker', 'Locker', 'radio', '{"label":["Có", "Không"],"value":[1,2],"sameline":1}', '2', '4110', NULL, NULL, NULL, NULL, '1', NULL, NULL, '2016-01-14 23:40:22', NULL, NULL);

delete from ddfields where table_name = 'tk_product' and object_name = 'tk_product';

delete from ddfields where table_name = 'product' and object_name = 'product';

INSERT INTO `ddfields` (`id`, `object_name`, `table_name`, `col_name`, `col_code`, `col_label`, `data_type`, `data_source`, `value_default`, `col_position`, `value_readonly`, `trigger_url`, `trigger_target`, `value_maxlength`, `col_active`, `search_opt`, `zero`, `sysdate`, `hidden`, `attributes`) VALUES
(null, 'product', 'product', 'name_vi', 'name_vi', 'Tên(VN)', 'text', '', NULL, 4101, NULL, NULL, NULL, 255, 1, NULL, NULL, '2016-01-11 08:29:23', NULL, NULL),
(null, 'product', 'product', 'code', 'code', 'Mã', 'text', '', NULL, 4100, NULL, NULL, NULL, 255, 1, NULL, NULL, '2016-01-11 08:29:23', NULL, NULL),
(null, 'product', 'product', 'name_en', 'name_en', 'Tên(EN)', 'text', '', NULL, 4102, NULL, NULL, NULL, 255, 1, NULL, NULL, '2016-01-11 08:29:23', NULL, NULL),
(null, 'product', 'product', 'unit', 'unit', 'Đơn vị', 'SELECT', '/unit/json', NULL, 4101, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-15 02:20:43', NULL, NULL),
(null, 'product', 'product', 'category', 'category', 'Danh mục', 'SELECT', '/category/json', NULL, 4102, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-14 14:16:31', NULL, NULL),
(null, 'product', 'product', 'type', 'type', 'Loại', 'SELECT', '{\n  "value":["GOODS","PRODUCT","DRINK","MATERIAL","EQUIPMENT","SERVICE","TOOL"],\n  "label":["Hàng hóa", "Sản phẩm", "Uống","Nguyên vật liệu","Đền bù","Dịch vụ","Công cụ"],\n  "sameline": 1\n}', NULL, 4101, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-15 01:49:48', NULL, NULL),
(null, 'product', 'product', 'price', 'price', 'Giá', 'numeric', '', NULL, 4102, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-11 08:29:23', NULL, NULL),
(null, 'product', 'product', 'start_date', 'start_date', 'Ngày bắt đầu', 'date', '', 0, 4101, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-07 08:42:33', NULL, NULL),
(null, 'product', 'product', 'end_date', 'end_date', 'Ngày kết thúc', 'date', '', 0, 4102, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-07 08:42:33', NULL, NULL),
(null, 'product', 'product', 'showinbar', 'showinbar', 'Quầy bar?', 'CHECK', '', 1, 4101, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-15 02:31:46', NULL, NULL),
(null, 'product', 'product', 'id', 'id', '', 'hidden', '', NULL, 4100, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-13 03:57:27', NULL, NULL);

delete from ddfields where object_name = 'group';
INSERT INTO `ddfields` (`id`, `object_name`, `table_name`, `col_name`, `col_code`, `col_label`, `data_type`, `data_source`, `value_default`, `col_position`, `value_readonly`, `trigger_url`, `trigger_target`, `value_maxlength`, `col_active`, `search_opt`, `zero`, `sysdate`, `hidden`, `attributes`) VALUES
(null, 'group', 'groups', 'id', 'id', 'id', 'hidden', '', NULL, 4100, NULL, NULL, NULL, 255, 1, NULL, NULL, '2016-01-27 07:40:12', NULL, NULL),
(null, 'group', 'groups', 'members', 'members', 'Số thành viên', 'numeric', '', NULL, 4101, NULL, NULL, NULL, 255, 1, NULL, NULL, '2016-01-27 07:40:15', NULL, NULL),
(null, 'group', 'groups', 'description', 'description', 'Mô tả/Ghi chú', 'textarea', '', NULL, 4102, NULL, NULL, NULL, 255, 1, NULL, NULL, '2016-01-27 07:40:19', NULL, NULL),
(null, 'group', 'groups', 'taxaddress', 'taxaddress', 'Địa chỉ thuế', 'text', '', NULL, 4101, NULL, NULL, NULL, 255, 1, NULL, NULL, '2016-01-27 07:40:22', NULL, NULL),
(null, 'group', 'groups', 'taxcode', 'taxcode', 'Mã số thuế', 'text', '', NULL, 4102, NULL, NULL, NULL, 255, 1, NULL, NULL, '2016-01-27 07:40:26', NULL, NULL),
(null, 'group', 'groups', 'address', 'address', 'Địa chỉ', 'text', '', NULL, 4102, NULL, NULL, NULL, 255, 1, NULL, NULL, '2016-01-27 07:40:28', NULL, NULL),
(null, 'group', 'groups', 'phone', 'phone', 'Điện thoại', 'text', '', NULL, 4101, NULL, NULL, NULL, 255, 1, NULL, NULL, '2016-01-27 07:40:33', NULL, NULL),
(null, 'group', 'groups', 'name', 'name', 'Tên', 'text', '', NULL, 4101, NULL, NULL, NULL, 255, 1, NULL, NULL, '2016-01-27 07:40:35', NULL, NULL),
(null, 'group', 'groups', 'regionid', 'regionid', 'Địa điểm', 'TEXT_MULTI', '@/region/get-json', NULL, 4101, NULL, NULL, NULL, 255, 1, NULL, NULL, '2016-01-27 07:40:35', NULL, NULL);


DELETE FROM ddfields WHERE table_name = 'member' AND object_name = 'customerform';
INSERT INTO `ddfields` (`id`, `object_name`, `table_name`, `col_name`, `col_code`, `col_label`, `data_type`, `data_source`, `value_default`, `col_position`, `value_readonly`, `trigger_url`, `trigger_target`, `value_maxlength`, `col_active`, `search_opt`, `zero`, `sysdate`, `hidden`, `attributes`) VALUES
(null, 'customerform', 'member', 'level', 'level', 'Hạng', 'radio', '{"label":["Khách", "Thành viên", "Thành viên VIP"],"value":[1,2,3],"sameline":1}', 1, 4109, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-17 07:21:34', NULL, NULL),
(null, 'customerform', 'member', 'membersince', 'membersince', 'Ngày bắt đầu', 'date', '', NULL, 4110, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-17 07:49:24', NULL, NULL),
(null, 'customerform', 'member', 'active', 'active', 'Trạng thái', 'radio', '{"label":["Hoạt động", "Ngừng hoạt động"],"value":[1,2],"sameline":1}', 1, 4109, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-17 07:49:32', NULL, NULL),
(null, 'customerform', 'member', 'birthday', 'birthday', 'Ngày sinh', 'BIRTHDAY', '', NULL, 4106, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-17 07:49:37', NULL, NULL),
(null, 'customerform', 'member', 'phone', 'phone', 'Điện thoại', 'text', '', NULL, 4109, NULL, NULL, NULL, 20, 1, NULL, NULL, '2016-01-16 08:41:41', NULL, NULL),
(null, 'customerform', 'member', 'email', 'email', 'Email', 'TEXT_MULTI', '', NULL, 4105, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-16 08:41:41', NULL, NULL),
(null, 'customerform', 'member', 'idno', 'idcardnumber', 'CMND', 'text', '', NULL, 4102, NULL, NULL, NULL, 20, 1, NULL, NULL, '2016-01-17 09:17:14', NULL, NULL),
(null, 'customerform', 'member', 'name', 'name', 'Tên', 'text', '', NULL, 4101, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-14 09:40:22', NULL, NULL),
(null, 'customerform', 'member', 'regionid', 'regionid', 'Địa điểm', 'TEXT_MULTI', '@/region/get-json', NULL, 4102, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-27 07:14:28', NULL, NULL);

DELETE FROM ddfields WHERE table_name = 'group_member' AND object_name = 'group_member';
INSERT INTO `ddfields` (`id`, `object_name`, `table_name`, `col_name`, `col_code`, `col_label`, `data_type`, `data_source`, `value_default`, `col_position`, `value_readonly`, `trigger_url`, `trigger_target`, `value_maxlength`, `col_active`, `search_opt`, `zero`, `sysdate`, `hidden`, `attributes`) VALUES
(null, 'group_member', 'group_member', 'groupid', 'groupid', 'Nhóm', 'hidden', '', NULL, 4100, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-26 02:31:17', NULL, NULL),
(null, 'group_member', 'group_member', 'members', 'members', 'Thành viên', 'TEXT_MULTI', '@/group/member-json', NULL, 4100, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-18 07:42:23', NULL, NULL);


DELETE FROM ddfields WHERE table_name = 'member_package' AND object_name = 'memberpackage';
INSERT INTO `ddfields` (`id`, `object_name`, `table_name`, `col_name`, `col_code`, `col_label`, `data_type`, `data_source`, `value_default`, `col_position`, `value_readonly`, `trigger_url`, `trigger_target`, `value_maxlength`, `col_active`, `search_opt`, `zero`, `sysdate`, `hidden`, `attributes`) VALUES
(null, 'memberpackage', 'member_package', 'packageid', 'packageid', 'Chọn gói', 'text_multi', '/customer/getpackages', NULL, 4101, NULL, NULL, NULL, 1, 1, NULL, NULL, '2016-01-14 09:40:22', NULL, NULL),
(null, 'memberpackage', 'member_package', 'maxhours', 'maxhours', 'Số giờ', 'numeric', '', NULL, 4102, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-18 03:25:59', NULL, NULL),
(null, 'memberpackage', 'member_package', 'maxvisitors', 'maxvisitors', 'Số khách', 'numeric', '', NULL, 4101, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-18 03:26:04', NULL, NULL),
(null, 'memberpackage', 'member_package', 'maxdays', 'maxdays', 'Số ngày', 'numeric', '', NULL, 4102, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-18 03:25:45', NULL, NULL),
(null, 'memberpackage', 'member_package', 'maxprintpapers', 'maxprintpapers', 'Số tờ in', 'numeric', '', NULL, 4101, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-14 09:40:22', NULL, NULL),
(null, 'memberpackage', 'member_package', 'maxmeetinghours', 'maxmeetinghours', 'Số giờ họp', 'numeric', '', NULL, 4102, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-14 09:40:22', NULL, NULL),
(null, 'memberpackage', 'member_package', 'allowcredit', 'allowcredit', 'Cho phép nợ', 'radio', '{"label":["Có", "Không"],"value":[1,2],"sameline":1}', NULL, 4101, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-18 03:31:53', NULL, NULL),
(null, 'memberpackage', 'member_package', 'locker', 'locker', 'Locker', 'radio', '{"label":["Có", "Không"],"value":[1,2],"sameline":1}', NULL, 4102, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-18 03:31:53', NULL, NULL),
(null, 'memberpackage', 'member_package', 'discountbar', 'discountbar', 'Giảm giá bar', 'percentage', '', NULL, 4101, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-18 03:31:53', NULL, NULL),
(null, 'memberpackage', 'member_package', 'price', 'price', 'Price', 'money', '', NULL, 4102, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-18 03:31:53', NULL, NULL),
(null, 'memberpackage', 'member_package', 'efffrom', 'efffrom', 'Bắt đầu', 'date', '', NULL, 4101, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-18 03:31:53', NULL, NULL),
(null, 'memberpackage', 'member_package', 'effto', 'effto', 'Hết hạn', 'date', '', NULL, 4102, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-18 03:31:53', NULL, NULL),
(null, 'memberpackage', 'member_package', 'visitorprice', 'visitorprice', 'Giá khách ngoài', 'money', '', NULL, 4101, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-18 03:31:53', NULL, NULL);

DELETE FROM ddfields WHERE table_name = 'group_package' AND object_name = 'group_package';
INSERT INTO `ddfields` (`id`, `object_name`, `table_name`, `col_name`, `col_code`, `col_label`, `data_type`, `data_source`, `value_default`, `col_position`, `value_readonly`, `trigger_url`, `trigger_target`, `value_maxlength`, `col_active`, `search_opt`, `zero`, `sysdate`, `hidden`, `attributes`) VALUES
(null, 'group_package', 'group_package', 'packageid', 'packageid', 'Chọn gói', 'text_multi', '/customer/getpackages', NULL, 4101, NULL, NULL, NULL, 1, 1, NULL, NULL, '2016-01-19 07:03:50', NULL, NULL),
(null, 'group_package', 'group_package', 'price', 'price', 'Giá', 'numeric', '', NULL, 4102, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-19 04:03:17', NULL, NULL),
(null, 'group_package', 'group_package', 'maxhours', 'maxhours', 'Số giờ', 'numeric', '', NULL, 4101, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-19 04:03:17', NULL, NULL),
(null, 'group_package', 'group_package', 'maxdays', 'maxdays', 'Số ngày', 'numeric', '', NULL, 4102, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-19 04:03:17', NULL, NULL),
(null, 'group_package', 'group_package', 'maxvisitors', 'maxvisitors', 'Số khách', 'numeric', '', NULL, 4101, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-19 04:03:17', NULL, NULL),
(null, 'group_package', 'group_package', 'maxmeetinghours', 'maxmeetinghours', 'Số giờ họp', 'numeric', '', NULL, 4102, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-19 04:03:17', NULL, NULL),
(null, 'group_package', 'group_package', 'maxprintpapers', 'maxprintpapers', 'Số tờ in', 'numeric', '', NULL, 4101, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-19 04:03:17', NULL, NULL),
(null, 'group_package', 'group_package', 'discountbar', 'discountbar', 'Giảm giá bar', 'percentage', '', NULL, 4102, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-19 04:03:17', NULL, NULL),
(null, 'group_package', 'group_package', 'allowcredit', 'allowcredit', 'Cho phép nợ', 'radio', '{"label":["Có", "Không"],"value":[1,2],"sameline":1}', 1, 4101, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-14 09:40:22', NULL, NULL),
(null, 'group_package', 'group_package', 'locker', 'locker', 'Locker', 'radio', '{"label":["Có", "Không"],"value":[1,2],"sameline":1}', 2, 4102, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-14 16:40:22', NULL, NULL),
(null, 'group_package', 'group_package', 'efffrom', 'efffrom', 'Ngày bắt đầu', 'date', '', 0, 4101, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-15 00:53:33', NULL, NULL),
(null, 'group_package', 'group_package', 'effto', 'effto', 'Ngày hết hạn', 'date', '', 0, 4102, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-15 00:53:33', NULL, NULL),
(null, 'group_package', 'group_package', 'visitorprice', 'visitorprice', 'Giá khách ngoài', 'money', '', NULL, 4101, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-19 04:03:17', NULL, NULL);

DELETE FROM ddfields WHERE table_name = 'customer_timelog' AND object_name IN ('memberchecking', 'visitorchecking');
INSERT INTO `ddfields` (`id`, `object_name`, `table_name`, `col_name`, `col_code`, `col_label`, `data_type`, `data_source`, `value_default`, `col_position`, `value_readonly`, `trigger_url`, `trigger_target`, `value_maxlength`, `col_active`, `search_opt`, `zero`, `sysdate`, `hidden`, `attributes`) VALUES
(null, 'visitorchecking', 'customer_timelog', 'visitorphone', 'visitorphone', 'Điện thoại', 'text', '', NULL, 4102, NULL, NULL, NULL, 20, 1, NULL, NULL, '2016-01-19 20:50:59', NULL, NULL),
(null, 'visitorchecking', 'customer_timelog', 'visitoremail', 'visitoremail', 'Email', 'text', '', NULL, 4101, NULL, NULL, NULL, 255, 1, NULL, NULL, '2016-01-19 20:50:59', NULL, NULL),
(null, 'visitorchecking', 'customer_timelog', 'visitorname', 'visitorname', 'Tên khách', 'text', '', NULL, 4102, NULL, NULL, NULL, 255, 1, NULL, NULL, '2016-01-19 20:50:59', NULL, NULL),
(null, 'visitorchecking', 'customer_timelog', 'memberid', 'memberid', 'Thành viên', 'TEXT_MULTI', '/get-members', NULL, 4101, NULL, NULL, NULL, 1, 1, NULL, NULL, '2016-01-27 09:09:30', NULL, NULL),
(null, 'memberchecking', 'customer_timelog', 'checkout', 'checkout', 'Check out', 'datetime', '', NULL, 4101, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-19 20:50:59', NULL, NULL),
(null, 'memberchecking', 'customer_timelog', 'checkin', 'checkin', 'Check in', 'datetime', '', NULL, 4102, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-19 20:50:59', NULL, NULL),
(null, 'memberchecking', 'customer_timelog', 'memberid', 'memberid', 'Thành viên', 'TEXT_MULTI', '/get-members', NULL, 4101, NULL, NULL, NULL, 1, 1, NULL, NULL, '2016-01-27 09:09:26', NULL, NULL),
(null, 'visitorchecking', 'customer_timelog', 'checkin', 'checkin', 'Check in', 'datetime', '', NULL, 4101, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-19 20:50:59', NULL, NULL),
(null, 'visitorchecking', 'customer_timelog', 'checkout', 'checkout', 'Check out', 'datetime', '', NULL, 4102, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-19 20:50:59', NULL, NULL),
(null, 'memberchecking', 'customer_timelog', 'id', 'id', '', 'hidden', '', NULL, 4100, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-13 03:57:27', NULL, NULL),
(null, 'visitorchecking', 'customer_timelog', 'id', 'id', '', 'hidden', '', NULL, 4100, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-13 03:57:27', NULL, NULL),
(null, 'memberchecking', 'customer_timelog', 'printedpapers', 'printedpapers', 'Số tờ in', 'numeric', '', NULL, 4102, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-27 09:09:26', NULL, NULL);

DELETE FROM ddfields WHERE table_name = 'room' AND object_name = 'room';
INSERT INTO `ddfields` (`id`, `object_name`, `table_name`, `col_name`, `col_code`, `col_label`, `data_type`, `data_source`, `value_default`, `col_position`, `value_readonly`, `trigger_url`, `trigger_target`, `value_maxlength`, `col_active`, `search_opt`, `zero`, `sysdate`, `hidden`, `attributes`) VALUES
(null, 'room', 'room', 'id', 'id', '', 'hidden', '', NULL, 4100, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-13 03:57:27', NULL, NULL),
(null, 'room', 'room', 'name', 'name', 'Tên', 'text', '', NULL, 4101, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-26 03:53:46', NULL, NULL),
(null, 'room', 'room', 'code', 'code', 'Mã', 'text', '', NULL, 4102, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-26 03:54:05', NULL, NULL),
(null, 'room', 'room', 'note', 'note', 'Ghi chú', 'textarea', '', NULL, 4101, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-26 03:54:29', NULL, NULL),
(null, 'room', 'room', 'type', 'type', 'Loại', 'RADIO', '{\r\n  "value":["meeting_room","event_room"],\r\n  "label":["Phòng họp", "Phòng sự kiện"],\r\n  "sameline": 1\r\n}', NULL, 4102, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-26 03:58:12', NULL, NULL),
(null, 'room', 'room', 'status', 'status', 'Trạng thái', 'RADIO', '{\r\n  "value":["1","0"],\r\n  "label":["Đang hoạt động", "Ngừng hoạt động"],\r\n  "sameline": 1\r\n}', NULL, 4102, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-26 03:58:12', NULL, NULL);
