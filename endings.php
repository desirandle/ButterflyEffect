<?php
session_start();

// if the player tries to skip straight to ending.php without starting a game, bounce them back to index
if (!isset($_SESSION['hero'])) {
    header('Location: index.php');
    exit;
}

// grab session data for the hero summary
$heroName = $_SESSION['hero']['name'];
$heroClass = $_SESSION['hero']['class'];
$heroWeapon = $_SESSION['weapon'] ?? 'none';
$finalNode = $_SESSION['node'];
$choiceLog = $_SESSION['choice_log'] ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title> Ending Summary</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

  <div class="game-container">
    
    <!-- main header -->
    <h1>adventure complete!</h1>
    <h2>ending achieved: <span><?php echo $finalNode; ?></span></h2>

    <!-- hero summary box -->
    <div class="story-box">
      <h3>Hero Summary </h3>
      <p><strong>Name: </strong> <?php echo htmlspecialchars($heroName); ?></p>
      <p><strong>Class: </strong> <?php echo htmlspecialchars($heroClass); ?></p>
      <p><strong>Equipped Weapon: </strong> <?php echo htmlspecialchars($heroWeapon); ?></p>
      
      <hr>

      <!-- 3 major choices -->
      <h3>Your 3 major choices</h3>
      <ul>
        <?php 
        // first 3 choices recorded in the session log using array_slice
        $milestones = array_slice($choiceLog, 0, 3);
        if (!empty($milestones)) {
            foreach ($milestones as $choice) {
                echo "<li>" . htmlspecialchars($choice) . "</li>";
            }
        } else {
            echo "<li> no choices recorded.</li>";
        }
        ?>
      </ul>

      <hr>

      <!-- play again button to restart the session -->
      <a href="index.php">play again </a>
      <a href="index.php">play again</a> | <a href="logout.php">log out</a>
    </div>

  </div>

</body>
</html>