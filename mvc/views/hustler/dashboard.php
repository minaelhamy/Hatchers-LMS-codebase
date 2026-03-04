<?php
    $taskItems = [];
    $milestoneItems = [];
    if (customCompute($action_items)) {
        foreach ($action_items as $item) {
            if ($item->item_type === 'milestone') {
                $milestoneItems[] = $item;
            } else {
                $taskItems[] = $item;
            }
        }
    }

    $diagnosisCore = isset($diagnosis['diagnosis']) && is_array($diagnosis['diagnosis']) ? $diagnosis['diagnosis'] : [];
    $gaps = isset($diagnosis['gaps']) && is_array($diagnosis['gaps']) ? $diagnosis['gaps'] : [];
    $priorityActions = isset($diagnosis['priority_actions']) && is_array($diagnosis['priority_actions']) ? $diagnosis['priority_actions'] : [];
    $tools = isset($diagnosis['suggested_tools']) && is_array($diagnosis['suggested_tools']) ? $diagnosis['suggested_tools'] : [];
    $escalation = isset($diagnosis['escalation']) && is_array($diagnosis['escalation']) ? $diagnosis['escalation'] : [];
?>
<div class="hustler-app">
    <aside class="hustler-sidebar">
        <div class="hustler-sidebar-top">
            <div class="hustler-sidebar-brand">Hustler</div>
            <div class="hustler-sidebar-subtitle">Founder execution engine</div>
        </div>

        <nav class="hustler-sidebar-nav">
            <a class="active" href="<?=base_url('hustler/dashboard')?>">Weekly Plan</a>
            <a href="<?=base_url('hustler/market-access')?>">Market Access</a>
            <a href="<?=base_url('hustler/logout')?>">Log Out</a>
        </nav>

        <div class="hustler-sidebar-section">
            <div class="hustler-sidebar-label">Tasks</div>
            <div id="hustler-task-list">
                <?php if (customCompute($taskItems)) { ?>
                    <?php foreach ($taskItems as $item) { ?>
                        <div class="hustler-plan-card">
                            <div class="hustler-plan-type">Task</div>
                            <div class="hustler-plan-title"><?=htmlspecialchars($item->title)?></div>
                            <div class="hustler-plan-copy"><?=htmlspecialchars((string) $item->description)?></div>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <div class="hustler-plan-empty">The AI will populate the weekly task stack after the founder conversation starts.</div>
                <?php } ?>
            </div>
        </div>

        <div class="hustler-sidebar-section">
            <div class="hustler-sidebar-label">Milestones</div>
            <div id="hustler-milestone-list">
                <?php if (customCompute($milestoneItems)) { ?>
                    <?php foreach ($milestoneItems as $item) { ?>
                        <div class="hustler-plan-card milestone">
                            <div class="hustler-plan-type">Milestone</div>
                            <div class="hustler-plan-title"><?=htmlspecialchars($item->title)?></div>
                            <div class="hustler-plan-copy"><?=htmlspecialchars((string) $item->description)?></div>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <div class="hustler-plan-empty">Milestones will appear here once the engine has enough data to route execution.</div>
                <?php } ?>
            </div>
        </div>
    </aside>

    <main class="hustler-main">
        <section class="hustler-hero">
            <div>
                <div class="hustler-eyebrow">Investor Demo Workspace</div>
                <h1>Founder diagnostic + execution routing</h1>
                <p>Use the chat to capture the founder context. The engine stores it, updates memory, diagnoses the business, and writes the next weekly plan.</p>
            </div>
            <div class="hustler-summary-card">
                <div class="hustler-summary-label">Current Founder Snapshot</div>
                <div class="hustler-summary-value" id="hustler-founder-name"><?=htmlspecialchars($profile->founder_name !== '' ? $profile->founder_name : 'Founder not identified yet')?></div>
                <div class="hustler-summary-meta" id="hustler-company-name"><?=htmlspecialchars($profile->company_name !== '' ? $profile->company_name : 'Company still being defined')?></div>
                <div class="hustler-stage-pill" id="hustler-stage-label"><?=htmlspecialchars($profile->stage_label)?></div>
            </div>
        </section>

        <section class="hustler-chat-panel">
            <div class="hustler-panel-head">
                <div>
                    <div class="hustler-panel-title">Founder Intake + AI Mentor</div>
                    <div class="hustler-panel-copy">Collect the founder details, keep long context, and convert the discussion into concrete execution steps.</div>
                </div>
            </div>

            <div class="hustler-chat-log" id="hustler-chat-log">
                <?php if (customCompute($chat_rows)) { ?>
                    <?php foreach ($chat_rows as $row) { ?>
                        <div class="hustler-chat-bubble <?=$row->role === 'user' ? 'user' : 'assistant'?>">
                            <?=nl2br(htmlspecialchars($row->message))?>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <div class="hustler-chat-bubble assistant">Start by describing the founder, the startup idea, available time, budget, traction, and the main constraint.</div>
                <?php } ?>
            </div>

            <div class="hustler-chat-form">
                <textarea id="hustler-chat-input" placeholder="Example: Founder is a solo operator, has 10 hours a week, $3k budget, idea is a concierge visa advisory service, no traction yet, and can access expat communities directly."></textarea>
                <button id="hustler-chat-send" class="hustlers-primary-btn" type="button">Run Diagnosis</button>
            </div>
        </section>
    </main>

    <aside class="hustler-right">
        <section class="hustler-right-card">
            <div class="hustler-right-title">Diagnosis</div>
            <div class="hustler-diagnosis-grid" id="hustler-diagnosis-panel">
                <div class="hustler-mini-stat">
                    <span>Status</span>
                    <strong><?=htmlspecialchars(isset($diagnosisCore['current_status']) ? (string) $diagnosisCore['current_status'] : 'Pending')?></strong>
                </div>
                <div class="hustler-mini-stat">
                    <span>Founder-Idea Fit</span>
                    <strong><?=htmlspecialchars(isset($diagnosisCore['founder_idea_fit']) ? (string) $diagnosisCore['founder_idea_fit'] : 'Pending')?></strong>
                </div>
                <div class="hustler-mini-stat full">
                    <span>Bottleneck</span>
                    <strong><?=htmlspecialchars(isset($diagnosisCore['bottleneck_identification']) ? (string) $diagnosisCore['bottleneck_identification'] : 'Pending')?></strong>
                </div>
            </div>
        </section>

        <section class="hustler-right-card">
            <div class="hustler-right-title">Gaps</div>
            <div class="hustler-tag-list" id="hustler-gap-list">
                <?php if (customCompute($gaps)) { ?>
                    <?php foreach ($gaps as $gap) { ?>
                        <span><?=htmlspecialchars((string) $gap)?></span>
                    <?php } ?>
                <?php } else { ?>
                    <span>Waiting for founder context</span>
                <?php } ?>
            </div>
        </section>

        <section class="hustler-right-card">
            <div class="hustler-right-title">Priority Actions</div>
            <div class="hustler-plain-list" id="hustler-priority-list">
                <?php if (customCompute($priorityActions)) { ?>
                    <?php foreach ($priorityActions as $action) { ?>
                        <div><?=htmlspecialchars((string) $action)?></div>
                    <?php } ?>
                <?php } else { ?>
                    <div>No action plan generated yet.</div>
                <?php } ?>
            </div>
        </section>

        <section class="hustler-right-card">
            <div class="hustler-right-title">Tools + Escalation</div>
            <div class="hustler-plain-list" id="hustler-tool-list">
                <?php if (customCompute($tools)) { ?>
                    <?php foreach ($tools as $tool) { ?>
                        <div><?=htmlspecialchars((string) $tool)?></div>
                    <?php } ?>
                <?php } else { ?>
                    <div>No tool recommendations yet.</div>
                <?php } ?>
            </div>
            <div class="hustler-escalation" id="hustler-escalation-box">
                <?php if (!empty($escalation)) { ?>
                    <strong><?=!empty($escalation['needs_human']) ? 'Needs human mentor' : 'AI can continue'?></strong>
                    <span><?=htmlspecialchars(isset($escalation['reason']) ? (string) $escalation['reason'] : '')?></span>
                <?php } else { ?>
                    <strong>Escalation not triggered</strong>
                    <span>The system will flag when a human mentor should intervene.</span>
                <?php } ?>
            </div>
        </section>
    </aside>
</div>

<script type="text/javascript">
    (function() {
        function escapeHtml(text) {
            return $('<div>').text(text || '').html();
        }

        function appendChat(role, text) {
            var bubbleClass = role === 'user' ? 'user' : 'assistant';
            $('#hustler-chat-log').append('<div class="hustler-chat-bubble ' + bubbleClass + '">' + escapeHtml(text).replace(/\n/g, '<br>') + '</div>');
            var log = $('#hustler-chat-log').get(0);
            if (log) {
                log.scrollTop = log.scrollHeight;
            }
        }

        function renderPlan(items) {
            var tasks = [];
            var milestones = [];
            (items || []).forEach(function(item) {
                if (item.item_type === 'milestone') {
                    milestones.push(item);
                } else {
                    tasks.push(item);
                }
            });

            var taskHtml = tasks.length ? tasks.map(function(item) {
                return '<div class="hustler-plan-card"><div class="hustler-plan-type">Task</div><div class="hustler-plan-title">' + escapeHtml(item.title) + '</div><div class="hustler-plan-copy">' + escapeHtml(item.description || '') + '</div></div>';
            }).join('') : '<div class="hustler-plan-empty">The AI will populate the weekly task stack after the founder conversation starts.</div>';

            var milestoneHtml = milestones.length ? milestones.map(function(item) {
                return '<div class="hustler-plan-card milestone"><div class="hustler-plan-type">Milestone</div><div class="hustler-plan-title">' + escapeHtml(item.title) + '</div><div class="hustler-plan-copy">' + escapeHtml(item.description || '') + '</div></div>';
            }).join('') : '<div class="hustler-plan-empty">Milestones will appear here once the engine has enough data to route execution.</div>';

            $('#hustler-task-list').html(taskHtml);
            $('#hustler-milestone-list').html(milestoneHtml);
        }

        function renderDiagnosis(diagnosis) {
            diagnosis = diagnosis || {};
            var core = diagnosis.diagnosis || {};
            var gaps = diagnosis.gaps || [];
            var actions = diagnosis.priority_actions || [];
            var tools = diagnosis.suggested_tools || [];
            var escalation = diagnosis.escalation || {};

            $('#hustler-diagnosis-panel').html(
                '<div class="hustler-mini-stat"><span>Status</span><strong>' + escapeHtml(core.current_status || 'Pending') + '</strong></div>' +
                '<div class="hustler-mini-stat"><span>Founder-Idea Fit</span><strong>' + escapeHtml(core.founder_idea_fit || 'Pending') + '</strong></div>' +
                '<div class="hustler-mini-stat full"><span>Bottleneck</span><strong>' + escapeHtml(core.bottleneck_identification || 'Pending') + '</strong></div>'
            );

            $('#hustler-gap-list').html((gaps.length ? gaps : ['Waiting for founder context']).map(function(item) {
                return '<span>' + escapeHtml(item) + '</span>';
            }).join(''));

            $('#hustler-priority-list').html((actions.length ? actions : ['No action plan generated yet.']).map(function(item) {
                return '<div>' + escapeHtml(item) + '</div>';
            }).join(''));

            $('#hustler-tool-list').html((tools.length ? tools : ['No tool recommendations yet.']).map(function(item) {
                return '<div>' + escapeHtml(item) + '</div>';
            }).join(''));

            var escalationTitle = escalation.needs_human ? 'Needs human mentor' : 'AI can continue';
            var escalationCopy = escalation.reason || 'The system will flag when a human mentor should intervene.';
            $('#hustler-escalation-box').html('<strong>' + escapeHtml(escalationTitle) + '</strong><span>' + escapeHtml(escalationCopy) + '</span>');
        }

        function sendChat() {
            var $input = $('#hustler-chat-input');
            var message = ($input.val() || '').trim();
            if (!message) {
                return;
            }

            appendChat('user', message);
            $input.val('');
            $('#hustler-chat-send').prop('disabled', true).text('Running...');

            $.post('<?=base_url('hustler/chat')?>', { message: message }, function(res) {
                if (res && res.ok) {
                    appendChat('assistant', res.reply || '');
                    renderPlan(res.action_items || []);
                    renderDiagnosis(res.diagnosis || {});
                    if (res.profile) {
                        $('#hustler-founder-name').text(res.profile.founder_name || 'Founder not identified yet');
                        $('#hustler-company-name').text(res.profile.company_name || 'Company still being defined');
                        $('#hustler-stage-label').text(res.profile.stage_label || 'Needs diagnosis');
                    }
                } else {
                    appendChat('assistant', (res && res.error) ? res.error : 'AI error.');
                }
            }, 'json').fail(function() {
                appendChat('assistant', 'Request failed. Please try again.');
            }).always(function() {
                $('#hustler-chat-send').prop('disabled', false).text('Run Diagnosis');
            });
        }

        $('#hustler-chat-send').on('click', sendChat);
        $('#hustler-chat-input').on('keydown', function(event) {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                sendChat();
            }
        });
    })();
</script>
