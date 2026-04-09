<?php
    if (!function_exists('hatchers_tool_href')) {
        function hatchers_tool_href($link)
        {
            $raw = trim((string) $link);
            if ($raw !== '' && preg_match('/^https?:\/\//i', $raw)) {
                return ['href' => $raw, 'meta' => 'External tool', 'target' => '_blank', 'rel' => 'noopener noreferrer'];
            }

            return ['href' => base_url($raw !== '' ? $raw : 'dashboard/index'), 'meta' => $raw !== '' ? $raw : 'Internal route', 'target' => null, 'rel' => null];
        }
    }
?>
<div class="hatchers-page">
    <div class="hatchers-page-header">
        <div>
            <h1>AI Tools</h1>
            <p>Quick access to Hatchers AI products, social automation tools, website builders, CRM links, and execution systems.</p>
        </div>
    </div>

    <div class="hatchers-tool-grid">
        <?php if (customCompute($tools)) { ?>
            <?php foreach ($tools as $tool) { ?>
                <?php $toolMeta = hatchers_tool_href(!empty($tool->link) ? $tool->link : ''); ?>
                <a class="hatchers-tool-panel" href="<?=htmlspecialchars($toolMeta['href'])?>" <?=$toolMeta['target'] ? 'target="'.$toolMeta['target'].'"' : ''?> <?=$toolMeta['rel'] ? 'rel="'.$toolMeta['rel'].'"' : ''?>>
                    <div class="icon"><i class="fa <?=!empty($tool->icon) ? $tool->icon : 'fa-link'?>"></i></div>
                    <div class="title"><?=htmlspecialchars($tool->label)?></div>
                    <div class="copy"><?=htmlspecialchars($toolMeta['meta'])?></div>
                </a>
            <?php } ?>
        <?php } else { ?>
            <div class="hatchers-empty-state">No tools configured yet.</div>
        <?php } ?>
    </div>
</div>
