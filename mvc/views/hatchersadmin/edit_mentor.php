<script type="text/javascript">
    document.body.classList.add('hatchers-mentor');
</script>

<div class="hatchers-dashboard hatchers-mentor-detail">
    <div class="hatchers-header">
        <div>
            <h1>Edit Mentor</h1>
            <p>Update mentor profile and login.</p>
        </div>
        <div class="hatchers-header-actions">
            <a class="hatchers-ghost-btn" href="<?=base_url('hatchersadmin/profiles')?>">Back to Profiles</a>
        </div>
    </div>

    <div class="hatchers-detail-section">
        <form class="hatchers-form" method="post" action="<?=base_url('hatchersadmin/update_mentor/'.$mentor->teacherID)?>">
            <input type="text" name="name" value="<?=htmlspecialchars($mentor->name)?>" required>
            <input type="email" name="email" value="<?=htmlspecialchars((string) $mentor->email)?>" placeholder="Email">
            <input type="text" name="phone" value="<?=htmlspecialchars((string) $mentor->phone)?>" placeholder="Phone">
            <input type="text" name="username" value="<?=htmlspecialchars($mentor->username)?>" required>
            <input type="password" name="password" placeholder="New password (leave blank to keep)">
            <input type="text" name="designation" value="<?=htmlspecialchars((string) $mentor->designation)?>" placeholder="Designation">
            <select name="sex" required>
                <option value="">Sex</option>
                <option value="Male" <?=$mentor->sex == 'Male' ? 'selected' : ''?>>Male</option>
                <option value="Female" <?=$mentor->sex == 'Female' ? 'selected' : ''?>>Female</option>
                <option value="Other" <?=$mentor->sex == 'Other' ? 'selected' : ''?>>Other</option>
            </select>
            <input type="date" name="dob" value="<?=htmlspecialchars((string) $mentor->dob)?>" required>
            <input type="text" name="religion" value="<?=htmlspecialchars((string) $mentor->religion)?>" placeholder="Religion">
            <input type="text" name="address" value="<?=htmlspecialchars((string) $mentor->address)?>" placeholder="Address">
            <button class="hatchers-cta" type="submit">Save Changes</button>
        </form>
    </div>
</div>

