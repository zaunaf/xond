<?php

namespace Xond\Info;

class ColumnInfo 
{
    public $column_name;		// jelas. contoh: status_kepegawaian_id
    public $column_php_name;	// jelas. contoh: StatusKepegawaianId
    public $display_field;		// display field lah pokona
    public $type;				// Type, postprocessed earlier from propel types
    public $is_fk;				// Is it an FK ?
    public $fk_table_name;		// Is it an FK ?
    public $min;				// Nilai minimal
    public $max;				// Nilai maximal (kalau tidak ada, ambil dari nilai terbesar foreign key)
    public $label;				// Label (form)
    public $header;				// Header (grid)
    public $label_width;		// Label (form)
    public $input_length;		// Default filled by column size
    public $column_width; 		// If not anchored 100%, how big normally is
    public $hide_column; 		// Whether to hide the column    
    public $flex;				// Flex (form)
    public $xtype;				// Default filled by foreign key table's combo, atau tipe datanya (numeric ya numberfield, date ya datefield)
    public $combo_xtype;		// Kalau referensi, combo xtype untuk di grid nya apa
    public $validation; 		// (belum kebayang)
    public $allow_empty;		// Protect NOT NULL
    public $description;		// Info nya gimana
    public $linked_with;		// If it's a combo, who linked it from
    public $linked_to;			// If it's a combo, who linked it to
    public $enum_values;		// If it's a combo, who linked it to
    public $anchor;				// anchor to length field on form  
    public $editable;			// jelas
    public $disabled;			// jelas
    public $columns_radio;		// for column radio button
    public $decimal_precision;	// jelas
    public $form_text_align_right; // utk form, agar value rata kanan, sample: lintang dan bujur
	public $use_min_value;		// use minValue (BOOLEAN)
    public $min_length;			// minLength
    public $validation_type;	// validation type, ex: email
    public $force_selection;    // Force selection on combos
    public $force_election;		// true to restrict the selected value to one of the values in the list, false to allow the user to set arbitrary text into the field
    public $read_only;			// readonly
	
    function getName() {
    	return $this->column_name;
    }    

    /*
     * Set Column Name
    *
    * <p>Set Column Name ini FieldName format</p>
    *
    * @param string $column_name Column name
    *
    * @return void
    */
    
    function setName($column_name) {
    	$this->column_name = $column_name;
    }
    

    /*
     * Set Column Name
    *
    * <p>Set Column Name ini PhpName format</p>
    *
    * @param string $column_name Column name
    *
    * @return void
    */
    
    function setPhpName($column_php_name) {
    	$this->column_php_name = $column_php_name;
    }    
    
    function getPhpName() {
    	return $this->column_php_name;
    }

    
    /*
     * Set Column Name
     *
     * <p>Set Column Name ini FieldName format (this one only an alias)</p>
     *
     * @param string $column_name Column name
     *
     * @return void
     */    
    function setColumnName($column_name) {
    	$this->column_name = $column_name;
    }
    
    function getColumnName() {
    	return $this->column_name;
    }
    

    /*
     * Set Column Name
     *
     * <p>Set Column Name ini PhpName format</p>
     *
     * @param string $column_name Column name
     *
     * @return void
     */    
    function setColumnPhpName($column_php_name) {
    	$this->column_php_name = $column_php_name;
    }    
    
    function getColumnPhpName() {
    	return $this->column_php_name;
    }
    
    /*
     * Set Column Value Type
     *
     * <p>Set Column Value Type</p>
     *
     * @param string $type Value Type
     *
     * @return void
     */
    function setType($type) {
    	$this->type = $type;
    }
    
    function getType() {
    	return $this->type;
    }
    
    /*
     * Set whether this is a PK in UUID format
     *
     * <p>Set Whether this is a PK in UUID format</p>
     *
     * @param string $is_pk_uuid Is this PK an UUID one
     *
     * @return void
     */
    
    function setIsPkUuid($is_pk_uuid) {
    	$this->is_pk_uuid = $is_pk_uuid;
    }    
    
    function getIsPkUuid() {
    	return $this->is_pk_uuid;
    }
    

    /*
     * Set whether this col is a PK
    *
    * <p>Set whether this col is a PK</p>
    *
    * @param string $is_pk Is this PK 
    *
    * @return void
    */
    
    function setIsPk($is_pk) {
    	$this->is_pk = $is_pk;
    }    
    
    function getIsPk() {
    	return $this->is_pk;
    }
    
    /**
     * Set whether this col is a FK
     *
     * <p>Set whether this col is a FK</p>
     *
     * @param string $is_fk Is this FK
     *
     * @return void
     */    
    function setIsFk($is_fk) {
    	$this->is_fk = $is_fk;
    }
    
