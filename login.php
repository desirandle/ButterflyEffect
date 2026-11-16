<?php
session_start();

// A new login attempt must check the entered credentials again.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    unset($_SESSION['username']);
}

// checks to see if user is already logged in 
// redirects to indexx.php if so 
if (isset($_SESSION['username']) && ($_GET['continue'] ?? '') === '1') {
    header('Location:index.php');
    exit();
}

// guarantees error already exists 
// stays empty if no validation errors actually happen 
$error = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = 'Username and password are both required to log in.';
    } else {
        // php stores users who register to users.txt
        $file = 'users.txt';
        // setting login success to false by default but it will flip to true if matching username is found 
        $loginSuccess = false;

        // checking if username actually exists
        if (file_exists($file)) {
            // built in php function that ignores \n and blank lines
            $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            // loop looks at every registered user one by one 
            foreach ($lines as $line) {
                // splits current line whenever it encounters a | and then puts the pieces in a temp array
                // data[0] = name, data[1] = email, data[2] = password 
                $data = explode('|', $line);

                // checks to see if username and password exists
                // and if it matches the username and password the user typed in 
                if(isset($data[0], $data[2]) && $data[0] === $username && $data[2] === $password) {
                    // if the match is found, loginSuccess flips to true 
                    $loginSuccess = true;
                    break;
                }    
            }
        }

        // redirection to index.php if successful 
        if ($loginSuccess) {
            $_SESSION['username'] = $username;
            header('Location: index.php');
            exit();
        // otherwise...     
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Butterfly Effect</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page login-page">
    <!-- php logic is fully set up and should run correctly. you just need to build the login form in HTML and style it -->

    <main class="auth-layout">
        <section class="story-panel" aria-label="Butterfly Effect adventure">
            <a class="brand" href="login.php" aria-label="Butterfly Effect login">
                <span class="brand-mark" aria-hidden="true">✦</span>
                <span>BUTTERFLY EFFECT</span>
            </a>

            <div class="story-art" aria-hidden="true">
                <div class="moon-ring"></div>
                <img src="butterfly.png" alt="" class="butterfly-art">
                <span class="spark spark-one">✦</span>
                <span class="spark spark-two">✧</span>
                <span class="spark spark-three">✦</span>
            </div>

            <div class="story-copy">
                <p class="eyebrow">A CHOOSE-YOUR-PATH ADVENTURE</p>
                <h1>One choice can change <em>everything.</em></h1>
                <p>Every path has a price. Which future will you create?</p>
            </div>
            <p class="story-footer">YOUR STORY STARTS HERE <span aria-hidden="true">✧</span></p>
        </section>

        <section class="form-panel" aria-labelledby="form-heading">
            <div class="form-content">
                <p class="eyebrow form-kicker">WELCOME BACK</p>
                <h2 id="form-heading">Return to your story</h2>
                <p class="form-subtitle">Your next chapter is just a step away.</p>

                <?php if (isset($_SESSION['username'])): ?>
                <p class="switch-page">Already signed in? <a href="login.php?continue=1">Continue your story</a></p>
                <?php endif; ?>

                <?php if (isset($_GET['success']) && $_GET['success'] === '1'): ?>
                <p class="notice success" role="status">Account created! You can log in now.</p>
                <?php endif; ?>
                <?php if ($error !== ''): ?>
                    <p class="notice error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>

                <form action="login.php" method="post">
                    <div class="field">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Your username" autocomplete="username" maxlength="40" value="<?= htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Your password" autocomplete="current-password" required>
                </div>
                    <button class="submit-btn" type="submit">
                        Enter the Story <span aria-hidden="true">→</span>
                    </button>
                </form>

                <p class="switch-page">New to the story? <a href="register.php">Create an account</a></p>
            </div>
        </section>
    </main>
</body>
</html>
