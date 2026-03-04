<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Hustler_profile_m extends MY_Model
{
    protected $_table_name = 'hustler_founder_profiles';
    protected $_primary_key = 'hustler_founder_profile_id';
    protected $_primary_filter = 'intval';
    protected $_order_by = 'hustler_founder_profile_id DESC';

    public function __construct()
    {
        parent::__construct();
    }

    public function get_by_investor($investorID)
    {
        return $this->get_single_hustler_profile(['hustler_investor_id' => $investorID]);
    }

    public function get_single_hustler_profile($array)
    {
        return parent::get_single($array);
    }

    public function upsert_profile($investorID, $data)
    {
        $existing = $this->get_by_investor($investorID);
        if (customCompute($existing)) {
            parent::update($data, $existing->{$this->_primary_key});
            return $existing->{$this->_primary_key};
        }

        $data['hustler_investor_id'] = $investorID;
        return parent::insert($data);
    }
}
