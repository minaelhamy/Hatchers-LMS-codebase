        </div>
        <script type="text/javascript" src="<?php echo base_url('assets/bootstrap/bootstrap.min.js'); ?>"></script>
        <script type="text/javascript" src="<?php echo base_url('assets/inilabs/style.js'); ?>"></script>
        <script type="text/javascript" src="<?php echo base_url('assets/datatables/tools/jquery.dataTables.min.js'); ?>"></script>
        <script type="text/javascript" src="<?php echo base_url('assets/datatables/tools/dataTables.buttons.min.js'); ?>"></script>
        <script type="text/javascript" src="<?php echo base_url('assets/datatables/tools/jszip.min.js'); ?>"></script>
        <script type="text/javascript" src="<?php echo base_url('assets/datatables/tools/pdfmake.min.js'); ?>"></script>
        <script type="text/javascript" src="<?php echo base_url('assets/datatables/tools/vfs_fonts.js'); ?>"></script>
        <script type="text/javascript" src="<?php echo base_url('assets/datatables/tools/buttons.html5.min.js'); ?>"></script>
        <script type="text/javascript" src="<?php echo base_url('assets/datatables/dataTables.bootstrap.js'); ?>"></script>
        <script type="text/javascript" src="<?php echo base_url('assets/inilabs/inilabs.js'); ?>"></script>
        <script type="text/javascript">
          $(document).ready(function () {
            $(document).ajaxStart(function () {
              $("#loading").show();
            }).ajaxStop(function () {
              $("#loading").hide();
            });
          });

          $(document).ready(function () {
            $('#example3, #example1, #example2').DataTable({
              dom : 'Bfrtip',
              buttons : [
                'copyHtml5',
                'excelHtml5',
                'csvHtml5',
                'pdfHtml5'
              ],
              search : false
            });
          });
        </script>

        <script type="text/javascript">
          $(function () {
            $("#withoutBtn").dataTable();
          });
        </script>

        <?php if ($this->session->flashdata('success')): ?>
            <script type="text/javascript">
              toastr[ "success" ]("<?=$this->session->flashdata('success');?>");
              toastr.options = {
                "closeButton" : true,
                "debug" : false,
                "newestOnTop" : false,
                "progressBar" : false,
                "positionClass" : "toast-top-right",
                "preventDuplicates" : false,
                "onclick" : null,
                "showDuration" : "500",
                "hideDuration" : "500",
                "timeOut" : "5000",
                "extendedTimeOut" : "1000",
                "showEasing" : "swing",
                "hideEasing" : "linear",
                "showMethod" : "fadeIn",
                "hideMethod" : "fadeOut"
              }
            </script>
            <?php $this->session->unset_userdata('success'); 
            ?>
        <?php endif ?>
        <?php if ($this->session->flashdata('error')): ?>
            <script type="text/javascript">
              toastr[ "error" ]("<?=$this->session->flashdata('error');?>");
              toastr.options = {
                "closeButton" : true,
                "debug" : false,
                "newestOnTop" : false,
                "progressBar" : false,
                "positionClass" : "toast-top-right",
                "preventDuplicates" : false,
                "onclick" : null,
                "showDuration" : "500",
                "hideDuration" : "500",
                "timeOut" : "5000",
                "extendedTimeOut" : "1000",
                "showEasing" : "swing",
                "hideEasing" : "linear",
                "showMethod" : "fadeIn",
                "hideMethod" : "fadeOut"
              }
            </script>
            <?php $this->session->unset_userdata('error'); 
            ?>
        <?php endif ?>

        <?php
            if ( isset($footerassets) ) {
                foreach ( $footerassets as $assetstype => $footerasset ) {
                    if ( $assetstype == 'css' ) {
                        if ( customCompute($footerasset) ) {
                            foreach ( $footerasset as $keycss => $css ) {
                                echo '<link rel="stylesheet" href="' . base_url($css) . '">' . "\n";
                            }
                        }
                    } elseif ( $assetstype == 'js' ) {
                        if ( customCompute($footerasset) ) {
                            foreach ( $footerasset as $keyjs => $js ) {
                                echo '<script type="text/javascript" src="' . base_url($js) . '"></script>' . "\n";
                            }
                        }
                    }
                }
            }
        ?>
        
        <script type="text/javascript">
            $("ul.sidebar-menu li").each(function() {
                if($(this).attr('class') === 'active') {
                    $(this).parents('li').addClass('active');
                }
            });

            $(document).ready(function () {
              var lastAlertCount = 0;
              setTimeout(function () {
                $.ajax({
                  type : 'GET',
                  dataType : "html",
                  async : false,
                  url : "<?=base_url('alert/alert')?>",
                  success : function (data) {
                    $(".my-push-message-list").html(data);
                    var alertNumber = 0;
                    $('.my-push-message-list li').each(function () {
                      alertNumber++;
                    });
                    if (alertNumber > 0) {
                      $('.my-push-message-ul').removeAttr('style');
                      $('.my-push-message-a').append('<span class="hatchers-bell-badge">' + alertNumber + '</span>');
                      $('.my-push-message-number').html('<?=$this->lang->line("la_fs") . " "?>' + alertNumber + '<?=" " . $this->lang->line("la_ls")?>');
                    } else {
                      $('.my-push-message-ul').remove();
                    }

                    if (alertNumber > lastAlertCount) {
                      var newCount = alertNumber - lastAlertCount;
                      if (soundEnabled) {
                        var audio = new Audio('<?=base_url('assets/webcamjs/demos/shutter.mp3')?>');
                        audio.play();
                      }
                      if (desktopEnabled && "Notification" in window && Notification.permission === "granted") {
                        new Notification('Hatchers', {
                          body: newCount + ' new notification(s)',
                          icon: '<?=base_url('uploads/images/'.$siteinfos->photo)?>'
                        });
                      }
                    }
                    lastAlertCount = alertNumber;
                  }
                });
              }, 5000);

              $(document).on('click', '.hatchers-dismiss', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var $item = $(this).closest('li');
                var type = $(this).data('type');
                var id = $(this).data('id');
                $.ajax({
                  type: 'GET',
                  url: "<?=base_url('alert/dismiss')?>/" + type + "/" + id,
                  success: function () {
                    $item.remove();
                  }
                });
              });

              var soundEnabled = localStorage.getItem('hatchers_sound') !== 'off';
              var desktopEnabled = localStorage.getItem('hatchers_desktop') === 'on';
              $(document).on('click', '.hatchers-toggle-sound', function (e) {
                e.preventDefault();
                soundEnabled = !soundEnabled;
                localStorage.setItem('hatchers_sound', soundEnabled ? 'on' : 'off');
                $(this).toggleClass('active', soundEnabled);
              });
              $(document).on('click', '.hatchers-toggle-desktop', function (e) {
                e.preventDefault();
                desktopEnabled = !desktopEnabled;
                localStorage.setItem('hatchers_desktop', desktopEnabled ? 'on' : 'off');
                $(this).toggleClass('active', desktopEnabled);
                if (desktopEnabled && "Notification" in window && Notification.permission !== "granted") {
                  Notification.requestPermission();
                }
              });

              $(document).on('click', '.hatchers-clear-all', function (e) {
                e.preventDefault();
                if (confirm('Clear all notifications?')) {
                  window.location.href = $(this).attr('href');
                }
              });
            });
        </script>
    </body>
</html>
