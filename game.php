<?php
session_start();

// bounces player back to index.php if they try to skip
if (!isset($_SESSION['hero'])) {
    header('Location: index.php');
    exit;
}

// These checks use the hero's saved stats, not values from the form.
$statChecks = [
    'dark_path:cliff_side' => [
        'needed' => 22,
        'failure' => 'Your foot slips on the cliff, and you tumble back to safety.'
    ],
    'cave_entrance:item_pickup' => [
        'needed' => 21, 'favored' => 'Mage',
        'failure' => 'The lock flashes and sends you stumbling backward.'
    ],
    'hill_path:castle_hall' => [
        'needed' => 21, 'favored' => 'Warrior', 'weapon_bonus' => 10,
        'failure' => 'You slam against the doors, but they barely move.'
    ],
    'hill_path:castle_walls' => [
        'needed' => 21, 'favored' => 'Rogue',
        'failure' => 'Your hand slips, and you fall back to the foot of the wall.'
    ],
    'old_bridge:bandit_victory' => [
        'needed' => 21, 'weapon_bonus' => 20, 'mage_bonus' => 12,
        'failure' => 'Oof! You try to strike, but the bandit moves faster and hits you first.'
    ]
];

// Each class can handle obstacles in its own way without changing its saved stats.
function actionScore($hero, $check) {
    $class = $hero['class'] ?? 'Warrior';
    $stat = $class === 'Mage' ? 'magic' : 'strength';
    $score = (int) ($hero[$stat] ?? 0);
    if ($class === 'Rogue') $score += 8; // represents quickness without a new stat
    if (($check['favored'] ?? '') === $class) $score += 1;
    return $score;
}

