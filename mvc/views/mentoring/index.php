<?php
    $founderName = customCompute($threadFounder) ? $threadFounder->name : 'Founder';
    $mentorName = customCompute($threadMentor) ? $threadMentor->name : 'Mentor';
    $isFounder = ((int) $this->session->userdata('usertypeID') === 3);
    $assignedFounders = isset($assignedFounders) ? $assignedFounders : [];
    $tasks = isset($tasks) ? $tasks : [];
    $milestones = isset($milestones) ? $milestones : [];
    $milestoneMap = isset($milestoneMap) ? $milestoneMap : [];
?>

<div class="hatchers-page">
    <div class="hatchers-page-header">
        <div>
            <h1><?=$isFounder ? 'Mentoring' : 'Mentoring Workspace'?></h1>
            <p><?=$isFounder ? htmlspecialchars($founderName) . ' and ' . htmlspecialchars($mentorName) . ' communication hub.' : 'Communication, meetings, milestones, and weekly tasks live together in one founder workspace.'?></p>
        </div>
    </div>

    <?php if (!customCompute($threadFounder) || !customCompute($threadMentor)) { ?>
        <div class="hatchers-empty-state">No mentoring relationship is active yet.</div>
    <?php } else { ?>
        <?php if (!$isFounder) { ?>
            <section class="hatchers-card-panel hatchers-founder-roster">
                <div class="hatchers-panel-title">Assigned Founders</div>
                <div class="hatchers-founder-roster-grid">
                    <?php foreach ($assignedFounders as $founder) { ?>
                        <a class="hatchers-founder-chip <?=$threadFounder->studentID == $founder->studentID ? 'is-active' : ''?>" href="<?=base_url('mentoring/index?founder_id=' . $founder->studentID)?>">
                            <span class="name"><?=htmlspecialchars($founder->name)?></span>
                            <span class="meta"><?=htmlspecialchars(!empty($founder->email) ? $founder->email : 'Founder')?></span>
                        </a>
                    <?php } ?>
                </div>
            </section>
        <?php } ?>

        <div class="hatchers-page-intro">
            <div class="eyebrow">Core workflow</div>
            <div class="title"><?=$isFounder ? 'Stay aligned with your mentor' : 'Plan the week and keep momentum visible'?></div>
            <div class="copy"><?=$isFounder ? 'Use this space to request meetings, review upcoming sessions, and keep the conversation moving.' : 'Schedule meetings, assign milestones, place tasks on the founder calendar, and keep all communication in one place.'?></div>
            <?php if (!empty($threadFounder->remarks)) { ?>
                <div class="copy" style="margin-top:8px;"><?=htmlspecialchars((string) $threadFounder->remarks)?></div>
            <?php } ?>
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
                                <?php if (!empty($meeting->notes)) { ?>
                                    <div class="hatchers-line-copy muted"><?=htmlspecialchars((string) $meeting->notes)?></div>
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
                                <?php if (!$isFounder && (string) $meeting->request_status === 'reschedule_requested') { ?>
                                    <div class="actions">
                                        <a href="<?=base_url('mentoring/respond_meeting/'.$meeting->founder_meeting_id.'/accepted')?>">Confirm new time</a>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>

                        <?php if ($isFounder && in_array((string) $meeting->request_status, ['scheduled', 'accepted'], true)) { ?>
                            <div class="hatchers-card-actions" style="margin: -4px 0 14px;">
                                <a class="hatchers-inline-btn" href="<?=base_url('mentoring/founder_meeting_response/'.$meeting->founder_meeting_id.'/accepted')?>">Accept timing</a>
                            </div>
                            <form class="hatchers-stacked-form" method="post" action="<?=base_url('mentoring/founder_meeting_response/'.$meeting->founder_meeting_id.'/reschedule_requested')?>">
                                <input type="datetime-local" name="starts_at" value="<?=!empty($meeting->starts_at) ? date('Y-m-d\TH:i', strtotime($meeting->starts_at)) : ''?>">
                                <input type="datetime-local" name="ends_at" value="<?=!empty($meeting->ends_at) ? date('Y-m-d\TH:i', strtotime($meeting->ends_at)) : ''?>">
                                <textarea name="notes" placeholder="Suggest a new time or explain the reschedule request"></textarea>
                                <button class="hatchers-ghost-btn" type="submit">Request reschedule</button>
                            </form>
                        <?php } ?>
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

        <?php if (!$isFounder) { ?>
            <div class="hatchers-two-column">
                <section class="hatchers-card-panel">
                    <div class="hatchers-panel-title">Milestones</div>
                    <?php if (customCompute($milestones)) { ?>
                        <?php foreach ($milestones as $milestone) { ?>
                            <div class="hatchers-line-card">
                                <div>
                                    <div class="hatchers-line-title"><?=htmlspecialchars($milestone->title)?></div>
                                    <div class="hatchers-line-copy"><?=htmlspecialchars((string) $milestone->description)?></div>
                                </div>
                                <div class="hatchers-line-meta">
                                    <?=!empty($milestone->due_date) ? date('M j, Y', strtotime($milestone->due_date)) : 'No due date'?>
                                </div>
                            </div>
                        <?php } ?>
                    <?php } else { ?>
                        <div class="hatchers-empty-state small">No milestones have been set yet.</div>
                    <?php } ?>

                    <form class="hatchers-stacked-form" method="post" action="<?=base_url('mentoring/add_milestone')?>">
                        <input type="hidden" name="founder_id" value="<?=$threadFounder->studentID?>">
                        <input type="text" name="title" placeholder="Milestone title" required>
                        <textarea name="description" placeholder="What outcome should the founder reach?"></textarea>
                        <input type="date" name="due_date">
                        <textarea name="notes" placeholder="Mentor notes"></textarea>
                        <button class="hatchers-inline-btn" type="submit">Add milestone</button>
                    </form>
                </section>

                <section class="hatchers-card-panel">
                    <div class="hatchers-panel-title">Tasks</div>
                    <?php if (customCompute($tasks)) { ?>
                        <?php foreach ($tasks as $task) { ?>
                            <?php $taskMilestone = (!empty($task->milestone_id) && isset($milestoneMap[$task->milestone_id])) ? $milestoneMap[$task->milestone_id]->title : 'Weekly task'; ?>
                            <div class="hatchers-task-card <?=((int) $task->status === 1) ? 'is-complete' : ''?>">
                                <div class="hatchers-task-top">
                                    <span><?=htmlspecialchars($taskMilestone)?></span>
                                    <span><?=!empty($task->due_date) ? date('M j, Y', strtotime($task->due_date)) : 'No due date'?></span>
                                </div>
                                <div>
                                    <div class="hatchers-task-title"><?=htmlspecialchars($task->title)?></div>
                                    <div class="hatchers-task-copy"><?=htmlspecialchars((string) $task->description)?></div>
                                </div>
                            </div>
                        <?php } ?>
                    <?php } else { ?>
                        <div class="hatchers-empty-state small">No tasks have been assigned yet.</div>
                    <?php } ?>

                    <form class="hatchers-stacked-form" method="post" action="<?=base_url('mentoring/add_task')?>">
                        <input type="hidden" name="founder_id" value="<?=$threadFounder->studentID?>">
                        <input type="text" name="title" placeholder="Task title" required>
                        <textarea name="description" placeholder="What should the founder do?"></textarea>
                        <input type="date" name="due_date">
                        <select name="milestone_id">
                            <option value="0">No milestone</option>
                            <?php foreach ($milestones as $milestone) { ?>
                                <option value="<?=$milestone->milestone_meta_id?>"><?=htmlspecialchars($milestone->title)?></option>
                            <?php } ?>
                        </select>
                        <button class="hatchers-inline-btn" type="submit">Add task</button>
                    </form>
                </section>
            </div>
        <?php } ?>
    <?php } ?>
</div>
