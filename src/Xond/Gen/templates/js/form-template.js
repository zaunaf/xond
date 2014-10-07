Ext.define('{{appName}}.view._components.form.{{table.getPhpName}}', {
    extend: 'Ext.form.Panel',
    alias: 'widget.{{table.getPhpName|lower}}form',
    bodyPadding: 10,
    defaults: {
        labelWidth: {{table.getFormDefaultLabelWidth}},
        anchor: '{{table.getFormDefaultAnchor}}'
    },
    initComponent: function() {
        
        var record = this.initialConfig.record ? this.initialConfig.record : false;
        if (record){
            this.listeners = {
                afterrender: function(form, options) {
                    form.loadRecord(record);
                }
            };
        }

        /*this.on('beforeadd', function(form, field){
            if (!field.allowBlank)
              field.labelSeparator += '<span style="color: rgb(255, 0, 0); padding-left: 2px;">*</span>';
        });*/
        
        this.items = [{
{# Start LOOP ALL COLUMNS #}
{% for col in columns %}
{#  Start Processing Column ONLY #}
{% if col|get_class == 'ColumnInfo' %}
{#      Column is ENUM VALUE #}
{% if col.getEnumValues %}
            xtype: 'radiogroup'
            ,fieldLabel: '{{col.getLabel}}'
            ,labelAlign: 'right'
            ,name: '{{col.getName}}_radiogroup'
{% if col.getAllowEmpty %}
            ,allowBlank: false
{% endif %}
{% if col.getDisabled == '1' %}
            ,disabled: true
{% endif %}
{% if col.getAllowEmpty == '1' %}
            ,labelSeparator: ':<span style="color: rgb(255, 0, 0); padding-left: 2px;">*</span>'
{% endif %}
{% if col.getColumnsRadio %}
            ,columns: {{col.getColumnsRadio}}
{% endif %}
            ,items: [
{% for key,value in col.getEnumValues %}
{% if col.getType == 'string' %}
                    { boxLabel: '{{value}}', name: '{{col.getName}}', inputValue: '{{key}}'} {{ loop.last ? '' : ',' }}
{% else %}
                    { boxLabel: '{{value}}', name: '{{col.getName}}', inputValue: {{key}} } {{ loop.last ? '' : ',' }}
{% endif %}
{% endfor %}
            ]
{#      END Column is ENUM VALUE #}
{% else %}
{#      START Column is NOT ENUM VALUE #}
            xtype: '{{col.getXtype}}'
            ,fieldLabel: '{{col.getLabel}}'
            ,labelAlign: 'right'
{% if col.getReadOnly %}
            ,readOnly: true
{% endif %}
{% if col.getAllowEmpty == '1' %}
            ,labelSeparator: ':<span style="color: rgb(255, 0, 0); padding-left: 2px;">*</span>'
            ,allowBlank: false
{% endif %}
{% if col.getMax and col.getXtype != 'datefield' %}
            ,maxValue: {{col.getMax}}
{% endif %}
{#              START Column is NOT DATEFIELD #}
{% if col.getXtype != 'datefield' %}
{% if col.getUseMinValue == '0' %}
{% else %}
            ,minValue: {{col.getMin ? col.getMin : 0}}
{% endif %}
{% endif %}
{#              END Column is NOT DATEFIELD #}
{% if col.getColumnsRadio %}
            ,columns: {{col.getColumnsRadio}}
{% endif %}
{% if col.getForceSelection %}
            ,forceSelection: true
{% endif %}
{% if col.getFormTextAlignRight %}
            ,fieldCls: 'a-form-num-field'
{% endif %}
{% if col.getDecimalPrecision %}
            ,decimalPrecision: '{{col.getDecimalPrecision}}'
{% endif %}
{% if col.getEditable == '1' %}
            ,editable: false
{% endif %}
{% if col.getDisabled == '1' %}
            ,disabled: true
{% endif %}
{% if col.getXtype == 'numberfield' %}
            ,hideTrigger:true
{% endif %}
{% if col.getXtype == 'datefield' %}
            ,format: 'd/m/Y'
            ,maxValue: new Date()
{% endif %}
{% if col.getMinLength %}
            ,minLength: {{col.getMinLength}}
{% endif %}
{% if col.getInputLength and 'combo' not in col.getXtype and 'radiogroup' not in col.getXtype %}
            ,maxLength: {{col.getInputLength}}
            ,enforceMaxLength: true
{% endif %}
{#              START Column is RADIOGROUP #}
{% if ('radiogroup' in col.getXtype) or (col.getEnumValues) %}
{#              END Column is NOT RADIOGROUP #}
{% else %}
{#              START Column is NOT RADIOGROUP #}
{% if col.getAnchor %}
            ,name: '{{col.getName}}'
            ,anchor: '{{col.getAnchor}}%'
{% else %}
            ,name: '{{col.getName}}'
{% endif %}
{#              END Column is NOT RADIOGROUP #}
{% endif %}
{#      END Column is NOT ENUM VALUE #}
{% endif %}
{#  END Processing Column ONLY #}
{#  START Processing NON ColumnInfo #}
{% else %}
            xtype: '{{col.getXtype}}'
{#      START Processing FIELDSET #}
{% if col.getXtype == 'fieldset' %}
            ,title: '{{col.getTitle}}'
            ,collapsible: true
            ,labelAlign: 'right'
            ,defaults: {
                labelWidth: 185
                ,anchor: '100%'
                ,margins: '0 0 0 5'
            }
{#      ELSE START Processing CONTAINER #}
{% elseif col.getXtype == 'container' %}
            ,anchor: '100%'
            ,layout: 'hbox'
            ,items: [{
{#      ELSE OTHERWISE #}
{% else %}
            ,fieldLabel: '{{col.getTitle}}'
            ,name: '{{col.getTitle}}'
            ,labelAlign: 'right'
{% endif %}
{#      END #}
{#      START Processing FIELDCONTAINER #}
{% if col.getXtype == 'fieldcontainer' %}
            ,layout: 'hbox'
            ,defaults: {
                margins: '0 0 0 5'
            }
{% endif %}
{#      END Processing FIELDCONTAINER #}
{#      START Processing CHECKBOXGROUP #}
{% if col.getXtype == 'checkboxgroup' %}
            ,columns: {{col.getColumnNumber}}
            ,items: [
{% for col1 in col.getColumns %}
                {boxLabel: '{{col1.getLabel}}', name: '{{col1.getName}}', inputValue: '1', uncheckedValue: '0'} {{ loop.last ? '' : ',' }}
{% endfor %}
            ]
{#      ELSE START Processing CONTAINER #}
{% elseif col.getXtype == 'container' %}
{#          START Loop each column #}
{% for col1 in col.getColumns %}
                xtype: 'container',
{% if col1.getFlex %}
                flex: {{col1.getFlex}},
{% endif %}
                layout: 'anchor',
                items: [{
                    xtype:'textfield',
                    fieldLabel: '{{col1.getLabel}}',
{% if col1.getLabelWidth %}
                    labelWidth: {{col1.getLabelWidth}},
{% endif %}
{% if col1.getAllowEmpty == '1' %}
                    labelSeparator: ':<span style="color: rgb(255, 0, 0); padding-left: 2px;">*</span>',
{% endif %}
                    allowBlank: false,
                    name: '{{col1.getName}}',
                    anchor:'95%',
                    value: ''
                }]
            {{ loop.last ? '}]' : '},{' }}
{% endfor %}
{#          STOP Loop each column #}
{% else %}
{#      ELSE START Processing NON CONTAINER #}
            ,items: [{
{#          START Loop each column #}
{% for col1 in col.getColumns %}
{#              START Processing ENUM VALUES, USE RADIO #}
{% if col1.getEnumValues %}
                xtype: 'radiogroup'
                ,fieldLabel: '{{col1.getLabel}}'
                ,labelAlign: 'right'
{% if col1.getAllowEmpty == '1' %}
                ,labelSeparator: ':<span style="color: rgb(255, 0, 0); padding-left: 2px;">*</span>'
{% endif %}
                ,name: '{{col1.getName}}_radiogroup'
{% if col1.getAllowEmpty %}
                ,allowBlank: false
{% endif %}
{% if col1.getDisabled == '1' %}
                ,disabled: true
                ,disabledCls: 'x-item-disabled'
{% endif %}
                ,items: [
{% for key,value in col1.getEnumValues %}
{% if col1.getType == 'string' %}
                    { boxLabel: '{{value}}', name: '{{col1.getName}}', inputValue: '{{key}}' } {{ loop.last ? '' : ',' }}
{% else %}
                    { boxLabel: '{{value}}', name: '{{col1.getName}}', inputValue: {{key}} } {{ loop.last ? '' : ',' }}
{% endif %}
{% endfor %}
{#              STOP Processing ENUM VALUES #}
{#              ELSE Processing NON ENUM VALUES #}
{% else %}
                xtype: '{{col1.getXtype}}'
{% if col1.getReadOnly %}
                ,readOnly: true
{% endif %}
{% if col1.getAllowEmpty %}
                ,allowBlank: false
{% endif %}
{% if col1.getXtype != 'datefield' %}
{% if col1.getUseMinValue == '0' %}
{% else %}
                ,minValue: {{col1.getMin ? col1.getMin : 0}}
{% endif %}
{% endif %}
                ,fieldLabel: '{{col1.getLabel}}'
{% if col1.getMax and col1.getXtype != 'datefield'%}
{% if col1.getMax != 99999999 %}
                ,maxValue: {{col1.getMax}}
{% endif %}
{% endif %}
{% if col1.getValidationType %}
                ,vtype: '{{col1.getValidationType}}'
{% endif %}
{% if col1.getColumnsRadio %}
                ,columns: {{col1.getColumnsRadio}}
{% endif %}
{% if col1.getForceSelection %}
                ,forceSelection: true
{% endif %}
{% if col1.getFormTextAlignRight %}
                ,fieldCls: 'a-form-num-field'
{% endif %}
{% if col1.getDecimalPrecision %}
                ,decimalPrecision: '{{col1.getDecimalPrecision}}'
{% endif %}
{% if col1.getEditable == '1' %}
                ,editable: false
{% endif %}
{% if col1.getDisabled == '1' %}
                ,disabled: true
                ,disabledCls: 'x-item-disabled'
{% endif %}
{% if col1.getLabelWidth %}
                ,labelWidth: '{{col1.getLabelWidth}}'
{% endif %}
{% if col1.getXtype == 'numberfield' %}
                ,hideTrigger:true
{% endif %}
{% if col1.getXtype == 'datefield' %}
                ,format: 'd/m/Y'
                ,maxValue: new Date()
{% endif %}
{% if col1.getMinLength %}
                ,minLength: {{col1.getMinLength}}
{% endif %}
{% if col1.getInputLength and 'combo' not in col1.getXtype and 'radiogroup' not in col1.getXtype %}
                ,maxLength: {{col1.getInputLength}}
                ,enforceMaxLength: true
{% endif %}
{% if col1.getAllowEmpty == '1' %}
                ,labelSeparator: ':<span style="color: rgb(255, 0, 0); padding-left: 2px;">*</span>'
{% endif %}
{% if col1.getFlex %}
                ,flex: {{col1.getFlex}}
{% endif %}
                ,labelAlign: 'right'
{% if loop.first %}
                ,margins: '0 0 0 0'
{% endif %}
{% endif %}
{#              END Processing NON ENUM VALUES #}
{% if ('radiogroup' in col1.getXtype) or (col1.getEnumValues) %}
{% else %}
{% if col1.getAnchor %}
                ,name: '{{col1.getName}}'
                ,anchor: '{{col1.getAnchor}}%'
{% else %}
                ,name: '{{col1.getName}}'
{% endif %}
{% endif %}
            {{ loop.last ? '' : '},{' }}
{% endfor %}
{#          END Loop each column #}
            }]
{% endif %}
{#      END Processing NON CONTAINER #}            
{% endif %}
{#  END Processing NON ColumnInfo #}
        {{ loop.last ? '' : '},{' }}
{% endfor %}
{# END LOOP ALL COLUMNS #}
        }];

        this.buttons = [{
            text: 'Save',
            iconCls: 'fa fa-save fa-inverse fa-lg',
            action: 'save'
        }];

        this.callParent(arguments);
    }
});