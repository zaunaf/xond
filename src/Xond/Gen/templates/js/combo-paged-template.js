Ext.define('{{appName}}.view._components.combo.{{table.getPhpName}}', {
    extend: 'Ext.form.field.ComboBox',
    queryMode: 'remote',
    pageSize: 20,
    valueField: '{{table.getPkName}}',
    displayField: '{{table.getDisplayField}}',
    label: '{{table.getLabel}}',    
    alias: 'widget.{{table.getPhpName|lower}}combo',
    //hideTrigger: false,
    listeners: {
        beforerender: function(combo, options) {
            this.store.load();
        }
    },
    initComponent: function() {
        this.store = Ext.create('DataDikdas.store.{{table.getPhpName}}', {
            model: 'DataDikdas.model.{{table.getPhpName}}',
            sorters: ['{{table.getName}}_id']
        });
        this.callParent(arguments); 
    }
});