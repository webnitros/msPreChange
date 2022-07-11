<?php
/**
 * Created by Andrey Stepanenko.
 * User: webnitros
 * Date: 17.01.2019
 * Time: 23:01
 */

/* @var modX $modx */
trait msPreTrait
{
    /* @var modX $modx */
    public $modx;
    /* @var mspre $mspre */
    public $mspre;
    /* @var msProduct|modResource $object */
    public $object;

    /* @var string $separator */
    public $separator = ',';

    /**
     * Операции с ТВ параметрами
     */
    public function SetPropertyField()
    {
        $fieldName = $this->getProperty('field_name', null);
        $fieldValue = $this->getProperty('field_value', null);
        $this->properties = $this->object->toArray();
        if (!is_null($fieldName) and !is_null($fieldValue)) {
            $this->setProperty($fieldName, $fieldValue);
        }
    }


    /* @var mspreTvField $TvField */
    public $TvField = null;

    /**
     * Операции с ТВ параметрами
     * @param null $field_name
     * @param null $field_value
     * @param null $mode
     * @return boolean
     */
    public function updateTv($field_name = null, $field_value = null, $mode = null, $offset_resource = false, $enforce = false)
    {


        $updategrig = false;
        if (isset($this->properties['updategrig'])) {
            $updategrig = true;
        }

        $template = !isset($_REQUEST['template']) ? null : (int)$_REQUEST['template'];
        $template = (int)$this->getProperty('template', $template);


        if (isset($_REQUEST['offset_resource'])) {
            $this->setProperty('offset_resource', $_REQUEST['offset_resource']);
        } else {
            $this->setProperty('offset_resource', $offset_resource);
        }
        if (isset($_REQUEST['enforce'])) {
            $this->setProperty('enforce', $_REQUEST['enforce']);
        } else {
            $this->setProperty('enforce', $offset_resource);
        }

        $offset_resource = $this->setCheckbox('offset_resource');
        $enforce = $this->setCheckbox('enforce');
        $this->unsetProperty('template');
        $this->unsetProperty('enforce');
        $this->unsetProperty('offset_resource');

        $mode = $mode ? $mode : $_REQUEST['mode'];
        $mode = (string)$this->getProperty('mode', $mode);
        $this->unsetProperty('mode');

        if (!$fieldName = $this->getProperty('field_name', $field_name)) {
            $this->addFieldError('field_name', $this->modx->lexicon('mspre_error_replace_field_name'));
            return false;
        }


        if (!$fieldValue = $this->getProperty('field_value', $field_value) and !$updategrig) {
            $this->addFieldError('field_value', $this->modx->lexicon('mspre_error_replace_field_value'));
            return false;
        }

        /* @var modTemplateVar $object */
        if (!$tv = $this->modx->getObject('modTemplateVar', array('name' => $fieldName))) {
            $this->addFieldError('field_name', $this->modx->lexicon('mspre_error_replace_field_value'));
            return false;
        }

        if (!$this->TvField = $this->modx->getObject('mspreTvField', array('name' => $tv->get('type')))) {
            $this->addFieldError('field_name', $this->modx->lexicon('mspre_tv_error_field', array('field' => $fieldName)));
            return false;
        }

        $this->TvField->setTvObject($tv);

        if (empty($template)) {
            $this->addFieldError('template', $this->modx->lexicon('mspre_tv_error_template', array('tv_name' => $fieldName, 'tv_id' => $tv->get('id'))));
            return false;
        }


        // Проверка шаблона на совпадение
        if (!$enforce) {
            if ($this->object->get('template') != $template) {
                if (!$offset_resource) {
                    $this->addFieldError('template', $this->modx->lexicon('mspre_tv_error_template_resource', array(
                        'resource_id' => $this->object->get('id'),
                        'resource_name' => $this->object->get('pagetitle'),
                        'resource_template' => $this->object->get('template'),
                        'selected_template' => $template,
                        'tv_id' => $tv->get('id')))
                    );
                    return false;
                } else {
                    return true;
                }
            }
        }

        if (!$tv->hasTemplate($template)) {
            if ($object = $this->modx->getObject('modResource', $template)) {
                $object->get('id');
            }

            $template_id = '';
            $template_name = '';
            /* @var modTemplateVar $object */
            if ($modTemplate = $this->modx->getObject('modTemplate', $template)) {
                $template_id = $modTemplate->get('id');
                $template_name = $modTemplate->get('templatename');
            }

            $this->addFieldError('template', $this->modx->lexicon('mspre_tv_error_has_template', array('tv_id' => $tv->get('id'), 'tv_name' => $fieldName, 'resource_id' => $this->getProperty('id'), 'template' => $template_name, 'template_id' => $template_id)));
            return false;
        }

        switch ($mode) {
            case 'add':
                if (!empty($fieldValue) or $updategrig) {
                    $new = $this->TvField->addValue($this->object, $fieldValue, $updategrig);

                    if (!$this->object->setTVValue($fieldName, $new)) {
                        $this->addFieldError('fieldName', $this->modx->lexicon('mspre_tv_error_save_tv_add', array('mode' => $mode, 'id' => $this->object->get('id'))));
                    }
                    $this->unsetProperty('field_name');
                    $this->unsetProperty('field_value');
                }
                break;
            case 'replace':
                // Замены значения

                if (!$fieldReplace = $this->getProperty('field_replace', null)) {
                    $this->addFieldError('field_replace', $this->modx->lexicon('mspre_error_replace_field_replace'));
                    return false;
                }

                if ($newvalue = $this->TvField->newValue($this->object, $fieldValue, $fieldReplace)) {
                    $this->object->setTVValue($fieldName, $newvalue);
                }

                $this->unsetProperty('field_name');
                $this->unsetProperty('field_value');
                $this->unsetProperty('field_replace');

                break;
            case 'remove':
                $new = $this->TvField->removeValue($this->object, $fieldValue);
                $this->object->setTVValue($fieldName, $new);
                $this->unsetProperty('field_name');
                $this->unsetProperty('field_value');
                break;
            default:
                break;
        }

        return true;

    }


