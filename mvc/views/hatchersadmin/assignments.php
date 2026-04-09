<script type="text/javascript">
    document.body.classList.add('hatchers-mentor');
</script>

<div class="hatchers-dashboard hatchers-mentor-detail">
    <div class="hatchers-header">
        <div>
            <h1>Mentor Assignments</h1>
            <p>Match each founder with the mentor who will guide their weekly tasks, meetings, and learning path.</p>
        </div>
        <div class="hatchers-header-actions">
            <a class="hatchers-ghost-btn" href="<?=base_url('hatchersadmin/nav')?>">Navigation</a>
            <a class="hatchers-ghost-btn" href="<?=base_url('hatchersadmin/ai')?>">AI Settings</a>
            <a class="hatchers-ghost-btn" href="<?=base_url('hatchersadmin/profiles')?>">Profiles</a>
        </div>
    </div>

    <div class="hatchers-stats-grid">
        <div class="hatchers-stat-card">
            <div class="eyebrow">Founders</div>
            <div class="value"><?=customCompute($founders) ? count($founders) : 0?></div>
            <div class="copy">Founder accounts ready for assignment.</div>
        </div>
        <div class="hatchers-stat-card">
            <div class="eyebrow">Mentors</div>
            <div class="value"><?=customCompute($mentors) ? count($mentors) : 0?></div>
            <div class="copy">Mentors available in the LMS.</div>
        </div>
        <div class="hatchers-stat-card">
            <div class="eyebrow">Assigned</div>
            <div class="value"><?=customCompute($assignmentMap) ? count($assignmentMap) : 0?></div>
            <div class="copy">Active founder-mentor relationships.</div>
        </div>
    </div>

    <div class="hatchers-detail-section">
        <div class="hatchers-page-intro">
            <div class="eyebrow">Operational rule</div>
            <div class="title">One active mentor per founder</div>
            <div class="copy">Assignments define who can schedule meetings, send messages, and manage the founder’s weekly plan.</div>
        </div>
        <div class="hatchers-list">
            <?php if (customCompute($founders)) { ?>
                <?php foreach ($founders as $founder) { ?>
                    <?php
                        $assigned = isset($assignmentMap[$founder->studentID]) ? $assignmentMap[$founder->studentID] : null;
                        $assignedMentorId = $assigned ? $assigned->mentor_id : 0;
                    ?>
                    <form class="hatchers-list-item hatchers-assignment-card" method="post" action="<?=base_url('hatchersadmin/assign')?>">
                        <input type="hidden" name="founder_id" value="<?=$founder->studentID?>">
                        <div class="hatchers-assignment-avatar">
                            <img src="<?=imagelink($founder->photo)?>" alt="">
                        </div>
                        <div>
                            <div class="hatchers-list-title"><?=htmlspecialchars($founder->name)?></div>
                            <div class="hatchers-list-subtitle"><?=htmlspecialchars((string) $founder->email)?></div>
                            <div class="hatchers-list-meta"><?=$assigned ? 'Assigned' : 'Waiting for mentor assignment'?></div>
                        </div>
                        <div class="hatchers-form">
                            <select name="mentor_id">
                                <option value="0">Unassigned</option>
                                <?php if (customCompute($mentors)) { ?>
                                    <?php foreach ($mentors as $mentor) { ?>
                                        <option value="<?=$mentor->teacherID?>" <?=$assignedMentorId == $mentor->teacherID ? 'selected' : '';?>>
                                            <?=htmlspecialchars($mentor->name)?>
                                        </option>
                                    <?php } ?>
                                <?php } ?>
                            </select>
                            <button class="hatchers-cta" type="submit">Save</button>
                            <a class="hatchers-ghost-btn" href="<?=base_url('hatchersadmin/edit_founder/'.$founder->studentID)?>">Edit</a>
                        </div>
                    </form>
                <?php } ?>
            <?php } else { ?>
                <div class="hatchers-empty">
                    <div class="hatchers-empty-title">No founders found</div>
                    <div class="hatchers-empty-subtitle">Add founders first, then return here to assign mentors.</div>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
