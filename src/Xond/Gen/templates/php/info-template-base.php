<?php
namespace {{app_name}}\Info\base;
use Xond\Info\TableInfo;

/**
 * The Base TableInfo for {{php_name}} Table
 * 
 * @author Donny Fauzan <donny.fauzan@gmail.com>
 * @version $version$
 */
class Base{{php_name}}TableInfo extends TableInfo
{
    const CLASS_NAME = '{{app_name}}.Info.{{php_name}}TableInfo';

    public function __construct(){        
        parent::__construct();        
    }
    
    public function initialize()
    {
        $this->setName('{{name}}');
        $this->setPhpName('{{php_name}}');
        $this->setClassname('{{app_name}}\\Model\\{{php_name}}');
        $this->setPackage('{{app_name}}.Model');
    }
    
    public function setVariables() {
        
        $this->setPkName(            '{{pk_name}}');
        $this->setIsData(           {{is_data}});   
        $this->setCreateGrid(       {{create_grid}});
        $this->setCreateForm(       {{create_form}});
        $this->setRecordCount(      {{record_count}});
        $this->setIsRef(            {{is_ref}});
        $this->setIsStaticRef(      {{is_static_ref}});
        $this->setIsBigRef(         {{is_big_ref}});
        $this->setIsSmallRef(       {{is_small_ref}});
        $this->setIsCompositePk(    {{composite_pk}});
        $this->setDisplayField(     '{{display_field}}');
        $this->setRendererString(   '{{renderer_string}}');
        $this->setHeader(           '{{header}}');
        $this->setLabel(            '{{label}}');
        $this->setCreateCombobox(   {{create_combobox}});
        $this->setCreateRadiogroup( {{create_radiogroup}});
        $this->setCreateList(       {{create_list}});
        $this->setCreateModel(      {{create_model}});
        $this->setXtypeCombo(       '{{xtype_combo}}');
        $this->setXtypeRadio(       '{{xtype_radio}}');
        $this->setXtypeList(        '{{xtype_list}}');
        $this->setInfoBeforeDelete( '{{info_before_delete}}');
{% if has_many|sizeof >= 1 %}
        $this->setHasMany(array(    "{{has_many|join('","')|raw}}"));
{% endif %}
{% if belongs_to|sizeof >= 1 %}
        $this->setBelongsTo(array(  "{{belongs_to|join('","')|raw}}"));
{% endif %}
        $this->setIsSplitEntity(    {{is_split_entity}});
        $this->setHasSplitEntity(   {{has_split_entity}});
        $this->setSplitEntityName(  '{{split_entity_name}}');
{% if relating_columns|sizeof >= 1 %}
        $this->setRelatingColumns(array(    "{{relating_columns|join('","')|raw}}"));
{% endif %}
        $this->setFormDefaultLabelWidth({{form_default_label_width}});

{% for cvars in columns %}
        $cvar = new \Xond\Info\ColumnInfo();
        $cvar->setColumnName(       '{{cvars.column_name}}');
        $cvar->setColumnPhpName(    '{{cvars.column_php_name}}');
        $cvar->setType(             '{{cvars.type}}');
        $cvar->setIsPkUuid(         '{{cvars.is_pk_uuid}}');
		$cvar->setIsPk(             '{{cvars.is_pk}}');
        $cvar->setIsFk(             '{{cvars.is_fk}}');
        $cvar->setFkTableName(      '{{cvars.fk_table_name}}'); 
        $cvar->setMin(              {{cvars.min}});
        $cvar->setMax(              {{cvars.max}});
        $cvar->setHeader(           '{{cvars.header}}');
        $cvar->setLabel(            '{{cvars.label}}');
        $cvar->setInputLength(      {{cvars.input_length}});
        $cvar->setFieldWidth(       {{cvars.field_width}});
        $cvar->setColumnWidth(      {{cvars.column_width}});
        $cvar->setHideColumn(       {{cvars.hide_column}});
        $cvar->setXtype(            '{{cvars.xtype}}');
        $cvar->setComboXtype(       '{{cvars.combo_xtype}}');
        $cvar->setDisplayField(     '{{cvars.display_field}}');
        $cvar->setValidation(       {{cvars.validation}});
        $cvar->setAllowEmpty(       {{cvars.allow_empty}});
        $cvar->setDescription(      '{{cvars.description}}');
        $columns[] = $cvar;

{% endfor %}
        
        $this->setColumns($columns);        
        /*
        $this->setTitle( "Fieldset Name" );
        $this->setGroupId( 1 );
        $this->setParentGroupId( 0 );
        $this->setGroupingMethod( 'fieldset' );
        */
        
    }
    
}