    public function setSortes(xPDOQuery $c)
    {
        $sort = $this->getProperty('sort');
        if (!empty($sort)) {
            /* @var modResource $object */
            $fields = $this->modx->getFields('modResource');

            $dir = $this->getProperty('dir');
            $sorts = array_map('trim', explode(',', $sort));

            $MetaData = $this->modx->getFieldMeta('msProductData');
            unset($MetaData['id']);


            if (count($sorts) == 1 and $sorts[0] == 'id') {
                $sortField = $this->classKey . '.id';
                $c->sortby($sortField, $dir);
            } else {
                foreach ($sorts as $sort) {
                    list($field, $d) = explode(' ', $sort);
                    if ($field != 'parent' and $field != $this->classKey . '.parent') {
                        $prefix = '';
                        if (isset($fields[$field])) {
                            $prefix = $this->classKey . '.';
                        } else {
                            if (array_key_exists($field, $MetaData)) {
                                $prefix = 'Data.';
                            }
                        }

                        $sortField = $prefix . $field;
                        $this->setProperty('sort', $sortField);
                        #$c->sortby($sortField, $dir);
                    }
                }
            }
        }
    }

    /**
     * @return array
     */
    public function getFiledsFormatDate()
    {
        return array(
            'createdon',
            'editedon',
            'publishedon',
            'deletedon',
            'pub_date',
            'unpub_date',
        );
    }


    /**
     * @param xPDOQuery $c
     *
     */
    public function setStatusFields(xPDOQuery $c)
    {
        $status = $this->getProperty('status', false);
        if ($status) {
            $field = '';
            $value = 1;
            $classKey = $this->classKey;
            $operator = '=';


            switch ($status) {
                case 'published':
                    $field = 'published';
                    break;
                case 'unpublished':
                    $field = 'published';
                    $operator = '!=';
                    break;
                case 'deleted':
                    $field = 'deleted';
                    break;
                case 'undeleted':
                    $field = 'deleted';
                    $operator = '!=';
                    break;
                case 'new':
                    $classKey = 'Data';
                    $field = 'new';
                    break;
                case 'unnew':
                    $classKey = 'Data';
                    $field = 'new';
                    $operator = '!=';
                    break;
                case 'popular':
                    $classKey = 'Data';
                    $field = 'popular';
                    break;
                case 'unpopular':
                    $classKey = 'Data';
                    $field = 'popular';
                    $operator = '!=';
                    break;
                case 'favorite':
                    $classKey = 'Data';
                    $field = 'favorite';
                    break;
                case 'unfavorite':
                    $classKey = 'Data';
                    $field = 'favorite';
                    $operator = '!=';
                    break;
                case 'image':
                    $classKey = 'Data';
                    $field = 'image';
                    $operator = '!=';
                    $value = NULL;
                    break;
                case 'unimage':
                    $classKey = 'Data';
                    $field = 'image';
                    $value = NULL;
                    break;
                case 'duplicate':
                    $field = 'id';
                    $operator = "IN";
                    $value = $this->getDuplicateResources();
                    if (empty($value)) {
                        $value = array(10000000);
                    }
                    break;
                case 'duplicate_article':

                    $classKey = 'Data';
                    $field = 'article';
                    $operator = "IN";
                    $value = $this->getDuplicateProductArticle();
                    if (empty($value)) {
                        $value = array('10000000');
                    }

                    break;
                case 'more_category':

                    $field = 'id';
                    $operator = "IN";
                    $value = $this->getProductCategoryMore();
                    if (empty($value)) {
                        $value = array('10000000');
                    }

                    break;
                case 'not_more_category':

                    $field = 'id';
                    $operator = "NOT IN";
                    $value = $this->getProductCategoryMore();

                    break;
                case 'show_in_tree':
                    $field = 'show_in_tree';
                    $operator = '';
                    break;
                case 'unshow_in_tree':
                    $field = 'show_in_tree';
                    $operator = '!=';
                    break;
                default:
                    break;
            }

            if (!empty($field)) {

                if (!empty($operator)) {
                    $operator = ':' . $operator;
                }
                $filter = array(
                    "{$classKey}." . $field . $operator => $value
                );
                $c->where($filter);
            }
        }
    }

