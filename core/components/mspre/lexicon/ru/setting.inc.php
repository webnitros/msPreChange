<?php
/**
 * Settings Russian Lexicon Entries for mspre
 *
 * @package mspre
 * @subpackage lexicon
 */
$_lang['area_mspre_main'] = 'Основные настройки';
$_lang['area_mspre_fields'] = 'Поля для модификаций';
$_lang['area_mspre_resource_tree'] = 'Дерево ресурсов';
$_lang['area_mspre_export'] = 'Экспорт';

$_lang['setting_mspre_default_context'] = 'Контекст по-умолчанию';
$_lang['setting_mspre_default_context_desc'] = 'Контекст выбранный по умолчанию';


$_lang['setting_mspre_default_width'] = 'Ширина колонки по умолчанию';
$_lang['setting_mspre_default_width_desc'] = 'По умолчанию 70';

$_lang['setting_mspre_filter_size_colump'] = 'Ширина колонок фильтров';
$_lang['setting_mspre_filter_size_colump_left_desc'] = 'Ширина колонок с фильтрами, указывать целое число';

$_lang['setting_mspre_root_parent'] = 'Категории товаров';
$_lang['setting_mspre_root_parent_desc'] = 'Ид категории с товарами в контексте (пример "web:59,product:68")';

$_lang['setting_mspre_field_price'] = 'Поля в формате цены';
$_lang['setting_mspre_field_price_desc'] = 'Список полей в формате цены через запятую. По умолчанию "price,old_price". Можно добавить свои кастомизированные поля для выборки';

$_lang['setting_mspre_field_string'] = 'Поля в формате строки';
$_lang['setting_mspre_field_string_desc'] = 'Список полей в формате цены через строки. По умолчанию "made_in". Можно добавить свои кастомизированные поля для выборки';

$_lang['setting_mspre_field_weight'] = 'Поля в формате веса';
$_lang['setting_mspre_field_weight_desc'] = 'Список полей в формате веса через строки. По умолчанию "weight". Можно добавить свои кастомизированные поля для выборки';

$_lang['setting_mspre_product_table_selected_fields'] = 'Поля таблицы для товаров';
$_lang['setting_mspre_product_table_selected_fields_desc'] = 'Список полей в формате JSON через запятую. По умолчанию "color,size,tags". Можно добавить свои кастомизированные поля для выборки';
$_lang['setting_mspre_resource_table_selected_fields'] = 'Поля таблицы для ресурсов';
$_lang['setting_mspre_resource_table_selected_fields_desc'] = 'Список полей через запятую которые требуется выводить в таблице с ресурсами. Полями можно управлять на странице';

$_lang['setting_mspre_product_export_selected_fields'] = 'Поля для экспорта товаров';
$_lang['setting_mspre_product_export_selected_fields_desc'] = 'Список полей через запятую которые требуется выводить в таблице с товарами. Полями можно управлять на странице';
$_lang['setting_mspre_resource_export_selected_fields'] = 'Поля для экспорта ресурсов';
$_lang['setting_mspre_resource_export_selected_fields_desc'] = 'Список полей через запятую которые требуется выводить в таблице с ресурсами. Полями можно управлять на странице';

$_lang['setting_mspre_resource_tree_node_name'] = 'Поле для названия узла в дереве ресурсов';
$_lang['setting_mspre_resource_tree_node_name_desc'] = 'Укажите поле ресурса, которое будет использоваться в качестве названия узла в дереве ресурсов. По умолчанию поле «pagetitle», любое поле ресурса может быть использовано: «menutitle», «alias», «longtitle», и т.п.';

$_lang['setting_mspre_resource_tree_node_name_fallback'] = 'Запасное поле для узла в дереве ресурсов';
$_lang['setting_mspre_resource_tree_node_name_fallback_desc'] = 'Укажите поле ресурса для использования в качестве запасного названия узла в дереве ресурсов. Это значение будет использоваться, если ресурс имеет пустое значение для заданного поля ресурса в дереве.';

$_lang['setting_mspre_resource_tree_node_tooltip'] = 'Поле подсказки для ресурса в дереве ресурсов';
$_lang['setting_mspre_resource_tree_node_tooltip_desc'] = 'Укажите поле ресурса для использования в качестве всплывающей подсказки в дереве ресурсов. Любое поле ресурса может быть использовано: «menutitle», «alias», «longtitle», и т.п. Если не указано, будет использовано «longtitle» с «description» под ним.';

$_lang['setting_mspre_check_string_values_htmlentities'] = 'Проверять строковые значения через htmlentities';
$_lang['setting_mspre_check_string_values_htmlentities_desc'] = 'По умолчанию Да. Если вы используете html теги в строковых значениях то вам необходимо установить Нет';

