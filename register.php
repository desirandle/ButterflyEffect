<?php
session_start();

// guarantees error already exists 
// stays empty if no validation errors actually happen 
$error = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];


    // register form validation
    if (empty($username) || empty($email) || empty($password)|| empty($confirm_password)) {
        $error = 'All fields are required.';
    } elseif ($password !== $confirm_password) {
        // double quote messes it up, adding \ in front to fix
        $error = 'Passwords don\'t match.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    // built in php function that checks for invalid charcaters  
    } elseif (!filter_var ($email,FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } else {
        // php stores users who register to users.txt
        $file = 'users.txt';
        // setting ifemail exists to false by default but it will flip to true if matching email is found 
        $emailExists = false;

        // checkign if email actually exists
        if (file_exists($file)) {
            // built in php function that ignores \n and blank lines
            $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            // loop looks at every registered user one by one 
            foreach ($lines as $line) {
                // splits current line whenever it encounters a | and then puts the pieces in a temp array
                // data[0] = name, data[1] = email, data[2] = password 
                $data = explode('|', $line);

                // checks to see if email exists and if it matches the email the user typed in 
                if(isset($data[1]) && $data[1] == $email) {
                    // if the match is found, emailexists flips to true 
                    $emailExists = true;
                    break;
                }
            }
        }

        // if the emails exists.. 
        if ($emailExists) {
            $error = 'Sorry, but an account with this email already exists.';
        // otherwise..     
        } else {
            $new_user = "$username|$email|$password\n"; //combines the name, email, and password in plain text 
            // seperates by | and the next new user starts on a fresh new line with \n 

            // success? redirect to login.php
            if(file_put_contents($file, $new_user, FILE_APPEND)!= false) { //php will save our new users to users.txt and paste it to the bottom
                header('Location: login.php?success=1');
                exit();
            // otherwise? show error     
            } else {
                $error = 'We failed to save your user. Please try again.';
            }
        }
    }
} 
?>   

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Butterfly Effect</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page register-page">
    <!-- php logic is fully set up and should run correctly. you just need to build the register form in HTML and style it -->

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
                <p class="eyebrow form-kicker">BEGIN YOUR JOURNEY</p>
                <h2 id="form-heading">Write your first chapter</h2>
                <p class="form-subtitle">Every great story starts somewhere.</p>

                <?php if ($error !== ''): ?>
                    <p class="notice error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>

                <form action="register.php" method="post">
                    <div class="field">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Choose a username" autocomplete="username" maxlength="40" value="<?= htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                </div>

                <div class="field">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="you@example.com" autocomplete="email" maxlength="120" value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="At least 8 characters" autocomplete="new-password" minlength="8" required>
                </div>

                <div class="field">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Enter your password again" autocomplete="new-password" minlength="8" required>
                </div>
                    <button class="submit-btn" type="submit">
                        Create Account <span aria-hidden="true">→</span>
                    </button>
                </form>

                <p class="switch-page">Already have an account? <a href="login.php">Log in</a></p>
            </div>
        </section>
    </main>
</body>
</html>
