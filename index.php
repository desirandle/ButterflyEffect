<?php
session_start();


// bounces player back to index.php if they try to skip
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

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
    $_SESSION['choice_log'] = [];
    unset($_SESSION['weapon'], $_SESSION['score_saved']);

    // takes the player to the game page
    header('Location: game.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Your Hero | Butterfly Effect</title>
    <link rel="stylesheet" href="style.css?v=creation-fix">
</head>
<body class="auth-page creation-page">

 <!-- php logic is fully set up and should run correctly. -->

    <main class="auth-layout">
        <section class="story-panel" aria-labelledby="creation-title">
            <a href="login.php" class="brand" aria-label="Butterfly Effect login">
                <span class="brand-mark" aria-hidden="true">✦</span>
                <span>BUTTERFLY EFFECT</span>
            </a>

            <div class="story-art" aria-hidden="true">
                <div class="moon-ring"></div>
                <img src="butterfly.png" alt="" class="butterfly-art">
                <span class="spark spark-one">✦</span>
                <span class="spark spark-two">✧</span>
            </div>

            <div class="story-copy">
                <p class="eyebrow">THE FIRST CHOICE</p>
                <h1 id="creation-title">Every legend begins with a <em>name.</em></h1>
                <p>The road ahead changes with every choice. Before the adventure begins, choose who will walk it.</p>
            </div>
            <p class="story-footer">THREE PATHS. ONE JOURNEY. <span aria-hidden="true">✧</span></p>
        </section>

        <section class="form-panel" aria-labelledby="hero-heading">
            <div class="form-content">
                <p class="eyebrow form-kicker">YOUR ADVENTURE AWAITS</p>
                <h2 id="hero-heading">Create your hero</h2>
                <p class="form-subtitle">Choose a name and a class to begin.</p>

                <form action="index.php" method="post" class="hero-form">
                    <div class="field">
                        <label for="hero-name">Hero name</label>
                        <input type="text" id="hero-name" name="username" placeholder="Enter your hero's name" maxlength="40" required>
                    </div>

                    <fieldset class="class-picker">
                        <legend>Choose your class</legend>
                        <p class="class-instruction">Each class starts with different strengths.</p>

                        <label class="class-option">
                            <input type="radio" name="hero_class" value="Warrior" required>
                            <span class="class-details">
                                <span class="class-heading"><span class="class-symbol" aria-hidden="true">⚔</span> Warrior</span>
                                <span class="class-summary">High health and strength</span>
                                <span class="class-stats">120 HP <span>·</span> 20 STR <span>·</span> 5 MAG</span>
                            </span>
                        </label>

                        <label class="class-option">
                            <input type="radio" name="hero_class" value="Mage">
                            <span class="class-details">
                                <span class="class-heading"><span class="class-symbol" aria-hidden="true">✧</span> Mage</span>
                                <span class="class-summary">Powerful magic, lower defenses</span>
                                <span class="class-stats">90 HP <span>·</span> 5 STR <span>·</span> 20 MAG</span>
                            </span>
                        </label>

                        <label class="class-option">
                            <input type="radio" name="hero_class" value="Rogue">
                            <span class="class-details">
                                <span class="class-heading"><span class="class-symbol" aria-hidden="true">☽</span> Rogue</span>
                                <span class="class-summary">Balanced and adaptable</span>
                                <span class="class-stats">105 HP <span>·</span> 12 STR <span>·</span> 10 MAG</span>
                            </span>
                        </label>
                    </fieldset>

                    <button type="submit" class="submit-btn">Begin your adventure <span aria-hidden="true">→</span></button>
                </form>
            </div>
        </section>
    </main>
</body>
</html>
