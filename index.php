<?php
// Koneksi ke database
$koneksi = mysqli_connect('localhost', 'root', '', 'ukk2025_todolist');

// Cek koneksi
if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Edit List
if (isset($_POST['edit_list'])) {
    $list_id = intval($_POST['list_id']);
    $list_name = mysqli_real_escape_string($koneksi, trim($_POST['list_name']));
    mysqli_query($koneksi, "UPDATE lists SET name='$list_name' WHERE id='$list_id'");
    echo "<script>window.location.href='index.php';</script>";
}

// Hapus List
if (isset($_GET['delete_list'])) {
    $list_id = intval($_GET['delete_list']);
    mysqli_query($koneksi, "DELETE FROM lists WHERE id='$list_id'");
    echo "<script>window.location.href='index.php';</script>";
}

// Tambah Task
if (isset($_POST['add_task'])) {
    $task = mysqli_real_escape_string($koneksi, trim($_POST['task']));
    $list_id = intval($_POST['list_id']);
    $priority = intval($_POST['priority']);
    $due_date = mysqli_real_escape_string($koneksi, $_POST['due_date']);
    
    mysqli_query($koneksi, "INSERT INTO tasks (list_id, task, priority, due_date, status) VALUES ('$list_id', '$task', '$priority', '$due_date', '0')");
    echo "<script>window.location.href='index.php';</script>";
}

if (isset($_POST['edit_task'])) {
    $task_id = intval($_POST['task_id']); // ID Task yang akan diupdate
    $task = mysqli_real_escape_string($koneksi, trim($_POST['task']));
    $priority = intval($_POST['priority']);
    $due_date = mysqli_real_escape_string($koneksi, $_POST['due_date']);
    
    // Proses Update Task
    $query = "UPDATE tasks SET task='$task', priority='$priority', due_date='$due_date' WHERE id='$task_id'";
    
    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Task berhasil diupdate'); window.location.href='index.php';</script>";
    } else {
        echo "<script>alert('Gagal mengupdate task. Silakan coba lagi.'); window.location.href='index.php';</script>";
    }
}


// Hapus Task
if (isset($_GET['delete_task'])) {
    $task_id = intval($_GET['delete_task']);
    mysqli_query($koneksi, "DELETE FROM tasks WHERE id='$task_id'");
    echo "<script>window.location.href='index.php';</script>";
}

// Toggle Selesai ↔ Belum Selesai
if (isset($_GET['toggle_status'])) {
    $task_id = intval($_GET['toggle_status']);
    $result = mysqli_query($koneksi, "SELECT status FROM tasks WHERE id='$task_id'");
    $data = mysqli_fetch_assoc($result);
    $new_status = ($data['status'] == 1) ? 0 : 1; // Toggle status
    mysqli_query($koneksi, "UPDATE tasks SET status='$new_status' WHERE id='$task_id'");
    echo "<script>window.location.href='index.php';</script>"; // Reload page
}

// Ambil List dan Task
$lists = mysqli_query($koneksi, "SELECT * FROM lists");
$tasks = mysqli_query($koneksi, "SELECT tasks.*, lists.name AS list_name FROM tasks JOIN lists ON tasks.list_id = lists.id ORDER BY FIELD(tasks.status, '0', '1'), tasks.id DESC");
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>To-Do List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('input[type="date"]').forEach(input => {
                input.addEventListener('keydown', e => e.preventDefault());
            });
        });
    </script>
</head>
<body>
<nav class="navbar shadow-lg fixed-top bg-light">
    <div class="container">
        <a class="navbar-brand fw-bold" href="#">To Do List Apps</a>
        <div class="ms-auto">
            <a href="index.php" class="text-decoration-none mx-3">Home</a>
            <a href="tambah_list.php" class="text-decoration-none mx-3">Tambah List</a>
        </div>
    </div>
</nav>

<div class="container mt-5 pt-5">
    <div class="row">
        <!-- Tambah Task -->
        <div class="border p-3 bg-light">
            <h5>Tambah Task</h5>
            <form method="POST">
                <select name="list_id" class="form-control" required>
                    <option value="">Pilih List</option>
                    <?php mysqli_data_seek($lists, 0); while ($list = mysqli_fetch_assoc($lists)) : ?>
                        <option value="<?= $list['id']; ?>"><?= $list['name']; ?></option>
                    <?php endwhile; ?>
                </select>
                <input type="text" name="task" class="form-control mt-2" placeholder="Masukkan task" required>
                <select name="priority" class="form-control mt-2">
                    <option value="1">Low</option>
                    <option value="2">Medium</option>
                    <option value="3">High</option>
                </select>
                <input type="date" name="due_date" class="form-control mt-2" required min="<?= date('Y-m-d'); ?>">
                <button type="submit" class="btn btn-success w-100 mt-2" name="add_task">Tambah Task</button>
            </form>
        </div>

        <!-- Tabel Task -->
        <div class="border p-3 bg-light mt-3">
            <h5>Daftar Task</h5>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>List</th>
                        <th>Task</th>
                        <th>Prioritas</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php $no = 1; while ($task = mysqli_fetch_assoc($tasks)) : ?>
                    <?php 
                        $is_done = ($task['status'] == 1);
                        $row_class = $is_done ? 'table-success' : '';
                        $task_style = $is_done ? 'text-decoration: line-through; color: gray;' : '';
                    ?>
                    <tr class="<?= $row_class; ?>">
                        <td><?= $no++; ?></td>
                        <td><?= $task['list_name']; ?></td>
                        <td style="<?= $task_style; ?>"><?= $task['task']; ?></td>
                        <td><?= ['Low', 'Medium', 'High'][$task['priority'] - 1]; ?></td>
                        <td><?= $task['due_date']; ?></td>
                        <td>
                            <!-- Edit Modal Trigger -->
                            <a href="#" data-bs-toggle="modal" data-bs-target="#editTaskModal<?= $task['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="?delete_task=<?= $task['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus task ini?')">Delete</a>
                            <!-- Toggle Status Button -->
                            <a href="?toggle_status=<?= $task['id']; ?>" class="btn btn-sm <?= $is_done ? 'btn-secondary' : 'btn-primary'; ?>">
                                <?= $is_done ? 'Tandai Belum Selesai' : 'Tandai Selesai'; ?>
                            </a>
                        </td>
                    </tr>

                <!-- Modal Edit Task -->
<div class="modal fade" id="editTaskModal<?= $task['id']; ?>" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <input type="hidden" name="task_id" value="<?= $task['id']; ?>">
                    
                    <input type="text" name="task" class="form-control mb-2" value="<?= htmlspecialchars($task['task']); ?>" required>
                    
                    <select name="priority" class="form-control mb-2">
                        <option value="1" <?= ($task['priority'] == 1) ? 'selected' : ''; ?>>Low</option>
                        <option value="2" <?= ($task['priority'] == 2) ? 'selected' : ''; ?>>Medium</option>
                        <option value="3" <?= ($task['priority'] == 3) ? 'selected' : ''; ?>>High</option>
                    </select>
                    
                    <input type="date" name="due_date" class="form-control mt-2" value="<?= $task['due_date']; ?>" required min="<?= date('Y-m-d'); ?>">

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <?php if ($task['status'] == 1): ?>
                            <button type="submit" name="edit_task" class="btn btn-primary">Simpan Perubahan</button>
                        <?php else: ?>
                            <button type="submit" name="edit_task" class="btn btn-primary">Update Task</button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

