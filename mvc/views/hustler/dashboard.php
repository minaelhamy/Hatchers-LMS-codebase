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

    $founderName = trim((string) $profile->founder_name);
    $companyName = trim((string) $profile->company_name);
    $profilePhoto = isset($profile->profile_photo_url) ? trim((string) $profile->profile_photo_url) : '';
    $companyLogo = isset($profile->company_logo_url) ? trim((string) $profile->company_logo_url) : '';
    $marketAccessAllowed = isset($market_access_allowed) ? (bool) $market_access_allowed : false;
    $marketAccessReason = isset($market_access_reason) ? (string) $market_access_reason : '';
    $marketGateFlash = (string) $this->session->flashdata('hustler_market_gate_error');
    $marketLinkLabel = $marketAccessAllowed ? 'Market Access' : 'Market Access (Locked)';
    $marketLinkHref = $marketAccessAllowed ? base_url('hustler/market-access') : '#';
    $marketLinkTitle = $marketAccessAllowed ? 'Open Market Access' : ($marketAccessReason !== '' ? $marketAccessReason : 'Complete founder context first');
    $initials = 'FN';
    if ($founderName !== '') {
        $parts = preg_split('/\s+/', $founderName);
        if (is_array($parts) && customCompute($parts)) {
            $first = strtoupper(substr((string) $parts[0], 0, 1));
            $second = isset($parts[1]) ? strtoupper(substr((string) $parts[1], 0, 1)) : '';
            $initials = trim($first . $second);
            if ($initials === '') {
                $initials = 'FN';
            }
        }
    }
    $companyInitials = 'CO';
    if ($companyName !== '') {
        $cleanCompany = preg_replace('/[^A-Za-z0-9\s]/', ' ', $companyName);
        $companyParts = preg_split('/\s+/', trim((string) $cleanCompany));
        if (is_array($companyParts) && customCompute($companyParts)) {
            $firstCompany = strtoupper(substr((string) $companyParts[0], 0, 1));
            $secondCompany = isset($companyParts[1]) ? strtoupper(substr((string) $companyParts[1], 0, 1)) : '';
            $companyInitials = trim($firstCompany . $secondCompany);
            if ($companyInitials === '') {
                $companyInitials = 'CO';
            }
        }
    }
