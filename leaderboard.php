<?php
session_start();

// all player scores will be saved here
$file = 'leaderboard.txt';

// if a player just finished a game and hasnt saved their score yet, add them to the file
if (isset($_SESSION['hero'], $_SESSION['node']) && in_array($_SESSION['node'], ['heroic_victory', 'tragic_failure', 'secret_ending'], true) && !isset($_SESSION['score_saved'])) {
    $name = str_replace(["|", "\n", "\r"], ' ', $_SESSION['hero']['name']);
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

// Higher remaining HP appears first on the board.
usort($scores, function ($a, $b) {
    return (int) ($b[2] ?? 0) <=> (int) ($a[2] ?? 0);
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Leaderboard | Butterfly Effect</title>
  <link rel="stylesheet" href="style.css?v=final-ui">
</head>
<body class="world-page leaderboard-page">

<!-- html  -->
 <!-- somehwere near the end put 
      <a href="index.php">play again</a> | <a href="logout.php">log out</a>
so player can log out and start a new game or just start a new game without logging out. -->

  <div class="page-shell">
    <header class="site-header">
      <a href="index.php" class="brand" aria-label="Butterfly Effect character creation">
        <span class="brand-mark" aria-hidden="true">✦</span>
        <span>BUTTERFLY EFFECT</span>
      </a>
      <span class="header-label">HALL OF LEGENDS</span>
    </header>

    <main class="leaderboard-layout">
      <div class="leaderboard-heading">
        <p class="eyebrow">THE STORIES THAT REMAIN</p>
        <h1>Hall of <em>Legends</em></h1>
        <p>Every ending leaves a mark. See how each adventurer's journey ended.</p>
      </div>

      <section class="leaderboard-card" aria-label="Completed adventures">
        <?php if (empty($scores)): ?>
          <div class="empty-board">
            <div class="empty-symbol" aria-hidden="true">✧</div>
            <h2>No journeys recorded yet</h2>
            <p>Complete an adventure to leave your name among the legends.</p>
          </div>
        <?php else: ?>
          <div class="table-scroll">
            <table class="score-table">
              <thead>
                <tr>
                  <th scope="col">Rank</th>
                  <th scope="col">Hero</th>
                  <th scope="col">Class</th>
                  <th scope="col">HP Left</th>
                  <th scope="col">Ending</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($scores as $rank => $score): ?>
                  <tr>
                    <td><span class="rank-mark"><?php echo $rank + 1; ?></span></td>
                    <td class="hero-cell"><?php echo htmlspecialchars($score[0] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($score[1] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td class="hp-cell"><?php echo (int) ($score[2] ?? 0); ?></td>
                    <td><span class="ending-tag"><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $score[3] ?? 'Unknown')), ENT_QUOTES, 'UTF-8'); ?></span></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </section>

      <nav class="page-actions" aria-label="After adventure">
        <a href="index.php" class="action-link primary-action">Play again <span aria-hidden="true">→</span></a>
        <a href="logout.php" class="action-link secondary-action">Log out</a>
      </nav>
    </main>
  </div>
</body>
</html>
