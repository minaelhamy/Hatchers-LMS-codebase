<div class="hustler-login-shell">
    <div class="hustler-login-card">
        <div class="hustler-login-brand">Hustler by Hatchers</div>
        <h1>Investor MVP</h1>
        <p>Review the founder-diagnostic engine, weekly plan generation, and market access layer from one workspace.</p>

        <?php if (!empty($form_error)) { ?>
            <div class="hustler-login-error"><?=htmlspecialchars($form_error)?></div>
        <?php } ?>

        <form method="post" action="<?=base_url('hustler/login')?>" class="hustler-login-form">
            <label for="username">Username</label>
            <input id="username" type="text" name="username" autocomplete="username" required>

            <label for="password">Password</label>
            <input id="password" type="password" name="password" autocomplete="current-password" required>

            <button type="submit" class="hustlers-primary-btn">Enter Workspace</button>
        </form>
    </div>
</div>