// story nodes and their corresponding text and choices
$story = [
    // sunlit path vs dark path
    'start' => [
        'text' => 'You stand at the edge of the ancient woods. To your left, a clear, sunlit path winds peacefully. To your right, 
        a jagged, menacing path disappears into dark shadows.',
        'choices' => [
            ['text' => 'Take the sunlit path.', 'next' => 'sunny_path'],
            ['text' => 'Venture down the dark path.', 'next' => 'dark_path']
        ]
    ],
    // sunlit path
    'sunny_path' => [
        'text' => 'The sunlit path cuts quickly through the trees with the wind calmly brushing past your face. It leads you straight to a hidden cave 
        opening thats glowing faintly.',
        'choices' => [
            ['text' => 'Enter the cave front entrance.', 'next' => 'cave_entrance'],
            ['text' => 'Keep walking toward an old wooden bridge.', 'next' => 'old_bridge']
        ]
    ],
    // dark path
    'dark_path' => [
        'text' => 'The dark path plunges you into gloom. Hushed whispers echo through the trees as you trudge past and finally land in front 
        of a bubbling swamp that is radiating toxic fumes.',
        'choices' => [
            ['text' => 'Wade deeper through the swamp.', 'next' => 'swamp_crossing'],
            [
                'text' => 'Climb a steep, rocky cliffside to get away from the water.', 
                'next' => 'cliff_side',

                // passes 15 to hp costs to deduct health
                'hp_cost' => 15, 
                'warning' => 'Warning: This action is dangerous.'
            ]
        ]
    ],
    // cave entrance (item pickup)
    'cave_entrance' => [
        'text' => 'Inside the hidden cavern, you stumble upon an old armory chest that has been left behind. It has a sturdy lock but it is nothing you 
        cannot break open. As you approach, you notice a faint glimmer of light coming from the keyhole and you realize it might be a trap.',
        'choices' => [
            ['text' => 'Take the risk and open the chest to claim your treasure!', 'next' => 'item_pickup'],
            ['text' => 'Ignore it and walk deeper into the dark tunnel.', 'next' => 'deep_tunnel'],
            ['text' => 'Leave the cave and head toward the bridge.', 'next' => 'old_bridge']
        ]
    ],
    // old bridge (first fight encounter)
    'old_bridge' => [
        'text' => 'A bandit that has been hiding under the bridge suddenly reveals himself. He makes a sudden move to block your way across the bridge, 
        demanding all you have! ' . (isset($_SESSION['weapon'])
            ? 'With your weapon in hand, you have an advantage, but you could still use the environment to escape.'
            : 'You do not have any weapons, but there is a possibilty you can use the environment to your advantage.'),
        'choices' => [
            [
                'text' => ($_SESSION['hero']['class'] ?? '') === 'Mage'
                    ? 'Fight the bandit using your magic!'
                    : (isset($_SESSION['weapon']) ? 'Fight the bandit with your weapon!' : 'Fight the bandit with your brain power and bare hands!'),
                'next' => 'bandit_victory',

                // hp penalty for a bad fight
                'hp_cost' => 50, 
                'warning' => 'Warning: This action is dangerous.'
            ],
            ['text' => 'Create a distraction and sneak past him into a tangeled canopy.', 'next' => 'tangled_canopy']
        ]
    ],
    // Winning the bandit fight opens the same path as sneaking past him.
    'bandit_victory' => [
        'text' => 'You stand your ground and drive the bandit away, but the fight leaves you exhausted. Beyond the bridge, you spot a path through the trees.',
        'choices' => [
            ['text' => 'Follow the path into the tangled canopy.', 'next' => 'tangled_canopy']
        ]
    ],
    // swamp (loops back to cave entrance for item pickup)
    'swamp_crossing' => [
        'text' => 'You brave the thick fog and naueseating stench of the swamp and discover a hidden fissure in the stone wall. It is a tigth fit, 
        but you can squeeze through to the other side.',
        'choices' => [
            ['text' => 'Squeeze through the entrance.', 'next' => 'cave_entrance'],
            ['text' => 'Turn around and head toward the hills', 'next' => 'hill_path']
        ]
    ],
    // hill path
    'hill_path' => [
        'text' => 'You climb the ridge slowly, careful to not slip, and reach the towering gates of an abandoned castle. The gate lock is rusted and broken. 
        If you push hard enough, maybe the doors will give way, but the castle walls seem to offer an alternative route.',
        'choices' => [
            ['text' => 'Push on the heavy castle doors.', 'next' => 'castle_hall'],
            [
                'text' => 'Climb the castle walls.', 
                'next' => 'castle_walls',

                // hp penalty for wall climbing
                'hp_cost' => 20, 
                'warning' => 'Warning: This action is dangerous.'
            ],
            ['text' => 'Head back down toward the swamp.', 'next' => 'swamp_crossing']
        ]
    ],
    // item pickup 
    'item_pickup' => [
        'text' => !empty($_SESSION['weapon_just_found'])
            ? 'You chose to open the chest. It is as if the chest reads your mind, because the weapon looks to be specially crafted for a ' . htmlspecialchars($_SESSION['hero']['class']) . ' like you! Upon weidling it, you feel a surge of power and confidence. You feel ready enough to face the challenges ahead. Up ahead, you see a winding hallway that leads to a grand castle.'
            : 'The chest is empty. You already claimed your ' . htmlspecialchars($_SESSION['weapon'] ?? 'weapon') . '!',
        'choices' => [
            ['text' => 'Equip your weapon and make your way to the castle.', 'next' => 'castle_hall'],
            ['text' => 'Return to the cave entrance and explore.', 'next' => 'cave_entrance']
        ]
    ],
    // deep tunnel (dragon encounter)
    'deep_tunnel' => [
        'text' => 'With the winding and twisting passages, you get lost and by some bad luck stumble into a sleeping dragon\'s lair! You gasp and the dragon wakes 
        up, glaring at you with its fiery eyes for interupting its slumber. You have no weapons and the dragon is clearly not in a good mood.',
        'choices' => [
            [
                'text' => 'Try to fight the dragon without proper gear.', 
                'next' => 'tragic_failure',

                // fatal hp cost
                'hp_cost' => 100, 
                'warning' => 'Warning: Your journey is at stake.'
            ],
            ['text' => 'Back away slowly into the light.', 'next' => 'cave_entrance']
        ]
    ],
    // tangled canopy (bandit encounter)
    'tangled_canopy' => [
        'text' => 'You manage to slip safely past the bandit while he\'s distracted and discover a hermit\'s hut that has been been tucked away. The hermit doesn\'t get 
        many visitors and is excited to finally have someone to talk to. He says he knows a secret passage to a castle but will only lead you to it if you promise
        to converse with him for a little while. But you worry about the time lost.',
        'choices' => [
            [
                'text' => 'Entertain the converation with the Hermit and then continue to the castle.', 
                'next' => 'castle_hall',

                // passes 20 to hp heal to restore health
                'hp_heal' => 20, 
                'warning' => 'Bonus: Resting to converse with the hermit restores +20 HP!'
            ],
            ['text' => 'Keep running through the canopy.', 'next' => 'dark_path']
        ]
    ],
    // castle hall
    'castle_hall' => [
        'text' => 'You step into the grand hall of the castle. You dont get much time to admire the architecture as a massive stone beast blocks the throne room doors!
        On the the left of the beast, you notice an open door that leads down a staircase that might have some weapons to help you in the fight. But it could be a trap.',
        'choices' => [
            ['text' => 'Confront the beast!', 'next' => 'throne_room'],
            [
                'text' => 'Explore the left door anyway.', 
                'next' => 'tragic_failure',

                // hp penalty for trap
                'hp_cost' => 40, 
                'warning' => 'Warning: Your journey is at stake.'
            ]
        ]
    ],
    // castle walls
    'castle_walls' => [
        'text' => 'You begrudgingly climb the walls. when you reach the top, you get a clear view of the castle. On the right, you see a hidden path that leads to a 
        door that looks to be hidden on purpose. On the left, you see a staircase that leads to the grand hall. The throne room is sure to haev treasures, but the
         hidden door might have some more valuable since they went through the trouble of hiding it.',
        'choices' => [
            ['text' => 'Head towards the grand hall.', 'next' => 'castle_hall'],
            ['text' => 'Head towards the hidden door.', 'next' => 'secret_ending']
        ]
    ],
    // cliff side
    'cliff_side' => [
        'text' => 'You scale the rocky cliffside and find yourself looking down at the gates of an old castle. There is a hill path that leads to the castle, 
        but it looks treacherous and dangerous. You can also see a path that leads down into the misty woods.',
        'choices' => [
            ['text' => 'Approach the castle gates.', 'next' => 'hill_path'],
            ['text' => 'Descend back down into the misty woods.', 'next' => 'dark_path']
        ]
    ],
    // throne
    'throne_room' => [
        'text' => 'The beast snarls, with all four paws on the ground, it prepares to attack!',
        'choices' => []
    ],
    // ending nodes
    //heroic victory 
    'heroic_victory' => [
        'text' => 'Victory achieved! You have defeated the beast and claimed the throne room as your own. The castle now belongs to you and 
        your name will be remembered in history!',
        'choices' => []
    ],
    // tragic failure
    'tragic_failure' => [
        'text' => 'Tragic Failure... unfortunately, your journey ends here.',
        'choices' => []
    ],
    // secret ending 
    'secret_ending' => [
        'text' => 'You have unlocked the Secret Ending! Upon entering the hidden door, you discover a treasure chest that contains a book of the realm\'s most hidden secrets. 
        This can be used for immense power, money, or knowledge! ',
        'choices' => []
    ]
];

