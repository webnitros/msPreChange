function initMspreTree () {
    var nav = Ext.getCmp('modx-leftbar-tabpanel')
    if (nav) {
        nav.add({
            title: _('mspre_category'),
            id: 'mspre-tree-categories-panel1',
            layout: 'anchor',
            getState: function () {
                return {
                    activeTab: this.items.indexOf(this.getActiveTab())
                }
            },
            items: [
                {
                    html: _('mspre_intro'),
                    bodyCssClass: 'panel-desc',
                }, {
                    /*title: _('mspre_category'),*/
                    cls: 'mspre-category-tab',
                    xtype: 'mspre-tree-option-categories',
                    id: 'mspre-tree-categories-panel',
                    optionGrid: 'mspre-grid-category',
                    border: true,
                    getState: function () {
                        return {
                            activeTab: this.items.indexOf(this.getActiveTab())
                        }
                    },
                    hideMode: 'offsets',
                }]
        })
        Ext.getCmp('mspre-tree-categories-panel1').show()
        Ext.getCmp('mspre-tree-categories-panel').show()
    }
}

function initMspreGrid () {

    var nav = Ext.getCmp('mspre-tree-and-product-panel')
    if (nav) {
        nav.add({
            xtype: 'mspre-grid-product',
            id: 'mspre-grid-product',
            columnWidth: .99
        })
    }

}

mspre.panel.All = function (config) {

    mspre.store = new MODx.HttpProvider({
        baseParams: {
            register: 'msprestate2' + mspre.config.controller
        }
    })

    mspre.store.initState({
        default_context: mspre.config.default_context || 'web',
        query: '',
        vendor: '',
        class_key: '',
        categories: {},
        per_page: 20,
        limit: 20,
        product_link: 0,
        template: 0,
        resource_group: 0,

        filter_field: '',
        filter_type: '',
        filter_value: '',
        option_key: '',
        option_value: '',
        option_value_exclude: 'IN',
        search: '',
        context: mspre.config.default_context || 'web',

// Состояни фильтров
        zoom: mspre.config.zoom,
        additional: mspre.config.additional || false,
        favorites: mspre.config.favorites || false,
        purchased_goods: mspre.config.purchased_goods || false,
        filter_modifications: mspre.config.filter_modifications || false,
        nested: mspre.config.nested || true,
        favorite_resource: mspre.config.favorite_resource || {},
    })

    config = config || {}

    Ext.apply(config, {
        title: '<h2>' + _('mspre_' + mspre.config.controller) + ' :: ' + _('mspre_desc') + '</h2>',
        baseCls: 'modx-formpanel',
        cls: 'mspre-formpanel',
        header: true,
        standardSubmit: true,
        buttons: this.getButtons(config),
        layout: 'anchor',
        hideMode: 'offsets',
        items: [{
            xtype: 'modx-tabs',
            id: 'mspre-panel-home-tabs',
            defaults: {
                border: false,
                autoHeight: true
            },
            border: true,
            hideMode: 'offsets',
            items: this.getItems()
        }]
    })
    mspre.panel.All.superclass.constructor.call(this, config)

}
//Ext.extend(mspre.panel.All, MODx.Panel)
Ext.extend(mspre.panel.All, MODx.Panel, {

    getItems: function (config) {
        /*
        // Скролит всю таблицу
        var scrolling = {
             layout:{
                 tableAttrs: {
                     style: {width:'100%'}
                 },
                 type:'table'
             },
             cls: 'body',

             height: 500,
             autoScroll: true,
             id: 'mspre-tree-and-product-panel'
         }*/
        var scrolling = {
            layout: 'column',
            id: 'mspre-tree-and-product-panel'
        }

        var items = []
        items.push({
            title: _('mspre_' + mspre.config.controller + 's')
            , deferredRender: true
            , items: [
                {
                    xtype: 'mspre-form-panel'
                    , id: 'mspre-form-panel'
                    , border: false
                },
                scrolling
            ]
        })

        if (mspre.config.controller === 'product') {
            items.push({
                title: _('mspre_transactions'),
                layout: 'anchor',
                items: [{
                    html: _('mspre_transactions_intro_msg'),
                    cls: 'panel-desc',
                }, {
                    xtype: 'mspre-grid-transactions',
                    cls: 'main-wrapper',
                }]
            })
        }
        return items
    },
    getButtons: function (config) {

        var buttons = []
        switch (mspre.config.controller) {
            case 'product':
                buttons.push({
                    text: '<i class="icon icon-arrow-right"></i>  ' + _('mspre_panel_resource'),
                    handler: function () {
                        MODx.loadPage('index.php?a=resource&namespace=mspre')
                    }
                })
                break
            case 'resource':
                buttons.push({
                    text: '<i class="icon icon-arrow-right"></i>  ' + _('mspre_panel_product'),
                    handler: function () {
                        MODx.loadPage('index.php?a=product&namespace=mspre')
                    }
                })
                break
            default:
                break
        }

        buttons.push({
            text: '<i class="icon icon-question-circle"></i>  ' + _('mspre_help'),
            handler: function () {
                MODx.loadPage('index.php?a=help&namespace=mspre')
            }
        })

        return buttons
    },
})
Ext.reg('mspre-panel-all', mspre.panel.All)
