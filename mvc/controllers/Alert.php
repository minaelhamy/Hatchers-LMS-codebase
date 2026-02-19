<?php if ( !defined('BASEPATH') ) {
    exit('No direct script access allowed');
}

    class Alert extends Admin_Controller
    {
        public $load;
        public $session;
        public $lang;
        public $uri;
        public $alert_m;
        public $conversation_m;
        public $input;
        public $notice_m;
        public $event_m;
        public $holiday_m;
        public $data;
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

        private $_alert = [];
        private $_userAlert = [];

        public function __construct()
        {
            parent::__construct();
            $this->load->model("alert_m");
            $this->load->model('notice_m');
            $this->load->model('event_m');
            $this->load->model('holiday_m');
            $this->load->model('conversation_m');
            $language = $this->session->userdata('lang');
            $this->lang->load('alert', $language);
        }

        public function index()
        {
            $type = htmlentities((string) escapeString($this->uri->segment(3)));
            $id   = htmlentities((string) escapeString($this->uri->segment(4)));
            if ( $type && (int) $id ) {
                $alert = $this->alert_m->get_single_alert([
                    'itemID'     => $id,
                    "userID"     => $this->session->userdata("loginuserID"),
                    'usertypeID' => $this->session->userdata('usertypeID'),
                    'itemname'   => $type
                ]);
                if ( !customCompute($alert) ) {
                    $this->alert_m->insert_alert([
                        'itemID'     => $id,
                        "userID"     => $this->session->userdata("loginuserID"),
                        'usertypeID' => $this->session->userdata('usertypeID'),
                        'itemname'   => $type
                    ]);
                }

                if ( $type == 'notice' ) {
                    if ( permissionChecker('notice_view') ) {
                        redirect(base_url('notice/view/' . $id));
                    } else {
                        $this->session->set_flashdata('error', $this->lang->line('alert_notice_permission'));
                        redirect(base_url('dashboard/index'));
                    }
                } elseif ( $type == 'message' ) {
                    $pluckMessage = pluck($this->alert_m->get_order_by_alert([
                        "userID"     => $this->session->userdata("loginuserID"),
                        'usertypeID' => $this->session->userdata('usertypeID'),
                        'itemname'   => 'message'
                    ]), 'itemname', 'itemID');

                    $messages = $this->conversation_m->get_conversation_msg_by_id($id);
                    if ( customCompute($messages) ) {
                        foreach ( $messages as $message ) {
                            if ( !isset($pluckMessage[ $message->msg_id ]) ) {
                                $this->alert_m->insert_alert([
                                    'itemID'     => $message->msg_id,
                                    "userID"     => $this->session->userdata("loginuserID"),
                                    'usertypeID' => $this->session->userdata('usertypeID'),
                                    'itemname'   => 'message'
                                ]);
                            }
                        }
                    }

                    if ( permissionChecker('conversation') ) {
                        redirect(base_url('conversation/view/' . $id));
                    } else {
                        $this->session->set_flashdata('error', $this->lang->line('alert_message_permission'));
                        redirect(base_url('dashboard/index'));
                    }
                } elseif ( $type == 'event' ) {
                    if ( permissionChecker('event_view') ) {
                        redirect(base_url('event/view/' . $id));
                    } else {
                        $this->session->set_flashdata('error', $this->lang->line('alert_event_permission'));
                        redirect(base_url('dashboard/index'));
                    }
                } elseif ( $type == 'holiday' ) {
                    if ( permissionChecker('holiday_view') ) {
                        redirect(base_url('holiday/view/' . $id));
                    } else {
                        $this->session->set_flashdata('error', $this->lang->line('alert_holiday_permission'));
                        redirect(base_url('dashboard/index'));
                    }
                }
            }
        }

        public function alert()
        {
            if ($this->session->userdata('loggedin') && $this->input->is_ajax_request()) {
                $schoolYearID = $this->session->userdata('defaultschoolyearID');
                $this->_userAlert();
                $this->_alertMessage();
                $this->_alertNotice($schoolYearID);
                $this->_alertEvent($schoolYearID);
                $this->_alertHoliday($schoolYearID);
                $alerts = $this->_alertOrder($this->_alert);
                $this->_alertMarkup($alerts);
            }
        }

        public function clear()
        {
            if (!$this->session->userdata('loggedin')) {
                redirect(base_url('signin/index'));
            }

            $schoolYearID = $this->session->userdata('defaultschoolyearID');
            $this->_userAlert();
            $this->_alertMessage();
            $this->_alertNotice($schoolYearID);
            $this->_alertEvent($schoolYearID);
            $this->_alertHoliday($schoolYearID);
            $alerts = $this->_alertOrder($this->_alert);

            $batch = [];
            foreach ($alerts as $alert) {
                $identity = $this->_alertIdentity($alert);
                if (customCompute($identity)) {
                    $batch[] = [
                        'itemID'     => $identity['itemID'],
                        'userID'     => $this->session->userdata("loginuserID"),
                        'usertypeID' => $this->session->userdata('usertypeID'),
                        'itemname'   => $identity['itemname']
                    ];
                }
            }

            if (customCompute($batch)) {
                $this->alert_m->insert_batch_alert($batch);
            }

            redirect(base_url('dashboard/index'));
        }

        public function read()
        {
            if (!$this->session->userdata('loggedin')) {
                redirect(base_url('signin/index'));
            }

            $type = htmlentities((string) escapeString($this->uri->segment(3)));
            $id   = htmlentities((string) escapeString($this->uri->segment(4)));
            if ($type && (int) $id) {
                $alert = $this->alert_m->get_single_alert([
                    'itemID'     => $id,
                    "userID"     => $this->session->userdata("loginuserID"),
                    'usertypeID' => $this->session->userdata('usertypeID'),
                    'itemname'   => $type
                ]);
                if (!customCompute($alert)) {
                    $this->alert_m->insert_alert([
                        'itemID'     => $id,
                        "userID"     => $this->session->userdata("loginuserID"),
                        'usertypeID' => $this->session->userdata('usertypeID'),
                        'itemname'   => $type
                    ]);
                }
            }
            redirect(base_url('dashboard/index'));
        }

        public function dismiss()
        {
            if (!$this->session->userdata('loggedin')) {
                $this->_json(['ok' => false]);
                return;
            }

            $type = htmlentities((string) escapeString($this->uri->segment(3)));
            $id   = htmlentities((string) escapeString($this->uri->segment(4)));
            if ($type && (int) $id) {
                $alert = $this->alert_m->get_single_alert([
                    'itemID'     => $id,
                    "userID"     => $this->session->userdata("loginuserID"),
                    'usertypeID' => $this->session->userdata('usertypeID'),
                    'itemname'   => $type
                ]);
                if (!customCompute($alert)) {
                    $this->alert_m->insert_alert([
                        'itemID'     => $id,
                        "userID"     => $this->session->userdata("loginuserID"),
                        'usertypeID' => $this->session->userdata('usertypeID'),
                        'itemname'   => $type
                    ]);
                }
            }
            $this->_json(['ok' => true]);
        }

        private function _alertOrder( $alerts )
        {
            $i          = 0;
            $alertOrder = [];
            if ( customCompute($alerts) ) {
                foreach ( $alerts as $alert ) {
                    foreach ( $alert as $alt ) {
                        $alertOrder[ $i ] = (array) $alt;
                        $i++;
                    }
                }
                array_multisort(array_column($alertOrder, "create_date"), SORT_DESC, $alertOrder);
            }
            return $alertOrder;
        }

        private function _userAlert()
        {
            $this->load->model('alert_m');
            $alerts = $this->alert_m->get_order_by_alert([
                'userID'     => $this->session->userdata('loginuserID'),
                'usertypeID' => $this->session->userdata('usertypeID')
            ]);
            if ( customCompute($alerts) ) {
                foreach ( $alerts as $alert ) {
                    $this->_userAlert[ $alert->itemID ][ $alert->itemname ] = $alert;
                }
            }
            $this->_userAlert;
        }

        private function _alertNotice( $schoolYearID )
        {
            if ( permissionChecker('notice_view') ) {
                $notices = $this->notice_m->get_order_by_notice([ 'schoolyearID' => $schoolYearID ]);
                if ( customCompute($notices) ) {
                    foreach ( $notices as $notice ) {
                        if ( !isset($this->_userAlert[ $notice->noticeID ]['notice']) ) {
                            $this->_alert['notice'][] = $notice;
                        }
                    }
                }
            }
        }

        private function _alertEvent( $schoolYearID )
        {
            if ( permissionChecker('event_view') ) {
                $events = $this->event_m->get_order_by_event([ 'schoolyearID' => $schoolYearID ]);
                if ( customCompute($events) ) {
                    foreach ( $events as $event ) {
                        if ( !isset($this->_userAlert[ $event->eventID ]['event']) ) {
                            $this->_alert['event'][] = $event;
                        }
                    }
                }
            }
        }

        private function _alertHoliday( $schoolYearID )
        {
            if ( permissionChecker('holiday_view') ) {
                $holiday = $this->holiday_m->get_order_by_holiday([ 'schoolyearID' => $schoolYearID ]);
                if ( customCompute($holiday) ) {
                    foreach ( $holiday as $day ) {
                        if ( !isset($this->_userAlert[ $day->holidayID ]['holiday']) ) {
                            $this->_alert['holiday'][] = $day;
                        }
                    }
                }
            }
        }

        public function _alertMessage()
        {
            if ( permissionChecker('conversation') ) {
                $messages         = $this->conversation_m->get_my_conversations();
                $flagConversation = [];
                $flagSubject      = [];
                $mergeMessages    = [];

                if ( customCompute($messages) ) {
                    foreach ( $messages as $messageKey => $message ) {
                        if ( !array_key_exists($message->conversation_id, $flagSubject) ) {
                            $flagSubject[ $message->conversation_id ] = $message->subject;
                        }

                        if ( !isset($this->_userAlert[ $message->msg_id ]['message']) ) {
                            if ( !in_array($message->conversation_id, $flagConversation) ) {
                                $flagConversation[] = $message->conversation_id;
                            }

                            if ( in_array($message->conversation_id, $flagConversation) ) {
                                $mergeMessages[ $message->conversation_id ] = $message;
                            }
                        }
                    }
                }

                if ( customCompute($mergeMessages) ) {
                    foreach ( $mergeMessages as $messageKey => $message ) {
                        if ( empty($message->subject) && isset($flagSubject[ $message->conversation_id ]) ) {
                            $mergeMessages[ $message->conversation_id ]->subject = $flagSubject[ $message->conversation_id ];
                        }
                    }
                }
                $this->_alert['message'] = $mergeMessages;
            }
        }

        private function _alertMarkup( $alerts )
        {
            $html = '';
            if ( customCompute($alerts) > 0 ) {
                foreach ( $alerts as $alert ) {
                    $pusher = $this->_pusher($alert);
                    $html   .= '<li>';
                    $html   .= "<a href=" . base_url($pusher->link) . ">";
                    $html   .= "<div class='pull-left hatchers-notify-icon " . $pusher->iconClass . "'>";
                    $html   .= "<i class='fa " . $pusher->icon . "'></i>";
                    $html   .= "</div>";
                    $html   .= "<div class='notification-content'>";
                    $html   .= "<div class='notification-title'>" . strip_tags((string) $pusher->title) . "</div>";
                    $html   .= "<div class='notification-subtitle'>" . strip_tags((string) $pusher->description) . "</div>";
                    $html   .= "</div>";
                    $html   .= "<span class='time'><i class='fa fa-clock-o'></i> " . $pusher->date . "</span>";
                    $html   .= "<span class='hatchers-mark-read'>Mark as read</span>";
                    $html   .= "<span class='hatchers-dismiss' data-type='" . $pusher->type . "' data-id='" . $pusher->itemID . "'>×</span>";
                    $html   .= "</a>";
                    $html   .= "</li>";
                }
            }
            echo $html;
        }

        private function _pusher( $alert )
        {
            $title       = '';
            $description = '';
            $link        = '';
            $date        = '';
            $photo       = $this->data['siteinfos']->photo;
            $icon        = 'fa-bell';
            $iconClass   = 'notify-default';

            if ( customCompute($alert) ) {
                if ( isset($alert['noticeID']) ) {
                    $link        = "alert/read/notice/" . $alert['noticeID'];
                    $date        = $this->_timer($alert['create_date']);
                    $title       = namesorting($alert['title'], 27);
                    $description = namesorting($alert['notice'], 32);
                    $photo       = ( customCompute(userInfo($alert['create_usertypeID'],
                        $alert['create_userID'])) ? userInfo($alert['create_usertypeID'],
                        $alert['create_userID'])->photo : 'default.png' );
                    $icon        = 'fa-bullhorn';
                    $iconClass   = 'notify-notice';
                } elseif ( isset($alert['msg_id']) ) {
                    $link        = "alert/read/message/" . $alert['msg_id'];
                    $date        = $this->_timer($alert['create_date']);
                    $title       = namesorting($alert['subject'], 27);
                    $description = namesorting($alert['msg'], 32);
                    $photo       = ( customCompute(userInfo($alert['usertypeID'], $alert['user_id'])) ? userInfo($alert['usertypeID'],
                        $alert['user_id'])->photo : 'default.png' );
                    $icon        = 'fa-comments';
                    $iconClass   = 'notify-message';
                } elseif ( isset($alert['eventID']) ) {
                    $link        = "alert/read/event/" . $alert['eventID'];
                    $date        = $this->_timer($alert['create_date']);
                    $title       = namesorting($alert['title'], 27);
                    $description = namesorting($alert['details'], 32);
                    $photo       = ( customCompute(userInfo($alert['create_usertypeID'],
                        $alert['create_userID'])) ? userInfo($alert['create_usertypeID'],
                        $alert['create_userID'])->photo : 'default.png' );
                    $icon        = 'fa-calendar';
                    $iconClass   = 'notify-event';
                } elseif ( isset($alert['holidayID']) ) {
                    $link        = "alert/read/holiday/" . $alert['holidayID'];
                    $date        = $this->_timer($alert['create_date']);
                    $title       = namesorting($alert['title'], 27);
                    $description = namesorting($alert['details'], 32);
                    $photo       = ( customCompute(userInfo($alert['create_usertypeID'],
                        $alert['create_userID'])) ? userInfo($alert['create_usertypeID'],
                        $alert['create_userID'])->photo : 'default.png' );
                    $icon        = 'fa-plane';
                    $iconClass   = 'notify-holiday';
                }
            }
            return (object) [
                'title'       => $title,
                'description' => $description,
                'link'        => $link,
                'photo'       => imagelink($photo),
                'date'        => $date,
                'icon'        => $icon,
                'iconClass'   => $iconClass,
                'type'        => $this->_alertIdentity($alert)['itemname'] ?? '',
                'itemID'      => $this->_alertIdentity($alert)['itemID'] ?? 0
            ];
        }

        private function _alertIdentity($alert)
        {
            if (isset($alert['noticeID'])) {
                return ['itemname' => 'notice', 'itemID' => $alert['noticeID']];
            } elseif (isset($alert['msg_id'])) {
                return ['itemname' => 'message', 'itemID' => $alert['msg_id']];
            } elseif (isset($alert['eventID'])) {
                return ['itemname' => 'event', 'itemID' => $alert['eventID']];
            } elseif (isset($alert['holidayID'])) {
                return ['itemname' => 'holiday', 'itemID' => $alert['holidayID']];
            }
            return [];
        }

        private function _json($payload)
        {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($payload));
        }

        private function _timer( $createDate )
        {
            $date        = date('Y-m-d H:i:s');
            $presentDate = date("Y-m-d H:i:s");
            $firstDate   = new DateTime($createDate);
            $secondDate  = new DateTime($presentDate);
            $difference  = $firstDate->diff($secondDate);
            if ( $difference->y >= 1 ) {
                $format = 'Y-m-d H:i:s';
                $date   = DateTime::createFromFormat($format, $createDate);
                $date   = $date->format('M d Y');
            } elseif ( $difference->m == 1 && $difference->m != 0 ) {
                $date = $difference->m . " month";
            } elseif ( $difference->m <= 12 && $difference->m != 0 ) {
                $date = $difference->m . " months";
            } elseif ( $difference->d == 1 && $difference->d != 0 ) {
                $date = "Yesterday";
            } elseif ( $difference->d <= 31 && $difference->d != 0 ) {
                $date = $difference->d . " days";
            } elseif ($difference->h == 1 && $difference->h != 0) {
                $date = $difference->h . " hr";
            } elseif ($difference->h <= 24 && $difference->h != 0) {
                $date = $difference->h . " hrs";
            } elseif ( $difference->i <= 60 && $difference->i != 0 ) {
                $date = $difference->i . " mins";
            } elseif ( $difference->s <= 10 ) {
                $date = "Just Now";
            } elseif ( $difference->s <= 60 && $difference->s != 0 ) {
                $date = $difference->s . " sec";
            }
            return $date;
        }
    }
