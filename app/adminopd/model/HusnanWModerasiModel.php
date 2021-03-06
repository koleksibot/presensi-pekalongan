<?php

namespace app\adminopd\model;

use system;
use comp\FUNC;

class HusnanWModerasiModel extends system\Model {

    protected $dbPresensi = null;
    protected $dbPegawai = null;

    public function __construct() {
        parent::__construct();
        parent::setConnection('db_presensi');
    }

    public function isValidJumlahModerasiSatuPeriode($kodeLokasi, $pinAbsen, $katMod, $jenMod, $tglAwal, $tglAkhir) {
        parent::setConnection("db_presensi");

        $sql = "SELECT kd_jenis, kode_presensi FROM tb_moderasi WHERE kdlokasi = ? AND pin_absen = ? AND tanggal_awal BETWEEN '" . $tglAwal . "' AND '" . $tglAkhir . "'";
        $data1 = $this->getData($sql, [$kodeLokasi, $pinAbsen])["value"];
        //FUNC::husnanWVarDump($data1);
        if (!empty($data1) && count($data1) > 0) {
            $data1 = $data1[0];
            if (($katMod === "JNSMOD04" && $data1["kd_jenis"] !== "JNSMOD04") || ($katMod !== "JNSMOD04" && $data1["kd_jenis"] === "JNSMOD04")) {
                return false;
            }
        }

        $whereKodePresensi = "";
        $params = [$kodeLokasi, $pinAbsen, $katMod];

        /*
          if ($katMod !== "JNSMOD04") {
          $whereKodePresensi = "AND kode_presensi = ?";
          $params = [$kodeLokasi, $pinAbsen, $katMod, $jenMod];
          }
         */

        $sql = "SELECT kd_jenis, kode_presensi FROM tb_moderasi WHERE kdlokasi = ? AND pin_absen = ? AND kd_jenis = ? " . $whereKodePresensi . " AND tanggal_awal BETWEEN '" . $tglAwal . "' AND '" . $tglAkhir . "'";

        $data2 = $this->getData($sql, $params)["value"];
//FUNC::husnanWVarDump($data2);
        if (!empty($data2) && count($data2) > 0) {
            return false;
        }

        return true;
    }

    public function checkDatesMod($dateAwal, $dateAkhir, $pinAbsen) {
        // when returning values then it has invalid dates
        parent::setConnection("db_presensi");
        $sql = "SELECT tanggal_awal, tanggal_akhir FROM `tb_moderasi` WHERE (('" . $dateAwal . "' BETWEEN tanggal_awal AND tanggal_akhir AND '" . $dateAwal . "' <> tanggal_awal) OR ('" . $dateAkhir . "' BETWEEN tanggal_awal AND tanggal_akhir AND '" . $dateAkhir . "' <> tanggal_akhir) OR (tanggal_awal BETWEEN '" . $dateAwal . "' AND '" . $dateAkhir . "' AND '" . $dateAwal . "' <> tanggal_awal) OR (tanggal_akhir BETWEEN '" . $dateAwal . "' AND '" . $dateAkhir . "' AND '" . $dateAkhir . "' <> tanggal_akhir))  AND pin_absen = '" . $pinAbsen . "'";

        $data = $this->getData($sql, [])["value"];
//FUNC::husnanWVarDump($sql);
        if (!empty($data) && count($data) > 0) {
            return [
                "status" => "fail",
                "tglAwal" => $data[0]["tanggal_awal"],
                "tglAkhir" => $data[0]["tanggal_akhir"]
            ];
        }

        return [
            "status" => "success"
        ];
    }
    
    public function chkLockMod($kdlokasi, $year, $month) {
        parent::setConnection('db_presensi');
        $idKey = [$month, $year, $kdlokasi];
        $getReport = $this->getData('SELECT * FROM tb_laporan WHERE 1 AND bulan = ? AND tahun = ? AND kdlokasi = ?', $idKey);
//        return $getReport;
        return ($getReport['count']) ? true : false;
    }

    public function getDateLimit($kodeGrup) {
        parent::setConnection('db_presensi');
        $sql = "SELECT batas_moderasi FROM tb_grup_pengguna WHERE kd_grup_pengguna = ?";
        return $this->getData($sql, [$kodeGrup])["value"][0]["batas_moderasi"];
    }