$_lang['setting_mspre_export_add_url'] = 'Добавить URL к полю для экспорта';
$_lang['setting_mspre_export_add_url_desc'] = 'Вы можете перечислить поля для которых будет подставлена ссылка на сайт ';

$_lang['setting_mspre_export_date_format'] = 'Формат даты для экспорта';
$_lang['setting_mspre_export_date_format_desc'] = 'По умолчанию Y-m-d H:i:s. Оставьте пусты чтобы дата выгружалась в цифровом формате';


$_lang['setting_mspre_export_price_format'] = 'Форма цены для экспорта';
$_lang['setting_mspre_export_price_format_desc'] = 'По умолчанию "[2, ",", " "]"';

$_lang['setting_mspre_export_values_default_empty'] = 'Значения по умолчани для пустых значений';
$_lang['setting_mspre_export_values_default_empty_desc'] = 'Если значение поля будет пустое то в место пусто будет выводится ваше значение. Пример price:0,pagetitle:нет заголовка';

$_lang['setting_mspre_export_memory_limit'] = 'Выделять оперативной памяти при экспорте';
$_lang['setting_mspre_export_memory_limit_desc'] = 'По умолчанию 1024m . Вы можете установить больше в случае появления ошибки при выгрзке больших объеком информациии';

$_lang['setting_mspre_max_execution_time'] = 'Максимальное вермя исполнения скрипт для экспорта';
$_lang['setting_mspre_max_execution_time_desc'] = 'По умолчанию 50 секунд. Вы можете установить больше';

$_lang['setting_mspre_export_weight_format'] = 'Форма Веса для экспорта';
$_lang['setting_mspre_export_weight_format_desc'] = 'По умолчанию "[3, ",", " "]"';

$_lang['setting_mspre_export_add_default_columns'] = 'Добавить колонку в файле с экспортом';
$_lang['setting_mspre_export_add_default_columns_desc'] = 'Вы можете добавить поле которое будет экспортироваться пустым или со значением. Формат добавления field:value';

$_lang['setting_mspre_character_separate_options'] = 'Разделитель опций при экспорте';
$_lang['setting_mspre_character_separate_options_desc'] = 'По умолчанию ||. Вы можете указать свой разделитель для опций';

$_lang['setting_mspre_alias_field_export'] = 'Алиасы для полей экспорта';
$_lang['setting_mspre_alias_field_export_desc'] = 'По умолчанию Пусто. Вы можете задать свои название полей для колонок XLS файлов. Например pagetitle:Заголовок или thumb:изображение';

$_lang['setting_mspre_allow_output_to_toolbar'] = 'Выводить в боковой панели иконки';
$_lang['setting_mspre_allow_output_to_toolbar_desc'] = 'По умолчанию product,resource. product - редактирование товаров, resource - редактирование ресурсов';

$_lang['setting_mspre_status_purchased_goods'] = 'Статус заказа для фильтрации купленных товаров';
$_lang['setting_mspre_status_purchased_goods_desc'] = 'По умолчанию 2 - оплачен. Можно перечислить через запятую список статусов товаров. Оставить пустым чтобы не учитывать статус заказа';

$_lang['setting_mspre_max_records_processed'] = 'Максимальное кол-во записей за один шаг';
$_lang['setting_mspre_max_records_processed_desc'] = 'По умолчанию 10. Вы можете указать большее количество записей обрабатываемых во время массовых действий за один шаг';

$_lang['setting_mspre_max_records_processed_all'] = 'Максимальное кол-во записей за найденных ресурсов';
$_lang['setting_mspre_max_records_processed_all_desc'] = 'По умолчанию 5000. Вы можете указать большее количество записей обрабатываемых во время массовых действий и найденных с учетом фильтров';

$_lang['setting_mspre_enable_save_setting_user'] = 'Хранить настройки полей для пользователя';
$_lang['setting_mspre_enable_save_setting_user_desc'] = 'По умолчанию Да. Если установить Нет то все настройки будут браться из системных настроек а не из настроек персонально установленных менеджером';

$_lang['setting_mspre_enable_msoptionsprice2'] = 'Включить модификации msOptionsPrice2';
$_lang['setting_mspre_enable_msoptionsprice2_desc'] = 'По умолчанию Да. Если установить Нет скрипты msOptionsPrice2 не будут загружаться';

$_lang['setting_mspre_mode_expert'] = 'Режим эксперт';
$_lang['setting_mspre_mode_expert_desc'] = 'По умолчанию Нет. Режим эксперт позволяет производить массовые действия над тысячами ресурсов за один раз.';

$_lang['setting_mspre_enable_plugins_minishop2'] = 'Включить плагины minishop2';
$_lang['setting_mspre_enable_plugins_minishop2_desc'] = 'По умолчанию Да. Включает поддержку редактирование полей в таблице с товарами';
