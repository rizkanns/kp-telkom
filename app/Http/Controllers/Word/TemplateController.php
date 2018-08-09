<?php

namespace App\Http\Controllers\Word;

use Illuminate\Http\Request;
use PhpOffice\PhpWord\Template;
use PhpOffice\PhpWord\Settings;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\AspekBisnis;
use App\Jabatan;
use App\LatarBelakang;
use App\Mitra;
use App\Pelanggan;
use App\Proyek;
use App\User;
use App\UnitKerja;
use DB;

class TemplateController extends Controller
{

    public function createWordDocxP0(){
        $templateProcessor = new Template('template/template_p0.docx');
        $templateProcessor->setValue('rowValue#1', 'Sun');

        $objectWriter = \PhpOffice\PhpWord\IOFactory::createWriter($wordTest, 'Word2007');
        try{
            $objectWriter->save(storage_path('TestWordFile.docx'));
        }
        catch (Exception $e){

        }
        return response()->download(storage_path('TestWordFile.docx'));
    }

    public function createWordDocxP1(Request $request, $id){
        $settings = new Settings();
        $settings->setOutputEscapingEnabled(true);

        $proyek = DB::table('proyek')
            ->leftJoin('unit_kerja', 'proyek.id_unit_kerja', '=', 'unit_kerja.id_unit_kerja')
            ->leftJoin('pelanggan', 'proyek.id_pelanggan', '=', 'pelanggan.id_pelanggan')
            ->leftJoin('mitra', 'proyek.id_mitra', '=', 'mitra.id_mitra')
            ->leftJoin('aspek_bisnis', 'proyek.id_proyek', '=', 'aspek_bisnis.id_proyek')
            ->where('proyek.id_proyek','=',$id)
            ->first();

        // $latarbelakang = DB::table('LatarBelakang')->where('id_proyek','=',$id)->first();
        // $pelanggan = DB::table('')->where('','=',$id)->first();
        // $unit_kerja = DB::table('proyek')
        //     ->leftJoin('unit_kerja', 'proyek.id_unit_kerja', '=', 'unit_kerja.id_unit_kerja')
        //     // ->where('proyek.id_unit_kerja', 'unit_kerja.id_unit_kerja')
        //     ->first();
        
        // $pelanggan = DB::table('')->where('','=',$id)->first();
        // $mitra = DB::table('')->where('','=',$id)->first();
        // $aspekbisnis = DB::table('AspekBisnis')->where('id_proyek','=',$id)->first();
        
        $templateProcessor = new Template('template/template_p1_v2.docx');
        $templateProcessor->setValue('jenisPelanggan', strtoupper($proyek->jenis_pelanggan));
        $templateProcessor->setValue('judul', $proyek->judul);
        $templateProcessor->setValue('tahun', Carbon::createFromFormat('Y-m-d H:i:s', Carbon::now())->format('Y'));
        $templateProcessor->setValue('unitKerja', $proyek->nama_unit_kerja);
        $templateProcessor->setValue('bebanMitra', number_format($proyek->beban_mitra));
        setlocale(LC_TIME, 'Indonesian');
        Carbon::setUtf8(true);
        $templateProcessor->setValue('saatPenggunaan', Carbon::createFromFormat('Y-m-d', $proyek->saat_penggunaan)->format('M Y'));
        setlocale(LC_TIME, '');

        // A. LATAR BELAKANG

        // foreach ($latarbelakang as $lb) {
        //     $i++;
        //     $templateProcessor->setValue('lb'.$i, $lb->latar_belakang);
        // }
        $templateProcessor->setValue('lb1', $proyek->latar_belakang_1);
        $templateProcessor->setValue('lb2', $proyek->latar_belakang_2);

        $templateProcessor->setValue('pelanggan', $proyek->nama_pelanggan);

        // B. LINGKUP PEKERJAAN
        $templateProcessor->setValue('namaMitra', $proyek->nama_mitra);

        // D. WAKTU PENGGUNAAN
        setlocale(LC_TIME, 'Indonesian');
        Carbon::setUtf8(true);
        $templateProcessor->setValue('readyForService', Carbon::createFromFormat('Y-m-d', $proyek->ready_for_service)->formatLocalized('%B %Y'));
        setlocale(LC_TIME, '');

        // E. LOKASI INSTALASI / LAYANAN
        $templateProcessor->setValue('alamatDelivery', $proyek->alamat_delivery);

        // F. SKEMA BISNIS LAYANAN

        // Sewa Murni / Sewa Beli / Pengadaan Beli Putus (ada masa garansi)
        //  ̶S̶e̶w̶a̶ ̶M̶u̶r̶n̶i̶   ̶S̶e̶w̶a̶ ̶B̶e̶l̶i̶   ̶P̶e̶n̶g̶a̶d̶a̶a̶n̶ ̶B̶e̶l̶i̶ ̶P̶u̶t̶u̶s̶ ̶(̶a̶d̶a̶ ̶m̶a̶s̶a̶ ̶g̶a̶r̶a̶n̶s̶i̶)̶  ̶ ̶/̶ 

        if($proyek->skema_bisnis == 'Sewa Murni'){
            $templateProcessor->setValue('skema', 'Sewa Murni ̶/̶ ̶S̶e̶w̶a̶ ̶B̶e̶l̶i̶ ̶/̶ ̶̶P̶e̶n̶g̶a̶d̶a̶a̶n̶ ̶B̶e̶l̶i̶ ̶P̶u̶t̶u̶s̶ ̶(̶a̶d̶a̶ ̶m̶a̶s̶a̶ ̶g̶a̶r̶a̶n̶s̶i̶)̶');
        }
        elseif($proyek->skema_bisnis == 'Sewa Beli'){
            $templateProcessor->setValue('skema', '̶S̶e̶w̶a̶ ̶M̶u̶r̶n̶i̶ ̶/ Sewa Beli ̶/̶ ̶P̶e̶n̶g̶a̶d̶a̶a̶n̶ ̶B̶e̶l̶i̶ ̶P̶u̶t̶u̶s̶ ̶(̶a̶d̶a̶ ̶m̶a̶s̶a̶ ̶g̶a̶r̶a̶n̶s̶i̶)̶');
        }
        else{
            $templateProcessor->setValue('skema', '̶S̶e̶w̶a̶ ̶M̶u̶r̶n̶i̶ ̶/̶ ̶S̶e̶w̶a̶ ̶B̶e̶l̶i̶ ̶/ Pengadaan Beli Putus (ada masa garansi)');
        }
        
        // G. ASPEK BISNIS
        if($proyek->layanan_revenue == 'Bulanan'){
            $templateProcessor->setValue('layanan', 'Bulanan ̶/̶ ̶T̶a̶h̶u̶n̶a̶n̶ ̶/̶ ̶O̶T̶C̶');
        }
        elseif ($proyek->layanan_revenue == 'Tahunan') {
            $templateProcessor->setValue('layanan', '̶B̶u̶l̶a̶n̶a̶n̶ ̶/ Tahunan ̶/̶ ̶O̶T̶C̶');
        }
        else{
            $templateProcessor->setValue('layanan', '̶B̶u̶l̶a̶n̶a̶n̶ ̶/̶ ̶T̶a̶h̶u̶n̶a̶n̶ ̶/ OTC');
        }
        $templateProcessor->setValue('nilaiKontrak', number_format($proyek->nilai_kontrak));
        $templateProcessor->setValue('marginTg', $proyek->margin_tg);
        $templateProcessor->setValue('rpMargin', number_format($proyek->rp_margin));

        // H. USULAN MEKANISME PEMBAYARAN PADA MITRA
        $templateProcessor->setValue('mekanismePembayaran', $proyek->mekanisme_pembayaran);
        $strikethrough = implode('̶', str_split(strtoupper($proyek->jenis_pelanggan)));
        if($proyek->mekanisme_pembayaran == 'Sebelum'){
            $templateProcessor->setValue('rincianPembayaran1', 'Dilakukan dengan menunggu pembayaran dari Pelanggan '.strtoupper($proyek->jenis_pelanggan));
            $templateProcessor->setValue('rincianPembayaran2', ' ̶D̶i̶l̶a̶k̶u̶k̶a̶n̶ ̶s̶e̶t̶e̶l̶a̶h̶ ̶T̶E̶L̶K̶O̶M̶ ̶m̶e̶n̶e̶r̶i̶m̶a̶ ̶p̶e̶m̶b̶a̶y̶a̶r̶a̶n̶ ̶d̶a̶r̶i̶ ̶p̶e̶l̶a̶n̶g̶g̶a̶n̶ ̶'.$strikethrough);
        }
        else{
            $templateProcessor->setValue('rincianPembayaran1', ' ̶D̶i̶l̶a̶k̶u̶k̶a̶n̶ ̶d̶e̶n̶g̶a̶n̶ ̶m̶e̶n̶u̶n̶g̶g̶u̶ ̶p̶e̶m̶b̶a̶y̶a̶r̶a̶n̶ ̶d̶a̶r̶i̶ ̶P̶e̶l̶a̶n̶g̶g̶a̶n̶ ̶'.$strikethrough);
            $templateProcessor->setValue('rincianPembayaran2', 'Dilakukan setelah TELKOM menerima pembayaran dari pelanggan '.strtoupper($proyek->jenis_pelanggan));
        }
        
        // I. MASA KONTRAK LAYANAN
        $templateProcessor->setValue('masaKontrak', $proyek->masa_kontrak);

        // J. JADWAL PEMASUKAN DOKUMEN
        setlocale(LC_TIME, 'Indonesian');
        Carbon::setUtf8(true);
        $templateProcessor->setValue('pemasukanDokumen', Carbon::createFromFormat('Y-m-d', $proyek->pemasukan_dokumen)->formatLocalized('%B %Y'));
        setlocale(LC_TIME, '');

        list($width, $height) = getimagesize(public_path('images/'. $proyek->file));
        // $templateProcessor->setValue('file', asset('images/'. $proyek->file));
        // $templateProcessor->setImageValue('image1.png', public_path('images/'. $proyek->file));
        $templateProcessor->setImg('selector',array('src' => public_path('images/'. $proyek->file),'swh'=>'200', 'size'=>array(0=>$width, 1=>$height)));

        // K. INFORMASI TAMBAHAN
        $templateProcessor->setValue('am', 'MUNARTI');
        $templateProcessor->setValue('nikAm', '720336');
        $templateProcessor->setValue('jabatanAm', 'ACCOUNT MANAGER');
        $templateProcessor->setValue('se', 'I PUTU AGUS PICASTANA');
        $templateProcessor->setValue('nikSe', '870036');
        $templateProcessor->setValue('jabatanSe', 'ASMAN GES SALES ENGINEER');
        $templateProcessor->setValue('bidding', 'DETRI YUSZIANI');
        $templateProcessor->setValue('nikBidding', '640579');
        $templateProcessor->setValue('jabatanBidding', 'ASMAN GES OBL & BIDDING MANAGEMENT');
        $templateProcessor->setValue('manager', 'YUSUF HARYANTO');
        $templateProcessor->setValue('nikManager', '740290');
        $templateProcessor->setValue('jabatanManager', 'MGR GOVERNMENT & ENTERPRISE SERVICE');
        $templateProcessor->setValue('deputy', 'MEYLA KUSUMADIARTI RR,ST');
        $templateProcessor->setValue('nikDeputy', '720205');
        $templateProcessor->setValue('jabatanDeputy', 'DEPUTY GM WITEL SURABAYA');
        $templateProcessor->setValue('gm', 'MUHAMMAD NASRUN IHSAN');
        $templateProcessor->setValue('nikGm', '720099');
        $templateProcessor->setValue('jabatanGm', 'GM WITEL SURABAYA');

        // $objectWriter = \PhpOffice\PhpWord\IOFactory::createWriter($wordTest, 'Word2007');
        try{
            $templateProcessor->saveAs('results/P1 - '.$proyek->judul.'.docx');
            // $objectWriter->save(storage_path('P1 - Pekerjaan Penyediaan CPE Managed Services untuk Layanan Astinet, Indihome dan Wifi Station untuk RSUD Dr Soetomo.docx'));
        }
        catch (Exception $e){

        }
        return response()->download('results/P1 - '.$proyek->judul.'.docx');
    }
}