<script type="text/javascript">
    document.body.classList.add('hatchers-mentor');
</script>

<?php
    $aiSettings = customCompute($aiSettings) ? $aiSettings : (object) [
        'system_prompt' => 'You are Hatchers AI, a friendly mentor for founders.',
        'guidelines' => 'Be concise, practical, and action-oriented.',
        'model' => 'gpt-4o-mini',
        'temperature' => 0.7,
        'max_tokens' => 600
    ];
?>

<div class="hatchers-dashboard hatchers-mentor-detail">
    <div class="hatchers-header">
        <div>
            <h1>AI Settings</h1>
            <p>Control the assistant behavior and the API connection.</p>
        </div>
        <div class="hatchers-header-actions">
            <a class="hatchers-ghost-btn" href="<?=base_url('hatchersadmin/assignments')?>">Assignments</a>
            <a class="hatchers-ghost-btn" href="<?=base_url('hatchersadmin/nav')?>">Navigation</a>
            <a class="hatchers-ghost-btn" href="<?=base_url('hatchersadmin/profiles')?>">Profiles</a>
        </div>
    </div>

    <div class="hatchers-detail-section">
        <form class="hatchers-form" method="post" action="<?=base_url('hatchersadmin/ai_save')?>">
            <div class="hatchers-form-title">OpenAI API Key</div>
            <div class="hatchers-list-subtitle">
                The API key is loaded from the server environment variable <code>OPENAI_API_KEY</code>.
                For security, it is not stored in the database.
            </div>

            <div class="hatchers-form-title">System Prompt</div>
            <textarea name="system_prompt" required><?=htmlspecialchars((string) $aiSettings->system_prompt)?></textarea>

            <div class="hatchers-form-title">Guidelines</div>
            <textarea name="guidelines"><?=htmlspecialchars((string) $aiSettings->guidelines)?></textarea>

            <div class="hatchers-form-title">Model</div>
            <input type="text" name="model" value="<?=htmlspecialchars((string) $aiSettings->model)?>" placeholder="gpt-4o-mini">

            <div class="hatchers-form-title">Temperature</div>
            <input type="number" step="0.1" name="temperature" value="<?=htmlspecialchars((string) $aiSettings->temperature)?>">

            <div class="hatchers-form-title">Max Tokens</div>
            <input type="number" name="max_tokens" value="<?=htmlspecialchars((string) $aiSettings->max_tokens)?>">

            <button class="hatchers-cta" type="submit">Save AI Settings</button>
        </form>
    </div>
</div>
