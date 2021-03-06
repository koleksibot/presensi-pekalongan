<?php

namespace app\admin\model;

use system;
use comp\FUNC;

class HusnanWModerasiModel extends system\Model {

    protected $dbPresensi = null;
    protected $dbPegawai = null;

    public function __construct() {
        parent::__construct();
        parent::setConnection('db_presensi');
    }

    public function getDaftarPengguna() {
        parent::setConnection("db_presensi");
        $sql = "SELECT username, nipbaru, kdlokasi, grup_pengguna_kd FROM tb_pengguna WHERE status_pengguna = 'enable'";
        return $this->getData($sql, [])["value"];
    }

    public function setPass($username, $pass) {
        parent::setConnection("db_presensi");
        $where = "username = '" . $username . "'";
        $params = ["password" => FUNC::encryptor($pass)];
        return $this->husnanWUpdate("tb_pengguna", $params, $where);
    }

    public function getFlags($mid) {
        parent::setConnection('db_presensi');
        $sql = "SELECT flag_operator_opd, flag_kepala_opd, flag_operator_kota, flag_kepala_kota FROM tb_moderasi WHERE id = ?";
        return $this->getData($sql, [$mid])["value"][0];
    }

    public function getDateLimit($kodeGrup) {
        parent::setConnection('db_presensi');
        $sql = "SELECT batas_moderasi FROM tb_grup_pengguna WHERE kd_grup_pengguna = ?";
        return $this->getData($sql, [$kodeGrup])["value"][0]["batas_moderasi"];
    }

    public function getDateModified($mid) {
        parent::setConnection('db_presensi');
        $sql = "SELECT dt_flag_operator_opd, dt_flag_kepala_opd, dt_flag_operator_kota, dt_flag_kepala_kota FROM tb_moderasi WHERE id = ?";
        return $this->getData($sql, [$mid])["value"][0];
    }

    public function getDokumenModerasi($mid) {
        parent::setConnection('db_presensi');
        $sql = "SELECT filename FROM tb_dokumen_moderasi WHERE moderasi_id = ?";
        return $this->getData($sql, [$mid])["value"];
    }

    public function getInfoModerasi($mid, $isFinal = false) {
        $infoMod = $this->getDaftarVerModOri($mid, $isFinal)["daftarModPegawai"];

        if (count($infoMod) === 0) {
            return false;
        }

        return $infoMod[0];
    }

    public function updateModerasi($data, $isMassive = false) {
        if (!$isMassive) {
            $where = "id = " . $data["mid"];
        } else {
            $where = "id IN(" . implode(",", $data["mids"]) . ")";
        }

        //added by daniek
        if (isset($data['kdlokasi']) && $data['kdlokasi'] != '') {
            $where .= ' AND kdlokasi = "' . $data['kdlokasi'] . '"';
        }

        $params = [
            "flag_operator_kota" => $data["flag"],
            "dt_flag_operator_kota" => date("Y-m-d H:i:s"),
            "dt_last_modified" => date("Y-m-d H:i:s"),
            "catatan_operator_kota" => $data["catatan"]
        ];
        return $this->husnanWUpdate("tb_moderasi", $params, $where);
    }

    public function getUserGroup($mid) {
        parent::setConnection('db_presensi');
        $sql = "SELECT usergroup FROM tb_moderasi WHERE id = ?";
        return $this->getData($sql, [$mid])["value"][0]["usergroup"];
    }

    public function getDaftarOpd() {
        parent::setConnection('db_pegawai');

        $sql = "SELECT tlokasi.kdlokasi, tlokasi.singkatan_lokasi FROM tref_lokasi_kerja tlokasi ORDER BY singkatan_lokasi";
        $daftarOpd = $this->getData($sql, [])["value"];

        return $daftarOpd;
    }

