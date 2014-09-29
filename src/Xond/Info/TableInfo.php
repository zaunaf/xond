<?php

namespace Xond\Info;

class TableInfo
{
    protected $name;
    protected $php_name;
    protected $pk_name;

    public $is_data;			// Merupakan tabel data,
    public $create_grid;		// maka perlu gak dibuat gridnya
    public $create_form;        // maka perlu gak dibuat formnya

    public $is_ref;             // Merupakan tabel referensi
    public $is_static_ref;      // Jangan load ajax utk storenya, bikin static aja
    public $is_big_ref;         // Paging combonya
    public $is_small_ref;       // Radio nya
    public $composite_pk;		// Table ini pake composite PK ndak

    public $display_field;
    public $renderer_string;

    public $create_combobox;    // Buat combobox nya
    public $create_radiogroup;  // Buat radiogroup nya
    public $create_list;        // Buat list nya
    public $create_model;       // Buat modelnya secara khusus
    public $xtype_combo;        // Nama xtype nya kalau jadi combo
    public $xtype_radio;        // Nama xtype nya kalau jadi radio
    public $xtype_list;         // Nama xtype nya kalau jadi list
    public $has_many;           // Otomatis tarik dari referrer (tabel yg merefer)
    public $belongs_to;         // Otomatis tarik dari referensi/foreign key yang ada

    public $is_split_entity;    // Merupakan entitas yg displit, dilepaskan sebagian, disisakan kolom yang infrequent change
    public $has_split_entity;   // Merupakan entitas yg displit, dilepaskan dari main entity karena frequent change
    public $split_entity_name;  // Kalau merupakan main entity yang displit, potongannya apa ..

    public $infoBeforeDelete;	// Pesen info sebelum menghapus record

    public function __construct(){
        $this->initialize();
        $this->setVariables();
    }

    public function initialize() {
        //to be overridden
    }

    public function setVariables() {
        //to be overridden
    }

    function getPkName() {
        return $this->pk_name;
    }

    function setPkName($pk_name) {
        $this->pk_name = $pk_name;
    }

    function getName() {
        return $this->name;
    }

    function setName($name) {
        $this->name = $name;
    }

    function getPhpName() {
        return $this->php_name;
    }

    function setPhpName($name) {
        $this->php_name = $name;
    }

    function getClassname() {
        return $this->class_name;
    }

    function setClassname($name) {
        $this->class_name = $name;
    }

    function getPackage() {
        return $this->package;
    }

    function setPackage($name) {
        $this->package = $name;
    }

    function getIsData() {
        return $this->is_data;
    }

    function setIsData($is_data) {
        $this->is_data = $is_data;
    }

    function getCreateGrid() {
        return $this->create_grid;
    }

    function setCreateGrid($create_grid) {
        $this->create_grid = $create_grid;
    }

    function getCreateForm() {
        return $this->create_form;
    }

    function setCreateForm($create_form) {
        $this->create_form = $create_form;
    }

    function getIsRef() {
        return $this->is_ref;
    }

    function setIsRef($is_ref) {
        $this->is_ref = $is_ref;
    }

    function getIsStaticRef() {
        return $this->is_static_ref;
    }

    function setIsStaticRef($is_static_ref) {
        $this->is_static_ref = $is_static_ref;
    }

    function getIsBigRef() {
        return $this->is_big_ref;
    }

    function setIsBigRef($is_big_ref) {
        $this->is_big_ref = $is_big_ref;
    }

    function getIsSmallRef() {
        return $this->is_small_ref;
    }

    function setIsSmallRef($is_small_ref) {
        $this->is_small_ref = $is_small_ref;
    }

    function getIsCompositePk() {
        return $this->composite_pk;
    }

    function setIsCompositePk($composite_pk) {
        $this->composite_pk = $composite_pk;
    }

    function setDisplayField($displayField) {
        $this->display_field = $displayField;
    }

    function getDisplayField() {
        return $this->display_field;
    }

    function setRendererString($rendererString) {
        $this->renderer_string = $rendererString;
    }

    function getRendererString() {
        return $this->renderer_string;
    }

    function setLabel($label) {
        $this->label = $label;
    }

    function getLabel() {
        return $this->label;
    }

    function setHeader($header) {
        $this->header = $header;
    }

    function getHeader() {
        return $this->header;
    }

    function getCreateCombobox() {
        return $this->create_combobox;
    }

    function setCreateCombobox($create_combobox) {
        $this->create_combobox = $create_combobox;
    }

    function getCreateRadiogroup() {
        return $this->create_radiogroup;
    }

    function setCreateRadiogroup($create_radiogroup) {
        $this->create_radiogroup = $create_radiogroup;
    }

    function getCreateList() {
        return $this->create_list;
    }

    function setCreateList($create_list) {
        $this->create_list = $create_list;
    }

    function getCreateModel() {
        return $this->create_model;
    }

