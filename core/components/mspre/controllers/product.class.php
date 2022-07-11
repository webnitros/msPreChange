<?php
if (!class_exists('msManagerController')) {
    require_once dirname(dirname(__FILE__)) . '/index.class.php';
}

/**
 * The home manager controller for mspre.
 *
 */
class mspreProductManagerController extends mspreMainController
{
    public $classKey = 'msProduct';

    /**
     * @return bool
     */
    public function beforeInitialize()
    {
        // Только для товаров
        if (!$this->mspre->loadMinishop2()) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, "Errir load controlle product, Could not found minishop2", '', __METHOD__, __FILE__, __LINE__);
            return false;
        }
        $this->modx->lexicon->load('minishop2:product');
        return true;
    }


    /**
     * @return array
     */
    public function getLanguageTopics()
    {
        return array_merge(parent::getLanguageTopics(), array('minishop2:product', 'msoptionsprice:default'));
    }

    public function getFieldsAvailable()
    {
        $fields = parent::getFieldsAvailable();
        $fields = array_merge($fields, array('product_link', 'additional_categories'));
        return $fields;
    }


    /**
     * @return void
     */
    public function loadCustomCssJs()
    {
        $v = 1;

        $this->addJs($this->mspre->config['jsUrl'] . 'mgr/transactions/grid.js?v=' . $v);


        $this->addJs($this->mspre->config['jsUrl'] . 'mgr/misc/ms2.combo.minishop.js?v=' . $v);

        parent::loadCustomCssJs();

        // Регистрация minishop2 для загрузки комбо для опций
        $this->addJs($this->mspre->config['jsUrl'] . 'mgr/minishop2.js');
        $this->addJs($this->mspre->miniShop2->config['jsUrl'] . 'mgr/misc/ms2.combo.js');

        if ((boolean)$this->modx->getOption('mspre_enable_plugins_minishop2')) {
            // Загружаем плагины
            $this->loadPlugins();
        }

        $this->addHtml('
		<script type="text/javascript">
			miniShop2.config = ' . $this->modx->toJSON(array(
                'isEbableOptionPrice2' => $this->mspre->isEnablemsOptionsPrice2()
            )) . ';
			miniShop2.config.controller = "' . $this->config['controller'] . '";
			miniShop2.config.connector_url = "/assets/components/minishop2/connector.php";
			miniShop2.config.msoptionsprice_connector_url = "/assets/components/msoptionsprice/connector.php";
		</script>
		');

        $this->addJs($this->mspre->config['jsUrl'] . 'mgr/misc/options/render.js');
        $this->addJs($this->mspre->config['jsUrl'] . 'mgr/misc/options/combo.js');
        $this->addJsTv();


        // Подключение JS
        if ($this->mspre->isEnablemsOptionsPrice2()) {
            $this->mspre->msOptionsPrice2(true);
            $this->addJs($this->mspre->config['jsUrl'] . 'mgr/product/optionprice/grid.js?v=' . $v);
        }
    }

    /**
     * Loads additional scripts for product form from miniShop2 plugins
     */
    function loadPlugins()
    {
        $managerPlugin = [];
        // Original plugins
        $plugins = scandir($this->mspre->miniShop2->config['pluginsPath']);
        foreach ($plugins as $plugin) {
            if ($plugin == '.' || $plugin == '..') {
                continue;
            }
            $dir = $this->mspre->miniShop2->config['pluginsPath'] . $plugin;

            if (is_dir($dir) && file_exists($dir . '/index.php')) {
                /** @noinspection PhpIncludeInspection */
                $include = include($dir . '/index.php');
                if (is_array($include)) {
                    $managerPlugin[$plugin] = $include['manager'];
                }
            }
        }

        foreach ($managerPlugin as $plugin) {
            if (!empty($plugin['msProductData'])) {
                $this->addJavascript($plugin['msProductData']);
            }
        }

    }

    /**
     * Вернет список статусов для контроллера
     * @return string
     */
    public function loadAllowedFiltersStatus()
    {
        return 'published,unpublished,deleted,undeleted,duplicate,show_in_tree,unshow_in_tree,new,unnew,popular,unpopular,favorite,unfavorite,image,unimage,duplicate_article,more_category,not_more_category';
    }

    /**
     * @return array
     */
    public function loadAllowedFilters()
    {
        $arrays = array(
            'left' => array(
                'context',
                'query',
                'status',
                'product_link',
            ),
            'after' => array(
                'vendor',
                'template',
                'resource_group',
                'total',
            ),
            'center' => array(
                'filter_field',
                'filter_type',
                'filter_value',

                'option_key',
                'option_value_exclude',
                'option_value',
            ),
            'right' => array(
                'nested',
                'additional',
                'favorites',
                'purchased_goods',
            )
        );


        if ($this->mspre->isEnablemsOptionsPrice2()) {
            $arrays['right'][] = 'filter_modifications';
        }
        return $arrays;
    }

    /**
     * @return array
     */
    public function loadTopBar()
    {
        $menus = parent::loadTopBar();
        $newMenus = array();
        foreach ($menus as $value) {
            $newMenus[] = $value;
            if ($value == 'combo') {
                $newMenus = array_merge($newMenus, array('product_menu', 'options'));
            }
        }
        return $newMenus;
    }


    /**
     * @return array
     */
    public function getFields()
    {
        /* @var msProduct $object */
        $getAllFieldsNames = array();
        $product = $this->modx->newObject($this->classKey);
        #$fields = array_merge($getAllFieldsNames, $product->getAllFieldsNames());
        $fields = array_merge(
            array_merge($getAllFieldsNames, $product->getAllFieldsNames()),
            array('vendor_name')
        );
        $options = array();
        $q = $this->modx->newQuery('msOption');
        $q->select('key');
        if ($q->prepare() && $q->stmt->execute()) {
            while ($row = $q->stmt->fetch(PDO::FETCH_ASSOC)) {
                $options[] = $row['key'];
            }
        }
        if (!empty($options) and is_array($options)) {
            $prefixed_array = preg_filter('/^/', 'options-', $options);
            $fields = array_merge($fields, $prefixed_array);
        }
        return $fields;
    }

    /**
     * @param null|array $exclude
     * @return array
     */
    public function loadActions($exclude = null)
    {
        $this->mspre->loadAction('options', $exclude);
        $this->mspre->loadAction('product', $exclude);
        return parent::loadActions($exclude);
    }


    /**
     * Загрузка полей для таблиц
     *
     * public function loadFields()
     * {
     * return array_merge(parent::loadFields(), array(
     * 'options' => $this->getFieldsOptions(),
     * ));
     * } */

    /**
     * Поля опции minishop2
     *
     * private function getFieldsOptions()
     * {
     * $available = array();
     * $q = $this->modx->newQuery('msOption');
     * $q->select('key,caption');
     * $q->where(array(
     * 'type:IN' => array(
     * 'combo-multiple',
     * 'combobox',
     * 'combo-options',
     * 'numberfield',
     * 'combo-boolean',
     * 'textfield',
     * 'checkbox',
     * ),
     * ));
     *
     * if ($q->prepare() && $q->stmt->execute()) {
     * while ($row = $q->stmt->fetch(PDO::FETCH_ASSOC)) {
     * $available[] = prefixOptionsAdd($row['key']);;
     * }
     * }
     * $selected = $this->mspre->getFieldsTable('product', 'options');
     * $available = $this->mspre->getIntersect($available, array_column($selected, 'field'));
     * return array(
     * 'available' => $this->getSizeAvailable($available, false),
     * 'selected' => $selected,
     * );
     * }*/

    /**
     * Вернет ключи для загрузки мета данных
     * @return array
     */
    public function loadMetaKeys()
    {
        return array('fields', 'options', 'tv');
    }
}
