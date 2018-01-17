Ext.define('{{appName}}.store.{{tableName}}', {
    extend: 'Ext.data.Store',
    alias: 'store.{{tableName | lower}}',
    requires: '{{appName}}.model.{{tableName}}',
    model: '{{appName}}.model.{{tableName}}'{% if table.getIsData == 1 %},
    pageSize: 50,
{% if table.getGroupField %}
    groupField: '{{table.getGroupField}}',
{% endif %}
    autoLoad: false,
    listeners: {
        write: function(store, operation){
            var record = operation.getRecords()[0],
            name = Ext.String.capitalize(operation.action),
            verb;

            var msg = "";
            switch (name) {
                case 'Create':
                    msg = 'dibuat';
                    break;
                case 'Update':
                    msg = 'diperbarui';
                    break;
                case 'Destroy':
                    msg = 'dihapus';
                    break;
            }
            if (operation.wasSuccessful()) {
                Xond.msg('Info', 'Data {{tableName}} berhasil ' + msg);
            } else {
                var errorMsg = operation.getError();
                //Xond.msg('Info', 'Gagal menyimpan data {{tableName}} ('+ errorMsg +')');
                Ext.Msg.alert('Error', 'Gagal menyimpan data {{tableName}} ('+ errorMsg +')');
            }
        }
    }
    /*
    onCreateRecords: function(records, operation, success) {
        //console.log(records);
        if (success) {
            Xond.msg('Info', 'Berhasil menyimpan data');
        } else {
            var errorMsg = operation.getError();
            Xond.msg('Info', 'Gagal menyimpan data ('+ errorMsg +')');
        }
    },

    onUpdateRecords: function(records, operation, success) {
        //console.log(records);
        if (success) {
            Xond.msg('Info', 'Berhasil menyimpan data');
        } else {
            var errorMsg = operation.getError();
            Xond.msg('Info', 'Gagal menyimpan data ('+ errorMsg +')');
        }
    },

    onDestroyRecords: function(records, operation, success) {
        //console.log(records);
        if (success) {
            Xond.msg('Info', 'Berhasil menyimpan data');
        } else {
            var errorMsg = operation.getError();
            Xond.msg('Info', 'Gagal menyimpan data ('+ errorMsg +')');
        }
    },
    */
{% endif %}{% if table.is_static_ref == 1 %},
    proxy: {
        type: 'memory',
        reader: {
            type: 'json',
            rootProperty: 'rows'
        }
    }
{% else %},
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
    }{% endif %}
{% if table.is_static_ref == 1 %},
    autoLoad: true,
    data: {
        rows: [
{% for row in data %}
            {{row|join('","')|raw}}{% if not loop.last %},{% endif %}

{% endfor %}
        ]
    }
{% endif %}
});