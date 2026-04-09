<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Dashboard extends Admin_Controller
{
    public $load;
    public $session;
    public $lang;
    public $data;
    public $setting_m;
    public $feetypes_m;
    public $automation_shudulu_m;
    public $student_m;
    public $lmember_m;
    public $tmember_m;
    public $hmember_m;
    public $automation_rec_m;
    public $systemadmin_m;
    public $maininvoice_m;
    public $invoice_m;
    public $studentrelation_m;
    public $classes_m;
    public $teacher_m;
    public $parents_m;
    public $book_m;
    public $event_m;
    public $holiday_m;
    public $visitorinfo_m;
    public $menu_m;
    public $subject_m;
    public $issue_m;
    public $sattendance_m;
    public $subjectattendance_m;
    public $schoolyear_m;
    public $income_m;
    public $payment_m;
    public $expense_m;
    public $make_payment_m;
    public $loginlog_m;
    public $update_m;
    public $mentor_founder_m;
    public $founder_task_m;
    public $founder_meeting_m;
    public $founder_learning_m;
    public $milestone_meta_m;
    public $input;
    public $hatchers_shell_m;
    /*
        | -----------------------------------------------------
        | PRODUCT NAME: 	INILABS SCHOOL MANAGEMENT SYSTEM
        | -----------------------------------------------------
        | AUTHOR:			INILABS TEAM
        | -----------------------------------------------------
        | EMAIL:			info@inilabs.net
        | -----------------------------------------------------
        | COPYRIGHT:		RESERVED BY INILABS IT
        | -----------------------------------------------------
        | WEBSITE:			http://inilabs.net
        | -----------------------------------------------------
        */
    protected $_versionCheckingUrl = 'http://demo.inilabs.net/autoupdate/update/index';

    function __construct()
    {
        parent::__construct();
        $this->load->model('systemadmin_m');
        $this->load->model("dashboard_m");
        $this->load->model("automation_shudulu_m");
        $this->load->model("automation_rec_m");
        $this->load->model("setting_m");
        $this->load->model("notice_m");
        $this->load->model("user_m");
        $this->load->model("student_m");
        $this->load->model("classes_m");
        $this->load->model("teacher_m");
        $this->load->model("parents_m");
        $this->load->model("sattendance_m");
        $this->load->model("subjectattendance_m");
        $this->load->model("subject_m");
        $this->load->model("feetypes_m");
        $this->load->model("invoice_m");
        $this->load->model("expense_m");
        $this->load->model("payment_m");
        $this->load->model("lmember_m");
        $this->load->model("book_m");
        $this->load->model("issue_m");
        $this->load->model('hmember_m');
        $this->load->model('tmember_m');
        $this->load->model('event_m');
        $this->load->model('holiday_m');
        $this->load->model('visitorinfo_m');
        $this->load->model('income_m');
        $this->load->model('make_payment_m');
        $this->load->model('maininvoice_m');
        $this->load->model('studentrelation_m');
        $this->load->model('mentor_founder_m');
        $this->load->model('founder_task_m');
        $this->load->model('founder_meeting_m');
        $this->load->model('founder_learning_m');
        $this->load->model('milestone_meta_m');
        $this->load->model('hatchers_shell_m');
        $language = $this->session->userdata('lang');
        $this->lang->load('dashboard', $language);

        $this->_automation();
    }

    private function _automation()
    {
        /* Automation Start */
        if ($this->data['siteinfos']->auto_invoice_generate == 1) {

            $array        = [];
            $autoRecArray = [];
            $cnt          = 0;
            $date         = date('Y-m-d');
            $day          = date('d');
            $month        = date('m');
            $year         = date('Y');
            $setting      = $this->setting_m->get_setting();
            if ($day >= $setting->automation) {
                $libraryFeetype = $this->feetypes_m->get_single_feetypes(['feetypes' => $this->lang->line('dashboard_libraryfee')]);
                if (!customCompute($libraryFeetype)) {
                    $this->feetypes_m->insert_feetypes([
                        'feetypes' => $this->lang->line('dashboard_libraryfee'),
                        'note'     => "Don't delete it!"
                    ]);
                }
                $libraryFeetype = $this->feetypes_m->get_single_feetypes(['feetypes' => $this->lang->line('dashboard_libraryfee')]);

                $transportFeetype = $this->feetypes_m->get_single_feetypes(['feetypes' => $this->lang->line('dashboard_transportfee')]);
                if (!customCompute($transportFeetype)) {
                    $this->feetypes_m->insert_feetypes([
                        'feetypes' => $this->lang->line('dashboard_transportfee'),
                        'note'     => "Don't delete it!"
                    ]);
                }
                $transportFeetype = $this->feetypes_m->get_single_feetypes(['feetypes' => $this->lang->line('dashboard_transportfee')]);

                $hostelFeetype = $this->feetypes_m->get_single_feetypes(['feetypes' => $this->lang->line('dashboard_hostelfee')]);
                if (!customCompute($hostelFeetype)) {
                    $this->feetypes_m->insert_feetypes([
                        'feetypes' => $this->lang->line('dashboard_hostelfee'),
                        'note'     => "Don't delete it!"
                    ]);
                }
                $hostelFeetype = $this->feetypes_m->get_single_feetypes(['feetypes' => $this->lang->line('dashboard_hostelfee')]);

                $automation_shudulus = $this->automation_shudulu_m->get_automation_shudulu();

                if (customCompute($automation_shudulus)) {
                    foreach ($automation_shudulus as $automation_shudulu) {
                        if ($automation_shudulu->month == $month && $automation_shudulu->year == $year) {
                            $cnt = 1;
                        }
                    }

                    if ($cnt === 0) {
                        $automationStudents = $this->student_m->general_get_order_by_student([
                            'schoolyearID' => $this->data['siteinfos']->school_year,
                            'classesID !=' => $this->data['siteinfos']->ex_class
                        ]);
                        $automationLMember  = pluck($this->lmember_m->get_lmember(), 'lbalance', 'studentID');
                        $automationTMember  = pluck($this->tmember_m->get_tmember(), 'tbalance', 'studentID');
                        $automationHMember  = pluck($this->hmember_m->get_hmember(), 'hbalance', 'studentID');
                        $allRecord          = $this->_getAllRec($this->automation_rec_m->get_automation_rec());
                        $superAdmin         = $this->systemadmin_m->get_systemadmin(1);

                        $mainInvoiceArray = [];
                        if (customCompute($automationStudents)) {
                            foreach ($automationStudents as $aTstudentkey => $aTstudent) {
                                if (customCompute($automationLMember) && isset($automationLMember[$aTstudent->studentID]) && ($automationLMember[$aTstudent->studentID] > 0 && !isset($allRecord[5427279][$aTstudent->studentID][$month][$year]))) {
                                    $mainInvoiceArray[] = [
                                        'maininvoiceschoolyearID' => $this->data['siteinfos']->school_year,
                                        'maininvoiceclassesID'    => $aTstudent->classesID,
                                        'maininvoicestudentID'    => $aTstudent->studentID,
                                        'maininvoicestatus'       => 0,
                                        'maininvoiceuserID'       => 1,
                                        'maininvoiceusertypeID'   => 1,
                                        'maininvoiceuname'        => null,
                                        'maininvoicedate'         => date("Y-m-d"),
                                        'maininvoicecreate_date'  => date('Y-m-d'),
                                        'maininvoiceday'          => date('d'),
                                        'maininvoicemonth'        => date('m'),
                                        'maininvoiceyear'         => date('Y'),
                                        'maininvoicedeleted_at'   => 1
                                    ];
                                    $array[] = [
                                        'schoolyearID' => $this->data['siteinfos']->school_year,
                                        'classesID'    => $aTstudent->classesID,
                                        'studentID'    => $aTstudent->studentID,
                                        'feetypeID'    => customCompute($libraryFeetype) ? $libraryFeetype->feetypesID : 0,
                                        'feetype'      => customCompute($libraryFeetype) ? $libraryFeetype->feetypes : null,
                                        'amount'       => (int) $automationLMember[$aTstudent->studentID],
                                        'discount'     => 0,
                                        'paidstatus'   => 0,
                                        'userID'       => 1,
                                        'usertypeID'   => 1,
                                        'uname'        => $superAdmin->name,
                                        'date'         => date("Y-m-d"),
                                        'create_date'  => date('Y-m-d'),
                                        'day'          => date('d'),
                                        'month'        => date('m'),
                                        'year'         => date('Y'),
                                        'deleted_at'   => 1
                                    ];
                                    $autoRecArray[] = [
                                        'studentID' => $aTstudent->studentID,
                                        'date'      => $date,
                                        'day'       => $day,
                                        'month'     => $month,
                                        'year'      => $year,
                                        'nofmodule' => 5427279
                                    ];
                                }

                                if (customCompute($automationTMember) && isset($automationTMember[$aTstudent->studentID]) && ($automationTMember[$aTstudent->studentID] > 0 && !isset($allRecord[872677678][$aTstudent->studentID][$month][$year]))) {
                                    $mainInvoiceArray[] = [
                                        'maininvoiceschoolyearID' => $this->data['siteinfos']->school_year,
                                        'maininvoiceclassesID'    => $aTstudent->classesID,
                                        'maininvoicestudentID'    => $aTstudent->studentID,
                                        'maininvoicestatus'       => 0,
                                        'maininvoiceuserID'       => 1,
                                        'maininvoiceusertypeID'   => 1,
                                        'maininvoiceuname'        => null,
                                        'maininvoicedate'         => date("Y-m-d"),
                                        'maininvoicecreate_date'  => date('Y-m-d'),
                                        'maininvoiceday'          => date('d'),
                                        'maininvoicemonth'        => date('m'),
                                        'maininvoiceyear'         => date('Y'),
                                        'maininvoicedeleted_at'   => 1
                                    ];
                                    $array[] = [
                                        'schoolyearID' => $this->data['siteinfos']->school_year,
                                        'classesID'    => $aTstudent->classesID,
                                        'studentID'    => $aTstudent->studentID,
                                        'feetypeID'    => customCompute($transportFeetype) ? $transportFeetype->feetypesID : 0,
                                        'feetype'      => customCompute($transportFeetype) ? $transportFeetype->feetypes : 0,
                                        'amount'       => (int) $automationTMember[$aTstudent->studentID],
                                        'discount'     => 0,
                                        'paidstatus'   => 0,
                                        'userID'       => 1,
                                        'usertypeID'   => 1,
                                        'uname'        => $superAdmin->name,
                                        'date'         => date("Y-m-d"),
                                        'create_date'  => date('Y-m-d'),
                                        'day'          => date('d'),
                                        'month'        => date('m'),
                                        'year'         => date('Y'),
                                        'deleted_at'   => 1
                                    ];
                                    $autoRecArray[] = [
                                        'studentID' => $aTstudent->studentID,
                                        'date'      => $date,
                                        'day'       => $day,
                                        'month'     => $month,
                                        'year'      => $year,
                                        'nofmodule' => 872677678
                                    ];
                                }

                                if (customCompute($automationHMember) && isset($automationHMember[$aTstudent->studentID]) && ($automationHMember[$aTstudent->studentID] > 0 && !isset($allRecord[467835][$aTstudent->studentID][$month][$year]))) {
                                    $mainInvoiceArray[] = [
                                        'maininvoiceschoolyearID' => $this->data['siteinfos']->school_year,
                                        'maininvoiceclassesID'    => $aTstudent->classesID,
                                        'maininvoicestudentID'    => $aTstudent->studentID,
                                        'maininvoicestatus'       => 0,
                                        'maininvoiceuserID'       => 1,
                                        'maininvoiceusertypeID'   => 1,
                                        'maininvoiceuname'        => null,
                                        'maininvoicedate'         => date("Y-m-d"),
                                        'maininvoicecreate_date'  => date('Y-m-d'),
                                        'maininvoiceday'          => date('d'),
                                        'maininvoicemonth'        => date('m'),
                                        'maininvoiceyear'         => date('Y'),
                                        'maininvoicedeleted_at'   => 1
                                    ];
                                    $array[] = [
                                        'schoolyearID' => $this->data['siteinfos']->school_year,
                                        'classesID'    => $aTstudent->classesID,
                                        'studentID'    => $aTstudent->studentID,
                                        'feetypeID'    => customCompute($hostelFeetype) ? $hostelFeetype->feetypesID : null,
                                        'feetype'      => customCompute($hostelFeetype) ? $hostelFeetype->feetypes : null,
                                        'amount'       => (int) $automationHMember[$aTstudent->studentID],
                                        'discount'     => 0,
                                        'paidstatus'   => 0,
                                        'userID'       => 1,
                                        'usertypeID'   => 1,
                                        'uname'        => $superAdmin->name,
                                        'date'         => date("Y-m-d"),
                                        'create_date'  => date('Y-m-d'),
                                        'day'          => date('d'),
                                        'month'        => date('m'),
                                        'year'         => date('Y'),
                                        'deleted_at'   => 1
                                    ];
                                    $autoRecArray[] = [
                                        'studentID' => $aTstudent->studentID,
                                        'date'      => $date,
                                        'day'       => $day,
                                        'month'     => $month,
                                        'year'      => $year,
                                        'nofmodule' => 467835
                                    ];
                                }
                            }
                        }

                        if (customCompute($mainInvoiceArray)) {
                            $count   = customCompute($mainInvoiceArray);
                            $firstID = $this->maininvoice_m->insert_batch_maininvoice($mainInvoiceArray);
                            $lastID  = $firstID + ($count - 1);

                            if ($lastID >= $firstID) {
                                $j = 0;
                                for ($i = $firstID; $i <= $lastID; $i++) {
                                    $array[$j]['maininvoiceID'] = $i;
                                    $j++;
                                }
                            }

                            if (customCompute($array)) {
                                $this->invoice_m->insert_batch_invoice($array);
                            }

                            if (customCompute($autoRecArray)) {
                                $this->automation_rec_m->insert_batch_automation_rec($autoRecArray);
                            }

                            $this->automation_shudulu_m->insert_automation_shudulu([
                                'date'  => $date,
                                'day'   => $day,
                                'month' => $month,
                                'year'  => $year
                            ]);
                        }
                    }
                } else {
                    $this->automation_shudulu_m->insert_automation_shudulu([
                        'date'  => $date,
                        'day'   => $day,
                        'month' => $month,
                        'year'  => $year
                    ]);
                }
            }
        }
        /* Automation Close */
    }

    private function _getAllRec($arrays)
    {
        $returnArray = [];
        if (customCompute($arrays)) {
            foreach ($arrays as $key => $array) {
                $returnArray[$array->nofmodule][$array->studentID][$array->month][$array->year] = 'Yes';
            }
        }
        return $returnArray;
    }

    public function index()
    {
        $this->data['headerassets'] = [
            'css' => [
                'assets/hatchers/hatchers.css',
                'assets/fullcalendar/lib/cupertino/jquery-ui.min.css',
                'assets/fullcalendar/fullcalendar.css',
            ],
            'js'  => [
                'assets/highcharts/highcharts.js',
                'assets/highcharts/highcharts-more.js',
                'assets/highcharts/data.js',
                'assets/highcharts/drilldown.js',
                'assets/highcharts/exporting.js',
                'assets/fullcalendar/lib/jquery-ui.min.js',
                'assets/fullcalendar/lib/moment.min.js',
                'assets/fullcalendar/fullcalendar.min.js',
            ]
        ];

        if (in_array((int) $this->session->userdata('usertypeID'), [1, 2, 3], true)) {
            $this->_hatchersFounderDashboard();
            $this->_hatchersMentorDashboard();
            $this->_hatchersHome();
            $this->data["subview"] = "dashboard/hatchers_home";
            $this->load->view('_layout_hatchers', $this->data);
            return;
        }

        $this->_tails();
        $this->_attendanceGraph();
        $this->_incomeExpenseGraph();
        $this->_visitorGraph();
        $this->_profile();
        $this->_hatchersFounderDashboard();
        $this->_hatchersMentorDashboard();

        if ((config_item('demo') === false) && ($this->data['siteinfos']->auto_update_notification == 1) && ($this->session->userdata('usertypeID') == 1) && ($this->session->userdata('loginuserID') == 1)) {
            $this->data['versionChecking'] = $this->session->userdata('updatestatus') === null ? $this->_checkUpdate() : 'none';
        } else {
            $this->data['versionChecking'] = 'none';
        }

        $this->data["subview"] = "dashboard/index";
        $this->load->view('_layout_main', $this->data);
    }

    private function _hatchersHome()
    {
        $usertypeID = (int) $this->session->userdata('usertypeID');
        $userID = (int) $this->session->userdata('loginuserID');
        $name = (string) $this->session->userdata('name');
        $calendarEvents = [];
        $stats = [];
        $cards = [];
        $spotlight = [];
        $headline = 'Welcome back ' . $name;
        $subheadline = 'Here is what is moving right now.';

        if ($usertypeID === 3) {
            $hatchersData = isset($this->data['hatchers']) ? $this->data['hatchers'] : [];
            $meetings = isset($hatchersData['meetings']) ? $hatchersData['meetings'] : [];
            $learning = isset($hatchersData['learning']) ? $hatchersData['learning'] : [];
            $tasks = isset($hatchersData['tasks']) ? $hatchersData['tasks'] : [];
            $calendarEvents = isset($hatchersData['calendar_events']) ? $hatchersData['calendar_events'] : [];
            $openTasks = 0;

            if (customCompute($tasks)) {
                foreach ($tasks as $task) {
                    if ((int) $task->status === 0) {
                        $openTasks++;
                    }
                }
            }

            $stats = [
                ['label' => 'Open Tasks', 'value' => $openTasks, 'copy' => 'Clear weekly action items with your mentor.'],
                ['label' => 'Meetings', 'value' => customCompute($meetings) ? count($meetings) : 0, 'copy' => 'Upcoming mentoring sessions on your calendar.'],
                ['label' => 'Lessons', 'value' => customCompute($learning) ? count($learning) : 0, 'copy' => 'Assigned learning sessions and resources.'],
            ];

            $subheadline = 'Here is what is on for you this week.';
        } elseif ($usertypeID === 2) {
            $mentorData = isset($this->data['hatchers_mentor']) ? $this->data['hatchers_mentor'] : ['founders' => []];
            $founders = isset($mentorData['founders']) ? $mentorData['founders'] : [];
            $founderSummaries = isset($mentorData['founder_summaries']) ? $mentorData['founder_summaries'] : [];
            $meetings = isset($mentorData['upcoming_meetings']) ? $mentorData['upcoming_meetings'] : [];
            $openTasks = isset($mentorData['open_tasks']) ? (int) $mentorData['open_tasks'] : 0;
            $completedTasks = isset($mentorData['completed_tasks']) ? (int) $mentorData['completed_tasks'] : 0;
            $pendingRequests = isset($mentorData['pending_requests']) ? (int) $mentorData['pending_requests'] : 0;
            $activeMilestones = isset($mentorData['active_milestones']) ? (int) $mentorData['active_milestones'] : 0;

            if (customCompute($meetings)) {
                foreach ($meetings as $meeting) {
                    if (!empty($meeting['starts_at'])) {
                        $calendarEvents[] = [
                            'title' => !empty($meeting['title']) ? $meeting['title'] : 'Mentoring session',
                            'start' => $meeting['starts_at'],
                        ];
                    }
                }
            }

            $stats = [
                ['label' => 'Assigned Founders', 'value' => customCompute($founders) ? count($founders) : 0, 'copy' => 'Founders currently under your guidance.'],
                ['label' => 'Open Tasks', 'value' => $openTasks, 'copy' => 'Weekly execution still in progress across your founders.'],
                ['label' => 'Completed Tasks', 'value' => $completedTasks, 'copy' => 'Tasks already completed across your current founder portfolio.'],
            ];

            if (customCompute($meetings)) {
                foreach (array_slice($meetings, 0, 5) as $meeting) {
                    $cards[] = [
                        'title' => !empty($meeting['title']) ? $meeting['title'] : 'Mentoring session',
                        'copy' => $meeting['founder_name'] . (!empty($meeting['request_status']) ? ' • ' . ucfirst($meeting['request_status']) : ''),
                        'action' => !empty($meeting['starts_at']) ? date('M j, g:ia', strtotime($meeting['starts_at'])) : 'Calendar item',
                        'link' => 'mentoring/index?founder_id=' . (int) $meeting['founder_id']
                    ];
                }
            }

            $spotlight = [
                ['label' => 'Execution', 'title' => 'Create tasks and milestones', 'copy' => 'Open a founder workspace to plan weekly work and keep momentum visible.', 'action' => 'Open mentoring', 'link' => 'mentoring/index'],
                ['label' => 'Learning', 'title' => 'Assign lessons and resources', 'copy' => 'Upload PDFs, add YouTube links, and curate articles.', 'action' => 'Open learning', 'link' => 'learningplan/index'],
                ['label' => 'Requests', 'title' => 'Respond fast to founders', 'copy' => $pendingRequests > 0 ? $pendingRequests . ' meeting requests are waiting for a response.' : 'No pending meeting requests right now.', 'action' => 'Review requests', 'link' => 'mentoring/index'],
                ['label' => 'Milestones', 'title' => 'Track longer-term progress', 'copy' => $activeMilestones . ' active milestones are currently shaping founder progress.', 'action' => 'View founder portfolio', 'link' => 'dashboard/index'],
            ];

            $subheadline = 'Your founder relationships, requests, and scheduled work at a glance.';
        } elseif ($usertypeID === 1) {
            $founders = $this->student_m->get_order_by_student();
            $mentors = $this->teacher_m->get_teacher();
            $assignments = $this->mentor_founder_m->get_order_by_mentor_founder(['status' => 1]);
            $meetings = $this->db->table_exists('founder_meetings') ? $this->founder_meeting_m->get_order_by_founder_meeting() : [];

            if (customCompute($meetings)) {
                foreach (array_slice($meetings, 0, 20) as $meeting) {
                    if (!empty($meeting->starts_at)) {
                        $calendarEvents[] = [
                            'title' => !empty($meeting->title) ? $meeting->title : 'Mentoring session',
                            'start' => $meeting->starts_at,
                        ];
                    }
                }
            }

            $stats = [
                ['label' => 'Founders', 'value' => customCompute($founders) ? count($founders) : 0, 'copy' => 'Active founder accounts in the LMS.'],
                ['label' => 'Mentors', 'value' => customCompute($mentors) ? count($mentors) : 0, 'copy' => 'Mentors available to guide founders.'],
                ['label' => 'Assignments', 'value' => customCompute($assignments) ? count($assignments) : 0, 'copy' => 'Active founder-to-mentor relationships.'],
            ];

            if (customCompute($founders)) {
                foreach (array_slice($founders, 0, 5) as $founder) {
                    $cards[] = [
                        'title' => $founder->name,
                        'copy' => !empty($founder->email) ? $founder->email : 'Founder account ready for review.',
                        'action' => 'Manage profile',
                        'link' => 'hatchersadmin/edit_founder/' . $founder->studentID
                    ];
                }
            }

            $spotlight = [
                ['label' => 'Onboarding', 'title' => 'Create founder access', 'copy' => 'Create founder and mentor accounts without school-era fields.', 'action' => 'Open profiles', 'link' => 'hatchersadmin/profiles'],
                ['label' => 'Assignments', 'title' => 'Match founders to mentors', 'copy' => 'Control who guides whom across the full six-month journey.', 'action' => 'Open assignments', 'link' => 'hatchersadmin/assignments'],
                ['label' => 'AI', 'title' => 'Configure the assistant', 'copy' => 'Manage OpenAI prompts and keep AI aligned with mentor guidance.', 'action' => 'Open AI settings', 'link' => 'hatchersadmin/ai'],
            ];

            $subheadline = 'Run onboarding, assignments, and AI configuration from one control surface.';
        }

        $this->data['hatchers_home'] = [
            'role' => ($usertypeID === 1 ? 'admin' : ($usertypeID === 2 ? 'mentor' : 'founder')),
            'headline' => $headline,
            'subheadline' => $subheadline,
            'stats' => $stats,
            'cards' => $cards,
            'spotlight' => $spotlight,
            'founder_summaries' => isset($founderSummaries) ? $founderSummaries : [],
            'upcoming_meetings' => isset($meetings) ? $meetings : [],
        ];
        $this->data['hatchers_shell'] = $this->hatchers_shell_m->build('home', $calendarEvents);
    }

    private function _hatchersFounderDashboard()
    {
        $usertypeID = $this->session->userdata('usertypeID');
        if ($usertypeID != 3) {
            return;
        }

        $founderID = $this->session->userdata('loginuserID');
        $mentorAssignment = $this->mentor_founder_m->get_single_mentor_founder([
            'founder_id' => $founderID,
            'status'     => 1
        ]);

        $mentor = null;
        if (customCompute($mentorAssignment)) {
            $mentor = $this->teacher_m->get_single_teacher(['teacherID' => $mentorAssignment->mentor_id]);
        }

        $meetings   = $this->founder_meeting_m->get_order_by_founder_meeting(['founder_id' => $founderID]);
        $learning   = $this->founder_learning_m->get_order_by_founder_learning(['founder_id' => $founderID]);
        $tasks      = $this->founder_task_m->get_order_by_founder_task(['founder_id' => $founderID]);
        $milestones = $this->milestone_meta_m->get_order_by_milestone_meta(['founder_id' => $founderID]);

        $milestoneMap = [];
        $weeklyScopeTotal = 0;
        $weeklyScopeDone = 0;
        $weekStart = strtotime('monday this week');
        $weekEnd = strtotime('sunday this week 23:59:59');
        if (customCompute($milestones)) {
            foreach ($milestones as $milestone) {
                $milestoneMap[$milestone->milestone_meta_id] = $milestone;
                $dueTs = !empty($milestone->due_date) ? strtotime($milestone->due_date) : 0;
                if (($dueTs >= $weekStart && $dueTs <= $weekEnd) || empty($milestone->due_date)) {
                    $weeklyScopeTotal++;
                    if ((int) $milestone->status === 1) {
                        $weeklyScopeDone++;
                    }
                }
            }
        }

        $calendarEvents = [];
        if (customCompute($meetings)) {
            foreach ($meetings as $meeting) {
                $calendarEvents[] = [
                    'title' => 'Mentoring',
                    'start' => $meeting->starts_at
                ];
            }
        }
        if (customCompute($learning)) {
            foreach ($learning as $lesson) {
                if (!empty($lesson->starts_at)) {
                    $calendarEvents[] = [
                        'title' => 'Learning',
                        'start' => $lesson->starts_at
                    ];
                }
            }
        }
        if (customCompute($tasks)) {
            foreach ($tasks as $task) {
                if (!empty($task->due_date)) {
                    $calendarEvents[] = [
                        'title' => 'Task Due',
                        'start' => $task->due_date
                    ];
                }
                $dueTs = !empty($task->due_date) ? strtotime($task->due_date) : 0;
                if (($dueTs >= $weekStart && $dueTs <= $weekEnd) || empty($task->due_date)) {
                    $weeklyScopeTotal++;
                    if ((int) $task->status === 1) {
                        $weeklyScopeDone++;
                    }
                }
            }
        }

        $weeklyProgress = $weeklyScopeTotal > 0 ? (int) round(($weeklyScopeDone / $weeklyScopeTotal) * 100) : 0;

        $this->data['hatchers'] = [
            'mentor'          => $mentor,
            'meetings'        => $meetings,
            'learning'        => $learning,
            'tasks'           => $tasks,
            'milestones'      => $milestones,
            'calendar_events' => $calendarEvents,
            'milestone_map'   => $milestoneMap,
            'weekly_progress' => $weeklyProgress,
            'weekly_scope_total' => $weeklyScopeTotal,
            'weekly_scope_done' => $weeklyScopeDone,
            'ai_history'      => $this->db->table_exists('hatcher_ai_conversations')
                ? $this->db->order_by('hatcher_ai_conversation_id', 'ASC')->limit(12)->get_where('hatcher_ai_conversations', ['founder_id' => $founderID])->result()
                : []
        ];
    }

    private function _hatchersMentorDashboard()
    {
        $usertypeID = $this->session->userdata('usertypeID');
        if ($usertypeID != 2) {
            return;
        }

        $mentorID = $this->session->userdata('loginuserID');
        $assignments = $this->mentor_founder_m->get_order_by_mentor_founder([
            'mentor_id' => $mentorID,
            'status'    => 1
        ]);

        $founders = [];
        $founderSummaries = [];
        $upcomingMeetings = [];
        $openTasks = 0;
        $completedTasks = 0;
        $pendingRequests = 0;
        $activeMilestones = 0;
        if (customCompute($assignments)) {
            foreach ($assignments as $assignment) {
                $this->db->select('student.studentID, student.name, student.photo, student.email, student.phone, student.registerNO, studentextend.remarks as company_brief');
                $this->db->from('student');
                $this->db->join('studentextend', 'studentextend.studentID = student.studentID', 'LEFT');
                $this->db->where('student.studentID', $assignment->founder_id);
                $query = $this->db->get();
                $student = $query->row();
                if (customCompute($student)) {
                    $founders[] = $student;

                    $tasks = $this->founder_task_m->get_order_by_founder_task(['founder_id' => $student->studentID]);
                    $meetings = $this->founder_meeting_m->get_order_by_founder_meeting(['founder_id' => $student->studentID]);
                    $milestones = $this->milestone_meta_m->get_order_by_milestone_meta(['founder_id' => $student->studentID]);
                    $founderOpenTasks = 0;
                    $founderCompletedTasks = 0;
                    $founderOverdueTasks = 0;
                    $nextMeeting = null;

                    if (customCompute($tasks)) {
                        foreach ($tasks as $task) {
                            if ((int) $task->status === 1) {
                                $founderCompletedTasks++;
                                $completedTasks++;
                            } else {
                                $founderOpenTasks++;
                                $openTasks++;
                                if (!empty($task->due_date) && strtotime($task->due_date) < time()) {
                                    $founderOverdueTasks++;
                                }
                            }
                        }
                    }

                    if (customCompute($milestones)) {
                        foreach ($milestones as $milestone) {
                            if ((int) $milestone->status !== 1) {
                                $activeMilestones++;
                            }
                        }
                    }

                    if (customCompute($meetings)) {
                        foreach ($meetings as $meeting) {
                            if ((string) $meeting->request_status === 'requested') {
                                $pendingRequests++;
                            }

                            if (!empty($meeting->starts_at)) {
                                $upcomingMeetings[] = [
                                    'founder_id' => (int) $student->studentID,
                                    'founder_name' => $student->name,
                                    'title' => !empty($meeting->title) ? $meeting->title : 'Mentoring session',
                                    'starts_at' => $meeting->starts_at,
                                    'request_status' => $meeting->request_status,
                                ];

                                $meetingTs = strtotime($meeting->starts_at);
                                if ($meetingTs && $meetingTs >= time() && ($nextMeeting === null || $meetingTs < strtotime((string) $nextMeeting->starts_at))) {
                                    $nextMeeting = $meeting;
                                }
                            }
                        }
                    }

                    $taskTotal = $founderOpenTasks + $founderCompletedTasks;
                    $progressPercent = $taskTotal > 0 ? (int) round(($founderCompletedTasks / $taskTotal) * 100) : 0;
                    $founderSummaries[] = [
                        'founder_id' => (int) $student->studentID,
                        'name' => $student->name,
                        'email' => $student->email,
                        'company_brief' => isset($student->company_brief) ? (string) $student->company_brief : '',
                        'open_tasks' => $founderOpenTasks,
                        'completed_tasks' => $founderCompletedTasks,
                        'overdue_tasks' => $founderOverdueTasks,
                        'milestones' => customCompute($milestones) ? count($milestones) : 0,
                        'progress_percent' => $progressPercent,
                        'next_meeting' => customCompute($nextMeeting) ? $nextMeeting->starts_at : null,
                    ];
                }
            }
        }

        if (customCompute($upcomingMeetings)) {
            usort($upcomingMeetings, function ($a, $b) {
                return strtotime((string) $a['starts_at']) - strtotime((string) $b['starts_at']);
            });
        }

        $this->data['hatchers_mentor'] = [
            'founders' => $founders,
            'founder_summaries' => $founderSummaries,
            'upcoming_meetings' => array_slice($upcomingMeetings, 0, 8),
            'open_tasks' => $openTasks,
            'completed_tasks' => $completedTasks,
            'pending_requests' => $pendingRequests,
            'active_milestones' => $activeMilestones,
        ];
    }

    private function _tails()
    {
        $schoolyearID = $this->session->userdata('defaultschoolyearID');
        $loginuserID  = $this->session->userdata('loginuserID');

        $students    = $this->studentrelation_m->get_order_by_student(['srschoolyearID' => $schoolyearID]);
        $classes     = pluck($this->classes_m->get_classes(), 'obj', 'classesID');
        $teachers    = $this->teacher_m->get_teacher();
        $parents     = $this->parents_m->get_parents();
        $books       = $this->book_m->get_book();
        $feetypes    = $this->feetypes_m->get_feetypes();
        $lmembers    = $this->lmember_m->get_lmember();
        $events      = $this->event_m->get_order_by_event(['schoolyearID' => $schoolyearID]);
        $holidays    = $this->holiday_m->get_order_by_holiday(['schoolyearID' => $schoolyearID]);
        $visitors    = $this->visitorinfo_m->get_order_by_visitorinfo(['schoolyearID' => $schoolyearID]);
        $allmenu     = pluck($this->menu_m->get_order_by_menu(), 'icon', 'link');
        $allmenulang = pluck($this->menu_m->get_order_by_menu(), 'menuName', 'link');

        if ($this->session->userdata('usertypeID') == 3) {
            $getLoginStudent = $this->studentrelation_m->get_single_student([
                'srstudentID'    => $loginuserID,
                'srschoolyearID' => $schoolyearID
            ]);
            if (customCompute($getLoginStudent)) {
                $subjects = $this->subject_m->get_order_by_subject(['classesID' => $getLoginStudent->srclassesID]);
                $invoices = $this->maininvoice_m->get_order_by_maininvoice([
                    'maininvoicestudentID'    => $getLoginStudent->srstudentID,
                    'maininvoiceschoolyearID' => $schoolyearID,
                    'maininvoicedeleted_at'   => 1
                ]);
                $lmember  = $this->lmember_m->get_single_lmember(['studentID' => $getLoginStudent->srstudentID]);
                if (customCompute($lmember)) {
                    $issues = $this->issue_m->get_order_by_issue(["lID" => $lmember->lID, 'return_date' => null]);
                } else {
                    $issues = [];
                }
            } else {
                $invoices = [];
                $subjects = [];
                $issues   = [];
            }
        } else {
            $invoices = $this->maininvoice_m->get_order_by_maininvoice([
                'maininvoiceschoolyearID' => $schoolyearID,
                'maininvoicedeleted_at'   => 1
            ]);
            $subjects = $this->subject_m->get_subject();
            $issues   = $this->issue_m->get_order_by_issue(['return_date' => null]);
        }

        $this->data['dashboardWidget']['students']    = customCompute($students);
        $this->data['dashboardWidget']['classes']     = customCompute($classes);
        $this->data['dashboardWidget']['teachers']    = customCompute($teachers);
        $this->data['dashboardWidget']['parents']     = customCompute($parents);
        $this->data['dashboardWidget']['subjects']    = customCompute($subjects);
        $this->data['dashboardWidget']['books']       = customCompute($books);
        $this->data['dashboardWidget']['feetypes']    = customCompute($feetypes);
        $this->data['dashboardWidget']['lmembers']    = customCompute($lmembers);
        $this->data['dashboardWidget']['events']      = customCompute($events);
        $this->data['dashboardWidget']['issues']      = customCompute($issues);
        $this->data['dashboardWidget']['holidays']    = customCompute($holidays);
        $this->data['dashboardWidget']['invoices']    = customCompute($invoices);
        $this->data['dashboardWidget']['visitors']    = customCompute($visitors);
        $this->data['dashboardWidget']['allmenu']     = $allmenu;
        $this->data['dashboardWidget']['allmenulang'] = $allmenulang;

        $this->data['notices']  = $this->notice_m->get_order_by_notice(['schoolyearID' => $schoolyearID]);
        $this->data['holidays'] = $holidays;
        $this->data['events']   = $events;
        $this->data['classes']  = $classes;
    }

    private function _attendanceGraph()
    {
        $schoolyearID                   = $this->session->userdata('defaultschoolyearID');
        $attendanceSystem               = $this->data['siteinfos']->attendance;
        $this->data['attendanceSystem'] = $attendanceSystem;

        if ($attendanceSystem != 'subject') {
            $attendances = $this->sattendance_m->get_order_by_attendance([
                'schoolyearID' => $schoolyearID,
                'monthyear'    => date('m-Y')
            ]);

            $classWiseAttendance = [];
            foreach ($attendances as $attendance) {
                for ($i = 1; $i <= 31; $i++) {
                    if ($i > date('d')) {
                        break;
                    }
                    $date = 'a' . $i;

                    if (!isset($classWiseAttendance[$attendance->classesID][$i]['P'])) {
                        $classWiseAttendance[$attendance->classesID][$i]['P'] = 0;
                    }

                    if (!isset($classWiseAttendance[$attendance->classesID][$i]['A'])) {
                        $classWiseAttendance[$attendance->classesID][$i]['A'] = 0;
                    }

                    if ($attendance->$date == 'P' || $attendance->$date == 'L' || $attendance->$date == 'LE') {
                        $classWiseAttendance[$attendance->classesID][$i]['P']++;
                    } else {
                        $classWiseAttendance[$attendance->classesID][$i]['A']++;
                    }
                }
            }

            $todaysAttendance = [];
            foreach ($classWiseAttendance as $key => $value) {
                $todaysAttendance[$key] = $value[(int) date('d')];
            }

            $this->data['classWiseAttendance'] = $classWiseAttendance;
            $this->data['todaysAttendance']    = $todaysAttendance;
        } else {
            $subjectWiseAttendance = [];
            $attendances           = $this->subjectattendance_m->get_order_by_sub_attendance([
                'schoolyearID' => $schoolyearID,
                'monthyear'    => date('m-Y')
            ]);

            foreach ($attendances as $attendance) {
                for ($i = 1; $i <= 31; $i++) {
                    if ($i > date('d')) {
                        break;
                    }
                    $date = 'a' . $i;

                    if (!isset($subjectWiseAttendance[$attendance->classesID][$attendance->subjectID][$i]['P'])) {
                        $subjectWiseAttendance[$attendance->classesID][$attendance->subjectID][$i]['P'] = 0;
                    }

                    if (!isset($subjectWiseAttendance[$attendance->classesID][$attendance->subjectID][$i]['A'])) {
                        $subjectWiseAttendance[$attendance->classesID][$attendance->subjectID][$i]['A'] = 0;
                    }

                    if ($attendance->$date == 'P' || $attendance->$date == 'L' || $attendance->$date == 'LE') {
                        $subjectWiseAttendance[$attendance->classesID][$attendance->subjectID][$i]['P']++;
                    } else {
                        $subjectWiseAttendance[$attendance->classesID][$attendance->subjectID][$i]['A']++;
                    }
                }
            }

            $todaysSubjectWiseAttendance = [];
            foreach ($subjectWiseAttendance as $class => $subject) {
                foreach ($subject as $key => $value) {
                    if (!isset($todaysSubjectWiseAttendance[$class])) {
                        $todaysSubjectWiseAttendance[$class]['P'] = 0;
                        $todaysSubjectWiseAttendance[$class]['A'] = 0;
                    }
                    $todaysSubjectWiseAttendance[$class]['P'] += $value[(int) date('d')]['P'];
                    $todaysSubjectWiseAttendance[$class]['A'] += $value[(int) date('d')]['A'];
                }
            }

            $this->data['subjectWiseAttendance']       = $subjectWiseAttendance;
            $this->data['todaysSubjectWiseAttendance'] = $todaysSubjectWiseAttendance;
        }
    }

    private function _incomeExpenseGraph()
    {
        $months = [
            1 => 'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July ',
            'August',
            'September',
            'October',
            'November',
            'December',
        ];

        $monthArray   = [];
        $schoolyearID = $this->session->userdata('defaultschoolyearID');
        $schoolyear   = $this->schoolyear_m->get_obj_schoolyear($schoolyearID);
        if (customCompute($schoolyear)) {
            $monthStart = abs($schoolyear->startingmonth);
            if ($schoolyear->startingyear == $schoolyear->endingyear) {
                $monthLimit = (($schoolyear->endingmonth - $schoolyear->startingmonth) + 1);
            } else {
                $monthLimit = ($schoolyear->startingmonth + $schoolyear->endingmonth + 1);
            }

            $n = $monthStart;
            for ($k = 1; $k <= $monthLimit; $k++) {
                $monthArray[$n] = $months[$n];
                $n++;
                if ($n > 12) {
                    $n = 1;
                }
            }
            $months = $monthArray;
        }

        $incomes  = $this->income_m->get_order_by_income(['schoolyearID' => $schoolyearID]);
        $payments = $this->payment_m->get_order_by_payment([
            'schoolyearID'  => $schoolyearID,
            'paymentamount' => null
        ]);

        $expenses     = $this->expense_m->get_order_by_expense(['schoolyearID' => $schoolyearID]);
        $makepayments = $this->make_payment_m->get_order_by_make_payment(['schoolyearID' => $schoolyearID]);


        $incomeMonthAndDay = [];
        $incomeMonthTotal  = [];
        if (customCompute($incomes)) {
            foreach ($incomes as $incomeKey => $income) {
                if (!isset($incomeMonthAndDay[(int) $income->incomemonth][$income->incomeday])) {
                    $incomeMonthAndDay[(int) $income->incomemonth][(string) $income->incomeday] = 0;
                }

                $incomeMonthAndDay[(int) $income->incomemonth][(string) $income->incomeday] += $income->amount;
                if (!isset($incomeMonthTotal[(int) $income->incomemonth])) {
                    $incomeMonthTotal[(int) $income->incomemonth] = 0;
                }
                $incomeMonthTotal[(int) $income->incomemonth] += $income->amount;
            }
        }

        if (customCompute($payments)) {
            foreach ($payments as $paymentKey => $payment) {
                if (!isset($incomeMonthAndDay[(int) $payment->paymentmonth][$payment->paymentday])) {
                    $incomeMonthAndDay[(int) $payment->paymentmonth][(string) $payment->paymentday] = 0;
                }

                $incomeMonthAndDay[(int) $payment->paymentmonth][(string) $payment->paymentday] += $payment->paymentamount;
                if (!isset($incomeMonthTotal[(int) $payment->paymentmonth])) {
                    $incomeMonthTotal[(int) $payment->paymentmonth] = 0;
                }
                $incomeMonthTotal[(int) $payment->paymentmonth] += $payment->paymentamount;
            }
        }

        $expenseMonthAndDay = [];
        $expenseMonthTotal  = [];
        if (customCompute($expenses)) {
            foreach ($expenses as $expenseKey => $expense) {
                if (!isset($expenseMonthAndDay[(int) $expense->expensemonth][$expense->expenseday])) {
                    $expenseMonthAndDay[(int) $expense->expensemonth][(string) $expense->expenseday] = 0;
                }

                $expenseMonthAndDay[(int) $expense->expensemonth][(string) $expense->expenseday] += $expense->amount;
                if (!isset($expenseMonthTotal[(int) $expense->expensemonth])) {
                    $expenseMonthTotal[(int) $expense->expensemonth] = 0;
                }
                $expenseMonthTotal[(int) $expense->expensemonth] += $expense->amount;
            }
        }

        if (customCompute($makepayments)) {
            foreach ($makepayments as $makepaymentKey => $makepayment) {
                $makepaymentDay   = date('d', strtotime((string) $makepayment->create_date));
                $makepaymentMonth = date('m', strtotime((string) $makepayment->create_date));
                if (!isset($expenseMonthAndDay[(int) $makepaymentMonth][$makepaymentDay])) {
                    $expenseMonthAndDay[(int) $makepaymentMonth][(string) $makepaymentDay] = 0;
                }

                $expenseMonthAndDay[(int) $makepaymentMonth][(string) $makepaymentDay] += $makepayment->payment_amount;
                if (!isset($expenseMonthTotal[(int) $makepaymentMonth])) {
                    $expenseMonthTotal[(int) $makepaymentMonth] = 0;
                }
                $expenseMonthTotal[(int) $makepaymentMonth] += $makepayment->payment_amount;
            }
        }

        $this->data['months']             = $months;
        $this->data['incomeMonthAndDay']  = $incomeMonthAndDay;
        $this->data['incomeMonthTotal']   = $incomeMonthTotal;
        $this->data['expenseMonthAndDay'] = $expenseMonthAndDay;
        $this->data['expenseMonthTotal']  = $expenseMonthTotal;
    }

    private function _visitorGraph()
    {
        $showChartVisitor  = [];
        $currentDate       = strtotime(date('Y-m-d H:i:s'));
        $previousSevenDate = strtotime(date('Y-m-d 00:00:00', strtotime('-7 days')));
        $visitors          = $this->loginlog_m->get_order_by_loginlog([
            'login <= ' => $currentDate,
            'login >= ' => $previousSevenDate
        ]);
        foreach ($visitors as $visitor) {
            $date = date('j M', $visitor->login);
            if (!isset($showChartVisitor[$date])) {
                $showChartVisitor[$date] = 0;
            }
            $showChartVisitor[$date]++;
        }
        $this->data['showChartVisitor'] = $showChartVisitor;
    }

    private function _profile()
    {
        $userTypeID             = $this->session->userdata('usertypeID');
        $loginUserID            = $this->session->userdata('loginuserID');
        $this->data['usertype'] = $this->session->userdata('usertype');

        if ($userTypeID == 1) {
            $this->data['user'] = $this->systemadmin_m->get_single_systemadmin(['systemadminID' => $loginUserID]);
        } elseif ($userTypeID == 2) {
            $this->data['user'] = $this->teacher_m->get_single_teacher(['teacherID' => $loginUserID]);
        } elseif ($userTypeID == 3) {
            $this->data['user'] = $this->studentrelation_m->general_get_single_student(['studentID' => $loginUserID]);
        } elseif ($userTypeID == 4) {
            $this->data['user'] = $this->parents_m->get_single_parents(['parentsID' => $loginUserID]);
        } else {
            $this->data['user'] = $this->user_m->get_single_user(['userID' => $loginUserID]);
        }
    }

    private function _checkUpdate()
    {
        $version = 'none';
        if ($this->session->userdata('usertypeID') == 1 && $this->session->userdata('loginuserID') == 1 && customCompute($postDatas = @$this->_postData())) {
            $versionChecking = $this->_versionChecking($postDatas);
            if ($versionChecking->status) {
                $version = $versionChecking->version;
            }
        }

        return $version;
    }

    private function _postData()
    {
        $postDatas = [];
        $this->load->model('update_m');
        $updates = $this->update_m->get_max_update();
        if (customCompute($updates)) {
            $postDatas = [
                'username'       => customCompute($this->data['siteinfos']) ? $this->data['siteinfos']->purchase_username : '',
                'purchasekey'    => customCompute($this->data['siteinfos']) ? $this->data['siteinfos']->purchase_code : '',
                'domainname'     => base_url(),
                'email'          => customCompute($this->data['siteinfos']) ? $this->data['siteinfos']->email : '',
                'currentversion' => $updates->version,
                'projectname'    => 'school',
            ];
        }

        return $postDatas;
    }

    private function _versionChecking($postDatas)
    {

        try {
            $result = [
                'status'  => false,
                'message' => 'Error',
                'version' => 'none'
            ];

            $postDataStrings = json_encode($postDatas);

            $guzzle = new Guzzle();
            $response = $guzzle->request($postDataStrings, $this->_versionCheckingUrl);
            $header      = explode(';', (string) $response->getHeader('Content-Type')[0]);
            $contentType = $header[0];
            if ($contentType == 'application/json') {
                $contents = $response->getBody()->getContents();
                $result     = json_decode((string) $contents);
                if ($result == null) {
                    $result = [
                        'status'  => true,
                        'version' => 'none'
                    ];
                }
                return (object) $result;
            }
            return (object) $result;
        } catch (Exception $e) {
            return (object) [
                'status'  => false,
                'message' => $e->getMessage(),
                'version' => 'none'
            ];
        }
    }

    public function update()
    {
        if ($this->session->userdata('usertypeID') == 1 && $this->session->userdata('loginuserID') == 1) {
            $this->session->set_userdata('updatestatus', true);
            redirect(base_url('update/autoupdate'));
        }
        redirect(base_url('dashboard/index'));
    }

    public function remind()
    {
        if ($this->session->userdata('usertypeID') == 1 && $this->session->userdata('loginuserID') == 1) {
            $this->session->set_userdata('updatestatus', false);
        }
        redirect(base_url('dashboard/index'));
    }

    public function getDayWiseAttendance()
    {
        $showChartData = [];
        if ($this->input->post('dayWiseAttendance')) {
            $dayWiseAttendance = json_decode((string) $this->input->post('dayWiseAttendance'), true);
            $type              = $this->input->post('type');
            foreach ($dayWiseAttendance as $key => $value) {
                $showChartData[$key] = $value[$type];
            }
        }
        echo json_encode($showChartData);
    }

    public function dayWiseExpenseOrIncome()
    {
        $type          = $this->input->post('type');
        $monthID       = $this->input->post('monthID');
        $schoolyearID  = $this->session->userdata('defaultschoolyearID');
        $showChartData = [];
        if ($type && $monthID) {
            $year = date('Y');

            $yearArray  = [];
            $schoolyear = $this->schoolyear_m->get_obj_schoolyear($schoolyearID);
            if (customCompute($schoolyear)) {
                $monthStart = abs($schoolyear->startingmonth);
                if ($schoolyear->startingyear == $schoolyear->endingyear) {
                    $monthLimit = (($schoolyear->endingmonth - $schoolyear->startingmonth) + 1);
                } else {
                    $monthLimit = ($schoolyear->startingmonth + $schoolyear->endingmonth + 1);
                }

                $n             = $monthStart;
                $endYearStatus = false;
                for ($k = 1; $k <= $monthLimit; $k++) {
                    if ($endYearStatus == false) {
                        $yearArray[$n] = $schoolyear->startingyear;
                    }

                    if ($endYearStatus) {
                        $yearArray[$n] = $schoolyear->endingyear;
                    }

                    $n++;
                    if ($n > 12) {
                        $n             = 1;
                        $endYearStatus = true;
                    }
                }
                $year = (isset($yearArray[abs($monthID)]) ? $yearArray[abs($monthID)] : date('Y'));
            }

            $days        = date('t', mktime(0, 0, 0, $monthID, 1, $year));
            $dayWiseData = json_decode((string) $this->input->post('dayWiseData'), true);
            for ($i = 1; $i <= $days; $i++) {
                if (!isset($dayWiseData[lzero($i)])) {
                    $showChartData[$i] = 0;
                } else {
                    $showChartData[$i] = isset($dayWiseData[lzero($i)]) ? $dayWiseData[lzero($i)] : 0;
                }
            }
        } else {
            for ($i = 1; $i <= 31; $i++) {
                $showChartData[$i] = 0;
            }
        }

        echo json_encode($showChartData);
    }

    public function getSubjectWiseAttendance()
    {
        $subjectWiseAttendance = json_decode((string) $this->input->post('subjectWiseAttendance'), true);
        $classID               = $this->input->post('classID');
        $data['subjects']      = pluck(
            $this->subject_m->get_order_by_subject(['classesID' => $classID]),
            'obj',
            'subjectID'
        );
        $present               = [];
        $absent                = [];
        foreach ($subjectWiseAttendance as $subjectID => $days) {
            foreach ($days as $key => $attendance) {
                if (!isset($present[$subjectID])) {
                    $present[$subjectID] = 0;
                }

                if (!isset($absent[$subjectID])) {
                    $absent[$subjectID] = 0;
                }

                $present[$subjectID] += $attendance['P'];
                $absent[$subjectID]  += $attendance['A'];
            }
        }

        $data['present']               = $present;
        $data['absent']                = $absent;
        $data['subjectWiseAttendance'] = $subjectWiseAttendance;
        echo json_encode($data);
    }
}
