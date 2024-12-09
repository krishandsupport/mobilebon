<?php
    $apiUrl = "http://192.168.194.50:3000/kategoris";
    $apiUrlPerm = "http://192.168.194.50:3000/permissions";

    session_start();
    if (!isset($_SESSION['username'])) {
        header("Location: ?page=login");
       exit();
    }

    // Cek user permission untuk menu kategori
    $useraktif=$_SESSION['username'];
    $menuname='form_kategori';
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
                    $kategoris = json_decode(file_get_contents($url, false, $context));
                }
            }

            // User permission untuk addnew form ini
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['insert'])) {

                if ($permission->is_addnew == 0) {
                    echo "<script type='text/javascript'>";
                    echo "alert('Anda tidak memiliki hak untuk menambahkan data!');";
                    echo "window.location.href = '?page=kategori';";
                    echo "</script>";
                    exit();
                }

                // Handle Create
                $data = array(
                    //"id_kategori" => $_POST['id_kategori'],
                    "kd_kategori" => $_POST['kd_kategori'],
                    "nm_kategori" => $_POST['nm_kategori'],
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
                $kategoris=json_decode(file_get_contents($apiUrl, false, $context));
                header("Location: ?page=kategori");
            }

            // User permission untuk update form ini
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {

                if ($permission->is_update == 0) {
                    echo "<script type='text/javascript'>";
                    echo "alert('Anda tidak memiliki hak untuk mengupdate data!');";
                    echo "window.location.href = '?page=kategori';";
                    echo "</script>";
                    exit();
                }

                // Handle Update
                $id = $_POST['id_kategori'];
                $data = array(
                    "id_kategori" => $_POST['id_kategori'],
                    "kd_kategori" => $_POST['kd_kategori'],
                    "nm_kategori" => $_POST['nm_kategori'],
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
                $kategoris = json_decode(file_get_contents($apiUrl . '/' . $id, false, $context));
                header("Location: ?page=kategori");
            }

            // User permission untuk delete form ini
            if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['delete'])) {

                if ($permission->is_delete == 0) {
                    echo "<script type='text/javascript'>";
                    echo "alert('Anda tidak memiliki hak untuk menghapus data!');";
                    echo "window.location.href = '?page=kategori';";
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
                $kategoris = json_decode(file_get_contents($apiUrl . '/' . $id, false, $context));
                header("Location: ?page=kategori");
            }
        }
    }

    // pencarian data kategori
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['search'])) {
        $searchQuery = $_POST['cari'];
        $url=$apiUrl . '?q=' . urlencode($searchQuery);
        $options = array(
            'http' => array(
                'method'  => 'GET',
            ),
        );
        $context  = stream_context_create($options);
        $kategoris = json_decode(file_get_contents($url, false, $context));
    }

    $i=1;
?>

<section id="kategori">
    <div class="header fixed-top">
        <div class="left">
            <a href="?page=home"><img src="assets/images/arrow-left.svg" alt="Back"></a>
        </div>
        <div class="center">
            <judul>Daftar Kategori</judul>
        </div>
        <div class="right">
            <a href="" data-toggle="modal" data-target="#NewModal"><img src="assets/images/plus-lg.svg" alt="Tambah"></a>
            <a href="" data-toggle="modal" data-target="#CariModal"><img src="assets/images/search.svg" alt="Cari"></a>
            <a href="?page=kategori"><img src="assets/images/arrow-clockwise.svg" alt="Hapus"></a>
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
                    <th>Nama Kategori</th>
                    <th style="width:80px;">Perintah</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($kategoris as $kategori): ?>
                <tr>
                    <td label="#"><?php echo $i++; ?></td>
                    <td label="Kode"><?php echo $kategori->kd_kategori; ?></td>
                    <td label="kategori"><?php echo $kategori->nm_kategori; ?></td>
                    <td label="Perintah" style="width:80px;">
                        <!-- Update Link -->
                        <a class="btn btn-basic btn-sm" 
                            data-id_kategori="<?php echo $kategori->id_kategori; ?>" 
                            data-kd_kategori="<?php echo $kategori->kd_kategori; ?>" 
                            data-nm_kategori="<?php echo $kategori->nm_kategori; ?>" 
                            onclick="
                                $('#EditModal').modal('show');
                                $('#edit-id_kategori').val($(this).data('id_kategori'));
                                $('#edit-kd_kategori').val($(this).data('kd_kategori'));
                                $('#edit-nm_kategori').val($(this).data('nm_kategori'));
                            ">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-vector-pen" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M10.646.646a.5.5 0 0 1 .708 0l4 4a.5.5 0 0 1 0 .708l-1.902 1.902-.829 3.313a1.5 1.5 0 0 1-1.024 1.073L1.254 14.746 4.358 4.4A1.5 1.5 0 0 1 5.43 3.377l3.313-.828zm-1.8 2.908-3.173.793a.5.5 0 0 0-.358.342l-2.57 8.565 8.567-2.57a.5.5 0 0 0 .34-.357l.794-3.174-3.6-3.6z"/>
                                <path fill-rule="evenodd" d="M2.832 13.228 8 9a1 1 0 1 0-1-1l-4.228 5.168-.026.086z"/>
                            </svg>
                            <!--<img src="assets/images/vector-pen.svg" alt="Edit data">-->
                        </a>
                        <!-- Delete Link --> 
                        <a class="btn btn-basic btn-sm" href="?page=kategori&delete=<?php echo $kategori->id_kategori; ?>" onclick="return confirm('Apakah anda yakin akan menghapus data <?php echo $kategori->nm_kategori; ?> ini?');">
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
                <form method="POST" style="display:inline-block;">
                    <div class="modal-header">
                        <h6 class="modal-title" id="addModalLabel">Tambah Kategori</h6>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" class="form-control" id="useraktif" name="useraktif" value="<?php echo $useraktif; ?>" required>
                        <div class="form-floating mt-1 mb-1">
                            <input type="text" class="form-control" id="kd_kategori" name="kd_kategori" placeholder="Kode kategori" required>
                            <label for="kd_kategori">Kode</label>
                        </div>
                        <div class="form-floating mt-1 mb-1">
                            <input type="text" class="form-control" id="nm_kategori" name="nm_kategori" placeholder="Nama kategori" required>
                            <label for="nm_kategori">Nama Kategori</label>
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
                <form method="POST" style="display:inline-block;">
                    <div class="modal-header">
                        <h6 class="modal-title" id="addModalLabel">Edit Kategori</h6>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" class="form-control" id="edit-useraktif" name="useraktif" value="<?php echo $useraktif; ?>" required>
                        <input type="hidden" class="form-control" id="edit-id_kategori" name="id_kategori" placeholder="ID kategori" required>
                        <!--<input type="hidden" name="_method" value="PUT"> Ini sinyal untuk update -->
                        <div class="form-floating mt-1 mb-1">
                            <input type="text" class="form-control" id="edit-kd_kategori" name="kd_kategori" placeholder="Kode kategori" required>
                            <label for="kd_kategori">Kode</label>
                        </div>
                        <div class="form-floating mt-1 mb-1">
                            <input type="text" class="form-control" id="edit-nm_kategori" name="nm_kategori" placeholder="Nama kategori" required>
                            <label for="nm_kategori">Nama kategori</label>
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
                        <h6 class="modal-title" id="addModalLabel">Cari Kategori</h6>
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
