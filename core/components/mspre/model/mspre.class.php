<?php


if (!function_exists('array_column_mspre')) {

    /**
     * Функция для замены array_column так как в некоторых версиях php возникает баг
     * @param array $array
     * @param string $columnKey
     * @param null $indexKey
     * @return array
     */
    function array_column_mspre($array, $columnKey, $indexKey = null)
    {
        $result = array();
        foreach ($array as $subArray) {
            if (is_null($indexKey) && array_key_exists($columnKey, $subArray)) {
                $result[] = is_object($subArray) ? $subArray->$columnKey : $subArray[$columnKey];
            } elseif (array_key_exists($indexKey, $subArray)) {
                if (is_null($columnKey)) {
                    $index = is_object($subArray) ? $subArray->$indexKey : $subArray[$indexKey];
                    $result[$index] = $subArray;
                } elseif (array_key_exists($columnKey, $subArray)) {
                    $index = is_object($subArray) ? $subArray->$indexKey : $subArray[$indexKey];
                    $result[$index] = is_object($subArray) ? $subArray->$columnKey : $subArray[$columnKey];
                }
            }
        }
        return $result;
    }

}


if (!function_exists('prefixOptions')) {

    /**
     * Вернет наименование поля опции
     * @param $key
     * @return bool|mixed
     */
    function prefixOptions($key)
    {
        $newkey = preg_replace('#^options-(.*?)#', '$1', $key);
        if ($newkey == $key) {
            return false;
        }
        return $newkey;
    }

}

if (!function_exists('prefixOptionsAdd')) {

    /**
     * Добавит префикс в поле
     * @param $key
     * @return bool|mixed
     */
    function prefixOptionsAdd($key)
    {
        $newkey = preg_replace('#^options-(.*?)#', '$1', $key);
        if ($newkey == $key) {
            return 'options-' . $key;
        }
        return $newkey;
    }

}
if (!function_exists('prefixTv')) {

    /**
     * Вернет наименование поля опции
     * @param $key
     * @return bool|mixed
     */
    function prefixTv($key)
    {
        $newkey = preg_replace('#^tv-(.*?)#', '$1', $key);
        if ($newkey == $key) {
            return false;
        }
        return $newkey;
    }

}

if (!function_exists('prefixTvAdd')) {

    /**
     * Добавит префикс в поле
     * @param $key
     * @return bool|mixed
     */
    function prefixTvAdd($key)
    {
        $newkey = preg_replace('#^tv-(.*?)#', '$1', $key);
        if ($newkey == $key) {
            return 'tv-' . $key;
        }
        return $newkey;
    }

}


if (!function_exists('prefixFields')) {

    /**
     * Вернет наименование поля опции
     * @param $key
     * @return bool|mixed
     */
    function prefixFields($key)
    {
        $newkey = preg_replace('#^fields-(.*?)#', '$1', $key);
        if ($newkey == $key) {
            return false;
        }
        return $newkey;
    }

}


if (!function_exists('prefixFieldsAdd')) {

    /**
     * Добавит префикс в поле
     * @param $key
     * @return bool|mixed
     */
    function prefixFieldsAdd($key)
    {
        $newkey = preg_replace('#^fields-(.*?)#', '$1', $key);
        if ($newkey == $key) {
            return 'fields-' . $key;
        }
        return $newkey;
    }

}


if (!function_exists('mspreGetJSONField')) {

    /**
     * @param array $metas
     * @param modX $modx
     * @param $field
     * @param $value
     * @return string
     */
    function mspreGetJSONField($metas, $modx, $field, $value, $character_separate_options)
    {
        $meta = $metas[$field];
        if ($key = prefixOptions($field) or $meta['phptype'] == 'json') {
            if (!empty($value)) {
                $data = is_array($value) ? $value : $modx->fromJSON($value);
                if (!empty($data)) {
                    $value = implode($character_separate_options, $data);
                }
            }
        }

        if (strripos($value, $character_separate_options) !== false) {
            $value = '';
        }
        return $value;
    }
}

