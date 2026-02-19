<script type="text/javascript">
    document.body.classList.add('hatchers-mentor');
</script>

<div class="hatchers-dashboard hatchers-mentor-detail">
    <div class="hatchers-header">
        <div>
            <h1>Navigation Manager</h1>
            <p>Control left and right navigation items for founders.</p>
        </div>
        <div class="hatchers-header-actions">
            <a class="hatchers-ghost-btn" href="<?=base_url('hatchersadmin/assignments')?>">Assignments</a>
            <a class="hatchers-ghost-btn" href="<?=base_url('hatchersadmin/ai')?>">AI Settings</a>
            <a class="hatchers-ghost-btn" href="<?=base_url('hatchersadmin/profiles')?>">Profiles</a>
        </div>
    </div>

    <div class="hatchers-detail-section">
        <form class="hatchers-form" method="post" action="<?=base_url('hatchersadmin/nav_save')?>">
            <div class="hatchers-form-title">Add item</div>
            <input type="hidden" name="hatchers_nav_item_id" value="0">
            <input type="text" name="label" placeholder="Label" required>
            <input type="text" name="icon" placeholder="Font Awesome icon (e.g., fa-home)">
            <input type="text" name="link" placeholder="Link (e.g., dashboard/index)">
            <select name="location">
                <option value="left">Left navigation</option>
                <option value="right_ai">Right AI tools</option>
            </select>
            <input type="number" name="sort_order" placeholder="Sort order" value="1">
            <select name="active">
                <option value="1">Active</option>
                <option value="0">Hidden</option>
            </select>
            <button class="hatchers-cta" type="submit">Add item</button>
        </form>
    </div>

    <div class="hatchers-detail-section">
        <div class="hatchers-section-title">Current items</div>
        <div class="hatchers-list">
            <?php if (customCompute($nav_items)) { ?>
                <?php foreach ($nav_items as $item) { ?>
                    <form class="hatchers-list-item" method="post" action="<?=base_url('hatchersadmin/nav_save')?>">
                        <input type="hidden" name="hatchers_nav_item_id" value="<?=$item->hatchers_nav_item_id?>">
                        <div class="hatchers-form">
                            <input type="text" name="label" value="<?=htmlspecialchars($item->label)?>" required>
                            <input type="text" name="icon" value="<?=htmlspecialchars((string) $item->icon)?>">
                            <input type="text" name="link" value="<?=htmlspecialchars((string) $item->link)?>">
                            <select name="location">
                                <option value="left" <?=$item->location == 'left' ? 'selected' : ''?>>Left navigation</option>
                                <option value="right_ai" <?=$item->location == 'right_ai' ? 'selected' : ''?>>Right AI tools</option>
                            </select>
                            <input type="number" name="sort_order" value="<?=$item->sort_order?>">
                            <select name="active">
                                <option value="1" <?=$item->active ? 'selected' : ''?>>Active</option>
                                <option value="0" <?=!$item->active ? 'selected' : ''?>>Hidden</option>
                            </select>
                        </div>
                        <div class="hatchers-form">
                            <button class="hatchers-cta" type="submit">Save</button>
                            <a class="hatchers-ghost-btn" href="<?=base_url('hatchersadmin/nav_delete/'.$item->hatchers_nav_item_id)?>">Delete</a>
                        </div>
                    </form>
                <?php } ?>
            <?php } else { ?>
                <div class="hatchers-empty">
                    <div class="hatchers-empty-title">No items yet</div>
                    <div class="hatchers-empty-subtitle">Add your first navigation item above.</div>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
