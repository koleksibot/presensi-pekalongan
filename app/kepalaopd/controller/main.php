<?php

namespace app\kepalaopd\controller;

use app\kepalaopd\model\servicemain;
use app\kepalaopd\model\servicemasterpresensi;
use app\kepalaopd\model\HusnanWModerasiModel; // added by husnanw
use app\kepalaopd\model\pegawai_service;
use system;

class main extends system\Controller {

    public function __construct() {
        parent::__construct();
        $this->servicemain = new servicemain();
        $this->servicemaster = new servicemasterpresensi();
        $this->husnanWModel = new HusnanWModerasiModel(); // added by husnanw
        $this->pegawai_service = new pegawai_service();
        $session = $this->servicemain->cekSession();
        if ($session['status'] === true) {
            $this->setSession('SESSION_LOGIN', $session['data']);
            $this->login = $this->getSession('SESSION_LOGIN');
        } else {
            $this->setSession('SESSION_RELOAD', true);
            $this->redirect($this->link('kepalaopd/login'));
        }
    }

    protected function index() {
        $data['title'] = 'BERANDA';
        $data['subtitle'] = '';
        $data['breadcrumb'] = '';
        $data['teks'] = $this->servicemaster->getTeksBeranda();
        $this->showView('index', $data, 'theme_admin');
    }

    protected function loading() {
        $data['title'] = '<!-- Loading -->';
        $this->subView('loading', $data);
    }
    
    protected function header() {
        $data['title'] = '<!-- Header -->';
        $data['user'] = 'Admin OPD';
        $data['status'] = 'Web Developer';
        $data['since'] = 'Member since Nov. 2012';
        $data['link_logout'] = $this->link('kepalaopd/logout/');
        $this->subView('header', $data);
    }
    
    protected function menu() {
        $data['title'] = '<!-- Menu -->';
        $data['user'] = 'Admin OPD';
        $data['subtitle'] = 'MENU UTAMA';
        $data['link_beranda'] = $this->link('kepalaopd/');
        
        $data['link_manketidakhadiran'] = $this->link('kepalaopd/modmanketidakhadiran/');

         // -- start husnanw moderasi menu -- //
        $data['link_daftar_ver_mod'] = $this->link('kepalaopd/moderasi/daftarVerMod');
        // -- end husnanw moderasi menu -- //
        
        // MENU PENGATURAN
        $data['link_pengaturan_profil'] = $this->link('kepalaopd/pengaturanprofil/');
        
        // MENU PANDUAN
        $data['link_panduan'] = $this->link('kepalaopd/panduan/');

        $data["selfId"] = $this->husnanWModel->getCurrentPns($this->login["nipbaru"]); // added by husnanw
        /*--- BEGIN - added by daniek ---*/ 
        $lokasi = $this->pegawai_service->getDataSatker($this->login["kdlokasi"]);
        $data["selfId"]["singkatan_lokasi"] = $lokasi["singkatan_lokasi"];
        $jabatan = $this->login["jabatan_pengguna"] ? $this->login["jabatan_pengguna"] : '';
        $data["selfId"]["jabatan_pengguna"] = $jabatan;
        /*--- END - added by daniek ---*/

        $data['link_apelpagi'] = $this->link('kepalaopd/apelpagi/');
        $data['link_batalapelpagi'] = $this->link('kepalaopd/batalapelpagi/');

        $data['link_laporan'] = $this->link('kepalaopd/laporan/');
        $data['link_laporan_final'] = $this->link('kepalaopd/laporan/laporanfinal');

        $data['link_logout'] = $this->link('kepalaopd/logout/');
        $this->subView('menu', $data);
    }
    
    protected function modalInput() {
        $data['title'] = '<!-- Modal -->';
        $this->subView('modalInput', $data);
    }
    
    protected function footer() {
        $data['title'] = '<!-- Footer -->';
        $this->subView('footer', $data);
    }
    
    public function script() {
        $data['title'] = '<!-- Script -->';
        $this->subView('script', $data);
    }

}

?>
