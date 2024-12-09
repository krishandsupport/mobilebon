<?php
    $apiUrl = "http://192.168.194.50:3000/permissions";
    $apiUrlUserp = "http://192.168.194.50:3000/userps";
    
    // Cek user aktif
    $useraktif = $_SESSION['username'];
    $username = $_GET['user'];

    // Insert daftar menu untuk user permission
    $data = array(
        "username" => $username,
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
    $userps=json_decode(file_get_contents($apiUrl, false, $context));

    
    // Tampilkan data user permission
    $url=$apiUrlUserp . '?q=' . urlencode($username);
    $optusers = array(
        'http' => array(
            'method'  => 'GET',
        ),
    );
    $contuserp  = stream_context_create($optusers);
    $pusers = json_decode(file_get_contents($url, false, $contuserp));

    
    
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
</style>

<section id="beli">
    <div class="header fixed-top">
        <div class="left">
            <a href="?page=user"><img src="assets/images/arrow-left.svg" alt="Back"></a>
        </div>
        <div class="center">
            <judul>User : <?php echo $username; ?></judul>
        </div>
        <div class="right">
            <a href="" data-toggle="modal" data-target="#NewModal"><img src="assets/images/plus-lg.svg" alt="Tambah"></a>
            <!--<a href="" data-toggle="modal" data-target="#CariModal"><img src="assets/images/search.svg" alt="Cari"></a>-->
            <a href="?page=userp&user=<?php echo $username; ?>"><img src="assets/images/arrow-clockwise.svg" alt="Refresh"></a>
        </div>
    </div>
    <div class="container-t45">
    </div>
    <div class="table-container">
        <table class="styled-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama Menu</th>
                    <th>Open</th>
                    <th>Add New</th>
                    <th>Update</th>
                    <th>Delete</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach ($pusers as $puser): ?>
                    <tr>
                        <td label="#"><?php echo $i++; ?></td>
                        <td label="Nama Menu"><?php echo $puser->ket_menu; ?></td>
                        
                        <!-- Checkbox Open -->
                        <td label="Open" class="center">
                            <div class='form-check form-switch'>
                                <input class='form-check-input' type='checkbox' 
                                        id='openSwitch_<?php echo $puser->id_permission; ?>'
                                        onchange="updPermission(<?php echo $puser->id_permission; ?>, 'is_open', this.checked ? -1 : 0)"
                                       <?php echo ($puser->is_open == -1) ? 'checked' : ''; ?> >
                            </div>
                        </td>
                        
                        <!-- Checkbox AddNew -->
                        <td label="AddNew" class="center">
                            <div class='form-check form-switch'>
                                <input class='form-check-input' type='checkbox' 
                                       id='addNewSwitch_<?php echo $puser->id_permission; ?>'
                                       onchange="updPermission(<?php echo $puser->id_permission; ?>, 'is_addnew', this.checked ? -1 : 0)"
                                       <?php echo ($puser->is_addnew == -1) ? 'checked' : ''; ?> >
                            </div>
                        </td>

                        <!-- Checkbox Update -->
                        <td label="Update" class="center">
                            <div class='form-check form-switch'>
                                <input class='form-check-input' type='checkbox' 
                                       id='updateSwitch_<?php echo $puser->id_permission; ?>'
                                       onchange="updPermission(<?php echo $puser->id_permission; ?>, 'is_update', this.checked ? -1 : 0)"
                                       <?php echo ($puser->is_update == -1) ? 'checked' : ''; ?> >
                            </div>
                        </td>

                        <!-- Checkbox Delete -->
                        <td label="Delete" class="center">
                            <div class='form-check form-switch'>
                                <input class='form-check-input' type='checkbox' 
                                       id='deleteSwitch_<?php echo $puser->id_permission; ?>'
                                       onchange="updPermission(<?php echo $puser->id_permission; ?>, 'is_delete', this.checked ? -1 : 0)"
                                       <?php echo ($puser->is_delete == -1) ? 'checked' : ''; ?> >
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div style="height:100px;">
    </div>

</section>

<script>

    function updPermission(id, field, value) {
        // Menampilkan alert di JavaScript
        //alert(`Test id=${id}, field=${field}, value=${value}`);

        // Mendefinisikan API URL
        const apiUrl = 'http://192.168.194.50:3000/userps';

        // Menyusun data yang akan dikirim
        const data = {
            field: field,
            value: value
        };

        // Mengirimkan request ke API menggunakan fetch
        return fetch(`${apiUrl}/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Gagal melakukan permintaan ke API');
            }
            return response.json(); // Mengurai hasil JSON dari API
        })
        .then(userps => {
            return userps; // Mengembalikan hasil dari API
        })
        .catch(error => {
            console.error('Terjadi kesalahan:', error);
        });
    }


</script>


