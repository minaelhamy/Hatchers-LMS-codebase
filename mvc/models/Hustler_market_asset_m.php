<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Hustler_market_asset_m extends MY_Model
{
    protected $_table_name = 'hustler_market_assets';
    protected $_primary_key = 'hustler_market_asset_id';
    protected $_primary_filter = 'intval';
    protected $_order_by = 'hustler_market_asset_id DESC';

    public function __construct()
    {
        parent::__construct();
    }

    public function get_latest_for_profile($profileID)
    {
        return $this->get_single_hustler_market_asset(['hustler_founder_profile_id' => $profileID]);
    }

    public function get_single_hustler_market_asset($array)
    {
        return parent::get_single($array);
    }

    public function upsert_for_profile($profileID, $data)
    {
        $existing = $this->get_latest_for_profile($profileID);
        if (customCompute($existing)) {
            parent::update($data, $existing->{$this->_primary_key});
            return $existing->{$this->_primary_key};
        }

        $data['hustler_founder_profile_id'] = $profileID;
        return parent::insert($data);
    }
}
