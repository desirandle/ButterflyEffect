<?php
session_start();

//if everythign was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //takes the extra white spaces out of the end or beginning of username for readability 
    $name = trim($_POST['username']);
    $class = $_POST['hero_class'];

    // readjusts the stats based on what class the player chose
    // warriors have advanced strength and hp stats, but very low magic 
    if ($class === 'Warrior') {
        $hp = 120;
        $strength = 20;
        $magic = 5;

    // mages have advanced magic stats, but lower hp and strength
    } elseif ($class === 'Mage') {
        $hp = 90;
        $strength = 5;
        $magic = 20;

    // rogue stats are in the middle, jack of all trades but master of none 
    } else {
        $hp = 105;
        $strength = 12;
        $magic = 10;
    }

    // saves everything to the session 
    $_SESSION['hero'] = [
        'name' => $name,
        'class' => $class,
        'hp' => $hp,
        'strength' => $strength,
        'magic' => $magic,
        // empty inventory to start with 
        'inventory' => []
    ];
    
    // tracks the start
    $_SESSION['node'] = 'start';
    // tracks what choices the player makes from this point on 
    $_SESSION['choices_log'] = [];

    // takes the player to the game page
    header('Location: game.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title> Index Page</title>
</head>
<body>

 <!-- php logic is fully set up and should run correctly. -->

</body>
</html>    