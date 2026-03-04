<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Hustler_action_item_m extends MY_Model
{
    protected $_table_name = 'hustler_action_items';
    protected $_primary_key = 'hustler_action_item_id';
    protected $_primary_filter = 'intval';
    protected $_order_by = 'sort_order ASC, hustler_action_item_id ASC';

    public function __construct()
    {
        parent::__construct();
    }

    public function get_for_profile($profileID)
    {
        return $this->get_order_by_hustler_action_item(['hustler_founder_profile_id' => $profileID]);
    }

    public function get_order_by_hustler_action_item($array = null)
    {
        return parent::get_order_by($array);
    }

    public function replace_for_profile($profileID, $items)
    {
        $this->db->delete($this->_table_name, ['hustler_founder_profile_id' => $profileID]);

        if (!is_array($items) || empty($items)) {
            return;
        }

        foreach ($items as $item) {
            parent::insert($item);
        }
    }
}
