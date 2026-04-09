<?php
    $role = isset($hatchers_home['role']) ? $hatchers_home['role'] : 'founder';
    $headline = isset($hatchers_home['headline']) ? $hatchers_home['headline'] : 'Welcome back';
    $subheadline = isset($hatchers_home['subheadline']) ? $hatchers_home['subheadline'] : 'Here is what is moving right now.';
    $stats = isset($hatchers_home['stats']) ? $hatchers_home['stats'] : [];
    $cards = isset($hatchers_home['cards']) ? $hatchers_home['cards'] : [];
    $spotlight = isset($hatchers_home['spotlight']) ? $hatchers_home['spotlight'] : [];
    $founderSummaries = isset($hatchers_home['founder_summaries']) ? $hatchers_home['founder_summaries'] : [];
    $hatchersData = isset($hatchers) ? $hatchers : [];
    $mentor = isset($hatchersData['mentor']) ? $hatchersData['mentor'] : null;
    $meetings = isset($hatchersData['meetings']) ? $hatchersData['meetings'] : [];
    $learning = isset($hatchersData['learning']) ? $hatchersData['learning'] : [];
    $tasks = isset($hatchersData['tasks']) ? $hatchersData['tasks'] : [];
    $milestoneMap = isset($hatchersData['milestone_map']) ? $hatchersData['milestone_map'] : [];
    $aiHistory = isset($hatchersData['ai_history']) ? $hatchersData['ai_history'] : [];
    $weeklyProgress = isset($hatchersData['weekly_progress']) ? (int) $hatchersData['weekly_progress'] : 0;
    $weeklyScopeTotal = isset($hatchersData['weekly_scope_total']) ? (int) $hatchersData['weekly_scope_total'] : 0;
    $weeklyScopeDone = isset($hatchersData['weekly_scope_done']) ? (int) $hatchersData['weekly_scope_done'] : 0;
?>

