<?php
    $getActiveUserID = $this->session->userdata('usertypeID');
    $isFounder = ($getActiveUserID == 3);
    $isMentor  = ($getActiveUserID == 2);
?>

<?php if ($isFounder) { ?>
    <script type="text/javascript">
        document.body.classList.add('hatchers-founder');
    </script>
    <?php
        $hatchersData = isset($hatchers) ? $hatchers : [
            'mentor' => null,
            'meetings' => [],
            'learning' => [],
            'tasks' => [],
            'calendar_events' => []
        ];
        $hatchersTools = isset($hatchers_nav_right) ? $hatchers_nav_right : [];
        $milestoneMap  = isset($hatchersData['milestone_map']) ? $hatchersData['milestone_map'] : [];

        function hatchers_time_until($dateString)
        {
            if (empty($dateString)) return '';
            $now = new DateTime();
            $future = new DateTime($dateString);
            if ($future < $now) return 'Happened';
            $diff = $now->diff($future);
            if ($diff->d > 0) return $diff->d . 'd ' . $diff->h . 'h';
            if ($diff->h > 0) return $diff->h . 'h ' . $diff->i . 'm';
            return $diff->i . 'm';
        }
    ?>
    <div class="hatchers-dashboard">
        <div class="hatchers-header">
            <div>
                <h1>Welcome back <?=htmlspecialchars($this->session->userdata('name'))?>,</h1>
                <p>Here's what's on for you for this week:</p>
            </div>
        </div>

        <div class="hatchers-grid">
            <div class="hatchers-main">
                <section class="hatchers-section">
                    <div class="hatchers-section-title">Mentoring</div>
                    <?php if (customCompute($hatchersData['meetings'])) { ?>
                        <?php $meeting = $hatchersData['meetings'][0]; ?>
                        <div class="hatchers-card">
                            <div class="hatchers-card-meta">
                                <?=strtoupper(date('l, M j', strtotime($meeting->starts_at)))?> • <?=date('g:ia', strtotime($meeting->starts_at))?>
                            </div>
                            <div class="hatchers-card-content">
                                <div class="hatchers-card-title">1 on 1 session</div>
                                <div class="hatchers-card-subtitle">
                                    <?php if (customCompute($hatchersData['mentor'])) { ?>
                                        with your mentor <?=htmlspecialchars($hatchersData['mentor']->name)?>
                                    <?php } else { ?>
                                        with your mentor
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="hatchers-chip">Session in <?=hatchers_time_until($meeting->starts_at)?></div>
                        </div>
                    <?php } else { ?>
                        <div class="hatchers-card">
                            <div class="hatchers-card-content">
                                <div class="hatchers-card-title">No mentoring sessions yet</div>
                                <div class="hatchers-card-subtitle">Your mentor will schedule your first session soon.</div>
                            </div>
                        </div>
                    <?php } ?>
                </section>

                <section class="hatchers-section">
                    <div class="hatchers-section-title">Learning</div>
                    <?php if (customCompute($hatchersData['learning'])) { ?>
                        <?php $lesson = $hatchersData['learning'][0]; ?>
                        <div class="hatchers-card">
                            <div class="hatchers-card-meta hatchers-meta-blue">
                                <?php if (!empty($lesson->starts_at)) { ?>
                                    <?=strtoupper(date('l, M j', strtotime($lesson->starts_at)))?> • <?=date('g:ia', strtotime($lesson->starts_at))?>
                                <?php } else { ?>
                                    UPCOMING
                                <?php } ?>
                            </div>
                            <div class="hatchers-card-content">
                                <div class="hatchers-card-title"><?=htmlspecialchars($lesson->title)?></div>
                                <div class="hatchers-card-subtitle"><?=htmlspecialchars((string) $lesson->subtitle)?></div>
                            </div>
                            <div class="hatchers-chip">
                                <?php if (!empty($lesson->starts_at)) { ?>
                                    Lesson in <?=hatchers_time_until($lesson->starts_at)?>
                                <?php } else { ?>
                                    Scheduled soon
                                <?php } ?>
                            </div>
                        </div>
                    <?php } else { ?>
                        <div class="hatchers-card">
                            <div class="hatchers-card-content">
                                <div class="hatchers-card-title">No learning scheduled yet</div>
                                <div class="hatchers-card-subtitle">Your learning plan will appear here.</div>
                            </div>
                        </div>
                    <?php } ?>
                </section>

                <section class="hatchers-section">
                    <div class="hatchers-section-title">Tasks</div>
                    <?php if (customCompute($hatchersData['tasks'])) { ?>
                        <?php $taskCount = 0; ?>
                        <?php foreach ($hatchersData['tasks'] as $task) { ?>
                            <?php if ($taskCount >= 3) break; ?>
                            <?php
                                $milestoneTitle = 'Milestone';
                                $milestoneDue = '';
                                if (!empty($task->milestone_id) && isset($milestoneMap[$task->milestone_id])) {
                                    $milestoneTitle = $milestoneMap[$task->milestone_id]->title;
                                    $milestoneDue = $milestoneMap[$task->milestone_id]->due_date;
                                }
                                $dueDate = !empty($task->due_date) ? $task->due_date : $milestoneDue;
                            ?>
                            <div class="hatchers-task">
                                <div class="hatchers-task-meta hatchers-meta-purple">
                                    <?=strtoupper(htmlspecialchars($milestoneTitle))?>
                                    <?php if (!empty($dueDate)) { ?>
                                        • DUE IN <?=strtoupper(hatchers_time_until($dueDate))?>
                                    <?php } ?>
                                </div>
                                <div class="hatchers-task-content">
                                    <div class="hatchers-task-title"><?=htmlspecialchars($task->title)?></div>
                                    <div class="hatchers-task-subtitle"><?=htmlspecialchars((string) $task->description)?></div>
                                </div>
                                <button class="hatchers-cta">Work with AI</button>
                            </div>
                            <?php $taskCount++; ?>
                        <?php } ?>
                    <?php } else { ?>
                        <div class="hatchers-task hatchers-task-muted">
                            <div class="hatchers-task-content">
                                <div class="hatchers-task-title">No tasks yet</div>
                                <div class="hatchers-task-subtitle">Your mentor will add tasks here.</div>
                            </div>
                        </div>
                    <?php } ?>
                </section>

                <div class="hatchers-chat-log" id="hatchers-chat-log"></div>
                <div class="hatchers-chat">
                    <div class="hatchers-chat-icon">
                        <i class="fa fa-microphone"></i>
                    </div>
                    <input type="text" id="hatchers-chat-input" class="hatchers-chat-input" placeholder="Ask AI anything about your project..." />
                    <button id="hatchers-chat-send" class="hatchers-chat-send"><i class="fa fa-arrow-right"></i></button>
                </div>
            </div>

            <aside class="hatchers-right">
                <div class="hatchers-right-section">
                    <div class="hatchers-right-title">Notifications</div>
                </div>

                <div class="hatchers-right-section">
                    <div class="hatchers-calendar">
                        <div id="hatchers-calendar"></div>
                    </div>
                </div>

                <div class="hatchers-right-section">
                    <div class="hatchers-right-title">AI Tools</div>
                    <div class="hatchers-tools">
                        <?php if (customCompute($hatchersTools)) { ?>
                            <?php foreach ($hatchersTools as $tool) { ?>
                                <div class="hatchers-tool"><?=htmlspecialchars($tool->label)?></div>
                            <?php } ?>
                        <?php } else { ?>
                            <div class="hatchers-tool">Landing Pages</div>
                            <div class="hatchers-tool">Forms</div>
                            <div class="hatchers-tool">Social Media</div>
                            <div class="hatchers-tool">CRM</div>
                            <div class="hatchers-tool">Payments &amp; Bookings</div>
                            <div class="hatchers-tool">SEO</div>
                            <div class="hatchers-tool">Messaging Automation</div>
                        <?php } ?>
                    </div>
                </div>
            </aside>
        </div>
    </div>

    <script type="text/javascript">
        $(document).ready(function () {
            function appendChat(role, text) {
                var $log = $('#hatchers-chat-log');
                if (!$log.length) return;
                var bubbleClass = role === 'user' ? 'hatchers-chat-bubble user' : 'hatchers-chat-bubble assistant';
                $log.append('<div class="' + bubbleClass + '"></div>');
                $log.find('.hatchers-chat-bubble').last().text(text);
                $log.scrollTop($log.prop('scrollHeight'));
            }

            function sendChat() {
                var message = $('#hatchers-chat-input').val().trim();
                if (!message) return;
                appendChat('user', message);
                $('#hatchers-chat-input').val('');
                $.post('<?=base_url('aiassistant/chat')?>', { message: message }, function (res) {
                    if (res && res.ok) {
                        appendChat('assistant', res.reply);
                    } else {
                        appendChat('assistant', res && res.error ? res.error : 'AI error.');
                    }
                });
            }

            $('#hatchers-chat-send').on('click', sendChat);
            $('#hatchers-chat-input').on('keypress', function (e) {
                if (e.which === 13) {
                    sendChat();
                }
            });

            if ($('#hatchers-calendar').length) {
                $('#hatchers-calendar').fullCalendar({
                    header: {
                        left: 'prev',
                        center: 'title',
                        right: 'next'
                    },
                    defaultView: 'month',
                    height: 260,
                    fixedWeekCount: false,
                    eventLimit: true,
                    events: <?=json_encode($hatchersData['calendar_events'])?>
                });
            }
        });
    </script>
<?php } elseif ($isMentor) { ?>
    <script type="text/javascript">
        document.body.classList.add('hatchers-mentor');
    </script>
    <?php
        $mentorData = isset($hatchers_mentor) ? $hatchers_mentor : ['founders' => []];
    ?>

    <div class="hatchers-dashboard">
        <div class="hatchers-header">
            <div>
                <h1>Your Founders</h1>
                <p>Choose a founder to view milestones, calendar, and notes.</p>
            </div>
        </div>

        <div class="hatchers-mentor-grid">
            <?php if (customCompute($mentorData['founders'])) { ?>
                <?php foreach ($mentorData['founders'] as $founder) { ?>
                    <a class="hatchers-founder-card" href="<?=base_url('mentor/view/'.$founder->studentID)?>">
                        <div class="hatchers-founder-avatar">
                            <img src="<?=imagelink($founder->photo)?>" alt="">
                        </div>
                        <div class="hatchers-founder-info">
                            <div class="hatchers-founder-name"><?=htmlspecialchars($founder->name)?></div>
                            <div class="hatchers-founder-meta">
                                <?=htmlspecialchars((string) $founder->email)?>
                                <?php if (!empty($founder->phone)) { ?>
                                    • <?=htmlspecialchars((string) $founder->phone)?>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="hatchers-founder-action">View</div>
                    </a>
                <?php } ?>
            <?php } else { ?>
                <div class="hatchers-empty">
                    <div class="hatchers-empty-title">No founders assigned yet</div>
                    <div class="hatchers-empty-subtitle">Ask the Super Admin to assign founders to you.</div>
                </div>
            <?php } ?>
        </div>
    </div>
<?php } else { ?>
<div class="row">
    <?php if (config_item('demo')) { ?>
        <div class="col-sm-12" id="resetDummyData">
            <div class="callout callout-danger">
                <h4>Reminder!</h4>
                <p>Dummy data will be reset in every <code>30</code> minutes</p>
            </div>
        </div>

        <script type="text/javascript">
            $(document).ready(function() {
                var count = 7;
                var countdown = setInterval(function() {
                    $("p.countdown").html(count + " seconds remaining!");
                    if (count == 0) {
                        clearInterval(countdown);
                        $('#resetDummyData').hide();
                    }
                    count--;
                }, 1000);
            });
        </script>
    <?php } ?>

    <?php if ((config_item('demo') === FALSE) && ($siteinfos->auto_update_notification == 1) && ($versionChecking != 'none')) { ?>
        <?php if ($this->session->userdata('updatestatus') === null) { ?>
            <div class="col-sm-12" id="updatenotify">
                <div class="callout callout-success">
                    <h4>Dear Admin</h4>
                    <p>INIlabs school management system has released a new update.</p>
                    <p>Do you want to update it now <?= config_item('ini_version') ?> to <?= $versionChecking ?> ?</p>
                    <a href="<?= base_url('dashboard/remind') ?>" class="btn btn-danger">Remind me</a>
                    <a href="<?= base_url('dashboard/update') ?>" class="btn btn-success">Update</a>
                </div>
            </div>
        <?php } ?>
    <?php } ?>

    <?php
    $arrayColor = array(
        'bg-orange-dark',
        'bg-teal-light',
        'bg-pink-light',
        'bg-purple-light'
    );
    function allModuleArray($dashboardWidget, $usertypeID = '1')
    {
        return array(
            $usertypeID => array(
                'student'   => $dashboardWidget['students'],
                'classes'   => $dashboardWidget['classes'],
                'teacher'   => $dashboardWidget['teachers'],
                'parents'   => $dashboardWidget['parents'],
                'subject'   => $dashboardWidget['subjects'],
                'book'     => $dashboardWidget['books'],
                'feetypes'  => $dashboardWidget['feetypes'],
                'lmember'   => $dashboardWidget['lmembers'],
                'event'     => $dashboardWidget['events'],
                'issue'     => $dashboardWidget['issues'],
                'holiday'   => $dashboardWidget['holidays'],
                'invoice'   => $dashboardWidget['invoices'],
            )
        );
    }

    $userArray = array(
        '1' => array(
            'student'   => $dashboardWidget['students'],
            'teacher'   => $dashboardWidget['teachers'],
            'parents'   => $dashboardWidget['parents'],
            'subject'   => $dashboardWidget['subjects']
        ),
        '2' => array(
            'student'   => $dashboardWidget['students'],
            'teacher'   => $dashboardWidget['teachers'],
            'classes'   => $dashboardWidget['classes'],
            'subject'   => $dashboardWidget['subjects'],
        ),
        '3' => array(
            'teacher'   => $dashboardWidget['teachers'],
            'subject'   => $dashboardWidget['subjects'],
            'issue'     => $dashboardWidget['issues'],
            'invoice'   => $dashboardWidget['invoices'],
        ),
        '4' => array(
            'teacher'   => $dashboardWidget['teachers'],
            'book'     => $dashboardWidget['books'],
            'event'     => $dashboardWidget['events'],
            'holiday'   => $dashboardWidget['holidays'],
        ),
        '5' => array(
            'teacher'   => $dashboardWidget['teachers'],
            'parents'   => $dashboardWidget['parents'],
            'feetypes'  => $dashboardWidget['feetypes'],
            'invoice'   => $dashboardWidget['invoices'],
        ),
        '6' => array(
            'teacher'   => $dashboardWidget['teachers'],
            'lmember'   => $dashboardWidget['lmembers'],
            'book'      => $dashboardWidget['books'],
            'issue'     => $dashboardWidget['issues'],
        ),
        '7' => array(
            'teacher'       => $dashboardWidget['teachers'],
            'event'         => $dashboardWidget['events'],
            'holiday'       => $dashboardWidget['holidays'],
            'visitorinfo'  => $dashboardWidget['visitors'],
        ),
    );

    $generateBoxArray = array();
    $counter = 0;
    $getActiveUserID = $this->session->userdata('usertypeID');
    $getAllSessionDatas = $this->session->userdata('master_permission_set');
    foreach ($getAllSessionDatas as $getAllSessionDataKey => $getAllSessionData) {
        if ($getAllSessionData == 'yes' && isset($userArray[$getActiveUserID][$getAllSessionDataKey])) {
            if ($counter == 4) {
                break;
            }
            $generateBoxArray[$getAllSessionDataKey] = array(
                'icon' => $dashboardWidget['allmenu'][$getAllSessionDataKey],
                'color' => $arrayColor[$counter],
                'link' => $getAllSessionDataKey,
                'count' => $userArray[$getActiveUserID][$getAllSessionDataKey],
                'menu' => $dashboardWidget['allmenulang'][$getAllSessionDataKey],
            );
            $counter++;
        }
    }

    $icon = '';
    $menu = '';
    if ($counter < 4) {
        $userArray = allModuleArray($dashboardWidget, $getActiveUserID);
        foreach ($getAllSessionDatas as $getAllSessionDataKey => $getAllSessionData) {
            if ($getAllSessionData == 'yes' && isset($userArray[$getActiveUserID][$getAllSessionDataKey])) {
                if ($counter == 4) {
                    break;
                }
                if (!isset($generateBoxArray[$getAllSessionDataKey])) {
                    $generateBoxArray[$getAllSessionDataKey] = array(
                        'icon' => $dashboardWidget['allmenu'][$getAllSessionDataKey],
                        'color' => $arrayColor[$counter],
                        'link' => $getAllSessionDataKey,
                        'count' => $userArray[$getActiveUserID][$getAllSessionDataKey],
                        'menu' => $dashboardWidget['allmenulang'][$getAllSessionDataKey]
                    );
                    $counter++;
                }
            }
        }
    }

    if (customCompute($generateBoxArray)) {
        foreach ($generateBoxArray as $generateBoxArrayKey => $generateBoxValue) { ?>
            <div class="col-lg-3 col-xs-6">
                <div class="small-box ">
                    <a class="small-box-footer <?= $generateBoxValue['color'] ?>" href="<?= base_url($generateBoxValue['link']) ?>">
                        <div class="icon <?= $generateBoxValue['color'] ?>" style="padding: 9.5px 18px 6px 18px;">
                            <i class="fa <?= $generateBoxValue['icon'] ?>"></i>
                        </div>
                        <div class="inner ">
                            <h3 class="text-white">
                                <?= $generateBoxValue['count'] ?>
                            </h3 class="text-white">
                            <p class="text-white">
                                <?= $this->lang->line('menu_' . $generateBoxValue['menu']) ?>
                            </p>
                        </div>
                    </a>
                </div>
            </div>
    <?php }
    } ?>
</div>

<?php if ($getActiveUserID == 1 || $getActiveUserID == 5) { ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="box">
                <div class="box-body" style="padding: 0px;">
                    <div id="earningGraph"></div>
                </div>
            </div>
        </div>
    </div>
<?php } ?>

<?php if ($getActiveUserID == 1 || $getActiveUserID == 5) { ?>
    <div class="row">
        <div class="col-sm-4">
            <?php $this->load->view('dashboard/ProfileBox'); ?>
        </div>
        <div class="col-sm-8">
            <div class="box">
                <div class="box-body" style="padding: 0px;">
                    <div id="attendanceGraph"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <?php if (permissionChecker('notice')) { ?>
            <div class="col-sm-6">
                <?php $this->load->view('dashboard/NoticeBoard', array('val' => 5, 'length' => 15, 'maxlength' => 45)); ?>
            </div>
        <?php } ?>
        <div class="col-sm-6">
            <div class="box">
                <div class="box-body" style="padding: 0px;">
                    <div id="visitor"></div>
                </div>
            </div>
        </div>
    </div>
<?php } else { ?>
    <div class="row">
        <div class="col-sm-4">
            <?php $this->load->view('dashboard/ProfileBox'); ?>
        </div>
        <?php if (permissionChecker('notice')) { ?>
            <div class="col-sm-8">
                <div class="box">
                    <div class="box-body" style="padding: 0px;height: 320px">
                        <?php $this->load->view('dashboard/NoticeBoard', array('val' => 5, 'length' => 20, 'maxlength' => 70)); ?>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
<?php } ?>

<div class="row">
    <div class="col-sm-12">
        <div class="box">
            <div class="box-body">
                <!-- THE CALENDAR -->
                <div id="calendar"></div>
            </div>
        </div>
    </div>
</div><!-- /.row -->

<?php
if ($attendanceSystem != 'subject') {
    $this->load->view("dashboard/AttendanceHighChartJavascript");
} else {
    $this->load->view("dashboard/SubjectWiseAttendanceHighChartJavascript");
}
?>
<?php $this->load->view("dashboard/EarningHighChartJavascript.php"); ?>
<?php $this->load->view("dashboard/CalenderJavascript"); ?>
<?php $this->load->view("dashboard/VisitorHighChartJavascript"); ?>
<?php } ?>