    /**
     * @param xPDOQuery $c
     *
     */
    protected function filterFavorites(xPDOQuery $c)
    {
        $favorites = $this->getProperty('favorites');
        if (!empty($favorites)) {
            $favorite_resource = array(10000000000);
            $controller = $this->mspre->getAclassController($this->classKey);
            $settings = $this->mspre->loadState($controller);
            if (isset($settings['favorite_resource']) and count($settings['favorite_resource']) > 0) {
                $favorite_resource = $settings['favorite_resource'];
            }
            $c->where(array(
                'id:IN' => $favorite_resource
            ));
        }
    }

    /**
     * @param xPDOQuery $c
     *
     */
    protected function filterProductLink(xPDOQuery $c)
    {
        $product_link = (int)$this->getProperty('product_link');
        if (!empty($product_link)) {
            $c->innerJoin('msProductLink', 'msProductLink', 'msProductLink.master = msProduct.id OR msProductLink.slave = msProduct.id');
            $c->where(array(
                'msProductLink.link' => $product_link
            ));
        }
    }

    /**
     * Метод запишет критерии для выборки товаров которые были куплены рание
     * @param xPDOQuery $c
     */
    protected function filterPurchasedGoods(xPDOQuery $c)
    {
        if ($this->setCheckbox('purchased_goods')) {
            $statuses = $this->modx->getOption('mspre_status_purchased_goods');
            $products_ids = null;

            $statuses = array_map('trim', explode(',', $statuses));
            $order_ids = null;
            $q = $this->modx->newQuery('msOrder');
            $q->select('id');

            if (!empty($statuses)) {
                $q->where(array(
                    'status:IN' => $statuses,
                ));
            }

            if ($q->prepare() && $q->stmt->execute()) {
                while ($row = $q->stmt->fetch(PDO::FETCH_ASSOC)) {
                    $order_ids[] = $row['id'];
                }
            }

            if ($order_ids) {
                $q = $this->modx->newQuery('msOrderProduct');
                $q->groupby('product_id');
                $q->select('product_id');
                $q->where(array(
                    'order_id:IN' => $order_ids,
                ));
                if ($q->prepare() && $q->stmt->execute()) {
                    while ($row = $q->stmt->fetch(PDO::FETCH_ASSOC)) {
                        $products_ids[] = $row['product_id'];
                    }
                }
            }

            $c->where(array(
                $this->classKey . '.id:IN' => $products_ids ? $products_ids : array(0)
            ));

        }
    }


    /**
     * Метод запишет критерии для выборки товаров которые были куплены рание
     * @param xPDOQuery $c
     */
    protected function filterModifications(xPDOQuery $c)
    {
        if ($this->mspre->isEnablemsOptionsPrice2()) {
            $this->mspre->msOptionsPrice2();
            $c->select("(SELECT COUNT(DISTINCT Modification.id) FROM {$this->modx->getTableName('msopModification')} as Modification WHERE Modification.rid = {$this->classKey}.id) as total_modification");
            if ($this->setCheckbox('filter_modifications')) {
                // Накладывает фильтр чтобы показалить только товары с опциями
                $c->innerJoin('msopModification', 'Modification2', "Modification2.rid = {$this->classKey}.id");
            }
        }
    }

