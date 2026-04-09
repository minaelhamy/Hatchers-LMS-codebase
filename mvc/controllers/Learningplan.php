<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Learningplan extends Admin_Controller
{
    public $load;
    public $session;
    public $data;
    public $input;
    public $db;
    public $student_m;
    public $mentor_founder_m;
    public $founder_learning_m;
    public $learning_library_m;
    public $hatchers_shell_m;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('student_m');
        $this->load->model('mentor_founder_m');
        $this->load->model('founder_learning_m');
        $this->load->model('learning_library_m');
        $this->load->model('hatchers_shell_m');

        $this->data['headerassets'] = [
            'css' => [
                'assets/hatchers/hatchers.css',
                'assets/fullcalendar/lib/cupertino/jquery-ui.min.css',
                'assets/fullcalendar/fullcalendar.css',
            ],
            'js' => [
                'assets/fullcalendar/lib/jquery-ui.min.js',
                'assets/fullcalendar/lib/moment.min.js',
                'assets/fullcalendar/fullcalendar.min.js',
            ]
        ];
    }

    public function index()
    {
        $founder = $this->_resolveFounder();
        $lessons = [];
        $resources = [];
        $calendar = [];

        if (customCompute($founder)) {
            $lessons = $this->founder_learning_m->get_order_by_founder_learning(['founder_id' => $founder->studentID]);
            if (customCompute($lessons)) {
                foreach ($lessons as $lesson) {
                    if (!empty($lesson->starts_at)) {
                        $calendar[] = [
                            'title' => 'Learning',
                            'start' => $lesson->starts_at
                        ];
                    }
                }
            }

            if ($this->db->table_exists('learning_library')) {
                $this->db->from('learning_library');
                $this->db->group_start();
                $this->db->where('founder_id IS NULL', null, false);
                $this->db->or_where('founder_id', $founder->studentID);
                $this->db->group_end();
                $this->db->where('is_active', 1);
                $this->db->order_by('learning_library_id', 'DESC');
                $resources = $this->db->get()->result();
            }
        } elseif ($this->_canManageLearning() && $this->db->table_exists('learning_library')) {
            $resources = $this->learning_library_m->get_order_by_learning_library(['is_active' => 1]);
        }

        $this->data['founder'] = $founder;
        $this->data['lessons'] = $lessons;
        $this->data['resources'] = $resources;
        $this->data['hatchers_shell'] = $this->hatchers_shell_m->build('learning', $calendar, [
            'founder' => $founder
        ]);
        $this->data['subview'] = 'learningplan/index';
        $this->load->view('_layout_hatchers', $this->data);
    }

    public function add_lesson()
    {
        $founder = $this->_resolveFounderFromPost();
        if (!customCompute($founder) || !$this->_canManageLearning()) {
            show_404();
        }

        $this->founder_learning_m->insert_founder_learning([
            'founder_id' => $founder->studentID,
            'title' => trim((string) $this->input->post('title')),
            'subtitle' => trim((string) $this->input->post('subtitle')),
            'description' => trim((string) $this->input->post('description')),
            'resource_url' => trim((string) $this->input->post('resource_url')),
            'resource_type' => trim((string) $this->input->post('resource_type')),
            'starts_at' => $this->input->post('starts_at') ?: null,
            'status' => 0
        ]);

        redirect('learningplan/index?founder_id=' . $founder->studentID);
    }

    public function add_resource()
    {
        if (!$this->_canManageLearning() || !$this->db->table_exists('learning_library')) {
            show_404();
        }

        $founderID = (int) $this->input->post('founder_id');
        $resourceType = trim((string) $this->input->post('resource_type'));
        $filePath = null;

        if ($resourceType === 'pdf' && isset($_FILES['resource_file']) && $_FILES['resource_file']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = FCPATH . 'uploads/learning_library/';
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0777, true);
            }
            $filename = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $_FILES['resource_file']['name']);
            $target = $uploadDir . $filename;
            if (@move_uploaded_file($_FILES['resource_file']['tmp_name'], $target)) {
                $filePath = 'uploads/learning_library/' . $filename;
            }
        }

        $this->learning_library_m->insert_learning_library([
            'founder_id' => $founderID > 0 ? $founderID : null,
            'mentor_id' => $this->session->userdata('usertypeID') == 2 ? $this->session->userdata('loginuserID') : null,
            'created_by_userID' => $this->session->userdata('loginuserID'),
            'created_by_usertypeID' => $this->session->userdata('usertypeID'),
            'title' => trim((string) $this->input->post('title')),
            'description' => trim((string) $this->input->post('description')),
            'resource_type' => $resourceType !== '' ? $resourceType : 'link',
            'resource_url' => trim((string) $this->input->post('resource_url')),
            'file_path' => $filePath,
            'is_active' => 1,
        ]);

        redirect('learningplan/index' . ($founderID > 0 ? '?founder_id=' . $founderID : ''));
    }

    private function _resolveFounder()
    {
        $usertypeID = (int) $this->session->userdata('usertypeID');
        $loginuserID = (int) $this->session->userdata('loginuserID');

        if ($usertypeID === 3) {
            return $this->student_m->general_get_single_student(['studentID' => $loginuserID]);
        }

        $founderID = (int) $this->input->get('founder_id');
        if ($founderID <= 0) {
            return null;
        }

        if ($usertypeID === 2) {
            $assignment = $this->mentor_founder_m->get_single_mentor_founder([
                'mentor_id' => $loginuserID,
                'founder_id' => $founderID,
                'status' => 1
            ]);
            if (!customCompute($assignment)) {
                return null;
            }
        }

        return $this->student_m->general_get_single_student(['studentID' => $founderID]);
    }

    private function _resolveFounderFromPost()
    {
        $_GET['founder_id'] = (int) $this->input->post('founder_id');
        return $this->_resolveFounder();
    }

    private function _canManageLearning()
    {
        $usertypeID = (int) $this->session->userdata('usertypeID');
        return ($usertypeID === 1 || $usertypeID === 2);
    }
}
