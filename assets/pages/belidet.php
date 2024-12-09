<?php
    $apiUrl = "http://192.168.194.50:3000/belidets";
    $apiUrlPerm = "http://192.168.194.50:3000/permissions";
    $nobeli=$_GET['nobeli'];
    
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
    $menuname='form_belidet';
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

                    $searchQuery = $nobeli;
                    $url=$apiUrl . '?q=' . urlencode($searchQuery);
                    $options = array(
                        'http' => array(
                            'method'  => 'GET',
                        ),
                    );
                    $context  = stream_context_create($options);
                    $belidets = json_decode(file_get_contents($url, false, $context));
                }
            }

            // User permission untuk addnew form ini
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['insert'])) {

                if ($permission->is_addnew == 0) {
                    echo "<script type='text/javascript'>";
                    echo "alert('Anda tidak memiliki hak untuk menambahkan data!');";
                    echo "window.location.href = '?page=belidet&nobeli=".$nobeli."';";
                    echo "</script>";
                    exit();
                }

                // Handle Create
                $data = array(
                    "no_beli" => $nobeli,
                    "kd_barang" => $_POST['kd_barang'],
                    "qty_beli" => $_POST['qty_beli'],
                    "harga_beli" => $_POST['harga_beli'],
                    "total_beli" => $_POST['total_beli'],
                    "kd_perusahaan" => $_POST['kd_perusahaan'],
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
                $belidets=json_decode(file_get_contents($apiUrl, false, $context));
                header("Location: ?page=belidet&nobeli=".$nobeli);
                
            }

            // User permission untuk update form ini
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {

                if ($permission->is_update == 0) {
                    echo "<script type='text/javascript'>";
                    echo "alert('Anda tidak memiliki hak untuk mengupdate data!');";
                    echo "window.location.href = '?page=belidet&nobeli=".$nobeli."';";
                    echo "</script>";
                    exit();
                }

                // Handle Update
                $id = $_POST['id_beli_detail'];
                $data = array(
                    "no_beli" => $nobeli,
                    "kd_barang" => $_POST['kd_barang'],
                    "qty_beli" => $_POST['qty_beli'],
                    "harga_beli" => $_POST['harga_beli'],
                    "total_beli" => $_POST['total_beli'],
                    "kd_perusahaan" => $_POST['kd_perusahaan'],
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
                $belidets = json_decode(file_get_contents($apiUrl . '/' . $id, false, $context));
                header("Location: ?page=belidet&nobeli=".$nobeli);
                    
            }

            // User permission untuk delete form ini
            if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['delete'])) {

                if ($permission->is_delete == 0) {
                    echo "<script type='text/javascript'>";
                    echo "alert('Anda tidak memiliki hak untuk menghapus data!');";
                    echo "window.location.href = '?page=belidet';";
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
                $belidets = json_decode(file_get_contents($apiUrl . '/' . $id, false, $context));
                header("Location: ?page=belidet&nobeli=".$nobeli);
            }
        }
    }


    // Data barang
    $apiUrlBarang= "http://localhost:3000/barangs";
    $urlBarang=$apiUrlBarang.'?q=';
    $optBarang=array(
        'http'=>array(
            'method'=>'GET',
        ),
    );
    $contBarang=stream_context_create($optBarang);
    $Barangs = json_decode(file_get_contents($urlBarang, false, $contBarang));

    // Data perusahaan
    $apiUrlPerusahaan= "http://localhost:3000/perusahaans";
    $urlPerusahaan=$apiUrlPerusahaan.'?q=';
    $optPerusahaan=array(
        'http'=>array(
            'method'=>'GET',
        ),
    );
    $contPerusahaan=stream_context_create($optPerusahaan);
    $Perusahaans = json_decode(file_get_contents($urlPerusahaan, false, $contPerusahaan));

    $i=1;
    $totBeli=0;
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