    function getIsFk() {
    	return $this->is_fk;
    }

    function setIsTimestamp($is_timestamp) {
    	$this->is_timestamp = $is_timestamp;
    }
    
    function getIsTimestamp() {
    	return $this->is_timestamp;
    }
    
    
    /**
     * Set the table name of related FK
     *
     * <p>Set the table name of related FK</p>
     *
     * @param string $fk_table_name FK Table Name
     *
     * @return void
     */
    
    function setFkTableName($fk_table_name) {
    	$this->fk_table_name = $fk_table_name;
    }
         
    function getFkTableName() {
    	return $this->fk_table_name;
    }

    /**
     * Used when needed to create fk table name but in ClassName format
     * 
     * @return string
     */
    function getFkTablePhpName() {
        return (string) phpNamize($this->fk_table_name);
    }
    
    /**
     * Set the Display Field of this FK
     *
     * <p>Set the name of the column for display on the relating table of the FK</p>
     *
     * @param string $display_field FK Display Field Name
     *
     * @return void
     */    
    function setDisplayField($display_field) {
    	$this->display_field = $display_field;
    }
    
    function getDisplayField() {
    	return $this->display_field;
    }
    
    /**
     * Set the minimum value of the FK
     *
     * <p>Set the minimum value of the FK if it's a numeric FK</p>
     *
     * @param integer $min Minimum value
     * 
     *  @return void
     */    
    function setMin($min) {
    	$this->min = $min;
    }
    
    function getMin() {
    	return $this->min;
    }
    
    /**
     * Set the maximum value of the FK
     *
     * <p>Set the maximum value of the FK if it's a numeric FK</p>
     *
     * @param integer $max maximum value
     *
     * @return void
     */    
    function setMax($max) {
    	$this->max = $max;
    }
    
    function getMax() {
    	return $this->max;
    }
    
    /*
     * Set label for this field
    *
    * <p>Set label for this field</p>
    *
    * @param string $label Label
    *
    * @return void
    */    
    function setLabel($label) {
    	$this->label = $label;
    }
    
    function getLabel() {
    	return $this->label;
    }
    
    /*
     * Set header for this field
    *
    * <p>Set header for this field</p>
    *
    * @param string $header Header
    *
    * @return void
    */    
    function setHeader($header) {
    	$this->header = $header;
    }
    
    function getHeader() {
    	return $this->header;
    }
    
    /*
     * Set the input length of the field
     *
     * <p>Set the input length of the field</p>
     *
     * @param integer $input_length Input length
     *
     * @return void
     */    
    function setInputLength($input_length) {
    	$this->input_length = $input_length;
    }
    
    function getInputLength() {
    	return $this->input_length;
    }
    
    /*
     * Set label width for this field
    *
    * <p>Set label width for this field</p>
    *
    * @param integer $label_width Label Width
    *
    * @return void
    */    
    function setLabelWidth($label_width) {
    	$this->label_width = $label_width;
    }
    
    function getLabelWidth() {
    	return isset($this->label_width) ? $this->label_width : false;
    }
    
    /*
     * Set flex point
    *
    * <p>Set flex point which is used in hbox style
    * fieldgroups. Flex give this field relative width
    * of this field compared to other fields</p>
    *
    * @param integer $flex Flex number 
    *
    * @return void
    */    
    function setFlex($flex) {
    	$this->flex = $flex;
    }

    function getFlex() {
    	return isset($this->flex) ? $this->flex : false;
    }
    
    /*
     * Set field width for this field
    *
    * <p>Set field width for this field</p>
    *
    * @param integer $field_width Field Width in Form format
    *
    * @return void
    */    
    function setFieldWidth($field_width) {
    	$this->field_width = $field_width;
    }
    
    function getFieldWidth() {
    	return $this->field_width;
    }
    
    /*
     * Set column width for this column
    *
    * <p>Set column width for this column if this field 
    * put inside a grid</p>
    *
    * @param integer $column_width Column Width in Grid format
    *
    * @return void
    */    
    function setColumnWidth($column_width) {
    	$this->column_width = $column_width;
    }
    
    function getColumnWidth() {
    	return $this->column_width;
    }

    /*
     * Set whether to hide this column
    *
    * <p>Set whether to hide this column </p>
    *
    * @param integer $hide_column Column Width in Grid format
    *
    * @return void
    */
    function setHideColumn($hide_column) {
    	$this->hide_column = $hide_column;
    }
    
    function getHideColumn() {
    	return $this->hide_column;
    }
    
    /*
     * Set xtype alias
    *
    * <p>Set the alias of this component to be called with "xtype: " config.</p>
    *
    * @param string $xtype Column Width in Grid format
    *
    * @return void
    */    
    function setXtype($xtype="") {
    	$this->xtype = $xtype;
    }
    
