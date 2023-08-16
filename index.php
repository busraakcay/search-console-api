<?php
// composer require google/apiclient

require_once 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createMutable(__DIR__);
$dotenv->load();

require_once 'app/helpers/Helpers.php';
require_once 'app/helpers/GA4Helpers.php';
require_once 'app/services/ApiRequest.php';
require_once 'app/services/Mailer.php';
require_once 'app/services/GoogleSearchConsoleClient.php';
require_once 'app/services/GA4ApiClient.php';
require_once 'app/routes/Routes.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <title>Document</title>
    <link rel="stylesheet" href="style/style.css">
</head>

<body class="container">
    <?php
    $controllerInstance->$action($_GET);
    ?>
    <script src="https://cdn.canvasjs.com/ga/canvasjs.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>
</body>

</html>