<section id="beli">
    <div class="header fixed-top">
        <div class="left">
            <a href="?page=beli&nobeli=<?php echo $_GET['nobeli']; ?>"><img src="assets/images/arrow-left.svg" alt="Back"></a>
        </div>
        <div class="center">
            <judul>Pembelian : <?php echo $nobeli; ?></judul>
        </div>
        <div class="right">
            <a href="" data-toggle="modal" data-target="#NewModal"><img src="assets/images/plus-lg.svg" alt="Tambah"></a>
            <!--<a href="" data-toggle="modal" data-target="#CariModal"><img src="assets/images/search.svg" alt="Cari"></a>-->
            <a href="?page=belidet&nobeli=<?php echo $_GET['nobeli']; ?>"><img src="assets/images/arrow-clockwise.svg" alt="Refresh"></a>
        </div>
    </div>
    <div class="container-t45">
    </div>
    <div class="table-container">
        <table class="styled-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Kode</th>
                    <th>Nama Barang</th>
                    <th style="text-align:right;">Jumlah</th>
                    <th style="text-align:right;">Harga</th>
                    <th style="text-align:right;">Total</th>
                    <th>Perusahaan</th>
                    <th style="width:80px;">Perintah</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($belidets as $belidet): ?>
                    <tr>
                        <td label="#"><?php echo $i++; ?></td>
                        <td label="Kode"><?php echo $belidet->kd_barang; ?></td>
                        <td label="Nama Barang"><?php echo $belidet->nm_barang; ?></a></td>
                        <td label="Jumlah" style="text-align:right;"><?php echo number_format($belidet->qty_beli,2); ?></td>
                        <td label="Harga" style="text-align:right;"><?php echo number_format($belidet->harga_beli,2); ?></td>
                        <td label="Total" style="text-align:right;"><?php echo number_format($belidet->total_beli,2); ?></td>
                        <td label="Perusahaan"><?php echo $belidet->nm_perusahaan; ?></td>
                        <td label="Perintah" style="width:80px;">
                        
                            <!-- Update Link -->
                            <a class="btn btn-basic btn-sm" 
                                data-id_beli_detail="<?php echo $belidet->id_beli_detail; ?>" 
                                data-no_beli="<?php echo $belidet->no_beli; ?>" 
                                data-kd_barang="<?php echo $belidet->kd_barang; ?>"  
                                data-qty_beli="<?php echo number_format($belidet->qty_beli,2); ?>" 
                                data-harga_beli="<?php echo number_format($belidet->harga_beli,2); ?>" 
                                data-total_beli="<?php echo number_format($belidet->total_beli,2); ?>" 
                                data-kd_perusahaan="<?php echo $belidet->kd_perusahaan; ?>"  
                                onclick="
                                    $('#EditModal').modal('show');
                                    $('#edit-id_beli_detail').val($(this).data('id_beli_detail'));
                                    $('#edit-no_beli').val($(this).data('no_beli'));
                                    $('#edit-kd_barang').val($(this).data('kd_barang'));
                                    $('#edit-qty_beli').val($(this).data('qty_beli'));
                                    $('#edit-harga_beli').val($(this).data('harga_beli'));
                                    $('#edit-total_beli').val($(this).data('total_beli'));
                                    $('#edit-kd_perusahaan').val($(this).data('kd_perusahaan'));
                                    
                                ">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-vector-pen" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd" d="M10.646.646a.5.5 0 0 1 .708 0l4 4a.5.5 0 0 1 0 .708l-1.902 1.902-.829 3.313a1.5 1.5 0 0 1-1.024 1.073L1.254 14.746 4.358 4.4A1.5 1.5 0 0 1 5.43 3.377l3.313-.828zm-1.8 2.908-3.173.793a.5.5 0 0 0-.358.342l-2.57 8.565 8.567-2.57a.5.5 0 0 0 .34-.357l.794-3.174-3.6-3.6z"/>
                                  <path fill-rule="evenodd" d="M2.832 13.228 8 9a1 1 0 1 0-1-1l-4.228 5.168-.026.086z"/>
                                </svg>
                                <!--<img src="assets/images/vector-pen.svg" alt="Edit data">-->
                            </a>

                            <!-- Delete Link --> 
                            <a class="btn btn-basic btn-sm" href="?page=belideti&delete=<?php echo $belidet->id_beli_detail; ?>" onclick="return confirm('Apakah anda yakin akan menghapus data <?php echo $belidet->nm_barang; ?> ini?');">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash3" viewBox="0 0 16 16">
                                    <path d="M6.5 1h3a.5.5 0 0 1 .5.5v1H6v-1a.5.5 0 0 1 .5-.5M11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3A1.5 1.5 0 0 0 5 1.5v1H1.5a.5.5 0 0 0 0 1h.538l.853 10.66A2 2 0 0 0 4.885 16h6.23a2 2 0 0 0 1.994-1.84l.853-10.66h.538a.5.5 0 0 0 0-1zm1.958 1-.846 10.58a1 1 0 0 1-.997.92h-6.23a1 1 0 0 1-.997-.92L3.042 3.5zm-7.487 1a.5.5 0 0 1 .528.47l.5 8.5a.5.5 0 0 1-.998.06L5 5.03a.5.5 0 0 1 .47-.53Zm5.058 0a.5.5 0 0 1 .47.53l-.5 8.5a.5.5 0 1 1-.998-.06l.5-8.5a.5.5 0 0 1 .528-.47M8 4.5a.5.5 0 0 1 .5.5v8.5a.5.5 0 0 1-1 0V5a.5.5 0 0 1 .5-.5"/>
                                </svg>
                                <!--<img src="assets/images/trash3.svg" alt="Hapus data">-->
                            </a>
                            
                        </td>
                    </tr>
                <?php
                    $totBeli = $totBeli + $belidet->total_beli;
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
                    <th style="text-align:right;"><?php echo number_format($totBeli,2); ?></th>
                    <th></th>
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
                        <h6 class="modal-title" id="addModalLabel">Tambah Beli Barang</h6>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" class="form-control" id="useraktif" name="useraktif" value="<?php echo $useraktif; ?>" required>
                        <input type="hidden" class="form-control" id="no_beli" name="no_beli" placeholder="No Beli" value="<?php echo $_GET['nobeli']; ?>" required>
                        <div class="form-floating mt-1 mb-1">
                            <select type="text" class="form-select" id="kd_barang" name="kd_barang" placeholder="Nama Barang" required>
                                <option value='' selected>Pilih Barang</option>
                                <?php foreach ($Barangs as $barang) { ?>
                                    <option value="<?php echo $barang->kd_barang; ?>"><?php echo $barang->nm_barang; ?></option>
                                <?php } ?>
                            </select>
                            <label for="kd_barang">Nama Barang</label>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <div class="form-floating mt-1 mb-1">
                                    <input type="text" class="form-control" id="qty_beli" name="qty_beli" value="0" placeholder="Jumlah" required>
                                    <label for="qty_beli">Jumlah</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-floating mt-1 mb-1">
                                    <input type="text" class="form-control" id="harga_beli" name="harga_beli" value="0" placeholder="Harga" required>
                                    <label for="harga_beli">Harga</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-floating mt-1 mb-2">
                            <input type="text" class="form-control" id="total_beli" name="total_beli" value="0" placeholder="Total Beli" required>
                            <label for="total_beli">Total Beli</label>
                        </div>
                        <div class="form-floating mt-1 mb-1">
                            <select type="text" class="form-select" id="kd_perusahaan" name="kd_perusahaan" placeholder="Nama Perusahaan" required>
                                <option value='' selected>Pilih Perusahaan</option>
                                <?php foreach ($Perusahaans as $perusahaan) { ?>
                                    <option value="<?php echo $perusahaan->kd_perusahaan; ?>"><?php echo $perusahaan->nm_perusahaan; ?></option>
                                <?php } ?>
                            </select>
                            <label for="kd_perusahaan">Nama Perusahaan</label>
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
                        <h6 class="modal-title" id="addModalLabel">Edit Beli Barang</h6>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" class="form-control" id="edit-useraktif" name="useraktif" value="<?php echo $useraktif; ?>" required>
                        <input type="hidden" class="form-control" id="edit-id_beli_detail" name="id_beli_detail" placeholder="ID beli" required>
                        <input type="hidden" class="form-control" id="edit-no_beli" name="no_beli" placeholder="No Beli" value="<?php echo $nobeli; ?>" required>
                        <div class="form-floating mt-1 mb-1">
                            <select type="text" class="form-select" id="edit-kd_barang" name="kd_barang" placeholder="Nama Barang" required>
                                <?php foreach ($Barangs as $barang) { ?>
                                    <option value="<?php echo $barang->kd_barang; ?>"><?php echo $barang->nm_barang; ?></option>
                                <?php } ?>
                            </select>
                            <label for="edit-kd_barang">Nama Barang</label>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <div class="form-floating mt-1 mb-1">
                                    <input type="text" class="form-control" id="edit-qty_beli" name="qty_beli" placeholder="Jumlah" required>
                                    <label for="edit-qty_beli">Jumlah</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-floating mt-1 mb-1">
                                    <input type="text" class="form-control" id="edit-harga_beli" name="harga_beli" placeholder="Harga" required>
                                    <label for="edit-harga_beli">Harga</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-floating mt-1 mb-2">
                            <input type="text" class="form-control" id="edit-total_beli" name="total_beli" placeholder="Total Beli" required>
                            <label for="edit-total_beli">Total Beli</label>
                        </div>
                        <div class="form-floating mt-1 mb-1">
                            <select type="text" class="form-select" id="edit-kd_perusahaan" name="kd_perusahaan" placeholder="Nama Perusahaan" required>
                                <?php foreach ($Perusahaans as $perusahaan) { ?>
                                    <option value="<?php echo $perusahaan->kd_perusahaan; ?>"><?php echo $perusahaan->nm_perusahaan; ?></option>
                                <?php } ?>
                            </select>
                            <label for="edit-kd_perusahaan">Nama Perusahaan</label>
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
                        <h6 class="modal-title" id="addModalLabel">Cari beli</h6>
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
