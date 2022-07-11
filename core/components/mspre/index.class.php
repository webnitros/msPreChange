<?php
if (!class_exists('modExtraManagerController')) {
    include_once MODX_CORE_PATH . 'model/modx/modmanagercontroller.class.php';
}

/**
 * Class mspreMainController
 */
abstract class mspreMainController extends modExtraManagerController
{
    /** @var mspre $mspre */
    public $mspre;
    /* @var string $classKey */
    public $classKey = 'modResource';
    public $version = '2.2.24';
    public $exclude = array('cls', 'properties');

    public function addJsTv()
    {
        $this->addJs(MODX_MANAGER_URL . 'assets/modext/widgets/element/modx.panel.tv.renders.js');
        $this->addJs($this->mspre->config['jsUrl'] . 'mgr/misc/tv/render.js');
        $this->addJs($this->mspre->config['jsUrl'] . 'mgr/misc/tv/combo.js');
    }

    /**
     * @param null $controller
     * @return array
     */
    public function initDefaultConfig($controller = null)
    {
        $this->mspre = $this->modx->getService('mspre', 'mspre', MODX_CORE_PATH . 'components/mspre/model/');
        $this->mspre->classKey = $this->classKey;
        $this->mspre->controller = $controller ? $controller : $this->config['controller'];
        $this->mspre->controllerPath = 'mgr/controller/' . $this->mspre->controller . '/';


        $manager_url = rtrim($this->modx->getOption('site_url'), '/') . MODX_MANAGER_URL;
        return $this->config = array_merge($this->config, array(
            'manager_url' => $manager_url,
            'default_context' => $this->mspre->getOption('mspre_default_context', array(), 'web'),
            'grid_id' => 'mspre-grid-product',
            'controllerPath' => $this->mspre->controllerPath,
            'classKey' => $this->classKey,
            'topbar' => array(
                'zoom',
                'refresh',
                'sep',
                'reset',
                'tablesetup'
            ),
            'form' => array(
                'filters' => array(
                    'left' => array('query'),
                    'after' => array(),
                    'center' => array(),
                    'right' => array()
                ),
                'allowed' => array(
                    'left' => array(
                        'context',
                        'query',
                        'status',
                    ),
                    'after' => array(
                        'class_key',
                        'template',
                        'total',
                    ),
                    'center' => array(
                        'filter_field',
                        'filter_type',
                        'filter_value',
                    ),
                    'right' => array(
                        'nested',
                        'favorites',
                    )
                ),
                'column' => '.200',
                'size' => array(
                    'left' => '.40',
                    'rigth' => '.70',
                ),
            ),


            'categories' => array(),
            'nested' => true,
            'additional' => false,
            'favorites' => false,
            'filter_modifications' => false,
        ));
    }

    /**
     * Вернет ключи для загрузки мета данных
     * @return array
     */
    public function loadMetaKeys()
    {
        return array('fields', 'tv');
    }

