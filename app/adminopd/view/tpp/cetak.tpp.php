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
                                            <span style="line-height: 48px;text-transform: uppercase;"><?= $title ?></span>
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
                            <form id="frmData" class="navbar-search expanded" role="search" method="post" action="<?= $this->link('adminopd/tpp/cetaktpp') ?>">
                                <div class="row">
                                    <div class="input-field col s7">
                                        <?= comp\MATERIALIZE::inputText('satker', 'text', $satker, 'disabled style="color: rgba(0,0,0,.7)"'); ?>
                                        <label>Satuan Kerja</label>
                                    </div>
                                    <div class="input-field col s2">
                                        <select name="tahun" id="pilihtahun">
                                            <option value="<?= $tahun ?>"><?= $tahun ?></option>
                                        </select>
                                        <label>Pilih Tahun</label>
                                    </div>
                                    <div class="input-field col s3">
                                        <button class="btn-floating btn waves-effect waves-light indigo" title="Cetak TPP" type="button" id="btnCetak" style="display: none">
                                            <i class="material-icons left">print</i>
                                        </button>
                                        <span id="btnMsg" class="chip red darken-4 white-text" style="display: none">Cetak menunggu backup data..</span>
                                    </div>
                                </div>
                                <select name="format" id="format" class="browser-default hide">
                                    <option value="TPP" selected>Laporan TPP</option>
                                </select>
                                <?= comp\MATERIALIZE::inputKey('download', 1); ?>
                                <?= comp\MATERIALIZE::inputKey('bulan', 12); ?>
                                <?= comp\MATERIALIZE::inputKey('kd_tpp', $kd_tpp); ?>
                                <?= comp\MATERIALIZE::inputKey('bendahara', (is_array($bendahara) ? $bendahara['id_bendahara'] : '')); ?>
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
    <script src="<?= $this->link($this->getProject() . $this->getController() . '/script.php'); ?>"></script>
    <script>
        (function ($) {
            "use strict";
            app.init("<?= $this->link($this->getProject() . $this->getController()); ?>");

            $("#page").val(1);
            app.loadTabel();
            app.checkBc();

            $('#btnCetak').on('click', function() {
                checkBendahara();
            });

            function checkBendahara() {
                var format = $('#format option:selected').val();
                var bendahara = $('#bendahara').val();
                var verified = $('#verified').val();
                console.log('tes');

                if (format == 'TPP' && !bendahara) {
                    alert('Mohon untuk memilih pegawai bendahara pengeluaran terlebih dahulu.');
                    app.loadTabel();

                //cek verifikasi laporan
                } else
                    $('#frmData').submit();
            }

            $("#data-tabel").on("click", ".paging", function () {
                app.tabelPagging($(this).attr("number-page"));
            });
        })(jQuery);
    </script>
</body>
