<?php
    $competitors = customCompute($market_asset) ? json_decode((string) $market_asset->competitor_patterns_json, true) : [];
    $angles = customCompute($market_asset) ? json_decode((string) $market_asset->distribution_angles_json, true) : [];
    $posts = customCompute($market_asset) ? json_decode((string) $market_asset->social_posts_json, true) : [];
    $postImages = customCompute($market_asset) ? json_decode((string) $market_asset->post_images_json, true) : [];
    $instagramProfile = customCompute($market_asset) ? json_decode((string) $market_asset->instagram_profile_json, true) : [];
    $funnels = customCompute($market_asset) ? json_decode((string) $market_asset->funnel_suggestions_json, true) : [];

    if (!is_array($competitors)) $competitors = [];
    if (!is_array($angles)) $angles = [];
    if (!is_array($posts)) $posts = [];
    if (!is_array($postImages)) $postImages = [];
    if (!is_array($instagramProfile)) $instagramProfile = [];
    if (!is_array($funnels)) $funnels = [];

    $igUsername = isset($instagramProfile['username']) ? (string) $instagramProfile['username'] : 'founderprofile';
    $igDisplayName = isset($instagramProfile['display_name']) ? (string) $instagramProfile['display_name'] : ($profile->company_name !== '' ? $profile->company_name : 'Founder Company');
    $igBio = isset($instagramProfile['bio']) ? (string) $instagramProfile['bio'] : ($profile->idea_summary !== '' ? $profile->idea_summary : 'Business profile pending');
    $igWebsite = isset($instagramProfile['website']) ? (string) $instagramProfile['website'] : 'https://hatchers.ai';
    $igFollowers = isset($instagramProfile['followers']) ? (int) $instagramProfile['followers'] : 0;
    $igFollowing = isset($instagramProfile['following']) ? (int) $instagramProfile['following'] : 0;
    $igLogo = isset($instagramProfile['logo_url']) ? trim((string) $instagramProfile['logo_url']) : '';
    if ($igLogo === '') {
        $igLogo = isset($profile->company_logo_url) ? trim((string) $profile->company_logo_url) : '';
    }
