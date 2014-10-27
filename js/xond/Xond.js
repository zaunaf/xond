function in_array (needle, haystack, argStrict) {
  // http://kevin.vanzonneveld.net
  // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // +   improved by: vlado houba
  // +   input by: Billy
  // +   bugfixed by: Brett Zamir (http://brett-zamir.me)
  // *     example 1: in_array('van', ['Kevin', 'van', 'Zonneveld']);
  // *     returns 1: true
  // *     example 2: in_array('vlado', {0: 'Kevin', vlado: 'van', 1: 'Zonneveld'});
  // *     returns 2: false
  // *     example 3: in_array(1, ['1', '2', '3']);
  // *     returns 3: true
  // *     example 3: in_array(1, ['1', '2', '3'], false);
  // *     returns 3: true
  // *     example 4: in_array(1, ['1', '2', '3'], true);
  // *     returns 4: false
  var key = '',
    strict = !! argStrict;

  if (strict) {
    for (key in haystack) {
      if (haystack[key] === needle) {
        return true;
      }
    }
  } else {
    for (key in haystack) {
      if (haystack[key] == needle) {
        return true;
      }
    }
  }

  return false;
}

function substractSemester(semester_id) {
	
	var tahun = semester_id.substr(0,4);
	var smt = semester_id.substr(4,1);
	// console.log(semester_id);
	// console.log(tahun);
	// console.log(smt);
	// console.log((((smt - 1) == 1) ? tahun : (tahun-1)) + (((smt - 1) == 1) ? "1" : "2" ));
	return (((smt - 1) == 1) ? tahun : (tahun-1)) + (((smt - 1) == 1) ? "1" : "2" );

}

Ext.override(Ext.form.field.Text, {
    validator:function(text){
        return (text.length === 0 || Ext.util.Format.trim(text).length !== 0);
    }
});

// Update ComboBox Prototype so that big FK field could be rendered properly
Ext.override(Ext.form.field.ComboBox, {
  findRecord: function(field, value) {
    var ds = this.store;
    var idx = ds.findExact(field, value);
    if(idx === -1 && !this.initialRecordFound) {
      this.initialRecordFound = true;
      this.store.on({
        load: {
          fn: Ext.Function.bind(function(value) {
            if (this.forceSelection) {
              this.setValue(value);
            }
            this.store.removeAll();
          }, this, [value]),
          single: true
        }
      });
      ds.load({
        params: {
          id: value
        }
      });
    }
    return idx !== -1 ? ds.getAt(idx) : false;
  }
});

function generateUuid() {
	var uuid = UUID.genV1();
	return uuid;
}

Ext.ns('Xond');

Xond = function(){
    var msgCt;

    function createBox(t, s){
       return '<div class="msg"><h3>' + t + '</h3><p>' + s + '</p></div>';
    }
    return {
        msg : function(title, format){
            if(!msgCt){
                msgCt = Ext.DomHelper.insertFirst(document.body, {id:'msg-div'}, true);
            }
            var s = Ext.String.format.apply(String, Array.prototype.slice.call(arguments, 1));
            var m = Ext.DomHelper.append(msgCt, createBox(title, s), true);
            m.hide();
            m.slideIn('t').ghost("t", { delay: 1000, remove: true});
        },

        init : function(){
            if(!msgCt){
                // It's better to create the msg-div here in order to avoid re-layouts 
                // later that could interfere with the HtmlEditor and reset its iFrame.
                msgCt = Ext.DomHelper.insertFirst(document.body, {id:'msg-div'}, true);
            }
        },

        indoMoneyConverter: function(v){
          decPlaces = 2,
          decSeparator = ",",
          thouSeparator = ".",
          sign = v < 0 ? "-" : "",
          i = parseInt(v = Math.abs(+v || 0).toFixed(decPlaces)) + "",
          j = (j = i.length) > 3 ? j % 3 : 0;
          return "Rp " + sign + (j ? i.substr(0, j) + thouSeparator : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + thouSeparator) + (decPlaces ? decSeparator + Math.abs(v - i).toFixed(decPlaces).slice(2) : "");
        }
    };
}();

Ext.override(Ext.Window, {
    constrainHeader: true
});
Xond.shortBogusMarkup = '<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Sed metus nibh, '+
    'sodales a, porta at, vulputate eget, dui. Pellentesque ut nisl. Maecenas tortor turpis, interdum non, sodales '+
    'non, iaculis ac, lacus. Vestibulum auctor, tortor quis iaculis malesuada, libero lectus bibendum purus, sit amet '+
    'tincidunt quam turpis vel lacus. In pellentesque nisl non sem. Suspendisse nunc sem, pretium eget, cursus a, fringilla.</p>'; 
    
// Update Model, Store and Grid Prototypes so that it's flexible and capable of adding records simultaneously
Ext.override(Ext.data.Store,{
    addField: function(field){
        field = new Ext.data.Field(field);
        this.recordType.prototype.fields.replace(field);
        if(typeof field.defaultValue != 'undefined'){
            this.each(function(r){
                if(typeof r.data[field.name] == 'undefined'){
                    r.data[field.name] = field.defaultValue;
                }
            });
        }
    },
    removeField: function(name){
        this.recordType.prototype.fields.removeKey(name);
        this.each(function(r){
            delete r.data[name];
            if(r.modified){
                delete r.modified[name];
            }
        });
    }
});

Ext.override(Ext.grid.ColumnModel,{
    addColumn: function(column, colIndex){
        if(typeof column == 'string'){
            column = {header: column, dataIndex: column};
        }
        var config = this.config;
        this.config = [];
        if(typeof colIndex == 'number'){
            config.splice(colIndex, 0, column);
        }else{
            colIndex = config.push(column);
        }
        this.setConfig(config);
        return colIndex;
    },
    removeColumn: function(colIndex){
        var config = this.config;
        this.config = [config[colIndex]];
        config.splice(colIndex, 1);
        this.setConfig(config);
    }
});

Ext.override(Ext.grid.GridPanel,{
    addColumn: function(field, column, colIndex){
        if(!column){
            if(field.dataIndex){
                column = field;
                field = field.dataIndex;
            } else{
                column = field.name || field;
            }
        }
        this.store.addField(field);
        return this.colModel.addColumn(column, colIndex);
    },
    removeColumn: function(name, colIndex){
        this.store.removeField(name);
        if(typeof colIndex != 'number'){
            colIndex = this.colModel.findColumnIndex(name);
        }
        if(colIndex >= 0){
            this.colModel.removeColumn(colIndex);
        }
    }
});

Ext.override(Ext.form.field.Checkbox,{
// Ext.define('Ext.form.field.Checkbox', {
    // override : 'Ext.form.field.Checkbox',
    getValue: function () {
        return this.checked ? 1 : 0;
    }
});

Ext.override('Ext.field.Number', {

    applyValue: function(value) {
          var minValue = this.getMinValue(),
          maxValue = this.getMaxValue();
    
          if (Ext.isNumber(minValue) && Ext.isNumber(value)) {
              value = Math.max(value, minValue);
          }

          if (Ext.isNumber(maxValue) && Ext.isNumber(value)) {
              value = Math.min(value, maxValue);
          }

          value = parseFloat(value);
          return (isNaN(value)) ? '' : value;
    }

});