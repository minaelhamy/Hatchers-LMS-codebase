<?php
    $role = isset($hatchers_home['role']) ? $hatchers_home['role'] : 'founder';
    $headline = isset($hatchers_home['headline']) ? $hatchers_home['headline'] : 'Welcome back';
    $subheadline = isset($hatchers_home['subheadline']) ? $hatchers_home['subheadline'] : 'Here is what is moving right now.';
    $stats = isset($hatchers_home['stats']) ? $hatchers_home['stats'] : [];
    $cards = isset($hatchers_home['cards']) ? $hatchers_home['cards'] : [];
    $spotlight = isset($hatchers_home['spotlight']) ? $hatchers_home['spotlight'] : [];
?>

<div class="hatchers-page">
    <div class="hatchers-page-header">
        <div>
            <h1><?=htmlspecialchars($headline)?></h1>
            <p><?=htmlspecialchars($subheadline)?></p>
        </div>
    </div>

    <?php if (customCompute($stats)) { ?>
        <div class="hatchers-stats-grid">
            <?php foreach ($stats as $stat) { ?>
                <div class="hatchers-stat-card">
                    <div class="eyebrow"><?=htmlspecialchars($stat['label'])?></div>
                    <div class="value"><?=htmlspecialchars((string) $stat['value'])?></div>
                    <div class="copy"><?=htmlspecialchars($stat['copy'])?></div>
                </div>
            <?php } ?>
        </div>
    <?php } ?>

    <div class="hatchers-two-column">
        <section class="hatchers-card-panel">
            <div class="hatchers-panel-title"><?=($role === 'admin') ? 'Founder Overview' : 'This Week'?></div>
            <?php if (customCompute($cards)) { ?>
                <?php foreach ($cards as $card) { ?>
                    <div class="hatchers-line-card">
                        <div>
                            <div class="hatchers-line-title"><?=htmlspecialchars($card['title'])?></div>
                            <div class="hatchers-line-copy"><?=htmlspecialchars($card['copy'])?></div>
                        </div>
                        <div class="hatchers-line-meta">
                            <?php if (!empty($card['link'])) { ?>
                                <a href="<?=base_url($card['link'])?>"><?=htmlspecialchars($card['action'])?></a>
                            <?php } else { ?>
                                <?=htmlspecialchars($card['action'])?>
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <div class="hatchers-empty-state small">Nothing to show yet.</div>
            <?php } ?>
        </section>

        <section class="hatchers-card-panel">
            <div class="hatchers-panel-title"><?=($role === 'founder') ? 'Quick Access' : 'Operational Focus'?></div>
            <?php if (customCompute($spotlight)) { ?>
                <?php foreach ($spotlight as $item) { ?>
                    <div class="hatchers-focus-card">
                        <div class="eyebrow"><?=htmlspecialchars($item['label'])?></div>
                        <div class="title"><?=htmlspecialchars($item['title'])?></div>
                        <div class="copy"><?=htmlspecialchars($item['copy'])?></div>
                        <?php if (!empty($item['link'])) { ?>
                            <a class="hatchers-inline-btn" href="<?=base_url($item['link'])?>"><?=htmlspecialchars($item['action'])?></a>
                        <?php } ?>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <div class="hatchers-empty-state small">No action items yet.</div>
            <?php } ?>
        </section>
    </div>
</div>
