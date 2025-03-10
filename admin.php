<?php
include 'db.php';
session_start();

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true || $_SESSION['level'] !== 'admin') {
    $_SESSION['redirect_message'] = 'Anda belum login';
    header("Location: index.php");
    exit();
}

$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
$max_file_size = 1 * 1024 * 1024;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add"])) {
    $name = $_POST["name"];
    $link = $_POST["link"];

    $icon_name = $_FILES["icon"]["name"];
    $icon_tmp = $_FILES["icon"]["tmp_name"];
    $icon_size = $_FILES["icon"]["size"];
    $icon_ext = strtolower(pathinfo($icon_name, PATHINFO_EXTENSION));

    if (!in_array($icon_ext, $allowed_extensions)) {
        $_SESSION['alert'] = "format_invalid";
    } elseif ($icon_size > $max_file_size) {
        $_SESSION['alert'] = "size_invalid";
    } else {
        $icon_path = "uploads/" . basename($icon_name);

        if (!is_dir('uploads')) {
            mkdir('uploads', 0777, true);
        }

        if (move_uploaded_file($icon_tmp, $icon_path)) {
            $stmt = $conn->prepare("INSERT INTO menus (name, icon, link) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $icon_name, $link);
            $stmt->execute();
            $stmt->close();
            $_SESSION['alert'] = "add";
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update"])) {
    $id = intval($_POST["id"]);
    $name = $_POST["name"];
    $link = $_POST["link"];

    $icon_name = $_FILES["icon"]["name"];
    $icon_tmp = $_FILES["icon"]["tmp_name"];
    $icon_size = $_FILES["icon"]["size"];
    $icon_ext = strtolower(pathinfo($icon_name, PATHINFO_EXTENSION));

    if (!empty($icon_name)) {
        if (!in_array($icon_ext, $allowed_extensions)) {
            $_SESSION['alert'] = "format_invalid";
        } elseif ($icon_size > $max_file_size) {
            $_SESSION['alert'] = "size_invalid";
        } else {
            $stmt = $conn->prepare("SELECT icon FROM menus WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();

            if ($row = $result->fetch_assoc()) {
                $old_icon_path = "uploads/" . $row['icon'];
                if (!empty($row['icon']) && file_exists($old_icon_path)) {
                    unlink($old_icon_path);
                }
            }

            $new_icon_name = uniqid() . "." . $icon_ext;
            $icon_path = "uploads/" . $new_icon_name;

            if (move_uploaded_file($icon_tmp, $icon_path)) {
                $stmt = $conn->prepare("UPDATE menus SET name = ?, icon = ?, link = ? WHERE id = ?");
                $stmt->bind_param("sssi", $name, $new_icon_name, $link, $id);
                $stmt->execute();
                $stmt->close();
                $_SESSION['alert'] = "update";
            } else {
                $_SESSION['alert'] = "upload_failed";
            }
        }
    } else {
        $stmt = $conn->prepare("UPDATE menus SET name = ?, link = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $link, $id);
        $stmt->execute();
        $stmt->close();
        $_SESSION['alert'] = "update";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete"])) {
    $id = intval($_POST["id"]);

    $stmt = $conn->prepare("SELECT icon FROM menus WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if ($row = $result->fetch_assoc()) {
        $icon_path = "uploads/" . $row['icon'];

        $stmt = $conn->prepare("DELETE FROM menus WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        if (!empty($row['icon']) && file_exists($icon_path)) {
            unlink($icon_path);
        }

        $_SESSION['alert'] = "delete";
    }
}

$menus = $conn->query("SELECT * FROM menus");
?>


<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/magang/new/images/bps.png" />
    <title>Manajemen Menu</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- FontAwesome -->
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">

    <style>
        /* Base Style */
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f0f4f8;
            transition: background-color 0.3s ease-in-out;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: 250px;
            background-color: #2f3542;
            color: white;
            padding-top: 40px;
            z-index: 1000;
            transition: transform 0.3s ease-in-out;
        }

        .sidebar .logo {
            font-size: 1.5rem;
            font-weight: bold;
            text-align: center;
            padding: 15px;
        }

        .sidebar .menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar .menu li {
            padding: 10px 20px;
            font-size: 1.1rem;
            cursor: pointer;
        }

        .sidebar .menu li:hover {
            background-color: #4e5d6e;
            transition: background-color 0.2s ease-in-out;
        }

        .content {
            margin-left: 250px;
            padding: 40px 30px;
            min-height: 100vh;
            transition: margin-left 0.3s ease-in-out;
        }

        .toggle-btn {
            display: none;
        }

        .toggle-btn+label {
            position: absolute;
            top: 20px;
            right: 20px;
            background-color: #007bff;
            color: white;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            z-index: 1001;
            transition: background-color 0.3s ease;
        }

        .toggle-btn:checked+label {
            background-color: #ff4d4d;
        }

        .toggle-btn:checked~.sidebar {
            transform: translateX(-100%);
        }

        .toggle-btn:checked~.content {
            margin-left: 0;
        }

        .card {
            border: none;
            background-color: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            border-radius: 8px;
            overflow: hidden;
        }

        .card-header {
            background-color: #007bff;
            color: white;
            padding: 10px;
            font-size: 1.25rem;
            text-align: center;
        }

        .card-body {
            padding: 20px;
        }

        .table img {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 50%;
        }

        .modal-header {
            background-color: #007bff;
            color: white;
        }

        .modal-content {
            border-radius: 10px;
        }

        .btn-sm {
            font-size: 14px;
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .toggle-btn:checked~.sidebar {
                transform: translateX(0);
            }

            .content {
                margin-left: 0;
            }

            .sidebar .menu li {
                text-align: center;
                font-size: 1rem;
            }

            .sidebar .logo {
                font-size: 1.25rem;
            }

            .table-responsive {
                overflow-x: auto;
            }

            .card-header {
                font-size: 1.1rem;
            }
        }
    </style>
</head>


<body>
    <!-- Sidebar Toggle Button -->
    <input type="checkbox" class="toggle-btn" id="toggle-btn">
    <label for="toggle-btn"><i class="fas fa-bars"></i></label>

    <!-- Sidebar Menu -->
    <div class="sidebar">
        <div class="logo">Admin</div>
        <ul class="menu">
            <a href="#menu" style="text-decoration : none; color:white;">
                <li>Manajemen Menu</li>
            </a>
            <a href="add_user.php" style="text-decoration : none; color:white;">
                <li>Manajemen User</li>
            </a>
            <a href="logout.php" style="text-decoration : none; color:white;">
                <li>Keluar</li>
            </a>
        </ul>
    </div>

    <!-- Content -->
    <div class="content">

        <div class="container mt-5" id="menu">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-cogs"></i> Manajemen Menu
                </div>
                <div class="card-body">
                    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addModal">
                        <i class="fas fa-plus btn-icon"></i>Tambah Menu
                    </button>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="menuTable">
                            <thead>
                                <tr>
                                    <th>Icon</th>
                                    <th>Nama</th>
                                    <th>Link</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="menuList">
                                <?php while ($row = $menus->fetch_assoc()) : ?>
                                    <tr>
                                        <td><img src="uploads/<?php echo $row['icon']; ?>" alt="Icon"></td>
                                        <td><?php echo $row['name']; ?></td>
                                        <td><?php echo $row['link']; ?></td>
                                        <td>
                                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $row['id']; ?>">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $row['id']; ?>">
                                                <i class="fas fa-trash"></i> Hapus
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Modal Edit -->
                                    <div class="modal fade" id="editModal<?php echo $row['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Menu</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <form method="POST" enctype="multipart/form-data">
                                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                        <div class="mb-3">
                                                            <label>Nama Menu</label>
                                                            <input type="text" name="name" class="form-control" value="<?php echo $row['name']; ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label>Link</label>
                                                            <input type="text" name="link" class="form-control" value="<?php echo $row['link']; ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label>Icon</label>
                                                            <input type="file" name="icon" class="form-control">
                                                            <small>Biarkan kosong jika tidak ingin mengganti ikon</small>
                                                        </div>
                                                        <button type="submit" name="update" class="btn btn-primary">Simpan Perubahan</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Modal Hapus -->
                                    <div class="modal fade" id="deleteModal<?php echo $row['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Hapus Menu</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Apakah Anda yakin ingin menghapus menu ini?</p>
                                                    <form method="POST">
                                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                        <button type="submit" name="delete" class="btn btn-danger">Hapus</button>
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
        </div>

    </div>

    <!-- Modal Tambah -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Menu Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label>Nama Menu</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Link</label>
                            <input type="text" name="link" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Icon</label>
                            <input type="file" name="icon" class="form-control" required>
                        </div>
                        <button type="submit" name="add" class="btn btn-primary">Tambah Menu</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        window.onload = function() {
            <?php if (isset($_SESSION['alert'])) : ?>
                <?php if ($_SESSION['alert'] == "add") : ?>
                    Swal.fire({
                        title: 'Berhasil!',
                        text: 'Menu berhasil ditambahkan.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    });
                    <?php unset($_SESSION['alert']); ?>
                <?php elseif ($_SESSION['alert'] == "update") : ?>
                    Swal.fire({
                        title: 'Berhasil!',
                        text: 'Menu berhasil diperbarui.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    });
                    <?php unset($_SESSION['alert']); ?>
                <?php elseif ($_SESSION['alert'] == "delete") : ?>
                    Swal.fire({
                        title: 'Berhasil!',
                        text: 'Menu berhasil dihapus.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    });
                    <?php unset($_SESSION['alert']); ?>
                <?php elseif ($_SESSION['alert'] == "format_invalid") : ?>
                    Swal.fire({
                        title: 'Gagal!',
                        text: 'Oops..., Format tidak didukung',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                    <?php unset($_SESSION['alert']); ?>
                <?php elseif ($_SESSION['alert'] == "size_invalid") : ?>
                    Swal.fire({
                        title: 'Gagal!',
                        text: 'Ukuran file terlalu besar! Maksimal 1MB.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                    <?php unset($_SESSION['alert']); ?>
                <?php endif; ?>
            <?php endif; ?>
        };
    </script>

    <!-- DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#menuTable').DataTable(); // Mengaktifkan DataTables pada tabel
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>