<?php
    $apiUrl = "http://192.168.194.50:3000/imporbcas";
    $apiUrlPerm = "http://192.168.194.50:3000/permissions";

    session_start();
    if (!isset($_SESSION['username'])) {
        header("Location: ?page=login");
       exit();
    }

    function DateYmd($date) { // fungsi atau method untuk mengubah tanggal ke format indonesia
       // variabel BulanIndo merupakan variabel array yang menyimpan nama-nama bulan
         
        $tahun = substr($date, 0, 4); // memisahkan format tahun menggunakan substring
        $bulan = substr($date, 5, 2); // memisahkan format bulan menggunakan substring
        $tgl   = substr($date, 8, 2); // memisahkan format tanggal menggunakan substring
         
        $result=$tahun . "-" . $bulan . "-" . $tgl;
        return($result);
    }

    // Cek user permission untuk menu impor bca
    $useraktif=$_SESSION['username'];
    $menuname='form_imporbca';
    $urlPerm=$apiUrlPerm.'?user='.$useraktif.'&menu='.$menuname;
    $optPerm=array(
        'http'=>array(
            'method'=>'GET',
        ),
    );
    $contPerm=stream_context_create($optPerm);
    $permissions = json_decode(file_get_contents($urlPerm, false, $contPerm));
    
    if (count($permissions) > 0) {
        foreach ($permissions as $permission) {

            // User permission untuk membuka form ini
            if ($permission->is_open == 0) {
                echo "<script type='text/javascript'>";
                echo "alert('Anda tidak memiliki hak untuk membuka form ini!');";
                echo "window.location.href = '?page=home';";
                echo "</script>";
            } else {
                if ($_SERVER['REQUEST_METHOD'] == 'GET' && !isset($_GET['delete'])) {
                    $searchQuery = '';
                    $url=$apiUrl . '?q=' . urlencode($searchQuery);
                    $options = array(
                        'http' => array(
                            'method'  => 'GET',
                        ),
                    );
                    $context  = stream_context_create($options);
                    $imporbcas = json_decode(file_get_contents($url, false, $context));
                }
            }

            // User permission untuk addnew form ini
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['import'])) {

                if ($permission->is_addnew == 0) {
                    echo "<script type='text/javascript'>";
                    echo "alert('Anda tidak memiliki hak untuk menambahkan data!');";
                    echo "window.location.href = '?page=imporbca';";
                    echo "</script>";
                    exit();
                }

                // Ambil file CSV yang diunggah
                $file = $_FILES['csv_file']['tmp_name'];
                $fileName = $_FILES['csv_file']['name'];
                $tgltransaksi = $_POST['tgl_transaksi'];

                // Cek apakah file ada
                if ($file) {
                    // Inisialisasi cURL
                    $curl = curl_init();

                    // Konfigurasi cURL untuk mengirim file ke Node.js API
                    curl_setopt_array($curl, [
                        CURLOPT_URL => $apiUrl ,  // Ganti URL dengan API Node.js-mu
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_POST => true,
                        CURLOPT_POSTFIELDS => [
                            'file' => new CURLFile($file, 'text/csv', $fileName),
                            "tgl_transaksi" => $tgltransaksi,
                        ],
                    ]);

                    // Kirim permintaan cURL dan terima respon
                    $response = curl_exec($curl);
                    curl_close($curl);

                    // Tampilkan hasilnya
                    //echo $response;
                } else {
                    echo "Gagal mengunggah file.";
                }

                //$context = stream_context_create($options);
                //$imporbcas=json_decode(file_get_contents($apiUrl, false, $context));
                header("Location: ?page=imporbca");
            }

            // User permission untuk update form ini
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['posting'])) {

                if ($permission->is_update == 0) {
                    echo "<script type='text/javascript'>";
                    echo "alert('Anda tidak memiliki hak untuk mengupdate data!');";
                    echo "window.location.href = '?page=imporbca';";
                    echo "</script>";
                    exit();
                }

                // Handle Update
                $data = array(
                    "kd_metode_bayar" => $_POST['kd_metode_bayar'],
                    "user_created" => $useraktif
                );
                $options = array(
                    'http' => array(
                        'header'  => "Content-Type: application/json\r\n",
                        'method'  => 'PUT',
                        'content' => json_encode($data)
                    )
                );
                $context  = stream_context_create($options);
                $imporbcas = json_decode(file_get_contents($apiUrl, false, $context));
                header("Location: ?page=bayar");
            }

            // User permission untuk delete form ini
            if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['delete'])) {

                if ($permission->is_delete == 0) {
                    echo "<script type='text/javascript'>";
                    echo "alert('Anda tidak memiliki hak untuk menghapus data!');";
                    echo "window.location.href = '?page=imporbca';";
                    echo "</script>";
                    exit();
                }

                // Handle Delete
                $id = $_GET['delete'];
                $options = array(
                    'http' => array(
                        'method'  => 'DELETE',
                    ),
                );
                $context = stream_context_create($options);
                $imporbcas = json_decode(file_get_contents($apiUrl . '/' . $id, false, $context));
                header("Location: ?page=imporbca");
            }
        }
    }

    // pencarian data impor bca
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['search'])) {
        $searchQuery = $_POST['cari'];
        $url=$apiUrl . '?q=' . urlencode($searchQuery);
        $options = array(
            'http' => array(
                'method'  => 'GET',
            ),
        );
        $context  = stream_context_create($options);
        $imporbcas = json_decode(file_get_contents($url, false, $context));
    }

    // Data metode pembayaran
    $apiUrlMetodepay= "http://localhost:3000/metodepays";
    $urlMetodepay=$apiUrlMetodepay.'?q=';
    $optMetodepay=array(
        'http'=>array(
            'method'=>'GET',
        ),
    );
    $contMetodepay=stream_context_create($optMetodepay);
    $Metodepays = json_decode(file_get_contents($urlMetodepay, false, $contMetodepay));

    $i=1;
