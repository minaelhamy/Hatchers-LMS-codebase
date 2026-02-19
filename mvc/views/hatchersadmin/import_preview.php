<script type="text/javascript">
    document.body.classList.add('hatchers-mentor');
</script>

<div class="hatchers-dashboard hatchers-mentor-detail">
    <div class="hatchers-header">
        <div>
            <h1>Import Preview</h1>
            <p>Review rows before importing.</p>
        </div>
        <div class="hatchers-header-actions">
            <a class="hatchers-ghost-btn" href="<?=base_url('hatchersadmin/profiles')?>">Back to Profiles</a>
        </div>
    </div>

    <div class="hatchers-detail-section">
        <div class="hatchers-list">
            <?php
                $rowsJson = json_encode($preview_rows);
                $hasErrors = false;
            ?>
            <?php if (customCompute($preview_rows)) { ?>
                <?php foreach ($preview_rows as $row) { ?>
                    <?php if (customCompute($row['errors'])) { $hasErrors = true; } ?>
                    <div class="hatchers-list-item">
                        <div>
                            <div class="hatchers-list-title"><?=htmlspecialchars($row['data']['name'])?></div>
                            <div class="hatchers-list-subtitle">
                                <?=htmlspecialchars($row['data']['username'])?>
                                <?php if (!empty($row['data']['email'])) { ?>
                                    • <?=htmlspecialchars($row['data']['email'])?>
                                <?php } ?>
                            </div>
                            <?php if (customCompute($row['errors'])) { ?>
                                <div class="hatchers-error">
                                    <?=htmlspecialchars(implode(' ', $row['errors']))?>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="hatchers-list-meta">
                            <?=customCompute($row['errors']) ? 'Invalid' : 'Ready'?>
                        </div>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <div class="hatchers-empty">
                    <div class="hatchers-empty-title">No rows to preview</div>
                </div>
            <?php } ?>
        </div>

        <form class="hatchers-form" method="post" action="<?=base_url('hatchersadmin/import_from_preview')?>">
            <input type="hidden" name="import_type" value="<?=htmlspecialchars($import_type)?>">
            <input type="hidden" name="rows_json" value='<?=htmlspecialchars($rowsJson, ENT_QUOTES)?>'>
            <?php if ($hasErrors) { ?>
                <div class="hatchers-error">Fix errors in your CSV before importing.</div>
                <button class="hatchers-cta" type="submit" disabled>Import</button>
            <?php } else { ?>
                <button class="hatchers-cta" type="submit">Confirm Import</button>
            <?php } ?>
        </form>
    </div>
</div>