    public function getDaftarPegawaiModerasi($kodeLokasi) {
        parent::setConnection('db_pegawai');

        $sql = "SELECT tpeg.nipbaru, CONCAT(gelar_depan, ' ', namapeg, ' ', gelar_blkg) AS nama_lengkap, pin_absen FROM texisting_personal tperson INNER JOIN texisting_kepegawaian tpeg ON tperson.nipbaru = tpeg.nipbaru INNER JOIN tref_golongan_ruang tgolruang ON tpeg.golruang = tgolruang.golruang WHERE tpeg.kdlokasi = ? ORDER BY kdgol";
        $daftarPegawai = $this->getData($sql, [$kodeLokasi])["value"];

        return $daftarPegawai;
    }

    //added by daniek
    public function getDetailMod($mid = null) {
        parent::setConnection('db_presensi');

        if ($mid === null) {
            return false;
        }

        $sql = "SELECT tmod.*, tjm.nama_jenis, tkp.ket_kode_presensi, tkp.pot_kode_presensi, tgrup.nama_grup_pengguna AS grup_pemohon FROM tb_moderasi tmod INNER JOIN tb_kode_presensi tkp ON tmod.kode_presensi = tkp.kode_presensi INNER JOIN tb_jenis_moderasi tjm ON tmod.kd_jenis = tjm.kd_jenis INNER JOIN tb_grup_pengguna tgrup ON tmod.usergroup = tgrup.kd_grup_pengguna WHERE tmod.id = ? AND flag_operator_opd IS NOT NULL AND flag_kepala_opd IS NOT NULL ";
        $params = [$mid];

        $daftarModerasi = $this->getData($sql, $params)["value"];
        $strPin = FUNC::husnanWStrImplode($daftarModerasi, "pin_absen");

        parent::setConnection('db_pegawai');
        $sql = "SELECT tpeg.nipbaru, CONCAT(gelar_depan, ' ', namapeg, ' ', gelar_blkg) AS nama_lengkap, pin_absen, singkatan_lokasi FROM texisting_personal tperson INNER JOIN texisting_kepegawaian tpeg ON tperson.nipbaru = tpeg.nipbaru INNER JOIN tref_lokasi_kerja tlokasi ON tpeg.kdlokasi = tlokasi.kdlokasi WHERE pin_absen IN (" . $strPin . ")";
        $daftarPegawai = $this->getData($sql, [])["value"];

        $daftarModPegawai = [];

        foreach ($daftarModerasi as $mod) {
            foreach ($daftarPegawai as $peg) {
                if ($mod["pin_absen"] === $peg["pin_absen"]) {
                    $daftarModPegawai[] = array_merge($mod, $peg);
                    break;
                }
            }
        }

        if (count($daftarModPegawai) == 0) {
            return false;
        }

        return $daftarModPegawai[0];
    }

