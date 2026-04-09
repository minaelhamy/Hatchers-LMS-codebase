<?php $this->load->view("components/page_header"); ?>
<?php
    $shell = isset($hatchers_shell) && is_array($hatchers_shell) ? $hatchers_shell : [];
    $navItems = isset($shell['nav_items']) ? $shell['nav_items'] : [];
    $aiTools = isset($shell['ai_tools']) ? $shell['ai_tools'] : [];
    $notifications = isset($shell['notifications']) ? $shell['notifications'] : [];
    $calendar = isset($shell['calendar']) ? $shell['calendar'] : [];
    $activeNav = isset($shell['active_nav']) ? $shell['active_nav'] : 'home';
    $messageBadge = isset($shell['message_badge']) ? (int) $shell['message_badge'] : 0;
    $userType = (int) $this->session->userdata('usertypeID');
    $roleLabel = $userType === 1 ? 'Super Admin' : ($userType === 2 ? 'Mentor' : 'Founder');
    if (!function_exists('hatchers_link_meta')) {
        function hatchers_link_meta($link)
        {
            $raw = trim((string) $link);
            if ($raw !== '' && preg_match('/^https?:\/\//i', $raw)) {
                return ['href' => $raw, 'target' => '_blank', 'rel' => 'noopener noreferrer'];
            }

            return ['href' => base_url($raw !== '' ? $raw : 'aitools/index'), 'target' => null, 'rel' => null];
        }
    }
?>

<div class="hatchers-app-frame">
    <div class="hatchers-shell">
        <aside class="hatchers-shell-sidebar">
            <a class="hatchers-shell-logo" href="<?=base_url('dashboard/index')?>">
                <span class="mark">HATCHERS</span><span class="accent">AI</span>
            </a>
            <div class="hatchers-shell-role"><?=$roleLabel?></div>

            <nav class="hatchers-shell-nav">
                <?php foreach ($navItems as $item) { ?>
                    <a class="hatchers-shell-link <?=$activeNav === $item['key'] ? 'is-active' : ''?>" href="<?=base_url($item['link'])?>">
                        <i class="fa <?=$item['icon']?>"></i>
                        <span><?=htmlspecialchars($item['label'])?></span>
                    </a>
                <?php } ?>
            </nav>

            <div class="hatchers-shell-user">
                <div class="hatchers-shell-avatar">
                    <?php if (!empty($this->session->userdata('photo'))) { ?>
                        <img src="<?=imagelink($this->session->userdata('photo'))?>" alt="">
                    <?php } else { ?>
                        <span><?=htmlspecialchars(strtoupper(substr((string) $this->session->userdata('name'), 0, 1)))?></span>
                    <?php } ?>
                </div>
                <div class="hatchers-shell-user-meta">
                    <div class="name"><?=htmlspecialchars((string) $this->session->userdata('name'))?></div>
                    <div class="role"><?=htmlspecialchars((string) $this->session->userdata('usertype'))?></div>
                </div>
                <a class="hatchers-shell-logout" href="<?=base_url('signin/signout')?>"><i class="fa fa-sign-out"></i></a>
            </div>
        </aside>

        <main class="hatchers-shell-main">
            <?php if ($this->session->flashdata('success')) { ?>
                <div class="hatchers-alert success"><?=htmlspecialchars((string) $this->session->flashdata('success'))?></div>
            <?php } ?>
            <?php if ($this->session->flashdata('error')) { ?>
                <div class="hatchers-alert error"><?=htmlspecialchars((string) $this->session->flashdata('error'))?></div>
            <?php } ?>
            <?php $this->load->view($subview); ?>
        </main>

        <aside class="hatchers-shell-right">
            <div class="hatchers-side-card">
                <div class="hatchers-side-head">
                    <span>Notifications</span>
                    <a class="hatchers-side-count" href="<?=base_url('mentoring/notifications')?>"><?=count($notifications)?></a>
                </div>
                <div class="hatchers-side-list">
                    <?php if (customCompute($notifications)) { ?>
                        <?php foreach ($notifications as $notification) { ?>
                            <a class="hatchers-side-item" href="<?=htmlspecialchars($notification['link'])?>">
                                <div class="title"><?=htmlspecialchars($notification['title'])?></div>
                                <div class="body"><?=htmlspecialchars($notification['body'])?></div>
                            </a>
                        <?php } ?>
                    <?php } else { ?>
                        <div class="hatchers-side-empty">No new notifications</div>
                    <?php } ?>
                </div>
            </div>

            <div class="hatchers-side-card">
                <div class="hatchers-side-head">
                    <span>Calendar</span>
                    <?php if ($messageBadge > 0) { ?>
                        <a class="hatchers-side-count" href="<?=base_url('mentoring/index')?>"><?=$messageBadge?></a>
                    <?php } ?>
                </div>
                <div id="hatchers-shell-calendar" class="hatchers-shell-calendar"></div>
            </div>

            <div class="hatchers-side-card">
                <div class="hatchers-side-head">
                    <span>AI Tools</span>
                </div>
                <div class="hatchers-tools-list">
                    <?php if (customCompute($aiTools)) { ?>
                        <?php foreach ($aiTools as $tool) { ?>
                            <?php $toolLink = hatchers_link_meta(!empty($tool->link) ? $tool->link : 'aitools/index'); ?>
                            <a class="hatchers-tool-card" href="<?=htmlspecialchars($toolLink['href'])?>" <?=$toolLink['target'] ? 'target="'.$toolLink['target'].'"' : ''?> <?=$toolLink['rel'] ? 'rel="'.$toolLink['rel'].'"' : ''?>>
                                <i class="fa <?=!empty($tool->icon) ? $tool->icon : 'fa-link'?>"></i>
                                <span><?=htmlspecialchars($tool->label)?></span>
                            </a>
                        <?php } ?>
                    <?php } else { ?>
                        <a class="hatchers-tool-card" href="<?=base_url('aitools/index')?>"><i class="fa fa-magic"></i><span>Open tools</span></a>
                    <?php } ?>
                </div>
            </div>
        </aside>
    </div>

    <script type="text/javascript">
        $(document).ready(function () {
            if ($('#hatchers-shell-calendar').length && $.fn.fullCalendar) {
                $('#hatchers-shell-calendar').fullCalendar({
                    header: {
                        left: 'prev',
                        center: 'title',
                        right: 'next'
                    },
                    defaultView: 'month',
                    height: 280,
                    fixedWeekCount: false,
                    eventLimit: true,
                    events: <?=json_encode($calendar)?>
                });
            }
        });
    </script>

<?php $this->load->view("components/page_footer"); ?>
