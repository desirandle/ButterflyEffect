<?php
session_start();

//bounces player back to index.php if they try to skip
if (!isset($_SESSION['hero'])) {
    header('Location: index.php');
    exit;
}

// POST forms that update the story as the player chooses their choices 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['next_node'])) {
    // overwrites old tree with new choice added 
    $_SESSION['node'] = $_POST['next_node'];

    // appends new choice to the very end of the choice log 
    $_SESSION['choice_log'][] = $_POST['choice_txt'];

    // if the player reaches one of the three end nodes, they are directed to the ending php
    $endings = ['heroic_victory', 'tragic_failure', 'secret_ending'];
    if (in_array($_SESSION['node'], $endings)) {
        header('Location: ending.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title> Game Page</title>
</head>
<body>

 <!-- php logic is fully set up and should run correctly. -->

</body>
</html>    