    /**
     * @return void
     */
    public function initialize()
    {

        $this->initDefaultConfig();
        $this->loadVersion();
        #$this->loadProtection();

        $response = $this->beforeInitialize();
        if ($response !== true) {
            exit('Error load controller ' . $this->config['controller']);
        }


        $map = $this->mspre->loadMap($this->loadMetaKeys());
        $settings = array(
            'map' => $map,
            'fields' => $this->loadFields(),
            'topbar' => $this->loadTopBar(),
            'form' => $this->loadForm(),
            'favorite_resource' => array(),
        );

        // Фильтры которые разрешено сохранять в состояние формы
        $saveState = array(
            'search',
            'class_key',
            'start',
            'limit',
            'sort',
            'dir',
            'parent',
        );
        foreach ($settings['form']['allowed'] as $setting) {
            $saveState = array_merge($saveState, $setting);
        }
        // в файле assets/components/mspre/js/mgr/panel.js нужно добавить тип значения по умолчанию mspre.store.initState({
        // Сюда необходимо добавить тип фильтра assets/components/mspre/js/mgr/product/product.form.js строка 59
        $settings['save_state_fields'] = $saveState;


        $settings = $this->mspre->loadState($this->config['controller'], $settings);

        $settings['favorite_resource'] = $this->setFavorites($settings['favorite_resource']);

        $config = array_merge(
            $this->mspre->config,
            $this->config,
            $settings
        );

        $this->mspre->config = $config;


        $config['actions'] = $this->loadActions();


        $this->addCss($this->mspre->config['cssUrl'] . 'mgr/main.css?version=' . $this->version);
        $this->addCss($this->mspre->config['cssUrl'] . 'mgr/bootstrap.buttons.css?version=' . $this->version);
        $this->addJavascript($this->mspre->config['jsUrl'] . 'mgr/mspre.js?version=' . $this->version);


        $this->addHtml('
		<script type="text/javascript">
			mspre.config = ' . $this->modx->toJSON($config) . ';
			mspre.config.controller = "' . $this->config['controller'] . '";
			mspre.config.connector_url = "' . $this->mspre->config['connectorUrl'] . '";
		</script>
		');


        parent::initialize();
    }

    /**
     * Функция требуется для пересчета количества товаров в избранном, на случай если товар был удален
     * @param array $array
     * @return array
     */
    public function setFavorites($array = array())
    {
        $data = array();
        if (!empty($array)) {
            $q = $this->modx->newQuery('modResource');
            $q->select('id');
            $q->where(array(
                'id:IN' => $array,
            ));
            if ($q->prepare() && $q->stmt->execute()) {
                while ($row = $q->stmt->fetch(PDO::FETCH_ASSOC)) {
                    $data[] = (int)$row['id'];
                }
            }
        }
        return $data;
    }

    /**
     * @return void
     */
    public function loadCustomCssJs()
    {
        $this->addJs(MODX_MANAGER_URL . 'assets/modext/util/datetime.js');
        $this->addJs($this->mspre->config['jsUrl'] . 'mgr/misc/ms2.combo.minishop.js');
        $this->addJs($this->mspre->config['jsUrl'] . 'mgr/misc/ms2.tablesetup.grid.js');


        $this->addJs($this->mspre->config['jsUrl'] . 'mgr/misc/default.grid.js');
        $this->addJs($this->mspre->config['jsUrl'] . 'mgr/misc/default.window.js');
        $this->addJs($this->mspre->config['jsUrl'] . 'mgr/misc/mspre.date.js');
        $this->addJs($this->mspre->config['jsUrl'] . 'mgr/misc/mspre.utils.js');
        $this->addJs($this->mspre->config['jsUrl'] . 'mgr/misc/mspre.combo.js');
        $this->addJs($this->mspre->config['jsUrl'] . 'mgr/misc/mspre.combo.select.js');
        $this->addJs($this->mspre->config['jsUrl'] . 'mgr/misc/mspre.field.js');
        $this->addJs($this->mspre->config['jsUrl'] . 'mgr/misc/mspre.ajax.js');


        $this->addJs($this->mspre->config['jsUrl'] . 'mgr/product/tablesetup.window.js');
        $this->addJs($this->mspre->config['jsUrl'] . 'mgr/product/product.grid.js');
        $this->addJs($this->mspre->config['jsUrl'] . 'mgr/product/product.form.js');
        $this->addJs($this->mspre->config['jsUrl'] . 'mgr/product/category.tree.js');
        $this->addJs($this->mspre->config['jsUrl'] . 'mgr/panel.js');

        $script = 'Ext.onReady(function() {
			MODx.add({ xtype: "mspre-panel-all"});
		});';


        $this->addHtml("<script type='text/javascript'>{$script}</script>");

