<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Founder_task_m extends MY_Model
{
    protected $_table_name = 'founder_tasks';
    protected $_primary_key = 'founder_task_id';
    protected $_primary_filter = 'intval';
    protected $_order_by = "due_date ASC, founder_task_id DESC";

    public function __construct()
    {
        parent::__construct();
    }

    public function get_founder_task($array = NULL, $signal = FALSE)
    {
        return parent::get($array, $signal);
    }

    public function get_order_by_founder_task($array = NULL)
    {
        return parent::get_order_by($array);
    }

    public function get_single_founder_task($array)
    {
        return parent::get_single($array);
    }

    public function insert_founder_task($array)
    {
        parent::insert($array);
        return TRUE;
    }

    public function update_founder_task($data, $id = NULL)
    {
        parent::update($data, $id);
        return $id;
    }

    public function delete_founder_task($id)
    {
        parent::delete($id);
    }
}