if (!function_exists('mspreFormatWeight')) {
    /**
     * Function for weight format
     *
     * @param $weight
     *
     * @return int|mixed|string
     */
    function mspreFormatWeight($weight = 0)
    {
        if (!$wf = json_decode('[3, ",", " "]')) {
            $wf = array(3, ',', ' ');
        }
        $weight = number_format($weight, $wf[0], $wf[1], $wf[2]);
        if (true) {
            $tmp = explode($wf[1], $weight);
            $tmp[1] = rtrim(rtrim(@$tmp[1], '0'), '.');
            $weight = !empty($tmp[1])
                ? $tmp[0] . $wf[1] . $tmp[1]
                : $tmp[0];
        }

        return $weight;
    }
}


class mspre
{
    /* @var modX $modx */
    public $modx;

    public $namespace = 'mspre';

    /* @var string $classKey */
    public $classKey = null;

    /* @var string $controller */
    public $controller = null;

    /* @var miniShop2 $miniShop2 */
    public $miniShop2 = null;

    /* @var array|null $actions */
    public $actions = null;


    /* @var mspreMetaFields|null $fields */
    protected $fields = null;

    /* @var mspreMetaTv|null $tv */
    protected $tv = null;

    /* @var mspreMetaOptions|null $options */
    protected $options = null;

    public $controllers = array();


    /**
     * @param modX $modx
     * @param array $config
     */
    function __construct(modX &$modx, array $config = array())
    {
        $this->modx =& $modx;
        $corePath = $this->modx->getOption('mspre_core_path', $config, $this->modx->getOption('core_path') . 'components/mspre/');
        $assetsUrl = $this->modx->getOption('mspre_assets_url', $config, $this->modx->getOption('assets_url') . 'components/mspre/');
        $connectorUrl = $assetsUrl . 'connector.php';
        $connectorUrlMinishop = '/assets/components/minishop2/connector.php';
        $this->config = array_merge(array(
            'assetsUrl' => $assetsUrl,
            'cssUrl' => $assetsUrl . 'css/',
            'jsUrl' => $assetsUrl . 'js/',
            'imagesUrl' => $assetsUrl . 'images/',
            'connectorUrl' => $connectorUrl,
            'connectorUrlMinishop' => $connectorUrlMinishop,

            'corePath' => $corePath,
            'modelPath' => $corePath . 'model/',

            'templatesPath' => $corePath . 'elements/templates/',
            'actionsPath' => $corePath . 'model/actions/',
            'chunkSuffix' => '.chunk.tpl',
            'snippetsPath' => $corePath . 'elements/snippets/',
            'processorsPath' => $corePath . 'processors/',
            'controllersPath' => $corePath . 'controllers/',
            'action' => $assetsUrl . 'action.php',

            'metaPath' => $corePath . 'model/meta/',
            'dataPath' => $corePath . 'model/meta/data/',

            'exportUrl' => $this->modx->getOption('site_url') . substr($assetsUrl, 1) . 'export.php',
            'default_width' => (int)$this->modx->getOption('mspre_default_width', $config, 70),
            'max_records_processed' => (int)$this->modx->getOption('mspre_max_records_processed', $config, 10),
            'max_records_processed_all' => (int)$this->modx->getOption('mspre_max_records_processed_all', $config, 5000),
            'mode_expert' => (boolean)$this->modx->getOption('mspre_mode_expert', $config, false),
        ), $config);
        $this->modx->addPackage('mspre', $this->config['modelPath']);
        $this->modx->lexicon->load('mspre:default');
        $this->modx->lexicon->load('mspre:transactions');
        $this->modx->lexicon->load('mspre:menus');
        $this->modx->lexicon->load('mspre:options');
        $this->modx->lexicon->load('mspre:filter');


        $enable = $this->modx->getOption('mspre_enable_msoptionsprice2', null, true);
        if ($enable) {
            $component = MODX_CORE_PATH . 'components/msoptionsprice/model/msoptionsprice/msoptionsprice.class.php';
            if (file_exists($component)) {
                $this->isEbableOptionPrice2 = true;
            }
        }

    }

