<script type="text/javascript">
    document.body.classList.add('hatchers-mentor');
</script>

<div class="hatchers-dashboard hatchers-mentor-detail">
    <div class="hatchers-header">
        <div>
            <h1>Edit Founder</h1>
            <p>Update founder profile and login.</p>
        </div>
        <div class="hatchers-header-actions">
            <a class="hatchers-ghost-btn" href="<?=base_url('hatchersadmin/profiles')?>">Back to Profiles</a>
        </div>
    </div>

    <div class="hatchers-detail-section">
        <form class="hatchers-form" method="post" action="<?=base_url('hatchersadmin/update_founder/'.$founder->studentID)?>">
            <input type="text" name="name" value="<?=htmlspecialchars($founder->name)?>" required>
            <input type="email" name="email" value="<?=htmlspecialchars((string) $founder->email)?>" placeholder="Email">
            <input type="text" name="phone" value="<?=htmlspecialchars((string) $founder->phone)?>" placeholder="Phone">
            <input type="text" name="username" value="<?=htmlspecialchars($founder->username)?>" required>
            <input type="password" name="password" placeholder="New password (leave blank to keep)">
            <input type="hidden" name="roll" value="<?=htmlspecialchars((string) $founder->roll)?>">
            <select name="sex" required>
                <option value="">Sex</option>
                <option value="Male" <?=$founder->sex == 'Male' ? 'selected' : ''?>>Male</option>
                <option value="Female" <?=$founder->sex == 'Female' ? 'selected' : ''?>>Female</option>
                <option value="Other" <?=$founder->sex == 'Other' ? 'selected' : ''?>>Other</option>
            </select>
            <input type="text" name="religion" value="<?=htmlspecialchars((string) $founder->religion)?>" placeholder="Religion">
            <input type="text" name="bloodgroup" value="<?=htmlspecialchars((string) $founder->bloodgroup)?>" placeholder="Blood group">
            <input type="text" name="address" value="<?=htmlspecialchars((string) $founder->address)?>" placeholder="Address">
            <input type="text" name="state" value="<?=htmlspecialchars((string) $founder->state)?>" placeholder="State">
            <input type="text" name="country" value="<?=htmlspecialchars((string) $founder->country)?>" placeholder="Country">
            <textarea name="company_brief" placeholder="Company brief"><?=htmlspecialchars((string) $founder->company_brief)?></textarea>
            <button class="hatchers-cta" type="submit">Save Changes</button>
            <div class="hatchers-list-subtitle">Sprint and section are compatibility-only fields and are no longer part of the operational workflow.</div>
        </form>
    </div>
</div>
