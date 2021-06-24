<?php

namespace App\Http\Controllers\transaksi;

use App\Http\Controllers\Controller;
use App\Pengajuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PengajuanController extends Controller
{
    //get pengajuan
    public function index() //deklarasi fungsi index
    {
        $data['status'] = true; //menampilkan status
        $data['message'] = "Data Pengajuan"; //menampilkan pesan
        $data['data'] = DB::select("SELECT *FROM pengajuans LEFT JOIN mustahiks ON pengajuans.id_mustahiks = mustahiks.id_mustahiks"); //menampilkan relasi table antara table pengajuans dan table mustahiks
        return $data; //menampilkan data relasi yang sudah dibuat
    }

    //create pengajjuan
    public function create(Request $request) //deklarasi fungsi create
    {
        //pilih default id ketika ada kasus belum ada data sama sekali
        $next_id = "PJN-1800001";

        $max_pengguna = DB::table("pengajuans")->max('no_pengajuan'); //ambil id terbesar -> PJN-18000001 

        if ($max_pengguna) { //jika sudah ada data generate id baru
            # code...
            $pecah_dulu = str_split($max_pengguna, 7); //misal "PJN-1800001" hasilnya jadi ["PJN-1800", "001"]
            $increment_id = $pecah_dulu[1];
            $result = sprintf("%'.03d", $increment_id + 1);

            $next_id = $pecah_dulu[0] . $result;
        }

        $pengajuan = new Pengajuan; //insialisasi objek
        $pengajuan->no_pengajuan = $next_id; //memanggil perintah next_id yang tadi telah dibuat
        $pengajuan->id_mustahik = $request->id_mustahik; //menset id_mustahik yang diambil dari request body
        $pengajuan->pengajuan_kegiatan = $request->pengajuan_kegiatan; //menset pengajuan_kegiatan yang diambil dari request body
        $pengajuan->jumlah_pengajuan = $request->jumlah_pengajuan; //menset jumlah_pengajuan yang diambil dari request body
        $pengajuan->jenis_pengajuan = $request->jenis_pengajuan; //menset jenis_pengajuan yang diambil dari request body
        $pengajuan->asnaf = $request->asnaf; //menset asnaf yang diambil dari request body
        $pengajuan->status_pengajuan = 1; //menset status agar otomastis tercreate

        $simpan = $pengajuan->save(); //menyimpan data pengajuan ke databse
        if ($simpan) { //jika penyimpanan berhasil
            # code...
            $data['status'] = true;
            $data['mesaage'] = "Berhasil Menambahkan Pengajuan";
            $data['data'] = $pengajuan;
        } else { //jika penyimpanan gagal
            $data['status'] = false;
            $data['message'] = "Gagal Menambahkan Pengajuan";
            $data['data'] = null;
        }
        return $data;  //menampilka data yang baru disave/simpan
    }

    //delete pengajuan
    public function delete($id) //deklarasi delete
    {
        $pengajuan =  Pengajuan::find($id); //mengambil data berdasarkan id

        if ($pengajuan) { //mengecek apakah data pengajuan ada atau tidak
            # code...
            $delete = $pengajuan->delete(); //menghapus data pengajuan

            if ($delete) {  //jika fungsi hapus berhasil
                # code...
                $data['status'] = true;
                $data['message'] = "Data Berhasil diHapus";
                $data['data'] = $pengajuan;
            } else { //jikak fungsi hapus gagal
                $data['status'] = false;
                $data['message'] = "Data Gagal diHapus";
                $data['data'] = null;
            }
        } else { //jika data tidak ada
            $data['status'] = false;
            $data['message'] = "Data Tidak Ada";
            $data['data'] = null;
        }
        return $data; //menampilkan hasil data yang dihapus (berhasil/gagal/tidak ada data)
    }
}