    protected function setFilterAll(xPDOQuery $c)
    {
        $resource_group = $this->getProperty('resource_group');
        if (!empty($resource_group)) {
            $resource_group = (int)$resource_group;
            $c->leftJoin('modResourceGroupResource', 'GroupResource', "GroupResource.document = {$this->classKey}.id");
            $c->where(array(
                "GroupResource.document_group" => $resource_group
            ));
        }
    }


    /**
     * @param xPDOQuery $c
     *
     */
    protected function setFilterFields(xPDOQuery $c)
    {

        $filter_field = $this->getProperty('filter_field', null);
        $filter_type = $this->getProperty('filter_type', null);
        $filter_value = $this->getProperty('filter_value', null);
        if (!empty($filter_field) and !empty($filter_type) and !is_null($filter_value)) {

            $filterField = trim($this->getProperty('filter_field'));
            $filterType = trim($this->getProperty('filter_type'));
            $filterValue = trim($this->getProperty('filter_value'));

            // Определяем класс
            $classKey = $this->classKey;
            $dataFields = $this->modx->getFieldMeta($classKey);

            $meta = null;
            if (!isset($dataFields[$filterField]) and $classKey == 'msProduct') {
                $dataFields = $this->modx->getFieldMeta('msProductData');
                if (isset($dataFields[$filterField])) {
                    $meta = $dataFields[$filterField];
                    $classKey = 'msProductData';
                    $alias = 'Data';
                }
            } else {
                $alias = 'msProduct';
                $meta = $dataFields[$filterField];
            }

            if (!$meta) {
                $this->modx->log(modX::LOG_LEVEL_ERROR, "Не удалось получить мета данные класса {$classKey} для поля {$filterField}", '', __METHOD__, __FILE__, __LINE__);
            }


            $phpType = $meta['phptype'];

            if ($filterField) {
                switch ($filterType) {
                    case 'BETWEEN':
                    case 'IN':
                    case 'NOT IN':
                        if (!empty($filterValue)) {
                            $filterValue = explode(',', $filterValue);
                        } else {
                            $filterValue = array();
                        }
                        break;
                    case 'IS NULL':
                        $filterType = 'IS';
                        $filterValue = null;
                        break;
                    case 'IS NOT NULL':
                        $filterType = 'IS NOT';
                        $filterValue = null;
                        break;
                    case 'LIKE%%':
                        $filterType = 'LIKE';
                        $filterValue = '%' . $filterValue . '%';
                        break;
                }


                $filterQuery = [$alias . '.' . $filterField . ':' . $filterType => $filterValue];
                if ($filterType === 'BETWEEN' && count($filterValue) === 2) {
                    $filterQuery = $alias . '.' . $filterField . ' BETWEEN ' . $filterValue[0] . ' AND ' . $filterValue[1];
                }


                $c->where($filterQuery);
            }

        }

    }

    /**
     * Вернет индитификаторы дублирующих ресурсов
     * @return array
     */
    public function getDuplicateResources()
    {
        $key = $this->getProperty('context');

        $cache = $this->modx->getCacheManager();
        $options = array();
        $duplicateResources = null;

        /** @var modContext $obj */
        $obj = $this->modx->getObject('modContext', $key, true);
        if (is_object($obj) && $obj instanceof modContext && $obj->get('key')) {
            $cacheKey = $obj->getCacheKey();
            $contextKey = is_object($this->modx->context) ? $this->modx->context->get('key') : $key;
            $contextConfig = array_merge($this->modx->_systemConfig, $options);


            /* generate the ContextSettings */
            $results['config'] = array();
            if ($settings = $obj->getMany('ContextSettings')) {
                /** @var modContextSetting $setting */
                foreach ($settings as $setting) {
                    $k = $setting->get('key');
                    $v = $setting->get('value');
                    $matches = array();
                    if (preg_match_all('~\{(.*?)\}~', $v, $matches, PREG_SET_ORDER)) {
                        foreach ($matches as $match) {
                            if (array_key_exists("{$match[1]}", $contextConfig)) {
                                $matchValue = $contextConfig["{$match[1]}"];
                                $v = str_replace($match[0], $matchValue, $v);
                            }
                        }
                    }
                    $results['config'][$k] = $v;
                    $contextConfig[$k] = $v;
                }
            }
            $results['config'] = array_merge($results['config'], $options);

            /* generate the aliasMap and resourceMap */
            $collResources = $obj->getResourceCacheMap();
            $friendlyUrls = $cache->getOption('friendly_urls', $contextConfig, false);
            $cacheAliasMap = $cache->getOption('cache_alias_map', $options, false);
            if ($friendlyUrls && $cacheAliasMap) {
                $results['aliasMap'] = array();
            }
            if ($collResources) {
                /** @var Object $r */
                while ($r = $collResources->fetch(PDO::FETCH_OBJ)) {
                    if (!isset($results['resourceMap'][(integer)$r->parent])) {
                        $results['resourceMap'][(integer)$r->parent] = array();
                    }
                    $results['resourceMap'][(integer)$r->parent][] = (integer)$r->id;
                    if ($friendlyUrls && $cacheAliasMap) {
                        if (array_key_exists($r->uri, $results['aliasMap'])) {
                            $duplicateResources[] = $r->id;
                            $duplicateResources[] = $results['aliasMap'][$r->uri];
                            #$this->modx->log(xPDO::LOG_LEVEL_ERROR, "Resource URI {$r->uri} already exists for resource id = {$results['aliasMap'][$r->uri]}; skipping duplicate resource URI for resource id = {$r->id}");
                            continue;
                        }
                        $results['aliasMap'][$r->uri] = (integer)$r->id;
                    }
                }
            }

        }
        return $duplicateResources;
    }


