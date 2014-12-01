// Get Primary Keys from REST Response.
// Assumptions data: rows. 
function getColumnArrayFromRestResponse(request, column) {
  var rows = request.rows;
  var outArr = [];
  for (var i = 0; i < rows.length; i++) {
    outArr[i] = rows[i][column];
  }
  return outArr;
}

// If there is, lets say 6 digit of numbers, get the most significant numbers without 0s
// For example: 102390 --> 10239. 128000 --> 128
function getSignificantDigits(number, minimum_digit) {
  
  minimum_digit = 0;

  var str = (typeof number === 'number') ? number.toString() : number.trim();
  var res = "";
  var sig = false;

  for (var i = (str.length-1); i >= minimum_digit; i--) {

    if (str[i] === '0' && !sig) {
      //lanjut
    } else {
      //non zero value found! It's all significant now
      sig = true;
      res = str[i] + res;
      //console.log(i + ":" + str[i]);
    }

  }
  return res;
}
    
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

Ext.override(Ext.form.field.ComboBox, {

  // Set initial value for big data FK
  setInitialValue: function(field, value){
      
      var me = this;
      var ds = this.store;
      var idx = ds.findExact(field, value);

      if(idx === -1 && !this.initialRecordFound) {
          
          var onLoad = function() {
              me.setValue(value);
              ds.un('load', onLoad);
          };
          ds.on('load', onLoad);
          
          var params = {};
          params[field] = value;

          ds.load({
              params: params
          });
      }
      
  },
  // Update ComboBox Prototype so that big FK field could be rendered properly
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
            m.slideIn('t').ghost("t", { delay: 2000, remove: true});
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
/* It's only for Extjs 3. So it is now disabled 
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
*/

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

Ext.override( Ext.grid.Panel, {
    getColumnByName: function(colname){
        //return this.down('[dataIndex='+ colname +']');
        var retVal={};
        for (var i=0; i < this.columns.length; i++) {
            if (this.columns[i].dataIndex==colname){
                retVal = this.columns[i];
            }
        }
        return retVal;
    },
    addColumn: function(index, column) {
        this.headerCt.insert(index, column);
        this.columns.splice(index, 0, column);
        this.getView().refresh();
        // var columns = this.columns;
        // //console.log(columns);
        // var store = this.store;
        // //console.log(store);
        // var newCols = [];
        // for (var i=0; i<columns.length; i++){
        //     newCols.push(columns[i].config);
        // }
        // newCols.push(column);
        // //columns.push(column);
        // this.reconfigure(store, columns);
    },
    removeColumn: function(columnId) {
        var grid = this;
        var col = grid.headerCt.getComponent(columnId);
        grid.headerCt.remove(col);
        grid.getView().refresh();
    },
    /* Set this column visible */
    changeVisibleColumn: function(colname, visible) {
        if (typeof this.getColumnByName(colname) === 'undefined') {
            Xond.msg('Error', 'No such column: ' + colname);
            return;
        }
        this.getColumnByName(colname).setVisible(visible);
    },
    /* Show multiple columns visible by array */
    changeVisibleColumns: function(arr){
        // Hide them all first
        for (var i = 0; i < this.columns.length; i++) {
            this.columns[i].setVisible(false);
        }
        // Check using column name one by one 
        for (var j = 0; j < arr.length; j++) {
            this.changeVisibleColumn(arr[j], true);
        }
    },
    changeHeader: function(colname,text){
        this.getColumnByName(colname).setText(text);
        this.getColumnByName(colname).tipMarkup = text;
    },
    /* NEED to be called after changeVisibleColumns change the configuration of visibiltiy of the columns */
    changeHeaders: function(headerArr){
        var visibleCols = [];
        for (var i=0; i < this.columns.length; i++) {
            if (this.columns[i].isVisible()) {
                visibleCols.push(this.columns[i]);
            }
        }
        if (headerArr.length != visibleCols.length) {
            console.log('Number of columns header to set is different with the number of the visible columns');
            return false;
        }
        for (var j=0; j < visibleCols.length; j++) {
            //console.log(visibleCols[j].dataIndex + '|' + headerArr[j]);
            this.changeHeader(visibleCols[j].dataIndex, headerArr[j]);
        }
    },
    changeWidth: function(colname, width){
        this.getColumnByName(colname).setWidth(width);
    },
    /* NEED to be called after changeVisibleColumns change the configuration of visibiltiy of the columns */
    changeWidths: function(widthArr){
        var visibleCols = [];
        for (var i=0; i < this.columns.length; i++) {
            if (this.columns[i].isVisible()) {
                visibleCols.push(this.columns[i]);
            }
        }
        if (widthArr.length != visibleCols.length) {
            console.log('Number of columns width to set is different with the number of the visible columns');
            return false;
        }
        for (var j=0; j<visibleCols.length; j++) {
            this.changeWidth(visibleCols[j].dataIndex, widthArr[j]);
        }
    },
    changeAlign: function(colname, align){
        this.getColumnByName(colname).align=align;
    },
    /* NEED to be called after changeVisibleColumns change the configuration of visibiltiy of the columns */
    changeAligns: function(alignArr){
        var visibleCols = [];
        for (var i=0; i < this.columns.length; i++) {
            if (this.columns[i].isVisible()) {
                visibleCols.push(this.columns[i]);
            }
        }
        if (alignArr.length != visibleCols.length) {
            console.log('Number of columns align to set is different with the number of the visible columns');
            return false;
        }
        for (var j=0; j < visibleCols.length; j++) {
            this.changeAlign(visibleCols[j].dataIndex, alignArr[j]);
        }
    },
    changeRenderer: function(colname, renderer){
        var col = this.getColumnByName(colname);
        switch (renderer) {
            case 'number':
                col.renderer = function (v, meta, record) {
                    return (v > 0) ? Ext.util.Format.number(v, '0,000') : 0;
                };
                break;
            case 'percent':
                col.renderer = function (v, meta, record) {
                    return (v > 0) ? Ext.util.Format.number(v, '0,000') + '%' : 0;
                };
                break;
            case 'check':
                //console.log('updating renderer check');
                col.renderer = function (v, meta, record) {
                    return (v > 0) ? "<img width=12 height=12 src='resources/icons2/tick.png'>": "<img width=12 height=12 src='resources/icons2/cross.png'>";
                };
                break;
            case 'new_check_cross':
                //console.log('updating renderer check');
                col.renderer = function (v, meta, record) {
                    var img = "";
                    if (v === 0) {
                        img = 'asterisk_orange';
                    } else if (v == 1) {
                        img = 'tick';
                    } else if (v == 2) {
                        img = 'cross';
                    }
                    return "<img width=12 height=12 src='resources/icons2/"+ img +".png'>";
                };
                break;
            case 'wrap':
                col.renderer = function (v, meta, record) {
                    meta.style = "white-space: normal; font-size: smaller;";
                    return v;
                };
                break;
            case 'decimal3':
                col.renderer = Ext.util.Format.numberRenderer('0.000');
                break;
            default:
                if (typeof renderer == 'function') {
                    col.renderer = renderer;
                } else if (renderer === '') {
                    // do nothing
                } else {
                    // 
                }
                break;
        }
    },
    changeRenderers: function(rendererArr){
        var visibleCols = [];
        for (var i=0; i < this.columns.length; i++) {
            if (this.columns[i].isVisible()) {
                visibleCols.push(this.columns[i]);
            }
        }
        if (rendererArr.length != visibleCols.length) {
            console.log('Number of columns align to set is different with the number of the visible columns');
            return false;
        }
        for (var j=0; j < visibleCols.length; j++) {
            this.changeRenderer(visibleCols[j].dataIndex, rendererArr[j]);
        }
    }
  });