    public function getDaftarVerModOri($mid = null, $isFinal = false, $page = 1) {
        parent::setConnection('db_presensi');

        $posisi = 0;
        $batas = 1000;
        $total = 0;

        $where = $isFinal ? " AND (flag_kepala_opd = '2' OR flag_kepala_opd = '3')" : " AND (flag_kepala_opd <> '2' AND flag_kepala_opd <> '3')";

        if ($mid === null || !is_numeric($mid)) {
            $params = [];

            if (!is_numeric($mid) && $mid !== null) {
                $where .= " AND kdlokasi = ?";
                $params = [$mid];
            }

            $sql = "SELECT COUNT(pin_absen) AS total
            FROM tb_moderasi tmod INNER JOIN tb_kode_presensi tkp ON tmod.kode_presensi = tkp.kode_presensi INNER JOIN tb_jenis_moderasi tjm ON tmod.kd_jenis = tjm.kd_jenis WHERE flag_operator_opd IS NOT NULL AND flag_kepala_opd IS NOT NULL " . $where;

            $total = $this->getData($sql, $params)["value"];

            if (count($total) > 0) {
                $total = intval($total[0]["total"]);
            }

            $posisi = ($page - 1) * $batas;

            $sql = "SELECT tmod.*, tjm.nama_jenis, tkp.ket_kode_presensi, tkp.pot_kode_presensi, (SELECT COUNT(*) FROM tb_moderasi tm WHERE tm.pin_absen = tmod.pin_absen) AS jml
            FROM tb_moderasi tmod INNER JOIN tb_kode_presensi tkp ON tmod.kode_presensi = tkp.kode_presensi INNER JOIN tb_jenis_moderasi tjm ON tmod.kd_jenis = tjm.kd_jenis WHERE flag_operator_opd IS NOT NULL AND flag_kepala_opd IS NOT NULL " . $where . " ORDER BY jml DESC, pin_absen, flag_operator_kota, tanggal_awal LIMIT " . $posisi . ", " . $batas;
        } else {
            $sql = "SELECT tmod.*, tjm.nama_jenis, tkp.ket_kode_presensi, tkp.pot_kode_presensi, tgrup.nama_grup_pengguna AS grup_pemohon FROM tb_moderasi tmod INNER JOIN tb_kode_presensi tkp ON tmod.kode_presensi = tkp.kode_presensi INNER JOIN tb_jenis_moderasi tjm ON tmod.kd_jenis = tjm.kd_jenis INNER JOIN tb_grup_pengguna tgrup ON tmod.usergroup = tgrup.kd_grup_pengguna WHERE tmod.id = ? AND flag_operator_opd IS NOT NULL AND flag_kepala_opd IS NOT NULL " . $where;
            $params = [$mid];
        }

        $daftarModerasi = $this->getData($sql, $params)["value"];
        $strPin = FUNC::husnanWStrImplode($daftarModerasi, "pin_absen");

        parent::setConnection('db_pegawai');
        $sql = "SELECT tpeg.nipbaru, CONCAT(gelar_depan, ' ', namapeg, ' ', gelar_blkg) AS nama_lengkap, pin_absen, singkatan_lokasi FROM texisting_personal tperson INNER JOIN texisting_kepegawaian tpeg ON tperson.nipbaru = tpeg.nipbaru INNER JOIN tref_lokasi_kerja tlokasi ON tpeg.kdlokasi = tlokasi.kdlokasi WHERE pin_absen IN (" . $strPin . ")";
        $daftarPegawai = $this->getData($sql, [])["value"];

        $daftarModPegawai = [];

        foreach ($daftarModerasi as $mod) {
            foreach ($daftarPegawai as $peg) {
                if ($mod["pin_absen"] === $peg["pin_absen"]) {
                    $daftarModPegawai[] = array_merge($mod, $peg);
                    break;
                }
            }
        }
        
        return [
            "daftarModPegawai" => $daftarModPegawai,
            "no" => $posisi + 1,
            "page" => $page,
            "limiter" => $batas,
            "total" => $total
        ];
    }

