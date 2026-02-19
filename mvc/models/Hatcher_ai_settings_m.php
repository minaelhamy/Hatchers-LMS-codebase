<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Hatcher_ai_settings_m extends MY_Model
{
    protected $_table_name = 'hatcher_ai_settings';
    protected $_primary_key = 'hatcher_ai_settings_id';
    protected $_primary_filter = 'intval';
    protected $_order_by = "hatcher_ai_settings_id DESC";

    public function __construct()
    {
        parent::__construct();
    }

    public function get_latest_settings()
    {
        $this->db->order_by($this->_primary_key, 'DESC');
        $query = $this->db->get($this->_table_name, 1);
        return $query->row();
    }

    public function upsert_settings($data)
    {
        $existing = $this->get_latest_settings();
        if (customCompute($existing)) {
            parent::update($data, $existing->{$this->_primary_key});
            return $existing->{$this->_primary_key};
        }
        return parent::insert($data);
    }
}

