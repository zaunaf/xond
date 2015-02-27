Ext.define('{{appName}}.view._components.combo.{{table.getPhpName}}', {
    extend: 'Ext.form.field.ComboBox',
    alias: 'widget.{{table.getPhpName|lower}}combo',
    valueField: '{{table.getPkName}}',
    displayField: '{{table.getDisplayField}}',
    label: '{{table.getLabel}}',
	// editable: false,
    initComponent: function() {
        this.store = Ext.create('{{appName}}.store.{{table.getPhpName}}', {
            sorters: ['{{table.getName}}_id']
        });
        this.callParent(arguments);
    }
});