    function setCreateModel($create_model) {
        $this->create_model = $create_model;
    }

    function getXtypeCombo() {
        return $this->xtype_combo;
    }

    function setXtypeCombo($xtype_combo) {
        $this->xtype_combo = $xtype_combo;
    }

    function getXtypeRadio() {
        return $this->xtype_radio;
    }

    function setXtypeRadio($xtype_radio) {
        $this->xtype_radio = $xtype_radio;
    }

    function getXtypeList() {
        return $this->xtype_list;
    }

    function setXtypeList($xtype_list) {
        $this->xtype_list = $xtype_list;
    }

    function getHasMany() {
        return $this->has_many;
    }

    function setHasMany($has_many) {
        $this->has_many = $has_many;
    }

    function getBelongsTo() {
        return $this->belongs_to;
    }

    function setBelongsTo($belongs_to) {
        $this->belongs_to = $belongs_to;
    }

    function getIsSplitEntity() {
        return $this->is_split_entity;
    }

    function setIsSplitEntity($is_split_entity=false) {
        $this->is_split_entity = $is_split_entity;
    }

    function getHasSplitEntity() {
        return $this->has_split_entity;
    }

    function setHasSplitEntity($has_split_entity=false) {
        $this->has_split_entity = $has_split_entity;
    }

    function getSplitEntityName() {
        return $this->split_entity_name;
    }

    function getSplitEntityPhpName() {
        return phpnamize($this->split_entity_name);
    }

    function setSplitEntityName($split_entity_name) {
        $this->split_entity_name = $split_entity_name;
    }

    function getColumns() {
        return $this->columns;
    }

    function getRelatingColumns() {
        return $this->relating_columns;
    }

    function setRelatingColumns($relating_columns) {
        $this->relating_columns = $relating_columns;
    }

    function getInfoBeforeDelete() {
        return $this->info_before_delete;
    }

    function setInfoBeforeDelete($infoBeforeDelete) {
        $this->info_before_delete = $infoBeforeDelete;
    }

    /*
     * Memasukkan array columns pada tableinfo
    *
    * <p>Memasukkan array columns pada tableinfo</p>
    *
    * @param array $cols Array of ColumnInfos
    */
    function setColumns($cols) {
        $this->columns = $cols;
    }

    /*
     * Mengambil column utk dioverride menggunakan nama kolom sbg parameter
    *
    * <p>Mengambil column utk dioverride menggunakan nama kolom sbg parameter</p>
    *
    * @param string $name Nama kolom yang dicari
    * @return Xond\Info\ColumnInfo Kolom yang dimaksud
    */
    function getColumnByName($name) {
        $cols = $this->getColumns();
        foreach($cols as $c) {
            //$c = new ColumnInfo();
            if ($c->getColumnName() == $name) {
                $retObj = $c;
                break;
            }
        }
        if (@is_object($retObj)) {
            return $retObj;
        } else {
            return false;
        }
    }

    /*
     * Menambahkan range of columns ke suatu group
    *
    * <p>Menambahkan range of columns ke suatu group
    * entah itu checkboxgroup, fieldset, atau fieldgroup
    * Add range juga otomatis meng-add group yang sudah ditambah
    * ke dalam list of groups. Add range ditulis
    * di object [TableName]TableInfo yang sudah digenerate.
    * Contoh penggunaan: </p>
    *
    * <code>
    *  $fieldgroup1 = new FieldgroupInfo();
    *  $this->addRange('tempat_lahir', 'tanggal_lahir', $fieldgroup1);
    *  $this->addGroup($fieldgroup1);
    *
    *  $fieldset1 = new FieldsetInfo();
    *	$this->addRange('nama_ayah', 'kebutuhan_khusus_id_ayah', $fieldset1);
    *	$this->addGroup($fieldset1);
    * </code>
    *
    * @param string $startColumnName Nama kolom mulai masuk group
    * @param string $endColumnName Nama kolom terakhir masuk group
    * @param mixed $group FieldsetInfo, FieldgroupInfo, atau CheckboxGroup yang ditambahkan
    *
    * @return void
    */
    public function addRange($startColumnName, $endColumnName, $group, $title='') {
        	
        $cols = $this->getColumns();
        $startAddingColumn = false;
        	
        $counter = 0;
        	
        foreach($cols as $col) {

            //$col = new ColumnInfo();
            //$group = new FieldgroupInfo();
            if ($counter == 0) {
                $group->setTitle($title);
            }

            if ($col->getColumnName() == $startColumnName) {
                	
                // Use first column's label as title otherwise
                $title = ($title != '') ? $title : humanize($col->getPhpName());
                	
                // If it's a field container, prevent double labelling
                if ($group->getXtype() == 'fieldcontainer') {
                    $col->setLabel('');
                }
                $startAddingColumn = true;
            }

            if ($startAddingColumn) {
                $group->addColumn($col);
            }
            	
            if ($col->getColumnName() == $endColumnName) {
                break;
            }
        }

        $this->addGroup($group);
        	
    }

