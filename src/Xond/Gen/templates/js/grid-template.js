Ext.define('{{appName}}.view._components.grid.{{table.getPhpName}}', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.{{table.getPhpName|lower}}grid',
    title: '',
    selType: 'rowmodel',
    autoScroll: true,
    createStore: function() {
        return Ext.create('{{appName}}.store.{{table.getPhpName}}');
    },
    createEditing: function() {
        return Ext.create('Ext.grid.plugin.CellEditing', {
            clicksToEdit: 2
        });
    },
    createDockedItems: function() {
        return [{
            xtype: 'toolbar',
            items: [{
                xtype: 'button',
                text: 'Tambah',
                // iconCls: 'fa fa-plus fa-lg glyph-dark-green glyph-shadow',
                iconCls: 'ic-add',
                scope: this,
                action: 'add'
            }, {
                xtype: 'button',
                text: 'Ubah',
                // iconCls: 'fa fa-eraser fa-lg glyph-dark-orange glyph-shadow',
                iconCls: 'ic-pencil',
                itemId: 'edit',
                scope: this,
                action: 'edit'
            }, {
                xtype: 'button',
                text: 'Simpan',
                // iconCls: 'fa fa-check fa-lg glyph-blue glyph-shadow',
                iconCls: 'ic-disk',
                itemId: 'save',
                scope: this,
                action: 'save'
            }, {
                xtype: 'button',
                text: 'Hapus',
                // iconCls: 'fa fa-remove fa-lg glyph-red glyph-shadow',
                iconCls: 'ic-cross',
                itemId: 'delete',
                scope: this,
                action: 'delete'
            }]
        }];
    },
    createBbar: function(){
        return Ext.create('Ext.PagingToolbar', {
            store: this.store,
            displayInfo: true,
            displayMsg: 'Displaying data {0} - {1} of {2}',
            emptyMsg: "Tidak ada data"
        });
    },
    initComponent: function() {
        
        var grid = this;
        
        this.store = this.createStore();
        
        // You can instaniate the component with basic parameters that affects filtering of the store
        // For example:
        // {
        //     xtype: 'yourgrid',
        //     baseParams: {
        //         area_id: 120,
        //         status_id: 1
        //     }
        // }      
        if (this.initialConfig.baseParams) {
            
            var baseParams = this.initialConfig.baseParams;
            this.store.on('beforeload', function(store, options) {
                Ext.apply(store.proxy.extraParams, baseParams);
            });
            
        }
        
        this.getSelectionModel().setSelectionMode('SINGLE');
        
        var rowEditing = Ext.create('Ext.grid.plugin.RowEditing', {
            listeners: {
                cancelEdit: function(rowEditing, context) {
                    // Canceling editing of a locally added, unsaved record: remove it
                    if (context.record.phantom) {
                        grid.store.remove(context.record);
                    }
                }
            }
        });
        
        var editing = this.createEditing();

        this.rowEditing = editing;
        this.plugins = [editing];

{% for col in columns %}
{% if col.getIsFk == 1 %}
{% if col.getFkTableInfo.getIsBigRef != 1 %}
        this.lookupStore{{col.getFkTableName}} = new {{appName}}.store.{{col.getFkTableName}}({
            autoLoad: true
        });
{% endif %}
{% endif %}
{% endfor %}
        
        this.columns = [{
{% for col in columns %}
{% if col.getEnumValues %}			
            header: '{{col.getHeader}}',
            tooltip: '{{col.getHeader}}',
            width: {{col.getColumnWidth}},
            sortable: true,
            dataIndex: '{{col.getName}}',
			hideable: false,
            field: {
                xtype: 'combobox',
				selectOnTab: true,
                valueField: 'id',
                hiddenName: 'id',
{% if col.getReadOnly %}
                readOnly: true,
{% endif %}
                displayField: 'data',
				store: {
                    fields: [{ name : 'id'},{ name : 'data'}],
                    data: [
{% for key,value in col.getEnumValues %}
{% if col.getType == 'string' %}
                        {'id': '{{key}}', 'data': '{{value}}'}{{ loop.last ? '' : ',' }}
{% else %}
                        {'id': '{{key}}', 'data': '{{value}}'}{{ loop.last ? '' : ',' }}
{% endif %}
{% endfor %}
                    ]
                },
				lazyRender: true            
            },
			renderer: function(v,p,r) {
				switch (v) {
{% for key,value in col.getEnumValues %}
{% if col.getType == 'string' %}
					case '{{key}}' : return '{{value}}'; break;
{% else %}
					case {{key}} : return '{{value}}'; break;					
{% endif %}
{% endfor %}
					default : return '-'; break;
				}
            }
{% else %}
            header: '{{col.getHeader}}',
            tooltip: '{{col.getHeader}}',
            width: {{col.getColumnWidth}},
            sortable: true,
            dataIndex: '{{col.getName}}',
			hideable: false,
{% if col.getHideColumn == 1 %}
            hidden: true,
{% endif %}
{% if col.getXtype == 'datefield' %}
            renderer : Ext.util.Format.dateRenderer('d/m/Y'),
{% endif %}
{% if col.getIsFk == 1 %}
{% if col.getFkTableInfo.getIsBigRef != 1 %}
            renderer: function(v,p,r) {
                var record = this.lookupStore{{col.getFkTableName}}.getById(v);
                if(record) {
                    return record.get('{{col.getFkTableInfo.getDisplayField}}');
                } else {
                    return v;
                }                        
            },
{% else %}
            renderer: function(v,p,r) {
                return r.data.{{col.getFkTableInfo.getRendererString}};
            },
{% endif %}
{% endif %}
            field: {
{% if col.getIsFk == 1 %}
                xtype: '{{col.getComboXtype}}'{% if col.getFkTableInfo.getIsBigRef == 1 %},
                listeners: {
                    change: function(combo, newValue, oldValue, eOpts ) {
                        var grid = combo.up('gridpanel');
                        var selections = grid.getSelectionModel().getSelection();
                        var r = selections[0];
                        r.set('{{col.getFkTableInfo.getRendererString}}', combo.getRawValue());
                    }
                }
{% endif %}
{% else %}
                xtype: '{{col.getXtype}}'
{% if col.getReadOnly %}
                ,readOnly: true
{% endif %}
{% if col.getInputLength and 'combo' not in col.getXtype and 'radiogroup' not in col.getXtype %}
				,maxLength: {{col.getInputLength}}
				,enforceMaxLength: true
				,minValue: {{col.getMin ? col.getMin : 0}}
{% endif %}
{% if col.getMinLength %}
				,minLength: {{col.getMinLength}}
{% endif %}
{% if col.getDecimalPrecision %}
				,decimalPrecision: '{{col.getDecimalPrecision}}'
{% endif %}
{% if col.getXtype == 'datefield' %}
                ,format: 'd/m/Y'
				,maxValue: new Date()
{% endif %}
{% endif %}
            }
{% endif %}
        {{ loop.last ? '' : '},{' }}
{% endfor %}
        }];
        
        this.dockedItems = this.createDockedItems();

        this.bbar = this.createBbar();
        
        this.callParent(arguments);
    }
});