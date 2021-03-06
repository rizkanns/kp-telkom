<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Proyek extends Model
{
    protected $table = 'proyek';
    protected $primaryKey = 'id_proyek';
    protected $fillable = ['id_mitra','id_users','id_pelanggan','judul','tahun','id_unit_kerja','latar_belakang_1','latar_belakang_2','saat_penggunaan','pemasukan_dokumen','ready_for_service','skema_bisnis','masa_kontrak','alamat_delivery','status_pengajuan','mekanisme_pembayaran','rincian_pembayaran','ket_problem','file','bukti_scan'];
    public $incrementing = true;
    public $timestamp = true;

    public function proyek()
    {
    	return $this->hasmany('App\Proyek', 'id_proyek', 'id_proyek');
    }

    public function mitra()
    {
    	return $this->belongsTo('App\Mitra', 'id_mitra', 'id_mitra');
    }

    public function user()
    {
    	return $this->belongsTo('App\User', 'id_users', 'id');
    }

    public function pelanggan()
    {
    	return $this->belongsTo('App\Pelanggan', 'id_pelanggan', 'id_pelanggan');
    }

    public function unit_kerja()
    {
        return $this->belongsTo('App\User', 'id_unit_kerja', 'id_unit_kerja');
    }

    public function delete()
    {
        $this->checks()->delete();
        $this->results()->delete();
        parent::delete();
    }
}
