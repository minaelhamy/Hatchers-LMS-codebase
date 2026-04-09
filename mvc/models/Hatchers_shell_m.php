<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Hatchers_shell_m extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('hatchers_nav_item_m');
        $this->load->model('mentor_founder_m');
        $this->load->model('teacher_m');
        $this->load->model('student_m');
        $this->load->model('founder_task_m');
        $this->load->model('founder_meeting_m');
        $this->load->model('founder_learning_m');
        $this->load->model('hatchers_message_m');
    }

    public function build($activeNav = 'home', $calendarEvents = [], $context = [])
    {
        $usertypeID = (int) $this->session->userdata('usertypeID');
        $userID = (int) $this->session->userdata('loginuserID');

        return [
            'active_nav'    => $activeNav,
            'nav_items'     => $this->get_nav_items($usertypeID),
            'ai_tools'      => $this->get_ai_tools(),
            'notifications' => $this->get_notifications($usertypeID, $userID),
            'calendar'      => $calendarEvents,
            'context'       => $context,
            'message_badge' => $this->hatchers_message_m->count_unread_for_user($userID, $usertypeID),
        ];
    }

    public function get_nav_items($usertypeID)
    {
        if ((int) $usertypeID === 1) {
            return [
                ['key' => 'home', 'label' => 'Home', 'icon' => 'fa-home', 'link' => 'dashboard/index'],
                ['key' => 'profiles', 'label' => 'Profiles', 'icon' => 'fa-users', 'link' => 'hatchersadmin/profiles'],
                ['key' => 'mentoring', 'label' => 'Assignments', 'icon' => 'fa-comments-o', 'link' => 'hatchersadmin/assignments'],
                ['key' => 'learning', 'label' => 'Library', 'icon' => 'fa-book', 'link' => 'learningplan/index'],
                ['key' => 'ai_tools', 'label' => 'AI Tools', 'icon' => 'fa-magic', 'link' => 'aitools/index'],
            ];
        }

        if ((int) $usertypeID === 2) {
            return [
                ['key' => 'home', 'label' => 'Home', 'icon' => 'fa-home', 'link' => 'dashboard/index'],
                ['key' => 'learning', 'label' => 'Learning Plan', 'icon' => 'fa-book', 'link' => 'learningplan/index'],
                ['key' => 'mentoring', 'label' => 'Mentoring', 'icon' => 'fa-comments-o', 'link' => 'mentoring/index'],
                ['key' => 'ai_tools', 'label' => 'AI Tools', 'icon' => 'fa-magic', 'link' => 'aitools/index'],
            ];
        }

        return [
            ['key' => 'home', 'label' => 'Home', 'icon' => 'fa-home', 'link' => 'dashboard/index'],
            ['key' => 'launch_plan', 'label' => 'Launch Plan', 'icon' => 'fa-rocket', 'link' => 'launchplan/index'],
            ['key' => 'ai_tools', 'label' => 'AI Tools', 'icon' => 'fa-magic', 'link' => 'aitools/index'],
            ['key' => 'learning', 'label' => 'Learning Plan', 'icon' => 'fa-book', 'link' => 'learningplan/index'],
            ['key' => 'mentoring', 'label' => 'Mentoring', 'icon' => 'fa-comments-o', 'link' => 'mentoring/index'],
        ];
    }

    public function get_ai_tools()
    {
        if (!$this->db->table_exists('hatchers_nav_items')) {
            return [];
        }

        return $this->hatchers_nav_item_m->get_order_by_hatchers_nav_item([
            'location' => 'right_ai',
            'active'   => 1
        ]);
    }

    public function get_notifications($usertypeID, $userID)
    {
        $items = [];
        $today = date('Y-m-d');

        if ((int) $usertypeID === 3) {
            $meetings = $this->founder_meeting_m->get_order_by_founder_meeting(['founder_id' => $userID]);
            $tasks = $this->founder_task_m->get_order_by_founder_task(['founder_id' => $userID]);
            $learning = $this->founder_learning_m->get_order_by_founder_learning(['founder_id' => $userID]);

            if (customCompute($meetings)) {
                foreach ($meetings as $meeting) {
                    if (!empty($meeting->starts_at) && date('Y-m-d', strtotime($meeting->starts_at)) === $today) {
                        $items[] = [
                            'title' => 'Meeting today',
                            'body'  => (!empty($meeting->title) ? $meeting->title : 'Mentoring session') . ' at ' . date('g:ia', strtotime($meeting->starts_at)),
                            'link'  => base_url('mentoring/index')
                        ];
                    }
                }
            }

            if (customCompute($tasks)) {
                foreach ($tasks as $task) {
                    if ((int) $task->status === 0) {
                        $items[] = [
                            'title' => 'Open task',
                            'body'  => $task->title,
                            'link'  => base_url('launchplan/index')
                        ];
                        if (count($items) >= 6) {
                            break;
                        }
                    }
                }
            }

            if (customCompute($learning) && count($items) < 6) {
                foreach ($learning as $lesson) {
                    $items[] = [
                        'title' => 'Learning item',
                        'body'  => $lesson->title,
                        'link'  => base_url('learningplan/index')
                    ];
                    if (count($items) >= 6) {
                        break;
                    }
                }
            }
        } elseif ((int) $usertypeID === 2) {
            $meetings = $this->founder_meeting_m->get_order_by_founder_meeting(['mentor_id' => $userID]);
            if (customCompute($meetings)) {
                foreach ($meetings as $meeting) {
                    if (!in_array((string) $meeting->request_status, ['requested', 'reschedule_requested'], true)) {
                        continue;
                    }
                    $founder = $this->student_m->general_get_single_student(['studentID' => $meeting->founder_id]);
                    if (!customCompute($founder)) {
                        continue;
                    }
                    $items[] = [
                        'title' => (string) $meeting->request_status === 'reschedule_requested' ? 'Reschedule request' : 'Meeting request',
                        'body'  => $founder->name . ' • ' . (!empty($meeting->title) ? $meeting->title : 'Mentoring session'),
                        'link'  => base_url('mentoring/index?founder_id=' . $founder->studentID)
                    ];
                    if (count($items) >= 6) {
                        break;
                    }
                }
            }

            if (count($items) < 6) {
                $assignments = $this->mentor_founder_m->get_order_by_mentor_founder([
                    'mentor_id' => $userID,
                    'status'    => 1
                ]);

                if (customCompute($assignments)) {
                    foreach ($assignments as $assignment) {
                        $founder = $this->student_m->general_get_single_student(['studentID' => $assignment->founder_id]);
                        if (customCompute($founder)) {
                            $items[] = [
                                'title' => 'Assigned founder',
                                'body'  => $founder->name,
                                'link'  => base_url('mentoring/index?founder_id=' . $founder->studentID)
                            ];
                            if (count($items) >= 6) {
                                break;
                            }
                        }
                    }
                }
            }
        } elseif ((int) $usertypeID === 1) {
            $founders = $this->student_m->get_order_by_student();
            if (customCompute($founders)) {
                foreach (array_slice($founders, 0, 6) as $founder) {
                    $items[] = [
                        'title' => 'Founder account',
                        'body'  => $founder->name,
                        'link'  => base_url('hatchersadmin/profiles')
                    ];
                }
            }
        }

        return $items;
    }
}
