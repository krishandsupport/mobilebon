<?php
    $apiUrl = "http://192.168.194.50:3000/bayardets";
    $apiUrlPerm = "http://192.168.194.50:3000/permissions";
    $nobayar=$_GET['nobayar'];
    $idbyr=$_GET['idbyr'];
    $nm_sup=$_GET['nm_sup'];
    
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
    $menuname='form_bayardet';
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

                    $searchQuery = $nobayar;
                    $url=$apiUrl . '?q=' . urlencode($searchQuery);
                    $options = array(
                        'http' => array(
                            'method'  => 'GET',
                        ),
                    );
                    $context  = stream_context_create($options);
                    $bayardets = json_decode(file_get_contents($url, false, $context));
                }
            }

            // User permission untuk addnew form ini
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['insert'])) {

                if ($permission->is_addnew == 0) {
                    echo "<script type='text/javascript'>";
                    echo "alert('Anda tidak memiliki hak untuk menambahkan data!');";
                    echo "window.location.href = '?page=belidet&nobeli=".$nobayar."';";
                    echo "</script>";
                    exit();
                }

                // Handle Create
                $data = array(
                    "no_bayar" => $nobayar,
                    "no_beli" => $_POST['no_beli'],
                    "nilai_bayar" => $_POST['nilai_bayar'],
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
                $bayardets=json_decode(file_get_contents($apiUrl, false, $context));
                header("Location: ?page=bayardet&nobayar=".$nobayar."&idbyr=".$idbyr."&nm_sup=".$nm_sup);
                
            }

            // User permission untuk update form ini
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {

                if ($permission->is_update == 0) {
                    echo "<script type='text/javascript'>";
                    echo "alert('Anda tidak memiliki hak untuk mengupdate data!');";
                    echo "window.location.href = '?page=belidet&nobeli=".$nobayar."';";
                    echo "</script>";
                    exit();
                }

                // Handle Update
                $id = $_POST['id_bayar_detail'];
                $data = array(
                    "no_bayar" => $nobayar,
                    "no_beli" => $_POST['no_beli'],
                    "nilai_bayar" => $_POST['nilai_bayar'],
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
                $bayardets = json_decode(file_get_contents($apiUrl . '/' . $id, false, $context));
                header("Location: ?page=bayardet&nobayar=".$nobayar."&idbyr=".$idbyr."&nm_sup=".$nm_sup);
                    
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
                $bayardets = json_decode(file_get_contents($apiUrl . '/' . $id, false, $context));
                header("Location: ?page=bayardet&nobayar=".$nobayar."&idbyr=".$idbyr."&nm_sup=".$nm_sup);
            }
        }
    }


    // Data beli
    $apiUrlBeli= "http://localhost:3000/belis";
    $urlBeli=$apiUrlBeli.'?q='.urlencode($nm_sup);
    $optBeli=array(
        'http'=>array(
            'method'=>'GET',
        ),
    );
    $contBeli=stream_context_create($optBeli);
    $Belis = json_decode(file_get_contents($urlBeli, false, $contBeli));

    $i=1;
    $totBeli=0;
    $totBayar=0;
    $sisaHutang=0;
?>

