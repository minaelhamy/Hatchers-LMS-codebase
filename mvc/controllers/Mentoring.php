<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Mentoring extends Admin_Controller
{
    public $load;
    public $session;
    public $data;
    public $input;
    public $db;
    public $student_m;
    public $teacher_m;
    public $mentor_founder_m;
    public $founder_meeting_m;
    public $hatchers_message_m;
    public $hatchers_shell_m;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('student_m');
        $this->load->model('teacher_m');
        $this->load->model('mentor_founder_m');
        $this->load->model('founder_meeting_m');
        $this->load->model('hatchers_message_m');
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
        $thread = $this->_resolveThread();
        $founder = isset($thread['founder']) ? $thread['founder'] : null;
        $mentor = isset($thread['mentor']) ? $thread['mentor'] : null;
        $meetings = [];
        $messages = [];
        $calendar = [];

        if (customCompute($founder) && customCompute($mentor)) {
            $meetings = $this->founder_meeting_m->get_order_by_founder_meeting(['founder_id' => $founder->studentID]);
            $messages = $this->hatchers_message_m->get_thread($founder->studentID, $mentor->teacherID);
            $this->hatchers_message_m->mark_thread_read($founder->studentID, $mentor->teacherID, (int) $this->session->userdata('usertypeID'));

            if (customCompute($meetings)) {
                foreach ($meetings as $meeting) {
                    if (!empty($meeting->starts_at)) {
                        $calendar[] = [
                            'title' => !empty($meeting->title) ? $meeting->title : 'Mentoring',
                            'start' => $meeting->starts_at
                        ];
                    }
                }
            }
        }

        $this->data['threadFounder'] = $founder;
        $this->data['threadMentor'] = $mentor;
        $this->data['meetings'] = $meetings;
        $this->data['messages'] = $messages;
        $this->data['hatchers_shell'] = $this->hatchers_shell_m->build('mentoring', $calendar, [
            'founder' => $founder,
            'mentor' => $mentor
        ]);
        $this->data['subview'] = 'mentoring/index';
        $this->load->view('_layout_hatchers', $this->data);
    }

    public function request_meeting()
    {
        $thread = $this->_resolveThreadFromPost();
        if (!customCompute($thread['founder']) || !customCompute($thread['mentor'])) {
            show_404();
        }

        $usertypeID = (int) $this->session->userdata('usertypeID');
        $title = trim((string) $this->input->post('title'));

        $this->founder_meeting_m->insert_founder_meeting([
            'founder_id' => $thread['founder']->studentID,
            'mentor_id' => $thread['mentor']->teacherID,
            'title' => $title !== '' ? $title : 'Mentoring session',
            'description' => trim((string) $this->input->post('description')),
            'starts_at' => $this->input->post('starts_at'),
            'ends_at' => $this->input->post('ends_at') ?: null,
            'meeting_type' => $usertypeID === 3 ? 'request' : 'mentoring',
            'requested_by_usertypeID' => $usertypeID,
            'request_status' => $usertypeID === 3 ? 'requested' : 'scheduled',
            'join_link' => trim((string) $this->input->post('join_link')),
            'status' => 0,
            'notes' => trim((string) $this->input->post('notes')),
        ]);

        redirect('mentoring/index' . $this->_threadQuery($thread['founder']->studentID));
    }

    public function respond_meeting($meetingID = 0, $status = 'accepted')
    {
        $meetingID = (int) $meetingID;
        if ($meetingID <= 0 || (int) $this->session->userdata('usertypeID') !== 2) {
            show_404();
        }

        $meeting = $this->founder_meeting_m->get_single_founder_meeting(['founder_meeting_id' => $meetingID]);
        if (!customCompute($meeting)) {
            show_404();
        }

        $assignment = $this->mentor_founder_m->get_single_mentor_founder([
            'mentor_id' => $this->session->userdata('loginuserID'),
            'founder_id' => $meeting->founder_id,
            'status' => 1
        ]);
        if (!customCompute($assignment)) {
            show_404();
        }

        $nextStatus = ($status === 'declined') ? 'declined' : 'scheduled';
        $this->founder_meeting_m->update_founder_meeting([
            'request_status' => $nextStatus,
            'updated_at' => date('Y-m-d H:i:s')
        ], $meetingID);

        redirect('mentoring/index?founder_id=' . (int) $meeting->founder_id);
    }

    public function send_message()
    {
        $thread = $this->_resolveThreadFromPost();
        if (!customCompute($thread['founder']) || !customCompute($thread['mentor']) || !$this->db->table_exists('hatchers_messages')) {
            show_404();
        }

        $message = trim((string) $this->input->post('message'));
        if ($message === '') {
            redirect('mentoring/index' . $this->_threadQuery($thread['founder']->studentID));
        }

        $this->hatchers_message_m->insert_hatchers_message([
            'founder_id' => $thread['founder']->studentID,
            'mentor_id' => $thread['mentor']->teacherID,
            'sender_id' => $this->session->userdata('loginuserID'),
            'sender_usertypeID' => $this->session->userdata('usertypeID'),
            'message' => $message,
            'is_read' => 0
        ]);

        redirect('mentoring/index' . $this->_threadQuery($thread['founder']->studentID));
    }

    public function notifications()
    {
        $this->data['hatchers_shell'] = $this->hatchers_shell_m->build('mentoring');
        $this->data['notifications'] = isset($this->data['hatchers_shell']['notifications']) ? $this->data['hatchers_shell']['notifications'] : [];
        $this->data['subview'] = 'mentoring/notifications';
        $this->load->view('_layout_hatchers', $this->data);
    }

    private function _resolveThread()
    {
        $usertypeID = (int) $this->session->userdata('usertypeID');
        $loginuserID = (int) $this->session->userdata('loginuserID');

        if ($usertypeID === 3) {
            $founder = $this->student_m->general_get_single_student(['studentID' => $loginuserID]);
            $assignment = $this->mentor_founder_m->get_single_mentor_founder([
                'founder_id' => $loginuserID,
                'status' => 1
            ]);
            $mentor = customCompute($assignment) ? $this->teacher_m->get_single_teacher(['teacherID' => $assignment->mentor_id]) : null;
            return ['founder' => $founder, 'mentor' => $mentor];
        }

        if ($usertypeID === 2) {
            $founderID = (int) $this->input->get('founder_id');
            if ($founderID <= 0) {
                $assignment = $this->mentor_founder_m->get_single_mentor_founder([
                    'mentor_id' => $loginuserID,
                    'status' => 1
                ]);
                $founderID = customCompute($assignment) ? (int) $assignment->founder_id : 0;
            }
            if ($founderID <= 0) {
                return ['founder' => null, 'mentor' => null];
            }
            $assignment = $this->mentor_founder_m->get_single_mentor_founder([
                'mentor_id' => $loginuserID,
                'founder_id' => $founderID,
                'status' => 1
            ]);
            if (!customCompute($assignment)) {
                return ['founder' => null, 'mentor' => null];
            }

            return [
                'founder' => $this->student_m->general_get_single_student(['studentID' => $founderID]),
                'mentor' => $this->teacher_m->get_single_teacher(['teacherID' => $loginuserID])
            ];
        }

        return ['founder' => null, 'mentor' => null];
    }

    private function _resolveThreadFromPost()
    {
        $_GET['founder_id'] = (int) $this->input->post('founder_id');
        return $this->_resolveThread();
    }

    private function _threadQuery($founderID)
    {
        return ((int) $this->session->userdata('usertypeID') === 2) ? '?founder_id=' . (int) $founderID : '';
    }
}
