Ext.define('{{appName}}.store.{{tableName}}', {
    extend: 'Ext.data.Store',
    requires: '{{appName}}.model.{{tableName}}',
    model: '{{appName}}.model.{{tableName}}',
    pageSize: 50,
    autoLoad: false{% if table.getIsData == 1 %},    
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
{% endif %}    
});