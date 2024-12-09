<?php
    $apiUrl = "http://192.168.194.50:3000/barangs";
    $apiUrlPerm = "http://192.168.194.50:3000/permissions";
    

    session_start();
    if (!isset($_SESSION['username'])) {
        header("Location: ?page=login");
       exit();
    }

    // Cek user permission untuk menu barang
    $useraktif=$_SESSION['username'];
    $menuname='form_barang';
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
                    $barangs = json_decode(file_get_contents($url, false, $context));
                }
            }

            // User permission untuk addnew form ini
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['insert'])) {

                if ($permission->is_addnew == 0) {
                    echo "<script type='text/javascript'>";
                    echo "alert('Anda tidak memiliki hak untuk menambahkan data!');";
                    echo "window.location.href = '?page=barang';";
                    echo "</script>";
                    exit();
                }

                $targetDir = "assets/images/barang/";
                $fileName = basename($_FILES["foto_barang"]["name"]);
                $targetFilePath = $targetDir . $fileName;
                $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

                $allowTypes = array('jpg', 'png', 'jpeg', 'gif');
                if (in_array($fileType, $allowTypes)) {
                    // Allow certain file formats
                    if (move_uploaded_file($_FILES["foto_barang"]["tmp_name"], $targetFilePath)) {
                        // Handle Create
                        $data = array(
                            //"id_barang" => $_POST['id_barang'],
                            "kd_barang" => $_POST['kd_barang'],
                            "nm_barang" => $_POST['nm_barang'],
                            "kd_kategori" => $_POST['kd_kategori'],
                            "kd_supplier" => $_POST['kd_supplier'],
                            "kd_satuan_besar" => $_POST['kd_satuan_besar'],
                            "qty_besar" => $_POST['qty_besar'],
                            "kd_satuan_kecil" => $_POST['kd_satuan_kecil'],
                            "qty_kecil" => $_POST['qty_kecil'],
                            "is_stok" => $_POST['is_stok'],
                            "foto_barang" => $fileName, //$_POST['foto_barang'],
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
                        $barangs=json_decode(file_get_contents($apiUrl, false, $context));
                        header("Location: ?page=barang&q=".$_POST['id_barang']);
                    }
                } else {
                    $data = array(
                        //"id_barang" => $_POST['id_barang'],
                        "kd_barang" => $_POST['kd_barang'],
                        "nm_barang" => $_POST['nm_barang'],
                        "kd_kategori" => $_POST['kd_kategori'],
                        "kd_supplier" => $_POST['kd_supplier'],
                        "kd_satuan_besar" => $_POST['kd_satuan_besar'],
                        "qty_besar" => $_POST['qty_besar'],
                        "kd_satuan_kecil" => $_POST['kd_satuan_kecil'],
                        "qty_kecil" => $_POST['qty_kecil'],
                        "is_stok" => $_POST['is_stok'],
                        "foto_barang" => $_POST['foto_barang'],
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
                    $barangs=json_decode(file_get_contents($apiUrl, false, $context));
                    header("Location: ?page=barang&q=".$_POST['id_barang']);
                }
                
            }

            // User permission untuk update form ini
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {

                if ($permission->is_update == 0) {
                    echo "<script type='text/javascript'>";
                    echo "alert('Anda tidak memiliki hak untuk mengupdate data!');";
                    echo "window.location.href = '?page=barang';";
                    echo "</script>";
                    exit();
                }

                $targetDir = "assets/images/barang/";
                $fileName = basename($_FILES["foto_barang"]["name"]);
                $targetFilePath = $targetDir . $fileName;
                $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

                $allowTypes = array('jpg', 'png', 'jpeg', 'gif');
                if (in_array($fileType, $allowTypes)) {
                    // Allow certain file formats
                    if (move_uploaded_file($_FILES["foto_barang"]["tmp_name"], $targetFilePath)) {

                        // Handle Update
                        $id = $_POST['id_barang'];
                        $data = array(
                            "kd_barang" => $_POST['kd_barang'],
                            "nm_barang" => $_POST['nm_barang'],
                            "kd_kategori" => $_POST['kd_kategori'],
                            "kd_supplier" => $_POST['kd_supplier'],
                            "kd_satuan_besar" => $_POST['kd_satuan_besar'],
                            "qty_besar" => $_POST['qty_besar'],
                            "kd_satuan_kecil" => $_POST['kd_satuan_kecil'],
                            "qty_kecil" => $_POST['qty_kecil'],
                            "is_stok" => $_POST['is_stok'],
                            "foto_barang" => $fileName, //$_POST['foto_barang'],
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
                        $barangs = json_decode(file_get_contents($apiUrl . '/' . $id, false, $context));
                        header("Location: ?page=barang");
                    }
                } else {
                    // Handle Update
                    $id = $_POST['id_barang'];
                    $data = array(
                        "kd_barang" => $_POST['kd_barang'],
                        "nm_barang" => $_POST['nm_barang'],
                        "kd_kategori" => $_POST['kd_kategori'],
                        "kd_supplier" => $_POST['kd_supplier'],
                        "kd_satuan_besar" => $_POST['kd_satuan_besar'],
                        "qty_besar" => $_POST['qty_besar'],
                        "kd_satuan_kecil" => $_POST['kd_satuan_kecil'],
                        "qty_kecil" => $_POST['qty_kecil'],
                        "is_stok" => $_POST['is_stok'],
                        "foto_barang" => $_POST['old_foto_barang'],
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
                    $barangs = json_decode(file_get_contents($apiUrl . '/' . $id, false, $context));
                    header("Location: ?page=barang");
                }
            }

            // User permission untuk delete form ini
            if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['delete'])) {

                if ($permission->is_delete == 0) {
                    echo "<script type='text/javascript'>";
                    echo "alert('Anda tidak memiliki hak untuk menghapus data!');";
                    echo "window.location.href = '?page=barang';";
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
                $barangs = json_decode(file_get_contents($apiUrl . '/' . $id, false, $context));
                header("Location: ?page=barang");
            }
        }
    }

    // pencarian data barang
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['search'])) {
        $searchQuery = $_POST['cari'];
        $url=$apiUrl . '?q=' . urlencode($searchQuery);
        $options = array(
            'http' => array(
                'method'  => 'GET',
            ),
        );
        $context  = stream_context_create($options);
        $barangs = json_decode(file_get_contents($url, false, $context));
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

    // Data kategori
    $apiUrlKategori= "http://localhost:3000/kategoris";
    $urlKategori=$apiUrlKategori.'?q=';
    $optKategori=array(
        'http'=>array(
            'method'=>'GET',
        ),
    );
    $contKategori=stream_context_create($optKategori);
    $Kategoris = json_decode(file_get_contents($urlKategori, false, $contKategori));

    // Data Satuan
    $apiUrlSatuan= "http://localhost:3000/satuans";
    $urlSatuan=$apiUrlSatuan.'?q=';
    $optSatuan=array(
        'http'=>array(
            'method'=>'GET',
        ),
    );
    $contSatuan=stream_context_create($optSatuan);
    $Satuans = json_decode(file_get_contents($urlSatuan, false, $contSatuan));

    $i=1;
?>
<style>
    @media only screen and (max-width: 768px) {
        .styled-table thead tr th:nth-child(4),
        .styled-table tbody tr td:nth-child(4) {
            width: 20%; /* Tentukan lebar kolom ke-4, misalnya 150px */
        }

        /* Sticky dan styling lainnya tetap sama seperti sebelumnya */
        .styled-table thead tr th:nth-child(1),
        .styled-table thead tr th:nth-child(2),
        .styled-table thead tr th:nth-child(3),
        .styled-table thead tr th:nth-child(4) {
            position: sticky;
            left: 0;
            background-color: #4b2e83;
            z-index: 2;
            box-shadow: 2px 0px 5px rgba(0,0,0,0.1);
        }

        .styled-table tbody tr td:nth-child(1),
        .styled-table tbody tr td:nth-child(2),
        .styled-table tbody tr td:nth-child(3),
        .styled-table tbody tr td:nth-child(4) {
            position: sticky;
            left: 0;
            background-color: white;
            z-index: 2;
            box-shadow: 2px 0px 5px rgba(0,0,0,0.1);
        }

        /* Sesuaikan jarak left untuk sticky sesuai lebar kolom */
        .styled-table thead tr th:nth-child(2),
        .styled-table tbody tr td:nth-child(2) {
            left: 25px;
        }
        .styled-table thead tr th:nth-child(3),
        .styled-table tbody tr td:nth-child(3) {
            left: 25px;
        }
        .styled-table thead tr th:nth-child(4),
        .styled-table tbody tr td:nth-child(4) {
            left: 25px;
            width: 20%; /* pastikan lebar kolom ke-4 sesuai */
        }
        
    }
    .form-select {
            font-family: Poppins, sans-serif; /* Atur font sesuai keinginan */
            font-size: 12px; /* Ukuran font */
        }

    #foto_barang:focus + label {
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

<section id="barang">
    <div class="header fixed-top">
        <div class="left">
            <a href="?page=home"><img src="assets/images/arrow-left.svg" alt="Back"></a>
        </div>
        <div class="center">
            <judul>Daftar Barang</judul>
        </div>
        <div class="right">
            <a href="" data-toggle="modal" data-target="#NewModal"><img src="assets/images/plus-lg.svg" alt="Tambah"></a>
            <a href="" data-toggle="modal" data-target="#CariModal"><img src="assets/images/search.svg" alt="Cari"></a>
            <a href="?page=barang"><img src="assets/images/arrow-clockwise.svg" alt="Hapus"></a>
        </div>
    </div>
    <div class="container-t45">
    </div>
    <div class="table-container">
        <table class="styled-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Foto</th>
                    <th>Kode</th>
                    <th width='20px'>Nama Barang</th>
                    <th>Kategori</th>
                    <th>Supplier</th>
                    <th>Satuan (B)</th>
                    <th>Jumlah (B)</th>
                    <th>Satuan (K)</th>
                    <th>Jumlah (K)</th>
                    <th>Stok?</th>
                    <th style="width:80px;">Perintah</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($barangs as $barang): ?>
                <tr>
                    <td label="#"><?php echo $i++; ?></td>
                    <td label="Foto">
                        <img src="assets/images/barang/<?php if ($barang->foto_barang=='') {echo 'foto_blank.jpg';} else {echo $barang->foto_barang;} ?>" style="border-radius: 50%; width: 50px; height: 50px;" >
                    </td>
                    <td label="Kode"><?php echo $barang->kd_barang; ?></td>
                    <td label="Nama barang" width='20px'><?php echo $barang->nm_barang; ?></td>
                    <td label="Kategori"><?php echo $barang->nm_kategori; ?></td>
                    <td label="Supplier"><?php echo $barang->nm_supplier; ?></a></td>
                    <td label="Satuan (B)"><?php echo $barang->kd_satuan_besar; ?></td>
                    <td label="Jumlah (B)"><?php echo $barang->qty_besar; ?></td>
                    <td label="Satuan (K)"><?php echo $barang->kd_satuan_kecil; ?></td>
                    <td label="Jumlah (K)"><?php echo $barang->qty_kecil; ?></td>
                    <td label="Stok?"><?php if ($barang->is_stok==-1) {echo 'Yes';} else {echo 'No';}; ?></td>
                    <td label="Perintah" style="width:80px;">
                        <!-- Update Link -->
                        <a class="btn btn-basic btn-sm" 
                            data-id_barang="<?php echo $barang->id_barang; ?>" 
                            data-kd_barang="<?php echo $barang->kd_barang; ?>" 
                            data-nm_barang="<?php echo $barang->nm_barang; ?>" 
                            data-kd_kategori="<?php echo $barang->kd_kategori; ?>" 
                            data-kd_supplier="<?php echo $barang->kd_supplier; ?>" 
                            data-kd_satuan_besar="<?php echo $barang->kd_satuan_besar; ?>" 
                            data-qty_besar="<?php echo $barang->qty_besar; ?>" 
                            data-kd_satuan_kecil="<?php echo $barang->kd_satuan_kecil; ?>" 
                            data-qty_kecil="<?php echo $barang->qty_kecil; ?>" 
                            data-is_stok="<?php echo $barang->is_stok; ?>" 
                            data-foto_barang="<?php echo $barang->foto_barang; ?>" 
                            onclick="
                                $('#EditModal').modal('show');
                                $('#edit-id_barang').val($(this).data('id_barang'));
                                $('#edit-kd_barang').val($(this).data('kd_barang'));
                                $('#edit-nm_barang').val($(this).data('nm_barang'));
                                $('#edit-kd_kategori').val($(this).data('kd_kategori'));
                                $('#edit-kd_supplier').val($(this).data('kd_supplier'));
                                $('#edit-kd_satuan_besar').val($(this).data('kd_satuan_besar'));
                                $('#edit-qty_besar').val($(this).data('qty_besar'));
                                $('#edit-kd_satuan_kecil').val($(this).data('kd_satuan_kecil'));
                                $('#edit-qty_kecil').val($(this).data('qty_kecil'));
                                $('#edit-is_stok').val($(this).data('is_stok'));
                                $('#old_foto_barang').val($(this).data('foto_barang'));
                                var fotoBarang= $(this).data('foto_barang');
                                if (fotoBarang) {
                                    $('#editview-foto_barang').attr('src','assets/images/barang/'+ fotoBarang +'');
                                    $('#edit-imagePreview').css('display','block');
                                } else {
                                    $('#editview-foto_barang').attr('src','');
                                    $('#edit-imagePreview').css('display','none');
                                }
                            ">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-vector-pen" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M10.646.646a.5.5 0 0 1 .708 0l4 4a.5.5 0 0 1 0 .708l-1.902 1.902-.829 3.313a1.5 1.5 0 0 1-1.024 1.073L1.254 14.746 4.358 4.4A1.5 1.5 0 0 1 5.43 3.377l3.313-.828zm-1.8 2.908-3.173.793a.5.5 0 0 0-.358.342l-2.57 8.565 8.567-2.57a.5.5 0 0 0 .34-.357l.794-3.174-3.6-3.6z"/>
                                <path fill-rule="evenodd" d="M2.832 13.228 8 9a1 1 0 1 0-1-1l-4.228 5.168-.026.086z"/>
                            </svg>
                            <!--<img src="assets/images/vector-pen.svg" alt="Edit data">-->
                        </a>
                        <!-- Delete Link --> 
                        <a class="btn btn-basic btn-sm" href="?page=barang&delete=<?php echo $barang->id_barang; ?>" onclick="return confirm('Apakah anda yakin akan menghapus data <?php echo $barang->nm_barang; ?> ini?');">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash3" viewBox="0 0 16 16">
                                <path d="M6.5 1h3a.5.5 0 0 1 .5.5v1H6v-1a.5.5 0 0 1 .5-.5M11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3A1.5 1.5 0 0 0 5 1.5v1H1.5a.5.5 0 0 0 0 1h.538l.853 10.66A2 2 0 0 0 4.885 16h6.23a2 2 0 0 0 1.994-1.84l.853-10.66h.538a.5.5 0 0 0 0-1zm1.958 1-.846 10.58a1 1 0 0 1-.997.92h-6.23a1 1 0 0 1-.997-.92L3.042 3.5zm-7.487 1a.5.5 0 0 1 .528.47l.5 8.5a.5.5 0 0 1-.998.06L5 5.03a.5.5 0 0 1 .47-.53Zm5.058 0a.5.5 0 0 1 .47.53l-.5 8.5a.5.5 0 1 1-.998-.06l.5-8.5a.5.5 0 0 1 .528-.47M8 4.5a.5.5 0 0 1 .5.5v8.5a.5.5 0 0 1-1 0V5a.5.5 0 0 1 .5-.5"/>
                            </svg>
                            <!--<img src="assets/images/trash3.svg" alt="Hapus data">-->
                        </a>
                    </td>
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
                        <h6 class="modal-title" id="addModalLabel">Tambah Barang</h6>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" class="form-control" id="useraktif" name="useraktif" value="<?php echo $useraktif; ?>" required>
                        <div class="form-floating mt-1 mb-2">
                            <input type="text" class="form-control" id="nm_barang" name="nm_barang" placeholder="Nama barang" required>
                            <label for="nm_barang">Nama barang</label>
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
                        <div class="row">
                            <div class="col-8">
                                <div class="form-floating mt-1 mb-1">
                                    <select type="text" class="form-select" id="kd_kategori" name="kd_kategori" placeholder="Nama Kategori" required>
                                        <option value='' selected>Pilih Kategori</option>
                                        <?php foreach ($Kategoris as $kategori) { ?>
                                            <option value="<?php echo $kategori->kd_kategori; ?>"><?php echo $kategori->nm_kategori; ?></option>
                                        <?php } ?>
                                    </select>
                                    <label for="kd_kategori">Nama Kategori</label>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-floating mt-1 mb-1">
                                    <select type="text" class="form-select" id="is_stok" name="is_stok" placeholder="Apakah masuk stok">
                                        <option value='-1' selected>Yes</option>
                                        <option value='0'>No</option>
                                    </select>
                                    <label for="is_stok">Stok?</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <div class="form-floating mt-1 mb-1">
                                    <select type="text" class="form-select" id="kd_satuan_besar" name="kd_satuan_besar" placeholder="Satuan (B)" >
                                        <option value='' selected>Pilih Satuan</option>
                                        <?php foreach ($Satuans as $satuan) { ?>
                                            <option value="<?php echo $satuan->kd_satuan; ?>"><?php echo $satuan->nm_satuan; ?></option>
                                        <?php } ?>
                                    </select>
                                    <label for="kd_satuan_besar">Satuan (B)</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-floating mt-1 mb-1">
                                    <input type="text" class="form-control" id="qty_besar" name="qty_besar" placeholder="Jumlah Satuan (B)" value='0.0000'>
                                    <label for="qty_besar">Jumlah (B)</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <div class="form-floating mt-1 mb-1">
                                    <select type="text" class="form-select" id="kd_satuan_kecil" name="kd_satuan_kecil" placeholder="Satuan (K)" >
                                        <option value='' selected>Pilih Satuan</option>
                                        <?php foreach ($Satuans as $satuan) { ?>
                                            <option value="<?php echo $satuan->kd_satuan; ?>"><?php echo $satuan->nm_satuan; ?></option>
                                        <?php } ?>
                                    </select>
                                    <label for="kd_satuan_kecil">Satuan (K)</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-floating mt-1 mb-1">
                                    <input type="text" class="form-control" id="qty_kecil" name="qty_kecil" placeholder="Jumlah Satuan (K)" value='0.0000'>
                                    <label for="qty_kecil">Jumlah (K)</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-floating mt-1 mb-1">
                            <!-- Input file disembunyikan 
                            <input type="file" class="form-control" id="foto_barang" name="foto_barang" placeholder="Foto barang" accept="image/*">-->

                            <input type="file" class="form-control d-none" id="foto_barang" name="foto_barang" placeholder="Foto barang" accept="image/*" onchange="
                                const preview = document.getElementById('view-foto_barang');
                                const imagePreview = document.getElementById('imagePreview');
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
                            <button class="btn btn-primary mt-2" type="button" onclick="document.getElementById('foto_barang').click()">Pilih Foto</button> 

                            <!-- Area pratinjau gambar -->
                            <div id="imagePreview" class="mt-3" style="display:none;">
                                <p>Pratinjau Gambar:</p>
                                <img id="view-foto_barang" src="" alt="Foto Barang" style="max-width: 200px; border-radius: 8px;">
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
                        <h6 class="modal-title" id="addModalLabel">Edit Barang</h6>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" class="form-control" id="edit-useraktif" name="useraktif" value="<?php echo $useraktif; ?>" required>
                        <input type="hidden" class="form-control" id="edit-id_barang" name="id_barang" placeholder="ID barang" required>
                        <div class="form-floating mt-1 mb-2">
                            <input type="text" class="form-control" id="edit-kd_barang" name="kd_barang" placeholder="Nama barang" required>
                            <label for="edit-kd_barang">Kode Barang</label>
                        </div>
                        <div class="form-floating mt-1 mb-2">
                            <input type="text" class="form-control" id="edit-nm_barang" name="nm_barang" placeholder="Nama barang" required>
                            <label for="edit-nm_barang">Nama Barang</label>
                        </div>
                        <div class="form-floating mt-1 mb-1">
                            <select type="text" class="form-select" id="edit-kd_supplier" name="kd_supplier" placeholder="Nama Supplier" required>
                                <?php foreach ($Suppliers as $supplier) { ?>
                                    <option value="<?php echo $supplier->kd_supplier; ?>"><?php echo $supplier->nm_supplier; ?></option>
                                <?php } ?>
                            </select>
                            <label for="edit-kd_supplier">Nama Supplier</label>
                        </div>
                        <div class="row">
                            <div class="col-8">
                                <div class="form-floating mt-1 mb-1">
                                    <select type="text" class="form-select" id="edit-kd_kategori" name="kd_kategori" placeholder="Nama Kategori" required>
                                        <?php foreach ($Kategoris as $kategori) { ?>
                                            <option value="<?php echo $kategori->kd_kategori; ?>"><?php echo $kategori->nm_kategori; ?></option>
                                        <?php } ?>
                                    </select>
                                    <label for="edit-kd_kategori">Nama Kategori</label>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-floating mt-1 mb-1">
                                    <select type="text" class="form-select" id="edit-is_stok" name="is_stok" placeholder="Apakah masuk stok">
                                        <option value='-1'>Yes</option>
                                        <option value='0'>No</option>
                                    </select>
                                    <label for="edit-is_stok">Stok?</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <div class="form-floating mt-1 mb-1">
                                    <select type="text" class="form-select" id="edit-kd_satuan_besar" name="kd_satuan_besar" placeholder="Satuan (B)" >
                                        <?php foreach ($Satuans as $satuan) { ?>
                                            <option value="<?php echo $satuan->kd_satuan; ?>"><?php echo $satuan->nm_satuan; ?></option>
                                        <?php } ?>
                                    </select>
                                    <label for="edit-kd_satuan_besar">Satuan (B)</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-floating mt-1 mb-1">
                                    <input type="text" class="form-control" id="edit-qty_besar" name="qty_besar" placeholder="Jumlah Satuan (B)">
                                    <label for="edit-qty_besar">Jumlah (B)</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <div class="form-floating mt-1 mb-1">
                                    <select type="text" class="form-select" id="edit-kd_satuan_kecil" name="kd_satuan_kecil" placeholder="Satuan (K)" >
                                        <?php foreach ($Satuans as $satuan) { ?>
                                            <option value="<?php echo $satuan->kd_satuan; ?>"><?php echo $satuan->nm_satuan; ?></option>
                                        <?php } ?>
                                    </select>
                                    <label for="edit-kd_satuan_kecil">Satuan (K)</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-floating mt-1 mb-1">
                                    <input type="text" class="form-control" id="edit-qty_kecil" name="qty_kecil" placeholder="Jumlah Satuan (K)">
                                    <label for="edit-qty_kecil">Jumlah (K)</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-floating mt-1 mb-1">
                            <!-- Input file disembunyikan -->
                            <input type="file" class="form-control d-none" id="edit-foto_barang" name="foto_barang" placeholder="Foto barang" accept="image/*" onchange="
                                const preview = document.getElementById('editview-foto_barang');
                                const imagePreview = document.getElementById('edit-imagePreview');
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
                            <input type="text" class="form-control d-none" id="old_foto_barang" name="old_foto_barang">

                            <!-- Tombol kustom untuk memilih file -->
                            <button class="btn btn-primary mt-2" type="button" onclick="document.getElementById('edit-foto_barang').click()">Pilih Foto</button>

                            <!-- Area pratinjau gambar -->
                            <div id="edit-imagePreview" class="mt-3" style="display:none;">
                                <p>Pratinjau Gambar:</p>
                                <img id="editview-foto_barang" alt="Foto Barang" style="max-width: 200px; border-radius: 8px;">
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
                        <h6 class="modal-title" id="addModalLabel">Cari Barang</h6>
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