// finds out which node the player is on right now
$currentNode = $_SESSION['node'] ?? 'start';
if (!isset($story[$currentNode])) {
    $currentNode = 'start';
}
$currentScene = $story[$currentNode];

// changes the final boss fight based on if they got their weapon earlier in the story 
// no weapon = tragic failure
// weapon = heroic victory
if ($currentNode === 'throne_room') {
    $weapon = $_SESSION['weapon'] ?? null;

    // changing based on weapon 
    if ($weapon === 'Steel Sword') {
        $currentScene['text'] = 'You draw your heavy Steel Sword. With one mighty swing, you slice right through the beast, killing it instantly!';
        $currentScene['choices'] = [['text' => 'Claim your victory!', 'next' => 'heroic_victory']];

    } elseif ($weapon === 'Wooden Wand') {
        $currentScene['text'] = 'You raise your Wooden Wand. With one quick incantation, you blast the beast into dust with a fire spell, killing it instantly!';
        $currentScene['choices'] = [['text' => 'Claim your victory!', 'next' => 'heroic_victory']];

    } elseif ($weapon === 'Shadow Bow') {
        $currentScene['text'] = 'You pull back your Shadow Bow. With one quick motion, you fire an arrow straight through the beast\'s weak spot, killing it instantly!';
        $currentScene['choices'] = [['text' => 'Claim your victory!', 'next' => 'heroic_victory']];

    // no weapon at all? 
    } else {
        $currentScene['text'] = 'Without a weapon to defend yourself, you decide to face the beast head on... but it proves too powerful.';
        $currentScene['choices'] = [['text' => 'Accept your fate.', 'next' => 'tragic_failure', 'hp_cost' => 100]];
    }
}
// POST forms that update the story as the player chooses their choices 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['next_node'])) {
    // Only accept choices that belong to the current scene.
    $selectedChoice = null;
    foreach ($currentScene['choices'] as $option) {
        if ($option['next'] === $_POST['next_node']) {
            $selectedChoice = $option;
            break;
        }
    }
    if (!$selectedChoice) {
        header('Location: game.php');
        exit;
    }

    $checkKey = $currentNode . ':' . $selectedChoice['next'];
    $check = $statChecks[$checkKey] ?? null;

    // A weapon helps in a fight or at the doors, but never helps with climbing.
    $alreadyClaimed = $checkKey === 'cave_entrance:item_pickup' && isset($_SESSION['weapon']);
    if ($check && !$alreadyClaimed) {
        $stat = actionScore($_SESSION['hero'], $check);
        if ($stat < $check['needed']) {
            // Better stats and weapons improve the chance; no HP values change.
            $chance = max(30, 80 - ($check['needed'] - $stat) * 4);
            if (($_SESSION['hero']['class'] ?? '') === 'Mage') {
                $chance += $check['mage_bonus'] ?? 0;
            }
            if (isset($_SESSION['weapon'])) {
                $chance += $check['weapon_bonus'] ?? 0;
            }
            $chance = min(95, $chance);
            if (random_int(1, 100) > $chance) {
                $failureCost = (int) ($selectedChoice['hp_cost'] ?? 8);
                $_SESSION['hero']['hp'] = max(0, $_SESSION['hero']['hp'] - $failureCost);
                $_SESSION['choice_log'][] = $selectedChoice['text'] . ' (failed)';

                if ($_SESSION['hero']['hp'] === 0) {
                    $_SESSION['node'] = 'tragic_failure';
                    header('Location: endings.php');
                    exit;
                }

                $_SESSION['action_message'] = $check['failure'] . ' You lost ' . $failureCost . ' HP. You can try again.';
                $_SESSION['action_type'] = 'failure';
                header('Location: game.php');
                exit;
            }

            $_SESSION['action_message'] = 'You pulled it off despite the risk!';
            $_SESSION['action_type'] = 'success';
        }
    }

    // The first chest visit shows the weapon discovery; later visits show an empty chest.
    if ($currentNode === 'item_pickup') {
        unset($_SESSION['weapon_just_found']);
    }

    // overwrites old tree with new choice added 
    $_SESSION['node'] = $selectedChoice['next'];

    // appends new choice to the very end of the choice log 
    $_SESSION['choice_log'][] = $selectedChoice['text'];

    // hp loss 
    if (isset($selectedChoice['hp_cost'])) {
        // costs hp to climb and fight 
        $_SESSION['hero']['hp'] -= $selectedChoice['hp_cost'];
        // subtracts hp from player hp
        if ($_SESSION['hero']['hp'] <= 0) {
            $_SESSION['hero']['hp'] = 0;
            // if hp health is lower than or equal to 0, send them to the tragic failure ending 
            $_SESSION['node'] = 'tragic_failure';
        }
    }
    
    // gain hp 
    if (isset($selectedChoice['hp_heal'])) {
        $_SESSION['hero']['hp'] += $selectedChoice['hp_heal'];
    }

    // if the player visits the item_pickup node, give them their class weapon
    if ($_SESSION['node'] === 'item_pickup' && !isset($_SESSION['weapon'])) {
        $_SESSION['weapon_just_found'] = true;
        $playerClass = $_SESSION['hero']['class'];

        if ($playerClass === 'Warrior') {
            $_SESSION['weapon'] = 'Steel Sword';

        } elseif ($playerClass === 'Mage') {
            $_SESSION['weapon'] = 'Wooden Wand';

        } else {
            $_SESSION['weapon'] = 'Shadow Bow';
        }
    }

    // if the player reaches one of the three end nodes, they are directed to the ending php
    $endings = ['heroic_victory', 'tragic_failure', 'secret_ending'];
    if (in_array($_SESSION['node'], $endings)) {
        unset($_SESSION['action_message'], $_SESSION['action_type']);
        header('Location: endings.php');
        exit;
    }

    // Redirect after a choice so refreshing won't repeat the action.
    header('Location: game.php');
    exit;
}

