<?php
    
    session_start();
    if (!isset($_SESSION['username'])) {
       header("Location:?page=login");
       exit();
    }

?>

<section id="home">
    <div class="header">
        <div class="left">
            <img src="assets/images/home-icon.svg" alt="Back">
        </div>
        <div class="center">
            <judul>Mobile BON</judul>
        </div>
        <div class="right">
            <img src="assets/images/bell.svg" alt="Pesan masuk">
            <!--<img src="assets/images/bell.svg" alt="Cari">
            <img src="assets/images/bell.svg" alt="Hapus">-->
        </div>
    </div>
    <div class="container-t45">
    </div>

    <!-- Sliding Banner -->
    <div id="carouselExampleControls" class="carousel slide" data-ride="carousel">
      <div class="carousel-inner">
        <div class="carousel-item active">
          <img class="d-block w-100" src="https://img.freepik.com/free-vector/abstract-yellow-blue-geometric-banner-half-tone-design_1017-39620.jpg?size=626&ext=jpg" alt="First slide">
        </div>
        <div class="carousel-item">
          <img class="d-block w-100" src="https://img.freepik.com/free-vector/glossy-blue-color-geometric-hexagonal-shapes-elegant-banner_1055-13825.jpg?size=626&ext=jpg" alt="Second slide">
        </div>
        <div class="carousel-item">
          <img class="d-block w-100" src="https://img.freepik.com/free-vector/modern-colorful-geometric-design-banner_1055-21672.jpg?size=626&ext=jpg&ga=GA1.1.797511464.1725357456&semt=ais_hybrid" alt="Third slide">
        </div>
      </div>
      <a class="carousel-control-prev" href="#carouselExampleControls" role="button" data-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="sr-only">Previous</span>
      </a>
      <a class="carousel-control-next" href="#carouselExampleControls" role="button" data-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="sr-only">Next</span>
      </a>
    </div>
    <!-- Akhir Sliding Banner -->

    <!-- Grid Menu -->
    <div class="menu-grid">
        <div class="menu-item">
            <a href="?page=perusahaan" class="btn-block" style="text-decoration:none;">
                <img src="assets/images/house-up-fill.svg" alt="Daftar Perusahaan">
                <span>Perusahaan</span>
            </a>
        </div>
        <div class="menu-item">
            <a href="?page=supplier" class="btn-block" style="text-decoration:none;">
                <img src="assets/images/people.svg" alt="Daftar Supplier">
                <span>Supplier</span>
            </a>
        </div>
        <div class="menu-item">
            <a href="?page=kategori" class="btn-block" style="text-decoration:none;">
                <img src="assets/images/bookmark-star.svg" alt="Kategori">
                <span>Kategori</span>
            </a>
        </div>
        <div class="menu-item">
            <a href="?page=satuan" class="btn-block" style="text-decoration:none;">
                <img src="assets/images/rulers.svg" alt="Kategori">
                <span>Satuan</span>
            </a>
        </div>
        <div class="menu-item">
            <a href="?page=barang" class="btn-block" style="text-decoration:none;">
                <img src="assets/images/qr-code.svg" alt="Daftar Barang">
                <span>Barang</span>
            </a>
        </div>
         <div class="menu-item">
            <a href="?page=metodepay" class="btn-block" style="text-decoration:none;">
                <img src="assets/images/credit-card.svg" alt="Daftar Barang">
                <span>Metode<br>Bayar</span>
            </a>
        </div>
        <div class="menu-item">
            <a href="?page=beli" class="btn-block" style="text-decoration:none;">
                <img src="assets/images/person-workspace.svg" alt="Daftar Pembelian">
                <span>Pembelian</span>
            </a>
        </div>
        <div class="menu-item">
            <a href="?page=imporbca" class="btn-block" style="text-decoration:none;">
                <img src="assets/images/file-earmark-excel.svg" alt="Impor file BCA">
                <span>Impor BCA</span>
            </a>
        </div>
        <div class="menu-item">
            <a href="?page=bayar" class="btn-block" style="text-decoration:none;">
                <img src="assets/images/tags.svg" alt="Pembayaran Hutang">
                <span>Pembayaran</span>
            </a>
        </div>
    </div>
    <!-- Akhir Grid Menu -->
    
</section>