    public function getDaftarVerMod($input) {
        parent::setConnection('db_presensi');

        if (!isset($input['kdlokasi']))
            return [];

        $where = "";
        if (isset($input['kdlokasi']) && ($input['kdlokasi'] === null || !is_numeric($input['kdlokasi']))) {
            $params = [];

            if (!is_numeric($input['kdlokasi']) && $input['kdlokasi'] !== null) {
                $where .= " AND kdlokasi = ?";
                $params = [$input['kdlokasi']];
            }

            if (isset($input['bulan'])) {
                $where .= ' AND MONTH(tanggal_awal) = ? ';
                array_push($params, $input['bulan']);
            }

            if (isset($input['tahun'])) {
                $where .= '  AND YEAR(tanggal_awal) = ? ';
                array_push($params, $input['tahun']);
            }

            if (isset($input['status'])) {
                switch ($input['status']) {
                    case 'semua':
                        $c_status = '';
                        break;
                    case 'sah':
                        $c_status = 'AND flag_operator_kota = "2"';
                        break;
                    case 'null':
                        $c_status = 'AND flag_operator_kota IS NULL';
                        break;
                }
                $where .= $c_status . ' ';
            }

            $sql = "SELECT tmod.*, tjm.nama_jenis, tkp.ket_kode_presensi, tkp.pot_kode_presensi, (SELECT COUNT(*) FROM tb_moderasi tm WHERE tm.pin_absen = tmod.pin_absen) AS jml
            FROM tb_moderasi tmod INNER JOIN tb_kode_presensi tkp ON tmod.kode_presensi = tkp.kode_presensi INNER JOIN tb_jenis_moderasi tjm ON tmod.kd_jenis = tjm.kd_jenis WHERE flag_operator_opd IS NOT NULL AND flag_kepala_opd IS NOT NULL " . $where . " ORDER BY jml DESC, pin_absen, flag_operator_kota, tanggal_awal";
        }

        $daftarModerasi = $this->getData($sql, $params)["value"];
        $strPin = FUNC::husnanWStrImplode($daftarModerasi, "pin_absen");

        parent::setConnection('db_pegawai');
        $sql = "SELECT tpeg.nipbaru, CONCAT(gelar_depan, ' ', namapeg, ' ', gelar_blkg) AS nama_lengkap, pin_absen, singkatan_lokasi FROM texisting_personal tperson INNER JOIN texisting_kepegawaian tpeg ON tperson.nipbaru = tpeg.nipbaru INNER JOIN tref_lokasi_kerja tlokasi ON tpeg.kdlokasi = tlokasi.kdlokasi WHERE pin_absen IN (" . $strPin . ")";

        if (isset($input['cari']) && $input['cari'] != '') {
            $sql .= ' AND (tperson.nipbaru LIKE "%' . $input['cari'] . '%" OR tperson.namapeg LIKE "%' . $input['cari'] . '%")';
        }
        $daftarPegawai = $this->getData($sql, [])["value"];

        $daftarModPegawai = [];

        foreach ($daftarModerasi as $mod) {
            foreach ($daftarPegawai as $peg) {
                if ($mod["pin_absen"] === $peg["pin_absen"]) {
                    $daftarModPegawai[] = array_merge($mod, $peg);
                    break;
                }
            }
        }
        
        return $daftarModPegawai;
    }

    public function getDaftarVerMod_v3($input) {
        parent::setConnection('db_presensi');

        if (!isset($input['kdlokasi'])) {
            return [];
        }

        $where = "";
        if (isset($input['kdlokasi']) && ($input['kdlokasi'] === null || !is_numeric($input['kdlokasi']))) {
            $params = [];

            if (!is_numeric($input['kdlokasi']) && $input['kdlokasi'] !== null) {
                $where .= " AND kdlokasi = ?";
                $params = [$input['kdlokasi']];
            }

            if (isset($input['bulan'])) {
                $where .= ' AND MONTH(tanggal_awal) = ? ';
                array_push($params, $input['bulan']);
            }

            if (isset($input['tahun'])) {
                $where .= '  AND YEAR(tanggal_awal) = ? ';
                array_push($params, $input['tahun']);
            }

            if (isset($input['status'])) {
                switch ($input['status']) {
                    case 'semua':
                        $c_status = '';
                        break;
                    case 'sah':
                        $c_status = 'AND flag_operator_kota = "2"';
                        break;
                    case 'null':
                        $c_status = 'AND flag_operator_kota IS NULL';
                        break;
                }
                $where .= $c_status . ' ';
            }

            $sql = 'SELECT tmod.*, tjm.nama_jenis, tkp.ket_kode_presensi, tkp.pot_kode_presensi '
                    . ' FROM tb_moderasi tmod '
                    . ' INNER JOIN tb_kode_presensi tkp ON tmod.kode_presensi = tkp.kode_presensi '
                    . ' INNER JOIN tb_jenis_moderasi tjm ON tmod.kd_jenis = tjm.kd_jenis '
                    . ' WHERE flag_operator_opd IS NOT NULL AND flag_kepala_opd IS NOT NULL ' . $where
                    . ' ORDER BY pin_absen, flag_operator_kota, tanggal_awal';
        }

        $daftarModerasi = $this->getData($sql, $params)["value"];
        $strPin = FUNC::husnanWStrImplode($daftarModerasi, "pin_absen");

        parent::setConnection('db_pegawai');
        $sql = "SELECT tpeg.nipbaru, CONCAT(gelar_depan, ' ', namapeg, ' ', gelar_blkg) AS nama_lengkap, pin_absen, singkatan_lokasi FROM texisting_personal tperson INNER JOIN texisting_kepegawaian tpeg ON tperson.nipbaru = tpeg.nipbaru INNER JOIN tref_lokasi_kerja tlokasi ON tpeg.kdlokasi = tlokasi.kdlokasi WHERE pin_absen IN (" . $strPin . ")";

        if (isset($input['cari']) && $input['cari'] != '') {
            $sql .= ' AND (tperson.nipbaru LIKE "%' . $input['cari'] . '%" OR tperson.namapeg LIKE "%' . $input['cari'] . '%")';
        }
        $daftarPegawai = $this->getData($sql, [])["value"];

        $daftarModPegawai = [];

        foreach ($daftarModerasi as $mod) {
            foreach ($daftarPegawai as $peg) {
                if ($mod["pin_absen"] === $peg["pin_absen"]) {
                    $daftarModPegawai[] = array_merge($mod, $peg);
                    break;
                }
            }
        }
        
        return $daftarModPegawai;
    }

