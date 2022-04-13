<body class="search-app quick-results-off">
    <?php $this->getView('adminopd', 'main', 'loading', ''); ?>
    <div class="mn-content fixed-sidebar">
        <?php $this->getView('adminopd', 'main', 'header', ''); ?>
        <?php $this->getView('adminopd', 'main', 'menu', ''); ?>

        <main class="mn-inner">
            <div class="search-header">
                <div class="card card-transparent no-m">
                    <div class="card-content no-s">
                        <div class="z-depth-1 search-tabs">
                            <div class="search-tabs-container">
                                <div class="col s12 m12 l12">
                                    <div class="row search-tabs-row search-tabs-container blue-grey white-text">
                                        <div class="col s12 m6 l6">
                                            <span style="line-height: 48px;text-transform: uppercase;"><?= $title ?> - LAPORAN PRESENSI</span>
                                        </div>
                                        <div class="col s12 m6 l6 right-align search-stats">
                                            <span class="secondary-stats"><?= $breadcrumb; ?></span>
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
                    <!-- Tombol Navigasi Index -->
                    <div id="showIndex" class="card stats-card">
                        <div class="card-action" style="padding-bottom: 0px">
                            <form id="frmData" class="navbar-search expanded" role="search" method="post" action="<?= $this->link('adminopd/tpp/cetakpresensi') ?>">
                                <div class="row">
                                    <div class="input-field col s4">
                                        <?= comp\MATERIALIZE::inputText('satker', 'text', $satker, 'disabled style="color: rgba(0,0,0,.7)"'); ?>
                                        <label>Satuan Kerja</label>
                                    </div>
                                    <div class="input-field col s1">
                                        <?= comp\MATERIALIZE::inputText('bulan', 'text', $namabulan, 'disabled style="color: rgba(0,0,0,.7)"'); ?>
                                        <label>Bulan</label>
                                    </div>
                                    <div class="input-field col s1">
                                        <?= comp\MATERIALIZE::inputText('tahun', 'text', $tahun, 'disabled style="color: rgba(0,0,0,.7)"'); ?>
                                        <label>Tahun</label>
                                    </div>
                                    <div class="input-field col s2">
                                        <div id="kolomJenis">
                                            <select name="jenis" id="jenis">
                                                <option value="1">1 - MK</option>
                                                <option value="2">2 - APEL</option>
                                                <option value="3">3 - PK</option>
                                            </select>
                                            <label>Jenis Laporan</label>
                                        </div>
                                    </div>
                                    <!--div class="input-field col s2">
                                        <div id="kolomTingkat">
                                            <select name="tingkat" id="tingkat">
                                                <?php
                                                $selected = '';
                                                for ($i = 1; $i <= $tingkattpp; $i++) {
                                                    if ($i == $tingkattpp)
                                                        $selected = ' selected';

                                                    echo '<option value="' . $i . '" ' . $selected . '>Tingkat ' . $i . '</option>';
                                                }
                                                ?>
                                            </select>
                                            <label>Pilih Tingkat Laporan</label>
                                        </div>
                                    </div-->
                                    <div class="input-field col s3">
                                        <button class="btn-floating btn waves-effect waves-light green btnTampil" title="Tampilkan" type="button">
                                            <i class="material-icons left">search</i>
                                        </button>
                                        <button class="btn-floating btn waves-effect waves-light indigo" title="Cetak" type="submit" id="btnCetak" style="display: none">
                                            <i class="material-icons left">print</i>
                                        </button>
                                        <span id="btnMsg" class="chip red darken-4 white-text" style="display: none">Cetak menunggu backup data..</span>
                                    </div>
                                </div>
                                <?= comp\MATERIALIZE::inputKey('kd_tpp', $kd_tpp); ?>
                                <?= comp\MATERIALIZE::inputKey('tahun', $tahun); ?>
                                <?= comp\MATERIALIZE::inputKey('bulan', $bulan); ?>
                                <?= comp\MATERIALIZE::inputKey('tpptingkat', $tingkattpp); ?>
                                <?= comp\MATERIALIZE::inputKey('page', '1'); ?>
                                <?= comp\MATERIALIZE::inputKey('download', '1'); ?>
                            </form>
                        </div>
                        <div class="card-content" style="padding-top: 0px">
                            <div class="progress" id="progress" style="display: none">
                                <div class="indeterminate"></div>
                            </div>
                            <div id="data-tabel"></div>
                        </div>
                    </div>
                    <!-- end Tombol Navigasi Index -->

                    <div id="sparkline-bar"></div>
                </div>
            </div>
        </main>
        <?php $this->getView('adminopd', 'main', 'footer', ''); ?>
    </div>

    <!-- ./wrapper -->
    <link href="assets/plugins/sweetalert/sweetalert.css" rel="stylesheet">
    <style>
        .sweet-alert {
            width: 50%;
            margin-left: 0;
            left: 25%;
        }
    </style>
    <script src="assets/plugins/sweetalert/sweetalert.min.js"></script>
    <script src="<?= $this->link($this->getProject() . $this->getController() . '/script.php'); ?>"></script>
    <script>
        (function($) {
            "use strict";
            app.init("<?= $this->link($this->getProject() . $this->getController()); ?>");

            app.checkBc();

            $(".btnTampil").on("click", function() {
                $("#page").val(1);
                app.loadTabelpresensi();
            }).click();

            $(document).on("click", "#btnCetak", function() {
                $("#frmData").submit();
            }).click();

        })(jQuery);
    </script>
</body>