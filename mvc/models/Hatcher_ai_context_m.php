<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Hatcher_ai_context_m extends MY_Model
{
    protected $_table_name = 'hatcher_ai_context';
    protected $_primary_key = 'hatcher_ai_context_id';
    protected $_primary_filter = 'intval';
    protected $_order_by = "hatcher_ai_context_id DESC";

    public function __construct()
    {
        parent::__construct();
    }

    public function get_by_founder($founderID)
    {
        return $this->get_single_hatcher_ai_context(['founder_id' => $founderID]);
    }

    public function get_hatcher_ai_context($array = NULL, $signal = FALSE)
    {
        return parent::get($array, $signal);
    }

    public function get_order_by_hatcher_ai_context($array = NULL)
    {
        return parent::get_order_by($array);
    }

    public function get_single_hatcher_ai_context($array)
    {
        return parent::get_single($array);
    }

    public function upsert_context($founderID, $data)
    {
        $existing = $this->get_by_founder($founderID);
        if (customCompute($existing)) {
            parent::update($data, $existing->{$this->_primary_key});
            return $existing->{$this->_primary_key};
        }
        $data['founder_id'] = $founderID;
        return parent::insert($data);
    }
}

