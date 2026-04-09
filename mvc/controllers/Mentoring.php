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
    public $founder_task_m;
    public $milestone_meta_m;
    public $founder_learning_m;
    public $hatchers_message_m;
    public $hatchers_shell_m;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('student_m');
        $this->load->model('teacher_m');
        $this->load->model('mentor_founder_m');
        $this->load->model('founder_meeting_m');
        $this->load->model('founder_task_m');
        $this->load->model('milestone_meta_m');
        $this->load->model('founder_learning_m');
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
        $tasks = [];
        $milestones = [];
        $learning = [];
        $milestoneMap = [];
        $calendar = [];
        $assignedFounders = [];

        if ((int) $this->session->userdata('usertypeID') === 2) {
            $assignedFounders = $this->_getAssignedFounders((int) $this->session->userdata('loginuserID'));
        }

        if (customCompute($founder) && customCompute($mentor)) {
            $meetings = $this->founder_meeting_m->get_order_by_founder_meeting(['founder_id' => $founder->studentID]);
            $tasks = $this->founder_task_m->get_order_by_founder_task(['founder_id' => $founder->studentID]);
            $milestones = $this->milestone_meta_m->get_order_by_milestone_meta(['founder_id' => $founder->studentID]);
            $learning = $this->founder_learning_m->get_order_by_founder_learning(['founder_id' => $founder->studentID]);
            $messages = $this->hatchers_message_m->get_thread($founder->studentID, $mentor->teacherID);
            $this->hatchers_message_m->mark_thread_read($founder->studentID, $mentor->teacherID, (int) $this->session->userdata('usertypeID'));

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

            if (customCompute($learning)) {
                foreach ($learning as $lesson) {
                    if (!empty($lesson->starts_at)) {
                        $calendar[] = [
                            'title' => 'Learning',
                            'start' => $lesson->starts_at
                        ];
                    }
                }
            }
        }

        $this->data['threadFounder'] = $founder;
        $this->data['threadMentor'] = $mentor;
        $this->data['assignedFounders'] = $assignedFounders;
        $this->data['meetings'] = $meetings;
        $this->data['messages'] = $messages;
        $this->data['tasks'] = $tasks;
        $this->data['milestones'] = $milestones;
        $this->data['milestoneMap'] = $milestoneMap;
        $this->data['learning'] = $learning;
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

    public function founder_meeting_response($meetingID = 0, $status = 'accepted')
    {
        $meetingID = (int) $meetingID;
        if ($meetingID <= 0 || (int) $this->session->userdata('usertypeID') !== 3) {
            show_404();
        }

        $meeting = $this->founder_meeting_m->get_single_founder_meeting(['founder_meeting_id' => $meetingID]);
        if (!customCompute($meeting) || (int) $meeting->founder_id !== (int) $this->session->userdata('loginuserID')) {
            show_404();
        }

        $nextStatus = ($status === 'reschedule_requested') ? 'reschedule_requested' : 'accepted';
        $update = [
            'request_status' => $nextStatus,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($nextStatus === 'reschedule_requested') {
            $startsAt = trim((string) $this->input->post('starts_at'));
            $endsAt = trim((string) $this->input->post('ends_at'));
            $notes = trim((string) $this->input->post('notes'));
            if ($startsAt !== '') {
                $update['starts_at'] = $startsAt;
            }
            if ($endsAt !== '') {
                $update['ends_at'] = $endsAt;
            }
            if ($notes !== '') {
                $update['notes'] = $notes;
            }
        }

        $this->founder_meeting_m->update_founder_meeting($update, $meetingID);
        redirect('mentoring/index');
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

    public function add_task()
    {
        $thread = $this->_resolveThreadFromPost();
        if ((int) $this->session->userdata('usertypeID') !== 2 || !customCompute($thread['founder'])) {
            show_404();
        }

        $title = trim((string) $this->input->post('title'));
        if ($title === '') {
            redirect('mentoring/index' . $this->_threadQuery($thread['founder']->studentID));
        }

        $milestoneID = (int) $this->input->post('milestone_id');
        $this->founder_task_m->insert_founder_task([
            'founder_id' => $thread['founder']->studentID,
            'title' => $title,
            'description' => trim((string) $this->input->post('description')),
            'due_date' => $this->input->post('due_date') ? $this->input->post('due_date') : null,
            'milestone_id' => $milestoneID > 0 ? $milestoneID : null,
            'status' => 0
        ]);

        redirect('mentoring/index' . $this->_threadQuery($thread['founder']->studentID));
    }

    public function add_milestone()
    {
        $thread = $this->_resolveThreadFromPost();
        if ((int) $this->session->userdata('usertypeID') !== 2 || !customCompute($thread['founder'])) {
            show_404();
        }

        $title = trim((string) $this->input->post('title'));
        if ($title === '') {
            redirect('mentoring/index' . $this->_threadQuery($thread['founder']->studentID));
        }

        $this->milestone_meta_m->insert_milestone_meta([
            'milestone_id' => 0,
            'founder_id' => $thread['founder']->studentID,
            'title' => $title,
            'description' => trim((string) $this->input->post('description')),
            'due_date' => $this->input->post('due_date') ? $this->input->post('due_date') : null,
            'status' => 0,
            'notes' => trim((string) $this->input->post('notes'))
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

    private function _getAssignedFounders($mentorID)
    {
        $assignments = $this->mentor_founder_m->get_order_by_mentor_founder([
            'mentor_id' => $mentorID,
            'status' => 1
        ]);

        $founders = [];
        if (customCompute($assignments)) {
            foreach ($assignments as $assignment) {
                $founder = $this->student_m->general_get_single_student(['studentID' => $assignment->founder_id]);
                if (customCompute($founder)) {
                    $founders[] = $founder;
                }
            }
        }

        return $founders;
    }
}
