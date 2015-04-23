Ext.define('{{appName}}.view._components.radio.{{table.getPhpName}}', {
    extend: 'Ext.form.RadioGroup',    
    fieldLabel: '{{table.getName}}',
    // cls: 'x-check-group-alt',
    alias: 'widget.{{table.getPhpName|lower}}radiogroup',    
    initComponent: function() {
        this.items = [
{% for row in data %}
			{boxLabel: '{{row.displayField}}', name: '{{table.getPkName}}', inputValue: '{{row.valueField}}'} {{ loop.last ? '' : ', ' }}
{% endfor %}
        ];
        this.callParent(arguments); 
    }
});