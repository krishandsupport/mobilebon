<?php
    
    $apiUrl = "http://192.168.194.50:3000/userlogin";

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {

        $username = $_POST['username'];
        $password = $_POST['password'];

        $searchQuery = $username;
        $url=$apiUrl . '?q=' . urlencode($searchQuery);
        $options = array(
            'http' => array(
                'method'  => 'GET',
            ),
        );
        $context  = stream_context_create($options);
        $users = json_decode(file_get_contents($url, false, $context));

        // Periksa apakah data pengguna ditemukan
        if (count($users) > 0) {

            session_start();
            foreach ($users as $user) {
                // Verifikasi password
                if (md5($password) == $user->password) {
                    // Mulai sesi untuk pengguna yang berhasil login
                    $_SESSION['iduser'] = $user->id_user;
                    $_SESSION['username'] = $user->user_name;
                    echo $_SESSION['username'];
                    echo $_SESSION['iduser'];
                    header("Location: ?page=home");
                    //exit();
                } else {
                    echo "<script type='text/javascript'>";
                    echo "alert('Password yang Anda masukkan salah!');";
                    echo "window.location.href = '?page=home';";
                    echo "</script>";
                    exit();
                }
            }
        } else {
            echo '<script type="text/javascript">';
            echo 'alert("Username tidak ditemukan!");';
            echo "window.location.href = '?page=home';";
            echo '</script>';
            //exit();
        }

    }

?>
<!-- pages/login.php -->
<section id="login">
    <div class="container-fluid pt-5">
        <center><img src="assets/images/logo.jpg" width="250px"/></center>
        <form method="POST">
            <div class="form-floating mt-3 mb-3">
                <input type="text" class="form-control" id="username" name="username" required>
                <label for="username">Username:</label>
            </div>
            <div class="form-floating mt-3 mb-3">
                <input type="password" class="form-control" id="password" name="password" required>
                <label for="password">Password:</label>
            </div>
            <div class="form-floating mt-3 mb-3 text-center">
                <button class="btn btn-primary" name="login" type="submit">Login</button>
            </div>
        </form>
    </div>
</section>
