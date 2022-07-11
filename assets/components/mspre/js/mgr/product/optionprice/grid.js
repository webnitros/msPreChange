setTimeout(function () {
    mspre.grid.OptionsPrice2Modification = function (config) {
        mspre.grid.OptionsPrice2Modification.superclass.constructor.call(this, config)
    }
    Ext.extend(mspre.grid.OptionsPrice2Modification, msoptionsprice.grid.modification, {
        getColumns: function (config) {
            var columns = msoptionsprice.grid.modification.prototype.getColumns.call(this, config)
            delete columns[1]
            columns = columns.filter(function (item) {
                if (item.id === 'actions') {
                    item.renderer = function (value, props, row) {
                        var res = []
                        var cls, icon, title, action, item = ''
                        for (var i in row.data.actions) {
                            if (!row.data.actions.hasOwnProperty(i)) {
                                continue
                            }
                            var a = row.data.actions[i]
                            if (!a['button']) {
                                continue
                            }

                            cls = a['cls'] ? a['cls'] : ''
                            icon = a['icon'] ? a['icon'] : ''
                            action = a['action'] ? a['action'] : ''
                            title = a['title'] ? a['title'] : ''

                            if (icon == 'icon icon-edit green') {
                                continue
                            }
                            if (icon == 'icon icon-trash-o red') {
                                continue
                            }

                            item = String.format(
                                '<li class="{0}"><button class="btn btn-default {1}" action="{2}" title="{3}"></button></li>',
                                cls, icon, action, title
                            )

                            res.push(item)
                        }

                        return String.format(
                            '<ul class="msoptionsprice-row-actions">{0}</ul>',
                            res.join('')
                        )
                    }
                }
                return true
            })
            return columns
        },

        getTopBar: function (config) {

            var tbar = []

            var component = ['menu', 'update', 'left', 'option', 'spacer']

            var add = {
                menu: {
                    text: '<i class="icon icon-cogs"></i> ',
                    menu: [{
                        text: '<i class="icon icon-plus"></i> ' + _('msoptionsprice_action_create'),
                        cls: 'msoptionsprice-cogs',
                        handler: this.create,
                        scope: this
                    }/*, {
                        text: '<i class="icon icon-trash-o red"></i> ' + _('msoptionsprice_action_remove'),
                        cls: 'msoptionsprice-cogs',
                        handler: this.remove,
                        scope: this
                    }, '-', {
                        text: '<i class="icon icon-toggle-on green"></i> ' + _('msoptionsprice_action_turnon'),
                        cls: 'msoptionsprice-cogs',
                        handler: this.active,
                        scope: this
                    }, {
                        text: '<i class="icon icon-toggle-off red"></i> ' + _('msoptionsprice_action_turnoff'),
                        cls: 'msoptionsprice-cogs',
                        handler: this.inactive,
                        scope: this
                    }*/]
                },
                update: {
                    text: '<i class="icon icon-refresh"></i>',
                    handler: this._updateRow,
                    scope: this
                },
                left: '->',
                option: {
                    xtype: 'msoptionsprice-combo-option-key',
                    name: 'key',
                    width: 200,
                    custm: true,
                    clear: true,
                    addall: true,
                    rid: config.resource.id || 0,
                    value: '',
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
                spacer: {
                    xtype: 'spacer',
                    style: 'width:1px;'
                },

                bigspacer: {
                    xtype: 'spacer',
                    style: 'width:5px;'
                }

            }

            component.filter(function (item) {
                if (add[item]) {
                    tbar.push(add[item])
                }
            })

            var items = []
            if (tbar.length > 0) {
                items.push(new Ext.Toolbar(tbar))
            }

            return new Ext.Panel({items: items})
        },

    })
    Ext.reg('mspre-grid-options-price2-modification', mspre.grid.OptionsPrice2Modification)
}, 300)