    /**
     * Вернет список товарво без категорий
     * @return array
     */
    public function getProductCategoryMore()
    {
        $ids = [];
        $q = $this->modx->newQuery('msCategoryMember');
        $q->select('product_id');
        $q->groupby('product_id');
        if ($q->prepare() && $q->stmt->execute()) {
            while ($row = $q->stmt->fetch(PDO::FETCH_ASSOC)) {
                $ids[] = $row['product_id'];
            }
        }
        return $ids;
    }

    /**
     * Вернет индитификаторы дублирующих ресурсов
     * @return array
     */
    public function getDuplicateProductArticle()
    {
        $articles = null;
        $q = $this->modx->newQuery('msProductData');
        $q->select('article');
        $q->select('COUNT(article) as count');
        $q->groupby('article');
        $q->having('count > 1');
        if ($q->prepare() && $q->stmt->execute()) {
            while ($row = $q->stmt->fetch(PDO::FETCH_ASSOC)) {
                $articles[] = $row['article'];
            }
        }
        return $articles;
    }


    /**
     * @return null|mspre
     */
    public function loadMsPre()
    {
        /* @var mspre $mspre */
        $mspre = $this->modx->getService('mspre', 'mspre', MODX_CORE_PATH . 'components/mspre/model/');
        return $mspre;
    }

    /**
     * Вернет индитификаторы дублирующих ресурсов
     */
    public function updateFromGrid()
    {
        $data = $this->getProperty('data');
        if (empty($data)) {
            return $this->modx->lexicon('invalid_data');
        }
        $data = is_array($data) ? $data : $this->modx->fromJSON($data);
        if (empty($data)) {
            return $this->modx->lexicon('invalid_data');
        }

        if (isset($data['actions'])) {
            unset($data['actions']);
        }

        if (isset($data['updategrig'])) {
            $field = trim($data['updategrig']);


            if ($fieldOption = $this->loadMsPre()->getCutPrefixOptions($field)) {
                // Провердка доступа в категорию
                $response = $this->loadMsPre()->categoryAccessCheck($data['id'], $fieldOption);
                if ($response !== true) {
                    return $response;
                }
            }


            $value = trim($data[$field]);
            $data['updategrig'] = array(
                'field' => $field,
                'value' => $value,
            );
        }


        $data = $this->getTvValue($data);
        $data = $this->getOptionsValue($data);

        $this->setProperties($data);
        $this->unsetProperty('data');
        return true;
    }


    /**
     * Уничтожит тв Параметры
     * @param array $data
     * @return array
     */
    private function getTvValue($data = array())
    {
        $TvFields = array();
        foreach ($data as $field => $value) {
            if ($TvField = prefixTv($field)) {
                $TvFields[$TvField] = $value;
                unset($data[$field]);
            }
        }
        return $data;
    }

    /**
     * Уничтожит опции
     * @param array $data
     * @return array
     */
    private function getOptionsValue($data = array())
    {
        $OptionsFields = array();
        foreach ($data as $field => $value) {
            if ($key = prefixOptions($field)) {
                $OptionsFields[$key] = $value;
                unset($data[$field]);
            }
        }
        return $data;
    }


