<body class="search-app quick-results-off">
    <?php $this->getView('admin', 'main', 'loading', ''); ?>
    <div class="mn-content fixed-sidebar">
        <?php $this->getView('admin', 'main', 'header', ''); ?>    
        <?php $this->getView('admin', 'main', 'menu', ''); ?>
        <?php $this->getView('admin', 'main', 'modalInput', ''); ?>

        <main class="mn-inner">
            <div class="search-header">
                <div class="card card-transparent no-m">
                    <div class="card-content no-s">
                        <div class="z-depth-1 search-tabs">
                            <div class="search-tabs-container">
                                <div class="col s12 m12 l12">
                                    <div class="row search-tabs-row search-tabs-container blue-grey white-text">
                                        <div class="col s12 m6 l6">
                                            <span style="line-height: 48px;text-transform: uppercase;"><?= $title ?></span>
                                        </div>
                                        <div class="col s12 m6 l6 right-align search-stats">
                                            <span class="secondary-stats">
                                                <a style="font-size: 13px;" class="breadcrumb white-text" href="<?= $this->link('') ?>">Index</a>
                                                <a style="font-size: 13px;" class="breadcrumb white-text">Presensi</a>
                                                <a style="font-size: 13px;" class="breadcrumb white-text">Pembaruan Data Mesin</a>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col s12">
                    <div class="card stats-card">
                        <div class="card-action" style="padding-bottom: 0px">
                            <form id="frmData" class="navbar-search expanded" onsubmit="return false" autocomplete="off" role="search" >
                                <?= comp\MATERIALIZE::inputKey('page', '1'); ?>
                                <div class="row">
                                    <div class="input-field col s5">
                                        <?= comp\MATERIALIZE::inputSelect('id_kelompok_mesin', $pil_kel_mesin, '') ?>
                                        <label for="id_kelompok_mesin">Kelompok Lokasi Kerja</label>
                                    </div>
                                    <div class="input-field col s5">
                                        <?= comp\MATERIALIZE::inputText('nama_mesin', 'text', '') ?>
                                        <label for="nama_mesin">Cari Nama Mesin</label>
                                    </div>
                                    <div class="input-field col s2">
                                        <button class="waves-effect waves-light btn green" type="submit">
                                            <i class="material-icons left">search</i>Cari
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="card-content" style="padding-top: 0px">
                            <div id="data-tabel"></div>
                        </div>
                    </div>
                    <div id="sparkline-bar"></div>
                </div>
            </div>
        </main>
        <?php $this->getView('admin', 'main', 'footer', ''); ?>
    </div>
    <!-- ./wrapper -->
    <link href="assets/plugins/sweetalert/sweetalert.css" rel="stylesheet">
    
    <script src="assets/plugins/sweetalert/sweetalert.min.js"></script>
    <script src="<?= $this->link($this->getProject() . $this->getController() . '/script.php'); ?>"></script>
    <script>
                                    (function ($) {
                                        "use strict";
                                        app.init("<?= $this->link($this->getProject() . $this->getController()); ?>");
                                        app.loadTabel();
                                        $(document).on("submit", "#frmData", function () {
                                            $("#page").val(1);
                                            app.loadTabel();
                                        });
                                        $(document).on("change", "#id_kelompok_mesin", function () {
                                            $("#page").val(1);
                                            app.loadTabel();
                                        });

                                        $(document).on("click", ".btnForm", function () {
                                            app.showForm(this.id);
                                        });
                                        $(document).on("click", ".btnUpdate", function () {
                                            app.updateMesin(this.id);
//                                        alert(this.id);
                                        });

                                        $(document).on("click", ".paging", function () {
                                            app.tabelPagging($(this).attr("number-page"));
                                        });
                                    })(jQuery);
    </script>
</body>