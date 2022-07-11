mspre.grid.product = function (config) {
    config = config || {}

    this.sm = new Ext.grid.CheckboxSelectionModel({
        renderer: function (b, c, a) {
            return '<div class="x-grid3-row-checker">&#160;</div><div class="x-grid3-row-favorite"></div>'
            //return '<div class="x-grid3-row-checker">&#160;</div><div class="x-grid3-row-favorite"><i class="icon icon-star"></i></div>'
        },
        onMouseDown: function (c, b) {
            if (c.button === 0 && b.className == 'x-grid3-row-checker') {
                c.stopEvent()
                var d = c.getTarget('.x-grid3-row')
                if (d) {
                    var a = d.rowIndex
                    if (this.isSelected(a)) {this.deselectRow(a)} else {
                        this.selectRow(a, true)
                        this.grid.getView().focusRow(a)
                    }
                }
            }

            if (c.button === 0 && b.className == 'x-grid3-row-favorite') {
                c.stopEvent()
                var d = c.getTarget('.x-grid3-row')
                if (d) {
                    var a = d.rowIndex
                    this.grid.changeFavorite(this.grid, a, d)
                }
            }
        },
        handleMouseDown: function (g, rowIndex, e) {
            if (e.button !== 0 || this.isLocked()) {
                return
            }

            var elem = e.getTarget()
            if (elem.closest('#msoptionsprice-grid-modification')) {
                // для мобификаций отлючение выделение строки, иначе начинаются прыжки вверх так как индекс любой строки начинает с 0
                return null
            }

            var view = this.grid.getView()
            if (e.shiftKey && !this.singleSelect && this.last !== false) {
                var last = this.last
                this.selectRange(last, rowIndex, e.ctrlKey)
                this.last = last
                view.focusRow(rowIndex)
            } else {
                var isSelected = this.isSelected(rowIndex)
                if (e.ctrlKey && isSelected) {
                    this.deselectRow(rowIndex)
                } else if (!isSelected || this.getCount() > 1) {
                    this.selectRow(rowIndex, e.ctrlKey || e.shiftKey)
                    view.focusRow(rowIndex)
                }
            }
        },
    })

    // extend grid msOptionsPrice2
    pluginsEnable = false
    if (mspre.config.controller === 'product') {
        pluginsEnable = miniShop2.config.isEbableOptionPrice2 || false
        if (pluginsEnable) {
            this._loadExpander()
            Ext.applyIf(config, {
                plugins: this.expander
            })
        }
    }

    config['enable_option_price'] = pluginsEnable

    //icon icon-sta
    Ext.applyIf(config, {
        id: mspre.config.grid_id,
        url: mspre.config.connector_url,
        cls: config['cls'] || 'main-wrapper mspre-grid',
        baseParams: {
            action: mspre.config.controllerPath + 'getlist',
            categories: Ext.util.JSON.encode(mspre.config.categories)
        },
        save_action: mspre.config.controllerPath + 'updatefromgrid',
        autosave: true,

        paging: true,
        remoteSort: true,
        fields: this.getFields(config),
        columns: this.getColumns(config),
        tbar: this.getTopBar(config),
        listeners: this.getListeners(config),


        sm: this.sm,
        autoHeight: true,
        allowDeselect: true,
        enableColumnMove: false, // Отключение перемение колонок
        enableColumnHide: false, // Отключение показа списка полей
        enableHdMenu: false, // Отключение показаза выпадающего списка у колонки
        //,header: false
        viewConfig: {
            forceFit: true,
            enableRowBody: true,
            autoFill: true,
            showPreview: true,
            scrollOffset: -10,
            getRowClass: function (rec) {
                var cls = []
                cls.push('mspre-row-product-line')
                if (rec.data['published'] != undefined && rec.data['published'] == 0) {
                    cls.push('mspre-row-unpublished')
                }

                if (mspre.config.favorite_resource.indexOf(parseInt(rec.data['id'])) !== -1) {
                    cls.push('mspre-row-favorite')
                }

                if (rec.data['deleted'] != undefined && rec.data['deleted'] == 1) {
                    cls.push('mspre-row-deleted')
                }
                if (rec.data['preview_url'] != undefined && rec.data['preview_url'] == '') {
                    if (rec.data['deleted'] != 1) {
                        cls.push('mspre-row-duplicate')
                    }
                }
                return cls.join(' ')
            }
        },
        stateful: true,
        stateId: mspre.config.grid_id + '_' + mspre.config.controller + '-state',
        expandOnDblClick: false,
    })

    mspre.grid.product.superclass.constructor.call(this, config)


    this.on('afterrender', function () {
        // Размер превью в списке
        var cls = mspre.store.get('zoom') === 'x1' ? 'mspre-grid-product-zoom-x1' : 'mspre-grid-product-zoom-x0'
        Ext.getCmp(mspre.config.grid_id).addClass(cls)


        // Включение фильтров
        Ext.getCmp('mspre-filter-search').enable()
        Ext.getCmp('mspre-form-panel').setFilter()

    })

}
//Ext.extend(mspre.grid.product, MODx.grid.Grid, {
Ext.extend(mspre.grid.product, mspre.grid.Default, {

    _loadExpander: function () {
       this.expander = new Ext.ux.grid.RowExpander({
            tpl: new Ext.Template('<div id="modification-row-{id}"></div>'),
            listeners: {
                render: function () {
                    console.log(this)
                },
                expand: function (exp, record, body, rowIndex) {
                    var id = record.get('id')
                    this.expander.Grids[id] = new mspre.grid.OptionsPrice2Modification({
                        renderTo: Ext.get('modification-row-' + id)
                        , product_id: id
                        , resource: {
                            id: id
                        }
                    })
                },
                scope: this
            },
        })
        this.expander.Grids = {}


        this.expander.override({

            getRowClass: function (rec) {
                var cls = ['x-grid3-row-collapsed']

                cls.push('mspre-row-product-line')
                if (rec.data['published'] != undefined && rec.data['published'] == 0) {
                    cls.push('mspre-row-unpublished')
                }

                if (mspre.config.favorite_resource.indexOf(parseInt(rec.data['id'])) !== -1) {
                    cls.push('mspre-row-favorite')
                }

                if (rec.data['deleted'] != undefined && rec.data['deleted'] == 1) {
                    cls.push('mspre-row-deleted')
                }
                if (rec.data['preview_url'] != undefined && rec.data['preview_url'] == '') {
                    if (rec.data['deleted'] != 1) {
                        cls.push('mspre-row-duplicate')
                    }
                }

                return cls.join(' ')
            }
            /*
            getRowClass: function(record, rowIndex, p, ds) {

                console.log(2112)
                p.cols = p.cols - 1;
                var content = this.bodyContent[record.id];

                if (!content && !this.lazyRender) {
                    content = this.getBodyContent(record, rowIndex);
                }

                if (content) {
                    p.body = content;
                }

                return this.state[record.id] ? "x-grid3-row-expanded myClass" : "x-grid3-row-collapsed myClass";
            }*/
        });

    },

    windows: {
        exportFields: false,
        fields: false,
        combo: false,
        actions: false,
        changeTvCombo: false,
        categoryBinding: false,
        massActions: false,
    },

    getColumns: function (config) {

        var meta = mspre.config.map
        var tmp = mspre.config.fields['columns']
        var data = {}
        var field = ''
        var editor = null
        var columns = [this.sm]
        for (var i = 0; i < tmp.length; i++) {
            if (!tmp.hasOwnProperty(i)) {
                continue
            }

            field = tmp[i]
            data = meta[field]

            editor = data['editor'] || false

            delete data['actions']

            if (typeof editor === 'boolean') {
                delete data['editor']
            } else {
                if (editor.xtype === 'loadCombo') {
                    delete data['editor']
                    data.listeners = {
                        dblClick: function (elem, rowIndex, e, btn) {
                            Ext.getCmp(mspre.config.grid_id).loadComboOptions(elem)
                        }
                    }
                }
                if (editor.xtype === 'loadComboFields') {
                    delete data['editor']
                    data.listeners = {
                        dblClick: function (elem, rowIndex, e, btn) {
                            Ext.getCmp(mspre.config.grid_id).loadComboFields(elem)
                        }
                    }
                }
                if (editor.xtype === 'loadResourcePage') {
                    delete data['editor']
                    data.listeners = {
                        dblClick: function (elem, rowIndex, e, btn) {
                            Ext.getCmp(mspre.config.grid_id).loadResourcePage(elem)
                        }
                    }
                }
                if (editor.xtype === 'loadComboNotEditor') {
                    delete data['editor']
                    data.listeners = {
                        dblClick: function (elem, rowIndex, e, btn) {
                            Ext.getCmp(mspre.config.grid_id).loadComboNotEditor(elem)
                        }
                    }
                }
                if (editor.xtype === 'loadComboTv') {
                    delete data['editor']
                    data.listeners = {
                        dblClick: function (elem, rowIndex, e, btn) {
                            Ext.getCmp(mspre.config.grid_id).loadComboTv(elem)
                        }
                    }
                }
            }

            if (config.enable_option_price) {
                if (field === 'price') {
                    data.renderer = function (value, e, row) {
                        var total = parseInt(row.data.total_modification)
                        if (total === 0) {
                            return value
                        }
                        return '<span class="msoptionsprice-total" title="Всего модификаций ' + total + ' шт">модификаций ' + total + '</span>' + value
                    }
                }

            }
            columns.push(data)
        }
        if (typeof miniShop2 !== 'undefined') {

            var i, add, param
            for (i in miniShop2.plugin) {
                if (!miniShop2.plugin.hasOwnProperty(i)) {
                    continue
                }
                if (typeof (miniShop2.plugin[i]['getColumns']) == 'function') {
                    add = miniShop2.plugin[i].getColumns()

                    for (i in add) {
                        if (!add.hasOwnProperty(i)) {
                            continue
                        }
                        field = add[i]
                        for (var i2 = 0; i2 < columns.length; i2++) {
                            if (!columns.hasOwnProperty(i2)) {
                                continue
                            }
                            if (columns[i2].id === field.name && field.editor !== undefined) {
                                columns[i2]['editor'] = field.editor
                            }
                        }
                    }
                }
            }
        }

        if (this.expander) {
            columns.unshift(this.expander)
        }

        return columns
    },

    saveRecord: function (e) {
        e.record.data.menu = null
        var p = this.config.saveParams || {}
        delete this.config.saveParams
        Ext.apply(e.record.data, p)
        var d = Ext.util.JSON.encode(e.record.data)
        if (e.record.data.updategrig) {
            delete e.record.data.updategrig
        }
        var url = this.config.saveUrl || (this.config.url || this.config.connector)
        MODx.Ajax.request({
            url: url
            , params: {
                action: this.config.save_action || 'updateFromGrid'
                , data: d
            }
            , listeners: {
                success: {
                    fn: function (r) {
                        if (this.config.save_callback) {
                            Ext.callback(this.config.save_callback, this.config.scope || this, [r])
                        }
                        e.record.commit()
                        if (!this.config.preventSaveRefresh) {
                            this.refresh()
                        }
                        this.fireEvent('afterAutoSave', r)
                    }
                    , scope: this
                }
                , failure: {
                    fn: function (r) {
                        e.record.reject()
                        this.fireEvent('afterAutoSave', r)
                    }
                    , scope: this
                }
            }
        })
    },

    changeFavorite: function (grid, a, d) {
        var row = grid.getStore().getAt(a)
        var resource_id = parseInt(row.id)
        var bx = Ext.get(d)
        var favorite = false
        if (mspre.config.favorite_resource.indexOf(resource_id) !== -1) {
            bx.removeClass('mspre-row-favorite')
            mspre.config.favorite_resource.remove(resource_id)
        } else {
            favorite = true
            bx.addClass('mspre-row-favorite')
            mspre.config.favorite_resource.push(resource_id)
        }

        // Запись состояния в сессию
        mspre.store.start()
        mspre.store.dirty = true
        mspre.store.queue = mspre.store.state
        mspre.store.set('favorite_resource', mspre.config.favorite_resource)
        mspre.store.submitState()

        // Установка количества записей
        var count = mspre.config.favorite_resource.length || 0
        count = count.toString()
        var text_size = 20
        var elem = Ext.get('mspre-panel-info-favorite')

        if (elem) {
            elem.setStyle('font-size', text_size + 'px')
            elem.update(count)
        }

        return true
    },
    loadComboTv: function (field) {
        mspre.grid.product.fieldTv = field
        var grid = Ext.getCmp(mspre.config.grid_id)
        mspre.grid.product.accessTemplate({
            tvname: field.dataIndex,
            resource: grid.selected()
        }, mspre.grid.product.loadComboTvCustom)
        return true
    },
    loadComboNotEditor: function (field) {
        var type = field.type_field || 'all'
        Ext.Msg.confirm(_('warning'), _('mspre_error_tv_not_editor_' + type), function (e) {
            if (e == 'yes') {
                var grid = Ext.getCmp(mspre.config.grid_id)
                var resource_id = grid.getSelectionModel().getSelected().data.id
                MODx.loadPage('?a=resource/update&id=' + resource_id)
            } else {}
        }, this)
        return true
    },
    loadComboOptions: function (field) {
        mspre.grid.product.fieldOptions = field
        var grid = Ext.getCmp(mspre.config.grid_id)
        mspre.grid.product.accessCategory({
            option: field.dataIndex,
            resource: grid.selected()
        }, mspre.grid.product.loadComboOptionsCustom)
        return true
    },
    loadComboFields: function (field) {
        mspre.grid.product.fieldOptions = field
        mspre.grid.product.loadComboOptionsCustom()
        return true
    },
    loadResourcePage: function (field) {
        var grid = Ext.getCmp(mspre.config.grid_id)
        var resource_id = grid.getSelectionModel().getSelected().data.id
        this.loadResourcePageIframe(resource_id)
        return true
    },
    autoStartOptionsCombo: function (mode, field, record) {

        Ext.getCmp(mspre.config.grid_id).request('mgr/controller/product/options/render', {
            mode: mode,
            option: field,
            type: 'fields',
        }, function (response) {
            if (response.success) {

                Ext.getCmp('mspre-grid-product').defaultCombo(response.object.params, this)
                //Ext.getCmp('mspre-window-change-category-modal').destroy()
            } else {
                MODx.msg.alert(_('error'), response.message)
            }
        })

    },
    loadComboDefaultOptions: function (field, e) {

        var grid = Ext.getCmp(mspre.config.grid_id)
        var record = grid.getChecked()

        if (!record) return false

        if (grid.windows.massActions) {
            grid.windows.massActions.destroy()
        }

        grid.windows.massActions = MODx.load({
            xtype: 'mspre-window-options-change-combo',
            record: record,
            field: field.combo_id,
            listeners: {
                'success': {
                    fn: function (r) {
                        grid.refresh()
                    }, scope: this
                },
                afterrender: {
                    fn: function () {
                        grid.enabledMask()
                    },
                    scope: this
                },
                hide: {
                    fn: function () {
                        grid.windows.massActions.destroy()
                        grid.disabledMask()
                    },
                    scope: this
                }
            }
        })
        grid.windows.massActions.setValues(record)
        grid.windows.massActions.show(e.target)
        return true
    },

    categoryBinding: function (g) {

        var grid = Ext.getCmp(mspre.config.grid_id)

        if (grid.windows.categoryBinding) {
            grid.windows.categoryBinding.destroy()
        }

        grid.windows.categoryBinding = MODx.load({
            xtype: 'mspre-window-category-binding',
            records: {
                ids: ids
            },
            listeners: {
                success: {
                    fn: function () {
                        grid.disabledMask()
                        grid.refresh()
                    }, scope: this
                },
                hide: {
                    fn: function () {
                        grid.windows.categoryBinding.destroy()
                    },
                    scope: this
                }
            }
        })
        grid.windows.categoryBinding.show()
    },
    changeTvCombo: function (g) {
        if (!g.name) {
            MODx.msg.alert(_('error'), _('mspre_error_could_not_get_name_tv'))
        }

        var xtype = mspre.config.xtype.tv[g.name]
        var grid = Ext.getCmp(mspre.config.grid_id)
        var selected = this.getSelectionModel().getSelected()
        var ids = Ext.util.JSON.encode([selected.data.id])

        if (grid.windows.changeTvCombo) {
            grid.windows.changeTvCombo.destroy()
        }

        grid.windows.changeTvCombo = MODx.load({
            xtype: 'mspre-window-tv-change-combo',
            records: {
                template: selected.data.template,
                ids: ids,
                field: g.name,
                xtype_old: xtype.xtype_old,
                xtype_new: xtype.xtype_new
            },
            listeners: {
                success: {
                    fn: function () {
                        grid.disabledMask()
                        grid.refresh()
                    }, scope: this
                },
                hide: {
                    fn: function () {
                        grid.windows.changeTvCombo.destroy()
                    },
                    scope: this
                }
            }
        })
        grid.windows.changeTvCombo.show()
    },
    autoStartTvCombo: function (mode, records) {
        var grid = Ext.getCmp(mspre.config.grid_id)

        if (grid.windows.actions) {
            grid.windows.actions.destroy()
        }

        if (grid.windows.changeTvCombo) {
            grid.windows.changeTvCombo.destroy()
        }

        grid.windows.actions = MODx.load({
            xtype: 'mspre-window-' + mode + '-tv-field',
            records: records,
            listeners: {
                success: {
                    fn: function () {
                        grid.disabledMask()
                        grid.refresh()
                    }, scope: this
                },
                hide: {
                    fn: function () {
                        grid.windows.actions.destroy()
                    },
                    scope: this
                }
            }
        })
        grid.windows.actions.setValues({
            ids: records.ids
        })
        grid.windows.actions.show()
    },
    getFields: function (config) {
        var fields = mspre.config.fields['select']
        if (config.enable_option_price) {
            fields.push('total_modification')
        }
        return fields
    },
    zoom: function (config) {
        var grid = Ext.getCmp(mspre.config.grid_id)
        var zoom = 'x0'
        if (grid.el.hasClass('mspre-grid-product-zoom-x1')) {
            grid.removeClass('mspre-grid-product-zoom-x1')
            grid.addClass('mspre-grid-product-zoom-x0')
        } else {
            grid.addClass('mspre-grid-product-zoom-x1')
            grid.removeClass('mspre-grid-product-zoom-x0')
            zoom = 'x1'
        }
        mspre.store.set('zoom', zoom)
        mspre.store.submitState()
    },
    menus: function (name) {
        var actions = {
            menu: {
                tooltip: _('mspre_menu_tooltip_menu'),
                text: '<i class="icon icon-cogs"></i> ',
                menu: mspre.utils.getMenus(mspre.config.actions.resource, this, [], this._getSelectedIds()),
            },
            product_menu: {
                tooltip: _('mspre_menu_tooltip_menu_product'),
                text: '<i class="icon icon-barcode"></i> ',
                menu: mspre.utils.getMenus(mspre.config.actions.product, this, [], this._getSelectedIds()),
            },
            combo: {
                tooltip: _('mspre_menu_tooltip_combo'),
                text: 'COMBO',
                menu: mspre.utils.getMenus(mspre.config.actions.combo, this, [], this._getSelectedIds()),
            },
            options: {
                tooltip: _('mspre_menu_tooltip_json'),
                text: 'Опции ',
                menu: mspre.utils.getMenus(mspre.config.actions.options, this, [], this._getSelectedIds()),
            },
            tv: {
                tooltip: _('mspre_menu_tooltip_tv'),
                text: '<i class="icon icon-list-alt"></i> ',
                menu: mspre.utils.getMenus(mspre.config.actions.tv, this, [], this._getSelectedIds()),
            },

            create: {
                tooltip: _('mspre_menu_tooltip_create'),
                text: '<i class="icon icon-plus"></i> ',
                xtype: 'button',
                handler: this.create
            },
            tablesetup: {
                tooltip: _('mspre_menu_tooltip_create'),
                text: '<i class="icon icon-table"></i> ',
                xtype: 'button',
                combo_id: 'table',
                handler: this.windowFields
            },
            export: {
                tooltip: _('mspre_menu_tooltip_export'),
                text: '<i class="icon icon-download"></i> ',
                menu: mspre.utils.getMenus(mspre.config.actions.export, this, [], this._getSelectedIds()),
            },
            zoom: {
                tooltip: _('mspre_menu_tooltip_zoom'),
                text: '<i class="icon icon-search-plus"></i>',
                xtype: 'button',
                handler: this.zoom
            },
            refresh: {
                tooltip: _('mspre_menu_tooltip_refresh'),
                text: '<i class="icon icon-refresh"></i>',
                xtype: 'button',
                handler: this.refresh
            },

            sep: '->',
            reset: {
                xtype: 'button',
                width: 150,
                tooltip: _('mspre_menu_tooltip_reset'),
                iconCls: 'x-btn-small primary-button',
                text: '<i class="icon icon-times"></i> ' + _('mspre_filter_clear') + '<span style="display: none" id="mspre-panel-info-total_selected"></span>',
                listeners: {
                    click: {
                        fn: this.reset, scope: this
                    }
                }
            },
            spacer: {
                xtype: 'spacer',
                style: 'width:1px;'
            }
        }
        return actions[name]
    },
    selected: function () {
        var res = this.getSelectionModel().getSelected()
        return parseInt(res.data.id)
    },
    getTopBar: function (config) {
        var selected = mspre.config.topbar
        var actions = []
        for (var i in selected) {
            if (!selected.hasOwnProperty(i)) {
                continue
            }
            var name = selected[i]
            var fn = this.menus(name)
            if (fn !== undefined) {
                actions.push(fn)
            }
        }
        return actions
    },

    getListeners: function (config) {
        return {
            beforestatesave: function (grid, a) {
                if (a['columns']) {
                    delete a['columns']
                }
            },
            beforerender: function () {
                var form = Ext.getCmp('mspre-form-panel')
                if (form) {
                    form.saveState = true
                    this.getStore().on('load', function (res) {
                        form.updateInfo(res.reader['jsonData'])
                    })
                }
            }
        }
    },
    getMenu: function (grid, rowIndex) {
        var ids = this._getSelectedIds()
        var row = grid.getStore().getAt(rowIndex)
        var menu = mspre.utils.getMenu(row.data['actions'], this, ids)
        this.addContextMenuItem(menu)
    },
    reset: function (btn, e) {
        var $form = Ext.getCmp('mspre-form-panel')
        $form._filterSet('query', '')
        /*$form._filterSet('class_key', '')
        $form._filterSet('context', '')
        $form._filterSet('status', '')
        $form._filterSet('vendor', '')
        $form._filterSet('template', '')
        $form._filterSet('filter_field', '')
        $form._filterSet('filter_type', '')
        $form._filterSet('filter_value', '')
        $form._filterSet('additional', '')
        */
        $form.reset()
    },
    getChecked: function () {
        var ids = this.getSelectedAsList()
        if (ids === false) {
            MODx.msg.alert(_('mspre_error'), _('mspre_error_massive_actions_empty'))
            return false
        }
        ids = Ext.util.JSON.encode(ids.split(','))
        var r = {ids: ids, context: this.getStore().baseParams.context}
        return r
    },
    _getSelectedIds: function () {
        var ids = []
        var selected = this.getSelectionModel().getSelections()
        for (var i in selected) {
            if (!selected.hasOwnProperty(i)) {
                continue
            }
            ids.push(selected[i]['id'])
        }
        return ids
    },
    reloadTree: function (ids) {
        if (ids == undefined || typeof (ids) != 'object') {
            ids = this._getSelectedIds()
        }
        var store = this.getStore()
        var parents = {}
        for (var i in ids) {
            if (!ids.hasOwnProperty(i)) {
                continue
            }
            var item = store.data.map[Number(ids[i])]
            if (item != undefined) {
                parents[item['data']['parent']] = item['data']['context_key']
            }
        }
        var tree = Ext.getCmp('modx-resource-tree')
        if (tree) {
            for (var parent in parents) {
                if (!parents.hasOwnProperty(parent)) {
                    continue
                }
                var ctx = parents[parent]
                var node = tree.getNodeById(ctx + '_' + parent)
                if (typeof (node) !== 'undefined') {
                    node.leaf = false
                    node.reload(function () {
                        this.expand()
                    })
                }
            }
        }
    },
    refreshGrid: function (ids) {

        // Автоматическое блокирование записей на странице
        if (!mspre.disableRefresh) {
            this.refresh()
        }

    },
    setAction: function (method, field, value, callback, progress) {
        var ids = this._getSelectedIds()
        if (!ids.length && (field !== 'false')) {
            MODx.msg.alert(_('mspre_error'), _('mspre_error_massive_actions_empty'))
            return false
        }

        MODx.Ajax.request({
            url: mspre.config.connector_url,
            params: {
                action: mspre.config.controllerPath + 'multiple',
                classKey: mspre.config.classKey,
                method: method,
                field_name: field,
                field_value: value,
                progress: progress || false,
                ids: Ext.util.JSON.encode(ids)
            },
            listeners: {
                success: {
                    fn: function (r) {
                        Ext.get(mspre.config.grid_id).unmask()
                        if (typeof callback === 'function') {
                            return callback(method, field, value, r)
                        }
                        this.refreshGrid()
                    },
                    scope: this
                },
                failure: {
                    fn: function (response) {
                        MODx.msg.alert(_('mspre_error'), response.message)
                    },
                    scope: this
                }
            }
        })
    },
    productAction: function (method) {
        var ids = this._getSelectedIds()
        if (!ids.length) {
            MODx.msg.alert(_('mspre_error'), _('mspre_error_massive_actions_empty'))
            return false
        }
        MODx.Ajax.request({
            url: mspre.config['connector_url'],
            params: {
                action: 'mgr/common/multiple',
                classKey: mspre.config.classKey,
                method: method,
                ids: Ext.util.JSON.encode(ids),
            },
            listeners: {
                success: {
                    fn: function () {
                        this.reloadTree()
                        this.refresh()
                    }, scope: this
                },
                failure: {
                    fn: function (response) {
                        MODx.msg.alert(_('error'), response.message)
                    }, scope: this
                },
            }
        })
    },

    exportCallback: function (method, fields, format, response) {
        document.location.href = mspre.config.exportUrl + '?format=' + format
    },

    exportCSV: function () {
        var $th = Ext.getCmp(mspre.config.grid_id)
        $th.exportRun('CSV')
    },
    exportXLS: function () {
        var $th = Ext.getCmp(mspre.config.grid_id)
        $th.exportRun('XLS')
    },
    exportXLSX: function () {
        var $th = Ext.getCmp(mspre.config.grid_id)
        $th.exportRun('XLSX')
    },

    /* exportCSV: function (btn, e) {
         var $th = Ext.getCmp(mspre.config.grid_id)
         $th.setAction('export', mspre.config.controller, 'CSV', $th.exportCallback)
     },
     exportXLS: function (btn, e) {
         var $th = Ext.getCmp(mspre.config.grid_id)
         $th.setAction('export', mspre.config.controller, 'XLS', $th.exportCallback)
     },
 */

    exportRun: function (format) {
        var grid = Ext.getCmp(mspre.config.grid_id)
        var format = format

        var baseParamsCyclic = {cyclic: true, do_not_return: true, limit: 100000}
        for (var keyParam in grid.baseParams) {
            if (grid.baseParams.hasOwnProperty(keyParam)) {
                baseParamsCyclic[keyParam] = grid.baseParams[keyParam]
            }
        }

        // Показываем окно загрузки
        mspre.progress = Ext.MessageBox.wait('', _('please_wait'))
        mspre.progress.updateText(_('mspre_treated_resources'))
        MODx.Ajax.request({
            url: mspre.config.connector_url,
            params: baseParamsCyclic,
            listeners: {
                success: {
                    fn: function (r) {
                        if (r.success) {
                            document.location.href = mspre.config.exportUrl + '?format=' + format + '&controller=' + mspre.config.controller
                            mspre.progress.hide()
                            return false
                        }
                    },
                    scope: this
                },
                failure: {
                    fn: function (response) {
                        MODx.msg.alert(_('mspre_error'), response.message)
                    },
                    scope: this
                }
            }
        })

        //
    },

    /**
     * Запуск функций без цикла
     * @param btn
     * @param e
     * @returns {boolean}
     */
    setPropertyMassiv: function (btn, e) {
        var $th = Ext.getCmp(mspre.config.grid_id)
        $th.setAction('setproperty', btn.field_name, btn.field_value)
    },

    /**
     * Запуск функций без цикла
     * @param btn
     * @param e
     * @returns {boolean}
     */
    setPropertyMassivProgress: function (btn, e) {
        Ext.getCmp(mspre.config.grid_id).setAction('setproperty', btn.field_name, btn.field_value, null, true)
    },

    windowOptionsCategory: function (field, e) {

        var grid = Ext.getCmp(mspre.config.grid_id)
        var record = grid.getChecked()

        if (!record) return false

        if (grid.windows.massActions) {
            grid.windows.massActions.destroy()
        }

        grid.windows.massActions = MODx.load({
            xtype: 'mspre-window-options-change-category',
            record: record,
            mode: field.combo_id,
            field_params: field.field_params,
            listeners: {
                'success': {
                    fn: function (r) {
                        grid.refresh()
                    }, scope: this
                },
                afterrender: {
                    fn: function () {
                        grid.enabledMask()
                    },
                    scope: this
                },
                hide: {
                    fn: function () {
                        grid.windows.massActions.destroy()
                        grid.disabledMask()
                    },
                    scope: this
                }
            }
        })
        grid.windows.massActions.setValues(record)
        grid.windows.massActions.show(e.target)
        return true
    },
    windowTvTemplate: function (field, e) {

        var grid = Ext.getCmp(mspre.config.grid_id)
        var record = grid.getChecked()
        if (!record) return false

        if (grid.windows.massActions) {
            grid.windows.massActions.destroy()
        }
        grid.windows.massActions = MODx.load({
            xtype: 'mspre-window-tv-change-template',
            record: record,
            mode: field.field_params.mode,
            field_params: field.field_params,
            listeners: {
                'success': {
                    fn: function (r) {
                        grid.refresh()
                    }, scope: this
                },
                afterrender: {
                    fn: function () {
                        grid.enabledMask()
                    },
                    scope: this
                },
                hide: {
                    fn: function () {
                        grid.windows.massActions.destroy()
                        grid.disabledMask()
                    },
                    scope: this
                }
            }
        })
        grid.windows.massActions.setValues(record)
        grid.windows.massActions.show(e.target)
        return true
    },

    /**
     * Запуск функций без цикла
     * @param btn
     * @param e
     * @returns {boolean}
     */
    defaultAction: function (btn, e) {
        Ext.getCmp(mspre.config.grid_id).setAction(btn.combo_id)
        return true
    },

    /**
     * Запуск функци с циклами
     * @param btn
     * @param e
     * @returns {boolean}
     */
    defaultActionProgress: function (btn, e) {
        Ext.getCmp(mspre.config.grid_id).setAction(btn.combo_id, '', '', null, true)
        return true
    },

    /**
     * Запуск комбо боксов
     * @param btn
     * @param e
     * @returns {boolean}
     */
    defaultCombo: function (btn, e) {
        if (!btn.combo_id || btn.combo_id === '') {
            Ext.MessageBox.alert(_('mspre_error'), _('mspre_error_massive_combo_id_empty'))
            return false
        }

        if (!btn.field_params || btn.field_params < 0) {
            Ext.MessageBox.alert(_('mspre_error'), _('mspre_error_massive_field_params_empty'))
            return false
        }

        if (!Ext.ComponentMgr.isRegistered(btn.combo_id)) {
            Ext.MessageBox.alert(_('mspre_error'), _('mspre_error_massive_combo_id_empty_class') + btn.combo_id)
            return false
        }

        var grid = Ext.getCmp(mspre.config.grid_id)
        var record = grid.getChecked()

        if (!record) return false

        var boxindow = MODx.load({
            xtype: btn.combo_id
            , record: record
            , params: btn.field_params
            , listeners: {
                'success': {
                    fn: function (r) {
                        grid.refresh()
                    }, scope: this
                },
                afterrender: {
                    fn: function () {
                        grid.enabledMask()
                    },
                    scope: this
                },
                hide: {
                    fn: function () {
                        grid.disabledMask()
                    },
                    scope: this
                }
            }
        })
        boxindow.setValues(record)
        boxindow.show(e.target)
        return true
    },
    updateBox: function (btn, e) {
        if (!btn.combo_id || btn.combo_id === '') {
            Ext.MessageBox.alert(_('mspre_error'), _('mspre_error_massive_combo_id_empty'))
            return false
        }

        if (!Ext.ComponentMgr.isRegistered(btn.combo_id)) {
            Ext.MessageBox.alert(_('mspre_error'), _('mspre_error_massive_combo_id_empty_class') + btn.combo_id)
            return false
        }

        var grid = Ext.getCmp(mspre.config.grid_id)
        var record = grid.getChecked()
        if (!record) return false
        var boxindow = MODx.load({
            xtype: btn.combo_id
            , record: record
            , listeners: {
                'success': {
                    fn: function (r) {
                        grid.refresh()
                    }, scope: this
                }
            }
        })
        boxindow.setValues(record)
        boxindow.show(e.target)
        return true
    },
    enabledMask: function () {
        Ext.get('mspre-form-panel').mask()
        Ext.get(mspre.config.grid_id).mask()
    },
    disabledMask: function () {
        Ext.get('mspre-form-panel').unmask()
        Ext.get(mspre.config.grid_id).unmask()
    },

    loadResourcePageIframe: function (resource_id) {
        var url = mspre.config.manager_url + 'index.php?a=resource/update&mspre_iframe=1&id=' + resource_id
        MODx.helpWindow = new Ext.Window({
            id: this.id + 'window-resource-' + resource_id,
            cls: 'mspre-window-resource-modal-product',
            title: _('mspre_resource_update'),
            width: 1050,
            height: 700,
            modal: true,
            layout: 'fit',
            html: '<iframe onload="parent.MODx.helpWindow.getEl().unmask();" src="' + url + '" width="100%" height="100%" frameborder="0"></iframe>'
        })
        MODx.helpWindow.show(Ext.getBody())
    },

    /* grid table functeon context menu */
    editProduct: function (btn, el) {
        if (MODx.helpWindow) {
            MODx.helpWindow.destroy()
        }

        var resource_id = this.selected()
        if (resource_id === 0) {
            MODx.msg.alert(_('error'), 'Error resource_id')
            return false
        }
        this.loadResourcePageIframe(resource_id)

    },
    viewProduct: function () {
        window.open(this.menu.record['preview_url'])
        return false
    },
    deleteProduct: function () {
        this.productAction('delete')
    },
    undeleteProduct: function () {
        this.productAction('undelete')
    },
    publishProduct: function () {
        this.productAction('publish')
    },
    unpublishProduct: function () {
        this.productAction('unpublish')
    },
    showProduct: function () {
        this.productAction('show')
    },
    hideProduct: function () {
        this.productAction('hide')
    },
    duplicateProduct: function () {
        var r = this.menu.record
        var w = MODx.load({
            xtype: 'modx-window-resource-duplicate',
            resource: r.id,
            hasChildren: 0,
            listeners: {
                success: {
                    fn: function () {
                        this.reloadTree()
                        this.refresh()
                    }, scope: this
                }
            }
        })
        w.config.hasChildren = 0
        w.setValues(r.data)
        w.show()
    },
    create: function () {
        var cxt = mspre.store.state.context !== 'mgr' ? mspre.store.state.context : 'web'
        //MODx.loadPage('resource/create', 'class_key=' + mspre.config.classKey + '&context_key=' + cxt)

        if (MODx.helpWindow) {
            MODx.helpWindow.destroy()
        }
        var url = mspre.config.manager_url + 'index.php?a=resource/create&class_key=' + mspre.config.classKey + '&mspre_iframe=1&context_key=' + cxt
        MODx.helpWindow = new Ext.Window({
            id: this.id + 'window-resource',
            cls: 'mspre-window-resource-modal-product',
            title: _('mspre_resource_create'),
            width: 1050,
            height: 700,
            modal: true,
            layout: 'fit',
            html: '<iframe onload="parent.MODx.helpWindow.getEl().unmask();" src="' + url + '" width="100%" height="100%" frameborder="0"></iframe>'
        })
        MODx.helpWindow.show(Ext.getBody())

    },

    // manager column
    windowFields: function (config) {
        var grid = Ext.getCmp(mspre.config.grid_id)
        if (grid.windows.fields) {
            grid.windows.fields.destroy()
            grid.windows.fields = false
        }

        var mode = config.combo_id
        var controller = mspre.config.controller
        var enableSize = mode === 'table'
        var available_fields = mspre.config.fields[mode]['available']
        var selected_fields = mspre.config.fields[mode]['selected']
        grid.windows.fields = MODx.load({
            xtype: 'mspre-window-table-setup',
            available_fields: available_fields,
            selected_fields: selected_fields,
            enableSize: enableSize,
            listeners: {
                success: {
                    fn: function () {
                        location.reload()
                        grid.disabledMask()
                        grid.refresh()
                    }, scope: this
                },
                hide: {
                    fn: function () {
                        grid.windows.fields.destroy()
                    },
                    scope: this
                }
            }
        })
        grid.windows.fields.setValues({
            controller: controller,
            mode: mode,
        })
        grid.windows.fields.show()
        return true
    },
    assignSelected: function (btn, e) {
        var $this = Ext.getCmp(mspre.config.grid_id)
        var cs = $this._getSelectedIds()
        var result = true
        if (cs === false) {
            result = false
        } else if (cs.length === 0) {
            result = false
        }

        if (!result) {
            Ext.onReady(function () {
                var title = _('mspre_error_not_product_title')
                var msg = _('mspre_error_not_product_desc')
                Ext.MessageBox.alert(title, msg)
            })
            return false
        }

        if (!$this.windows.assignCategorys) {
            $this.windows.assignCategorys = MODx.load({
                xtype: 'mspre-window-categorys-assign'
                , listeners: {
                    success: {
                        fn: function () {
                            var tree = Ext.getCmp('mspre-tree-modal-categorys-assign-window')
                            tree.enable()
                            tree.refresh()
                            $this.disabledMask()
                            $this.refresh()
                        }, scope: this
                    },
                    hide: {
                        fn: function () {
                            $this.disabledMask()
                        }, scope: this
                    }
                }
            })
        }

        var f = $this.windows.assignCategorys.fp.getForm()
        f.reset()

        var actDom = Ext.getCmp('tbar-mspre-combo-mspre')

        f.setValues({categorys: cs})
        $this.windows.assignCategorys.show(e.target)
    },
    onClick: function (e) {
        var elem = e.getTarget()

        if (elem.closest('#msoptionsprice-grid-modification')) {
            // Отключаем любые клики в текущем гриде в случае есль контенер от другова
            return null
        }


        if (elem.nodeName === 'DIV') {
            // Для сортировки, иначе перестает работать
            var qtip = elem.getAttribute('ext:qtip')
            if (qtip === undefined || qtip === 'undefined') {
                return null
            }
        }



        if (elem.nodeName == 'BUTTON') {
            var row = this.getSelectionModel().getSelected()

            if (typeof (row) != 'undefined') {
                var action = elem.getAttribute('action')
                if (action == 'showMenu') {
                    var ri = this.getStore().find('id', row.id)
                    return this._showMenu(this, ri, e)
                } else if (typeof this[action] === 'function') {
                    this.menu.record = row.data
                    return this[action](this, e)
                }
            }
        } else if (elem.nodeName == 'A' && elem.href.match(/(\?|\&)a=resource/)) {
            if (e.button == 1 || (e.button == 0 && e.ctrlKey == true)) {
                // Bypass
            } else if (elem.target && elem.target == '_blank') {
                // Bypass
            } else {
                e.preventDefault()
                MODx.loadPage('', elem.href)
            }
        }
        return this.processEvent('click', e)
    },
    quickUpdate: function (btn, e) {

        var grid = Ext.getCmp(mspre.config.grid_id)
        var id = btn.initialConfig.ownerCt.record.id

        MODx.Ajax.request({
            url: MODx.config.connector_url
            , params: {
                action: 'resource/get'
                , id: id
            }
            , listeners: {
                'success': {
                    fn: function (r) {
                        var nameField = 'name'
                        var w = MODx.load({
                            xtype: 'modx-window-quick-update-modResource'
                            , record: r.object
                            , listeners: {
                                'success': {
                                    fn: function (r) {
                                        grid.refresh()
                                        //var newTitle = '<span dir="ltr">' + r.f.findField(nameField).getValue() + ' (' + w.record.id + ')</span>'
                                        //w.setTitle(w.title.replace(/<span.*\/span>/, newTitle))
                                    }, scope: this
                                }
                                , 'hide': {fn: function () {this.destroy()}}
                            }
                        })
                        w.title += ': <span dir="ltr">' + w.record[nameField] + ' (' + w.record.id + ')</span>'
                        w.setValues(r.object)
                        w.show(e.target)
                    }, scope: this
                }
            }
        })
    },
    request: function (action, params, callback) {
        params.action = action
        MODx.Ajax.request({
            url: mspre.config['connector_url']
            , params: params
            , listeners: {
                'success': {
                    fn: function (r) {
                        if (typeof callback === 'function') {
                            callback(r)
                        }
                    }, scope: this
                },
                'failure': {
                    fn: function (r) {
                        if (typeof callback === 'function') {
                            callback(r)
                        }
                    }, scope: this
                }
            }
        })
    }

})
Ext.reg('mspre-grid-product', mspre.grid.product)
