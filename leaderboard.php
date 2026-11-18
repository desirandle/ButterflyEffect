<?php
session_start();

// all player scores will be saved here
$file = 'leaderboard.txt';

// if a player just finished a game and hasnt saved their score yet, add them to the file
if (isset($_SESSION['hero']) && isset($_SESSION['node']) && !isset($_SESSION['score_saved'])) {
    $name = $_SESSION['hero']['name'];
    $class = $_SESSION['hero']['class'];
    $hp = $_SESSION['hero']['hp'];
    $ending = $_SESSION['node'];

    // formatting into line separated by pipes (like login.php file)
    $entry = "$name|$class|$hp|$ending\n";

    // append the new score to the bottom of leaderboard.txt
    file_put_contents($file, $entry, FILE_APPEND);

    // saved so refreshing the page doesnt duplicate score
    $_SESSION['score_saved'] = true;
}

// read all saved runs from the file into an array
// copied code from login.php to read the file and split 
$scores = [];
if (file_exists($file)) {
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $scores[] = explode('|', $line);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title> Leaderboard </title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- html  -->
 <!-- somehwere near the end put 
      <a href="index.php">play again</a> | <a href="logout.php">log out</a>
so player can log out and start a new game or just start a new game without logging out. -->


</body>
</html>