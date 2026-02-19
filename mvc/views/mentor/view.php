<?php
    $founderName = customCompute($founder) ? $founder->name : 'Founder';
?>

<script type="text/javascript">
    document.body.classList.add('hatchers-mentor');
</script>

<div class="hatchers-dashboard hatchers-mentor-detail">
    <div class="hatchers-header">
        <div>
            <h1><?=htmlspecialchars($founderName)?></h1>
            <p>
                <?=htmlspecialchars((string) $founder->email)?>
                <?php if (!empty($founder->phone)) { ?>
                    • <?=htmlspecialchars((string) $founder->phone)?>
                <?php } ?>
            </p>
        </div>
        <div class="hatchers-header-actions">
            <a class="hatchers-ghost-btn" href="<?=base_url('dashboard')?>">Back to founders</a>
        </div>
    </div>

    <div class="hatchers-detail-grid">
        <div class="hatchers-detail-main">
            <section class="hatchers-detail-section">
                <div class="hatchers-section-title">Milestones</div>
                <div class="hatchers-list">
                    <?php if (customCompute($milestones)) { ?>
                        <?php foreach ($milestones as $milestone) { ?>
                            <div class="hatchers-list-item">
                                <div>
                                    <div class="hatchers-list-title"><?=htmlspecialchars($milestone->title)?></div>
                                    <div class="hatchers-list-subtitle"><?=htmlspecialchars((string) $milestone->description)?></div>
                                </div>
                                <div class="hatchers-list-meta">
                                    <?php if (!empty($milestone->due_date)) { ?>
                                        Due <?=date('M j, Y', strtotime($milestone->due_date))?>
                                    <?php } else { ?>
                                        No due date
                                    <?php } ?>
                                </div>
                            </div>
                        <?php } ?>
                    <?php } else { ?>
                        <div class="hatchers-empty">
                            <div class="hatchers-empty-title">No milestones yet</div>
                            <div class="hatchers-empty-subtitle">Add the first milestone below.</div>
                        </div>
                    <?php } ?>
                </div>

                <form class="hatchers-form" method="post" action="<?=base_url('mentor/add_milestone')?>">
                    <input type="hidden" name="founder_id" value="<?=$founder->studentID?>">
                    <div class="hatchers-form-title">Add milestone</div>
                    <input type="text" name="title" placeholder="Milestone title" required>
                    <input type="text" name="description" placeholder="Short description">
                    <input type="date" name="due_date">
                    <textarea name="notes" placeholder="Notes"></textarea>
                    <button class="hatchers-cta" type="submit">Add milestone</button>
                </form>
            </section>

            <section class="hatchers-detail-section">
                <div class="hatchers-section-title">Mentoring Meetings</div>
                <div class="hatchers-list">
                    <?php if (customCompute($meetings)) { ?>
                        <?php foreach ($meetings as $meeting) { ?>
                            <div class="hatchers-list-item">
                                <div>
                                    <div class="hatchers-list-title">1 on 1 Session</div>
                                    <div class="hatchers-list-subtitle"><?=htmlspecialchars((string) $meeting->notes)?></div>
                                </div>
                                <div class="hatchers-list-meta">
                                    <?=date('M j, Y g:ia', strtotime($meeting->starts_at))?>
                                </div>
                            </div>
                        <?php } ?>
                    <?php } else { ?>
                        <div class="hatchers-empty">
                            <div class="hatchers-empty-title">No sessions scheduled</div>
                            <div class="hatchers-empty-subtitle">Add the next mentoring session below.</div>
                        </div>
                    <?php } ?>
                </div>

                <form class="hatchers-form" method="post" action="<?=base_url('mentor/add_meeting')?>">
                    <input type="hidden" name="founder_id" value="<?=$founder->studentID?>">
                    <div class="hatchers-form-title">Schedule meeting</div>
                    <input type="datetime-local" name="starts_at" required>
                    <input type="datetime-local" name="ends_at">
                    <textarea name="notes" placeholder="Meeting notes"></textarea>
                    <button class="hatchers-cta" type="submit">Add meeting</button>
                </form>
            </section>
        </div>

        <aside class="hatchers-detail-side">
            <section class="hatchers-detail-section">
                <div class="hatchers-section-title">Learning</div>
                <div class="hatchers-list">
                    <?php if (customCompute($learning)) { ?>
                        <?php foreach ($learning as $lesson) { ?>
                            <div class="hatchers-list-item">
                                <div>
                                    <div class="hatchers-list-title"><?=htmlspecialchars($lesson->title)?></div>
                                    <div class="hatchers-list-subtitle"><?=htmlspecialchars((string) $lesson->subtitle)?></div>
                                </div>
                                <div class="hatchers-list-meta">
                                    <?php if (!empty($lesson->starts_at)) { ?>
                                        <?=date('M j, Y g:ia', strtotime($lesson->starts_at))?>
                                    <?php } else { ?>
                                        Scheduled soon
                                    <?php } ?>
                                </div>
                            </div>
                        <?php } ?>
                    <?php } else { ?>
                        <div class="hatchers-empty">
                            <div class="hatchers-empty-title">No learning scheduled</div>
                            <div class="hatchers-empty-subtitle">Add the next lesson below.</div>
                        </div>
                    <?php } ?>
                </div>

                <form class="hatchers-form" method="post" action="<?=base_url('mentor/add_learning')?>">
                    <input type="hidden" name="founder_id" value="<?=$founder->studentID?>">
                    <div class="hatchers-form-title">Add learning</div>
                    <input type="text" name="title" placeholder="Lesson title" required>
                    <input type="text" name="subtitle" placeholder="Subtitle">
                    <input type="datetime-local" name="starts_at">
                    <button class="hatchers-cta" type="submit">Add lesson</button>
                </form>
            </section>

            <section class="hatchers-detail-section">
                <div class="hatchers-section-title">Tasks</div>
                <div class="hatchers-list">
                    <?php if (customCompute($tasks)) { ?>
                        <?php foreach ($tasks as $task) { ?>
                            <div class="hatchers-list-item">
                                <div>
                                    <div class="hatchers-list-title"><?=htmlspecialchars($task->title)?></div>
                                    <div class="hatchers-list-subtitle"><?=htmlspecialchars((string) $task->description)?></div>
                                </div>
                                <div class="hatchers-list-meta">
                                    <?php if (!empty($task->due_date)) { ?>
                                        Due <?=date('M j, Y', strtotime($task->due_date))?>
                                    <?php } else { ?>
                                        No due date
                                    <?php } ?>
                                </div>
                            </div>
                        <?php } ?>
                    <?php } else { ?>
                        <div class="hatchers-empty">
                            <div class="hatchers-empty-title">No tasks yet</div>
                            <div class="hatchers-empty-subtitle">Add the first task below.</div>
                        </div>
                    <?php } ?>
                </div>

                <form class="hatchers-form" method="post" action="<?=base_url('mentor/add_task')?>">
                    <input type="hidden" name="founder_id" value="<?=$founder->studentID?>">
                    <div class="hatchers-form-title">Add task</div>
                    <input type="text" name="title" placeholder="Task title" required>
                    <textarea name="description" placeholder="Task description"></textarea>
                    <select name="milestone_id">
                        <option value="0">No milestone</option>
                        <?php if (customCompute($milestones)) { ?>
                            <?php foreach ($milestones as $milestone) { ?>
                                <option value="<?=$milestone->milestone_meta_id?>"><?=htmlspecialchars($milestone->title)?></option>
                            <?php } ?>
                        <?php } ?>
                    </select>
                    <input type="date" name="due_date">
                    <button class="hatchers-cta" type="submit">Add task</button>
                </form>
            </section>
        </aside>
    </div>
</div>
