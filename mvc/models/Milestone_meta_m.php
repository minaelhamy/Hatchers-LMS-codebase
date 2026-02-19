<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Milestone_meta_m extends MY_Model
{
    protected $_table_name = 'milestone_meta';
    protected $_primary_key = 'milestone_meta_id';
    protected $_primary_filter = 'intval';
    protected $_order_by = "due_date ASC, milestone_meta_id DESC";

    public function __construct()
    {
        parent::__construct();
    }

    public function get_milestone_meta($array = NULL, $signal = FALSE)
    {
        return parent::get($array, $signal);
    }

    public function get_order_by_milestone_meta($array = NULL)
    {
        return parent::get_order_by($array);
    }

    public function get_single_milestone_meta($array)
    {
        return parent::get_single($array);
    }

    public function insert_milestone_meta($array)
    {
        parent::insert($array);
        return TRUE;
    }

    public function update_milestone_meta($data, $id = NULL)
    {
        parent::update($data, $id);
        return $id;
    }

    public function delete_milestone_meta($id)
    {
        parent::delete($id);
    }
}

