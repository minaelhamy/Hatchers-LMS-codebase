<?php echo doctype('html5'); ?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=htmlspecialchars(isset($page_title) ? $page_title : 'Hustler')?></title>
    <link rel="SHORTCUT ICON" href="<?= base_url("uploads/images/$siteinfos->photo") ?>" />
    <link rel="stylesheet" href="<?=base_url('assets/bootstrap/bootstrap.min.css')?>">
    <link rel="stylesheet" href="<?=base_url('frontend/default/assets/fonts/fontawesome/all.min.css')?>">
    <link rel="stylesheet" href="<?=base_url('assets/hatchers/hatchers.css')?>">
    <link rel="stylesheet" href="<?=base_url('assets/hatchers/hustler.css')?>">
</head>
<body class="hustler-shell">
    <script src="<?=base_url('assets/inilabs/jquery.min.js')?>"></script>
    <?php $this->load->view($subview); ?>
</body>
</html>
