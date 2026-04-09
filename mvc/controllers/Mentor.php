<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Mentor extends Admin_Controller
{
    public $load;
    public $session;
    public $lang;
    public $data;
    public $input;
    public $db;
    public $student_m;
    public $teacher_m;
    public $mentor_founder_m;
    public $founder_task_m;
    public $founder_meeting_m;
    public $founder_learning_m;
    public $milestone_meta_m;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('student_m');
        $this->load->model('teacher_m');
        $this->load->model('mentor_founder_m');
        $this->load->model('founder_task_m');
        $this->load->model('founder_meeting_m');
        $this->load->model('founder_learning_m');
        $this->load->model('milestone_meta_m');

        $this->data['headerassets'] = [
            'css' => [
                'assets/hatchers/hatchers.css'
            ]
        ];
    }

    public function view($founderID = 0)
    {
        $mentorID = $this->session->userdata('loginuserID');
        $usertypeID = $this->session->userdata('usertypeID');
        $founderID = (int) $founderID;

        if ($usertypeID != 2 || $founderID <= 0) {
            show_404();
        }

        $assignment = $this->mentor_founder_m->get_single_mentor_founder([
            'mentor_id' => $mentorID,
            'founder_id' => $founderID,
            'status' => 1
        ]);

        if (!customCompute($assignment)) {
            show_404();
        }
        redirect('mentoring/index?founder_id=' . $founderID);
    }

    public function add_meeting()
    {
        $mentorID = $this->session->userdata('loginuserID');
        $usertypeID = $this->session->userdata('usertypeID');
        if ($usertypeID != 2) {
            show_404();
        }

        $founderID = (int) $this->input->post('founder_id');
        $startsAt  = $this->input->post('starts_at');
        $endsAt    = $this->input->post('ends_at');
        $notes     = $this->input->post('notes');

        $assignment = $this->mentor_founder_m->get_single_mentor_founder([
            'mentor_id' => $mentorID,
            'founder_id' => $founderID,
            'status' => 1
        ]);
        if (!customCompute($assignment)) {
            show_404();
        }

        $this->founder_meeting_m->insert_founder_meeting([
            'founder_id' => $founderID,
            'mentor_id' => $mentorID,
            'starts_at' => $startsAt,
            'ends_at' => !empty($endsAt) ? $endsAt : null,
            'meeting_type' => 'mentoring',
            'status' => 0,
            'notes' => $notes
        ]);

        redirect('mentoring/index?founder_id=' . $founderID);
    }

    public function add_learning()
    {
        $mentorID = $this->session->userdata('loginuserID');
        $usertypeID = $this->session->userdata('usertypeID');
        if ($usertypeID != 2) {
            show_404();
        }

        $founderID = (int) $this->input->post('founder_id');
        $title     = $this->input->post('title');
        $subtitle  = $this->input->post('subtitle');
        $startsAt  = $this->input->post('starts_at');

        $assignment = $this->mentor_founder_m->get_single_mentor_founder([
            'mentor_id' => $mentorID,
            'founder_id' => $founderID,
            'status' => 1
        ]);
        if (!customCompute($assignment)) {
            show_404();
        }

        $this->founder_learning_m->insert_founder_learning([
            'founder_id' => $founderID,
            'title' => $title,
            'subtitle' => $subtitle,
            'starts_at' => !empty($startsAt) ? $startsAt : null,
            'status' => 0
        ]);

        redirect('learningplan/index?founder_id=' . $founderID);
    }

    public function add_task()
    {
        $mentorID = $this->session->userdata('loginuserID');
        $usertypeID = $this->session->userdata('usertypeID');
        if ($usertypeID != 2) {
            show_404();
        }

        $founderID = (int) $this->input->post('founder_id');
        $title     = $this->input->post('title');
        $description = $this->input->post('description');
        $dueDate   = $this->input->post('due_date');
        $milestoneID = (int) $this->input->post('milestone_id');

        $assignment = $this->mentor_founder_m->get_single_mentor_founder([
            'mentor_id' => $mentorID,
            'founder_id' => $founderID,
            'status' => 1
        ]);
        if (!customCompute($assignment)) {
            show_404();
        }

        $this->founder_task_m->insert_founder_task([
            'founder_id' => $founderID,
            'title' => $title,
            'description' => $description,
            'due_date' => !empty($dueDate) ? $dueDate : null,
            'milestone_id' => $milestoneID > 0 ? $milestoneID : null,
            'status' => 0
        ]);

        redirect('launchplan/index?founder_id=' . $founderID);
    }

    public function add_milestone()
    {
        $mentorID = $this->session->userdata('loginuserID');
        $usertypeID = $this->session->userdata('usertypeID');
        if ($usertypeID != 2) {
            show_404();
        }

        $founderID = (int) $this->input->post('founder_id');
        $title     = $this->input->post('title');
        $description = $this->input->post('description');
        $dueDate   = $this->input->post('due_date');
        $notes     = $this->input->post('notes');

        $assignment = $this->mentor_founder_m->get_single_mentor_founder([
            'mentor_id' => $mentorID,
            'founder_id' => $founderID,
            'status' => 1
        ]);
        if (!customCompute($assignment)) {
            show_404();
        }

        $this->milestone_meta_m->insert_milestone_meta([
            'milestone_id' => 0,
            'founder_id' => $founderID,
            'title' => $title,
            'description' => $description,
            'due_date' => !empty($dueDate) ? $dueDate : null,
            'status' => 0,
            'notes' => $notes
        ]);

        redirect('launchplan/index?founder_id=' . $founderID);
    }
}
