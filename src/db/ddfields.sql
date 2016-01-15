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
