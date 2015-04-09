Ext.require([
    'Ext.window.MessageBox',
    'Ext.tip.*',
    'Ext.Component',

{% if options.combo_count > 0 %}
    '{{appName}}.view._components.combo.*',
{% endif %}
{% if options.radiogroup_count > 0 %}
    '{{appName}}.view._components.radio.*',
{% endif %}
{% if table.getCreateForm > 0 %}
    '{{appName}}.view._components.form.{{table.getPhpName}}',
{% endif %}
{% if table.getCreateGrid > 0 %}
    '{{appName}}.view._components.grid.{{table.getPhpName}}'
{% endif %}
]);

Ext.define('{{appName}}.controller.base.{{table.getPhpName}}', {
    extend: 'Ext.app.Controller',
    views: [
{% set jml = 0 %}
{% for col in columns %}
{% if col.getIsFk == '1' %}
{% if jml < 1 %}
        '_components.combo.{{col.getFkTablePhpName}}'
{% else %}
		,'_components.combo.{{col.getFkTablePhpName}}'
{% endif %}
{% set jml = jml + 1 %}
{% endif %}
{% if col|cek_xtype == 'radio' %}
{% if jml < 1 %}
        '_components.radio.{{col.getFkTablePhpName}}'
{% else %}
		,'_components.radio.{{col.getFkTablePhpName}}'
{% endif %}
{% set jml = jml + 1 %}{% endif %}
{% endfor %}
    ],
    
    init: function() {
        this.control({
            '{{table.getPhpName|lower}}form button[action=save]': {
                click: this.saveForm
            },
{% if table.getHasSplitEntity %}
            '{{table.getPhpName|lower}}form': {
                afterrender: this.afterRenderForm
            },
{% endif %}
{% if table.getBelongsTo|sizeof == 1 %}
            '{{table.getPhpName|lower}}grid': {
                afterrender: this.afterRender
            },
{% endif %}
            '{{table.getPhpName|lower}}grid button[action=add]': {
                click: this.addRecord
            },
            '{{table.getPhpName|lower}}grid button[action=edit]': {
                click: this.editRecord
            },
            '{{table.getPhpName|lower}}grid button[action=save]': {
                click: this.saveRecord
            },
            '{{table.getPhpName|lower}}grid button[action=delete]': {
                click: this.deleteRecord
            }
        });
        
        //this.callParent(arguments);
    },
{% if table.getHasSplitEntity %}
    afterRenderForm: function(form, opt) {
        var splitForm = Ext.create('{{appName}}.view._components.form.{{table.getSplitEntityPhpName}}');
        var fields = splitForm.getFields();
        for (i = 0; i < fields.length; i++) {
            form.insert(fields[i]);
        }
    },
{% endif %}
{% if table.getBelongsTo|sizeof == 1 %}
    afterRender: function(grid, opt) {
        this.setParentId(grid.parentId);
        Ext.Function.defer(function(){
            grid.getStore().load({
                params: {
                    {{table|getlocalfkcolumnname}}: this.getParentId()
                }
            });
        }, 1000, this);        
        grid.newRecordCfg = {
            {{table|getlocalfkcolumnname}}: this.getParentId()
        };
    },
    getParentId: function() {
        return this.parentId;
    },    
    setParentId: function(id) {
        this.parentId = id;
    },    
{% endif %}
    saveForm: function(btn) {
        
        //Ext.Msg.alert('Saving', 'Manyimpan..');
        
        // Dari button, 'look up'/lihat ke atas, cari container dengan xtype = 'form'
        var form = btn.up('form');
        if (form.isValid()) {
            // Ambil record dari form, form tersebut diisi dari sebuah record
            var record = form.getRecord();
            var values = form.getValues(false,false,false,true);
            record.set(values);
            
            // Synchronize the store after editing the record
            var store = record.store;
            store.sync();
        } else {
            Xond.msg('Error', 'Gagal menyimpan, mohon isi form dengan benar. <br>Field yang salah ditandai dengan kotak merah.');
        }
        
    },
    addRecord: function(btn) {
        
        //Ext.Msg.alert('Info', 'Manambah..');
        //return;
        
        // Dari button, 'look up'/lihat ke atas, cari container dengan xtype = 'form'
        var grid = btn.up('gridpanel');
        grid.rowEditing.cancelEdit();

        // Create a model instance
        var r = this.getNewRecord(grid);

        grid.store.insert(0, r);
        grid.rowEditing.startEdit(0, 0);
        
    },
    editRecord: function(btn) {
        // Defaults using row editor. Override this to use other method, such as form etc        
        var grid = btn.up('gridpanel');
        grid.rowEditing.cancelEdit();
        
        //var selections = grid.getSelectionModel().getSelection();
        var selections = grid.getSelection();
        var r = selections[0];
        if (!r) {
            Xond.msg('Error', 'Mohon pilih salah satu baris');
            return;
        }
        var startEditingColumnNumber = 0;
        for (var i=0; i<grid.columns.length; i++) {
            if (grid.columns[i].isVisible()) {
                var startEditingColumnNumber = i;
                break;
            }
        }
        grid.rowEditing.startEdit(r, startEditingColumnNumber);
    },
    saveRecord: function(btn) {
        var grid = btn.up('gridpanel');
        grid.store.sync();
    },
    deleteRecord: function(btn) {
        // Defaults using row editor. Override this to use other method, such as form etc        
        var grid = btn.up('gridpanel');
        grid.rowEditing.cancelEdit();
        
        // var selections = grid.getSelectionModel().getSelection();
        var selections = grid.getSelection();
        var r = selections[0];
        if (!r) {
            Xond.msg('Error', 'Mohon pilih salah satu baris');
            return;
        }
		
		Ext.MessageBox.show({
			title: 'Hapus Data',
{% if (table.getInfoBeforeDelete) %}
			msg: '{{table.getInfoBeforeDelete}}',
{% else %}
			msg: 'Apakah anda yakin ingin menghapus data tsb ?',
{% endif %}
			buttonText: {yes: "Ya", no: "Tidak"},
			fn: function(btn){
				if (btn == "yes") {
					r.erase();
					grid.store.sync();
				}
				// console.debug('you clicked: ',btn); //you clicked:  yes
			}
		});
    },
    getNewRecord: function(grid) {
        var recordConfig = {
{% for val in vals %}
            {{val|raw}}{{ loop.last ? '' : ',' }}
{% endfor %}
        };
        Ext.apply(recordConfig, grid.newRecordCfg);
        var r = new {{appName}}.model.{{table.getPhpName}}(recordConfig);
        return r;
    }
});