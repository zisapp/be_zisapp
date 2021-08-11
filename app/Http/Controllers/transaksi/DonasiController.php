<?php

namespace App\Http\Controllers\transaksi;

use App\DetailDonasi;
use App\Donasi;
use App\Http\Controllers\Controller;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Symfony\Component\VarDumper\Cloner\Data;
use Barryvdh\DomPDF\Facade as PDF;
use GuzzleHttp\Psr7\FnStream;

class DonasiController extends Controller
{
    //get donasi
    public function index() //deklarasi fungsi index
    {
        $data['status'] = true; //menampilkan status
        $data['message'] = "Data Donasi"; //menampilkan pesan

        $data['data'] = DB::select("SELECT * FROM donasis LEFT JOIN banks ON donasis.id_bank = banks.id_bank
                                                            LEFT JOIN muzakis ON donasis.id_muzaki =  muzakis.id_muzakis"); //mengambil relasi donasi, bank dan muzaki
        return $data; //menampilkan data relasi yang sudah dibuat
    }

    //get donasi by id
    public function show($id) //deklarasi fungsi show get by id
    {
        $data['status'] = true; //menampilkan status
        $data['message'] = "Data Detail Donasi"; //menampilkan pesan

        $data['data'] = DB::select("SELECT * FROM detail_donasis LEFT JOIN donasis ON detail_donasis.id_donasi = donasis.id_donasi
                                                        LEFT JOIN programs ON detail_donasis.id_program = programs.id_program
                                                        LEFT JOIN muzakis ON donasis.id_muzaki = muzakis.id_muzaki
                                                        LEFT JOIN banks ON donasis.id_bank = banks.id_bank
                                                        LEFT JOIN penggunas ON penggunas.id_pengguna = donasis.id_pengguna
                                                        WHERE detail_donasis.id_donasi = '" . $id . " ");
        //perintah menampilkan enam table (relasi) -> relasi antara table donasis, table penggunas, table muzakis, table bank dan table periodes
        return $data; //menampilkan data relasi yang sudah dibuat

    }

    //create donasi
    public function create(Request $request) //pendeklarasian fungsi create
    {
        //buat id donasi berdasarkan datetime
        $date = new DateTime();
        $id_donasi = $date->getTimestamp();
        //pilih default id ketika ada kasus belum ada data sama sekali
        $next_id = "DNS-18000001"; //18 itu tahun

        $max_donasi = DB::table("donasis")->max('no_donasi'); //ambil id terbesar > DNS-18000001

        if ($max_donasi) { //jika sudah ada data genarate id baru
            # code...
            $tahun = $request->input('tahun'); //request tahun dari frontend
            $pecah_dulu = str_split($max_donasi, 8); //misal "DNS-1800001" hasilnya jadi ["DNS-1800","0001"]
            $pecah_tahun = str_split($pecah_dulu[0], 4);
            $increment_id = $pecah_dulu[1];
            $hasil_tahun = $tahun . "00";
            $result = sprintf("%'.4d", $increment_id + 1);

            $next_id = $pecah_tahun[0] . $hasil_tahun . $result;
        }

        $donasi = new Donasi; //inisalisasi atau menciptakan objek baru
        $donasi->id_donasi = $id_donasi;
        $donasi->no_donasi = $next_id; //memanggil perintah next_id yang sudah dibuat
        $donasi->no_bukti = $request->no_bukti; //menset no_bukti yang diambil dari request body
        $donasi->tgl_donasi = $request->tgl_donasi; //menset tgl_donasi yang diambil dari request body
        $donasi->total_donasi = $request->total_donasi; //menset total_donasi yang diambil dari request body
        $donasi->metode = $request->metode; //menset metode yang diambil dari request body
        $donasi->status_donasi = 1; //agar status langsung ter-create
        $donasi->id_muzaki = $request->id_muzaki; //menset id_muzaki yang diambil dari request body
        $donasi->id_bank = $request->id_bank; //menset id_bank yang diambil dari request body
        $donasi->id_pengguna = $request->id_pengguna; //menset id_pengguna yang diambil dari request body

        $simpan_donasi = $donasi->save(); //menyimpan data pengguna ke database

        if ($simpan_donasi) { //jika penyimpanan berhasil
            # code...
            $detail = $request->detail_donasi;
            $final_data = [];

            foreach ($detail as $item) {
                if ($item != null) {
                    array_push($final_data, array(
                        "id_donasi" => $id_donasi, //menset id_donasi yang diambil dari request body
                        "id_program" => $item['id_program'], //menset id_program yang diambil dari request body
                        "jumlah_donasi" => $item['jumlah_donasi'], //menset jumlah_donasi yang diambil dari request body
                        "keterangan" => $item['keterangan'], //menset keterangan yang diambil dari request body
                    ));
                }     //push data ke array

            }

            $simpan_detaildonasi = DetailDonasi::insert($final_data); //menyimpan data detai donasi ke dataabase
            if ($simpan_detaildonasi) { //jika penyimpanan berhasil
                # code...
                $data['status'] = true;
                $data['message'] = "Berhasil Menambahkan Detail Donasi";
                $data['data'] = $simpan_detaildonasi;
            } else { //jika penyimpanan gagal
                $data['status'] = false;
                $data['message'] = "Gagal Menambahkan Detail Donasi";
                $data['data'] = null;
            }
        } else { //jika penyimpanan gagal
            $data['status'] = false;
            $data['message'] = "Gagal Menambahkan Donasi";
            $data['data'] = null;
        }
        return $data; //menampilkan data yang baru disave/simpan
    }

    //update donasi (detail donasi)
    public function update($id) //deklarasi update
    {
        $donasi = Donasi::find($id); //mengambil data berdasarkan id

        if ($donasi) { //jika data ada maka data akan dieksekusi
            # code...


        }
    }

    //delete donasi
    public function delete($id) //deklarasi delete
    {
        $donasi = Donasi::find($id); //mengambil data berdasarkan id

        if ($donasi) { //mengecek apakah data donasi ada atau tidak
            # code...
            $delete_donasi = $donasi->delete(); //menghapus data donasi

            if ($delete_donasi) { //jika fungsi hapus berhasil
                # code...
                $delete_detaildonasi = DB::table('detail_donasis')->where('id_donasi', $id)->delete(); //menghapus data detail donasi
                if ($delete_detaildonasi) { //jika fungsi hapus detaildonasi berhasil
                    # code...
                    $data['status'] = true;
                    $data['message'] = "Berhasil Menghapus Detail Donasi";
                    $data['data'] = $delete_detaildonasi;
                } else { //jika fungsi hapus detaildonasi gagal
                    $data['status'] = false;
                    $data['message'] = "Gagal Menghapus Detail Donasi";
                    $data['data'] = null;
                }
            } else { //jika fungsi hapus gagal
                $data['status'] = false;
                $data['message'] = "Data Gagal diHapus";
                $data['data'] = null;
            }
        } else { //jika data tidak ada
            $data['status'] = false;
            $data['message'] = "Data Tidak Ada";
            $data['data'] = null;
        }
        return $data; //menampilkan hasil data yang dihapus (berhasil/gagal/tidak ada)
    }

    //cetak pdf
    public function cetak_pdf(Request $request)
    {

        //menampilkan data bersarkan tanggal (dari sampai)
        $donasi = DB::select(
            "SELECT * FROM detail_donasis
                    JOIN donasis
                        ON donasis.id_donasi = detail_donasis.id_donasi
                    JOIN programs
                        ON programs.id_program  = detail_donasis.id_program
                    JOIN muzakis
                        ON muzakis.id_muzaki = donasis.id_muzaki
                    WHERE donasis.created_at
                    BETWEEN '" . $request->tgl_dari . "'
                        AND '" . $request->tgl_sampai . "'"
        );

        //perintah cetak pdf
        $pdf = PDF::loadview('laporan_donasi', ['donasi' => $donasi])->setPaper('A4', 'potrait');
        return $pdf->stream();
    }

    //cetak tanda bukti
    public function cetak_tanda(Request $request)
    {
        //menampilkan hasil donasi
        $donasi = DB::select(
            "SELECT * FROM detail_donasis
                    JOIN donasis
                        ON donasis.id_donasi = detail_donasis.id_donasi
                    JOIN programs
                        ON programs.id_program  = detail_donasis.id_program
                    JOIN muzakis
                        ON muzakis.id_muzaki = donasis.id_muzaki"
        );

        //perintah cetak pdf
        $pdf = PDF::loadview('tandaterima', ['donasi' => $donasi])->setPaper('A4', 'potrait');
        return $pdf->stream();
    }
}
