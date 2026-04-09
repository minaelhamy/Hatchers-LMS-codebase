<?php
    $founderName = customCompute($threadFounder) ? $threadFounder->name : 'Founder';
    $mentorName = customCompute($threadMentor) ? $threadMentor->name : 'Mentor';
    $isFounder = ((int) $this->session->userdata('usertypeID') === 3);
?>

<div class="hatchers-page">
    <div class="hatchers-page-header">
        <div>
            <h1>Mentoring</h1>
            <p><?=htmlspecialchars($founderName)?> and <?=htmlspecialchars($mentorName)?> communication hub.</p>
        </div>
    </div>

    <?php if (!customCompute($threadFounder) || !customCompute($threadMentor)) { ?>
        <div class="hatchers-empty-state">No mentoring relationship is active yet.</div>
    <?php } else { ?>
        <div class="hatchers-page-intro">
            <div class="eyebrow">Core workflow</div>
            <div class="title">Communication is the product center</div>
            <div class="copy">Meetings, requests, and direct messages live together here so mentor and founder always share one source of truth.</div>
        </div>
        <div class="hatchers-two-column">
            <section class="hatchers-card-panel">
                <div class="hatchers-panel-title">Meetings</div>
                <?php if (customCompute($meetings)) { ?>
                    <?php foreach ($meetings as $meeting) { ?>
                        <div class="hatchers-line-card">
                            <div>
                                <div class="hatchers-line-title"><?=htmlspecialchars(!empty($meeting->title) ? $meeting->title : 'Mentoring session')?></div>
                                <div class="hatchers-line-copy"><?=htmlspecialchars((string) $meeting->description)?></div>
                                <?php if (!empty($meeting->join_link)) { ?>
                                    <div class="hatchers-line-copy"><a href="<?=htmlspecialchars($meeting->join_link)?>" target="_blank">Join session</a></div>
                                <?php } ?>
                            </div>
                            <div class="hatchers-line-meta">
                                <div><?=date('M j, Y g:ia', strtotime($meeting->starts_at))?></div>
                                <div class="status"><?=htmlspecialchars((string) $meeting->request_status)?></div>
                                <?php if (!$isFounder && (string) $meeting->request_status === 'requested') { ?>
                                    <div class="actions">
                                        <a href="<?=base_url('mentoring/respond_meeting/'.$meeting->founder_meeting_id.'/accepted')?>">Accept</a>
                                        <a href="<?=base_url('mentoring/respond_meeting/'.$meeting->founder_meeting_id.'/declined')?>">Decline</a>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <div class="hatchers-empty-state small">No meetings scheduled yet.</div>
                <?php } ?>

                <form class="hatchers-stacked-form" method="post" action="<?=base_url('mentoring/request_meeting')?>">
                    <input type="hidden" name="founder_id" value="<?=$threadFounder->studentID?>">
                    <input type="text" name="title" placeholder="<?=$isFounder ? 'Request title' : 'Meeting title'?>">
                    <textarea name="description" placeholder="<?=$isFounder ? 'What do you want to discuss?' : 'Agenda or notes'?>"></textarea>
                    <input type="datetime-local" name="starts_at" required>
                    <input type="datetime-local" name="ends_at">
                    <?php if (!$isFounder) { ?>
                        <input type="url" name="join_link" placeholder="Meeting link">
                    <?php } ?>
                    <textarea name="notes" placeholder="Extra notes"></textarea>
                    <button class="hatchers-inline-btn" type="submit"><?=$isFounder ? 'Request meeting' : 'Schedule meeting'?></button>
                </form>
            </section>

            <section class="hatchers-card-panel">
                <div class="hatchers-panel-title">Messages</div>
                <div class="hatchers-chat-thread">
                    <?php if (customCompute($messages)) { ?>
                        <?php foreach ($messages as $message) { ?>
                            <div class="hatchers-chat-row <?=$message->sender_usertypeID == $this->session->userdata('usertypeID') ? 'is-own' : ''?>">
                                <div class="bubble"><?=nl2br(htmlspecialchars($message->message))?></div>
                                <div class="time"><?=date('M j, g:ia', strtotime($message->created_at))?></div>
                            </div>
                        <?php } ?>
                    <?php } else { ?>
                        <div class="hatchers-empty-state small">No messages yet.</div>
                    <?php } ?>
                </div>

                <form class="hatchers-inline-form" method="post" action="<?=base_url('mentoring/send_message')?>">
                    <input type="hidden" name="founder_id" value="<?=$threadFounder->studentID?>">
                    <input type="text" name="message" placeholder="Type your message..." required>
                    <button class="hatchers-inline-btn" type="submit">Send</button>
                </form>
            </section>
        </div>
    <?php } ?>
</div>
