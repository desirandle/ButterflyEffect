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

// Only completed runs should show the ending summary.
if (!in_array($finalNode, ['heroic_victory', 'tragic_failure', 'secret_ending'], true)) {
    header('Location: game.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ending Summary | Butterfly Effect</title>
  <link rel="stylesheet" href="style.css?v=final-ui">
</head>
<body class="world-page ending-page ending-<?php echo htmlspecialchars($finalNode, ENT_QUOTES, 'UTF-8'); ?>">

  <div class="page-shell">
    <header class="site-header">
      <a href="index.php" class="brand" aria-label="Butterfly Effect character creation">
        <span class="brand-mark" aria-hidden="true">✦</span>
        <span>BUTTERFLY EFFECT</span>
      </a>
      <span class="header-label">THE FINAL CHAPTER</span>
    </header>

    <main class="ending-layout">
      <section class="ending-hero" aria-labelledby="ending-title">
        <!-- main header -->
        <div class="outcome-orbit" aria-hidden="true">
          <span class="outcome-symbol">✦</span>
        </div>
        <p class="eyebrow">ADVENTURE COMPLETE</p>
        <h1 id="ending-title">Your story has reached its <em>ending.</em></h1>
        <h2>Ending achieved: <span><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $finalNode)), ENT_QUOTES, 'UTF-8'); ?></span></h2>
        <p>Every choice brought you here. Take a look at the journey you made.</p>
      </section>

      <!-- hero summary box -->
      <section class="ending-summary story-box" aria-labelledby="summary-title">
        <p class="eyebrow">YOUR JOURNEY</p>
        <h3 id="summary-title">Hero Summary</h3>
        <dl class="ending-stats">
          <div><dt>Name</dt><dd><?php echo htmlspecialchars($heroName); ?></dd></div>
          <div><dt>Class</dt><dd><?php echo htmlspecialchars($heroClass); ?></dd></div>
          <div><dt>Equipped Weapon</dt><dd><?php echo htmlspecialchars($heroWeapon); ?></dd></div>
        </dl>

        <hr>

        <!-- 3 major choices -->
        <h3>Your 3 major choices</h3>
        <ul class="milestone-list">
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
        <nav class="page-actions" aria-label="After adventure">
          <a href="leaderboard.php" class="action-link primary-action">View leaderboard <span aria-hidden="true">→</span></a>
          <a href="index.php" class="action-link secondary-action">Play again</a>
          <a href="logout.php" class="action-link secondary-action">Log out</a>
        </nav>
      </section>
    </main>
  </div>
</body>
</html>