?>

<style>
    .form-select {
            font-family: Poppins, sans-serif; /* Atur font sesuai keinginan */
            font-size: 12px; /* Ukuran font */
        }


    .styled-table thead tr th:nth-child(1),
    .styled-table thead tr th:nth-child(2),
    .styled-table thead tr th:nth-child(3) {
        position: sticky;
        left: 0; /* posisi kolom tetap di kiri */
        background-color: #4b2e83;  /*latar belakang supaya tidak tertimpa elemen lainnya */
        z-index: 1; /* agar berada di atas elemen lain */
        box-shadow: 2px 0px 5px rgba(0,0,0,0.1); /* bayangan kecil untuk memisahkan */
    }
    .styled-table thead tr th:nth-child(2) {
        left: 35px; /* geser sesuai dengan lebar kolom pertama */
    }

    .styled-table thead tr th:nth-child(3) {
        left: 35px; /* geser sesuai dengan lebar kolom pertama dan kedua */
    }
    .styled-table tbody tr td:nth-child(1),
    .styled-table tbody tr td:nth-child(2),
    .styled-table tbody tr td:nth-child(3) {
        position: sticky;
        left: 0; /* posisi kolom tetap di kiri */
        background-color: white;  /*latar belakang supaya tidak tertimpa elemen lainnya */
        z-index: 1; /* agar berada di atas elemen lain */
        box-shadow: 2px 0px 5px rgba(0,0,0,0.1); /* bayangan kecil untuk memisahkan */
    }

    .styled-table tbody tr td:nth-child(2) {
        left: 35px; /* geser sesuai dengan lebar kolom pertama */
    }

    .styled-table tbody tr td:nth-child(3) {
        left: 35px; /* geser sesuai dengan lebar kolom pertama dan kedua */
    }
    .form-select {
            font-family: Poppins, sans-serif; /* Atur font sesuai keinginan */
            font-size: 12px; /* Ukuran font */
        }

    #foto_bon:focus + label {
        color: #495057;
    }

    .btn-primary {
        background-color: #007bff;
        border-color: #007bff;
        /*padding: 10px 20px;*/
        font-size: 12px;
        border-radius: 5px;
    }

    .btn-primary:hover {
        background-color: #0056b3;
        border-color: #004085;
    }
