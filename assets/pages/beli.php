<?php
    $apiUrl = "http://192.168.194.50:3000/belis";
    $apiUrlPerm = "http://192.168.194.50:3000/permissions";
    
    function DateYmd($date) { // fungsi atau method untuk mengubah tanggal ke format indonesia
       // variabel BulanIndo merupakan variabel array yang menyimpan nama-nama bulan
         
        $tahun = substr($date, 0, 4); // memisahkan format tahun menggunakan substring
        $bulan = substr($date, 5, 2); // memisahkan format bulan menggunakan substring
        $tgl   = substr($date, 8, 2); // memisahkan format tanggal menggunakan substring
         
        $result=$tahun . "-" . $bulan . "-" . $tgl;
        return($result);
    }

    session_start();
    if (!isset($_SESSION['username'])) {
        header("Location: ?page=login");
       exit();
    }

    // Cek user permission untuk menu beli
    $useraktif=$_SESSION['username'];
    $menuname='form_beli';
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
                    $searchQuery = $_GET['nobeli'];
                    $url=$apiUrl . '?q=' . urlencode($searchQuery);
                    $options = array(
                        'http' => array(
                            'method'  => 'GET',
                        ),
                    );
                    $context  = stream_context_create($options);
                    $belis = json_decode(file_get_contents($url, false, $context));
                }
            }

            // User permission untuk addnew form ini
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['insert'])) {

                if ($permission->is_addnew == 0) {
                    echo "<script type='text/javascript'>";
                    echo "alert('Anda tidak memiliki hak untuk menambahkan data!');";
                    echo "window.location.href = '?page=beli';";
                    echo "</script>";
                    exit();
                }

                $targetDir = "assets/images/bon/";
                $fileName = basename($_FILES["foto_bon"]["name"]);
                $targetFilePath = $targetDir . $fileName;
                $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

                $allowTypes = array('jpg', 'png', 'jpeg', 'gif');
                if (in_array($fileType, $allowTypes)) {
                    // Allow certain file formats
                    if (move_uploaded_file($_FILES["foto_bon"]["tmp_name"], $targetFilePath)) {

                        // Handle Create
                        $data = array(
                            //"id_beli" => $_POST['id_beli'],
                            "no_beli" => $_POST['no_beli'],
                            "tgl_beli" => $_POST['tgl_beli'],
                            "kd_supplier" => $_POST['kd_supplier'],
                            "ket_beli" => $_POST['ket_beli'],
                            "sub_beli" => $_POST['sub_beli'],
                            "pot_beli" => $_POST['pot_beli'],
                            "ppn_beli" => $_POST['ppn_beli'],
                            "tot_beli" => $_POST['tot_beli'],
                            "foto_bon" => $fileName, //$_POST['foto_bon'],
                            "user_created" => $_POST['user_created'],
                            "date_created" => $_POST['date_created'],
                            "user_modified" => $_POST['user_modified'],
                            "date_modified" => $_POST['date_modified'],
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
                        $belis=json_decode(file_get_contents($apiUrl, false, $context));
                        header("Location: ?page=beli");
                    }
                } else {
                    // Handle Create
                    $data = array(
                        //"id_beli" => $_POST['id_beli'],
                        "no_beli" => $_POST['no_beli'],
                        "tgl_beli" => $_POST['tgl_beli'],
                        "kd_supplier" => $_POST['kd_supplier'],
                        "ket_beli" => $_POST['ket_beli'],
                        "sub_beli" => $_POST['sub_beli'],
                        "pot_beli" => $_POST['pot_beli'],
                        "ppn_beli" => $_POST['ppn_beli'],
                        "tot_beli" => $_POST['tot_beli'],
                        "foto_bon" => $_POST['foto_bon'],
                        "user_created" => $_POST['user_created'],
                        "date_created" => $_POST['date_created'],
                        "user_modified" => $_POST['user_modified'],
                        "date_modified" => $_POST['date_modified'],
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
                    $belis=json_decode(file_get_contents($apiUrl, false, $context));
                    header("Location: ?page=beli");
                }
            }

            // User permission untuk update form ini
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {

                if ($permission->is_update == 0) {
                    echo "<script type='text/javascript'>";
                    echo "alert('Anda tidak memiliki hak untuk mengupdate data!');";
                    echo "window.location.href = '?page=beli';";
                    echo "</script>";
                    exit();
                }


                $targetDir = "assets/images/bon/";
                $fileName = basename($_FILES["foto_bon"]["name"]);
                $targetFilePath = $targetDir . $fileName;
                $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

                $allowTypes = array('jpg', 'png', 'jpeg', 'gif');
                if (in_array($fileType, $allowTypes)) {
                    // Allow certain file formats
                    if (move_uploaded_file($_FILES["foto_bon"]["tmp_name"], $targetFilePath)) {

                        // Handle Update
                        $id = $_POST['id_beli'];
                        $data = array(
                            "no_beli" => $_POST['no_beli'],
                            "tgl_beli" => $_POST['tgl_beli'],
                            "kd_supplier" => $_POST['kd_supplier'],
                            "ket_beli" => $_POST['ket_beli'],
                            "sub_beli" => $_POST['sub_beli'],
                            "pot_beli" => $_POST['pot_beli'],
                            "ppn_beli" => $_POST['ppn_beli'],
                            "tot_beli" => $_POST['tot_beli'],
                            "foto_bon" => $fileName, //$_POST['foto_bon'],
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
                        header("Location: ?page=beli&nobeli=".$_POST['no_beli']);
                    }
                } else {
                    // Handle Update
                    $id = $_POST['id_beli'];
                    $data = array(
                        "no_beli" => $_POST['no_beli'],
                        "tgl_beli" => $_POST['tgl_beli'],
                        "kd_supplier" => $_POST['kd_supplier'],
                        "ket_beli" => $_POST['ket_beli'],
                        "sub_beli" => $_POST['sub_beli'],
                        "pot_beli" => $_POST['pot_beli'],
                        "ppn_beli" => $_POST['ppn_beli'],
                        "tot_beli" => $_POST['tot_beli'],
                        "foto_bon" => $_POST['old_foto_bon'],
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
                    header("Location: ?page=beli");
                }
            }

            // User permission untuk delete form ini
            if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['delete'])) {

                if ($permission->is_delete == 0) {
                    echo "<script type='text/javascript'>";
                    echo "alert('Anda tidak memiliki hak untuk menghapus data!');";
                    echo "window.location.href = '?page=beli';";
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
                $belis = json_decode(file_get_contents($apiUrl . '/' . $id, false, $context));
                header("Location: ?page=beli");
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
        $belis = json_decode(file_get_contents($url, false, $context));
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

    $i=1;
    $subBeli=0;
    $potBeli=0;
    $ppnBeli=0;
    $totBeli=0;
    $totBayar=0;
    $totHutang=0;
    $sum_text="nomor, tanggal, supplier, total beli, total bayar, sisa hutang"."\n";
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

<section id="beli">
    <div class="header fixed-top">
        <div class="left">
            <a href="?page=home"><img src="assets/images/arrow-left.svg" alt="Back"></a>
        </div>
        <div class="center">
            <judul>Daftar Pembelian</judul>
        </div>
        <div class="right">
            <a href="" data-toggle="modal" data-target="#NewModal"><img src="assets/images/plus-lg.svg" alt="Tambah"></a>
            <a href="" data-toggle="modal" data-target="#CariModal"><img src="assets/images/search.svg" alt="Cari"></a>
            <a href="?page=beli"><img src="assets/images/arrow-clockwise.svg" alt="Refresh"></a>
            <a href="" data-toggle="modal" data-target="#SummaryModal"><img src="assets/images/table.svg" alt="Cari"></a>
            <!--<a href="http://192.168.194.50:3000/beliexport?q=<?php echo $_POST['cari']; ?>"><img src="assets/images/table.svg" alt="To Excel"></a>-->
        </div>
    </div>
    <div class="container-t45">
    </div>
    <!--<form method="POST" style="display:inline-block;">
        <div class="row">
            <div class='col-4'>
                <div class="form-group mt-1 mb-1 ml-1">
                    <select type="text" class="form-select" id="page" name="page" placeholder="Page" style='width:75px;' required style="margin-top:5px; margin-bottom:5px;">
                        <option value='10' selected>10</option>
                        <option value='10'>20</option>
                        <option value='10'>50</option>
                        <option value='10'>100</option>
                    </select>
                </div>
            </div>
            <div class='col-2'>
            </div>
            <div class='col-6'>
                <div class="form-grooup mt-1 mb-1 mr-1">
                    <input type="text" class="form-control" id="cari" name="cari" placeholder="Kata pencarian">
                </div>
            </div>
        </div>
    </form>-->
    <div class="table-container">
        <table class="styled-table">
            <thead>
                <tr>
                    <th>#</th>
                    <!--<th>Status</th>-->
                    <th>Tgl Beli</th>
                    <th>No Beli</th>
                    <th>Nama Supplier</th>
                    <th>User Created</th>
                    <th style="text-align:right;">Sub Total</th>
                    <th style="text-align:right;">Potongan</th>
                    <th style="text-align:right;">PPN</th>
                    <th style="text-align:right;">Total Beli</th>
                    <th style="text-align:right;">Total Bayar</th>
                    <th style="text-align:right;">Total Hutang</th>
                    <th>Keterangan</th>
                    <th style="width:80px;">Perintah</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($belis as $beli): ?>
                    <tr>
                        <td label="#"><?php echo $i++; ?></td>
                        <!--<td label='Status'>
                            <?php 
                                $nilai=number_format($beli->tot_hutang,2);
                                
                                if($nilai==0){
                                    $status='lunas';
                                } elseif ($nilai>0){
                                    $status='hutang';
                                } 

                                if ($beli->tot_beli==0) {
                                    $status='no valid';
                                }
                                
                                if ($status=="lunas"){
                                    $lbl="badge-success";
                                }elseif($status=="hutang"){
                                    $lbl="badge-warning";
                                }elseif($status=="no valid"){
                                    $lbl="badge-danger";
                                }
                            ?>
                            <div class='badge <?php echo $lbl ?> rounded-pill'><?php echo $status ?></div>
                        </td>-->
                        <td label="Tgl Beli">
                            <?php 
                                $dt_tglbeli = new DateTime($beli->tgl_beli); 
                                $dt_tglbeli->setTimezone(new DateTimeZone('Asia/Jakarta'));
                                echo $dt_tglbeli->format('Y-m-d');
                            ?>
                        </td>
                        <td label="No Beli">
                            <?php 
                                echo $beli->no_beli;
                                $nilai=number_format($beli->tot_hutang,2);
                                
                                if($nilai==0){
                                    $status='lunas';
                                } elseif ($nilai>0){
                                    $status='hutang';
                                } 

                                if ($beli->tot_beli==0) {
                                    $status='no valid';
                                }
                                
                                if ($status=="lunas"){
                                    $lbl="badge-success";
                                }elseif($status=="hutang"){
                                    $lbl="badge-warning";
                                }elseif($status=="no valid"){
                                    $lbl="badge-danger";
                                }
                            ?>
                            <span class='badge <?php echo $lbl ?> rounded-pill top-0 start-100'>
                                <?php echo $status ?>
                                <!--<span class="visually-hidden">Status</span>-->
                            </span>
                        </td>
                        <td label="Nama Supplier"><?php echo $beli->nm_supplier; ?></a></td>
                        <td label="User"><?php echo $beli->user_created; ?></td>
                        <td label="Sub Total" style="text-align:right;"><?php echo number_format($beli->sub_beli,0); ?></td>
                        <td label="Potongan" style="text-align:right;"><?php echo number_format($beli->pot_beli,0); ?></td>
                        <td label="PPN" style="text-align:right;"><?php echo number_format($beli->ppn_beli,0); ?></td>
                        <td label="Total Beli" style="text-align:right;"><?php echo number_format($beli->tot_beli,0); ?></td>
                        <td label="Total Bayar" style="text-align:right;"><?php echo number_format($beli->tot_bayar,0); ?></td>
                        <td label="Total Hutang" style="text-align:right;"><?php echo number_format($beli->tot_hutang,0); ?></td>
                        <td label="Keterangan"><?php echo $beli->ket_beli; ?></td>
                        <td label="Perintah" style="width:80px;">
                            <!-- Input detail barang Link -->
                            <a class="btn btn-basic btn-sm" href="?page=belidet&nobeli=<?php echo $beli->no_beli; ?>&id=<?php echo $beli->id_beli; ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-spreadsheet" viewBox="0 0 16 16">
                                    <path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v4h10V2a1 1 0 0 0-1-1zm9 6h-3v2h3zm0 3h-3v2h3zm0 3h-3v2h2a1 1 0 0 0 1-1zm-4 2v-2H6v2zm-4 0v-2H3v1a1 1 0 0 0 1 1zm-2-3h2v-2H3zm0-3h2V7H3zm3-2v2h3V7zm3 3H6v2h3z"/>
                                </svg>
                                <!--<img src="assets/images/file-spreadsheet.svg" alt="Input detail barang">-->
                            </a>

                            <!-- Update Link -->
                            <a class="btn btn-basic btn-sm" 
                                data-id_beli="<?php echo $beli->id_beli; ?>" 
                                data-no_beli="<?php echo $beli->no_beli; ?>" 
                                data-tgl_beli="<?php echo $dt_tglbeli->format('Y-m-d'); ?>" 
                                data-kd_supplier="<?php echo $beli->kd_supplier; ?>" 
                                data-ket_beli="<?php echo $beli->ket_beli; ?>" 
                                data-sub_beli="<?php echo number_format($beli->sub_beli,2); ?>" 
                                data-pot_beli="<?php echo number_format($beli->pot_beli,2); ?>" 
                                data-ppn_beli="<?php echo number_format($beli->ppn_beli,2); ?>" 
                                data-tot_beli="<?php echo number_format($beli->tot_beli,2); ?>" 
                                data-foto_bon="<?php echo $beli->foto_bon; ?>" 
                                onclick="
                                    $('#EditModal').modal('show');
                                    $('#edit-id_beli').val($(this).data('id_beli'));
                                    $('#edit-no_beli').val($(this).data('no_beli'));
                                    $('#edit-tgl_beli').val($(this).data('tgl_beli'));
                                    $('#edit-kd_supplier').val($(this).data('kd_supplier'));
                                    $('#edit-ket_beli').val($(this).data('ket_beli'));
                                    $('#edit-sub_beli').val($(this).data('sub_beli'));
                                    $('#edit-pot_beli').val($(this).data('pot_beli'));
                                    $('#edit-ppn_beli').val($(this).data('ppn_beli'));
                                    $('#edit-tot_beli').val($(this).data('tot_beli'));
                                    $('#old_foto_bon').val($(this).data('foto_bon'));
                                    var fotoBon= $(this).data('foto_bon');
                                    if (fotoBon) {
                                        $('#editview-foto_bon').attr('src','assets/images/bon/'+ fotoBon + '');
                                        $('#editbon-imagePreview').css('display','block');
                                    } else {
                                        $('#editview-foto_bon').attr('src','');
                                        $('#editbon-imagePreview').css('display','none'); //editbon_imagePreview
                                    }
                                ">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-vector-pen" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd" d="M10.646.646a.5.5 0 0 1 .708 0l4 4a.5.5 0 0 1 0 .708l-1.902 1.902-.829 3.313a1.5 1.5 0 0 1-1.024 1.073L1.254 14.746 4.358 4.4A1.5 1.5 0 0 1 5.43 3.377l3.313-.828zm-1.8 2.908-3.173.793a.5.5 0 0 0-.358.342l-2.57 8.565 8.567-2.57a.5.5 0 0 0 .34-.357l.794-3.174-3.6-3.6z"/>
                                  <path fill-rule="evenodd" d="M2.832 13.228 8 9a1 1 0 1 0-1-1l-4.228 5.168-.026.086z"/>
                                </svg>
                                <!--<img src="assets/images/vector-pen.svg" alt="Edit data">-->
                            </a>

                            <!-- Delete Link --> 
                            <a class="btn btn-basic btn-sm" href="?page=beli&delete=<?php echo $beli->id_beli; ?>" onclick="return confirm('Apakah anda yakin akan menghapus data <?php echo $beli->nm_beli; ?> ini?');">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash3" viewBox="0 0 16 16">
                                    <path d="M6.5 1h3a.5.5 0 0 1 .5.5v1H6v-1a.5.5 0 0 1 .5-.5M11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3A1.5 1.5 0 0 0 5 1.5v1H1.5a.5.5 0 0 0 0 1h.538l.853 10.66A2 2 0 0 0 4.885 16h6.23a2 2 0 0 0 1.994-1.84l.853-10.66h.538a.5.5 0 0 0 0-1zm1.958 1-.846 10.58a1 1 0 0 1-.997.92h-6.23a1 1 0 0 1-.997-.92L3.042 3.5zm-7.487 1a.5.5 0 0 1 .528.47l.5 8.5a.5.5 0 0 1-.998.06L5 5.03a.5.5 0 0 1 .47-.53Zm5.058 0a.5.5 0 0 1 .47.53l-.5 8.5a.5.5 0 1 1-.998-.06l.5-8.5a.5.5 0 0 1 .528-.47M8 4.5a.5.5 0 0 1 .5.5v8.5a.5.5 0 0 1-1 0V5a.5.5 0 0 1 .5-.5"/>
                                </svg>
                                <!--<img src="assets/images/trash3.svg" alt="Hapus data">-->
                            </a>
                            
                        </td>
                    </tr>
                <?php
                    $subBeli = $subBeli + $beli->sub_beli;
                    $potBeli = $potBeli + $beli->pot_beli;
                    $ppnBeli = $ppnBeli + $beli->ppn_beli;
                    $totBeli = $totBeli + $beli->tot_beli;
                    $totBayar = $totBayar + $beli->tot_bayar;
                    $totHutang = $totHutang + $beli->tot_hutang;
                    
                    $text=$beli->no_beli.", ".$dt_tglbeli->format('Y-m-d').", ".$beli->nm_supplier.", ".number_format($beli->tot_beli,2).", ".number_format($beli->tot_bayar,2).", ".number_format($beli->tot_hutang,2)."\n";
                    $sum_text= $sum_text . $text;
                    endforeach; 
                ?>
            </tbody>
            <thead>
                <tr>
                    <th></th>
                    <th></th>
                    <!--<th></th>-->
                    <th>Total</th>
                    <th></th>
                    <th></th>
                    <th style="text-align:right;"><?php echo number_format($subBeli,0); ?></th>
                    <th style="text-align:right;"><?php echo number_format($potBeli,0); ?></th>
                    <th style="text-align:right;"><?php echo number_format($ppnBeli,0); ?></th>
                    <th style="text-align:right;"><?php echo number_format($totBeli,0); ?></th>
                    <th style="text-align:right;"><?php echo number_format($totBayar,0); ?></th>
                    <th style="text-align:right;"><?php echo number_format($totHutang,0); ?></th>
                    <th></th>
                    <th></th>
                </tr>
                <?php $sum_text= $sum_text . "*Total,,,".number_format($totBeli,2).",".number_format($totBayar,2).",".number_format($totHutang,2)."*"; ?>
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
                        <h6 class="modal-title" id="addModalLabel">Tambah Pembelian</h6>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" class="form-control" id="useraktif" name="useraktif" value="<?php echo $useraktif; ?>" required>
                        <!--<div class="form-floating mt-1 mb-2">
                            <input type="date" class="form-control" id="tgl_beli" name="tgl_beli" placeholder="Tanggal Pembelian" required>
                            <label for="tgl_beli">Tanggal Beli</label>
                        </div>-->
                        <div class="row">
                            <div class="col-6">
                                <div class="form-floating mt-1 mb-1">
                                    <input type="text" class="form-control" id="no_beli" name="no_beli" placeholder="No BON/Beli" required disabled>
                                    <label for="no_beli">No BON/Beli</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-floating mt-1 mb-1">
                                    <input type="date" class="form-control" id="tgl_beli" name="tgl_beli" placeholder="Tanggal Pembelian" required>
                                    <label for="tgl_beli">Tanggal Beli</label>
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
                            <input type="text" class="form-control" id="ket_beli" name="ket_beli" placeholder="Keterangan" required>
                            <label for="ket_beli">Keterangan</label>
                        </div>
                        <div class="form-floating mt-1 mb-2">
                            <input type="number" class="form-control" id="sub_beli" name="sub_beli" value="0" placeholder="Sub Total" required>
                            <label for="sub_beli">Sub Total</label>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <div class="form-floating mt-1 mb-2">
                                    <input type="number" class="form-control" id="pot_beli" name="pot_beli" value="0" placeholder="Potongan" required>
                                    <label for="pot_beli">Potongan</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-floating mt-1 mb-2">
                                    <input type="number" class="form-control" id="ppn_beli" name="ppn_beli" value="0" placeholder="PPN" required>
                                    <label for="ppn_beli">PPN</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-floating mt-1 mb-2">
                            <input type="number" class="form-control" id="tot_beli" name="tot_beli" value="0" placeholder="Total Beli" required>
                            <label for="tot_beli">Total Beli</label>
                        </div>
                        <div class="form-floating mt-1 mb-1">
                            <!-- Input file disembunyikan -->
                            
                            <input type="file" class="form-control d-none" id="foto_bon" name="foto_bon" placeholder="Foto BON" accept="image/*" onchange="
                                const preview = document.getElementById('view-foto_bon');
                                const imagePreview = document.getElementById('bon_imagePreview');
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
                            <!--<label for="foto_barang">Unggah Foto Barang</label>-->

                            <!-- Tombol kustom untuk memilih file -->
                            <button class="btn btn-primary mt-2" type="button" onclick="document.getElementById('foto_bon').click()">Pilih Foto</button> 

                            <!-- Area pratinjau gambar -->
                            <div id="bon_imagePreview" class="mt-3" style="display:none;">
                                <p>Pratinjau Gambar:</p>
                                <div class="zoom-container" align="center">
                                    <img id="view-foto_bon" src="" alt="Foto BON" >
                                </div> <!--style="max-width: 200px; border-radius: 8px;"-->
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
                        <h6 class="modal-title" id="addModalLabel">Edit Pembelian</h6>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" class="form-control" id="edit-useraktif" name="useraktif" value="<?php echo $useraktif; ?>" required>
                        <input type="hidden" class="form-control" id="edit-id_beli" name="id_beli" placeholder="ID beli" required>
                        <div class="row">
                            <div class="col-6">
                                <div class="form-floating mt-1 mb-1">
                                    <input type="text" class="form-control" id="edit-no_beli" name="no_beli" placeholder="No BON/Beli" required>
                                    <label for="edit-no_beli">No BON/Beli</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-floating mt-1 mb-1">
                                    <input type="date" class="form-control" id="edit-tgl_beli" name="tgl_beli" placeholder="Tanggal Pembelian" required>
                                    <label for="edit-tgl_beli">Tanggal Beli</label>
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
                            <input type="text" class="form-control" id="edit-ket_beli" name="ket_beli" placeholder="Keterangan" required>
                            <label for="edit-ket_beli">Keterangan</label>
                        </div>
                        <div class="form-floating mt-1 mb-2">
                            <input type="text" class="form-control" id="edit-sub_beli" name="sub_beli" placeholder="Sub Total" required>
                            <label for="edit-sub_beli">Sub Total</label>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <div class="form-floating mt-1 mb-2">
                                    <input type="text" class="form-control" id="edit-pot_beli" name="pot_beli" placeholder="Potongan" required>
                                    <label for="edit-pot_beli">Potongan</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-floating mt-1 mb-2">
                                    <input type="text" class="form-control" id="edit-ppn_beli" name="ppn_beli" placeholder="PPN" required>
                                    <label for="edit-ppn_beli">PPN</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-floating mt-1 mb-2">
                            <input type="text" class="form-control" id="edit-tot_beli" name="tot_beli" placeholder="Total Beli" required>
                            <label for="edit-tot_beli">Total Beli</label>
                        </div>
                        <div class="form-floating mt-1 mb-1">
                            <!-- Input file disembunyikan -->
                            
                            <input type="file" class="form-control d-none" id="edit-foto_bon" name="foto_bon" placeholder="Foto BON" accept="image/*" onchange="
                                const preview = document.getElementById('editview-foto_bon');
                                const imagePreview = document.getElementById('editbon-imagePreview');
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
                            <input type="text" class="form-control d-none" id="old_foto_bon" name="old_foto_bon">

                            <!-- Tombol kustom untuk memilih file -->
                            <button class="btn btn-primary mt-2" type="button" onclick="document.getElementById('edit-foto_bon').click()">Pilih Foto</button> 

                            <!-- Area pratinjau gambar -->
                            <div id="editbon-imagePreview" class="mt-3" style="display:none;">
                                <p>Pratinjau Gambar:</p>
                                <div class="zoom-container" align="center">
                                    <img id="editview-foto_bon" src="" alt="Foto BON">
                                </div> <!--style="max-width: 200px; border-radius: 8px;"-->
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
                        <h6 class="modal-title" id="addModalLabel">Cari Pembelian</h6>
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

    <!-- The Rangkuman Modal -->
    <div id="SummaryModal" class="modal fade" role="dialog" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" style="display:inline-block;" action="https://api.whatsapp.com/send/?phone=6289673738953&text=<?php echo urlencode($sum_text); ?>" target="_blank">
                <!--<form method="POST" style="display:inline-block;" action="whatsapp://send?phone=6289673738953&text=<?php echo urlencode($sum_text); ?>">-->
                    <div class="modal-header">
                        <h6 class="modal-title" id="addModalLabel">Rangkuman Pembelian</h6>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-floating mt-1 mb-1">
                            <textarea type="text" class="form-control" id="sum-text" name="sum-text" style="height: 300px;">
                                <?php 
                                    //echo implode("%0A", ); 
                                    echo urlencode($sum_text);
                                ?>
                            </textarea>
                            <label for="cari">Rangkuman pembelian sebagai berikut...</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                        <button type="submit" name="summary" class="btn btn-primary btn-sm">Kirim WA</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</section>