?>
<div class="hustler-app chat-first">
    <aside class="hustler-sidebar">
        <div class="hustler-sidebar-top">
            <div class="hustler-sidebar-brand">Hustler</div>
            <div class="hustler-sidebar-subtitle">Founder execution engine</div>
        </div>

        <nav class="hustler-sidebar-nav">
            <a class="active" href="<?=base_url('hustler/dashboard')?>">Weekly Plan</a>
            <a id="hustler-market-access-link" class="<?=$marketAccessAllowed ? '' : 'disabled'?>" href="<?=htmlspecialchars($marketLinkHref)?>" title="<?=htmlspecialchars($marketLinkTitle)?>"><?=htmlspecialchars($marketLinkLabel)?></a>
            <a href="<?=base_url('hustler/logout')?>">Log Out</a>
            <form method="post" action="<?=base_url('hustler/restart-profile')?>" onsubmit="return confirm('Restart profile and erase all generated data?');">
                <button class="hustler-nav-danger" type="submit">Restart Profile</button>
            </form>
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
        <?php if ($marketGateFlash !== '' || (!$marketAccessAllowed && $marketAccessReason !== '')) { ?>
            <div class="hustler-gate-message" id="hustler-gate-message">
                <?=htmlspecialchars($marketGateFlash !== '' ? $marketGateFlash : $marketAccessReason)?>
            </div>
        <?php } else { ?>
            <div class="hustler-gate-message" id="hustler-gate-message" style="display:none;"></div>
        <?php } ?>

        <section class="hustler-founder-card">
            <div class="hustler-founder-media-stack">
                <div class="hustler-founder-avatar" id="hustler-founder-avatar">
                    <?php if ($profilePhoto !== '') { ?>
                        <img id="hustler-founder-avatar-image" src="<?=htmlspecialchars($profilePhoto)?>" alt="Founder profile photo">
                    <?php } else { ?>
                        <span id="hustler-founder-avatar-fallback"><?=htmlspecialchars($initials)?></span>
                    <?php } ?>
                </div>
                <div class="hustler-company-avatar" id="hustler-company-avatar">
                    <?php if ($companyLogo !== '') { ?>
                        <img id="hustler-company-avatar-image" src="<?=htmlspecialchars($companyLogo)?>" alt="Company logo">
                    <?php } else { ?>
                        <span id="hustler-company-avatar-fallback"><?=htmlspecialchars($companyInitials)?></span>
                    <?php } ?>
                </div>
            </div>
            <div class="hustler-founder-copy">
                <div class="hustler-founder-name" id="hustler-founder-name"><?=htmlspecialchars($founderName !== '' ? $founderName : 'Founder profile pending')?></div>
                <div class="hustler-founder-company" id="hustler-company-name"><?=htmlspecialchars($companyName !== '' ? $companyName : 'Add founder company in the DB to display it here')?></div>
                <div class="hustler-stage-pill" id="hustler-stage-label"><?=htmlspecialchars($profile->stage_label)?></div>
            </div>
            <div class="hustler-upload-actions">
                <form method="post" action="<?=base_url('hustler/upload-media')?>" enctype="multipart/form-data" class="hustler-upload-form">
                    <input type="hidden" name="media_type" value="founder_photo">
                    <label for="hustler-founder-photo-input">Founder photo</label>
                    <input id="hustler-founder-photo-input" type="file" name="media_file" accept=".jpg,.jpeg,.png,.webp" required>
                    <button type="submit">Upload</button>
                </form>
                <form method="post" action="<?=base_url('hustler/upload-media')?>" enctype="multipart/form-data" class="hustler-upload-form">
                    <input type="hidden" name="media_type" value="company_logo">
                    <label for="hustler-company-logo-input">Company logo</label>
                    <input id="hustler-company-logo-input" type="file" name="media_file" accept=".jpg,.jpeg,.png,.webp" required>
                    <button type="submit">Upload</button>
                </form>
            </div>
        </section>

        <section class="hustler-chat-panel centered-chat">
            <div class="hustler-panel-head center">
                <div class="hustler-eyebrow">Welcome to Hustler AI</div>
                <div class="hustler-panel-title">Tell me about your startup idea</div>
                <div class="hustler-panel-copy">Start with: founder name, company name + logo, idea/product, and target customer profile. Once these are captured, Market Access unlocks automatically.</div>
            </div>

            <div class="hustler-chat-log" id="hustler-chat-log">
                <?php if (customCompute($chat_rows)) { ?>
                    <?php foreach ($chat_rows as $row) { ?>
                        <div class="hustler-chat-bubble <?=$row->role === 'user' ? 'user' : 'assistant'?>">
                            <?=nl2br(htmlspecialchars($row->message))?>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <div class="hustler-chat-bubble assistant">Welcome to Hatchers Hustler. Share 4 items to unlock Market Access: founder name, company name + logo, idea/product, and target customer profile. If you are unsure about ICP, I will help you define it.</div>
                <?php } ?>
            </div>

            <div class="hustler-chat-form">
                <textarea id="hustler-chat-input" placeholder="Example: We are building X for Y. Founder has 15h/week, $5k budget, strong sales skills, early waitlist of 40 leads, and needs a 30-day launch plan."></textarea>
                <button id="hustler-chat-send" class="hustlers-primary-btn" type="button">Send Message</button>
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
        var marketAccessUrl = '<?=base_url('hustler/market-access')?>';
        var marketGenerateUrl = '<?=base_url('hustler/generate-market-access')?>';
        var marketAccessUnlocked = <?=($marketAccessAllowed ? 'true' : 'false')?>;
        var marketPrefetchStarted = false;

        function escapeHtml(text) {
            return $('<div>').text(text || '').html();
        }

        function formatInline(text) {
            var safe = escapeHtml(text || '');
            safe = safe.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
            safe = safe.replace(/`([^`]+)`/g, '<code>$1</code>');
            return safe;
        }

        function formatAssistantText(text) {
            var raw = (text || '').replace(/\r/g, '');
            var lines = raw.split('\n');
            var html = '';
            var listMode = null;

            function closeList() {
                if (listMode === 'ul') html += '</ul>';
                if (listMode === 'ol') html += '</ol>';
                listMode = null;
            }

            lines.forEach(function(line) {
                var trimmed = line.trim();
                if (!trimmed) {
                    closeList();
                    return;
                }

                if (/^[-*]\s+/.test(trimmed)) {
                    if (listMode !== 'ul') {
                        closeList();
                        html += '<ul>';
                        listMode = 'ul';
                    }
                    html += '<li>' + formatInline(trimmed.replace(/^[-*]\s+/, '')) + '</li>';
                    return;
                }

                if (/^\d+\.\s+/.test(trimmed)) {
                    if (listMode !== 'ol') {
                        closeList();
                        html += '<ol>';
                        listMode = 'ol';
                    }
                    html += '<li>' + formatInline(trimmed.replace(/^\d+\.\s+/, '')) + '</li>';
                    return;
                }

                closeList();
                html += '<p>' + formatInline(trimmed) + '</p>';
            });

            closeList();
            if (!html) {
                html = '<p></p>';
            }
            return html;
        }

        function founderInitials(name) {
            name = (name || '').trim();
            if (!name) {
                return 'FN';
            }
            var parts = name.split(/\s+/);
            var first = parts[0] ? parts[0].charAt(0).toUpperCase() : '';
            var second = parts[1] ? parts[1].charAt(0).toUpperCase() : '';
            return (first + second) || 'FN';
        }

        function renderFounderAvatar(name, photoUrl) {
            var $avatar = $('#hustler-founder-avatar');
            if (!$avatar.length) {
                return;
            }

            if (photoUrl) {
                $avatar.html('<img id="hustler-founder-avatar-image" src="' + escapeHtml(photoUrl) + '" alt="Founder profile photo">');
            } else {
                $avatar.html('<span id="hustler-founder-avatar-fallback">' + escapeHtml(founderInitials(name)) + '</span>');
            }
        }

        function companyInitials(name) {
            name = (name || '').replace(/[^A-Za-z0-9\s]/g, ' ').trim();
            if (!name) {
                return 'CO';
            }
            var parts = name.split(/\s+/);
            var first = parts[0] ? parts[0].charAt(0).toUpperCase() : '';
            var second = parts[1] ? parts[1].charAt(0).toUpperCase() : '';
            return (first + second) || 'CO';
        }

        function renderCompanyAvatar(name, logoUrl) {
            var $avatar = $('#hustler-company-avatar');
            if (!$avatar.length) {
                return;
            }

            if (logoUrl) {
                $avatar.html('<img id="hustler-company-avatar-image" src="' + escapeHtml(logoUrl) + '" alt="Company logo">');
            } else {
                $avatar.html('<span id="hustler-company-avatar-fallback">' + escapeHtml(companyInitials(name)) + '</span>');
            }
        }

        function setGateMessage(message) {
            var $gate = $('#hustler-gate-message');
            if (!$gate.length) {
                return;
            }
            if (!message) {
                $gate.hide();
                return;
            }
            $gate.text(message).show();
        }

        function updateMarketAccessLink(allowed, reason) {
            var $link = $('#hustler-market-access-link');
            if (!$link.length) {
                return;
            }

            if (allowed) {
                $link.removeClass('disabled');
                $link.attr('href', marketAccessUrl);
                $link.attr('title', 'Open Market Access');
                $link.text('Market Access');
                return;
            }

            var title = reason || 'Complete founder context first';
            $link.addClass('disabled');
            $link.attr('href', '#');
            $link.attr('title', title);
            $link.text('Market Access (Locked)');
        }

        function prefetchMarketAssets() {
            if (marketPrefetchStarted) {
                return;
            }
            marketPrefetchStarted = true;
            setGateMessage('Market Access unlocked. Preparing Instagram and funnel assets now...');

            $.post(marketGenerateUrl, { focus: 'Auto-generate initial market access assets from founder context.' }, function(res) {
                if (res && res.ok) {
                    setGateMessage('Market Access unlocked. Initial assets are ready. Click Market Access in the left menu.');
                    return;
                }
                setGateMessage('Market Access unlocked. Click Market Access to generate assets now.');
            }, 'json').fail(function() {
                setGateMessage('Market Access unlocked. Click Market Access to generate assets now.');
            });
        }

        function appendChat(role, text) {
            var bubbleClass = role === 'user' ? 'user' : 'assistant';
            var body = role === 'assistant' ? formatAssistantText(text) : '<p>' + escapeHtml(text).replace(/\n/g, '<br>') + '</p>';
            $('#hustler-chat-log').append('<div class="hustler-chat-bubble ' + bubbleClass + '">' + body + '</div>');
            var log = $('#hustler-chat-log').get(0);
            if (log) {
                log.scrollTop = log.scrollHeight;
            }
        }

        function appendPendingBubble() {
            var bubbleId = 'hustler-pending-' + Date.now() + '-' + Math.floor(Math.random() * 1000);
            var html = '<div class="hustler-chat-bubble assistant pending" id="' + bubbleId + '"><span class="hustler-spinner" aria-hidden="true"></span><span class="hustler-thinking">Thinking...</span></div>';
            $('#hustler-chat-log').append(html);
            var log = $('#hustler-chat-log').get(0);
            if (log) {
                log.scrollTop = log.scrollHeight;
            }
            return bubbleId;
        }

        function resolvePendingBubble(bubbleId, text) {
            var $bubble = $('#' + bubbleId);
            if (!$bubble.length) {
                appendChat('assistant', text);
                return;
            }

            $bubble.removeClass('pending');
            $bubble.html(formatAssistantText(text || ''));
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
            $('#hustler-chat-send').prop('disabled', true).text('Generating...');
            var pendingBubbleId = appendPendingBubble();
            var requestStartedAt = Date.now();
            var holdMs = 5000 + Math.floor(Math.random() * 5001);

            function finishResponse(renderFn) {
                var elapsed = Date.now() - requestStartedAt;
                var wait = holdMs - elapsed;
                if (wait < 0) {
                    wait = 0;
                }

                setTimeout(function() {
                    renderFn();
                    $('#hustler-chat-send').prop('disabled', false).text('Send Message');
                }, wait);
            }

            $.post('<?=base_url('hustler/chat')?>', { message: message }, function(res) {
                if (res && res.ok) {
                    finishResponse(function() {
                        resolvePendingBubble(pendingBubbleId, res.reply || '');
                        renderPlan(res.action_items || []);
                        renderDiagnosis(res.diagnosis || {});
                        if (res.profile) {
                            $('#hustler-founder-name').text(res.profile.founder_name || 'Founder profile pending');
                            $('#hustler-company-name').text(res.profile.company_name || 'Add founder company in the DB to display it here');
                            $('#hustler-stage-label').text(res.profile.stage_label || 'Needs diagnosis');
                            renderFounderAvatar(res.profile.founder_name || '', res.profile.profile_photo_url || '');
                            renderCompanyAvatar(res.profile.company_name || '', res.profile.company_logo_url || '');
                        }
                        updateMarketAccessLink(!!res.market_access_allowed, res.market_access_reason || '');
                        if (res.market_access_allowed) {
                            if (!marketAccessUnlocked) {
                                marketAccessUnlocked = true;
                                prefetchMarketAssets();
                            } else if (!marketPrefetchStarted) {
                                setGateMessage('Market Access unlocked. Click Market Access in the left menu.');
                            } else {
                                setGateMessage($('#hustler-gate-message').text());
                            }
                        } else {
                            marketAccessUnlocked = false;
                            setGateMessage(res.market_access_reason || '');
                        }
                    });
                } else {
                    finishResponse(function() {
                        resolvePendingBubble(pendingBubbleId, (res && res.error) ? res.error : 'AI error.');
                    });
                }
            }, 'json').fail(function() {
                finishResponse(function() {
                    resolvePendingBubble(pendingBubbleId, 'Request failed. Please try again.');
                });
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