    public function getTotalBelumVerifikasiOri($kodeLokasi) {
        parent::setConnection('db_presensi');
        $sql = "SELECT COUNT(id) AS total FROM tb_moderasi WHERE flag_operator_opd IS NOT NULL AND flag_kepala_opd IS NOT NULL AND flag_operator_kota IS NULL AND kdlokasi = ?";
        $total = $this->getData($sql, [$kodeLokasi])["value"];

        if (isset($total[0])) {
            return $total[0]["total"];
        }

        return 0;
    }

    public function getTotalBelumVerifikasi($input) {
        parent::setConnection('db_presensi');
        $sql = "SELECT COUNT(id) AS total FROM tb_moderasi WHERE flag_operator_opd IS NOT NULL AND flag_kepala_opd IS NOT NULL AND flag_operator_kota IS NULL";

        if (isset($input['kdlokasi'])) {
            $sql .= " AND kdlokasi = ?";
            $params = [$input['kdlokasi']];
        } else
            return 0;

        if (isset($input['bulan'])) {
            $sql .= ' AND MONTH(tanggal_awal) = ? ';
            array_push($params, $input['bulan'] + 1);
        }

        if (isset($input['tahun'])) {
            $sql .= '  AND YEAR(tanggal_awal) = ? ';
            array_push($params, $input['tahun']);
        }

        if (isset($input['status'])) {
            switch ($input['status']) {
                case 'semua':
                    $c_status = '';
                    break;
                case 'sah':
                    $c_status = 'AND flag_operator_kota = "2"';
                    break;
                case 'null':
                    $c_status = 'AND flag_operator_kota IS NULL';
                    break;
            }
            $sql .= $c_status . ' ';
        }

        $total = $this->getData($sql, $params)["value"];

        if (isset($total[0])) {
            return $total[0]["total"];
        }

        return 0;
    }

    public function simpanDokumenModerasi($data) {
        parent::setConnection('db_presensi');
        return $this->husnanWInsert("tb_dokumen_moderasi", $data);
    }

    public function simpanPemohonModerasi($data) {
        parent::setConnection('db_presensi');
        return $this->husnanWInsert("tb_moderasi", $data);
    }

    public function getJenisModerasi() {
        parent::setConnection('db_presensi');
        $sql = "SELECT kd_jenis, nama_jenis FROM tb_jenis_moderasi";
        return $this->getData($sql, [])["value"];
    }

    public function getCurrentPns($nip) {
        parent::setConnection('db_pegawai');

        $sql = "SELECT tpeg.nipbaru, CONCAT(gelar_depan, ' ', namapeg, ' ', gelar_blkg) AS nama_lengkap, pin_absen, singkatan_lokasi, path_foto_pegawai AS foto FROM texisting_personal tperson INNER JOIN texisting_kepegawaian tpeg ON tperson.nipbaru = tpeg.nipbaru INNER JOIN tref_lokasi_kerja tlokasi ON tpeg.kdlokasi = tlokasi.kdlokasi WHERE tpeg.nipbaru = ?";
        $data = $this->getData($sql, [$nip]);
        if ($data['count'] > 0) :
            return $data["value"][0];
        else :
            return ['nama_lengkap' => 'Nama Tidak Terdaftar', 'nipbaru' => 'Undefined'];
        endif;
    }

