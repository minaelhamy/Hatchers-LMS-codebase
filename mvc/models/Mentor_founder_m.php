<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Mentor_founder_m extends MY_Model
{
    protected $_table_name = 'mentor_founder';
    protected $_primary_key = 'mentor_founder_id';
    protected $_primary_filter = 'intval';
    protected $_order_by = "mentor_founder_id DESC";

    public function __construct()
    {
        parent::__construct();
    }

    public function get_mentor_founder($array = NULL, $signal = FALSE)
    {
        return parent::get($array, $signal);
    }

    public function get_order_by_mentor_founder($array = NULL)
    {
        return parent::get_order_by($array);
    }

    public function get_single_mentor_founder($array)
    {
        return parent::get_single($array);
    }

    public function insert_mentor_founder($array)
    {
        parent::insert($array);
        return TRUE;
    }

    public function update_mentor_founder($data, $id = NULL)
    {
        parent::update($data, $id);
        return $id;
    }

    public function delete_mentor_founder($id)
    {
        parent::delete($id);
    }
}