    /**
     * Уничтожит опции
     */
    private function updategrig()
    {
        // Получение поля ТВ или Опций для обновления, все остальные поля автоматически будут стерты
        if (isset($this->properties['updategrig'])) {
            $updategrig = $this->getProperty('updategrig');
            $f = $updategrig['field'];
            $v = $updategrig['value'];


            if ($field = prefixTv($f)) {
                $this->updateTv($field, $v, 'add');
            } else if ($key = prefixOptions($f)) {
                if ($object = $this->modx->getObject('msOption', array('key' => $key))) {
                    switch ($object->get('type')) {
                        case 'combo-boolean':
                        case 'checkbox':
                            $v = ($v == $this->modx->lexicon('yes') or $v == 1 or $v == '1') ? 1 : 0;
                            break;
                        default:
                            break;
                    }
                }

                $this->setProperty($f, $v);
            }
            $this->unsetProperty('updategrig');
        }
    }


    /**
     * Уничтожит опции
     */
    private function loadTv($array)
    {
        // Load Tv
        if ($fields = $this->getSelectedFields()) {

            foreach ($fields as $original) {
                if ($field = prefixTv($original)) {
                    $value = '';
                    /* @var mspreTvField $TvField */
                    if ($TvField = $this->modx->getObject('mspreTvField', array('name' => $field))) {
                        $values = $TvField->valueEntered($array['id'], $array['template']);
                        if (!empty($values)) {
                            if (is_array($values)) {
                                $value = implode(',', array_column($values, 'name'));
                            }
                        }
                    }
                    $array[$original] = $value;
                }
            }
        }
        // Load Tv
        /*if ($this->loadTv) {
            foreach ($this->tableFields as $original) {
                if (strripos($original, 'tv-') !== false) {
                    $pk = str_ireplace('tv-', '', $original);

                    $value = '';
                    if ($tv = $this->modx->getObject('modTemplateVar', array('name' => $pk))) {
                        $resource = $this->modx->getObject('modTemplateVarResource', array(
                            'tmplvarid' => $tv->get('id'),
                            'contentid' => $object->get('id'),
                        ), true);

                        if ($resource && $resource instanceof modTemplateVarResource) {
                            $value = $resource->get('value');
                        }
                    }
                    $array[$original] = $value;
                }
            }
        }*/
        return $array;
    }


    /**
     * Возвращает список значений для ресурсов из Тв параметров или из Опций
     * @param string $classKey
     * @param string $type_key
     * @param array $typesIds
     * @param string $resource_key
     * @param array $resource_ids
     * @param null|array $typesArray
     * @return null
     */
    public function loadingAdditionalData($classKey, $type_key, $typesIds, $resource_key, $resource_ids, $typesArray = false)
    {
        $values = null;

        $criteria = array(
            $type_key . ':IN' => $typesIds,
            $resource_key . ':IN' => $resource_ids,
        );

        $q = $this->modx->newQuery($classKey);
        $q->select('value,' . $type_key . ',' . $resource_key);
        $q->where($criteria);

        if ($classKey == 'msProductOption') {
            $q->where(array('value:!=' => ''));
        }

        if ($q->prepare() && $q->stmt->execute()) {
            while ($row = $q->stmt->fetch(PDO::FETCH_ASSOC)) {
                $type = $row[$type_key];
                $resource = $row[$resource_key];
                $value = $row['value'];

                // Для опций некоторые значения должны быть в массиве
                if ($typesArray) {
                    if (in_array($type, $typesArray)) {
                        if (isset($values[$resource][$type])) {
                            $values[$resource][$type] = array_merge($values[$resource][$type], array($value));
                        } else {
                            $values[$resource][$type][] = $value;
                        }
                    } else {
                        $values[$resource][$type] = $value;
                    }
                } else {
                    $values[$resource][$type] = $value;
                }

            }
        }
        return $values;
    }

    /**
     * Проверка дополнительно добавленых полей
     * @param string $field
     * @return bool
     */
    protected function isAddColumn($field)
    {
        $fields = $this->getSelectedFields();
        if (in_array($field, $fields)) {
            return true;
        }
        return false;
    }

    /**
     * Обработка одной записи
     * @param array $row
     * @param array $fields
     * @return array
     */
    protected function prepareRowInterte($data = array(), $fields = array())
    {
        $resource_id = (int)$data['id'];
        if ($this->isAddColumn('product_link')) {

            $q = $this->modx->newQuery('msProductLink');
            $q->where(array(
                array(
                    'master' => $resource_id,
                )
            ));
            $q->orCondition(array(
                'slave' => $resource_id
            ));

            $count = $this->modx->getCount('msProductLink', $q);
            $data['product_link'] = $count;
        }


        if ($this->isAddColumn('additional_categories')) {
            $rows = array();
            $q = $this->modx->newQuery('msCategoryMember');
            $q->select('msCategory.pagetitle as name');
            $q->innerJoin('msCategory', 'msCategory', 'msCategory.id = msCategoryMember.category_id');
            $q->where(array(
                'msCategoryMember.product_id' => $resource_id,
            ));
            if ($q->prepare() && $q->stmt->execute()) {
                while ($row = $q->stmt->fetch(PDO::FETCH_ASSOC)) {
                    $rows[] = $row['name'];
                }
            }
            #$data['additional_categories'] = $rows;
            $data['additional_categories'] = implode('<br>', $rows);
        }


        return $data;
    }


