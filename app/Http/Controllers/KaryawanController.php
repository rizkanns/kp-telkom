<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\AspekBisnis;
use App\ChatRoom;
use App\Jabatan;
use App\LatarBelakang;
use App\Mitra;
use App\Pelanggan;
use App\Proyek;
use App\User;
use App\UnitKerja;
use DB;
use Auth;
use Session;
use Telegram\Bot\Api;
use Telegram;

class KaryawanController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $proyek = DB::table('proyek')
            ->leftjoin('aspek_bisnis', 'aspek_bisnis.id_proyek', '=', 'proyek.id_proyek')
            ->leftjoin('pelanggan', 'pelanggan.id_pelanggan', '=', 'proyek.id_pelanggan')
            ->leftjoin('mitra','mitra.id_mitra','=','proyek.id_mitra')
            ->leftjoin('unit_kerja','unit_kerja.id_unit_kerja','=','proyek.id_unit_kerja')
            ->select('proyek.id_proyek', 'judul', 'saat_penggunaan', 'pemasukan_dokumen', 'ready_for_service', 'skema_bisnis', 'masa_kontrak', 'pelanggan.jenis_pelanggan', 'alamat_delivery', 'status_pengajuan', 'layanan_revenue', 'beban_mitra', 'nilai_kontrak', 'margin_tg', 'rp_margin', 'proyek.id_pelanggan', 'nama_pelanggan', 'nomor_telepon', 'alamat_pelanggan','nama_mitra','nama_unit_kerja', 'aspek_bisnis.id_aspek')
            ->get();
        $latarbelakang = DB::table('proyek')
            ->leftjoin('latar_belakang','latar_belakang.id_proyek','=','proyek.id_proyek')
            ->select('latar_belakang.id_proyek','latar_belakang')
            ->get();
        $pelanggan = DB::table('pelanggan')->get();
        return view('karyawan.dashboard', ['proyek'=>$proyek,'latarbelakang'=>$latarbelakang, 'pelanggan'=>$pelanggan]);
        // return view('AM.dashboard');
    }

    public function updateStatus(Request $request, $id_proyek)
    {
        $proyek = Proyek::find($id_proyek);
        $proyek->status_pengajuan = $request->input('status_pengajuan');
        $proyek->save();

        // dd($proyek);

        $proyek2 = DB::table('proyek')
            ->leftJoin('mitra', 'proyek.id_mitra', '=', 'mitra.id_mitra')
            ->leftJoin('aspek_bisnis', 'proyek.id_proyek', '=', 'aspek_bisnis.id_proyek')
            ->leftJoin('pelanggan', 'proyek.id_pelanggan', '=', 'pelanggan.id_pelanggan')
            ->first();

    if($proyek2->status_pengajuan == 1)
    {     
        
        $json = file_get_contents('https://api.telegram.org/bot577845467:AAGE3dmgDDvE9MIDAY3Cyd9wYQQG07xF5Nk/getUpdates');
        $obj = json_decode($json, true);
        $array = array();

        for ($i=0; $i<count($obj['result']); $i++)
        {
            print ($obj['result'][$i]['message']['chat']['id']);
            print '<br>';
            $chatid=Chatroom::where('chat_id','=', input::get('chat_id', $obj['result'][$i]['message']['chat']['id']))->first();
            if($chatid === null){
                $chatroom = new Chatroom;
                $count = Chatroom::count();
                $chatroom->id = Chatroom::count()+1;
                $chatroom->chat_id = input::get('chat_id', $obj['result'][$i]['message']['chat']['id']);
                $chatroom->save();
            }
        }

        $text = 
        "ALERT!
Ada proyek baru yang telah disetujui '".$proyek2->judul."'
Dengan rincian sebagai berikut:
        - Account Manager : ".Auth::user()->name."
        - Pelanggan : ".$proyek2->nama_pelanggan."
        - Ready for service : ".$proyek2->ready_for_service."
        - Nilai kontrak : ".$proyek2->nilai_kontrak."

        ";

        for ($i=1; $i<=Chatroom::count(); $i++)
        {
            $result = Chatroom::select('chat_id')->where('id', $i)->first();
            $response = Telegram::sendMessage([
                'chat_id' => $result->chat_id, 
                'text' => $text,
                'parse_mode' => 'HTML'
            ]);
        }
        $messageId = $response->getMessageId();

        return redirect()->route('karyawan-home');
    }
    
    else
    {
        return redirect()->route('karyawan-home');
    }

        
    }
}