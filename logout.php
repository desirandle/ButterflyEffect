<?php
session_start();
session_unset();

// destroy active session
session_destroy();

// send the player back to the starting page
header('Location: index.php');
exit;
?>

<!-- no html or css needed since this is just a redirect page.  -->