    function getXtype() {
    	return $this->xtype;
    }
    
    function setComboXtype($xtype="") {
    	$this->combo_xtype = $xtype;
    }
    
    function getComboXtype() {
    	return $this->combo_xtype;
    }
    
    function setValidation($validation="") {
    	$this->validation = $validation;
    }
    
    function getValidation() {
    	return $this->validation;
    }
    
    /*
     * Set whether this column is allowed to be empty
    *
    * <p>Set whether this column is allowed to be empty or "NULL" value</p>
    *
    * @param boolean $allow_empty Allow empty 
    *
    * @return void
    */    
    function setAllowEmpty($allow_empty=true) {
    	$this->allow_empty = $allow_empty;
    }
    
    function getAllowEmpty() {
    	return $this->allow_empty;
    }
    
    
    /*
     * Set Description for this field
     *
     * <p>Set a long narrative description for this field. 
     * May be called later in tooltips format</p>
     *
     * @param string $description Description
     *
     * @return void
     */    
    function setDescription($description="") {
    	$this->description = $description;
    }
    
    function getDescription() {
    	return $this->description;
    }
    
    function setLinkedWith($linked_with) {
    	$this->linked_with = $linked_with;
    }
    
    function getLinkedWith() {
    	return $this->linked_with;
    }
    
    function setLinkedTo($linked_to) {
    	$this->linked_to = $linked_to;
    }
    
    function getLinkedTo() {
    	return $this->linked_to;
    }
    
    /*
     * Set Enum Values
     * 
     * <p>Set Enum Values, in associative array format
     * </p>
     * <code>
     * $array = array("L"=> "Laki-laki" , "P" => "Perempuan");
     * $col->setEnumValues($array);
     * </code>
     * 
     * @param array $array Associative array
     * 
     * @return void
     */
    function setEnumValues($array) {
    	$this->enum_values = $array;    	
    }
    
    /*
     * Get Enum Values
     *
     * <p>Get Enum Values, in associative array format</p>
     * 
     * @return array
     */
    function getEnumValues() {
    	return ($this->enum_values) ? $this->enum_values : false;
    }
    
    function setAnchor($anchor) {
    	$this->anchor = $anchor;
    }
    
    function getAnchor() {
    	return $this->anchor;
    }
    
    function setEditable($editable) {
    	$this->editable = $editable;
    }
    
    function getEditable() {
    	return $this->editable;
    }
    
    function setDisabled($disabled) {
    	$this->disabled = $disabled;
    }
    
    function getDisabled() {
    	return $this->disabled;
    }
    
    function setColumnsRadio($columns_radio) {
    	$this->columns_radio = $columns_radio;
    }
    
    function getColumnsRadio() {
    	return $this->columns_radio;
    }

    function setDecimalPrecision($decimal_precision) {
    	$this->decimal_precision = $decimal_precision;
    }
    
    function getDecimalPrecision() {
    	return $this->decimal_precision;
    }
    
    function setFormTextAlignRight($form_text_align_right) {
    	$this->form_text_align_right = $form_text_align_right;
    }    
    
    function getFormTextAlignRight() {
    	return $this->form_text_align_right;
    }
    
    function setMinLength($min_length) {
    	$this->min_length = $min_length;
    }
    
    function getMinLength() {
    	return $this->min_length;
    }
    
    function setValidationType($validation_type) {
    	$this->validation_type = $validation_type;
    }
    
    function getValidationType() {
    	return $this->validation_type;
    }
    
    function setForceSelection($force_selection) {
    	$this->force_selection = $force_selection;
    }
    
    function getForceSelection() {
    	return $this->force_selection;
    }
    
	function setUseMinValue($use_min_value) {
    	$this->use_min_value = $use_min_value;
    }
    
    function getUseMinValue() {
    	return $this->use_min_value;
    }
    
    function setReadOnly($read_only) {
    	$this->read_only = $read_only;
    }
    
    function getReadOnly() {
    	return $this->read_only;
    }
    
    /*
     * Set initial values
     * 
     * <p>Set initial values for "new Record"</p>
     * 
     * @param mixed $value
     * 
     * @return void
     */
    function setInitialValue($value) {
    	$this->initial_value = $value;    	
    }
    
    /*
     * Return Initial Values
     *
     * <p>Get Initial Values, in any format</p>
     *
     * @return mixed
     */
    function getInitialValue() {
    	if (isset($this->initial_value)) {
    		return $this->initial_value;
    	} else {
    		return NULL;
    	}
    }
    
    function getFkTableInfo() {    	 
    	$key = APPNAME."\\Info\\".phpnamize($this->getFkTableName())."TableInfo";
    	return new ${'key'};
    }
    
}