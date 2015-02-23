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
    ],
    proxy: {
        type: 'rest',
        url : 'rest/{{tableName}}',
        timeout: 120000,
        reader: {
            type: 'json',
            rootProperty: 'rows',
            totalProperty: 'results'
        },
        listeners: {
            exception: function(proxy, response, operation, eOpts) {
                // console.log(response);
                if (response.status == '400') {
                    var json = Ext.decode(response.responseText);
                    //Xond.msg('Error', json.message);
                    //Ext.Msg.alert('Error', 'Gagal menyimpan data {{tableName}} ('+ errorMsg +')');
                    Ext.Msg.alert('Error', json.message);
                }
            }
        }
    }
});