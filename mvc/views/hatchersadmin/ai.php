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
            <p>Configure the OpenAI-powered assistant that supports founders using mentor context, tasks, meetings, and messages.</p>
        </div>
        <div class="hatchers-header-actions">
            <a class="hatchers-ghost-btn" href="<?=base_url('hatchersadmin/assignments')?>">Assignments</a>
            <a class="hatchers-ghost-btn" href="<?=base_url('hatchersadmin/nav')?>">Navigation</a>
            <a class="hatchers-ghost-btn" href="<?=base_url('hatchersadmin/profiles')?>">Profiles</a>
        </div>
    </div>

    <div class="hatchers-two-column">
    <div class="hatchers-detail-section">
        <div class="hatchers-page-intro">
            <div class="eyebrow">AI provider</div>
            <div class="title">OpenAI is the system default</div>
            <div class="copy">The API key is loaded from <code>OPENAI_API_KEY</code>. Prompts are stored here, but secrets stay on the server.</div>
        </div>

        <div class="hatchers-focus-card">
            <div class="eyebrow">Current model</div>
            <div class="title"><?=htmlspecialchars((string) $aiSettings->model)?></div>
            <div class="copy">Keep responses practical, responsive, and grounded in the founder’s actual mentoring context.</div>
        </div>

        <div class="hatchers-focus-card">
            <div class="eyebrow">Context sources</div>
            <div class="copy">Founder tasks, milestones, lessons, meetings, assignment data, AI chat history, and mentor-founder messages.</div>
        </div>
    </div>

    <div class="hatchers-detail-section">
        <form class="hatchers-form" method="post" action="<?=base_url('hatchersadmin/ai_save')?>">
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
</div>