<div class="hatchers-page">
    <div class="hatchers-page-header">
        <div>
            <h1><?=htmlspecialchars($headline)?></h1>
            <p><?=htmlspecialchars($subheadline)?></p>
        </div>
    </div>

    <?php if ($role !== 'founder' && customCompute($stats)) { ?>
        <div class="hatchers-stats-grid">
            <?php foreach ($stats as $stat) { ?>
                <div class="hatchers-stat-card">
                    <div class="eyebrow"><?=htmlspecialchars($stat['label'])?></div>
                    <div class="value"><?=htmlspecialchars((string) $stat['value'])?></div>
                    <div class="copy"><?=htmlspecialchars($stat['copy'])?></div>
                </div>
            <?php } ?>
        </div>
    <?php } ?>

    <?php if ($role === 'founder') { ?>
        <div class="hatchers-founder-stack">
            <section class="hatchers-card-panel">
                <div class="hatchers-panel-title">Weekly Progress</div>
                <div class="hatchers-founder-card-top">
                    <div>
                        <div class="hatchers-line-title"><?=$weeklyProgress?>% complete</div>
                        <div class="hatchers-line-copy"><?=$weeklyScopeDone?> of <?=$weeklyScopeTotal?> milestones and tasks completed this week.</div>
                    </div>
                    <a class="hatchers-ghost-btn" href="<?=base_url('launchplan/index')?>">Open launch plan</a>
                </div>
                <div class="hatchers-progress-bar"><span style="width: <?=$weeklyProgress?>%"></span></div>
            </section>

            <section class="hatchers-card-panel">
                <div class="hatchers-panel-title">Mentoring</div>
                <?php if (customCompute($meetings)) { ?>
                    <?php foreach (array_slice($meetings, 0, 2) as $meeting) { ?>
                        <div class="hatchers-line-card">
                            <div>
                                <div class="hatchers-line-meta accent">
                                    <?=!empty($meeting->starts_at) ? strtoupper(date('l, M j - g:ia', strtotime($meeting->starts_at))) : 'MENTORING SESSION'?>
                                </div>
                                <div class="hatchers-line-title"><?=htmlspecialchars(!empty($meeting->title) ? $meeting->title : '1 on 1 session')?></div>
                                <div class="hatchers-line-copy">with your mentor <?=htmlspecialchars(customCompute($mentor) ? $mentor->name : 'Hatchers mentor')?></div>
                            </div>
                            <div class="hatchers-card-actions">
                                <?php if (!empty($meeting->join_link)) { ?>
                                    <a class="hatchers-inline-btn" href="<?=htmlspecialchars($meeting->join_link)?>" target="_blank">Join Session</a>
                                <?php } else { ?>
                                    <a class="hatchers-ghost-btn" href="<?=base_url('mentoring/index')?>">Open mentoring</a>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <div class="hatchers-empty-state small">Your mentor has not scheduled a session yet.</div>
                <?php } ?>
            </section>

            <section class="hatchers-card-panel">
                <div class="hatchers-panel-title">Learning</div>
                <?php if (customCompute($learning)) { ?>
                    <?php foreach (array_slice($learning, 0, 3) as $lesson) { ?>
                        <div class="hatchers-line-card">
                            <div>
                                <div class="hatchers-line-meta">
                                    <?=!empty($lesson->starts_at) ? strtoupper(date('l, M j - g:ia', strtotime($lesson->starts_at))) : 'LEARNING SESSION'?>
                                </div>
                                <div class="hatchers-line-title"><?=htmlspecialchars($lesson->title)?></div>
                                <div class="hatchers-line-copy"><?=htmlspecialchars((string) $lesson->subtitle)?></div>
                            </div>
                            <div class="hatchers-card-actions">
                                <a class="hatchers-ghost-btn" href="<?=base_url('learningplan/index')?>">Open learning</a>
                            </div>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <div class="hatchers-empty-state small">No learning sessions have been assigned yet.</div>
                <?php } ?>
            </section>

            <section class="hatchers-card-panel">
                <div class="hatchers-panel-title">Tasks</div>
                <?php if (customCompute($tasks)) { ?>
                    <?php foreach (array_slice($tasks, 0, 5) as $task) { ?>
                        <?php
                            $milestoneTitle = (!empty($task->milestone_id) && isset($milestoneMap[$task->milestone_id])) ? $milestoneMap[$task->milestone_id]->title : 'Weekly task';
                        ?>
                        <div class="hatchers-task-card <?=((int) $task->status === 1) ? 'is-complete' : ''?>">
                            <div class="hatchers-task-top">
                                <span><?=htmlspecialchars($milestoneTitle)?></span>
                                <span><?=!empty($task->due_date) ? strtoupper(date('M j', strtotime($task->due_date))) : 'NO DUE DATE'?></span>
                            </div>
                            <div class="hatchers-task-title-row">
                                <div>
                                    <div class="hatchers-task-title"><?=htmlspecialchars($task->title)?></div>
                                    <div class="hatchers-task-copy"><?=htmlspecialchars((string) $task->description)?></div>
                                </div>
                                <div class="hatchers-card-actions">
                                    <?php if ((int) $task->status !== 1) { ?>
                                        <button
                                            type="button"
                                            class="hatchers-inline-btn hatchers-task-ai-btn"
                                            data-ai-prompt="<?=htmlspecialchars('Help me complete this task: ' . $task->title . '. Context: ' . (string) $task->description, ENT_QUOTES, 'UTF-8')?>"
                                        >
                                            Ask AI
                                        </button>
                                    <?php } ?>
                                    <a class="hatchers-ghost-btn" href="<?=base_url('launchplan/index')?>"><?=((int) $task->status === 1) ? 'View task' : 'Open task'?></a>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <div class="hatchers-empty-state small">No tasks have been assigned yet.</div>
                <?php } ?>
            </section>

            <section class="hatchers-card-panel">
                <div class="hatchers-panel-title">Hatchers AI Assistant</div>
                <div class="hatchers-chat-thread hatchers-ai-thread" id="hatchers-home-chat-log">
                    <?php if (customCompute($aiHistory)) { ?>
                        <?php foreach ($aiHistory as $message) { ?>
                            <div class="hatchers-chat-row <?=$message->role === 'user' ? 'is-own' : ''?>">
                                <div class="bubble"><?=nl2br(htmlspecialchars($message->message))?></div>
                            </div>
                        <?php } ?>
                    <?php } else { ?>
                        <div class="hatchers-empty-state small">Ask about a task, milestone, or business challenge any time.</div>
                    <?php } ?>
                </div>
                <div class="hatchers-ai-compose">
                    <input type="text" id="hatchers-home-chat-input" placeholder="Ask AI anything about your tasks or startup...">
                    <button id="hatchers-home-chat-send" class="hatchers-inline-btn" type="button">Send</button>
                </div>
            </section>
        </div>

        <script type="text/javascript">
            $(document).ready(function () {
                function appendHomeChat(role, text) {
                    var $log = $('#hatchers-home-chat-log');
                    if (!$log.length) return;
                    $log.find('.hatchers-empty-state').remove();
                    var ownClass = role === 'user' ? ' is-own' : '';
                    $log.append('<div class="hatchers-chat-row' + ownClass + '"><div class="bubble"></div></div>');
                    $log.find('.hatchers-chat-row').last().find('.bubble').text(text);
                    $log.scrollTop($log.prop('scrollHeight'));
                }

                function sendHomeChat(message) {
                    var trimmed = $.trim(message || $('#hatchers-home-chat-input').val());
                    if (!trimmed) return;
                    appendHomeChat('user', trimmed);
                    $('#hatchers-home-chat-input').val('');
                    $.post('<?=base_url('aiassistant/chat')?>', { message: trimmed }, function (res) {
                        if (res && res.ok) {
                            appendHomeChat('assistant', res.reply);
                        } else {
                            appendHomeChat('assistant', res && res.error ? res.error : 'AI error.');
                        }
                    }, 'json');
                }

                $('#hatchers-home-chat-send').on('click', function () {
                    sendHomeChat();
                });

                $('#hatchers-home-chat-input').on('keypress', function (e) {
                    if (e.which === 13) {
                        e.preventDefault();
                        sendHomeChat();
                    }
                });

                $('.hatchers-task-ai-btn').on('click', function () {
                    var prompt = $(this).data('ai-prompt');
                    $('#hatchers-home-chat-input').val(prompt);
                    sendHomeChat(prompt);
                });
            });
        </script>
    <?php } elseif ($role === 'mentor') { ?>
        <div class="hatchers-two-column">
            <section class="hatchers-card-panel">
                <div class="hatchers-panel-title">Founder Progress</div>
                <?php if (customCompute($founderSummaries)) { ?>
                    <?php foreach ($founderSummaries as $founder) { ?>
                        <div class="hatchers-founder-card">
                            <div class="hatchers-founder-card-top">
                                <div>
                                    <div class="hatchers-line-title"><?=htmlspecialchars($founder['name'])?></div>
                                    <div class="hatchers-line-copy"><?=htmlspecialchars(!empty($founder['email']) ? $founder['email'] : 'Founder workspace ready')?></div>
                                    <?php if (!empty($founder['company_brief'])) { ?>
                                        <div class="hatchers-line-copy muted"><?=htmlspecialchars((string) $founder['company_brief'])?></div>
                                    <?php } ?>
                                </div>
                                <div class="hatchers-founder-progress"><?=$founder['progress_percent']?>%</div>
                            </div>
                            <div class="hatchers-progress-bar"><span style="width: <?=$founder['progress_percent']?>%"></span></div>
                            <div class="hatchers-founder-metrics">
                                <span><?=$founder['open_tasks']?> open tasks</span>
                                <span><?=$founder['completed_tasks']?> completed</span>
                                <span><?=$founder['milestones']?> milestones</span>
                                <span><?=$founder['overdue_tasks']?> overdue</span>
                            </div>
                            <div class="hatchers-line-copy muted">
                                <?=!empty($founder['next_meeting']) ? 'Next meeting ' . date('M j, g:ia', strtotime($founder['next_meeting'])) : 'No meeting scheduled yet.'?>
                            </div>
                            <div class="hatchers-card-actions">
                                <a class="hatchers-inline-btn" href="<?=base_url('mentoring/index?founder_id=' . (int) $founder['founder_id'])?>">Open workspace</a>
                                <a class="hatchers-ghost-btn" href="<?=base_url('learningplan/index?founder_id=' . (int) $founder['founder_id'])?>">Learning</a>
                            </div>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <div class="hatchers-empty-state small">No founders are assigned to you yet.</div>
                <?php } ?>
            </section>

            <section class="hatchers-card-panel">
                <div class="hatchers-panel-title">This Week</div>
                <?php if (customCompute($cards)) { ?>
                    <?php foreach ($cards as $card) { ?>
                        <div class="hatchers-line-card">
                            <div>
                                <div class="hatchers-line-title"><?=htmlspecialchars($card['title'])?></div>
                                <div class="hatchers-line-copy"><?=htmlspecialchars($card['copy'])?></div>
                            </div>
                            <div class="hatchers-line-meta">
                                <a href="<?=base_url($card['link'])?>"><?=htmlspecialchars($card['action'])?></a>
                            </div>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <div class="hatchers-empty-state small">No meetings are on the calendar yet.</div>
                <?php } ?>

                <div class="hatchers-panel-title">Operational Focus</div>
                <?php if (customCompute($spotlight)) { ?>
                    <?php foreach ($spotlight as $item) { ?>
                        <div class="hatchers-focus-card">
                            <div class="eyebrow"><?=htmlspecialchars($item['label'])?></div>
                            <div class="title"><?=htmlspecialchars($item['title'])?></div>
                            <div class="copy"><?=htmlspecialchars($item['copy'])?></div>
                            <?php if (!empty($item['link'])) { ?>
                                <a class="hatchers-inline-btn" href="<?=base_url($item['link'])?>"><?=htmlspecialchars($item['action'])?></a>
                            <?php } ?>
                        </div>
                    <?php } ?>
                <?php } ?>
            </section>
        </div>
    <?php } else { ?>
        <div class="hatchers-two-column">
            <section class="hatchers-card-panel">
                <div class="hatchers-panel-title">Founder Overview</div>
                <?php if (customCompute($cards)) { ?>
                    <?php foreach ($cards as $card) { ?>
                        <div class="hatchers-line-card">
                            <div>
                                <div class="hatchers-line-title"><?=htmlspecialchars($card['title'])?></div>
                                <div class="hatchers-line-copy"><?=htmlspecialchars($card['copy'])?></div>
                            </div>
                            <div class="hatchers-line-meta">
                                <?php if (!empty($card['link'])) { ?>
                                    <a href="<?=base_url($card['link'])?>"><?=htmlspecialchars($card['action'])?></a>
                                <?php } else { ?>
                                    <?=htmlspecialchars($card['action'])?>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <div class="hatchers-empty-state small">Nothing to show yet.</div>
                <?php } ?>
            </section>

            <section class="hatchers-card-panel">
                <div class="hatchers-panel-title">Operational Focus</div>
                <?php if (customCompute($spotlight)) { ?>
                    <?php foreach ($spotlight as $item) { ?>
                        <div class="hatchers-focus-card">
                            <div class="eyebrow"><?=htmlspecialchars($item['label'])?></div>
                            <div class="title"><?=htmlspecialchars($item['title'])?></div>
                            <div class="copy"><?=htmlspecialchars($item['copy'])?></div>
                            <?php if (!empty($item['link'])) { ?>
                                <a class="hatchers-inline-btn" href="<?=base_url($item['link'])?>"><?=htmlspecialchars($item['action'])?></a>
                            <?php } ?>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <div class="hatchers-empty-state small">No action items yet.</div>
                <?php } ?>
            </section>
        </div>
    <?php } ?>
</div>