?>
<div class="hustler-app single-page market-redesign">
    <aside class="hustler-sidebar">
        <div class="hustler-sidebar-top">
            <div class="hustler-sidebar-brand">Hustler</div>
            <div class="hustler-sidebar-subtitle">Market access studio</div>
        </div>

        <nav class="hustler-sidebar-nav">
            <a href="<?=base_url('hustler/dashboard')?>">Weekly Plan</a>
            <a class="active" href="<?=base_url('hustler/market-access')?>">Market Access</a>
            <a href="<?=base_url('hustler/logout')?>">Log Out</a>
            <form method="post" action="<?=base_url('hustler/restart-profile')?>" onsubmit="return confirm('Restart profile and erase all generated data?');">
                <button class="hustler-nav-danger" type="submit">Restart Profile</button>
            </form>
        </nav>

        <div class="hustler-sidebar-section">
            <div class="hustler-sidebar-label">Founder Snapshot</div>
            <div class="hustler-plan-card">
                <div class="hustler-plan-title"><?=htmlspecialchars($profile->founder_name !== '' ? $profile->founder_name : 'Founder pending')?></div>
                <div class="hustler-plan-copy"><?=htmlspecialchars($profile->idea_summary !== '' ? $profile->idea_summary : 'Capture more context on the Weekly Plan page first.')?></div>
            </div>
        </div>
    </aside>

    <main class="hustler-main">
        <section class="hustler-chat-panel">
            <div class="hustler-panel-head">
                <div class="hustler-eyebrow">Market Access Demo</div>
                <div class="hustler-panel-title">Instagram + Funnel Assets</div>
                <div class="hustler-panel-copy">Generated from founder context, competitor patterns, and Sell Like Crazy style funnel logic.</div>
            </div>

            <div class="hustler-chat-form compact">
                <textarea id="hustler-market-focus" placeholder="Optional focus: market, channel, niche, Instagram angle, or specific campaign objective"></textarea>
            </div>

            <div class="hustler-generation-status" id="hustler-generation-status" style="display:none;">
                <div class="hustler-generation-row">
                    <span class="hustler-spinner"></span>
                    <span id="hustler-generation-label">Generating Instagram profile, 6 post visuals, and 3 funnels...</span>
                </div>
                <div class="hustler-progress-track">
                    <div class="hustler-progress-fill" id="hustler-progress-fill"></div>
                </div>
            </div>

            <div class="hustler-market-grid">
                <div class="hustler-right-card tall">
                    <div class="hustler-right-title">Market Overview</div>
                    <div id="hustler-market-overview"><?=htmlspecialchars(customCompute($market_asset) ? (string) $market_asset->market_overview : 'Generating overview...')?></div>
                </div>

                <div class="hustler-right-card tall">
                    <div class="hustler-right-title">Ideal Customer Profile</div>
                    <div id="hustler-icp"><?=htmlspecialchars(customCompute($market_asset) ? (string) $market_asset->ideal_customer_profile : 'Generating ICP...')?></div>
                </div>
            </div>

            <div class="hustler-instagram-shell">
                <div class="hustler-instagram-top">
                    <div class="hustler-instagram-user">@<span id="ig-username"><?=htmlspecialchars($igUsername)?></span></div>
                </div>
                <div class="hustler-instagram-profile">
                    <div class="hustler-instagram-avatar">
                        <?php if ($igLogo !== '') { ?>
                            <img id="ig-avatar-img" src="<?=htmlspecialchars($igLogo)?>" alt="">
                        <?php } elseif (customCompute($postImages)) { ?>
                            <img id="ig-avatar-img" src="<?=htmlspecialchars((string) $postImages[0]['image_url'])?>" alt="">
                        <?php } else { ?>
                            <span id="ig-avatar-fallback"><?=htmlspecialchars(substr($igDisplayName, 0, 2))?></span>
                        <?php } ?>
                    </div>
                    <div class="hustler-instagram-stats">
                        <div><strong id="ig-post-count"><?=customCompute($postImages) ? customCompute($postImages) : 6?></strong><span>Posts</span></div>
                        <div><strong id="ig-followers"><?=htmlspecialchars((string) $igFollowers)?></strong><span>Followers</span></div>
                        <div><strong id="ig-following"><?=htmlspecialchars((string) $igFollowing)?></strong><span>Following</span></div>
                    </div>
                </div>
                <div class="hustler-instagram-bio">
                    <strong id="ig-display-name"><?=htmlspecialchars($igDisplayName)?></strong>
                    <div id="ig-bio"><?=htmlspecialchars($igBio)?></div>
                    <a id="ig-website" href="<?=htmlspecialchars($igWebsite)?>" target="_blank"><?=htmlspecialchars($igWebsite)?></a>
                </div>
                <div class="hustler-image-grid instagram-grid" id="hustler-image-grid">
                    <?php if (customCompute($postImages)) { ?>
                        <?php foreach ($postImages as $imageItem) { ?>
                            <div class="hustler-image-card">
                                <img src="<?=htmlspecialchars(isset($imageItem['image_url']) ? (string) $imageItem['image_url'] : '')?>" alt="">
                            </div>
                        <?php } ?>
                    <?php } else { ?>
                        <div class="hustler-empty-image-grid">Instagram post previews will be generated automatically.</div>
                    <?php } ?>
                </div>
            </div>

            <div class="hustler-funnels-wrap" id="hustler-funnels-wrap">
                <?php if (customCompute($funnels)) { ?>
                    <?php foreach ($funnels as $funnel) { ?>
                        <div class="hustler-funnel-card style-<?=htmlspecialchars(isset($funnel['design_type']) ? (string) $funnel['design_type'] : 'funnel')?>">
                            <div class="hustler-funnel-title"><?=htmlspecialchars(isset($funnel['title']) ? (string) $funnel['title'] : 'Funnel')?></div>
                            <div class="hustler-funnel-subtitle"><?=htmlspecialchars(isset($funnel['subtitle']) ? (string) $funnel['subtitle'] : '')?></div>
                            <div class="hustler-funnel-steps">
                                <?php if (isset($funnel['steps']) && is_array($funnel['steps'])) { ?>
                                    <?php foreach ($funnel['steps'] as $step) { ?>
                                        <div><?=htmlspecialchars((string) $step)?></div>
                                    <?php } ?>
                                <?php } ?>
                            </div>
                            <div class="hustler-funnel-cta"><?=htmlspecialchars(isset($funnel['cta']) ? (string) $funnel['cta'] : '')?></div>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <div class="hustler-empty-image-grid">Three funnel suggestions will appear after generation.</div>
                <?php } ?>
            </div>

            <button id="hustler-market-generate" class="hustlers-primary-btn" type="button">Regenerate</button>

            <div class="hustler-demo-disclaimer">
                this is a demo account, and so downloading asset or automatic posting to social media is disabled, please contct admin
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
                    <div>Generating competitor signal...</div>
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
                    <div>Generating distribution suggestions...</div>
                <?php } ?>
            </div>
        </section>

        <section class="hustler-right-card">
            <div class="hustler-right-title">Social Captions</div>
            <div class="hustler-plain-list social" id="hustler-post-list">
                <?php if (customCompute($posts)) { ?>
                    <?php foreach ($posts as $post) { ?>
                        <div><?=htmlspecialchars((string) $post)?></div>
                    <?php } ?>
                <?php } else { ?>
                    <div>Generating social captions...</div>
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

        function renderImageGrid(images, logoUrl) {
            images = images || [];
            var html = '';
            if (!images.length) {
                html = '<div class="hustler-empty-image-grid">Instagram post previews will be generated automatically.</div>';
            } else {
                html = images.slice(0, 6).map(function(item) {
                    var url = item && item.image_url ? item.image_url : '';
                    return '<div class="hustler-image-card"><img src="' + escapeHtml(url) + '" alt=""></div>';
                }).join('');
            }
            $('#hustler-image-grid').html(html);
            $('#ig-post-count').text(images.length || 6);
            if (logoUrl) {
                $('.hustler-instagram-avatar').html('<img id="ig-avatar-img" src="' + escapeHtml(logoUrl) + '" alt="">');
            } else if (images.length && images[0].image_url) {
                $('.hustler-instagram-avatar').html('<img id="ig-avatar-img" src="' + escapeHtml(images[0].image_url) + '" alt="">');
            }
        }

        function renderFunnels(funnels) {
            funnels = funnels || [];
            if (!funnels.length) {
                $('#hustler-funnels-wrap').html('<div class="hustler-empty-image-grid">Three funnel suggestions will appear after generation.</div>');
                return;
            }

            var html = funnels.slice(0, 3).map(function(funnel) {
                var steps = Array.isArray(funnel.steps) ? funnel.steps.slice(0, 4) : [];
                var stepsHtml = steps.map(function(step) {
                    return '<div>' + escapeHtml(step) + '</div>';
                }).join('');
                return '<div class="hustler-funnel-card style-' + escapeHtml(funnel.design_type || 'funnel') + '">' +
                    '<div class="hustler-funnel-title">' + escapeHtml(funnel.title || 'Funnel') + '</div>' +
                    '<div class="hustler-funnel-subtitle">' + escapeHtml(funnel.subtitle || '') + '</div>' +
                    '<div class="hustler-funnel-steps">' + stepsHtml + '</div>' +
                    '<div class="hustler-funnel-cta">' + escapeHtml(funnel.cta || '') + '</div>' +
                '</div>';
            }).join('');
            $('#hustler-funnels-wrap').html(html);
        }

        function renderInstagramProfile(profile) {
            profile = profile || {};
            $('#ig-username').text(profile.username || 'founderprofile');
            $('#ig-display-name').text(profile.display_name || 'Founder Company');
            $('#ig-bio').text(profile.bio || 'Business profile pending');
            $('#ig-website').text(profile.website || 'https://hatchers.ai');
            $('#ig-website').attr('href', profile.website || 'https://hatchers.ai');
            $('#ig-followers').text(profile.followers || 0);
            $('#ig-following').text(profile.following || 0);
        }

        function runGenerate(autoMode) {
            var focus = ($('#hustler-market-focus').val() || '').trim();
            var $button = $('#hustler-market-generate');
            $button.prop('disabled', true).text(autoMode ? 'Generating...' : 'Regenerating...');
            $('#hustler-generation-status').show();
            $('#hustler-progress-fill').css('width', '8%');

            var progressValue = 8;
            var progressTimer = setInterval(function() {
                progressValue += Math.random() * 7;
                if (progressValue > 90) progressValue = 90;
                $('#hustler-progress-fill').css('width', progressValue + '%');
            }, 700);

            $.post('<?=base_url('hustler/generate-market-access')?>', { focus: focus }, function(res) {
                if (!(res && res.ok && res.market_asset)) {
                    alert((res && res.error) ? res.error : 'Could not generate market access assets.');
                    return;
                }

                $('#hustler-market-overview').text(res.market_asset.market_overview || 'No report generated.');
                $('#hustler-icp').text(res.market_asset.ideal_customer_profile || 'No ICP generated.');
                renderInstagramProfile(res.market_asset.instagram_profile || {});
                renderList('#hustler-competitor-list', res.market_asset.competitor_patterns || [], 'No competitor signal set yet.');
                renderList('#hustler-angle-list', res.market_asset.distribution_angles || [], 'No distribution angles generated yet.');
                renderList('#hustler-post-list', res.market_asset.social_posts || [], 'No social content generated yet.');
                var logoUrl = res.market_asset.instagram_profile && res.market_asset.instagram_profile.logo_url
                    ? res.market_asset.instagram_profile.logo_url
                    : '';
                renderImageGrid(res.market_asset.post_images || [], logoUrl);
                renderFunnels(res.market_asset.funnel_suggestions || []);
            }, 'json').fail(function(xhr) {
                var serverMessage = 'Request failed. Please try again.';
                if (xhr && xhr.responseJSON && xhr.responseJSON.error) {
                    serverMessage = xhr.responseJSON.error;
                } else if (xhr && xhr.responseText) {
                    serverMessage = xhr.responseText.substring(0, 220);
                }
                alert(serverMessage);
            }).always(function() {
                clearInterval(progressTimer);
                $('#hustler-progress-fill').css('width', '100%');
                setTimeout(function() {
                    $('#hustler-generation-status').hide();
                    $('#hustler-progress-fill').css('width', '0%');
                }, 450);
                $button.prop('disabled', false).text('Regenerate');
            });
        }

        $('#hustler-market-generate').on('click', function() {
            runGenerate(false);
        });

        var hasImages = <?=customCompute($postImages) ? 'true' : 'false'?>;
        if (!hasImages) {
            runGenerate(true);
        }
    })();
</script>
