<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Hustler_investor_m extends MY_Model
{
    protected $_table_name = 'hustler_investors';
    protected $_primary_key = 'hustler_investor_id';
    protected $_primary_filter = 'intval';
    protected $_order_by = 'hustler_investor_id DESC';

    public function __construct()
    {
        parent::__construct();
    }

    public function get_single_hustler_investor($array)
    {
        return parent::get_single($array);
    }

    public function insert_hustler_investor($array)
    {
        return parent::insert($array);
    }

    public function update_hustler_investor($data, $id = null)
    {
        parent::update($data, $id);
        return $id;
    }
}