    /**
     * @param array $list
     * @return array
     */
    public function iterateResults($list = array())
    {
        if (!count($list)) {
            return $list;
        }

        // Форматирование даты для полей
        if ($fieldsFormatDate = $this->getFiledsFormatDate()) {
            foreach ($list as $index => $row) {
                foreach ($fieldsFormatDate as $field) {
                    if (isset($row[$field])) {
                        $value = $row[$field];
                        if (!empty($value)) {
                            $newvalue = date('Y-m-d H:i:s', $value);
                        } else {
                            $newvalue = '';
                        }
                        $list[$index][$field] = $newvalue;
                    }
                }
            }
        }

        if ($this->classKey == 'msProduct') {
            // Данные в формате json
            foreach ($list as $index => $row) {
                // Форматирование полей в JSON формате
                if ($fields = $this->getFieldsFormatJSON()) {
                    foreach ($fields as $field) {
                        if (isset($row[$field])) {
                            $value = empty($row[$field]) ? '' : $this->getJSONField($row[$field]);
                            $list[$index][$field] = $value;
                        }
                    }
                }
            }


            $fields = $this->getSelectedFields();
            if (in_array('category_member', $fields)) {
                $ids = array_column($list, 'id');
                $dataCategory = array();
                $q = $this->modx->newQuery('msCategoryMember');
                $q->select('msCategoryMember.product_id,msCategory.pagetitle');
                $q->where(array(
                    'msCategoryMember.product_id:IN' => $ids,
                ));
                $q->innerJoin('msCategory', 'msCategory', 'msCategoryMember.category_id = msCategory.id');
                if ($q->prepare() && $q->stmt->execute()) {
                    while ($row = $q->stmt->fetch(PDO::FETCH_ASSOC)) {
                        $dataCategory[] = $row;
                    }
                }

                foreach ($list as $index => $row) {
                    $pro_id = $row['id'];
                    $category_member = array();
                    foreach ($dataCategory as $item) {
                        $product_id = $item['product_id'];
                        if ($product_id == $pro_id) {
                            $category_member[] = $item['pagetitle'];
                        }
                    }
                    $list[$index]['category_member'] = count($category_member) > 0 ? implode('<br>', $category_member) : '';
                }
            }

        }

        $newList = $list;
        if ($fields = $this->getSelectedFields(array('options', 'tv'))) {
            $valuesDefault = null;

            // Получаем дополнительные поля и устанавливаем значения по умолчанию для всего массива
            $valuesDefault = array_flip($fields);
            foreach ($valuesDefault as $k => $index) {
                $valuesDefault[$k] = '';
            }


            $newList = array();
            foreach ($list as $row) {
                $id = $row['id'];
                $newList[$id] = array_merge($row, $valuesDefault);
            }


            // Получаем индитификаторы ресурсов для получения значений
            $ids = array_keys($newList);

            // Load Tv
            if ($tvs = $this->isLoadTv()) {
                $typesIds = array();
                foreach ($tvs as $tv) {
                    $typesIds[] = $tv->get('id');
                }

                if ($values = $this->loadingAdditionalData('modTemplateVarResource', 'tmplvarid', $typesIds, 'contentid', $ids)) {
                    foreach ($values as $resourceId => $typesValues) {
                        foreach ($tvs as $tv) {
                            $id = $tv->get('id');
                            $type = $tv->get('type');
                            $prefix = prefixTvAdd($tv->get('name'));


                            $value = '';
                            /* @var mspreTvField $TvField */
                            if ($TvField = $this->modx->getObject('mspreTvField', array('name' => $type))) {
                                $TvField->setTvObject($tv);
                                $entered = $TvField->enteredValues(array($resourceId));
                            } else {
                                $tv->set('resourceId', $resourceId);
                                if (isset($typesValues[$id])) {
                                    $tv->set('value', $typesValues[$id]);
                                } else {
                                    $tv->set('value', null);
                                }
                                $entered = $tv->getValue($resourceId);
                            }


                            if ($TvField) {
                                if (is_array($entered) and !empty($entered)) {
                                    $entered = array_map('trim', array_column($entered, 'name'));
                                    $value = implode(',', $entered);
                                } else {
                                    $value = '';
                                }

                                // Обработка значений для вывода для некоторых типов тв полей
                                $value = $TvField->prepareOutput($value, $resourceId);
                            } else {
                                $value = $tv->getValue($resourceId);
                                $value = $tv->prepareOutput($value, $resourceId);
                            }

                            $newList[$resourceId][$prefix] = $value;
                        }
                    }
                }
            }

            // Load Options
            if ($options = $this->isLoadOptions()) {

                $typesIds = array();
                $typesArray = array();
                foreach ($options as $option) {
                    $type = $option->get('type');
                    switch ($type) {
                        case 'combo-multiple':
                        case 'combo-options':
                            $typesArray[] = $option->get('key');
                            break;
                        default:
                            break;
                    }
                    $typesIds[] = $option->get('key');
                }


                if (count($typesIds)) {
                    if ($values = $this->loadingAdditionalData('msProductOption', 'key', $typesIds, 'product_id', $ids, $typesArray)) {
                        foreach ($values as $resourceId => $typesValues) {
                            foreach ($options as $option) {
                                $key = $option->get('key');
                                $prefix = prefixOptionsAdd($key);
                                $value = $typesValues[$key];
                                switch ($option->get('type')) {
                                    case 'combo-multiple':
                                    case 'combo-options':
                                        $value = empty($value) ? '' : implode($this->separator, $value);
                                        break;
                                    case 'combo-boolean':
                                    case 'checkbox':
                                        $value = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
                                        break;
                                    default:
                                        break;
                                }
                                $newList[$resourceId][$prefix] = $value;
                            }
                        }
                    }
                }
            }

            $newList = array_values($newList);
        }


        foreach ($newList as $index => $row) {
            $newList[$index] = $this->prepareRowInterte($row, $fields);
        }
        return $newList;
    }