    /**
     * Загрузка класса
     * @param string $name имя класса с действиями
     * @return mspreActions|boolean
     */
    public function loadActionClass($name)
    {
        if (!class_exists('mspreActions')) {
            include_once $this->config['actionsPath'] . 'default.php';
        }
        $file = $this->config['actionsPath'] . $name . '.class.php';
        if (!file_exists($file)) {
            return false;
        }
        $class = include_once($file);
        if (!class_exists($class)) {
            return false;
        }

        /* @var mspreActions $action */
        return new $class($this);
    }

    /**
     * Загрузка действиями
     * @param string $name имя класса с действиями
     * @param array|null $exclude исключаемые действия
     * @return bool
     */
    public function loadAction($name, $exclude)
    {
        if (!class_exists('mspreActions')) {
            include_once $this->config['actionsPath'] . 'default.php';
        }
        $file = $this->config['actionsPath'] . $name . '.class.php';
        if (!file_exists($file)) {
            return false;
        }
        $class = include_once($file);
        if (!class_exists($class)) {
            return false;
        }

        /* @var mspreActions $action */
        $action = new $class($this);
        $this->actions[$name] = $action->excludeAction($exclude);
        return true;
    }

    /**
     * Проверка установки minishop2
     * @return bool
     */
    public function isMinishop2()
    {
        if (!file_exists(MODX_CORE_PATH . 'components/minishop2')) {
            return false;
        }
        return true;
    }


    /**
     * Загрузка minishop2
     * @return bool|miniShop2
     */
    public function loadMinishop2()
    {
        if (is_null($this->miniShop2)) {
            if (!$this->isMinishop2()) {
                return false;
            }

            /* @var miniShop2 $miniShop2 */
            $miniShop2 = $this->modx->getService('minishop2');
            if ($miniShop2 instanceof miniShop2) {
                $this->miniShop2 = $miniShop2;
                // Отключение получения дополнительных полей для minishop2 чтобы не делать дополнительные запросы на получение данных
                $this->modx->setOption('ms2_category_show_options', false);
                $miniShop2->loadMap();
            } else {
                $this->miniShop2 = false;
            }
        }
        return $this->miniShop2;
    }