    public function getLastId($tableName) {
        parent::setConnection('db_presensi');
        $sql = "SELECT id FROM " . $tableName . " ORDER BY id DESC LIMIT 1";
        return $this->getData($sql, [])["value"][0]["id"];
    }

    public function husnanWDelete($tableName, Array $params) {
        if (is_null($this->db)) {
            parent::setConnection('db_presensi');
            $this->openConnection();
        }

        $keys = array_keys($params);
        $vals = array_values($params);
        $where = '';

        foreach ($keys as $key) {
            $where .= $key . ' = ? AND ';
        }

        $where = substr($where, 0, strlen($where) - 5);

        $sql = "DELETE FROM " . $tableName . " WHERE " . $where;

        try {
            $result = $this->db->prepare($sql);
            return $result->execute($vals);
        } catch (Exception $e) {
            echo "ERROR ON husnanWDelete: " . $e->getMessages();
        }
    }

    public function husnanWInsert($tableName, Array $params) {
        if (is_null($this->db)) {
            parent::setConnection('db_presensi');
            $this->openConnection();
        }

        $keys = implode(',', array_keys($params));
        $vals = array_values($params);
        $signs = FUNC::husnanWStrRepeater('?', count($params));
        $sql = "INSERT INTO " . $tableName . "(" . $keys . ") VALUES(" . $signs . ")";

        try {
            $result = $this->db->prepare($sql);
            return $result->execute($vals);
        } catch (Exception $e) {
            echo "ERROR ON husnanWInsert: " . $e->getMessages();
        }
    }

    public function husnanWUpdate($tableName, Array $params, $where) {
        if (is_null($this->db)) {
            parent::setConnection('db_presensi');
            $this->openConnection();
        }

        $keys = array_keys($params);
        $vals = array_values($params);
        $signs = "";

        foreach ($keys as $k) {
            $signs .= $k . " = ?,";
        }

        $signs = substr($signs, 0, strlen($signs) - 1);
        //$sql = "UPDATE ".$tableName." SET ".$signs." WHERE ".$where; -- edited by daniek
        $sql = "UPDATE " . $tableName . " SET " . $signs . " WHERE " . $where . "  AND flag_operator_opd IS NOT NULL AND flag_kepala_opd IS NOT NULL";
//echo $sql; exit();
        try {
            $result = $this->db->prepare($sql);
            return $result->execute($vals);
        } catch (Exception $e) {
            echo "ERROR ON husnanWUpdate: " . $e->getMessages();
        }
    }

    // added by daniek
    public function checkLaporanVerif($input) {
        parent::setConnection('db_presensi');

        if (!isset($input['kdlokasi']) || !isset($input['bulan']) || !isset($input['tahun']))
            return true;

        $params = [$input['kdlokasi'], $input['bulan'], $input['tahun']];
        $sql = "SELECT * FROM tb_laporan WHERE kdlokasi = ?  AND bulan = ? AND tahun = ?";

        $get = $this->getData($sql, $params);
        if ($get['count'] == 0 || $get['value'][0]['sah_kepala_opd'] == NULL)
            return false;

        return true;
    }

    // check
    public function checkLaporanVerif2($input) {
        parent::setConnection('db_presensi');

        if (!isset($input['kdlokasi']) || !isset($input['bulan']) || !isset($input['tahun']))
            return true;

        $params = [$input['kdlokasi'], $input['bulan'] + 1, $input['tahun']];
        $sql = "SELECT * FROM tb_laporan WHERE kdlokasi = ?  AND bulan = ? AND tahun = ?";

        $get = $this->getData($sql, $params);
        return $get;

        if ($get['count'] == 0 || $get['value'][0]['sah_kepala_opd'] == NULL)
            return false;

        return true;
    }

}
