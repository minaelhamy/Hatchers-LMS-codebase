<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Hatchers_message_m extends MY_Model
{
    protected $_table_name = 'hatchers_messages';
    protected $_primary_key = 'hatchers_message_id';
    protected $_primary_filter = 'intval';
    protected $_order_by = 'hatchers_message_id ASC';

    public function __construct()
    {
        parent::__construct();
    }

    public function get_hatchers_message($array = NULL, $signal = FALSE)
    {
        return parent::get($array, $signal);
    }

    public function get_order_by_hatchers_message($array = NULL)
    {
        return parent::get_order_by($array);
    }

    public function get_single_hatchers_message($array)
    {
        return parent::get_single($array);
    }

    public function insert_hatchers_message($array)
    {
        return parent::insert($array);
    }

    public function update_hatchers_message($data, $id = NULL)
    {
        parent::update($data, $id);
        return $id;
    }

    public function get_thread($founderID, $mentorID)
    {
        return $this->get_order_by_hatchers_message([
            'founder_id' => $founderID,
            'mentor_id'  => $mentorID
        ]);
    }

    public function count_unread_for_user($userID, $usertypeID)
    {
        if (!$this->db->table_exists($this->_table_name)) {
            return 0;
        }

        $this->db->from($this->_table_name);
        $this->db->where('is_read', 0);
        $this->db->where('sender_usertypeID !=', $usertypeID);

        if ((int) $usertypeID === 3) {
            $this->db->where('founder_id', $userID);
        } elseif ((int) $usertypeID === 2) {
            $this->db->where('mentor_id', $userID);
        } else {
            return 0;
        }

        return (int) $this->db->count_all_results();
    }

    public function mark_thread_read($founderID, $mentorID, $viewerUsertypeID)
    {
        if (!$this->db->table_exists($this->_table_name)) {
            return;
        }

        $this->db->set([
            'is_read' => 1,
            'read_at' => date('Y-m-d H:i:s')
        ]);
        $this->db->where('founder_id', $founderID);
        $this->db->where('mentor_id', $mentorID);
        $this->db->where('sender_usertypeID !=', $viewerUsertypeID);
        $this->db->where('is_read', 0);
        $this->db->update($this->_table_name);
    }
}
