Ext.define('{{appName}}.model.{{tableName}}', {
    extend: 'Ext.data.Model',
    idProperty: '{{table.getPkName}}',
    clientIdProperty: 'ext_client_id',
    fields: [
{% for cvars in columns %}
{% if (cvars.type == 'date') %}
        { name: '{{cvars.column_name}}', type: 'date',  dateFormat: 'Y-m-d'}{{ loop.last ? '' : ',' }}
{% elseif (cvars.type == 'timestamp') %}
        { name: '{{cvars.column_name}}', type: 'date',  dateFormat: 'Y-m-d H:i:s'}{{ loop.last ? '' : ',' }}
{% else %}
{% if cvars.getIsFk == 1 %}
        { name: '{{cvars.column_name}}', type: '{{cvars.type}}', useNull: true },
        { name: '{{cvars.column_name}}_str', type: 'string'  }{{ loop.last ? '' : ',' }}
{% elseif (cvars.getIsFk == 1) and (cvars.type == 'float') %}
        { name: '{{cvars.column_name}}', type: '{{cvars.type}}', useNull: true },
        { name: '{{cvars.column_name}}_str', type: 'int'  }{{ loop.last ? '' : ',' }}
{% else %}
        { name: '{{cvars.column_name}}', type: '{{cvars.type}}'  }{{ loop.last ? '' : ',' }}
{% endif %}{% endif %}{% endfor %}
    ]
});