<script type="text/javascript">
    document.body.classList.add('hatchers-mentor');
</script>

<div class="hatchers-dashboard hatchers-mentor-detail">
    <div class="hatchers-header">
        <div>
            <h1>Mentor Assignments</h1>
            <p>Assign one mentor to each founder.</p>
        </div>
        <div class="hatchers-header-actions">
            <a class="hatchers-ghost-btn" href="<?=base_url('hatchersadmin/nav')?>">Navigation</a>
            <a class="hatchers-ghost-btn" href="<?=base_url('hatchersadmin/ai')?>">AI Settings</a>
            <a class="hatchers-ghost-btn" href="<?=base_url('hatchersadmin/profiles')?>">Profiles</a>
        </div>
    </div>

    <div class="hatchers-detail-section">
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
