<script type="text/javascript">
    document.body.classList.add('hatchers-mentor');
</script>

<div class="hatchers-dashboard hatchers-mentor-detail">
    <div class="hatchers-header">
        <div>
            <h1>Profiles</h1>
            <p>Create founder and mentor accounts.</p>
        </div>
        <div class="hatchers-header-actions">
            <a class="hatchers-ghost-btn" href="<?=base_url('hatchersadmin/assignments')?>">Assignments</a>
            <a class="hatchers-ghost-btn" href="<?=base_url('hatchersadmin/nav')?>">Navigation</a>
            <a class="hatchers-ghost-btn" href="<?=base_url('hatchersadmin/ai')?>">AI Settings</a>
        </div>
    </div>

    <div class="hatchers-detail-grid">
        <div class="hatchers-detail-section">
            <div class="hatchers-section-title">Create Founder</div>
            <form class="hatchers-form" method="post" action="<?=base_url('hatchersadmin/create_founder')?>">
                <input type="text" name="name" placeholder="Founder name" required>
                <input type="email" name="email" placeholder="Email">
                <input type="text" name="phone" placeholder="Phone">
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="text" name="registerNO" placeholder="Registration # (optional)">
                <input type="text" name="roll" placeholder="Roll (required)" required>
                <select name="classesID" required>
                    <option value="">Select Sprint</option>
                    <?php if (customCompute($classes)) { ?>
                        <?php foreach ($classes as $class) { ?>
                            <option value="<?=$class->classesID?>"><?=htmlspecialchars($class->classes)?></option>
                        <?php } ?>
                    <?php } ?>
                </select>
                <select name="sectionID" required>
                    <option value="">Select Section</option>
                    <?php if (customCompute($sections)) { ?>
                        <?php foreach ($sections as $section) { ?>
                            <option value="<?=$section->sectionID?>"><?=htmlspecialchars($section->section)?></option>
                        <?php } ?>
                    <?php } ?>
                </select>
                <select name="sex" required>
                    <option value="">Sex</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
                <input type="date" name="dob" placeholder="Date of birth">
                <input type="date" name="admission_date" placeholder="Admission date">
                <input type="text" name="religion" placeholder="Religion">
                <input type="text" name="bloodgroup" placeholder="Blood group">
                <input type="text" name="address" placeholder="Address">
                <input type="text" name="state" placeholder="State">
                <input type="text" name="country" placeholder="Country">
                <button class="hatchers-cta" type="submit">Create Founder</button>
                <div class="hatchers-list-subtitle">A guardian account is auto-created with username suffix `_g`.</div>
            </form>
        </div>

        <div class="hatchers-detail-section">
            <div class="hatchers-section-title">Create Mentor</div>
            <form class="hatchers-form" method="post" action="<?=base_url('hatchersadmin/create_mentor')?>">
                <input type="text" name="name" placeholder="Mentor name" required>
                <input type="email" name="email" placeholder="Email">
                <input type="text" name="phone" placeholder="Phone">
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="text" name="designation" placeholder="Designation (Mentor)">
                <select name="sex" required>
                    <option value="">Sex</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
                <input type="date" name="dob" placeholder="Date of birth" required>
                <input type="text" name="religion" placeholder="Religion">
                <input type="text" name="address" placeholder="Address">
                <button class="hatchers-cta" type="submit">Create Mentor</button>
            </form>
        </div>
    </div>

    <div class="hatchers-detail-section">
        <div class="hatchers-section-title">Bulk Import (CSV)</div>
        <form class="hatchers-form" method="post" enctype="multipart/form-data" action="<?=base_url('hatchersadmin/preview_import')?>">
            <select name="import_type" required>
                <option value="founder">Founders</option>
                <option value="mentor">Mentors</option>
            </select>
            <input type="file" name="csv_file" accept=".csv" required>
            <button class="hatchers-cta" type="submit">Preview Import</button>
            <div class="hatchers-list-subtitle">
                Founder CSV columns: name, email, phone, username, password, classesID, sectionID, roll
            </div>
            <div class="hatchers-list-subtitle">
                Mentor CSV columns: name, email, phone, username, password, sex, dob, designation
            </div>
            <div class="hatchers-list-subtitle">
                Download templates:
                <a class="hatchers-ghost-btn" href="<?=base_url('hatchersadmin/download_founder_template')?>">Founders CSV</a>
                <a class="hatchers-ghost-btn" href="<?=base_url('hatchersadmin/download_mentor_template')?>">Mentors CSV</a>
            </div>
        </form>
    </div>

    <div class="hatchers-detail-section">
        <div class="hatchers-section-title">Existing Founders</div>
        <div class="hatchers-list">
            <?php if (customCompute($founders)) { ?>
                <?php foreach ($founders as $founder) { ?>
                    <div class="hatchers-list-item">
                        <div>
                            <div class="hatchers-list-title"><?=htmlspecialchars($founder->name)?></div>
                            <div class="hatchers-list-subtitle"><?=htmlspecialchars((string) $founder->email)?> • <?=htmlspecialchars((string) $founder->username)?></div>
                        </div>
                        <div class="hatchers-form">
                            <a class="hatchers-ghost-btn" href="<?=base_url('hatchersadmin/edit_founder/'.$founder->studentID)?>">Edit</a>
                            <a class="hatchers-ghost-btn" href="<?=base_url('hatchersadmin/delete_founder/'.$founder->studentID)?>">Delete</a>
                        </div>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <div class="hatchers-empty">
                    <div class="hatchers-empty-title">No founders yet</div>
                    <div class="hatchers-empty-subtitle">Create a founder above to get started.</div>
                </div>
            <?php } ?>
        </div>
    </div>

    <div class="hatchers-detail-section">
        <div class="hatchers-section-title">Existing Mentors</div>
        <div class="hatchers-list">
            <?php if (customCompute($mentors)) { ?>
                <?php foreach ($mentors as $mentor) { ?>
                    <div class="hatchers-list-item">
                        <div>
                            <div class="hatchers-list-title"><?=htmlspecialchars($mentor->name)?></div>
                            <div class="hatchers-list-subtitle"><?=htmlspecialchars((string) $mentor->email)?> • <?=htmlspecialchars((string) $mentor->username)?></div>
                        </div>
                        <div class="hatchers-form">
                            <a class="hatchers-ghost-btn" href="<?=base_url('hatchersadmin/edit_mentor/'.$mentor->teacherID)?>">Edit</a>
                            <a class="hatchers-ghost-btn" href="<?=base_url('hatchersadmin/delete_mentor/'.$mentor->teacherID)?>">Delete</a>
                        </div>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <div class="hatchers-empty">
                    <div class="hatchers-empty-title">No mentors yet</div>
                    <div class="hatchers-empty-subtitle">Create a mentor above to get started.</div>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
