<?php
    $founderName = customCompute($founder) ? $founder->name : 'Founder';
    $isFounder = ((int) $this->session->userdata('usertypeID') === 3);
?>

<div class="hatchers-page">
    <div class="hatchers-page-header">
        <div>
            <h1>Launch Plan</h1>
            <p><?=htmlspecialchars($founderName)?>'s weekly milestones and tasks.</p>
        </div>
    </div>

    <?php if (!customCompute($founder)) { ?>
        <div class="hatchers-empty-state">Select a founder to view the launch plan.</div>
    <?php } else { ?>
        <div class="hatchers-card-panel">
            <div class="hatchers-panel-title">Weekly Progress</div>
            <div class="hatchers-founder-card-top">
                <div>
                    <div class="hatchers-line-title"><?=$weeklyProgress?>% complete</div>
                    <div class="hatchers-line-copy"><?=$weeklyScopeDone?> of <?=$weeklyScopeTotal?> milestones and tasks completed this week.</div>
                </div>
            </div>
            <div class="hatchers-progress-bar"><span style="width: <?=$weeklyProgress?>%"></span></div>
        </div>

        <div class="hatchers-page-intro">
            <div class="eyebrow">Execution track</div>
            <div class="title">Weekly clarity over school-style structure</div>
            <div class="copy">This is the founder’s real operating plan: milestones define outcomes, tasks define weekly execution, and completion status keeps mentor and founder aligned.</div>
        </div>
        <div class="hatchers-two-column">
            <section class="hatchers-card-panel">
                <div class="hatchers-panel-title">Milestones</div>
                <?php if (customCompute($milestones)) { ?>
                    <?php foreach ($milestones as $milestone) { ?>
                        <div class="hatchers-line-card <?=((int) $milestone->status === 1) ? 'is-complete' : ''?>">
                            <div>
                                <div class="hatchers-line-title"><?=htmlspecialchars($milestone->title)?></div>
                                <div class="hatchers-line-copy"><?=htmlspecialchars((string) $milestone->description)?></div>
                            </div>
                            <div class="hatchers-card-actions">
                                <div class="hatchers-line-meta">
                                    <?=!empty($milestone->due_date) ? date('M j, Y', strtotime($milestone->due_date)) : 'No due date'?>
                                </div>
                                <a class="hatchers-inline-btn" href="<?=base_url('launchplan/toggle_milestone/'.$milestone->milestone_meta_id) . (!empty($_GET['founder_id']) ? '?founder_id='.(int) $_GET['founder_id'] : '')?>">
                                    <?=((int) $milestone->status === 1) ? 'Mark Open' : 'Mark Complete'?>
                                </a>
                            </div>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <div class="hatchers-empty-state small">No milestones yet.</div>
                <?php } ?>
            </section>

            <section class="hatchers-card-panel">
                <div class="hatchers-panel-title">Tasks</div>
                <?php if (customCompute($tasks)) { ?>
                    <?php foreach ($tasks as $task) { ?>
                        <?php
                            $milestoneTitle = (!empty($task->milestone_id) && isset($milestoneMap[$task->milestone_id])) ? $milestoneMap[$task->milestone_id]->title : 'General task';
                        ?>
                        <div class="hatchers-task-card <?=((int) $task->status === 1) ? 'is-complete' : ''?>">
                            <div class="hatchers-task-top">
                                <span><?=htmlspecialchars($milestoneTitle)?></span>
                                <span><?=!empty($task->due_date) ? date('M j, Y', strtotime($task->due_date)) : 'No due date'?></span>
                            </div>
                            <div class="hatchers-task-title-row">
                                <div>
                                    <div class="hatchers-task-title"><?=htmlspecialchars($task->title)?></div>
                                    <div class="hatchers-task-copy"><?=htmlspecialchars((string) $task->description)?></div>
                                </div>
                                <a class="hatchers-inline-btn" href="<?=base_url('launchplan/toggle_task/'.$task->founder_task_id) . (!empty($_GET['founder_id']) ? '?founder_id='.(int) $_GET['founder_id'] : '')?>">
                                    <?=((int) $task->status === 1) ? 'Mark Open' : 'Mark Complete'?>
                                </a>
                            </div>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <div class="hatchers-empty-state small">No tasks assigned yet.</div>
                <?php } ?>
            </section>
        </div>
    <?php } ?>
</div>
