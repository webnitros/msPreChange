mspre.panel.PanelInfo = function (config) {
    config = config || {}
    if (!config.id) {
        config.id = 'mspre-form-panel'
    }

    Ext.apply(config, {
        layout: 'form',
        cls: 'main-wrapper',
        defaults: {msgTarget: 'under', border: false},
        anchor: '100% 100%',
        border: false,
        items: this.getFields(config),
        listeners: this.getListeners(config),
        keys: this.getKeys(config)
    })

    mspre.panel.PanelInfo.superclass.constructor.call(this, config)


    this.on('afterrender', function() {
        var w = this;
        initMspreTree()
        initMspreGrid()
    });
}
Ext.extend(mspre.panel.PanelInfo, MODx.FormPanel, {
    grid: null,
    saveState: false,

    getFields: function (config) {
        var setting = mspre.config.form

        return [
            {
                layout: 'column',
                items: [
                    {
                        columnWidth: setting.column,
                        layout: 'form',
                        defaults: {anchor: '100%', hideLabel: true},
                        items: this.getLeftFields(config),
                    },
                    {
                        columnWidth: setting.column,
                        layout: 'form',
                        defaults: {anchor: '100%', hideLabel: true},
                        items: this.getLeftAfterFields(config),
                    },
                    {
                        columnWidth: .190,
                        layout: 'form',
                        defaults: {anchor: '100%', hideLabel: true},
                        items: this.getCenterFields(config),
                    },
                    {
                        columnWidth: setting.column,
                        layout: 'form',
                        id: 'form-right-fields',
                        defaults: {anchor: '100%', hideLabel: true},
                        items: this.getRightFields(config),
                    }
                ],
            }]
    },
    filters: function (name) {
        var filters = {
            class_key: {
                name: 'class_key',
                xtype: 'mspre-combo-class-map',
                id: 'mspre_filter_class_key',
                custm: true,
                clear: true,
                emptyText: _('mspre_empty_class_key'),
                listeners: {
                    select: {
                        fn: this._filterByCombo,
                        scope: this
                    },
                    afterrender: {
                        fn: this._filterByCombo,
                        scope: this
                    }
                }
            },
            option_key: {
                name: 'option_key',
                xtype: 'mspre-combo-filter-options',
                id: 'mspre_filter_options',
                custm: true,
                clear: true,
                emptyText: _('mspre_empty_options'),
                listeners: {
                    select: {
                        fn: function (field) {
                            this.isValueFilterOptions(field, true)
                        },
                        scope: this
                    },
                    afterrender: {
                        fn: function (field) {
                            var $th = this
                            window.setTimeout(function () {
                                $th.isValueFilterOptions(field, false)
                            }, 1050)
                        },
                        scope: this
                    }
                }
            },
            option_value: {
                name: 'option_value',
                xtype: 'mspre-combo-filter-options-value',
                id: 'mspre_filter_options_value',
                custm: true,
                clear: true,
                hidden: true,
                emptyText: _('mspre_empty_options'),
                listeners: {
                    select: {
                        fn: this._filterByCombo,
                        scope: this
                    },
                    afterrender: {
                        fn: this._filterByCombo,
                        scope: this
                    }
                }
            },
            option_value_exclude: {
                name: 'option_value_exclude',
                xtype: 'mspre-combo-filter-options-value-exclude',
                id: 'mspre_filter_options_value_exclude',
                custm: true,
                hidden: true,
                clear: true,
                emptyText: _('mspre_empty_options_exclude'),
                listeners: {
                    select: {
                        fn: this._filterByCombo,
                        scope: this
                    },
                    afterrender: {
                        fn: this._filterByCombo,
                        scope: this
                    }
                }
            },
            context: {
                name: 'context',
                id: 'mspre-filter-context',
                xtype: 'mspre-combo-context',
                width: 170,
                custm: false,
                clear: false,
                value: 'web',
                emptyText: _('mspre_empty_context'),
                listeners: {
                    select: {
                        fn: this._filterByCombo,
                        scope: this
                    },
                    afterrender: {
                        fn: this._filterByCombo,
                        scope: this
                    },
                    clear: {
                        fn: function (field) {
                            field.setValue('web')
                            this._filterSet('filter_value', 'web')
                        },
                        scope: this
                    }
                }
            },
            query: {
                name: 'query',
                xtype: 'mspre-field-search',
                id: 'mspre-filter-search',
                width: 200,
                clear: true,
                custm: true,
                hasFocus: false,
                listeners: {
                    search: {
                        fn: function (field) {
                            this._doSearch(field)
                        },
                        scope: this
                    },
                    clear: {
                        fn: function (field) {
                            field.setValue('')
                            this._clearSearch()
                        },
                        scope: this
                    },
                    afterrender: function (field) {
                        field.disable()
                    }
                }
            },
            status: {
                name: 'status',
                xtype: 'mspre-combo-status',
                width: 210,
                custm: true,
                clear: true,
                listeners: {
                    select: {
                        fn: this._filterByCombo,
                        scope: this
                    },
                    afterrender: {
                        fn: this._filterByCombo,
                        scope: this
                    }
                }
            },
            vendor: {
                name: 'vendor',
                xtype: 'mspre-combo-vendors',
                id: 'mspre_filter_vendor',
                width: 200,
                custm: true,
                clear: true,
                listeners: {
                    select: {
                        fn: this._filterByCombo,
                        scope: this
                    },
                    afterrender: {
                        fn: this._filterByCombo,
                        scope: this
                    }
                }
            },
            template: {
                name: 'template',
                xtype: 'mspre-combo-template',
                id: 'mspre_filter_template',
                custm: true,
                clear: true,
                emptyText: _('mspre_empty_template'),
                listeners: {
                    select: {
                        fn: this._filterByCombo,
                        scope: this
                    },
                    afterrender: {
                        fn: this._filterByCombo,
                        scope: this
                    }
                }
            },
            resource_group: {
                name: 'resource_group',
                xtype: 'mspre-combo-resource-group',
                id: 'mspre_filter_resource_group',
                custm: true,
                clear: true,
                emptyText: _('mspre_empty_resource_group'),
                listeners: {
                    select: {
                        fn: this._filterByCombo,
                        scope: this
                    },
                    afterrender: {
                        fn: this._filterByCombo,
                        scope: this
                    }
                }
            },
            total: {
                xtype: 'displayfield',
                id: 'mspre-panel-info',
                html: String.format('\
                  <table>\
                      <tr class="top">\
                          <td class="mspre_panel_info"><span id="mspre-panel-info-total_info">0</span>{0}</td>\
                          <td class="mspre_panel_info"><span id="mspre-panel-info-favorite">' + mspre.config.favorite_resource.length + '</span>{1}</td>\
                      </tr>\
                  </table>',
                    _('mspre_form_total'),
                    _('mspre_form_favorite')
                ),
            },
            filter_field: {
                xtype: 'mspre-combo-filterfield',
                name: 'filter_field',
                custm: true,
                clear: true,
                id: 'mspre_filter_field',
                listeners: {
                    select: {
                        fn: function (field) {
                            this.isValueFilter(field)
                        },
                        scope: this
                    },
                    afterrender: {
                        fn: function (field) {
                            var $th = this
                            window.setTimeout(function () {
                                $th.isValueFilter(field)
                            }, 1050)
                        },
                        scope: this
                    }
                }
            },
            filter_type: {
                name: 'filter_type',
                xtype: 'mspre-combo-filtertype',
                id: 'mspre_filter_type',
                width: 360,
                custm: true,
                clear: true,
                hidden: true,
                listeners: {
                    select: {
                        fn: function (field, records) {
                            this.isValueFilter2(field, records)
                        },
                        scope: this
                    },
                    afterrender: {
                        fn: function (field, records) {
                            var $th = this
                            window.setTimeout(function () {
                                $th.isValueFilter2(field, records)
                            }, 1150)
                        },
                        scope: this
                    }
                }
            },
            filter_value: {
                name: 'filter_value',
                xtype: 'mspre-field-search',
                id: 'mspre_filter_values',
                width: 200,
                custm: true,
                clear: true,
                hidden: true,
                emptyText: _('mspre_filter_value'),
                listeners: {
                    search: {
                        fn: function (field) {
                            this._filterSet('filter_value', field.getValue())
                        },
                        scope: this
                    },
                    clear: {
                        fn: function (field) {
                            field.setValue('')
                            this._filterSet('filter_value', '')
                        },
                        scope: this
                    }
                }
            },
            nested: {
                name: 'nested',
                xtype: 'xcheckbox',
                width: 200,
                boxLabel: _('mspre_category_show_nested'),
                description: _('mspre_category_show_nested_desc'),
                ctCls: 'tbar-checkbox',
                listeners: {
                    check: {
                        fn: this.nestedFilter,
                        scope: this
                    }
                }
            },
            additional: {
                name: 'additional',
                xtype: 'xcheckbox',
                width: 200,
                boxLabel: _('mspre_category_show_additional'),
                description: _('mspre_category_show_additional_desc'),
                ctCls: 'tbar-checkbox',
                listeners: {
                    check: {
                        fn: this.additionalFilter,
                        scope: this
                    }
                }
            },
            favorites: {
                name: 'favorites',
                xtype: 'xcheckbox',
                width: 200,
                boxLabel: _('mspre_show_favorites'),
                description: _('mspre_show_favorites_desc'),
                ctCls: 'tbar-checkbox',
                listeners: {
                    check: {
                        fn: this.favoritesFilter,
                        scope: this
                    }
                }
            },


            purchased_goods: {
                name: 'purchased_goods',
                xtype: 'xcheckbox',
                width: 200,
                boxLabel: _('mspre_show_purchased_goods'),
                description: _('mspre_show_purchased_goods_desc'),
                ctCls: 'tbar-checkbox',
                listeners: {
                    check: {
                        fn: this.orderedPurchasedGoods,
                        scope: this
                    }
                }
            },
            filter_modifications: {
                name: 'filter_modifications',
                xtype: 'xcheckbox',
                width: 200,
                boxLabel: _('mspre_show_filter_modifications'),
                description: _('mspre_show_filter_modifications_desc'),
                ctCls: 'tbar-checkbox',
                listeners: {
                    check: {
                        fn: this.orderedFilterModifications,
                        scope: this
                    }
                }
            },
            product_link: {
                name: 'product_link',
                xtype: 'mspre-combo-link',
                id: 'mspre_product_link',
                custm: true,
                clear: true,
                emptyText: _('mspre_empty_product_link'),
                listeners: {
                    select: {
                        fn: this._filterByCombo,
                        scope: this
                    },
                    afterrender: {
                        fn: this._filterByCombo,
                        scope: this
                    }
                }
            }
        }

        return filters[name]
    },
    getFilters: function (selected) {
        var filters = []
        for (var i in selected) {
            if (!selected.hasOwnProperty(i)) {
                continue
            }
            filters.push(this.filters(selected[i]))
        }
        return filters
    },
    getLeftFields: function (config) {
        return this.getFilters(mspre.config.form.allowed.left)
    },
    getLeftAfterFields: function (config) {
        return this.getFilters(mspre.config.form.allowed.after)
    },
    getCenterFields: function () {
        return this.getFilters(mspre.config.form.allowed.center)
    },
    getRightFields: function (config) {
        return this.getFilters(mspre.config.form.allowed.right)
    },
    getListeners: function () {
        return {
            beforerender: function () {
                this.actionProduct('beforerender')
            },
            afterrender: function () {
                var form = this
                window.setTimeout(function () {
                    form.on('resize', function () {
                        form.updateInfo()
                    })
                }, 100)
            },
            change: function () {
                this.actionProduct('submit')
            },
        }
    },
    isValueFilterOptions: function (field, clear) {
        if (field.value) {
            this.showFilter('mspre_filter_options_value')
            this.showFilter('mspre_filter_options_value_exclude')
            var $optionValue = Ext.getCmp('mspre_filter_options_value')
            var $optionValueExclude = Ext.getCmp('mspre_filter_options_value_exclude')

            if ($optionValueExclude.value === '') {
                $optionValueExclude.setValue('IN')
                this._filterSet('option_value_exclude', 'IN')
            }
            if (clear) {
                $optionValue.clearValue()
            }
            $optionValue.store.reload({
                params: {
                    option_key: field.value,
                }
            })
            $optionValue.baseParams.option_key = field.value
        } else {
            this.hideFilter('mspre_filter_options_value')
            this.hideFilter('mspre_filter_options_value_exclude')
        }
        this._filterByComboNotRefrash(field)
    },
    isValueFilter: function (field) {
        if (field.value) {
            this.showFilter('mspre_filter_type')
        } else {
            this.hideFilter('mspre_filter_type')
            this.hideFilter('mspre_filter_values')
        }
        this._filterByComboNotRefrash(field)
    },
    isValueFilter2: function (field, records) {
        var disabled = false
        if (records) {
            if (records.data.value) {
                var value = records.data.value
                if (value == 'IS NULL' || value == 'IS NOT NULL') {
                    disabled = true
                }
            }
        }
        var filter_values = Ext.getCmp('mspre_filter_values')
        if (filter_values) {
            if (field.value) {
                this.showFilter('mspre_filter_values')
            } else {
                this.hideFilter('mspre_filter_values')
            }

            filter_values.setDisabled(disabled)
        }
        this._filterByComboNotRefrash(field)
    },
    getKeys: function () {
        return [{
            key: Ext.EventObject.ENTER,
            fn: function () {
                this.actionProduct('submit')
            },
            scope: this
        }]
    },
    nestedFilter: function (checkbox, checked) {
        this._filterSet('nested', checked ? 1 : 0)
    },
    additionalFilter: function (checkbox, checked) {
        this._filterSet('additional', checked ? 1 : 0)
    },
    favoritesFilter: function (checkbox, checked) {
        this._filterSet('favorites', checked ? 1 : 0)
    },
    orderedPurchasedGoods: function (checkbox, checked) {
        this._filterSet('purchased_goods', checked ? 1 : 0)
    },
    orderedFilterModifications: function (checkbox, checked) {
        this._filterSet('filter_modifications', checked ? 1 : 0)
    },
    _doSearch: function (tf) {
        this._filterSet('query', tf.getValue())
    },
    _clearSearch: function () {
        this._filterSet('query', '')
    },
    _filterSet: function (name, value, toll) {
        this.setState(name, value)
        this.actionProduct('getBottomToolbar')
    },
    _filterByCombo: function (cb) {
        this._filterSet(cb.name, cb.value)
    },
    _filterByComboNotRefrash: function (cb) {
        this.setState(cb.name, cb.value)
    },
    actionProduct: function (action, field, value) {
        this.grid = this.grid ? this.grid : Ext.getCmp(mspre.config.grid_id)
        if (this.grid) {
            var store = this.grid.getStore()
            switch (action) {
                case 'beforerender':
                    var form = this
                    store.on('load', function (res) {
                        form.updateInfo(res.reader['jsonData'])
                    })
                    break
                case 'setStore':
                    store.baseParams[field] = value
                    break
                case 'getBottomToolbar':
                    this.grid.getBottomToolbar().changePage(1)
                    break
                case 'submit':
                    var form = this.getForm()
                    var values = form.getFieldValues()
                    for (var i in values) {
                        if (i != undefined && values.hasOwnProperty(i)) {
                            store.baseParams[i] = values[i]
                        }
                    }
                    this.refresh()
                    break
                default:
                    break
            }
        }
    },
    setState: function (field, value) {

        if (field) {

            // save change
            if (this.saveState) {
                var newvalue = value
                switch (field) {
                    case 'categories':
                        newvalue = Ext.util.JSON.encode(newvalue)
                        break
                    default:
                        break
                }
                switch (field) {
                    case 'categories':
                    case 'context':
                        var tree = Ext.getCmp('mspre-tree-categories-panel')
                        if (tree) {
                            var old_value = tree.baseParams.context
                            switch (field) {
                                case 'categories':
                                    tree.baseParams.categories = newvalue
                                    break
                                case 'context':
                                    tree.baseParams.context = value

                                    if (old_value !== value) {
                                        tree.refresh()
                                    }
                                    break
                                default:
                                    break
                            }
                        }
                        break
                    default:
                        break
                }

                this.actionProduct('setStore', field, newvalue)

                var allowedSave = false
                var field_name = ''
                for (var i = 0; i < mspre.config.save_state_fields.length; i++) {
                    if (!mspre.config.save_state_fields.hasOwnProperty(i)) {
                        continue
                    }
                    field_name = mspre.config.save_state_fields[i]
                    if (field === field_name) {
                        allowedSave = true;
                    }
                }

                if (allowedSave) {
                    mspre.store.start()
                    mspre.store.queue = mspre.store.state
                    mspre.store.set(field, value)
                    mspre.store.submitState();
                }

               /* switch (field) {
                    case 'nested':
                    case 'additional':
                    case 'favorites':
                    case 'vendor':
                    case 'status':
                    case 'context':
                    case 'option_key':
                    case 'option_value':
                    case 'option_value_exclude':
                    case 'search':
                    case 'class_key':
                    case 'template':
                    case 'resource_group':
                    case 'filter_field':
                    case 'filter_type':
                    case 'filter_value':
                    case 'categories':
                    case 'start':
                    case 'limit':
                    case 'sort':
                    case 'dir':
                    case 'parent':
                    case 'query':
                        mspre.store.start()
                        mspre.store.queue = mspre.store.state
                        mspre.store.set(field, value)
                        mspre.store.submitState()

                        break
                    default:
                        break
                }*/

            }
        }
    },
    setFilter: function () {
        var $th = this
        var state = mspre.store.state
        var form = this.getForm()
        form.items.each(function (f) {
            var name = f.name
            if (state[f.name]) {
                var value = state[f.name]
                f.setValue(value)
                $th.actionProduct('setStore', name, value)
            }
        })
        this.refresh()
    },
    reset: function () {
        var $th = this
        var form = this.getForm()

        this.hideFilter('mspre_filter_type')
        this.hideFilter('mspre_filter_values')

        this.hideFilter('mspre_filter_options_value')
        this.hideFilter('mspre_filter_options_value_exclude')

        form.items.each(function (f) {
            if (f.name !== 'context' && f.name !== undefined) {
                $th.setState(f.name, '')
                // Сброс выбранного значениея
                this.setValue('')
            }
        })

        var values = form.getValues()
        for (var i in values) {
            if (values.hasOwnProperty(i)) {
                if (i !== 'context') {
                    $th.setState(i, '')
                }
            }
        }

        this.setState('categories', {})
        var tree = Ext.getCmp('mspre-tree-categories-panel')
        if (tree) {
            tree.refresh()
        }
        this.refresh()
    },
    refresh: function () {
        this.actionProduct('getBottomToolbar')
    },
    updateInfo: function (data) {
        var arr = {
            'total_info': 'total_info',
            'total_selected': 'total_selected',
        }

        for (var i in arr) {
            if (!arr.hasOwnProperty(i)) {
                continue
            }
            var text_size = 20
            var elem = Ext.get('mspre-panel-info-' + i)
            if (elem) {
                elem.setStyle('font-size', text_size + 'px')
                var val = data != undefined
                    ? data[arr[i]]
                    : elem.dom.innerText


                if (val === undefined) {
                  return false;
                }

                var elem_width = elem.parent().getWidth()
                var text_width = val.length * text_size * .6
                if (text_width > elem_width) {
                    for (var m = text_size; m >= 5; m--) {
                        if ((val.length * m * .6) < elem_width) {
                            break
                        }
                    }
                    elem.setStyle('font-size', m + 'px')
                }

                if (i === 'total_selected') {

                    if (val === 0) {
                        elem.setStyle('display','none')
                    } else {
                        elem.setStyle('display','')
                    }
                }
                elem.update(val)
            }
        }
    },
    hideFilter: function (id) {
        var filter = Ext.getCmp(id)
        if (filter) {
            filter.hide()
        }
    },
    showFilter: function (id) {
        var filter = Ext.getCmp(id)
        if (filter) {
            filter.show()
        }
    },
})
Ext.reg('mspre-form-panel', mspre.panel.PanelInfo)
