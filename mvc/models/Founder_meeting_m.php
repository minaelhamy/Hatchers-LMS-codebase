<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Founder_meeting_m extends MY_Model
{
    protected $_table_name = 'founder_meetings';
    protected $_primary_key = 'founder_meeting_id';
    protected $_primary_filter = 'intval';
    protected $_order_by = "starts_at ASC, founder_meeting_id DESC";

    public function __construct()
    {
        parent::__construct();
    }

    public function get_founder_meeting($array = NULL, $signal = FALSE)
    {
        return parent::get($array, $signal);
    }

    public function get_order_by_founder_meeting($array = NULL)
    {
        return parent::get_order_by($array);
    }

    public function get_single_founder_meeting($array)
    {
        return parent::get_single($array);
    }

    public function insert_founder_meeting($array)
    {
        parent::insert($array);
        return TRUE;
    }

    public function update_founder_meeting($data, $id = NULL)
    {
        parent::update($data, $id);
        return $id;
    }

    public function delete_founder_meeting($id)
    {
        parent::delete($id);
    }
}

