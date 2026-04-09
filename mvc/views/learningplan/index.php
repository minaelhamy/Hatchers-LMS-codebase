<?php $founderName = customCompute($founder) ? $founder->name : 'Founder'; ?>

<div class="hatchers-page">
    <div class="hatchers-page-header">
        <div>
            <h1>Learning Plan</h1>
            <p>Structured lessons and shared resources for <?=htmlspecialchars($founderName)?>.</p>
        </div>
    </div>

    <div class="hatchers-page-intro">
        <div class="eyebrow">Learning library</div>
        <div class="title">Mentor-curated knowledge, not school syllabus</div>
        <div class="copy">Use this space for PDFs, YouTube links, articles, websites, and session-based lessons that directly help the founder launch, solve blockers, and scale.</div>
    </div>

    <div class="hatchers-two-column">
        <section class="hatchers-card-panel">
            <div class="hatchers-panel-title">Learning Sessions</div>
            <?php if (customCompute($lessons)) { ?>
                <?php foreach ($lessons as $lesson) { ?>
                    <div class="hatchers-line-card">
                        <div>
                            <div class="hatchers-line-title"><?=htmlspecialchars($lesson->title)?></div>
                            <div class="hatchers-line-copy"><?=htmlspecialchars((string) $lesson->subtitle)?></div>
                            <?php if (!empty($lesson->description)) { ?>
                                <div class="hatchers-line-copy muted"><?=htmlspecialchars((string) $lesson->description)?></div>
                            <?php } ?>
                        </div>
                        <div class="hatchers-line-meta">
                            <?=!empty($lesson->starts_at) ? date('M j, Y g:ia', strtotime($lesson->starts_at)) : 'Scheduled soon'?>
                        </div>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <div class="hatchers-empty-state small">No learning sessions yet.</div>
            <?php } ?>

            <?php if ((int) $this->session->userdata('usertypeID') !== 3 && customCompute($founder)) { ?>
                <form class="hatchers-stacked-form" method="post" action="<?=base_url('learningplan/add_lesson')?>">
                    <input type="hidden" name="founder_id" value="<?=$founder->studentID?>">
                    <input type="text" name="title" placeholder="Lesson title" required>
                    <input type="text" name="subtitle" placeholder="Subtitle">
                    <textarea name="description" placeholder="Description"></textarea>
                    <input type="url" name="resource_url" placeholder="Resource link (optional)">
                    <select name="resource_type">
                        <option value="session">Session</option>
                        <option value="youtube">YouTube</option>
                        <option value="article">Article</option>
                        <option value="website">Website</option>
                    </select>
                    <input type="datetime-local" name="starts_at">
                    <button class="hatchers-inline-btn" type="submit">Add lesson</button>
                </form>
            <?php } ?>
        </section>

        <section class="hatchers-card-panel">
            <div class="hatchers-panel-title">Learning Library</div>
            <?php if (customCompute($resources)) { ?>
                <?php foreach ($resources as $resource) { ?>
                    <div class="hatchers-line-card">
                        <div>
                            <div class="hatchers-line-title"><?=htmlspecialchars($resource->title)?></div>
                            <div class="hatchers-line-copy"><?=htmlspecialchars((string) $resource->description)?></div>
                        </div>
                        <div class="hatchers-line-meta">
                            <?php if (!empty($resource->file_path)) { ?>
                                <a href="<?=base_url($resource->file_path)?>" target="_blank">Open PDF</a>
                            <?php } elseif (!empty($resource->resource_url)) { ?>
                                <a href="<?=htmlspecialchars($resource->resource_url)?>" target="_blank">Open link</a>
                            <?php } else { ?>
                                <?=htmlspecialchars($resource->resource_type)?>
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <div class="hatchers-empty-state small">No resources uploaded yet.</div>
            <?php } ?>

            <?php if ((int) $this->session->userdata('usertypeID') !== 3) { ?>
                <form class="hatchers-stacked-form" method="post" enctype="multipart/form-data" action="<?=base_url('learningplan/add_resource')?>">
                    <input type="hidden" name="founder_id" value="<?=customCompute($founder) ? $founder->studentID : 0?>">
                    <input type="text" name="title" placeholder="Resource title" required>
                    <textarea name="description" placeholder="Description"></textarea>
                    <select name="resource_type">
                        <option value="link">Website link</option>
                        <option value="article">Article</option>
                        <option value="youtube">YouTube</option>
                        <option value="pdf">PDF upload</option>
                    </select>
                    <input type="url" name="resource_url" placeholder="https://...">
                    <input type="file" name="resource_file" accept=".pdf">
                    <button class="hatchers-inline-btn" type="submit">Add resource</button>
                </form>
            <?php } ?>
        </section>
    </div>
</div>