    public function delModerasi($data, $kodeLokasi) {
        parent::setConnection('db_presensi');

        $sql = "SELECT filename FROM tb_dokumen_moderasi WHERE moderasi_id = ?";
        $dokumens = $this->getData($sql, [$data["mid"]])["value"];

        if (count($dokumens) > 0) {
            foreach ($dokumens as $dokumen) {
                @unlink(UPLOAD . 'moderasi/dokumen/' . $dokumen["filename"]);
            }

            $this->husnanWDelete("tb_dokumen_moderasi", ["moderasi_id" => $data["mid"]]);
        }

        $params = ["id" => $data["mid"], "kdlokasi" => $kodeLokasi];

        return $this->husnanWDelete("tb_moderasi", $params);
    }

    public function getUserGroup($mid) {
        parent::setConnection('db_presensi');
        $sql = "SELECT usergroup FROM tb_moderasi WHERE id = ?";
        return $this->getData($sql, [$mid])["value"][0]["usergroup"];
    }

    public function getFlags($mid) {
        parent::setConnection('db_presensi');
        $sql = "SELECT flag_operator_opd, flag_kepala_opd, flag_operator_kota, flag_kepala_kota FROM tb_moderasi WHERE id = ?";
        return $this->getData($sql, [$mid])["value"][0];
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

    public function getInfoModerasi($kodeLokasi, $mid, $isFinal = false) {
        return $this->getDaftarVerMod($kodeLokasi, $mid, $isFinal)[0];
    }
    
    public function updateModerasi($data, $kodeLokasi, $isMassive = false) {
        if (!$isMassive) {
            $where = "id = " . $data["mid"] . " AND kdlokasi = '" . $kodeLokasi . "'";
        } else {
            $where = "id IN(" . implode(",", $data["mids"]) . ") AND kdlokasi = '" . $kodeLokasi . "'";
        }

        $params = [
            "flag_operator_opd" => $data["flag"],
            "dt_flag_operator_opd" => date("Y-m-d H:i:s"),
            "dt_last_modified" => date("Y-m-d H:i:s"),
            "catatan_operator_opd" => $data["catatan"],
            "author" => $data["author"]
        ];
        return $this->husnanWUpdate("tb_moderasi", $params, $where);
    }

//    public function getDaftarVerMod($kodeLokasi, $mid = null, $isFinal = false)
//    {
//        parent::setConnection('db_presensi');
//
//        $where = $isFinal ? " AND (flag_kepala_opd = '2' OR flag_kepala_opd = '3')" : " AND (flag_kepala_opd <> '2' AND flag_kepala_opd <> '3' OR flag_kepala_opd IS NULL)";
//
//        if ($mid === null) {
//            $sql = "SELECT tmod.*, tjm.nama_jenis, tkp.ket_kode_presensi, tkp.pot_kode_presensi, (SELECT COUNT(*) FROM tb_moderasi tm WHERE tm.pin_absen = tmod.pin_absen) AS jml
//            FROM tb_moderasi tmod INNER JOIN tb_kode_presensi tkp ON tmod.kode_presensi = tkp.kode_presensi INNER JOIN tb_jenis_moderasi tjm ON tmod.kd_jenis = tjm.kd_jenis WHERE kdlokasi = ? ".$where." ORDER BY jml DESC, pin_absen, tanggal_awal";
//            $params = [$kodeLokasi];
//        } else {
//            $sql = "SELECT tmod.*, tjm.nama_jenis, tkp.ket_kode_presensi, tkp.pot_kode_presensi, tgrup.nama_grup_pengguna AS grup_pemohon FROM tb_moderasi tmod INNER JOIN tb_kode_presensi tkp ON tmod.kode_presensi = tkp.kode_presensi INNER JOIN tb_jenis_moderasi tjm ON tmod.kd_jenis = tjm.kd_jenis INNER JOIN tb_grup_pengguna tgrup ON tmod.usergroup = tgrup.kd_grup_pengguna WHERE kdlokasi = ? AND tmod.id = ? ".$where;
//            $params = [$kodeLokasi, $mid];
//        }
//
//        //FUNC::husnanWVarDump($sql);
//
//        $daftarModerasi = $this->getData($sql, $params)["value"];        
//        $strPin = FUNC::husnanWStrImplode($daftarModerasi, "pin_absen");
//
//        parent::setConnection('db_pegawai');        
//        $sql = "SELECT tpeg.nipbaru, CONCAT(gelar_depan, ' ', namapeg, ' ', gelar_blkg) AS nama_lengkap, pin_absen, singkatan_lokasi FROM texisting_personal tperson INNER JOIN texisting_kepegawaian tpeg ON tperson.nipbaru = tpeg.nipbaru INNER JOIN tref_lokasi_kerja tlokasi ON tpeg.kdlokasi = tlokasi.kdlokasi WHERE pin_absen IN (".$strPin.") AND tpeg.kdlokasi = ?";
//        $daftarPegawai = $this->getData($sql, [$kodeLokasi])["value"];
//
//        $daftarModPegawai = [];
//
//        foreach ($daftarModerasi as $mod) {
//            foreach ($daftarPegawai as $peg) {
//                if ($mod["pin_absen"] === $peg["pin_absen"]) {
//                    $daftarModPegawai[] = array_merge($mod, $peg);
//                    break;
//                }
//            }
//        }
//        
//        //FUNC::husnanWVarDump($daftarModPegawai);    
//
//        return $daftarModPegawai;
//    }

    /* added by Acil */
    // master
    public function getDataKrit($con, $tabel, $kriteria, $unset = array()) {
        parent::setConnection($con);
        $q_cari = '';
        $idKey = array();
        foreach ($unset as $key => $val) {
            unset($kriteria[$val]);
        }

        $field = $this->getTabel($tabel);
        foreach ($field as $key => $val) {
            if (isset($kriteria[$key])) {
                $q_cari .= 'AND (' . $key . ' = ?) ';
                array_push($idKey, $kriteria[$key]);
            }
        }

        $data = $this->getData('SELECT * FROM ' . $tabel . ' WHERE 1 ' . $q_cari, $idKey);

        if ($data['count'] > 0) {
            return $data['value'][0];
        } else {
            return $field;
        }
    }

    public function getPilKrit($con, $tabel, $kriteria, $opsi) {
        parent::setConnection($con);
        $q_cari = '';
        $idKey = array();
        $field = $this->getTabel($tabel);
        $order = isset($kriteria['order']) ? ' ORDER BY ' . $kriteria['order'] : '';
        foreach ($field as $key => $val) {
            if (isset($kriteria[$key])) {
                $q_cari .= 'AND (' . $key . ' = ?) ';
                array_push($idKey, $kriteria[$key]);
            }
        }

        $data = $this->getData('SELECT * FROM ' . $tabel . ' WHERE 1 ' . $q_cari . $order, $idKey);
        if ($data['count'] > 0) {
            foreach ($data['value'] as $kol) {
                $result[$kol[$opsi['key']]] = $kol[$opsi['value']];
            }
        } else {
            $result = array('' => '');
        }
        return $result;
    }
    //end master


    public function getDaftarVerMod($param) {
        parent::setConnection('db_presensi');
        $idKey = array();
        $q_cari = '';
        $field = $this->getTabel('view_moderasi');
        foreach ($field as $key => $val) {
            if (isset($param[$key])) {
                $q_cari .= 'AND (' . $key . ' = ?) ';
                array_push($idKey, $param[$key]);
            }
        }

        if (isset($param['bulan']) || isset($param['tahun'])) {
            $q_cari .= 'AND (MONTH(tanggal_awal) = ? AND YEAR(tanggal_awal) = ?) ';
            array_push($idKey, $param['bulan'], $param['tahun']);
        }
        if (isset($param['status'])) {
            switch ($param['status']) {
                case 'semua':
                    $c_status = '';
                    break;
                case 'tolak':
                    $c_status = 'AND flag_operator_opd = "0"';
                    break;
                case 'terima':
                    $c_status = 'AND flag_operator_opd = "1"';
                    break;
                case 'null':
                    $c_status = 'AND flag_operator_opd IS NULL';
                    break;
            }
            $q_cari .= $c_status . ' ';
        }

        $getModerasi = $this->getData('SELECT * FROM view_moderasi WHERE 1 ' . $q_cari . ' ORDER BY tanggal_awal', $idKey);
        $result = array();
        foreach ($getModerasi['value'] as $val) {
            $result['moderasi'][$val['pin_absen']][] = $val;
            $result['pegawai'][] = $val['pin_absen'];
        }

        return $result;
    }
    
    public function getDaftarPegawaiMod($param) {
        parent::setConnection('db_pegawai');
        $arrPin = join(' ,', $param['arrPin']);
        $q_cari = (!empty($param['cari'])) ? 'AND namapeg LIKE "%' . $param['cari'] . '%"' : '';
        $q_field = (!empty($param['field'])) ? $param['field'] : '*';
        $query = 'SELECT ' . $q_field . ' FROM texisting_kepegawaian pegawai '
                . 'JOIN texisting_personal personal ON pegawai.nipbaru = personal.nipbaru '
                . 'WHERE pin_absen IN ( ' . $arrPin . ' ) ' . $q_cari . ' ORDER BY namapeg ';
        $result = $this->getData($query, []);
        return $result;
    }

    /* end */

    public function simpanDokumenModerasi($data) {
        parent::setConnection('db_presensi');
        return $this->husnanWInsert("tb_dokumen_moderasi", $data);
    }

    public function simpanPemohonModerasi($data) {
        parent::setConnection('db_presensi');
        return $this->husnanWInsert("tb_moderasi", $data);
    }

    public function getDaftarPegawaiModerasi($kodeLokasi) {
        parent::setConnection('db_pegawai');

        $sql = "SELECT tpeg.nipbaru, CONCAT(gelar_depan, IF(gelar_depan = '', '', ' '), namapeg, IF(gelar_blkg = '', '', ', '), gelar_blkg) AS nama_lengkap, pin_absen "
                . "FROM texisting_personal tperson "
                . "INNER JOIN texisting_kepegawaian tpeg ON tperson.nipbaru = tpeg.nipbaru "
                . "WHERE tpeg.kdlokasi = ? OR tpeg.kdsublokasi = ? ORDER BY namapeg";
        $daftarPegawai = $this->getData($sql, [$kodeLokasi,$kodeLokasi])["value"];
//        $daftarPegawai = $this->getData($sql, [$kodeLokasi,$kodeLokasi]);

        return $daftarPegawai;
    }

    public function getCurrentPns($nip) {
        parent::setConnection('db_pegawai');

        $sql = "SELECT tpeg.nipbaru, CONCAT(gelar_depan, ' ', namapeg, ' ', gelar_blkg) AS nama_lengkap, pin_absen, singkatan_lokasi, path_foto_pegawai AS foto FROM texisting_personal tperson INNER JOIN texisting_kepegawaian tpeg ON tperson.nipbaru = tpeg.nipbaru INNER JOIN tref_lokasi_kerja tlokasi ON tpeg.kdlokasi = tlokasi.kdlokasi WHERE tpeg.nipbaru = ?";
        $currentPns = $this->getData($sql, [$nip]);
        return ($currentPns['count'] > 0) ? $currentPns["value"][0] : [];
    }

    public function getKategoriModerasi() {
        parent::setConnection('db_presensi');
        $sql = "SELECT tjm.kd_jenis, tjm.nama_jenis FROM tb_jenis_moderasi tjm ORDER BY tjm.nama_jenis";
        return $this->getData($sql, [])["value"];
    }

    public function getExceptPeriodMod($param) {
        parent::setConnection('db_presensi');
        extract($param);
        $sdate = $param['tanggal_awal'];
        $edate = $param['tanggal_akhir'];
        $kdlokasi = '%' . $param['kdlokasi'] . '%';

        $idKey = [$kdlokasi, $sdate, $edate, $sdate, $edate, $sdate, $edate];
        $query = 'SELECT * FROM tb_periode_larangan_moderasi 
            WHERE 1 
                AND lokasi_dampak LIKE ?
        AND mulai_berlaku < SYSDATE() 
        AND (
                    ? BETWEEN tgl_mulai AND tgl_akhir
                    OR ? BETWEEN tgl_mulai AND tgl_akhir
                    OR tgl_mulai BETWEEN ? AND ?
                    OR tgl_akhir BETWEEN ? AND ?
        )';
        $result = $this->getData($query, $idKey);
        return $result;
    }

    public function getJenisModerasi($kodeKatMod) {
        parent::setConnection('db_presensi');
        $sql = "SELECT tjm.kd_jenis, tjm.nama_jenis, tkp.kode_presensi, tkp.ket_kode_presensi, tjm.nama_jenis FROM tb_jenis_moderasi tjm INNER JOIN tb_jenis_kode_presensi tjkp ON tjm.kd_jenis = tjkp.kd_jenis INNER JOIN tb_kode_presensi tkp ON tjkp.kode_presensi = tkp.kode_presensi WHERE tkp.moderasi_kode_presensi = 1 AND tjm.kd_jenis = ? AND tjkp.access_level = 'adminopd' ORDER BY tjm.kd_jenis, tkp.ket_kode_presensi";
        return $this->getData($sql, [$kodeKatMod])["value"];
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
        $sql = "UPDATE " . $tableName . " SET " . $signs . " WHERE " . $where;
//echo $sql; exit();
        try {
            $result = $this->db->prepare($sql);
            return $result->execute($vals);
        } catch (Exception $e) {
            echo "ERROR ON husnanWUpdate: " . $e->getMessages();
        }
    }

}
