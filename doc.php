<?php
include_once __DIR__ . '/../includes/settings.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><link rel="icon" type="image/png" href="./favicon.ico"><title>API Documentation</title><!-- Add Bootstrap CSS link here --><link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" rel="stylesheet"><style>    body {        padding: 20px;        font-family: 'Arial', sans-serif;    }
    .container {        max-width: 800px;    }
    h5 {        color: #007bff;    }
    code {        background-color: #f8f9fa;        border: 1px solid #dee2e6;        border-radius: 4px;        padding: 2px 5px;    }</style>
</head>

<body>
<div class="container">    <h1 class="display-4">API Documentation</h1>
    <h2 class="mt-4">Overview</h2>    <p>This API provides information about the PLTFRM bot.</p>
    <h2 class="mt-4">Authentication</h2>    <p>To access the API, you need to provide a valid API key by including it in the request URL:</p>    <code>https://site.com/api?key=your-api-key</code>
    <h2 class="mt-4">Endpoints</h2>
    <p>Get information about the PLTFRM bot.</p>    <code>GET /api?key=your-api-key</code>
    <h5 class="mt-3">Parameters</h5>    <ul>        <li><strong>key</strong>: Your API key (valid values: 'open-api' or your own)</li>        <li><strong>stats</strong> (optional): Set to 'yes' to include statistics*</li>        <li><strong>user_id</strong> (optional): This should be the user ID. It will show information about the user, number of files and other things that are known.*</li>        <li><strong>plans</strong> (optional): Set to 'yes' to include rate information</li>    </ul>    <p>*These parameters can display information with different parameters, for example: <br><code>/api?key=your-api-key&stats=yes&user_id=123456</code> will show information about bot, stats and user</p>
    <h5 class="mt-3">Example</h5>    <code>https://site.com/api?key=your-api-key&stats=yes</code>
    <h2 class="mt-4">Response</h2>    <pre><code>{"name": "PLTFRM","description": "A handy tool for fast and secure file transfer in Telegram...","version": "1.0.0","website": "https://site.com/","botURL": "https://t.me/123456bot","userCount": 1000,"fileCount": 500
}</code></pre></div>
<!-- Contact section --><div class="container mt-4">    <h2>Contact Me</h2>    <p>For any inquiries or issues, feel free to contact me:</p>    <p>Email: zkyrylo@gmail.com</p></div>
<!-- Footer section --><footer class="footer mt-auto py-2 bg-light text-center">    <div class="container">        <div class="my-2">            <a href="https://site.com"><button class="btn btn-secondary btn-sm mx-1">Home</button></a>            <a href="https://site.com/stats"><button class="btn btn-secondary btn-sm mx-1">Stats</button></a>            <a href="https://site.com/doc"><button class="btn btn-secondary btn-sm mx-1">API</button></a>        </div>        <span class="text-muted">© 2023 PLTFRM. All rights reserved. Version: <?php echo $version; ?></span>    </div></footer>

<!-- Add Bootstrap JS and Popper.js (dependency for Bootstrap) links here --><script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script><script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script><script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
</body>

</html>
