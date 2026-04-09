<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Learning_library_m extends MY_Model
{
    protected $_table_name = 'learning_library';
    protected $_primary_key = 'learning_library_id';
    protected $_primary_filter = 'intval';
    protected $_order_by = 'learning_library_id DESC';

    public function __construct()
    {
        parent::__construct();
    }

    public function get_learning_library($array = NULL, $signal = FALSE)
    {
        return parent::get($array, $signal);
    }

    public function get_order_by_learning_library($array = NULL)
    {
        return parent::get_order_by($array);
    }

    public function get_single_learning_library($array)
    {
        return parent::get_single($array);
    }

    public function insert_learning_library($array)
    {
        return parent::insert($array);
    }

    public function update_learning_library($data, $id = NULL)
    {
        parent::update($data, $id);
        return $id;
    }

    public function delete_learning_library($id)
    {
        parent::delete($id);
    }
}
