<?php
use comp\FUNC;
?>
<style>
    .mn-content, body, html {
        font-size: 10px;
    }
    .chip {
        font-size: 10px;
    }
    .black-shadow {text-shadow: 2px 2px #000000;}
    .modal {width: 65%;}
    p, label { font-size: calc(50% + 0.8vw); }
    .card .card-image .card-title {position: relative; padding:0;}
    
</style>
<div class="row">
    <div class="col s12 m11">
        <div class="card" style="min-width: 90px;">
            <div class="card-image">
                <img src="http://simpeg.pekalongankota.go.id/upload/foto/<?= $info["nipbaru"] ?>-FOTO.jpg" class="responsive-img" style="max-width: 150px; margin: 0 auto;">
                <span class="card-title black-shadow" style="font-size:14px; text-align:center;"><p><?= $info["nama_lengkap"] ?></p></span>               
            </div>
            <div class="card-content center">
                <span class="chip red-text">STATUS MODERASI: <?= $info["status_final"] ?></span>
            </div>
            <div class="card-action center">
                <?php if ($this->login["grup_pengguna_kd"] === $info["usergroup"] && $info["flag_kepala_opd"] !== "2" && $info["flag_kepala_opd"] !== "3"): ?>
                <a class="btn-del-mod btn waves-effect waves-light grey" title="saya hapus" mid="<?= $info["id"] ?>"><i class="material-icons left red-text">delete_forever</i> HAPUS MODERASI</a>
                <?php else: ?>
                    <i class="material-icons medium">lock</i>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col s12">
        <table class="highlight bordered">
            <tbody>
                <tr>
                    <td>NIP</td>
                    <td>:</td>
                    <td><?= $info["nipbaru"] ?></td>
                </tr>
                <tr>
                    <td>NAMA LENGKAP</td>
                    <td>:</td>
                    <td><?= $info["nama_lengkap"] ?></td>
                </tr>
                <tr>
                    <td>OPD</td>
                    <td>:</td>
                    <td><?= $info["singkatan_lokasi"] ?></td>
                </tr>
                <tr>
                    <td>KATEGORI PRESENSI</td>
                    <td>:</td>
                    <td><?= $info["nama_jenis"] ?></td>
                </tr>
                <tr>
                    <td>JENIS MODERASI</td>
                    <td>:</td>
                    <td><?= strtoupper($info["ket_kode_presensi"]) ?> (<?= $info["kode_presensi"] ?>)</td>
                </tr>
                <tr>
                    <td>DIAJUKAN OLEH</td>
                    <td>:</td>
                    <td><?= $info["grup_pemohon"] ?></td>
                </tr>
                <tr>
                    <td>TGL PENGAJUAN</td>
                    <td>:</td>
                    <td><?= FUNC::toHusnanWSniDateTime($info["dt_created"]) ?></td>
                </tr>
                <tr>
                    <td>TGL AWAL MODERASI</td>
                    <td>:</td>
                    <td><?= FUNC::toHusnanWSniDate($info["tanggal_awal"]) ?></td>
                </tr>
                <tr>
                    <td>TGL AKHIR MODERASI</td>
                    <td>:</td>
                    <td><?= FUNC::toHusnanWSniDate($info["tanggal_akhir"]) ?></td>
                </tr>
                <tr>
                    <td>JML HARI MODERASI</td>
                    <td>:</td>
                    <td><?= FUNC::getHusnanWDeltaDates($info["tanggal_awal"], $info["tanggal_akhir"]) + 1 ?></td>
                </tr>
                <tr>
                    <td>POTONGAN</td>
                    <td>:</td>
                    <td><?= floatval($info["pot_kode_presensi"]) * 100 ?>%</td>
                </tr>
                <tr>
                    <td>KETERANGAN TAMBAHAN</td>
                    <td>:</td>
                    <td><?= empty($info["keterangan"]) ? '-' : $info["keterangan"] ?></td>
                </tr>
                <tr>
                    <td>DOKUMEN PENDUKUNG</td>
                    <td>:</td>
                    <td>
                        <?php if (count($dok) > 0): ?>
                        <ul>
                            <?php foreach ($dok as $index => $val): ?>
                                <li><a href="<?= $this->link('')."upload/moderasi/dokumen/".$val["filename"] ?>" target="_blank">Unduh Dokumen <?= $index + 1 ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                        <?php else: ?>
                            <b>-</b>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td>CATATAN ADMIN OPD</td>
                    <td>:</td>
                    <td><?= empty($info["catatan_operator_opd"]) ? '-' : $info["catatan_operator_opd"] ?></td>
                </tr>
                <tr>
                    <td>CATATAN KEPALA OPD</td>
                    <td>:</td>
                    <td><?= empty($info["catatan_kepala_opd"]) ? '-' : $info["catatan_kepala_opd"] ?></td>
                </tr>
                <tr>
                    <td>CATATAN ADMIN KOTA</td>
                    <td>:</td>
                    <td><?= empty($info["catatan_operator_kota"]) ? '-' : $info["catatan_operator_kota"] ?></td>
                </tr>
                <tr>
                    <td>CATATAN KEPALA BKPPD</td>
                    <td>:</td>
                    <td><?= empty($info["catatan_kepala_kota"]) ? '-' : $info["catatan_kepala_kota"] ?></td>
                </tr>
                <tr>
                    <td>CATATAN AKHIR KEPALA OPD</td>
                    <td>:</td>
                    <td><?= empty($info["catatan_final_kepala_opd"]) ? '-' : $info["catatan_final_kepala_opd"] ?></td>
                </tr>
                <tr>
                    <td>PERUBAHAN TERAKHIR</td>
                    <td>:</td>
                    <td><?= FUNC::toHusnanWSniDateTime($info["dt_last_modified"]) ?></td>
                </tr>
            </tbody>
        </table>
        <table class="bordered">
            <thead>
                <tr>
                    <th>&nbsp;</th>
                    <th class="center">Status</th>
                    <th class="center">Tgl. Verifikasi</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Operator OPD</td>
                    <td id="tdOpOpd" class="center"><?= FUNC::husnanWVerModStyle($info["flag_operator_opd"]) ?></td>
                    <td id="tdOpOpdDate" class="center"><?= FUNC::toHusnanWSniDateTime($info["dt_flag_operator_opd"]) ?></td>
                </tr>
                <tr>
                    <td>Kepala OPD</td>
                    <td id="tdKepOpd" class="center"><?= FUNC::husnanWVerModStyle($info["flag_kepala_opd"]) ?></td>
                    <td id="tdKepOpdDate" class="center"><?= FUNC::toHusnanWSniDateTime($info["dt_flag_kepala_opd"]) ?></td>
                </tr>
                <tr>
                    <td>Operator BKPPD</td>
                    <td id="tdOpKota" class="center"><?= FUNC::husnanWVerModStyle($info["flag_operator_kota"]) ?></td>
                    <td id="tdOpKotaDate" class="center"><?= FUNC::toHusnanWSniDateTime($info["dt_flag_operator_kota"]) ?></td>
                </tr>
                <tr>
                    <td>Kepala BKPPD</td>
                    <td id="tdKepKota" class="center"><?= FUNC::husnanWVerModStyle($info["flag_kepala_kota"]) ?></td>
                    <td id="tdKepKotaDate" class="center"><?= FUNC::toHusnanWSniDateTime($info["dt_flag_kepala_kota"]) ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>