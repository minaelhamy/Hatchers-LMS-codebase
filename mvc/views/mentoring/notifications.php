<div class="hatchers-page">
    <div class="hatchers-page-header">
        <div>
            <h1>Notifications</h1>
            <p>Recent platform updates and reminders.</p>
        </div>
    </div>

    <section class="hatchers-card-panel">
        <?php if (customCompute($notifications)) { ?>
            <?php foreach ($notifications as $notification) { ?>
                <a class="hatchers-line-card block-link" href="<?=htmlspecialchars($notification['link'])?>">
                    <div>
                        <div class="hatchers-line-title"><?=htmlspecialchars($notification['title'])?></div>
                        <div class="hatchers-line-copy"><?=htmlspecialchars($notification['body'])?></div>
                    </div>
                </a>
            <?php } ?>
        <?php } else { ?>
            <div class="hatchers-empty-state">No notifications right now.</div>
        <?php } ?>
    </section>
</div>
