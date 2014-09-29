Ext.define('{{appName}}.view._components.combo.{{table.getPhpName}}', {
    extend: 'Ext.form.field.ComboBox',
    queryMode: 'local',
    alias: 'widget.{{table.getPhpName|lower}}combo',
    valueField: '{{table.getPkName}}',
    displayField: '{{table.getDisplayField}}',
    label: '{{table.getLabel}}',
	// editable: false,
    initComponent: function() {
        var {{table.getName}}s = [
{% for row in data %}
            ["{{row|join('","')|raw}}"],
{% endfor %}
        ];
        this.store = Ext.create('DataDikdas.store.{{table.getPhpName}}', {
            model: 'DataDikdas.model.{{table.getPhpName}}',
            sorters: ['{{table.getName}}_id'],
            data: {{table.getName}}s
        });
        this.callParent(arguments); 
    }
});