    public function getJSONField($data, $json = true)
    {

        if ($json) {
            $data = is_array($data) ? $data : $this->modx->fromJSON($data);
        }

        $value = '';
        if (!empty($data)) {
            $value = implode($this->separator, $data);
        }
        return $value;
    }

    /* @var null|array $fieldsJson */
    protected $fieldsJson = null;


    /**
     * Вернет выбранные поля для контроллера
     * @param null|array $prefixs
     * @param bool $cut_prefix
     * @return array|null
     */
    public function getSelectedFields($prefixs = null, $cut_prefix = false)
    {
        $controller = '';
        switch ($this->classKey) {
            case 'modResource':
                $controller = 'resource';
                break;
            case 'msProduct':
                $controller = 'product';
                break;
            default:
                break;
        }

        return $this->mspre->getSelectedFields($controller, $prefixs, $cut_prefix);
    }


    /* @var msOption[]|null|boolean $mspre_options */
    protected $mspre_options = null;

    /* @var modTemplateVar[]|null|boolean $mspre_tvs */
    public $mspre_tvs = null;


    /**
     * @return bool|msOption[]|null
     */
    public function isLoadOptions()
    {
        if (is_null($this->mspre_options)) {
            $this->mspre_options = false;
            if ($selectedFields = $this->getSelectedFields()) {
                $fields = null;

                foreach ($selectedFields as $field) {
                    if ($key = prefixOptions($field)) {
                        $fields[] = $key;
                    }
                }
                if ($fields) {
                    $this->mspre_options = $this->modx->getIterator('msOption', array('key:IN' => $fields));
                }
            }
        }
        return $this->mspre_options;
    }

    /**
     * @return bool|modTemplateVar[]|null
     */
    public function isLoadTv()
    {
        if (is_null($this->mspre_tvs)) {
            $this->mspre_tvs = false;
            if ($selectedFields = $this->getSelectedFields()) {
                $fields = null;
                foreach ($selectedFields as $field) {
                    if ($key = prefixTv($field)) {
                        $fields[] = $key;
                    }
                }
                if ($fields) {
                    $this->mspre_tvs = $this->modx->getIterator('modTemplateVar', array('name:IN' => $fields));
                }
            }
        }
        return $this->mspre_tvs;
    }

    /**
     * Вернет поля в формате json
     * @return array|null
     */
    public function getFieldsFormatJSON()
    {
        if (is_null($this->fieldsJson)) {
            $this->mspre->loadMinishop2();
            $metas = $this->modx->getFieldMeta('msProductData');
            foreach ($metas as $field => $meta) {
                if ($meta['phptype'] == 'json') {
                    $this->fieldsJson[] = $field;
                }
            }
        }
        return $this->fieldsJson;
    }
}
