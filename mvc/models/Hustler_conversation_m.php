<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Hustler_conversation_m extends MY_Model
{
    protected $_table_name = 'hustler_conversations';
    protected $_primary_key = 'hustler_conversation_id';
    protected $_primary_filter = 'intval';
    protected $_order_by = 'hustler_conversation_id ASC';

    public function __construct()
    {
        parent::__construct();
    }

    public function get_for_profile($profileID, $limit = 24)
    {
        return $this->db->order_by($this->_primary_key, 'DESC')
            ->limit((int) $limit)
            ->get_where($this->_table_name, ['hustler_founder_profile_id' => $profileID])
            ->result();
    }

    public function insert_hustler_conversation($array)
    {
        return parent::insert($array);
    }
}
