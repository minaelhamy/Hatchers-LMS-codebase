<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Aitools extends Admin_Controller
{
    public $load;
    public $data;
    public $hatchers_shell_m;
    public $hatchers_nav_item_m;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('hatchers_shell_m');
        $this->load->model('hatchers_nav_item_m');
        $this->data['headerassets'] = [
            'css' => [
                'assets/hatchers/hatchers.css'
            ]
        ];
    }

    public function index()
    {
        $tools = $this->hatchers_shell_m->get_ai_tools();
        $this->data['tools'] = $tools;
        $this->data['hatchers_shell'] = $this->hatchers_shell_m->build('ai_tools');
        $this->data['subview'] = 'aitools/index';
        $this->load->view('_layout_hatchers', $this->data);
    }
}
