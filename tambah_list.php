<?php
$koneksi = mysqli_connect('localhost', 'root', '', 'ukk2025_todolist');

if (isset($_POST['add_list'])) {
    $list_name = mysqli_real_escape_string($koneksi, $_POST['list_name']);
    mysqli_query($koneksi, "INSERT INTO lists (name) VALUES ('$list_name')");
    echo "<script>window.location.href='index.php';</script>";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <title>Tambah List</title>
</head>
<body>
    <!-- Navigasi -->
    <nav class="navbar shadhow-lg fixed-top">
        <div class="container">
            <h1 class="navbar-brand" href=""> To Do List Apps</h1> <!-- Judul -->
           <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
           <li class="nav-item">
                <a  href="index.php"> Home | </a>  <!-- Ke Halaman Depan -->
                <a  href="tambah_list.php"> Tambah List </a> <!-- Ke Halaman Tambah List -->
            </li>
           </ul>
        </div>
    </nav>
    
     <!-- Tambah List -->
      <div class="container-fluid tambah-list pt-5 pb-5">
     <div class="container text-center"> <br>
            <div class="border p-3 bg-light">
                <h5 class="display-6" >Tambah List</h5>
                <form method="POST">
                    <input type="text" name="list_name" class="form-control" placeholder="Masukkan nama list" required>
                    <button type="submit" class="btn btn-primary w-100 mt-2" name="add_list">Tambah List</button>
                </form>
            </div>
            </div>
        </div>     
</body>
</html>