    /*
     * Menambahkan suatu group ke dalam tableinfo
    *
    * Menambahkan suatu group ke dalam tableinfo
    * entah itu checkboxgroup, fieldset, atau fieldgroup
    *
    * @param mixed $group Object dalam bentuk FieldsetInfo, FieldgroupInfo, atau CheckboxGroup
    *
    * @return void
    */
    function addGroup($group){
        	
        if (!is_object($group)) {
            throw new Exception('Group yang ditambahkan harus dalam bentuk object!');
        }
        $this->groups[] = $group;
        	
    }

    /*
     * Mengambil daftar group
    *
    * Mengambil daftar group
    * entah itu checkboxgroup, fieldset, atau fieldgroup
    *
    * @return array Array of groups
    */
    function getGroups(){
        if (@$this->groups) {
            return $this->groups;
        } else {
            return false;
        }
    }

    function getPkColumnInfo(){
        return $this->getColumnByName($this->getPkName());
    }

    /*
     * Set column untuk ditaruh di index tertentu
    *
    * <p>Set column untuk ditaruh di index tertentu</p>
    *
    * @param ColumnInfo $movedColumn	Obyek ColumnInfo yang dipindah
    * @param integer 	$index 			Index di mana kolom akan diinsert
    * @return void
    */
    function moveColumn($movedColumn, $index) {
        	
        $columns = $this->getColumns();
        	
        $i = 0;
        $outColumns = array();
        	
        foreach ($columns as $c) {

            if ($movedColumn->getColumnName() == $c->getColumnName()) {
                	
                // skip, because it's already pushed to the stack

            } else {
                	
                // Check the index, if the slot is visible than insert the column to the stack
                if ($index == $i) {
                    // Push the column to the stack first
                    $outColumns[] = $movedColumn;

                    // Then move the index
                    $i++;

                    // Then add the column below
                    $outColumns[] = $c;
                }
                // Otherwise, just add the current column
                else
                {
                    $outColumns[] = $c;
                }
            }

            // Add the counter no matter what happens upstairs
            $i++;
        }
        	
        //print_r($this->getColumns());
        //print_r($outColumns);
        //die;
        $this->setColumns($outColumns);
        	
    }

    /*
     * Set column untuk ditaruh di atas column tertentu
    *
    * <p>Set column untuk ditaruh di atas column tertentu</p>
    *
    * @param ColumnInfo $movedColumn			Obyek ColumnInfo yang dipindah
    * @param ColumnInfo $correspondingColumn	Obyek ColumnInfo target
    * @return void
    */
    function moveColumnAbove($movedColumn, $correspondingColumn) {

        $columns = $this->getColumns();

        $i = 0;
        $outColumns = array();

        foreach ($columns as $c) {

            if ($movedColumn->getColumnName() == $c->getColumnName()) {

                // skip, because it's already pushed to the stack

            } else {

                // Check the current column, if it is the target than insert the column above it
                if ($c->getColumnName() == $correspondingColumn->getColumnName()) {

                    // Push the column to the stack first
                    $outColumns[] = $movedColumn;

                    // Then move the index
                    $i++;

                    // Then add the column below
                    $outColumns[] = $c;
                }
                // Otherwise, just add the current column
                else
                {
                    $outColumns[] = $c;
                }
            }

            // Add the counter no matter what happens upstairs
            $i++;
        }

        //print_r($this->getColumns());
        //print_r($outColumns);
        //die;
        $this->setColumns($outColumns);

    }

    /*
     * Set column untuk ditaruh di bawah column tertentu
    *
    * <p>Set column untuk ditaruh di bawah column tertentu</p>
    *
    * @param ColumnInfo $movedColumn			Obyek ColumnInfo yang dipindah
    * @param ColumnInfo $correspondingColumn	Obyek ColumnInfo target
    * @return void
    */
    function moveColumnBelow($movedColumn, $correspondingColumn) {

        $columns = $this->getColumns();

        $i = 0;
        $outColumns = array();

        foreach ($columns as $c) {

            if ($movedColumn->getColumnName() == $c->getColumnName()) {

                // skip, because it's already pushed to the stack

            } else {

                // Check the current column, if it is the target than insert the column above it
                if ($c->getColumnName() == $correspondingColumn->getColumnName()) {

                    // Then add the column below
                    $outColumns[] = $c;

                    // Then move the index
                    $i++;

                    // Push the column to the stack first
                    $outColumns[] = $movedColumn;

                }
                // Otherwise, just add the current column
                else
                {
                    $outColumns[] = $c;
                }
            }

            // Add the counter no matter what happens upstairs
            $i++;
        }

        //print_r($this->getColumns());
        //print_r($outColumns);
        //die;
        $this->setColumns($outColumns);

    }

}