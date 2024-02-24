<?php
// Include necessary files and settings
include_once __DIR__ . '/../includes/settings.php';
include_once __DIR__ . '/../includes/functions.php';

// Fetch statistics data
$stats = getStats(); // Assuming you have the getStats() function in your functions.php

// Display the statistics
?>
<!DOCTYPE html>
<html>
<head><script src="https://code.jquery.com/jquery-3.6.4.min.js"></script><title>Bot Statistics</title><link rel="icon" type="image/png" href="./favicon.ico"><meta name="viewport" content="width=device-width, initial-scale=1"><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"><style>    body {        background-color: #f8f9fa;    }    .container {        margin-top: 50px;    }    h1 {        text-align: center;        margin-bottom: 30px;    }    .stat-block {        margin-bottom: 20px;        padding: 20px;        border: 1px solid #ced4da;        border-radius: 5px;        background-color: #fff;    }    .text {        padding: 0px;        margin: 0px;        text-align: center; /* Center-align the text */    }</style>
</head>
<body><div class="container">    <h1>Bot Statistics</h1>    <div class="row">        <div class="col-md-6 offset-md-3">            <div class="stat-block">                <p class="text"><?php echo $randomEmoji." ".$randomEmoji1." ".$randomEmoji2." User Count: "; ?> <span id="userCount"><?php echo $stats['userCount']; ?></span><?php echo " ".$randomEmoji6." ".$randomEmoji7." ".$randomEmoji8 ?></p>            </div>            <div class="stat-block">                <p class="text"><?php echo $randomEmoji3." ".$randomEmoji4." ".$randomEmoji5." File Count: "; ?> <span id="fileCount"><?php echo $stats['fileCount']; ?></span><?php echo " ".$randomEmoji9." ".$randomEmoji0." ".$randomEmoji1a ?></p>            </div>        </div>    </div></div>
<footer class="footer mt-auto py-2 bg-light text-center">    <div class="container">        <div class="my-2">            <a href="https://site.com"><button class="btn btn-secondary btn-sm mx-1">Home</button></a>            <a href="https://site.com/stats"><button class="btn btn-secondary btn-sm mx-1">Stats</button></a>            <a href="https://site.com/doc"><button class="btn btn-secondary btn-sm mx-1">API</button></a>        </div>        <span class="text-muted">© 2023 PLTFRM. All rights reserved. Version: <?php echo $version; ?></span>    </div></footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script><script>    $(document).ready(function() {        function updateStatistics() {            $.ajax({                url: 'ajax_stats.php',                type: 'GET',                dataType: 'json',                success: function(data) {                    // Update the content with the new statistics                    $('#userCount').text(data.userCount);                    $('#fileCount').text(data.fileCount);                },                complete: function() {                    // Schedule the next update after a certain interval (e.g., 5000 milliseconds)                    setTimeout(updateStatistics, 5000);                }            });        }
        // Initial update        updateStatistics();    });</script>
</body>
</html>