$actionMessage = $_SESSION['action_message'] ?? '';
$actionType = $_SESSION['action_type'] ?? '';
unset($_SESSION['action_message'], $_SESSION['action_type']);

// A finished run should always display its ending page.
if (in_array($currentNode, ['heroic_victory', 'tragic_failure', 'secret_ending'], true)) {
    header('Location: endings.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Adventure | Butterfly Effect</title>
  <link rel="stylesheet" href="style.css?v=v07">
</head>
<body class="world-page game-page">

<!-- 
i did the html for the game php bc it heavily involved the php logic and it can be hard to separate the two and i didnt wanna burden 
you with that. added a warning for hp costs and hp heals so the player knows what they are getting into, a preview 
of the consequences of their choices, and a sidebar that shows the players stats and their story path log so they can see how far they have come. 
feel free to change this if you want! its more of a base if anthing. -->

  <header class="site-header">
    <a href="index.php" class="brand" aria-label="Butterfly Effect character creation">
      <span class="brand-mark" aria-hidden="true">✦</span>
      <span>BUTTERFLY EFFECT</span>
    </a>
    <nav class="site-nav" aria-label="Game navigation">
      <span class="header-label">YOUR JOURNEY</span>
      <a href="logout.php" class="text-link">Log out</a>
    </nav>
  </header>

  <main class="game-container">
    <!-- left side of screen. story box and choices -->
    <section class="story-box" aria-labelledby="chapter-heading">
      <p class="eyebrow">CURRENT CHAPTER</p>
      <h1 id="chapter-heading"><?php echo ucfirst(str_replace('_', ' ', $currentNode)); ?></h1>
      <div class="story-divider" aria-hidden="true">✦</div>
      <p class="story-text"><?php echo $currentScene['text']; ?></p>

      <?php if ($actionMessage): ?>
        <p class="action-result action-result--<?php echo $actionType === 'success' ? 'success' : 'failure'; ?>" role="status">
          <?php echo htmlspecialchars($actionMessage, ENT_QUOTES, 'UTF-8'); ?>
        </p>
      <?php endif; ?>

      <?php if (!empty($currentScene['choices'])): ?>
        <h2 class="choice-heading">What's your next move?</h2>
        <div class="choices-list">
          <?php foreach ($currentScene['choices'] as $choice): ?>
            <?php
            $riskKey = $currentNode . ':' . $choice['next'];
            $risk = $statChecks[$riskKey] ?? null;
            $underqualified = $risk && actionScore($_SESSION['hero'], $risk) < $risk['needed']
                && !($riskKey === 'cave_entrance:item_pickup' && isset($_SESSION['weapon']));
            ?>
            <form method="POST" action="game.php" class="choice-form">
              <input type="hidden" name="next_node" value="<?php echo $choice['next']; ?>">
              <input type="hidden" name="choice_txt" value="<?php echo htmlspecialchars($choice['text']); ?>">

              <!-- hp costs -->
              <!-- hp heal -->

              <button class="choice-btn" type="submit">
                <span><?php echo htmlspecialchars($choice['text']); ?></span>
                <span class="choice-arrow" aria-hidden="true">→</span>
              </button>

              <!-- consequence preview warning -->
              <?php if ($underqualified): ?>
                <p class="choice-warning">Warning: This action could fail.</p>
              <?php elseif (!empty($choice['warning'])): ?>
                <p class="choice-warning"><?php echo htmlspecialchars($choice['warning']); ?></p>
              <?php endif; ?>
            </form>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>

    <!-- right side of screen. hero stats and story path log -->
    <aside class="sidebar" aria-label="Your hero and story progress">
      <div class="sidebar-section">
        <p class="eyebrow">THE TRAVELER</p>
        <h2>Hero Stats</h2>
        <dl class="stats-list">
          <div><dt>Name</dt><dd><?php echo htmlspecialchars($_SESSION['hero']['name']); ?></dd></div>
          <div><dt>Class</dt><dd><?php echo htmlspecialchars($_SESSION['hero']['class']); ?></dd></div>
          <div><dt>HP</dt><dd class="hp-stat"><?php echo $_SESSION['hero']['hp']; ?></dd></div>
          <div><dt>Strength</dt><dd><?php echo $_SESSION['hero']['strength']; ?></dd></div>
          <div><dt>Magic</dt><dd><?php echo $_SESSION['hero']['magic']; ?></dd></div>
          <div><dt>Weapon</dt><dd><?php echo htmlspecialchars($_SESSION['weapon'] ?? 'None yet'); ?></dd></div>
        </dl>
      </div>

      <hr>

      <!-- story log  -->
      <div class="sidebar-section">
        <p class="eyebrow">YOUR DECISIONS</p>
        <h2>Story Path Log</h2>
        <p class="path-log">
          <?php
          // keeps track of all choices thru the session
          if (!empty($_SESSION['choice_log'])) {
              echo implode(" &nbsp;➔&nbsp; ", array_map(function ($choice) {
              return htmlspecialchars($choice, ENT_QUOTES, 'UTF-8');
          }, $_SESSION['choice_log']));
          } else {
              echo "Your journey has only just begun...";
          }
          ?>
        </p>
      </div>
    </aside>
  </main>
</body>
</html>
