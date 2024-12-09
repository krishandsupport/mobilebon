<?php
    $apiUrl = "http://192.168.194.50:3000/bayars";
    $apiUrlPerm = "http://192.168.194.50:3000/permissions";
    
    function DateYmd($date) { // fungsi atau method untuk mengubah tanggal ke format indonesia
       // variabel BulanIndo merupakan variabel array yang menyimpan nama-nama bulan
         
        $tahun = substr($date, 0, 4); // memisahkan format tahun menggunakan substring
        $bulan = substr($date, 5, 2); // memisahkan format bulan menggunakan substring
        $tgl   = substr($date, 8, 2); // memisahkan format tanggal menggunakan substring
         
        $result=$tahun . "-" . $bulan . "-" . $tgl;
        return($result);
    }

    function isMobile() {
        return preg_match('/(android|iphone|ipad|ipod|blackberry|opera mini|iemobile|mobile)/i', $_SERVER['HTTP_USER_AGENT']);
    }

    if (isMobile()) {
        $sendWA="https://api.whatsapp.com/send?phone="; //https://wa.me/ atau https://api.whatsapp.com/send?phone=
        $isText="?text=";
    } else {
        $sendWA="https://wa.me/"; //whatsapp://send?phone=
        $isText="?text=";
    }

    session_start();
    if (!isset($_SESSION['username'])) {
        header("Location: ?page=login");
       exit();
    }

    // Cek user permission untuk menu beli
    $useraktif=$_SESSION['username'];
    $menuname='form_bayar';
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
                    $searchQuery = $_GET['nobayar'];
                    $url=$apiUrl . '?q=' . urlencode($searchQuery);
                    $options = array(
                        'http' => array(
                            'method'  => 'GET',
                        ),
                    );
                    $context  = stream_context_create($options);
                    $bayars = json_decode(file_get_contents($url, false, $context));
                }
            }

            // User permission untuk addnew form ini
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['insert'])) {

                if ($permission->is_addnew == 0) {
                    echo "<script type='text/javascript'>";
                    echo "alert('Anda tidak memiliki hak untuk menambahkan data!');";
                    echo "window.location.href = '?page=bayar';";
                    echo "</script>";
                    exit();
                }

                $targetDir = "assets/images/bayar/";
                $fileName = basename($_FILES["foto_bayar"]["name"]);
                $targetFilePath = $targetDir . $fileName;
                $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

                $allowTypes = array('jpg', 'png', 'jpeg', 'gif');
                if (in_array($fileType, $allowTypes)) {
                    // Allow certain file formats
                    if (move_uploaded_file($_FILES["foto_bayar"]["tmp_name"], $targetFilePath)) {

                        // Handle Create
                        $data = array(
                            "no_bayar" => $_POST['no_bayar'],
                            "tgl_bayar" => $_POST['tgl_bayar'],
                            "kd_supplier" => $_POST['kd_supplier'],
                            "ket_bayar" => $_POST['ket_bayar'],
                            "kd_metode_bayar" => $_POST['kd_metode_bayar'],
                            "total_bayar" => $_POST['total_bayar'],
                            "foto_bayar" => $fileName,
                            "user_created" => $_POST['user_created'],
                            "date_created" => $_POST['date_created'],
                            "useraktif" => $useraktif
                        );
                        $options = array(
                            'http' => array(
                                'header'  => "Content-type: application/json\r\n",
                                'method'  => 'POST',
                                'content' => json_encode($data),
                            ),
                        );
                        $context = stream_context_create($options);
                        $bayars=json_decode(file_get_contents($apiUrl, false, $context));
                        header("Location: ?page=bayar");
                    }
                } else {
                    // Handle Create
                    $data = array(
                        "no_bayar" => $_POST['no_bayar'],
                        "tgl_bayar" => $_POST['tgl_bayar'],
                        "kd_supplier" => $_POST['kd_supplier'],
                        "ket_bayar" => $_POST['ket_bayar'],
                        "kd_metode_bayar" => $_POST['kd_metode_bayar'],
                        "total_bayar" => $_POST['total_bayar'],
                        "foto_bayar" => $_POST['foto_bayar'],
                        "user_created" => $_POST['user_created'],
                        "date_created" => $_POST['date_created'],
                        "useraktif" => $useraktif
                    );
                    $options = array(
                        'http' => array(
                            'header'  => "Content-type: application/json\r\n",
                            'method'  => 'POST',
                            'content' => json_encode($data),
                        ),
                    );
                    $context = stream_context_create($options);
                    $bayars=json_decode(file_get_contents($apiUrl, false, $context));
                    header("Location: ?page=bayar&nobayar=".$_POST['no_bayar']);
                }
            }

            // User permission untuk update form ini
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {

                if ($permission->is_update == 0) {
                    echo "<script type='text/javascript'>";
                    echo "alert('Anda tidak memiliki hak untuk mengupdate data!');";
                    echo "window.location.href = '?page=bayar';";
                    echo "</script>";
                    exit();
                }


                $targetDir = "assets/images/bayar/";
                $fileName = basename($_FILES["foto_bayar"]["name"]);
                $targetFilePath = $targetDir . $fileName;
                $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

                $allowTypes = array('jpg', 'png', 'jpeg', 'gif');
                if (in_array($fileType, $allowTypes)) {
                    // Allow certain file formats
                    if (move_uploaded_file($_FILES["foto_bayar"]["tmp_name"], $targetFilePath)) {

                        // Handle Update
                        $id = $_POST['id_bayar_main'];
                        $data = array(
                            "no_bayar" => $_POST['no_bayar'],
                            "tgl_bayar" => $_POST['tgl_bayar'],
                            "kd_supplier" => $_POST['kd_supplier'],
                            "ket_bayar" => $_POST['ket_bayar'],
                            "kd_metode_bayar" => $_POST['kd_metode_bayar'],
                            "total_bayar" => $_POST['total_bayar'],
                            "foto_bayar" => $fileName,
                            "useraktif" => $useraktif
                        );
                        $options = array(
                            'http' => array(
                                'header'  => "Content-Type: application/json\r\n",
                                'method'  => 'PUT',
                                'content' => json_encode($data)
                            )
                        );
                        $context  = stream_context_create($options);
                        $belis = json_decode(file_get_contents($apiUrl . '/' . $id, false, $context));
                        header("Location: ?page=bayar&nobayar=".$_POST['no_bayar']);
                    }
                } else {
                    // Handle Update
                    $id = $_POST['id_bayar_main'];
                    $data = array(
                        "no_bayar" => $_POST['no_bayar'],
                        "tgl_bayar" => $_POST['tgl_bayar'],
                        "kd_supplier" => $_POST['kd_supplier'],
                        "ket_bayar" => $_POST['ket_bayar'],
                        "kd_metode_bayar" => $_POST['kd_metode_bayar'],
                        "total_bayar" => $_POST['total_bayar'],
                        "foto_bayar" => $_POST['old_foto_bayar'],
                        "useraktif" => $useraktif
                    );
                    $options = array(
                        'http' => array(
                            'header'  => "Content-Type: application/json\r\n",
                            'method'  => 'PUT',
                            'content' => json_encode($data)
                        )
                    );
                    $context  = stream_context_create($options);
                    $bayars = json_decode(file_get_contents($apiUrl . '/' . $id, false, $context));
                    header("Location: ?page=bayar&nobayar=".$_POST['no_bayar']);
                }
            }

            // User permission untuk delete form ini
            if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['delete'])) {

                if ($permission->is_delete == 0) {
                    echo "<script type='text/javascript'>";
                    echo "alert('Anda tidak memiliki hak untuk menghapus data!');";
                    echo "window.location.href = '?page=bayar';";
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
                $bayars = json_decode(file_get_contents($apiUrl . '/' . $id, false, $context));
                header("Location: ?page=bayar");
            }
        }
    }

    // pencarian data beli
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['search'])) {
        $searchQuery = $_POST['cari'];
        $url=$apiUrl . '?q=' . urlencode($searchQuery);
        $options = array(
            'http' => array(
                'method'  => 'GET',
            ),
        );
        $context  = stream_context_create($options);
        $bayars = json_decode(file_get_contents($url, false, $context));
    }

    // Data supplier
    $apiUrlSupplier= "http://localhost:3000/suppliers";
    $urlSupplier=$apiUrlSupplier.'?q=';
    $optSupplier=array(
        'http'=>array(
            'method'=>'GET',
        ),
    );
    $contSupplier=stream_context_create($optSupplier);
    $Suppliers = json_decode(file_get_contents($urlSupplier, false, $contSupplier));


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
    $totalBayar=0;
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

    .zoom-container {
        position: relative;
        overflow: hidden;
    }

    .zoom-container img {
        width: 50%;
        transition: transform 0.5s ease;
    }

    .zoom-container:hover img {
        width: 100%;
        /*transform: scale(1.5);*/
    }