</style>
<section id="imporbca">
    <div class="header fixed-top">
        <div class="left">
            <a href="?page=home"><img src="assets/images/arrow-left.svg" alt="Back"></a>
        </div>
        <div class="center">
            <judul>Impor BCA</judul>
        </div>
        <div class="right">
            <a href="" data-toggle="modal" data-target="#NewModal"><img src="assets/images/plus-lg.svg" alt="Tambah"></a>
            <a href="" data-toggle="modal" data-target="#CariModal"><img src="assets/images/search.svg" alt="Cari"></a>
            <a href="?page=imporbca"><img src="assets/images/arrow-clockwise.svg" alt="Refresh"></a>
            <a href="?page=imporbca&delete="><img src="assets/images/trash3.svg" alt="Delete All"></a>
            <a href="" data-toggle="modal" data-target="#PostingModal"><img src="assets/images/box-arrow-in-down.svg" alt="Posting"></a>
        </div>
    </div>
    <div class="container-t45">
    </div>
    <div class="table-container">
        <table class="styled-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tanggl Beli</th>
                    <th>No Beli</th>
                    <th style="text-align:right;">Total Beli</th>
                    <th>Tanggal BCA</th>
                    <th>Keterangan</th>
                    <th style="text-align:right;">Nilai Bayar</th>
                    <th style="text-align:right;">Sisa Hutang</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($imporbcas as $imporbca): ?>
                <tr>
                    <td label="#"><?php echo $i++; ?></td>
                    <td label="Tanggal Beli">
                        <?php 
                            $dt_tglbeli= new DateTime($imporbca->tgl_beli);
                            $dt_tglbeli->setTimezone(new DateTimeZone('Asia/Jakarta'));
                            echo $dt_tglbeli->format('Y-m-d'); 
                        ?>
                    </td>
                    <td label="No Beli"><?php echo $imporbca->no_beli; ?></td>
                    <td label="Total Beli" style="text-align:right;"><?php echo number_format($imporbca->total_beli,2); ?></td>
                    <td label="Tgl Transaksi">
                        <?php 
                            $dt_tgltransaksi= new DateTime($imporbca->tgl_bayar);
                            $dt_tgltransaksi->setTimezone(new DateTimeZone('Asia/Jakarta'));
                            echo $dt_tgltransaksi->format('Y-m-d');
                        ?> 
                    </td>
                    <td label="Keterangan"><?php echo $imporbca->keterangan; ?></td>
                    <td label="Nilai Bayar" style="text-align:right;"><?php echo number_format($imporbca->nilai_bayar,2); ?></td>
                    
                    <td label="Sisa Hutang" style="text-align:right;"><?php echo number_format($imporbca->sisa_hutang,2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div style="height:100px;">
    </div>

    <!-- The Addnew Modal -->
    <div id="NewModal" class="modal fade" role="dialog" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" style="display:inline-block;" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h6 class="modal-title" id="addModalLabel">Import BCA (CSV)</h6>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-floating mt-1 mb-2">
                            <input type="date" class="form-control" id="tgl_transaksi" name="tgl_transaksi" placeholder="Tanggal BCA" required>
                            <label for="tgl_transaksi">Tanggal BCA</label>
                        </div>
                        <div class="form-floating mt-1 mb-1">
                            <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv" placeholder="File BCA (CSV)">
                            <label for="csv_file">File BCA (CSV)</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                        <button type="submit" name="import" class="btn btn-primary btn-sm">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- The Posting Modal -->
    <div id="PostingModal" class="modal fade" role="dialog" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" style="display:inline-block;" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h6 class="modal-title" id="addModalLabel">Posting Impor BCA</h6>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" class="form-control" id="useraktif" name="useraktif" value="<?php echo $useraktif; ?>" required>
                        <div class="form-floating mt-1 mb-1">
                            <select type="text" class="form-select" id="kd_metode_bayar" name="kd_metode_bayar" placeholder="Metode Pembayaran" required>
                                <option value='' selected>Pilih Metode</option>
                                <?php foreach ($Metodepays as $metodepay) { ?>
                                    <option value="<?php echo $metodepay->kd_metode_bayar; ?>"><?php echo $metodepay->nm_metode_bayar; ?></option>
                                <?php } ?>
                            </select>
                            <label for="kd_metode_bayar">Metode Pembayaran</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                        <button type="submit" name="posting" class="btn btn-primary btn-sm">Posting</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- The Cari Modal -->
    <div id="CariModal" class="modal fade" role="dialog" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" style="display:inline-block;" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h6 class="modal-title" id="addModalLabel">Cari Satuan</h6>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-floating mt-1 mb-1">
                            <input type="text" class="form-control" id="cari" name="cari" placeholder="Kata pencarian" required>
                            <label for="cari">Masukan kata yang ingin dicari...</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                        <button type="submit" name="search" class="btn btn-primary btn-sm">Cari</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</section>
