<?php
    $competitors = customCompute($market_asset) ? json_decode((string) $market_asset->competitor_patterns_json, true) : [];
    $angles = customCompute($market_asset) ? json_decode((string) $market_asset->distribution_angles_json, true) : [];
    $posts = customCompute($market_asset) ? json_decode((string) $market_asset->social_posts_json, true) : [];
    if (!is_array($competitors)) $competitors = [];
    if (!is_array($angles)) $angles = [];
    if (!is_array($posts)) $posts = [];
?>
<div class="hustler-app single-page">
    <aside class="hustler-sidebar">
        <div class="hustler-sidebar-top">
            <div class="hustler-sidebar-brand">Hustler</div>
            <div class="hustler-sidebar-subtitle">Market access studio</div>
        </div>

        <nav class="hustler-sidebar-nav">
            <a href="<?=base_url('hustler/dashboard')?>">Weekly Plan</a>
            <a class="active" href="<?=base_url('hustler/market-access')?>">Market Access</a>
            <a href="<?=base_url('hustler/logout')?>">Log Out</a>
        </nav>

        <div class="hustler-sidebar-section">
            <div class="hustler-sidebar-label">Founder Snapshot</div>
            <div class="hustler-plan-card">
                <div class="hustler-plan-title"><?=htmlspecialchars($profile->founder_name !== '' ? $profile->founder_name : 'Founder pending')?></div>
                <div class="hustler-plan-copy"><?=htmlspecialchars($profile->idea_summary !== '' ? $profile->idea_summary : 'Capture the founder context from the weekly plan page first.')?></div>
            </div>
        </div>
    </aside>

    <main class="hustler-main">
        <section class="hustler-hero">
            <div>
                <div class="hustler-eyebrow">Market Access Tool</div>
                <h1>Research + content starter pack</h1>
                <p>Generate a market read, inferred competitor patterns, distribution angles, and social starter posts from the founder context already captured.</p>
            </div>
        </section>

        <section class="hustler-chat-panel">
            <div class="hustler-panel-head">
                <div>
                    <div class="hustler-panel-title">Generate Market Access Assets</div>
                    <div class="hustler-panel-copy">Optional: add a focus prompt like "B2B cold outreach" or "retail moms in Dubai".</div>
                </div>
            </div>

            <div class="hustler-chat-form compact">
                <textarea id="hustler-market-focus" placeholder="Optional focus: geography, ICP, channel, or competitor angle"></textarea>
                <button id="hustler-market-generate" class="hustlers-primary-btn" type="button">Generate</button>
            </div>

            <div class="hustler-market-grid">
                <div class="hustler-right-card tall">
                    <div class="hustler-right-title">Market Overview</div>
                    <div id="hustler-market-overview"><?=htmlspecialchars(customCompute($market_asset) ? (string) $market_asset->market_overview : 'No report generated yet.')?></div>
                </div>

                <div class="hustler-right-card tall">
                    <div class="hustler-right-title">Ideal Customer Profile</div>
                    <div id="hustler-icp"><?=htmlspecialchars(customCompute($market_asset) ? (string) $market_asset->ideal_customer_profile : 'No ICP generated yet.')?></div>
                </div>
            </div>
        </section>
    </main>

    <aside class="hustler-right">
        <section class="hustler-right-card">
            <div class="hustler-right-title">Competitor Patterns</div>
            <div class="hustler-plain-list" id="hustler-competitor-list">
                <?php if (customCompute($competitors)) { ?>
                    <?php foreach ($competitors as $item) { ?>
                        <div><?=htmlspecialchars((string) $item)?></div>
                    <?php } ?>
                <?php } else { ?>
                    <div>No competitor signal set yet.</div>
                <?php } ?>
            </div>
        </section>

        <section class="hustler-right-card">
            <div class="hustler-right-title">Distribution Angles</div>
            <div class="hustler-plain-list" id="hustler-angle-list">
                <?php if (customCompute($angles)) { ?>
                    <?php foreach ($angles as $item) { ?>
                        <div><?=htmlspecialchars((string) $item)?></div>
                    <?php } ?>
                <?php } else { ?>
                    <div>No distribution angles generated yet.</div>
                <?php } ?>
            </div>
        </section>

        <section class="hustler-right-card">
            <div class="hustler-right-title">Social Posts</div>
            <div class="hustler-plain-list social" id="hustler-post-list">
                <?php if (customCompute($posts)) { ?>
                    <?php foreach ($posts as $post) { ?>
                        <div><?=htmlspecialchars((string) $post)?></div>
                    <?php } ?>
                <?php } else { ?>
                    <div>No social content generated yet.</div>
                <?php } ?>
            </div>
        </section>
    </aside>
</div>

<script type="text/javascript">
    (function() {
        function escapeHtml(text) {
            return $('<div>').text(text || '').html();
        }

        function renderList(selector, values, emptyCopy) {
            values = values || [];
            $(selector).html((values.length ? values : [emptyCopy]).map(function(item) {
                return '<div>' + escapeHtml(item) + '</div>';
            }).join(''));
        }

        $('#hustler-market-generate').on('click', function() {
            var focus = ($('#hustler-market-focus').val() || '').trim();
            var $button = $(this);
            $button.prop('disabled', true).text('Generating...');

            $.post('<?=base_url('hustler/generate-market-access')?>', { focus: focus }, function(res) {
                if (!(res && res.ok && res.market_asset)) {
                    alert((res && res.error) ? res.error : 'Could not generate market access assets.');
                    return;
                }

                $('#hustler-market-overview').text(res.market_asset.market_overview || 'No report generated.');
                $('#hustler-icp').text(res.market_asset.ideal_customer_profile || 'No ICP generated.');
                renderList('#hustler-competitor-list', res.market_asset.competitor_patterns || [], 'No competitor signal set yet.');
                renderList('#hustler-angle-list', res.market_asset.distribution_angles || [], 'No distribution angles generated yet.');
                renderList('#hustler-post-list', res.market_asset.social_posts || [], 'No social content generated yet.');
            }, 'json').fail(function(xhr) {
                var serverMessage = 'Request failed. Please try again.';
                if (xhr && xhr.responseJSON && xhr.responseJSON.error) {
                    serverMessage = xhr.responseJSON.error;
                } else if (xhr && xhr.responseText) {
                    serverMessage = xhr.responseText.substring(0, 220);
                }
                alert(serverMessage);
            }).always(function() {
                $button.prop('disabled', false).text('Generate');
            });
        });
    })();
</script>