<style>
    .form-select {
            font-family: Poppins, sans-serif; /* Atur font sesuai keinginan */
            font-size: 12px; /* Ukuran font */
        }


    .styled-table thead tr th:nth-child(1),
    .styled-table thead tr th:nth-child(2) {
        position: sticky;
        left: 0; /* posisi kolom tetap di kiri */
        background-color: #4b2e83;  /*latar belakang supaya tidak tertimpa elemen lainnya */
        z-index: 1; /* agar berada di atas elemen lain */
        box-shadow: 2px 0px 5px rgba(0,0,0,0.1); /* bayangan kecil untuk memisahkan */
    }
    
    .styled-table tbody tr td:nth-child(1),
    .styled-table tbody tr td:nth-child(2) {
        position: sticky;
        left: 0; /* posisi kolom tetap di kiri */
        background-color: white;  /*latar belakang supaya tidak tertimpa elemen lainnya */
        z-index: 1; /* agar berada di atas elemen lain */
        box-shadow: 2px 0px 5px rgba(0,0,0,0.1); /* bayangan kecil untuk memisahkan */
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
            <a href="?page=bayar&nobayar=<?php echo $_GET['nobayar']; ?>"><img src="assets/images/arrow-left.svg" alt="Back"></a>
        </div>
        <div class="center">
            <judul>Pembayaran : <?php echo $nobayar; ?></judul>
        </div>
        <div class="right">
            <a href="" data-toggle="modal" data-target="#NewModal"><img src="assets/images/plus-lg.svg" alt="Tambah"></a>
            <!--<a href="" data-toggle="modal" data-target="#CariModal"><img src="assets/images/search.svg" alt="Cari"></a>-->
            <a href="?page=bayardet&nobayar=<?php echo $nobayar; ?>&idbyr=<?php echo $idbyr; ?>&nm_sup=<?php echo $nm_sup; ?>"><img src="assets/images/arrow-clockwise.svg" alt="Refresh"></a>
        </div>
    </div>
    <div class="container-t45">
    </div>
    <div class="table-container">
        <table class="styled-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>No Beli</th>
                    <th style="text-align:right;">Total Beli</th>
                    <th style="text-align:right;">Nilai Bayar</th>
                    <th style="text-align:right;">Sisa Hutang</th>
                    <th style="width:80px;">Perintah</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bayardets as $bayardet): ?>
                    <tr>
                        <td label="#"><?php echo $i++; ?></td>
                        <td label="No Beli"><?php echo $bayardet->no_beli; ?></td>
                        <td label="Total Beli" style="text-align:right;"><?php echo number_format($bayardet->total_beli,2); ?></td>
                        <td label="Total Bayar" style="text-align:right;"><?php echo number_format($bayardet->nilai_bayar,2); ?></td>
                        <td label="Sisa Hutang" style="text-align:right;"><?php echo number_format($bayardet->sisa_hutang,2); ?></td>
                        <td label="Perintah" style="width:80px;">
                        
                            <!-- Update Link -->
                            <a class="btn btn-basic btn-sm" 
                                data-id_bayar_detail="<?php echo $bayardet->id_bayar_detail; ?>" 
                                data-no_bayar="<?php echo $bayardet->no_bayar; ?>" 
                                data-no_beli="<?php echo $bayardet->no_beli; ?>"
                                data-total_beli="<?php echo number_format($bayardet->total_beli,2); ?>"  
                                data-nilai_bayar="<?php echo number_format($bayardet->nilai_bayar,2); ?>"
                                data-sisa_hutang="<?php echo number_format($bayardet->sisa_hutang,2); ?>"   
                                onclick="
                                    $('#EditModal').modal('show');
                                    $('#edit-id_bayar_detail').val($(this).data('id_bayar_detail'));
                                    $('#edit-no_bayar').val($(this).data('no_bayar'));
                                    $('#edit-no_beli').val($(this).data('no_beli'));
                                    $('#edit-total_beli').val($(this).data('total_beli'));
                                    $('#edit-nilai_bayar').val($(this).data('nilai_bayar'));
                                    $('#edit-sisa_hutang').val($(this).data('sisa_hutang'));
                                ">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-vector-pen" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd" d="M10.646.646a.5.5 0 0 1 .708 0l4 4a.5.5 0 0 1 0 .708l-1.902 1.902-.829 3.313a1.5 1.5 0 0 1-1.024 1.073L1.254 14.746 4.358 4.4A1.5 1.5 0 0 1 5.43 3.377l3.313-.828zm-1.8 2.908-3.173.793a.5.5 0 0 0-.358.342l-2.57 8.565 8.567-2.57a.5.5 0 0 0 .34-.357l.794-3.174-3.6-3.6z"/>
                                  <path fill-rule="evenodd" d="M2.832 13.228 8 9a1 1 0 1 0-1-1l-4.228 5.168-.026.086z"/>
                                </svg>
                                <!--<img src="assets/images/vector-pen.svg" alt="Edit data">-->
                            </a>

                            <!-- Delete Link --> 
                            <a class="btn btn-basic btn-sm" href="?page=bayardet&delete=<?php echo $bayardet->id_bayar_detail; ?>" onclick="return confirm('Apakah anda yakin akan menghapus data <?php echo $bayardet->no_beli; ?> ini?');">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash3" viewBox="0 0 16 16">
                                    <path d="M6.5 1h3a.5.5 0 0 1 .5.5v1H6v-1a.5.5 0 0 1 .5-.5M11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3A1.5 1.5 0 0 0 5 1.5v1H1.5a.5.5 0 0 0 0 1h.538l.853 10.66A2 2 0 0 0 4.885 16h6.23a2 2 0 0 0 1.994-1.84l.853-10.66h.538a.5.5 0 0 0 0-1zm1.958 1-.846 10.58a1 1 0 0 1-.997.92h-6.23a1 1 0 0 1-.997-.92L3.042 3.5zm-7.487 1a.5.5 0 0 1 .528.47l.5 8.5a.5.5 0 0 1-.998.06L5 5.03a.5.5 0 0 1 .47-.53Zm5.058 0a.5.5 0 0 1 .47.53l-.5 8.5a.5.5 0 1 1-.998-.06l.5-8.5a.5.5 0 0 1 .528-.47M8 4.5a.5.5 0 0 1 .5.5v8.5a.5.5 0 0 1-1 0V5a.5.5 0 0 1 .5-.5"/>
                                </svg>
                                <!--<img src="assets/images/trash3.svg" alt="Hapus data">-->
                            </a>
                            
                        </td>
                    </tr>
                <?php
                    $totBeli = $totBeli + $bayardet->total_beli;
                    $totBayar = $totBayar + $bayardet->nilai_bayar;
                    $sisaHutang = $sisaHutang + $bayardet->sisa_hutang;
                    endforeach; 
                ?>
            </tbody>
            <thead>
                <tr>
                    <th></th>
                    <th>Total</th>
                    <th style="text-align:right;"><?php echo number_format($totBeli,2); ?></th>
                    <th style="text-align:right;"><?php echo number_format($totBayar,2); ?></th>
                    <th style="text-align:right;"><?php echo number_format($sisaHutang,2); ?></th>
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
                        <h6 class="modal-title" id="addModalLabel">Tambah Bayar Pembelian</h6>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" class="form-control" id="useraktif" name="useraktif" value="<?php echo $useraktif; ?>" required>
                        <input type="hidden" class="form-control" id="no_bayar" name="no_bayar" placeholder="No Bayar" value="<?php echo $nobayar; ?>" required>
                        <div class="form-floating mt-1 mb-1">
                            <select type="text" class="form-select" id="no_beli" name="no_beli" placeholder="No Beli" required onchange="update_nobeli()">
                                <option value='' selected>Pilih No Pembelian</option>
                                <?php foreach ($Belis as $beli) { ?>
                                    <option value='<?php echo $beli->no_beli; ?>' data-total_beli='<?php echo $beli->tot_beli; ?>'><?php echo $beli->no_beli; ?></option>
                                <?php } ?>
                            </select>
                            <label for="no_beli">No Pembelian : <?php echo $nm_sup; ?></label>
                        </div>
                        <div class="form-floating mt-1 mb-1">
                            <input type="number" class="form-control" id="total_beli" name="total_beli" value="0" placeholder="Total Beli" required>
                            <label for="tot_beli">Total Beli</label>
                        </div>
                        <div class="form-floating mt-1 mb-1">
                            <input type="number" class="form-control" id="nilai_bayar" name="nilai_bayar" value="0" placeholder="Total Bayar" required>
                            <label for="nilai_bayar">Total Bayar</label>
                        </div>
                        <div class="form-floating mt-1 mb-2">
                            <input type="number" class="form-control" id="sisa_hutang" name="sisa_hutang" value="0" placeholder="Sisa Hutang" required>
                            <label for="sisa_hutang">Sisa Hutang</label>
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
                        <h6 class="modal-title" id="addModalLabel">Edit Bayar Pembelian</h6>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" class="form-control" id="edit-useraktif" name="useraktif" value="<?php echo $useraktif; ?>" required>
                        <input type="hidden" class="form-control" id="edit-id_bayar_detail" name="id_bayar_detail" placeholder="ID bayar detail" required>
                        <input type="hidden" class="form-control" id="edit-no_bayar" name="no_bayar" placeholder="No Bayar" value="<?php echo $nobayar; ?>" required>
                        <div class="form-floating mt-1 mb-1">
                            <select type="text" class="form-select" id="edit-no_beli" name="no_beli" placeholder="No Beli" required onchange="edit_update_nobeli()">
                                <option value='' selected>Pilih No Pembelian</option>
                                <?php foreach ($Belis as $beli) { ?>
                                    <option value='<?php echo $beli->no_beli; ?>' data-total_beli='<?php echo $beli->tot_beli; ?>'><?php echo $beli->no_beli; ?></option>
                                <?php } ?>
                            </select>
                            <label for="edit-no_beli">No Pembelian : <?php echo $nm_sup; ?></label>
                        </div>
                        <div class="form-floating mt-1 mb-1">
                            <input type="text" class="form-control" id="edit-total_beli" name="total_beli" value="0" placeholder="Total Beli" required>
                            <label for="edit-tot_beli">Total Beli</label>
                        </div>
                        <div class="form-floating mt-1 mb-1">
                            <input type="text" class="form-control" id="edit-nilai_bayar" name="nilai_bayar" value="0" placeholder="Total Bayar" required>
                            <label for="edit-nilai_bayar">Total Bayar</label>
                        </div>
                        <div class="form-floating mt-1 mb-2">
                            <input type="text" class="form-control" id="edit-sisa_hutang" name="sisa_hutang" value="0" placeholder="Sisa Hutang" required>
                            <label for="edit-sisa_hutang">Sisa Hutang</label>
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

</section>

<script>
    function update_nobeli() {
        var select = document.getElementById('no_beli');
        var selectedOption = select.options[select.selectedIndex];
        var totBeli = parseFloat(selectedOption.getAttribute('data-total_beli')) || 0;
        document.getElementById('total_beli').value = totBeli;
        document.getElementById('nilai_bayar').value = totBeli;
    }

    function edit_update_nobeli() {
        var select = document.getElementById('edit-no_beli');
        var selectedOption = select.options[select.selectedIndex];
        var totBeli = parseFloat(selectedOption.getAttribute('data-total_beli')) || 0;
        document.getElementById('edit-total_beli').value = totBeli;
        //document.getElementById('edit-nilai_bayar').value = totBeli;
    }
</script>