<div class="wrapper row-offcanvas row-offcanvas-left">
        <!-- Left side column. contains the logo and sidebar -->
        <aside class="left-side sidebar-offcanvas">
            <!-- sidebar: style can be found in sidebar.less -->
            <section class="sidebar">
                <!-- Sidebar user panel -->
                <div class="user-panel">
                    <div class="pull-left image">
                        <img style="display:block" src="<?=imagelink($this->session->userdata('photo'))?>" class="img-circle" alt="" />
                    </div>

                    <div class="pull-left info">
                        <?php
                            $name = $this->session->userdata("name");
                            if(strlen((string) $name) > 18) {
                               $name = substr((string) $name, 0,18);
                            }
                            echo "<p>".$name."</p>";
                        ?>
                        <a href="<?=base_url("profile/index")?>">
                            <i class="fa fa-hand-o-right color-green"></i>
                            <?=$this->session->userdata("usertype")?>
                        </a>
                    </div>
                </div>
                
                <ul class="sidebar-menu">
                    <?php
                        if ($this->session->userdata('usertypeID') == 3 && customCompute($hatchers_nav_left)) {
                            foreach ($hatchers_nav_left as $navItem) {
                                $link = !empty($navItem->link) ? $navItem->link : '#';
                                echo '<li>';
                                echo anchor(
                                    $link,
                                    '<i class="fa ' . (!empty($navItem->icon) ? $navItem->icon : 'fa-circle') . '"></i><span>' . $navItem->label . '</span>'
                                );
                                echo '</li>';
                            }
                        } else {
                            if(customCompute($dbMenus)) {
                                $menuDesign = '';
                                display_menu($dbMenus, $menuDesign);
                                echo $menuDesign;
                            }
                        }
                    ?>
                </ul>
            </section>
            <!-- /.sidebar -->
        </aside>
