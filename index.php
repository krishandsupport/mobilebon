<?php

    $apiUrl = "http://localhost:3000/users";
    $useraktif=$_SESSION['username'];

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['gantipass'])) {
        

        $username = $_POST['user_name'];
        $oldpassword = md5($_POST['old_password']);
        $newpassword = md5($_POST['new_password']);
        $passconfirm = $_POST['pass_confirm'];

        $searchQuery = $username;
        $url=$apiUrl . '?q=' . urlencode($searchQuery);
        $options = array(
            'http' => array(
                'method'  => 'GET',
            ),
        );
        $context  = stream_context_create($options);
        $users = json_decode(file_get_contents($url, false, $context));
        foreach ($users as $user) {
            
            //echo "<script type='text/javascript'>";
            //echo "alert('Terbaca OK!".$user->password."=".$oldpassword."');";
            //echo "</script>";

            if ($user->password == $oldpassword) {
                // Handle Update

                $id = $_POST['id_user'];
                $data = array(
                    "password" => $newpassword,
                    "useraktif" => $useraktif
                );
                $optlogs = array(
                    'http' => array(
                        'header'  => "Content-Type: application/json\r\n",
                        'method'  => 'PUT',
                        'content' => json_encode($data)
                    )
                );
                $contlog  = stream_context_create($optlogs);
                $userlogs = json_decode(file_get_contents($apiUrl.'/'.$id, false, $contlog));

                echo "<script type='text/javascript'>";
                echo "alert('Password telah berhasil diganti.');";
                echo "</script>";

            } else {

                echo "<script type='text/javascript'>";
                echo "alert('Password lama yang anda masukan salah!');";
                echo "window.location.href = '?page=home';";
                echo "</script>";

            }
        }
    
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mobile BON</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:400,600&display=swap">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>

    </style>
</head>
<body>
    <main>
         <?php
            $page = isset($_GET['page']) ? $_GET['page'] : 'home';
            $pageFile = "assets/pages/{$page}.php";
            if (file_exists($pageFile)) {
                include($pageFile);
            } else {
                echo "<p>Page tidak ditemukan.</p>";
            }
        ?>
    </main>
    <div class="menu">
        <a class="btn btn-basic btn-sm" href="?page=home">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" class="bi bi-house" viewBox="0 0 16 16">
                <path d="M8.707 1.5a1 1 0 0 0-1.414 0L.646 8.146a.5.5 0 0 0 .708.708L2 8.207V13.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5V8.207l.646.647a.5.5 0 0 0 .708-.708L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293zM13 7.207V13.5a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5V7.207l5-5z"/>
            </svg>
            <!--<img src="assets/images/home-icon.svg" alt="Home">-->
            <br>Home
        </a>
        <a class="btn btn-basic btn-sm" href="?page=user">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" class="bi bi-people" viewBox="0 0 16 16">
                <path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1zm-7.978-1L7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002-.014.002zM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4m3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0M6.936 9.28a6 6 0 0 0-1.23-.247A7 7 0 0 0 5 9c-4 0-5 3-5 4q0 1 1 1h4.216A2.24 2.24 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816M4.92 10A5.5 5.5 0 0 0 4 13H1c0-.26.164-1.03.76-1.724.545-.636 1.492-1.256 3.16-1.275ZM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0m3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4"/>
            </svg>
            <!--<img src="assets/images/wrench-adjustable-circle.svg" alt="Setting Sistem">-->
            <br>Users
        </a>
        <a class="btn btn-basic btn-sm" data-user_name="<?php echo $_SESSION['username']; ?>"
            onclick="
                $('#GantiPass').modal('show');
                $('#user_name').val($(this).data('user_name'));
            ">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" class="bi bi-key" viewBox="0 0 16 16">
                <path d="M0 8a4 4 0 0 1 7.465-2H14a.5.5 0 0 1 .354.146l1.5 1.5a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0L13 9.207l-.646.647a.5.5 0 0 1-.708 0L11 9.207l-.646.647a.5.5 0 0 1-.708 0L9 9.207l-.646.647A.5.5 0 0 1 8 10h-.535A4 4 0 0 1 0 8m4-3a3 3 0 1 0 2.712 4.285A.5.5 0 0 1 7.163 9h.63l.853-.854a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.793-.793-1-1h-6.63a.5.5 0 0 1-.451-.285A3 3 0 0 0 4 5"/>
                <path d="M4 8a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/>
            </svg>
            <!--<img src="assets/images/key.svg" alt="Ganti Password">-->
            <br>Ganti Pass
        </a>
        <a class="btn btn-basic btn-sm" href="?page=logout">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" class="bi bi-door-open" viewBox="0 0 16 16">
                  <path d="M8.5 10c-.276 0-.5-.448-.5-1s.224-1 .5-1 .5.448.5 1-.224 1-.5 1"/>
                  <path d="M10.828.122A.5.5 0 0 1 11 .5V1h.5A1.5 1.5 0 0 1 13 2.5V15h1.5a.5.5 0 0 1 0 1h-13a.5.5 0 0 1 0-1H3V1.5a.5.5 0 0 1 .43-.495l7-1a.5.5 0 0 1 .398.117M11.5 2H11v13h1V2.5a.5.5 0 0 0-.5-.5M4 1.934V15h6V1.077z"/>
            </svg>
            <!--<img src="assets/images/door-open.svg" alt="Logout">-->
            <br>Logout
        </a>
    </div>

    <!-- The Addnew Modal -->
    <div id="GantiPass" class="modal fade" role="dialog" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" style="display:inline-block;">
                    <div class="modal-header">
                        <h6 class="modal-title" id="addModalLabel">Ganti Password</h6>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" class="form-control" id="id_user" name="id_user" placeholder="ID User" value="<?php echo $_SESSION['iduser']; ?>" required>
                        <div class="form-floating mt-2 mb-2">
                            <input type="text" class="form-control" id="user_name" name="user_name" placeholder="User Name" value="<?php echo $_SESSION['username']; ?>" required readonly>
                            <label for="user_name">User Name</label>
                        </div>
                        <div class="form-floating mt-1 mb-2">
                            <input type="password" class="form-control" id="old_password" name="old_password" placeholder="Password" required>
                            <label for="old_password">Password Lama</label>
                        </div>
                        <div class="form-floating mt-3 mb-2">
                            <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Password" required>
                            <label for="new_password">Password Baru</label>
                        </div>
                        <div class="form-floating mt-1 mb-2">
                            <input type="password" class="form-control" id="pass_confirm" name="pass_confirm" placeholder="Password Confirm" required onchange="
                                if (this.value !== document.getElementById('new_password').value) {
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
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="gantipass" class="btn btn-primary btn-sm">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <!-- FontAwesome untuk ikon pencarian -->
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>

    <script>
        $(document).ready(function(){
            $('#sub_beli, #pot_beli, #ppn_beli').on('input', function() {
                var subtotal = parseFloat($('#sub_beli').val().replace(/,/g, '')) || 0;
                var potongan = parseFloat($('#pot_beli').val().replace(/,/g, '')) || 0;
                var ppn = parseFloat($('#ppn_beli').val().replace(/,/g, '')) || 0;
                var total = (subtotal + ppn) - potongan;
                $('#tot_beli').val(total.toFixed(2));
            });
        });
        $(document).ready(function(){
            $('#edit-sub_beli, #edit-pot_beli, #edit-ppn_beli').on('input', function() {
                var subtotal = parseFloat($('#edit-sub_beli').val().replace(/,/g, '')) || 0;
                var potongan = parseFloat($('#edit-pot_beli').val().replace(/,/g, '')) || 0;
                var ppn = parseFloat($('#edit-ppn_beli').val().replace(/,/g, '')) || 0;
                var total = (subtotal + ppn) - potongan;
                $('#edit-tot_beli').val(total.toFixed(2));
            });
        });
        $(document).ready(function(){
            $('#qty_beli, #harga_beli').on('input', function() {
                var qtybeli = parseFloat($('#qty_beli').val().replace(/,/g, '')) || 0;
                var hargabeli = parseFloat($('#harga_beli').val().replace(/,/g, '')) || 0;
                var total = (qtybeli * hargabeli);
                $('#total_beli').val(total.toFixed(2));
            });
        });
        $(document).ready(function(){
            $('#edit-qty_beli, #edit-harga_beli').on('input', function() {
                var qtybeli = parseFloat($('#edit-qty_beli').val().replace(/,/g, '')) || 0;
                var hargabeli = parseFloat($('#edit-harga_beli').val().replace(/,/g, '')) || 0;
                var total = (qtybeli * hargabeli);
                $('#edit-total_beli').val(total.toFixed(2));
            });
        });
        $(document).ready(function(){
            $('#total_beli, #nilai_bayar').on('input', function() {
                var totalbeli = parseFloat($('#total_beli').val().replace(/,/g, '')) || 0;
                var nilaibayar = parseFloat($('#nilai_bayar').val().replace(/,/g, '')) || 0;
                var total = (totalbeli - nilaibayar);
                $('#sisa_hutang').val(total.toFixed(2));
            });
        });
        $(document).ready(function(){
            $('#edit-total_beli, #edit-nilai_bayar').on('input', function() {
                var totalbeli = parseFloat($('#edit-total_beli').val().replace(/,/g, '')) || 0;
                var nilaibayar = parseFloat($('#edit-nilai_bayar').val().replace(/,/g, '')) || 0;
                var total = (totalbeli - nilaibayar);
                $('#edit-sisa_hutang').val(total.toFixed(2));
            });
        });
    </script>
    <script>
        function updatePermission(id, field, value) {
            console.log(`Updating permission: id=${id}, field=${field}, value=${value}`);
            fetch(`localhost:3000/update_permission/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    field: field,
                    value: value
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log("Update successful!", data);
                } else {
                    console.error("Update failed:", data.message);
                }
            })
            .catch(error => {
                console.error("Error:", error);
            });
        }
    </script>
</body>
</html>