    /**
     * Загруказка типов полей
     * @param $name
     * @return array|mixed
     */
    public function loadData($name)
    {
        $path = $this->config['dataPath'] . $name . '.php';

        if (!file_exists($path)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, "Error load data {$name} could not found file", '', __METHOD__, __FILE__, __LINE__);
            return array();
        }
        $types = include_once $path;
        if (!is_array($types) or empty($types)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, "Error load data field {$name}", '', __METHOD__, __FILE__, __LINE__);
            return array();
        }
        return $types;
    }


    /**
     * Загрузка действиями
     * @param string $name имя класса с действиями
     * @param array|null $exclude исключаемые действия
     * @return bool|mspreMeta
     */
    public function loadClassMeta($name)
    {
        if (!isset($this->controllers[$name])) {

            if (!class_exists('mspreMeta')) {
                include_once $this->config['metaPath'] . 'default.php';
            }
            $file = $this->config['metaPath'] . $name . '.class.php';
            if (!file_exists($file)) {
                return false;
            }
            $class = include_once($file);
            if (!class_exists($class)) {
                return false;
            }

            /*@var mspreMeta $handler*/
            $handler = new $class($this);
            if (!($handler instanceof mspreMetaInterface) or !$handler->initialize()) {
                #$this->modx->log(modX::LOG_LEVEL_ERROR, 'Could not initialize mspreMetaInterface prepare handler class: "' . $class . '"');
                return null;
            }
            $this->controllers[$name] = $handler;
        }

        return $this->controllers[$name];
    }


    /**
     * @param       $key
     * @param array $config
     * @param null $default
     *
     * @return mixed|null
     */
    public function getOption($key, $config = array(), $default = null, $skipEmpty = false)
    {
        $option = $default;
        if (!empty($key) and is_string($key)) {
            if ($config != null and array_key_exists($key, $config)) {
                $option = $config[$key];
            } else if (array_key_exists($key, $this->config)) {
                $option = $this->config[$key];
            } else if (array_key_exists("{$this->namespace}_{$key}", $this->modx->config)) {
                $option = $this->modx->getOption("{$this->namespace}_{$key}");
            } else {
                if ($object = $this->modx->getObject('modSystemSetting', array('key' => $key))) {
                    $option = $object->get('value');
                }
            }
        }
        if ($skipEmpty and empty($option)) {
            $option = $default;
        }
        return $option;
    }


    /**
     * Вернет настройки пользвоателей или системные
     * @param $keyOption
     * @return mixed|null
     */
    public function getOptionUserOrSystem($keyOption)
    {
        $saveSettingUser = (boolean)$this->modx->getOption('mspre_enable_save_setting_user');
        $valueOption = $this->getOption($keyOption, null, array());
        if ($saveSettingUser) {
            $default = null;
            $settingUser = $this->modx->user->getSettings();
            if (array_key_exists($keyOption, $settingUser)) {
                $default = $settingUser;
            }


            $valueOption = $this->modx->user->getOption($keyOption, $default, $valueOption);
        }
        return $valueOption;
    }

    /**
     * Вернет поля из настроек
     * @param string $controller
     * @param string $mode
     * @return array
     */
    public function getFieldsTable($controller, $mode = 'table', $returnfield = false, $prefix_field = null)
    {
        $keyOption = 'mspre_' . $controller . '_' . $mode . '_selected_fields';

        $valueOption = $this->getOptionUserOrSystem($keyOption);


        if (empty($valueOption)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, "Error empty {$keyOption}", '', __METHOD__, __FILE__, __LINE__);
            return $valueOption = array('id:30', 'pagetitle:150');
        }

        $result = array();
        if (!empty($valueOption)) {
            $data = array_map('trim', explode(',', $valueOption));
            if (count($data) > 0) {
                foreach ($data as $field) {
                    if (strripos($field, ':') !== false) {
                        list($field, $size) = explode(':', $field);
                        $r = array(
                            'field' => $field,
                            'size' => $size,
                        );
                    } else {
                        $r = array(
                            'field' => $field,
                        );
                    }
                    $result[] = $r;
                }
            }
        }
        if ($returnfield) {
            return array_column($result, 'field');
        }

        if ($prefix_field and !empty($result)) {
            $newResult = array();
            $fields = array_column($result, 'field');
            if (!empty($fields)) {
                foreach ($fields as $column) {
                    if (strripos($column, $prefix_field) !== false) {
                        $newResult[] = str_ireplace($prefix_field, '', $column);;
                    }
                }
                return $newResult;
            }
        }
        return $result;
    }


    /**
     * Вернет список доступных полей для вывода и операций
     * @param array $fields
     * @return mixed
     */
    public function getAvailable($fields)
    {
        $exclude = $this->getIntersect($fields, array_column($this->config['fields']['table']['available'], 'field'));
        foreach ($fields as $i => $field) {
            if (in_array($field, $exclude)) {
                unset($fields[$i]);
            }
        }
        return $fields;
    }


    /**
     * Возвращает возможные значения с исключение выбранных значений
     * @param array $available
     * @param array $selected
     * @return mixed
     */
    public function getIntersect($available, $selected)
    {
        $a = array_flip($available);
        $s = array_flip($selected);
        $intersect = array_keys(array_intersect_key($a, $s));
        foreach ($intersect as $k => $key) {
            if (isset($a[$key])) {
                unset($a[$key]);
            }
        }
        return array_keys($a);
    }

    /**
     * @param modSystemEvent $event
     * @param array $scriptProperties
     * @param mixed $controller
     */
    public function handleEvent(modSystemEvent $event, array $scriptProperties, &$controller)
    {
        /* @var array $fields */
        /* @var array $product */
        extract($scriptProperties);
        switch ($event->name) {
            case 'OnManagerPageBeforeRender':
                $namespace = $controller->config['namespace'];
                if ($namespace != 'mspre') {
                    $this->modx->controller->addJavascript(MODX_ASSETS_URL . 'components/mspre/js/mgr/enabled.js');
                    $this->modx->controller->addHtml('<script>Ext.onReady(function() {
                        var tree = Ext.getCmp("modx-resource-tree");
                        tree.mspreResource = function() {
                            MODx.loadPage("resource&namespace=mspre")
                        }
                        tree.mspreProduct = function() {
                            MODx.loadPage("product&namespace=mspre")
                        }
                    });</script>');

                }
                break;
            case 'OnResourceToolbarLoad':
                /* @var array $items */
                if (count($items) > 0) {

                    $toolbar = array_map('trim', explode(',', $this->modx->getOption('mspre_allow_output_to_toolbar', null, 'product,resource')));

                    $add = array();
                    if (in_array('resource', $toolbar)) {
                        $add[] = array(
                            'id' => 'mspre-panel-resource',
                            'cls' => 'tree-new-tv',
                            'tooltip' => $this->modx->lexicon('mspre_panel_modx_resource'),
                            'handler' => 'this.mspreResource',
                        );

                    }
                    if (in_array('product', $toolbar)) {
                        if ($this->isMinishop2()) {
                            $add[] = array(
                                'id' => 'mspre-panel-product',
                                'cls' => 'tree-new-chunk',
                                'tooltip' => $this->modx->lexicon('mspre_panel_modx_product'),
                                'handler' => 'this.mspreProduct',
                            );
                        }
                    }


                    if (count($add) > 0) {
                        $items = array_merge($items, $add);
                        exit($this->modx->toJSON($this->modx->error->success('', array_values($items))));
                    }
                }

                break;
            // Добавление поля в массив
            case 'msPreExportGetFields':
                /* @var array $fields */
                if (!isset($this->modx->event->returnedValues['fields'])) {
                    $this->modx->event->returnedValues['fields'] = $fields;
                }

                // Get link to fields
                $fields = &$this->modx->event->returnedValues['fields'];
                $fields[] = 'old_price';
                $this->modx->event->returnedValues['fields'] = $fields;
                break;

            // Добавление значения в массив
            case 'msPreExportToArrayAfter':
                /* @var array $product */
                /* @var msProduct $object */
                if (!isset($this->modx->event->returnedValues['product'])) {
                    $this->modx->event->returnedValues['product'] = $product;
                }
                // Get link to product
                $product = &$this->modx->event->returnedValues['product'];
                $product['old_price'] = 10000;
                $this->modx->event->returnedValues['product'] = $product;
                break;

        }
    }


    public function GetUsage()
    {
        global $tstart, $modx;

        $out = '';
        $memory = round(memory_get_usage(true) / 1024 / 1024, 4) . ' Mb';

        $out .= "<div>Memory: {$memory}</div>";

        $totalTime = (microtime(true) - $tstart);
        $totalTime = sprintf("%2.4f s", $totalTime);

        if (!empty($modx)) {
            $queryTime = $modx->queryTime;
            $queryTime = sprintf("%2.4f s", $queryTime);
            $queries = isset ($modx->executedQueries) ? $modx->executedQueries : 0;

            $phpTime = $totalTime - $queryTime;
            $phpTime = sprintf("%2.4f s", $phpTime);

            $out .= "<div>queries: {$queries}</div>";

            $out .= "<div>queryTime: {$queryTime}</div>";

            $out .= "<div>phpTime: {$phpTime}</div>";
        }

        $out .= "<div>TotalTime: {$totalTime}</div>";

        return $out;
    }

    /* @var array|null $tableFields */
    public $tableFields = null;

    /**
     * Найдет префикс и вернут наименование поля
     * @param string $field
     * @param string $prefix
     * @return bool|string
     */
    public function getCutPrefixOptions($field, $prefix = 'options-')
    {
        if (strripos($field, $prefix) !== false) {
            return str_ireplace($prefix, '', $field);
        }
        return false;
    }

    /**
     * Проверка доступа в категории
     * @param int|object $productId
     * @param string $field
     * @return boolean
     */
    public function categoryAccessCheck($productId, $field)
    {
        /* @var msOption $Option */
        if ($Option = $this->modx->getObject('msOption', array('key' => $field))) {

            if (is_object($productId)) {
                $Product = $productId;
            } else {
                if (!$Product = $this->modx->getObject('msProduct', $productId)) {
                    return $this->modx->lexicon('mspre_options_product_error', array('key' => $field, 'product_id' => $productId));
                }
            }

            $id = $Product->get('id');

            /* @var msCategory $Category */
            if ($Category = $Product->getOne('Category')) {

                if (!$categoryBinding = $this->categoryBindingCheck(array(
                    'option_id' => $Option->get('id'),
                    'category_id' => $Category->get('id'),
                    'active' => 1,
                ))) {
                    return $this->modx->lexicon('mspre_options_category_link_error', array('product_id' => $id, 'key' => $field, 'category_id' => $Category->get('id'), 'category_name' => $Category->get('pagetitle')));
                } else {
                    return true;
                }

            } else {
                return $this->modx->lexicon('mspre_options_category_error', array('category_id' => $Product->get('parent'), 'product_id' => $id, 'category_name' => $Category->get('pagetitle')));
            }

        } else {
            return $this->modx->lexicon('mspre_options_type_error', array('key' => $field));
        }
    }

    /**
     * @param array $criteria
     * @return msCategoryOption|bool
     */
    public function categoryBindingCheck(array $criteria = array())
    {
        if ($object = $this->modx->getObject('msCategoryOption', $criteria)) {
            return $object;
        }
        return false;
    }

    /**
     * Вернет выбранные поля для контроллера
     * @param string $controller
     * @param null|array $prefixs
     * @param bool $cut_prefix
     * @return array|null
     */
    public function getSelectedFields($controller, $prefixs = null, $cut_prefix = false, $exclude_prefix = null)
    {
        if (is_null($this->tableFields)) {
            $this->tableFields = $this->getFieldsTable($controller, 'table', true);
        }

        if ($prefixs) {
            $fields = null;
            if (!is_array($prefixs)) {
                $prefixs = array($prefixs);
            }

            foreach ($prefixs as $prefix) {
                $k = $prefix . '-';
                foreach ($this->tableFields as $field) {
                    if (strripos($field, $k) !== false) {
                        if ($cut_prefix) {
                            $field = str_ireplace($k, '', $field);
                        }
                        $fields[] = $field;
                    }
                }
            }
            return $fields;
        }


        if ($exclude_prefix and is_array($exclude_prefix) and $this->tableFields) {
            $fields = $this->tableFields;
            foreach ($fields as $i => $field) {
                foreach ($exclude_prefix as $prefix) {
                    if (strripos($field, $prefix . '-') !== false) {
                        unset($fields[$i]);
                    }
                }
            }
            return $fields;
        }
        return $this->tableFields;
    }


    /**
     * Transform array to placeholders
     *
     * @param array $array
     * @param string $plPrefix
     * @param string $prefix
     * @param string $suffix
     * @param bool $uncacheable
     *
     * @return array
     */
    public function makePlaceholders(
        array $array = array(),
              $plPrefix = '',
              $prefix = '[[+',
              $suffix = ']]',
              $uncacheable = true
    )
    {
        $result = array('pl' => array(), 'vl' => array());

        $uncached_prefix = str_replace('[[', '[[!', $prefix);
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                $result = array_merge_recursive($result,
                    $this->makePlaceholders($v, $plPrefix . $k . '.', $prefix, $suffix, $uncacheable));
            } else {
                $pl = $plPrefix . $k;
                $result['pl'][$pl] = $prefix . $pl . $suffix;
                $result['vl'][$pl] = $v;
                if ($uncacheable) {
                    $result['pl']['!' . $pl] = $uncached_prefix . $pl . $suffix;
                    $result['vl']['!' . $pl] = $v;
                }
            }
        }

        return $result;
    }

    /**
     * Загрузка контроллера менеджера
     * @param string $name
     * @return mspreMainController|false
     */
    public function loadManagerController($name)
    {
        /* @var mspreMainController $conroller */
        $class = 'mspre' . ucfirst($name) . 'ManagerController';

        $file = MODX_CORE_PATH . '/components/mspre/controllers/' . $name . '.class.php';
        if (!file_exists($file)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, "Файл контроллера не найден {$file}", '', __METHOD__, __FILE__, __LINE__);
        }
        if (!class_exists($class)) {
            include_once MODX_CORE_PATH . '/components/mspre/controllers/' . $name . '.class.php';
        }

        if (class_exists($class)) {
            $conroller = new $class($this->modx);
        } else {
            $this->modx->log(modX::LOG_LEVEL_ERROR, "Не удалось загрузить контроллер менеджера {$class}", '', __METHOD__, __FILE__, __LINE__);
            return false;
        }
        return $conroller;
    }


    /**
     * Загрузка действиями
     * @param array $load грузит мета данные для
     * @return array|boolean
     */
    public function getFieldMeta($load = array('fields', 'options', 'tv'))
    {
        if (!is_array($load)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, "Не верно указана переменна load. Должна быть массивом", '', __METHOD__, __FILE__, __LINE__);
            return false;
        }
        $fields = array();
        if (in_array('fields', $load)) {
            if ($ClassFields = $this->loadClassMeta('fields')) {
                $fields = $ClassFields->getMetaData(true);
            }
        }

        $options = array();
        if (in_array('options', $load)) {
            if ($ClassOptions = $this->loadClassMeta('options')) {
                $options = $ClassOptions->getMetaData();
            }
        }

        $tv = array();
        if (in_array('tv', $load)) {
            if ($ClassTv = $this->loadClassMeta('tv')) {
                $tv = $ClassTv->getMetaData();
            }
        }

        return array_merge($fields, $options, $tv);
    }


    /* @var array|null $map */
    protected $map = null;


    /**
     * @return array
     */
    public function loadMap($keys = array('fields', 'options', 'tv'))
    {
        if (is_null($this->map)) {
            $this->map = $this->getFieldMeta($keys);
        }
        return $this->map;
    }


    /**
     * @param array $fields
     * @param null|array $addkeys
     * @return array
     */
    public function excludesFields($fields, $addkeys = null)
    {
        $map = $this->loadMap();
        $keys = array();
        foreach ($fields as $key) {
            if (array_key_exists($key, $map)) {
                $keys[] = $key;
            }
        }

        if ($addkeys) {
            $keys = array_merge($keys, $addkeys);
        }
        return $keys;
    }

    /**
     * @param array $fields
     * @param null|array $addkeys
     * @return array
     */
    public function excludesFieldsKey($fields, $field_key)
    {
        $map = $this->loadMap();
        $keys = array();
        foreach ($fields as $key => $value) {
            $k = $value[$field_key];
            if (array_key_exists($k, $map)) {
                $keys[] = $value;
            }
        }
        return array_values($keys);
    }

    /**
     * Проверка доступа к контроллеру
     * @param $name
     * @return bool
     */
    public function allowedController($name)
    {
        $controllers = array(
            'product',
            'resource'
        );
        return in_array($name, $controllers) ? $name : false;
    }


    /**
     * Вернет имя контроллера по ckass_key
     * @param $classKey
     * @return null|string
     */
    public function getAclassController($classKey)
    {
        $controller = null;
        switch ($classKey) {
            case 'msProduct':
                $controller = 'product';
                break;
            case 'modResource':
                $controller = 'resource';
                break;
            default:
                break;
        }
        return $controller;
    }


    /**
     * Состояния которые разрешенно фиксировать
     * @return string[]
     */
    public function stateSettings()
    {
        return array(
            'categories',
            'nested',
            'additional',
            'favorite_resource',
            'filter_modifications',
            'purchased_goods',
            'zoom',
        );
    }

    /**
     * Загрузка состояния стараницы
     * @param array $settings
     * @return array
     */
    public function loadState($controller, $settings = array())
    {
        /* @var modProcessorResponse $response */
        $user_id = $this->modx->user->id;
        $data = array(
            'poll_limit' => 1,
            'poll_interval' => 1,
            'time_limit' => 10,
            'message_limit' => 1000,
            'remove_read' => 0,
            'show_filename' => 0,
            'include_keys' => 1,
            'register' => 'msprestate2' . $controller,
            'topic' => '/ys/user-' . $user_id . '/',
        );

        $response = $this->modx->runProcessor('system/registry/register/read', $data);
        if (!$response->isError()) {
            $message = $response->response['message'];
            if (!empty($message)) {
                $message = is_array($message) ? $message : $this->modx->fromJSON($message);
                $keys = $this->stateSettings();
                foreach ($keys as $key) {
                    if (isset($message[$key])) {
                        $settings[$key] = $message[$key];
                    }
                }
            }
        }
        return $settings;

    }


    protected $cacheOptions = array(
        xPDO::OPT_CACHE_KEY => 'default/mspre',
        xPDO::OPT_CACHE_HANDLER => 'xPDOFileCache'
    );

    protected $cacheKey = 'cyclic_ids';

    /**
     * Запишет список ID товаров сохранение для циклической обработки
     * @return boolean
     */
    public function setCacheManager($ids = array())
    {
        $result = false;
        /* @var modCacheManager $cacheManager */
        $cacheManager = $this->modx->getCacheManager();

        /* @var modCacheManager $cacheManager */
        if ($cacheManager instanceof modCacheManager) {
            $ids = array_map('intval', $ids);
            // Сохраняем список полученых ID в кэш для получения
            $result = $cacheManager->set($this->cacheKey, $ids, 10000, $this->cacheOptions);
        }
        return $result;
    }


    /**
     * Вренет список ID товаров сохранение для циклической обработки
     * @return array|null
     */
    public function getCacheManager()
    {
        $result = null;
        /* @var modCacheManager $cacheManager */
        if ($cacheManager = $this->modx->getCacheManager()) {
            $data = $cacheManager->get($this->cacheKey, $this->cacheOptions);
            if (!empty($data)) {
                $result = $data;
            }
        }
        return $result;
    }

    /**
     * Shorthand for original modX::invokeEvent() method with some useful additions.
     *
     * @param $eventName
     * @param array $params
     * @param $glue
     *
     * @return array
     */
    public function invokeEvent($eventName, array $params = array(), $glue = '<br/>')
    {
        if (isset($this->modx->event->returnedValues)) {
            $this->modx->event->returnedValues = null;
        }

        $response = $this->modx->invokeEvent($eventName, $params);
        if (is_array($response) && count($response) > 1) {
            foreach ($response as $k => $v) {
                if (empty($v)) {
                    unset($response[$k]);
                }
            }
        }

        $message = is_array($response) ? implode($glue, $response) : trim((string)$response);
        if (isset($this->modx->event->returnedValues) && is_array($this->modx->event->returnedValues)) {
            $params = array_merge($params, $this->modx->event->returnedValues);
        }

        return array(
            'success' => empty($message),
            'message' => $message,
            'data' => $params,
        );
    }


    /* @var msPreExecel|null|boolean $msPreExecel */
    protected $msPreExecel = null;

    /**
     * @return bool|msPreExecel
     */
    public function loadExecel()
    {
        if (!class_exists('msPreExecel')) {
            include_once $this->config['corePath'] . 'lib/mspreexecel.class.php';
        }
        if (!class_exists('msPreExecel')) {
            return false;
        }
        $msPreExecel = new msPreExecel($this->config);
        return $msPreExecel;
    }


    protected $isEbableOptionPrice2 = false;

    /**
     * Проверяет включение msOptionPrice2
     */
    public function isEnablemsOptionsPrice2()
    {
        return $this->isEbableOptionPrice2;
    }

    /**
     * Подключение класса
     * @param false $js
     */
    public function msOptionsPrice2($js = false)
    {
        /* @var msoptionsprice $msoptionsprice */
        $msoptionsprice = $this->modx->getService('msoptionsprice', 'msoptionsprice', MODX_CORE_PATH . 'components/msoptionsprice/model/msoptionsprice/');
        if ($js) {

            $msoptionsprice->loadControllerJsCss($this->modx->controller, array(
                'css' => true,
                'config' => true,
                'tools' => true,
                'option' => true,
                'modification' => true,
                'gallery' => true,
                'resource/inject' => false,
            ));

        }
    }
}
