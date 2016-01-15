delete from ddfields where table_name = 'product_category' and object_name = 'category';


INSERT INTO `ddfields` (`id`, `object_name`, `table_name`, `col_name`, `col_code`, `col_label`, `data_type`, `data_source`, `value_default`, `col_position`, `value_readonly`, `trigger_url`, `trigger_target`, `value_maxlength`, `col_active`, `search_opt`, `zero`, `sysdate`, `hidden`, `attributes`) VALUES


(null, 'category', 'product_category', 'parent_id', 'parent_id', 'Danh mục cha', 'SELECT', '/category/json', NULL, 4104, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-12 07:30:34', NULL, NULL),
(null, 'category', 'product_category', 'name', 'name', 'Tên', 'text', '', NULL, 4104, NULL, NULL, NULL, 255, 1, NULL, NULL, '2016-01-11 08:29:23', NULL, NULL),
(null, 'category', 'product_category', 'code', 'code', 'Mã', 'text', '', NULL, 4104, NULL, NULL, NULL, 255, 1, NULL, NULL, '2016-01-11 08:29:23', NULL, NULL),
(null, 'category', 'product_category', 'id', 'id', '', 'hidden', '', NULL, 4104, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-13 03:57:27', NULL, NULL),
(null, 'tk_product', 'tk_product', 'code', 'code', 'Mã', 'text', '', NULL, 4101, NULL, NULL, NULL, 255, 1, NULL, NULL, '2016-01-11 08:29:23', NULL, NULL),
(null, 'tk_product', 'tk_product', 'name', 'name', 'Tên', 'text', '', NULL, 4101, NULL, NULL, NULL, 255, 1, NULL, NULL, '2016-01-14 07:23:17', NULL, NULL),
(null, 'tk_product', 'tk_product', 'type', 'type', 'Loại', 'SELECT', '/category/json', NULL, 4106, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-14 07:29:48', NULL, NULL),
(null, 'tk_product', 'tk_product', 'start_date', 'start_date', 'date field', 'date', '', 0, 4109, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-07 08:42:33', NULL, NULL),
(null, 'tk_product', 'tk_product', 'danh_muc', 'category', 'Danh mục', 'SELECT', '/category/json', NULL, 4105, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-14 07:29:48', NULL, NULL),
(null, 'tk_product', 'tk_product', 'end_date', 'end_date', 'date field', 'date', '', 0, 4110, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-07 08:42:33', NULL, NULL);

DELETE FROM ddfields WHERE table_name = 'package' and object_name = 'packageform';

INSERT INTO `ddfields` (`id`, `object_name`, `table_name`, `col_name`, `col_code`, `col_label`, `data_type`, `data_source`, `value_default`, `col_position`, `value_readonly`, `trigger_url`, `trigger_target`, `value_maxlength`, `col_active`, `search_opt`, `zero`, `sysdate`, `hidden`, `attributes`) VALUES
(null, 'packageform', 'package', 'name', 'name', 'Tên gói', 'text', '', NULL, 4101, NULL, NULL, NULL, 50, 1, NULL, NULL, '2016-01-13 02:30:40', NULL, NULL),
(null, 'packageform', 'package', 'description', 'description', 'Mô tả', 'textarea', '', NULL, 4102, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-13 02:30:40', NULL, NULL),
(null, 'packageform', 'package', 'maxhours', 'maxhours', 'Số giờ', 'numeric', '', NULL, 4101, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-14 17:05:47', NULL, NULL),
(null, 'packageform', 'package', 'maxdays', 'maxdays', 'Số ngày', 'numeric', '', NULL, 4101, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-14 03:50:46', NULL, NULL),
(null, 'packageform', 'package', 'price', 'price', 'Giá', 'numeric', '', NULL, 4105, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-13 02:30:40', NULL, NULL),
(null, 'packageform', 'package', 'maxprintpapers', 'maxprintpapers', 'Số tờ in', 'numeric', '', NULL, 4106, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-13 02:30:40', NULL, NULL),
(null, 'packageform', 'package', 'maxmeetinghours', 'maxmeetinghours', 'Giờ phòng họp', 'numeric', '', NULL, 4107, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-13 02:30:40', NULL, NULL),
(null, 'packageform', 'package', 'allowcredit', 'allowcredit', 'Cho phép nợ', 'radio', '{"label":["Có", "Không"],"value":[1,2],"sameline":1}', 1, 4109, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-14 16:40:22', NULL, NULL),
(null, 'packageform', 'package', 'discountbar', 'discountbar', 'Giảm giá bar', 'percentage', '', NULL, 4110, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2016-01-14 04:43:28', NULL, NULL),
(null, 'packageform', 'package', 'locker', 'locker', 'Locker', 'radio', '{"label":["Có", "Không"],"value":[1,2],"sameline":1}', '2', '4110', NULL, NULL, NULL, NULL, '1', NULL, NULL, '2016-01-14 23:40:22', NULL, NULL);