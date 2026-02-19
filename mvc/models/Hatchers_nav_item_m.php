<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Hatchers_nav_item_m extends MY_Model
{
    protected $_table_name = 'hatchers_nav_items';
    protected $_primary_key = 'hatchers_nav_item_id';
    protected $_primary_filter = 'intval';
    protected $_order_by = "sort_order ASC, hatchers_nav_item_id ASC";

    public function __construct()
    {
        parent::__construct();
    }

    public function get_hatchers_nav_item($array = NULL, $signal = FALSE)
    {
        return parent::get($array, $signal);
    }

    public function get_order_by_hatchers_nav_item($array = NULL)
    {
        return parent::get_order_by($array);
    }

    public function get_single_hatchers_nav_item($array)
    {
        return parent::get_single($array);
    }

    public function insert_hatchers_nav_item($array)
    {
        parent::insert($array);
        return TRUE;
    }

    public function update_hatchers_nav_item($data, $id = NULL)
    {
        parent::update($data, $id);
        return $id;
    }

    public function delete_hatchers_nav_item($id)
    {
        parent::delete($id);
    }
}