</style>

<section id="bayar">
    <div class="header fixed-top">
        <div class="left">
            <a href="?page=home"><img src="assets/images/arrow-left.svg" alt="Back"></a>
        </div>
        <div class="center">
            <judul>Daftar Pembayaran</judul>
        </div>
        <div class="right">
            <a href="" data-toggle="modal" data-target="#NewModal"><img src="assets/images/plus-lg.svg" alt="Tambah"></a>
            <a href="" data-toggle="modal" data-target="#CariModal"><img src="assets/images/search.svg" alt="Cari"></a>
            <a href="?page=bayar"><img src="assets/images/arrow-clockwise.svg" alt="Hapus"></a>
        </div>
    </div>
    <div class="container-t45">
    </div>
    <div class="table-container">
        <table class="styled-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tgl Bayar</th>
                    <th>No Bayar</th>
                    <th>Nama Supplier</th>
                    <th>Keterangan</th>
                    <th>Metode</th>
                    <th style="text-align:right;">Total Bayar</th>
                    <th style="width:80px;">Perintah</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bayars as $bayar): ?>
                    <tr>
                        <td label="#"><?php echo $i++; ?></td>
                        <td label="Tgl Bayar">
                            <?php 
                                $dt_tglbayar = new DateTime($bayar->tgl_bayar); 
                                $dt_tglbayar->setTimezone(new DateTimeZone('Asia/Jakarta'));
                                echo $dt_tglbayar->format('Y-m-d');
                            ?>
                        </td>
                        <td label="No Bayar"><?php echo $bayar->no_bayar; ?></td>
                        <td label="Nama Supplier"><?php echo $bayar->nm_supplier; ?></a></td>
                        <td label="Keterangan"><?php echo $bayar->ket_bayar; ?></td>
                        <td label="Metode"><?php echo $bayar->kd_metode_bayar; ?></td>
                        <td label="Total Bayar" style="text-align:right;"><?php echo number_format($bayar->total_bayar,2); ?></td>
                        <td label="Perintah" style="width:80px;">
                            <!-- Input detail barang Link -->
                            <a class="btn btn-basic btn-sm" href="?page=bayardet&nobayar=<?php echo $bayar->no_bayar; ?>&idbyr=<?php echo $bayar->id_bayar_main; ?>&nm_sup=<?php echo $bayar->nm_supplier; ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-spreadsheet" viewBox="0 0 16 16">
                                    <path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v4h10V2a1 1 0 0 0-1-1zm9 6h-3v2h3zm0 3h-3v2h3zm0 3h-3v2h2a1 1 0 0 0 1-1zm-4 2v-2H6v2zm-4 0v-2H3v1a1 1 0 0 0 1 1zm-2-3h2v-2H3zm0-3h2V7H3zm3-2v2h3V7zm3 3H6v2h3z"/>
                                </svg>
                                <!--<img src="assets/images/file-spreadsheet.svg" alt="Input detail barang">-->
                            </a>

                            <!-- Update Link -->
                            <a class="btn btn-basic btn-sm" 
                                data-id_bayar_main="<?php echo $bayar->id_bayar_main; ?>" 
                                data-tgl_bayar="<?php echo $dt_tglbayar->format('Y-m-d'); ?>" 
                                data-no_bayar="<?php echo $bayar->no_bayar; ?>" 
                                data-kd_supplier="<?php echo $bayar->kd_supplier; ?>"
                                data-ket_bayar="<?php echo $bayar->ket_bayar; ?>" 
                                data-kd_metode_bayar="<?php echo $bayar->kd_metode_bayar; ?>"
                                data-total_bayar="<?php echo number_format($bayar->total_bayar,2); ?>" 
                                data-foto_bayar="<?php echo $bayar->foto_bayar; ?>" 
                                onclick="
                                    $('#EditModal').modal('show');
                                    $('#edit-id_bayar_main').val($(this).data('id_bayar_main'));
                                    $('#edit-no_bayar').val($(this).data('no_bayar'));
                                    $('#edit-tgl_bayar').val($(this).data('tgl_bayar'));
                                    $('#edit-kd_supplier').val($(this).data('kd_supplier'));
                                    $('#edit-ket_bayar').val($(this).data('ket_bayar'));
                                    $('#edit-kd_metode_bayar').val($(this).data('kd_metode_bayar'));
                                    $('#edit-total_bayar').val($(this).data('total_bayar'));
                                    $('#old_foto_bayar').val($(this).data('foto_bayar'));
                                    var fotoBayar= $(this).data('foto_bayar');
                                    if (fotoBayar) {
                                        $('#editview-foto_bayar').attr('src','assets/images/bayar/'+ fotoBayar + '');
                                        $('#editbayar-imagePreview').css('display','block');
                                    } else {
                                        $('#editview-foto_bayar').attr('src','');
                                        $('#editbayar-imagePreview').css('display','none'); //editbon_imagePreview
                                    }
                                ">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-vector-pen" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd" d="M10.646.646a.5.5 0 0 1 .708 0l4 4a.5.5 0 0 1 0 .708l-1.902 1.902-.829 3.313a1.5 1.5 0 0 1-1.024 1.073L1.254 14.746 4.358 4.4A1.5 1.5 0 0 1 5.43 3.377l3.313-.828zm-1.8 2.908-3.173.793a.5.5 0 0 0-.358.342l-2.57 8.565 8.567-2.57a.5.5 0 0 0 .34-.357l.794-3.174-3.6-3.6z"/>
                                  <path fill-rule="evenodd" d="M2.832 13.228 8 9a1 1 0 1 0-1-1l-4.228 5.168-.026.086z"/>
                                </svg>
                                <!--<img src="assets/images/vector-pen.svg" alt="Edit data">-->
                            </a>

                            <!-- Delete Link --> 
                            <a class="btn btn-basic btn-sm" href="?page=bayar&delete=<?php echo $bayar->id_bayar_main; ?>" onclick="return confirm('Apakah anda yakin akan menghapus data <?php echo $bayar->no_bayar; ?> ini?');">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash3" viewBox="0 0 16 16">
                                    <path d="M6.5 1h3a.5.5 0 0 1 .5.5v1H6v-1a.5.5 0 0 1 .5-.5M11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3A1.5 1.5 0 0 0 5 1.5v1H1.5a.5.5 0 0 0 0 1h.538l.853 10.66A2 2 0 0 0 4.885 16h6.23a2 2 0 0 0 1.994-1.84l.853-10.66h.538a.5.5 0 0 0 0-1zm1.958 1-.846 10.58a1 1 0 0 1-.997.92h-6.23a1 1 0 0 1-.997-.92L3.042 3.5zm-7.487 1a.5.5 0 0 1 .528.47l.5 8.5a.5.5 0 0 1-.998.06L5 5.03a.5.5 0 0 1 .47-.53Zm5.058 0a.5.5 0 0 1 .47.53l-.5 8.5a.5.5 0 1 1-.998-.06l.5-8.5a.5.5 0 0 1 .528-.47M8 4.5a.5.5 0 0 1 .5.5v8.5a.5.5 0 0 1-1 0V5a.5.5 0 0 1 .5-.5"/>
                                </svg>
                                <!--<img src="assets/images/trash3.svg" alt="Hapus data">-->
                            </a>
                            
                            <!-- Send WA Link --> 

                            <a class="btn btn-basic btn-sm" href="<?php echo $sendWA . $bayar->tl_supplier .  $isText . urlencode('Hai. Kami Bubur THI tanggal '. $dt_tglbayar->format('Y-m-d') .' sudah melakukan pembayaran sebesar Rp. ' . number_format($bayar->total_bayar,2) . ' silahkan dicek!'); ?>" target="_blank">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chat-left-dots" viewBox="0 0 16 16">
                                    <path d="M14 1a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H4.414A2 2 0 0 0 3 11.586l-2 2V2a1 1 0 0 1 1-1zM2 0a2 2 0 0 0-2 2v12.793a.5.5 0 0 0 .854.353l2.853-2.853A1 1 0 0 1 4.414 12H14a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2z"/>
                                    <path d="M5 6a1 1 0 1 1-2 0 1 1 0 0 1 2 0m4 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0m4 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/>
                                </svg>
                                <!--<img src="assets/images/trash3.svg" alt="Hapus data">-->
                            </a>

                        </td>
                    </tr>
                <?php
                    
                    $totalBayar = $totalBayar + $bayar->total_bayar;
                    endforeach; 
                ?>
            </tbody>
            <thead>
                <tr>
                    <th></th>
                    <th></th>
                    <th>Total</th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th style="text-align:right;"><?php echo number_format($totalBayar,2); ?></th>
                    <th></th>
                </tr>
            </thead>
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
                        <h6 class="modal-title" id="addModalLabel">Tambah Pembayaran</h6>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" class="form-control" id="useraktif" name="useraktif" value="<?php echo $useraktif; ?>" required>
                        <div class="row">
                            <div class="col-6">
                                <div class="form-floating mt-1 mb-1">
                                    <input type="text" class="form-control" id="no_bayar" name="no_bayar" placeholder="No Bayar" required disabled>
                                    <label for="no_bayar">No Bayar</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-floating mt-1 mb-1">
                                    <input type="date" class="form-control" id="tgl_bayar" name="tgl_bayar" placeholder="Tanggal Pembayaran" required>
                                    <label for="tgl_bayar">Tanggal Bayar</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-floating mt-1 mb-1">
                            <select type="text" class="form-select" id="kd_supplier" name="kd_supplier" placeholder="Nama Supplier" required>
                                <option value='' selected>Pilih Supplier</option>
                                <?php foreach ($Suppliers as $supplier) { ?>
                                    <option value="<?php echo $supplier->kd_supplier; ?>"><?php echo $supplier->nm_supplier; ?></option>
                                <?php } ?>
                            </select>
                            <label for="kd_supplier">Nama Supplier</label>
                        </div>
                        <div class="form-floating mt-2 mb-2">
                            <input type="text" class="form-control" id="ket_bayar" name="ket_bayar" placeholder="Keterangan" required>
                            <label for="ket_bayar">Keterangan</label>
                        </div>
                        <div class="form-floating mt-1 mb-1">
                            <select type="text" class="form-select" id="kd_metode_bayar" name="kd_metode_bayar" placeholder="Metode Pembayaran" required>
                                <option value='' selected>Pilih Metode</option>
                                <?php foreach ($Metodepays as $metodepay) { ?>
                                    <option value="<?php echo $metodepay->kd_metode_bayar; ?>"><?php echo $metodepay->nm_metode_bayar; ?></option>
                                <?php } ?>
                            </select>
                            <label for="kd_metode_bayar">Metode Pembayaran</label>
                        </div>
                        <div class="form-floating mt-1 mb-2">
                            <input type="number" class="form-control" id="total_bayar" name="total_bayar" value="0" placeholder="Total Bayar" required>
                            <label for="total_bayar">Total Bayar</label>
                        </div>
                        <div class="form-floating mt-1 mb-1">
                            <!-- Input file disembunyikan -->
                            
                            <input type="file" class="form-control d-none" id="foto_bayar" name="foto_bayar" placeholder="Foto Bayar" accept="image/*" onchange="
                                const preview = document.getElementById('view-foto_bayar');
                                const imagePreview = document.getElementById('bayar_imagePreview');
                                const file = event.target.files[0]; // Mendapatkan file dari input

                                if (file) {
                                    const reader = new FileReader();
                                    reader.onload = function(e) {
                                        preview.src = e.target.result; // Menampilkan gambar hasil unggahan
                                        imagePreview.style.display = 'block'; // Memunculkan area pratinjau
                                    };
                                    reader.readAsDataURL(file); // Membaca file gambar
                                } else {
                                    preview.src = ''; // Jika tidak ada file, kosongkan pratinjau
                                    imagePreview.style.display = 'none'; // Sembunyikan pratinjau
                                }
                            ">

                            <!-- Tombol kustom untuk memilih file -->
                            <button class="btn btn-primary mt-2" type="button" onclick="document.getElementById('foto_bayar').click()">Pilih Foto</button> 

                            <!-- Area pratinjau gambar -->
                            <div id="bayar_imagePreview" class="mt-3" style="display:none;">
                                <p>Pratinjau Gambar:</p>
                                <div class="zoom-container" align="center">
                                    <img id="view-foto_bayar" src="" alt="Foto Bayar" >
                                </div> <!-- style="max-width: 200px; border-radius: 8px;" -->
                            </div> 
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                        <button type="submit" name="insert" class="btn btn-primary btn-sm">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- The Edit Modal -->
    <div id="EditModal" class="modal fade" role="dialog" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" style="display:inline-block;" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h6 class="modal-title" id="addModalLabel">Edit Pembayaran</h6>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" class="form-control" id="edit-useraktif" name="useraktif" value="<?php echo $useraktif; ?>" required>
                        <input type="hidden" class="form-control" id="edit-id_bayar_main" name="id_bayar_main" placeholder="ID bayar" required>
                        <div class="row">
                            <div class="col-6">
                                <div class="form-floating mt-1 mb-1">
                                    <input type="text" class="form-control" id="edit-no_bayar" name="no_bayar" placeholder="No Bayar" required>
                                    <label for="edit-no_bayar">No Bayar</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-floating mt-1 mb-1">
                                    <input type="date" class="form-control" id="edit-tgl_bayar" name="tgl_bayar" placeholder="Tanggal Pembayaran" required>
                                    <label for="edit-tgl_bayar">Tanggal Bayar</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-floating mt-1 mb-1">
                            <select type="text" class="form-select" id="edit-kd_supplier" name="kd_supplier" placeholder="Nama Supplier" required>
                                <?php foreach ($Suppliers as $supplier) { ?>
                                    <option value="<?php echo $supplier->kd_supplier; ?>"><?php echo $supplier->nm_supplier; ?></option>
                                <?php } ?>
                            </select>
                            <label for="kd_supplier">Nama Supplier</label>
                        </div>
                        <div class="form-floating mt-2 mb-2">
                            <input type="text" class="form-control" id="edit-ket_bayar" name="ket_bayar" placeholder="Keterangan" required>
                            <label for="edit-ket_bayar">Keterangan</label>
                        </div>
                        <div class="form-floating mt-1 mb-1">
                            <select type="text" class="form-select" id="edit-kd_metode_bayar" name="kd_metode_bayar" placeholder="Metode Pembayaran" required>
                                <?php foreach ($Metodepays as $metodepay) { ?>
                                    <option value="<?php echo $metodepay->kd_metode_bayar; ?>"><?php echo $metodepay->nm_metode_bayar; ?></option>
                                <?php } ?>
                            </select>
                            <label for="edit-kd_metode_bayar">Metode Pembayaran</label>
                        </div>
                        <div class="form-floating mt-1 mb-2">
                            <input type="text" class="form-control" id="edit-total_bayar" name="total_bayar" placeholder="Total Bayar" required>
                            <label for="edit-total_bayar">Total Bayar</label>
                        </div>
                        <div class="form-floating mt-1 mb-1">
                            <!-- Input file disembunyikan -->
                            
                            <input type="file" class="form-control d-none" id="edit-foto_bayar" name="foto_bayar" placeholder="Foto Bayar" accept="image/*" onchange="
                                const preview = document.getElementById('editview-foto_bayar');
                                const imagePreview = document.getElementById('editbayar-imagePreview');
                                const file = event.target.files[0]; // Mendapatkan file dari input

                                if (file) {
                                    const reader = new FileReader();
                                    reader.onload = function(e) {
                                        preview.src = e.target.result; // Menampilkan gambar hasil unggahan
                                        imagePreview.style.display = 'block'; // Memunculkan area pratinjau
                                    };
                                    reader.readAsDataURL(file); // Membaca file gambar
                                } else {
                                    preview.src = ''; // Jika tidak ada file, kosongkan pratinjau
                                    imagePreview.style.display = 'none'; // Sembunyikan pratinjau
                                }
                            ">
                            <input type="text" class="form-control d-none" id="old_foto_bayar" name="old_foto_bayar">

                            <!-- Tombol kustom untuk memilih file -->
                            <button class="btn btn-primary mt-2" type="button" onclick="document.getElementById('edit-foto_bayar').click()">Pilih Foto</button> 

                            <!-- Area pratinjau gambar -->
                            <div id="editbayar-imagePreview" class="mt-3" style="display:none;">
                                <p>Pratinjau Gambar:</p>
                                <div class="zoom-container" align="center">
                                    <img id="editview-foto_bayar" src="" alt="Foto Bayar" >
                                </div> <!-- style="max-width: 200px; border-radius: 8px;" -->
                            </div> 
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="update" class="btn btn-primary btn-sm">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- The Cari Modal -->
    <div id="CariModal" class="modal fade" role="dialog" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" style="display:inline-block;">
                    <div class="modal-header">
                        <h6 class="modal-title" id="addModalLabel">Cari Pembayaran</h6>
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
