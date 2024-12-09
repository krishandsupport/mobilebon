<?php
    $apiUrl = "http://192.168.194.50:3000/users";
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
    $menuname='form_user';
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
                    $users = json_decode(file_get_contents($url, false, $context));
                }
            }

            // User permission untuk addnew form ini
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['insert'])) {

                if ($permission->is_addnew == 0) {
                    echo "<script type='text/javascript'>";
                    echo "alert('Anda tidak memiliki hak untuk menambahkan data!');";
                    echo "window.location.href = '?page=user';";
                    echo "</script>";
                    exit();
                }

                // Handle Create
                $data = array(
                    "user_name" => $_POST['user_name'],
                    "password" => md5($_POST['password']),
                    "is_aktif" => $_POST['is_aktif'],
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
                $users=json_decode(file_get_contents($apiUrl, false, $context));
                header("Location: ?page=user");
                    
            }


            // User permission untuk delete form ini
            if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['delete'])) {

                if ($permission->is_delete == 0) {
                    echo "<script type='text/javascript'>";
                    echo "alert('Anda tidak memiliki hak untuk menghapus data!');";
                    echo "window.location.href = '?page=user';";
                    echo "</script>";
                    exit();
                }

                // Handle Delete
                $id = $_GET['delete'];
                if ($_GET['isaktif']==0){
                    $isaktif=1;
                } else {
                    $isaktif=0;
                }
                $options = array(
                    'http' => array(
                        'method'  => 'DELETE',
                    ),
                );
                $context = stream_context_create($options);
                $users = json_decode(file_get_contents($apiUrl . '?delete='. $id.'&isaktif='.$isaktif, false, $context));
                header("Location: ?page=user");
            }
        }
    }

    
    $i=1;
?>

<style>
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
            <a href="?page=home"><img src="assets/images/arrow-left.svg" alt="Back"></a>
        </div>
        <div class="center">
            <judul>Daftar User</judul>
        </div>
        <div class="right">
            <a href="" data-toggle="modal" data-target="#NewModal"><img src="assets/images/plus-lg.svg" alt="Tambah"></a>
            <!--<a href="" data-toggle="modal" data-target="#CariModal"><img src="assets/images/search.svg" alt="Cari"></a>-->
            <a href="?page=user"><img src="assets/images/arrow-clockwise.svg" alt="Refresh"></a>
        </div>
    </div>
    <div class="container-t45">
    </div>
    <div class="table-container">
        <table class="styled-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>User Name</th>
                    <th>Aktif?</th>
                    <th style="width:80px;">Perintah</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td label="#"><?php echo $i++; ?></td>
                        <td label="User Name">
                            <?php echo $user->user_name; ?>
                        </td>
                        <td label="is_aktif" class="center">
                            <?php 
                                if ($user->is_aktif==1) {
                                    echo "
                                        <div class='form-check form-switch'>
                                            <input class='form-check-input' type='checkbox' id='flexSwitchCheckChecked' checked readonly>
                                            <!-- Layer pelindung -->
                                            <div style='position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 10;''></div>
                                        </div>
                                    ";
                                } else {
                                    echo "
                                        <div class='form-check form-switch'>
                                            <input class='form-check-input' type='checkbox' id='flexSwitchCheckChecked' disabled readonly>
                                            <!-- Layer pelindung -->
                                            <div style='position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 10;''></div>
                                        </div>
                                    ";
                                }  
                            ?>
                        </td>
                        <td label="Perintah" style="width:80px;">
                            <!-- Input detail barang Link -->
                            <a class="btn btn-basic btn-sm" href="?page=userp&user=<?php echo $user->user_name; ?>&id=<?php echo $user->id_user; ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-spreadsheet" viewBox="0 0 16 16">
                                    <path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v4h10V2a1 1 0 0 0-1-1zm9 6h-3v2h3zm0 3h-3v2h3zm0 3h-3v2h2a1 1 0 0 0 1-1zm-4 2v-2H6v2zm-4 0v-2H3v1a1 1 0 0 0 1 1zm-2-3h2v-2H3zm0-3h2V7H3zm3-2v2h3V7zm3 3H6v2h3z"/>
                                </svg>
                                <!--<img src="assets/images/file-spreadsheet.svg" alt="Input detail barang">-->
                            </a>

                            <!-- Delete Link --> 
                            <?php 
                                if ($user->id_user != 1) { ?>
                                    <a class="btn btn-basic btn-sm" href="?page=user&delete=<?php echo $user->id_user; ?>&isaktif=<?php echo $user->is_aktif; ?>" onclick="return confirm('Apakah anda yakin akan aktif/non aktifkan user <?php echo $user->user_name; ?> ini?');">
                                        <svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-toggle2-off' viewBox='0 0 16 16'>
                                            <path d='M9 11c.628-.836 1-1.874 1-3a4.98 4.98 0 0 0-1-3h4a3 3 0 1 1 0 6z'/>
                                            <path d='M5 12a4 4 0 1 1 0-8 4 4 0 0 1 0 8m0 1A5 5 0 1 0 5 3a5 5 0 0 0 0 10'/>
                                        </svg>
                                        <!--<img src="assets/images/trash3.svg" alt="Hapus data">-->
                                    </a>
                            <?php   }  ?>
                                
                            
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
                        <h6 class="modal-title" id="addModalLabel">Tambah User</h6>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" class="form-control" id="useraktif" name="useraktif" value="<?php echo $useraktif; ?>" required>
                        <div class="form-floating mt-2 mb-2">
                            <input type="text" class="form-control" id="user_name" name="user_name" placeholder="User Name" required>
                            <label for="user_name">User Name</label>
                        </div>
                        <div class="form-floating mt-1 mb-2">
                            <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                            <label for="password">Password</label>
                        </div>
                        <div class="form-floating mt-1 mb-2">
                            <input type="password" class="form-control" id="pass_confirm" name="pass_confirm" placeholder="Password Confirm" required onchange="
                                if (this.value !== document.getElementById('password').value) {
                                    alert('Password Confirm tidak sama!');
                                    this.setCustomValidity('Password Confirm tidak sama!');
                                } else {
                                    this.setCustomValidity('');
                                }
                                ">
                            <label for="pass_confirm">Password Confirm</label>
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

</section>