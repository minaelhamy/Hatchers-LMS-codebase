<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Launchplan extends Admin_Controller
{
    public $load;
    public $session;
    public $data;
    public $input;
    public $db;
    public $student_m;
    public $teacher_m;
    public $mentor_founder_m;
    public $founder_task_m;
    public $milestone_meta_m;
    public $hatchers_shell_m;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('student_m');
        $this->load->model('teacher_m');
        $this->load->model('mentor_founder_m');
        $this->load->model('founder_task_m');
        $this->load->model('milestone_meta_m');
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
        if (!customCompute($founder)) {
            show_404();
        }

        $tasks = $this->founder_task_m->get_order_by_founder_task(['founder_id' => $founder->studentID]);
        $milestones = $this->milestone_meta_m->get_order_by_milestone_meta(['founder_id' => $founder->studentID]);

        $milestoneMap = [];
        $calendar = [];
        if (customCompute($milestones)) {
            foreach ($milestones as $milestone) {
                $milestoneMap[$milestone->milestone_meta_id] = $milestone;
                if (!empty($milestone->due_date)) {
                    $calendar[] = [
                        'title' => 'Milestone',
                        'start' => $milestone->due_date
                    ];
                }
            }
        }

        if (customCompute($tasks)) {
            foreach ($tasks as $task) {
                if (!empty($task->due_date)) {
                    $calendar[] = [
                        'title' => 'Task Due',
                        'start' => $task->due_date
                    ];
                }
            }
        }

        $this->data['founder'] = $founder;
        $this->data['tasks'] = $tasks;
        $this->data['milestones'] = $milestones;
        $this->data['milestoneMap'] = $milestoneMap;
        $this->data['hatchers_shell'] = $this->hatchers_shell_m->build('launch_plan', $calendar, [
            'founder' => $founder
        ]);
        $this->data['subview'] = 'launchplan/index';
        $this->load->view('_layout_hatchers', $this->data);
    }

    public function toggle_task($taskID = 0)
    {
        $taskID = (int) $taskID;
        if ($taskID <= 0) {
            redirect('launchplan/index');
        }

        $task = $this->founder_task_m->get_single_founder_task(['founder_task_id' => $taskID]);
        if (!customCompute($task) || !$this->_canAccessFounder((int) $task->founder_id)) {
            show_404();
        }

        $newStatus = ((int) $task->status === 1) ? 0 : 1;
        $data = [
            'status' => $newStatus,
            'updated_at' => date('Y-m-d H:i:s'),
            'completed_at' => $newStatus === 1 ? date('Y-m-d H:i:s') : null,
            'completed_by_usertypeID' => $newStatus === 1 ? (int) $this->session->userdata('usertypeID') : null,
        ];

        $this->founder_task_m->update_founder_task($data, $taskID);
        redirect('launchplan/index' . ($this->input->get('founder_id') ? '?founder_id=' . (int) $this->input->get('founder_id') : ''));
    }

    private function _resolveFounder()
    {
        $usertypeID = (int) $this->session->userdata('usertypeID');
        $loginuserID = (int) $this->session->userdata('loginuserID');

        if ($usertypeID === 3) {
            return $this->student_m->general_get_single_student(['studentID' => $loginuserID]);
        }

        $founderID = (int) $this->input->get('founder_id');
        if ($founderID <= 0 && $usertypeID === 2) {
            $assignment = $this->mentor_founder_m->get_single_mentor_founder([
                'mentor_id' => $loginuserID,
                'status'    => 1
            ]);
            $founderID = customCompute($assignment) ? (int) $assignment->founder_id : 0;
        }

        if ($founderID <= 0) {
            return null;
        }

        if (!$this->_canAccessFounder($founderID)) {
            return null;
        }

        return $this->student_m->general_get_single_student(['studentID' => $founderID]);
    }

    private function _canAccessFounder($founderID)
    {
        $usertypeID = (int) $this->session->userdata('usertypeID');
        $loginuserID = (int) $this->session->userdata('loginuserID');

        if ($usertypeID === 1) {
            return true;
        }

        if ($usertypeID === 3) {
            return $loginuserID === (int) $founderID;
        }

        if ($usertypeID === 2) {
            $assignment = $this->mentor_founder_m->get_single_mentor_founder([
                'mentor_id'  => $loginuserID,
                'founder_id' => $founderID,
                'status'     => 1
            ]);
            return customCompute($assignment);
        }

        return false;
    }
}