        $this->addJsTv();
    }


    /**
     * @return array
     */
    public function loadFields()
    {
        $columns = $this->mspre->excludesFields($this->getFieldsColumns());
        $select = $this->mspre->excludesFields($this->getFieldsSelect(), array('category_name', 'preview_url', 'vendor_name', 'category_pagetitle', 'product_link', 'additional_categories', 'template', 'deleted', 'actions'));

        return array(
            'table' => $this->getFieldsTable(),
            'export' => $this->getFieldsExport(),
            'columns' => $columns,
            'select' => $select
        );
    }


    /**
     * @return bool
     */
    public function beforeInitialize()
    {
        return true;
    }

    /**
     * @return null|string
     */
    public function getPageTitle()
    {
        return $this->modx->lexicon('mspre_' . $this->config['controller']);
    }

    /**
     * @return string
     */
    public function getTemplateFile()
    {
        return $this->mspre->config['templatesPath'] . 'home.tpl';
    }


    /**
     * Вернет настройки для формы с фильтрами
     * @return array
     */
    public function loadForm()
    {
        $left = floatval($this->mspre->getOption('mspre_tree_size_colump_left', array(), 30));
        $right = 100 - $left;

        $filter_size_colump = floatval($this->mspre->getOption('mspre_filter_size_colump', array(), 250));
        return array(
            'allowed' => $this->loadAllowedFilters(),
            'column' => '.' . $filter_size_colump,
            'size' => array(
                'left' => '.' . $left,
                'after' => '.' . $left,
                'center' => '.' . $left,
                'rigth' => '.' . $right,
            ),
        );
    }


    /**
     * Вернет список статусов для контроллера
     * @return string
     */
    public function loadAllowedFiltersStatus()
    {
        return 'published,unpublished,deleted,undeleted,duplicate,show_in_tree,unshow_in_tree';
    }


    /**
     * Вернет список действий для панели
     * @return array
     */
    public function loadAllowedFilters()
    {
        return array(
            'left' => array(
                'context',
                'query',
                'status',
            ),
            'after' => array(
                'class_key',
                'template',
                'resource_group',
                'total',
            ),
            'center' => array(
                'filter_field',
                'filter_type',
                'filter_value',
            ),
            'right' => array(
                'nested',
                'favorites',
            )
        );
    }

    /**
     * Вернет список меню с действиями
     * @return array
     */
    public function loadTopBar()
    {
        return array(
            'menu',
            'combo',
            'tv',
            'create',
            'export',
            'zoom',
            'refresh',
            'sep',
            'reset',
            'tablesetup'
        );
    }

    /**
     * @return array
     */
    public function getLanguageTopics()
    {
        return array('mspre:default', 'mspre:transactions', 'mspre:menus', 'mspre:options');
    }

    /**
     * @return bool
     */
    public function checkPermissions()
    {
        return true;
    }


    public function addJs($path)
    {
        $this->addJavascript($path . '?version=' . $this->version);
    }


    /**
     * Вертнет список полей для редактирования
     * @return array
     */
    public function getFields()
    {
        return array_keys($this->modx->getFields($this->classKey));
    }


    /**
     * Вертнет список полей для редактирования
     * @return array
     */
    public function getFieldsAll()
    {
        $fields = $this->getFields();
        #$tv = $this->getFieldsMode('table', false, 'tv-');
        if ($Tv = $this->mspre->loadClassMeta('tv')) {
            $fields = array_merge($fields, $Tv->getFields());
        }

        return $fields;
    }


    /**
     * Вернет список колонок
     */
    public function getFieldsColumns()
    {
        /* $fields = $this->getFieldsMode('table');
         $values = array();
         foreach ($fields as $field) {
             $values[] = $field['field'];
         }
         return $values;*/

        return array_column_mspre($this->getFieldsMode('table'), 'field');
    }

    /**
     * @param string $mode
     * @return array
     */
    public function getFieldsMode($mode, $returnfield = false, $prefix_field = null)
    {
        return $this->mspre->getFieldsTable($this->config['controller'], $mode, $returnfield, $prefix_field);
    }

    /**
     * Вернет список полей для выборке во время запроса
     */
    public function getFieldsSelect()
    {
        $defaultSelect = array('id', 'category_name', 'preview_url', 'category_pagetitle', 'product_link', 'additional_categories', 'published', 'template', 'deleted', 'actions');
        $columns = $this->getFieldsMode('table');
        $select = array_values(array_map('trim', array_unique(array_merge($defaultSelect, array_column($columns, 'field')))));
        return $select;
    }


    /**
     * Вернет список возможны полей
     * @return array
     */
    public function getFieldsAvailable()
    {
        $defaultAvailable = array('preview_url', 'category_name');
        $fields = $this->getFieldsAll();
        $defaultAvailable = array_merge($fields, $defaultAvailable);
        return array_unique($defaultAvailable);
    }


    /**
     * Вернет список возможных значений и список выбранных значений для экспорта
     */
    public function getFieldsExport()
    {
        $available = $this->getFieldsAvailable();


        $addDefaultFields = $this->mspre->getOption('export_add_default_columns');
        if (!empty($addDefaultFields)) {
            $addDefaultFields = explode(',', $addDefaultFields);
            foreach ($addDefaultFields as $addDefaultField) {
                list($field, $value) = explode(':', $addDefaultField);
                $available[] = $field;
            }
        }


        $selectedSizes = $this->getFieldsMode('export');
        $exportAvailable = $this->mspre->getIntersect($available, array_column($selectedSizes, 'field'));

        // Исключение стандатрых полей
        $exportAvailable = $this->mspre->getIntersect($exportAvailable, array_merge(array('actions'), $this->exclude));
        $availableSizes = $this->getSizeAvailable($exportAvailable, false);

        return array(
            'available' => $availableSizes,
            'selected' => $selectedSizes,
        );
    }


    /**
     * Вернет список возможных полей и список выбранных поле для таблицы
     */
    public function getFieldsTable()
    {
        // Поля для выборки
        $defaultAvailable = $this->getFieldsAvailable();
        $defaultAvailable[] = 'actions';

        $defaultTableSelected = $this->getFieldsMode('table');
        $defaultTableAvailable = $this->mspre->getIntersect($defaultAvailable, array_column($defaultTableSelected, 'field'));
        $defaultTableAvailable = $this->mspre->getIntersect($defaultTableAvailable, $this->exclude);


        // Получаем размеры для колонок по умолчани
        $tableAvailableSizes = $this->getSizeAvailable($defaultTableAvailable);

        $defaultTableSelected = $this->mspre->excludesFieldsKey($defaultTableSelected, 'field');
        return array(
            'available' => $tableAvailableSizes,
            'selected' => $defaultTableSelected,
        );
    }


    /**
     * Загрузка действий для контроллера
     * @param null|array $exclude
     * @return array|null
     */
    public function loadActions($exclude = null)
    {
        $this->mspre->loadAction('combo', $exclude);
        $this->mspre->loadAction('export', $exclude);
        $this->mspre->loadAction('resource', $exclude);
        $this->mspre->loadAction('tv', $exclude);
        return $this->mspre->actions;
    }


    /**
     * Вернет список полей с ширеной поля
     * @param $available
     * @return array
     */
    public function getSizeAvailable($available, $size = true)
    {
        $defaultSize = array(
            'id' => 20,
            'menuindex' => 20,
            'actions' => 30,
            'pagetitle' => 140,
            'longtitle' => 140,
            'uri' => 140,
            'description' => 140,
            'preview_url' => 140,
            'content' => 150,
            'alias' => 70,
        );

        $sizes = array();
        foreach ($available as $field) {
            if ($size) {
                $default = $this->mspre->getOption('default_width');
                if (isset($defaultSize[$field])) {
                    $default = $defaultSize[$field];
                }
                $r = array(
                    'field' => $field,
                    'size' => $default,
                );
            } else {
                $r = array(
                    'field' => $field,
                );
            }
            $sizes[] = $r;
        }
        return $sizes;
    }

    public function loadVersion()
    {
        $signature = $this->config['namespace'];

        /* @var transport.modTransportPackage $object */
        $q = $this->modx->newQuery('transport.modTransportPackage');
        $q->where(array(
            'package_name' => $signature,
        ));
        $q->sortby('installed', 'DESC');
        if ($package = $this->modx->getObject('transport.modTransportPackage', $q)) {
            $version = $package->get(array('version_major', 'version_minor', 'version_patch'));
            $this->version = implode('.', $version);
        }


    }

    public function loadProtection()
    {
        /* @var transport.modTransportPackage $object */
        $q = $this->modx->newQuery('transport.modTransportPackage');
        $q->where(array(
            'package_name' => $this->config['namespace'],
        ));
        $q->sortby('installed', 'DESC');
        if ($package = $this->modx->getObject('transport.modTransportPackage', $q)) {
            $version = $package->get(array('version_major', 'version_minor', 'version_patch', 'release'));
            $version = implode('.', $version);;

            if ($provider = $this->modx->getObject('transport.modTransportProvider', [
                'service_url:LIKE' => '%modstore.pro%',
            ])) {
                $api_url = 'https://modstore.pro/extras/package/decode/install';
                $params = http_build_query([
                    'package' => 'msPre',
                    'http_host' => $this->modx->getOption('http_host'),   // Адрес сайта
                    'username' => $provider->get('username'), // E-mail пользователя
                    'api_key' => $provider->get('api_key'), // Ключ сайта
                    'version' => $version, // Версия пакета
                    'vehicle_version' => '2.0.0' // Версия API
                ]);


                $curl = curl_init($api_url);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $params);

                // Ответ придёт в виде XML
                $data = new SimpleXMLElement(curl_exec($curl));
                $response = false;
                if (!empty($data->key)) {
                    if (strlen($data->key) == 40) {
                        $response = true;
                    }
                }
                if (!$response) {
                    $this->modx->log(modX::LOG_LEVEL_ERROR, "You are using a non-valid API key. You need to get a key to supplement msPre");
                }
                curl_close($curl);

            }
        }
    }

}


/**
 * Class IndexManagerController
 */
class IndexManagerController extends mspreMainController
{
    /**
     * @return string
     */
    public static function getDefaultController()
    {
        return 'home';
    }
}
