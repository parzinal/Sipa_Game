<?php
// Replace the existing PHP code at the top of the file
include '../dbconnection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['saveScore'])) {
    header('Content-Type: application/json');

    $playerName = trim((string)($_POST['playerName'] ?? ''));
    $finalScore = isset($_POST['finalScore']) ? (int)$_POST['finalScore'] : 0;

    if ($playerName === '' || $finalScore < 0) {
        exit(json_encode(['success' => false, 'message' => 'Invalid player name or score.']));
    }
    
    // Match nickname in a case-insensitive, trim-safe way.
    $checkSql = "SELECT id, score FROM game_scores WHERE LOWER(TRIM(player_name)) = LOWER(TRIM(?)) LIMIT 1";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("s", $playerName);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows > 0) {
        // Player exists, update score if new score is higher
        $row = $result->fetch_assoc();
        if ($finalScore > $row['score']) {
            $updateSql = "UPDATE game_scores SET score = ? WHERE id = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("ii", $finalScore, $row['id']);
            $updateStmt->execute();
            $updateStmt->close();
        }
    } else {
        // New player, insert new score
        $insertSql = "INSERT INTO game_scores (player_name, score) VALUES (?, ?)";
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bind_param("si", $playerName, $finalScore);
        $insertStmt->execute();
        $insertStmt->close();
    }
    
    $checkStmt->close();
    exit(json_encode(['success' => true]));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sipa Gameplay</title>
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Audiowide&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            overflow: hidden;
            font-family: 'Press Start 2P', cursive; /* Retro pixel font */
            background-color: #1e1e1e; /* Dark background for retro feel */
        }

        canvas {
            display: block;
        }

        #gameStats {
            position: absolute;
            top: 20px;
            left: 20px;
            z-index: 10;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        #score {
            font-size: 28px;
            color: #fff;
            font-family: 'Press Start 2P', cursive;
            background: rgba(0, 0, 0, 0.7);
            padding: 15px 20px;
            border: 3px solid #ffcc00;
            border-radius: 5px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            box-shadow: 0 0 15px rgba(255, 204, 0, 0.3);
        }

        #comboDisplay {
            font-size: 24px;
            color: #ffcc00;
            font-family: 'Press Start 2P', cursive;
            background: rgba(0, 0, 0, 0.7);
            padding: 10px 15px;
            border: 3px solid #ff6600;
            border-radius: 5px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            box-shadow: 0 0 15px rgba(255, 102, 0, 0.3);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        #comboDisplay.active {
            opacity: 1;
        }

        @keyframes comboGlow {
            0% {
                box-shadow: 0 0 15px rgba(255, 102, 0, 0.3);
                transform: scale(1);
            }
            50% {
                box-shadow: 0 0 25px rgba(255, 102, 0, 0.6);
                transform: scale(1.05);
            }
            100% {
                box-shadow: 0 0 15px rgba(255, 102, 0, 0.3);
                transform: scale(1);
            }
        }

        .combo-active {
            animation: comboGlow 1s infinite;
        }

        #startScreen, #gameOverScreen {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.9); /* Darker overlay */
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 20;
            border: 4px solid #fff; /* Pixelated border */
            box-shadow: 0px 0px 20px rgba(255, 255, 255, 0.5);
        }

        #startMessage, #gameOverMessage {
            font-size: 48px;
            color: #ffcc00; /* Retro yellow */
            font-family: 'Press Start 2P', cursive;
            text-shadow: 4px 4px 0px #000, 8px 8px 0px rgba(0, 0, 0, 0.5); /* Pixelated shadow */
            margin-bottom: 20px;
        }

        #countdown, #finalScore {
            font-size: 32px;
            color: #ffffff;
            font-family: 'Press Start 2P', cursive;
            text-shadow: 2px 2px 0px #000;
            margin-bottom: 20px;
        }

        #startButton, #retryButton, #quitButton {
            font-size: 20px;
            padding: 15px 30px;
            background-color: #ffcc00;
            color: #000;
            border: 4px solid #fff;
            border-radius: 0; /* Square buttons for pixel art style */
            cursor: pointer;
            margin: 10px;
            font-family: 'Press Start 2P', cursive;
            text-transform: uppercase;
            box-shadow: 4px 4px 0px #000;
            transition: transform 0.2s, background-color 0.2s;
        }

        #startButton:hover, #retryButton:hover, #quitButton:hover {
            background-color: #ffaa00;
            transform: translate(-4px, -4px); /* Retro "pressed" effect */
            box-shadow: 0px 0px 0px #000;
        }

        @keyframes fadeOut {
            0% {
                opacity: 1;
                transform: translateY(0);
            }
            100% {
                opacity: 0;
                transform: translateY(-20px);
            }
        }

        .comboText {
            animation: fadeOut 1.5s forwards;
        }

        @keyframes glow {
            0% {
                text-shadow: 0 0 10px yellow, 0 0 20px orange;
            }
            100% {
                text-shadow: 0 0 20px orange, 0 0 30px red;
            }
        }

        /* Fire effect for sipa */
        @keyframes fireEffect {
            0% {
                box-shadow: 0 0 10px rgba(255, 69, 0, 0.8), 0 0 20px rgba(255, 140, 0, 0.8);
            }
            50% {
                box-shadow: 0 0 20px rgba(255, 69, 0, 1), 0 0 40px rgba(255, 140, 0, 1);
            }
            100% {
                box-shadow: 0 0 10px rgba(255, 69, 0, 0.8), 0 0 20px rgba(255, 140, 0, 0.8);
            }
        }

        .fireEffect {
            animation: fireEffect 0.5s infinite;
            border-radius: 50%; /* Optional: Makes the effect circular */
        }

        /* Pixelated fire particle */
        .fireParticle {
            position: absolute;
            width: 5px; /* Small pixel size */
            height: 5px;
            background-color: rgba(255, 69, 0, 1); /* Fire color */
            animation: fireAnimation 0.5s linear infinite;
            z-index: 10;
        }

        /* Fire animation */
        @keyframes fireAnimation {
            0% {
                transform: translate(0, 0) scale(1);
                opacity: 1;
            }
            100% {
                transform: translate(calc(-10px + 20px * random()), calc(-20px + 40px * random())) scale(0.5);
                opacity: 0;
            }
        }

        #bgFadeOverlay {
            background: rgba(0,0,0,0.7); /* Transparent black */
            z-index: 0;
            transition: opacity 0.7s;
        }
        #backgroundImage {
            z-index: -1;
        }

        #characterSelection {
            display: none;
            flex-direction: column;
            align-items: center;
            margin-bottom: 30px;
        }

        .character-options {
            display: flex;
            gap: 30px;
        }

        .character-option {
            background: rgba(0, 0, 0, 0.7);
            border: 4px solid #fff;
            padding: 15px;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
        }

        .character-option:hover {
            transform: scale(1.05);
            border-color: #ffcc00;
        }

        .character-option.selected {
            border-color: #ffcc00;
            box-shadow: 0 0 20px #ffcc00;
        }

        .character-option img {
            width: 200px;
            height: 250px;
            object-fit: contain;
            margin-bottom: 10px;
        }

        .character-option p {
            color: #fff;
            font-size: 18px;
            margin: 0;
            font-family: 'Press Start 2P', cursive;
        }

        #nameInput input {
            padding: 15px;
            font-size: 18px;
            font-family: 'Press Start 2P', cursive;
            text-align: center;
            background: rgba(0, 0, 0, 0.7);
            border: 3px solid #ffcc00;
            color: #fff;
            width: 300px;
            margin-bottom: 20px;
        }

        #topScores {
            color: #fff;
            font-family: 'Press Start 2P', cursive;
            font-size: 14px;
            text-align: center;
            margin: 20px 0;
        }

        #topScores h3 {
            color: #ffcc00;
            margin-bottom: 15px;
        }

        .score-item {
            margin: 5px 0;
            padding: 5px;
            background: rgba(255, 204, 0, 0.1);
        }

        #highestScore {
            color: #ffcc00;
            font-size: 24px;
            margin: 10px 0;
        }

        .selection-title {
            color: #ffcc00;
            font-size: 36px;
            margin-bottom: 20px;
            text-align: center;
            font-family: 'Press Start 2P', cursive;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
        }

        .select-button {
            margin-top: 10px;
            padding: 10px 20px;
            background-color: #ffcc00;
            color: #000;
            border: 3px solid #fff;
            border-radius: 5px;
            cursor: pointer;
            font-family: 'Press Start 2P', cursive;
            transition: background-color 0.3s, transform 0.3s;
        }

        .select-button:hover {
            background-color: #ffaa00;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div id="startScreen">
        <div id="startMessage">WELCOME TO SIPA</div>
        <div id="nameInput" style="margin-bottom: 20px;">
            <input type="text" id="playerName" placeholder="Enter your name" 
                   style="font-family: 'Press Start 2P'; padding: 10px; font-size: 16px; text-align: center;">
        </div>
        <div id="characterSelection" style="display: none;">
            <h2 class="selection-title">SELECT YOUR CHARACTER</h2>
            <div class="character-options">
                <div class="character-option selected" data-character="female">
                    <img src="../assets/ramona-idle.gif" alt="Female Character">
                    <div class="character-name">FEMALE</div>
                    <div class="select-button">SELECT</div>
                </div>
                <div class="character-option" data-character="male">
                    <img src="../assets/male-idle.gif" alt="Male Character">
                    <div class="character-name">MALE</div>
                    <div class="select-button">SELECT</div>
                </div>
            </div>
        </div>
        <button id="startButton">START</button>
    </div>

    <div id="gameOverScreen" style="display: none;">
        <div id="gameOverMessage">GAME OVER</div>
        <div id="finalScore"></div>
        <div id="highestScore"></div>
        <div id="topScores" style="margin: 20px 0;"></div>
        <div id="gameOverButtons">
            <button id="retryButton">RETRY</button>
            <button id="quitButton">QUIT</button>
        </div>
    </div>

    <div id="gameStats">
        <div id="score">Score: 0</div>
        <div id="comboDisplay"></div>
    </div>

    <div id="bgFadeOverlay" style="position:absolute;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.7);opacity:0;z-index:0;pointer-events:none;transition:opacity 0.7s;"></div>
    <img id="backgroundImage" src="../assets/sipa-background.png" alt="Background" 
         style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: -1;">
    <canvas id="gameCanvas"></canvas>
    <img id="characterGif" src="../assets/ramona-idle.gif" alt="Character" 
         style="position: absolute; top: 0; left: 0; width: 200px; height: 300px;">
    <img id="sipa" src="../assets/sipa4.png" alt="Sipa" 
         style="position: absolute; width: 70x; height: 70px; z-index: 5;">
    <div id="countdown" style="display: none; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 72px; color: #ffcc00; z-index: 25;"></div>
    
    <script>
        const canvas = document.getElementById('gameCanvas');
        const ctx = canvas.getContext('2d');
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;

        const characterGif = document.getElementById('characterGif');
        const sipa = document.getElementById('sipa');
        const startScreen = document.getElementById('startScreen');
        const countdownElement = document.getElementById('countdown');
        const startButton = document.getElementById('startButton');
        const gameOverScreen = document.getElementById('gameOverScreen');
        const gameOverMessage = document.getElementById('gameOverMessage');
        const finalScore = document.getElementById('finalScore');
        const retryButton = document.getElementById('retryButton');
        const quitButton = document.getElementById('quitButton');
        const scoreElement = document.getElementById('score');

        const hitSound = new Audio('../assets/hit.mp3');
        const missSound = new Audio('../assets/miss.mp3');

        // Add this for background music
        const bgMusic = new Audio('../assets/bg-music.mp3');
        bgMusic.loop = true; // Loop the music
        bgMusic.volume = 0.5; // Set volume (0.0 to 1.0)

        function playHitSound() {
            hitSound.currentTime = 0;
            hitSound.play();
        }

        function playMissSound() {
            missSound.currentTime = 0;
            missSound.play();
        }

        function flashScreen() {
            document.body.style.backgroundColor = '#fff';
            setTimeout(() => {
                document.body.style.backgroundColor = '#1e1e1e';
            }, 60);
        }

        let score = 0;
        let playerName = '';
        let highestScore = 0;

        let character = {
            x: canvas.width / 2 - 180, // Horizontal position (centered)
            y: canvas.height - 380,   // Adjusted vertical position to align closer to the floor
            width: 120,              // Reduced from 160
            height: 360,             // Reduced from 460
            speed: 7,
            moving: false
        };

        let sipaState = {
            width: 80, // Reduced from 100 to 80
            height: 80, // Reduced from 100 to 80
            x: character.x + character.width / 2 - 40 + 40, // <-- add +40 to move right
            y: character.y + character.height * 0.6 - 40, // Adjusted to align with the new height
            velocityY: -10,
            velocityX: 0.6,
            gravity: 0.3,
            bouncing: true,
            angle: 0
        };

        let idleSrc = '../assets/ramona-idle.gif';
        let runSrc = '../assets/ramona-run.gif';
        let kickSrc = '../assets/ramona-kick.gif';

// Update the CHARACTERS object and add event listeners
const CHARACTERS = {
    female: {
        idle: '../assets/ramona-idle.gif',
        run: '../assets/ramona-run.gif',
        kick: '../assets/ramona-kick.gif'
    },
    male: {
        idle: '../assets/male-idle.gif',
        run: '../assets/male-run.gif',
        kick: '../assets/male-kick.gif'
    }
};

let selectedCharacter = 'female'; // Default character
let characterSelected = false; // Add this flag to track character selection

// Update the character selection logic
const characterOptions = document.querySelectorAll('.character-option');
characterOptions.forEach(option => {
    option.addEventListener('click', () => {
        // Remove selected class from all options
        characterOptions.forEach(opt => opt.classList.remove('selected'));
        // Add selected class to clicked option
        option.classList.add('selected');
        // Update selected character
        selectedCharacter = option.dataset.character;
        characterSelected = true; // Set flag when character is selected
        
        // Update character assets
        idleSrc = CHARACTERS[selectedCharacter].idle;
        runSrc = CHARACTERS[selectedCharacter].run;
        kickSrc = CHARACTERS[selectedCharacter].kick;
        // Update character preview
        characterGif.src = CHARACTERS[selectedCharacter].idle;
    });
});

        let particles = [];
        let difficulty = 1;
        let lives = 3;

        const livesContainer = document.createElement('div');
        livesContainer.id = 'livesContainer';
        livesContainer.style.position = 'absolute';
        livesContainer.style.top = '10px';
        livesContainer.style.right = '10px';
        livesContainer.style.zIndex = '10';
        document.body.appendChild(livesContainer);

        function updateLivesDisplay() {
            livesContainer.innerHTML = '';
            for (let i = 0; i < lives; i++) {
                const heart = document.createElement('img');
                heart.src = '../assets/heart.png';
                heart.style.width = '80px';
                heart.style.height = '80px';
                heart.style.marginLeft = '1px';
                livesContainer.appendChild(heart);
            }
        }

        updateLivesDisplay();

        function createParticles(x, y) {
            for (let i = 0; i < 10; i++) {
                particles.push({
                    x: x,
                    y: y,
                    size: Math.random() * 5 + 2,
                    velocityX: (Math.random() - 0.5) * 4,
                    velocityY: (Math.random() - 0.5) * 4,
                    alpha: 1
                });
            }
        }

        function createHitParticles(x, y) {
            let count = combo >= 10 ? 40 : 20; // More particles for big combos
            for (let i = 0; i < count; i++) {
                particles.push({
                    x: x,
                    y: y,
                    size: Math.random() * 8 + 2,
                    velocityX: (Math.random() - 0.5) * 8,
                    velocityY: (Math.random() - 0.5) * 8,
                    color: `hsl(${Math.random() * 360}, 100%, 50%)`,
                    alpha: 1
                });
            }
        }

        function updateParticles() {
            particles.forEach((particle, index) => {
                particle.x += particle.velocityX;
                particle.y += particle.velocityY;
                particle.alpha -= 0.02;
                if (particle.alpha <= 0) {
                    particles.splice(index, 1);
                }
            });

            particles.forEach((particle) => {
                ctx.fillStyle = `rgba(255, 255, 255, ${particle.alpha})`;
                ctx.beginPath();
                ctx.arc(particle.x, particle.y, particle.size, 0, Math.PI * 2);
                ctx.fill();
            });
        }

        // Add combo variables
        let combo = 0; // Track consecutive hits
        let comboTextTimeout; // Timeout for removing combo text

        // Define the paths for the normal and flame sipa images
        const normalSipaSrc = '../assets/sipa4.png';
        const flameSipaSrc = '../assets/flame-sipa.gif';

        // Function to switch to the flame sipa
        function switchToFlameSipa() {
            sipa.src = flameSipaSrc;
        }

        // Function to switch back to the normal sipa
        function switchToNormalSipa() {
            sipa.src = normalSipaSrc;
        }

        // Function to create a single fire particle
        function createFireParticle(x, y) {
            const particle = document.createElement('div');
            particle.classList.add('fireParticle');
            particle.style.left = `${x}px`;
            particle.style.top = `${y}px`;

            // Randomize the particle's color for a more dynamic fire effect
            const colors = ['rgb(17, 108, 136)', 'rgb(27, 150, 187)', 'rgb(0, 217, 255)'];
            particle.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];

            document.body.appendChild(particle);

            // Remove the particle after the animation ends
            setTimeout(() => {
                particle.remove();
            }, 500); // Match the duration of the fireAnimation
        }

        // Function to generate fire particles around the sipa
        function generateFireEffect() {
            const sipa = document.getElementById('sipa');
            const sipaRect = sipa.getBoundingClientRect();

            // Generate multiple particles around the sipa
            for (let i = 0; i < 10; i++) {
                const x = sipaRect.left + Math.random() * sipaRect.width;
                const y = sipaRect.top + Math.random() * sipaRect.height;
                createFireParticle(x, y);
            }
        }

        // Function to start the fire effect
        let fireEffectInterval;
        function startFireEffect() {
            if (!fireEffectInterval) {
                fireEffectInterval = setInterval(generateFireEffect, 100); // Generate particles every 100ms
            }
        }

        // Function to stop the fire effect
        function stopFireEffect() {
            clearInterval(fireEffectInterval);
            fireEffectInterval = null;
        }

        // Function to display combo text with multiplier
        function displayComboText() {
            const comboText = document.createElement('div');
            comboText.classList.add('comboText');

            // Set text based on combo streak
            if (combo < 5) {
                comboText.textContent = `Combo x${combo}`;
            } else if (combo < 10) {
                comboText.textContent = `Good x${combo}`;
            } else if (combo < 15) {
                comboText.textContent = `Awesome x${combo}`;
            } else {
                comboText.textContent = `Unstoppable x${combo}`;
            }

            comboText.style.position = 'absolute';
            comboText.style.top = '50px'; // Position below the score
            comboText.style.left = '10px'; // Align with the score
            comboText.style.fontSize = '24px';
            comboText.style.color = '#ffcc00';
            comboText.style.fontFamily = 'Press Start 2P, cursive';
            comboText.style.textShadow = '2px 2px 4px rgba(0, 0, 0, 0.8)';
            comboText.style.zIndex = '30';
            comboText.style.animation = 'fadeOut 1.5s forwards'; // Same animation as combo text

            document.body.appendChild(comboText);

            // Remove combo text after animation
            setTimeout(() => {
                comboText.remove();
            }, 1500);
        }

        // Function to display combo glow effect
        function displayComboGlow() {
            const comboDisplay = document.getElementById('comboDisplay');
            comboDisplay.style.animation = 'glow 1s infinite alternate';
        }

        // Update the combo logic to start/stop the fire effect
        function updateCombo() {
            combo++;
            const comboDisplay = document.getElementById('comboDisplay');
            
            if (combo > 1) {
                comboDisplay.textContent = `Combo x${combo}`;
                comboDisplay.classList.add('active');
                comboDisplay.classList.add('combo-active');
            } else {
                comboDisplay.classList.remove('active');
                comboDisplay.classList.remove('combo-active');
            }

            // Switch to flame sipa if combo reaches 5
            if (combo >= 5) {
                switchToFlameSipa();
                startFireEffect();
            }

            // Display "Nice", "Good", "Awesome", or "Unstoppable" above the character
            displayHitFeedback();

            // Flash screen on combo milestone
            if (combo % 5 === 0) {
                flashScreen();
            }

            // Display combo glow effect on specific milestones
            if (combo === 5 || combo === 10 || combo === 15) {
                displayComboGlow();
                flashScreen();
            }
        }

        // Reset combo and stop fire effect on miss
        function resetCombo() {
            combo = 0;
            const comboDisplay = document.getElementById('comboDisplay');
            comboDisplay.classList.remove('active');
            comboDisplay.classList.remove('combo-active');
            comboDisplay.textContent = '';
            
            // Switch back to the normal sipa
            switchToNormalSipa();
            stopFireEffect();
        }

        function slowMotion(duration) {
            const originalSpeed = 1;
            document.body.style.transition = 'filter 0.5s';
            document.body.style.filter = 'blur(2px)';
            setTimeout(() => {
                document.body.style.filter = 'none';
            }, duration);
        }

        window.addEventListener('keydown', (e) => {
            if (['ArrowLeft', 'a', 'A'].includes(e.key)) {
                character.moving = 'left';
                characterGif.style.transform = 'scaleX(-1)';
            } else if (['ArrowRight', 'd', 'D'].includes(e.key)) {
                character.moving = 'right';
                characterGif.style.transform = 'scaleX(1)';
            }

            // Ensure the character switches to the running animation if not idle
            if (characterGif.src.indexOf('Kick') !== -1 || characterGif.src.indexOf('idle') !== -1) {
                characterGif.src = runSrc;
            }
        });

        window.addEventListener('keyup', (e) => {
            if (['ArrowLeft', 'a', 'A', 'ArrowRight', 'd', 'D'].includes(e.key)) {
                character.moving = false;
                if (characterGif.src.indexOf('Kick') === -1) { // Only change to idle if not kicking
                    characterGif.src = idleSrc;
                }
            }
        });

        function updateCharacterPosition() {
            // Prevent the character from moving beyond the left and right edges of the canvas
            if (character.moving === 'left') {
                character.x = Math.max(-character.width / 2, character.x - character.speed); // Allow the character to move fully to the left
            } else if (character.moving === 'right') {
                character.x = Math.min(canvas.width - character.width, character.x + character.speed); // Clamp to the right edge
            }

            // Update the character's position on the screen
            characterGif.style.left = `${character.x}px`;
            characterGif.style.top = `${character.y}px`;

            // Update the sipa position if it's not bouncing
            if (!sipaState.bouncing) {
                sipaState.x = character.x + character.width / 2 - sipaState.width / 2;
            }
        }

        function calculateKickTrajectory(hitXLeft, hitXRight) {
            const sipaCenterX = sipaState.x + sipaState.width / 2;
            const hitCenterX = (hitXLeft + hitXRight) / 2;
            const halfHitWidth = (hitXRight - hitXLeft) / 2;

            // Contact point and movement direction influence where the sipa flies.
            const contactOffset = Math.max(-1, Math.min(1, (sipaCenterX - hitCenterX) / halfHitWidth));
            const moveBias = character.moving === 'left' ? -0.25 : character.moving === 'right' ? 0.25 : 0;

            let direction = contactOffset + moveBias;
            if (Math.abs(direction) < 0.15) {
                direction = Math.random() < 0.5 ? -0.35 : 0.35;
            }

            const cornerMargin = canvas.width * 0.12;
            const targetX = direction < 0 ? cornerMargin : canvas.width - cornerMargin;
            const framesToApex = 52;

            let velocityX = (targetX - sipaCenterX) / framesToApex;
            velocityX = Math.max(-2.8, Math.min(2.8, velocityX));

            const liftVariation = (Math.random() - 0.5) * 0.5;
            const velocityY = -18.6 + liftVariation;

            return { velocityX, velocityY };
        }

        function updateSipaPosition() {
            if (sipaState.bouncing) {
                sipaState.velocityY += sipaState.gravity;
                sipaState.y += sipaState.velocityY;
                sipaState.x += sipaState.velocityX;

                if (sipaState.velocityY < 0) {
                    sipaState.angle = 180;
                } else {
                    sipaState.angle = 0;
                }

                const hitY = character.y + character.height * 0.6; // Around knee height
                const hitXLeft = character.x + character.width * 0.65;
                const hitXRight = character.x + character.width * 0.95;

                if (
                    sipaState.y + sipaState.height >= hitY &&
                    sipaState.y <= character.y + character.height &&
                    sipaState.x + sipaState.width >= hitXLeft &&
                    sipaState.x <= hitXRight
                ) {
                    // Trajectory follows kick contact so shots go toward corners naturally.
                    const kickTrajectory = calculateKickTrajectory(hitXLeft, hitXRight);
                    sipaState.y = hitY - sipaState.height;
                    sipaState.velocityY = kickTrajectory.velocityY;
                    sipaState.velocityX = kickTrajectory.velocityX;

                    createParticles(sipaState.x + sipaState.width / 2, sipaState.y + sipaState.height / 2);
                    createHitParticles(sipaState.x + sipaState.width / 2, sipaState.y + sipaState.height / 2);

                    sipa.style.transition = 'transform 0.1s';
                    sipa.style.transform = `scale(1.2) rotate(${sipaState.angle}deg)`;
                    setTimeout(() => {
                        sipa.style.transform = `scale(1) rotate(${sipaState.angle}deg)`;
                    }, 100);

                    updateScore();
                    updateCombo();
                    playHitSound();
                    flashScreen();

                    characterGif.src = CHARACTERS[selectedCharacter].kick;
                    setTimeout(() => {
                        if (character.moving) {
                            characterGif.src = runSrc;
                        } else {
                            characterGif.src = idleSrc;
                        }
                    }, 300); // Reduced from 1000ms to 300ms for a quicker kick
                }

                // Allow sipa to exceed above the top but bounce on the sides
                if (sipaState.x <= 0) {
                    sipaState.x = 0; // Prevent getting stuck by setting position to edge
                    sipaState.velocityX *= -1; // Reverse horizontal velocity
                } else if (sipaState.x + sipaState.width >= canvas.width) {
                    sipaState.x = canvas.width - sipaState.width; // Prevent getting stuck on right edge
                    sipaState.velocityX *= -1;
                }

                // Remove top bounce logic
                if (sipaState.y + sipaState.height >= canvas.height) {
                    // When the sipa touches the ground (miss)
                    lives--;
                    updateLivesDisplay();

                    // Add a "miss" effect
                    createMissEffect();

                    // Reset combo on miss
                    resetCombo();

                    // Play miss sound
                    playMissSound();

                    if (lives <= 0) {
                        showGameOverScreen();
                    } else {
                        // Reset sipa position to above the character
                        sipaState.bouncing = false;
                        sipaState.y = character.y - sipaState.height; // Above the character
                        sipaState.x = character.x + character.width / 2 - sipaState.width / 2 + 40; // <-- add +40 here too
                        sipaState.velocityY = 3.8; // Start falling again
                        sipaState.velocityX = 0; // No horizontal movement initially

                        setTimeout(() => {
                            // Allow bouncing again
                            sipaState.bouncing = true;
                        }, 1000); // Short delay before allowing bouncing again
                    }
                }
            }

            sipa.style.left = `${sipaState.x}px`;
            sipa.style.top = `${sipaState.y}px`;
            sipa.style.transform = `rotate(${sipaState.angle}deg)`;
        }

        // Updated createMissEffect() function
        function createMissEffect() {
            const missText = document.createElement('div');
            missText.textContent = 'MISS!';
            missText.style.position = 'absolute';
            missText.style.top = `${character.y - 50}px`;
            missText.style.left = `${character.x + character.width / 2 - 50}px`; // Centered above the character
            missText.style.fontSize = '32px';
            missText.style.color = 'red';
            missText.style.fontFamily = 'Press Start 2P, cursive';
            missText.style.textShadow = '2px 2px 4px rgba(0, 0, 0, 0.8)';
            missText.style.zIndex = '30';
            missText.style.animation = 'fadeOut 1.5s forwards';

            document.body.appendChild(missText);

            setTimeout(() => {
                missText.remove();
            }, 1500);
        }

        function displayHitFeedback() {
            const feedbackText = document.createElement('div');
            feedbackText.classList.add('feedbackText');

            // Set text based on combo streak
            if (combo < 5) {
                feedbackText.textContent = 'Nice!';
            } else if (combo < 10) {
                feedbackText.textContent = 'Good!';
            } else if (combo < 15) {
                feedbackText.textContent = 'Awesome!';
            } else {
                feedbackText.textContent = 'Unstoppable!';
            }

            feedbackText.style.position = 'absolute';
            feedbackText.style.top = `${character.y - 50}px`; // Position above the character
            feedbackText.style.left = `${character.x + character.width / 2 - 50}px`; // Centered above the character
            feedbackText.style.fontSize = '32px';
            feedbackText.style.color = '#ffcc00';
            feedbackText.style.fontFamily = 'Press Start 2P, cursive';
            feedbackText.style.textShadow = '2px 2px 4px rgba(0, 0, 0, 0.8)';
            feedbackText.style.zIndex = '30';
            feedbackText.style.animation = 'fadeOut 1.5s forwards';

            document.body.appendChild(feedbackText);

            // Remove feedback text after animation
            setTimeout(() => {
                feedbackText.remove();
            }, 1500);
        }

        function updateScore() {
            score++;
            scoreElement.textContent = 'Score: ' + score;

            // Switch background after scoring 10, 20, 30 with fade effect
            let newBg = null;
            if (score === 10) {
                newBg = '../assets/nightty.png';
            } else if (score === 20) {
                newBg = '../assets/7eleven.png';
            } else if (score === 30) {
                newBg = '../assets/kubo.png';
            }

            if (newBg) {
                const backgroundImage = document.getElementById('backgroundImage');
                const fadeOverlay = document.getElementById('bgFadeOverlay');
                // Bring overlay above background
                fadeOverlay.style.zIndex = '1';
                fadeOverlay.style.opacity = '1';
                setTimeout(() => {
                    backgroundImage.src = newBg;
                    // Wait a bit before fading out
                    setTimeout(() => {
                        fadeOverlay.style.opacity = '0';
                        // After fade out, send overlay back
                        setTimeout(() => {
                            fadeOverlay.style.zIndex = '0';
                        }, 700);
                    }, 200);
                }, 400);
            }
        }

        function updateLeaderboard(score) {
            const leaderboard = JSON.parse(localStorage.getItem('leaderboard')) || [];
            leaderboard.push(score);
            leaderboard.sort((a, b) => b - a); // Sort descending
            localStorage.setItem('leaderboard', JSON.stringify(leaderboard.slice(0, 5))); // Keep top 5
        }

        function showGameOverScreen() {
            if (score > highestScore) {
                highestScore = score;
            }
            
            gameOverScreen.style.display = 'flex';
            finalScore.textContent = `Final Score: ${score}`;
            document.getElementById('highestScore').textContent = `Highest Score: ${highestScore}`;
            
            // Save score to database if it's the highest
            if (score === highestScore) {
                saveScore(playerName, score);
            }
            
            // Display top scores
            fetchTopScores();
        }

        function saveScore(name, score) {
            const formData = new FormData();
            formData.append('playerName', name);
            formData.append('finalScore', score);
            formData.append('saveScore', true);

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => console.log('Score saved:', data))
            .catch(error => console.error('Error saving score:', error));
        }

        function fetchTopScores() {
            fetch('../api/get_top_scores.php')
            .then(response => response.json())
            .then(scores => {
                const topScoresDiv = document.getElementById('topScores');
                topScoresDiv.innerHTML = '<h3>Top Scores</h3>';
                const scoreList = document.createElement('div');
                scores.forEach((score, index) => {
                    scoreList.innerHTML += `
                        <div class="score-item">
                            ${index + 1}. ${score.player_name}: ${score.score}
                        </div>
                    `;
                });
                topScoresDiv.appendChild(scoreList);
            })
            .catch(error => console.error('Error fetching scores:', error));
        }

        function resetGame() {
            // Create and style overlay
            const overlay = document.createElement('div');
            overlay.style.position = 'fixed';
            overlay.style.top = '0';
            overlay.style.left = '0';
            overlay.style.width = '100%';
            overlay.style.height = '100%';
            overlay.style.backgroundColor = 'rgba(0, 0, 0, 0.7)';
            overlay.style.zIndex = '20';
            document.body.appendChild(overlay);

            // Show countdown overlay
            const countdownElement = document.getElementById('countdown');
            gameOverScreen.style.display = 'none';
            countdownElement.style.display = 'block';
            countdownElement.style.zIndex = '21';

            // Start retry countdown (no "GAME START")
            startCountdown(true);

            // Remove overlay after countdown
            setTimeout(() => {
                overlay.remove();
            }, 5000); // Enough time for countdown to finish
        }

        retryButton.addEventListener('click', resetGame);
        quitButton.addEventListener('click', () => {
            // Stop the background music if it's playing
            bgMusic.pause();
            bgMusic.currentTime = 0;
            
            // Redirect to startgame.php
            window.location.href = 'startgame.php';
        });

        // Replace the startCountdown function with this:
function startCountdown(isRetry = false) {
    const countdownElement = document.getElementById('countdown');
    const startMessage = document.getElementById('startMessage');
    let countdown = 3;

    // Hide character selection and other elements
    document.getElementById('characterSelection').style.display = 'none';
    startButton.style.display = 'none';
    if (startMessage) startMessage.style.display = 'none';

    // Show and style countdown
    countdownElement.style.display = 'block';
    countdownElement.style.fontSize = '60px';
    countdownElement.style.color = '#ffcc00';
    countdownElement.style.top = '50%';

    if (!isRetry) {
        // Show "GAME START" for 1 second, then countdown
        countdownElement.textContent = 'GAME START';
        setTimeout(() => {
            countdownElement.textContent = countdown;
            doCountdown();
        }, 1000);
    } else {
        // Directly start with 3, 2, 1, GO
        countdownElement.textContent = countdown;
        doCountdown();
    }

    function doCountdown() {
        const interval = setInterval(() => {
            if (countdown > 0) {
                countdownElement.textContent = countdown;
                countdownElement.style.transform = 'translate(-50%, -50%) scale(1.5)';
                setTimeout(() => {
                    countdownElement.style.transform = 'translate(-50%, -50%) scale(1)';
                }, 300);
            } else if (countdown === 0) {
                countdownElement.textContent = 'GO!';
                countdownElement.style.transform = 'translate(-50%, -50%) scale(2)';
                countdownElement.style.color = '#00ff00';
                setTimeout(() => {
                    clearInterval(interval);
                    countdownElement.style.display = 'none';
                    if (!isRetry) startScreen.style.display = 'none';
                    startGame();
                }, 1000);
            }
            countdown--;
        }, 1000);
    }
}

        function startGame() {
            // Reset game state
            score = 0;
            lives = 3;
            difficulty = 1;
            combo = 0;
            
            // Update displays
            scoreElement.textContent = 'Score: 0';
            updateLivesDisplay();
            
            // Reset sipa position
            sipaState.y = character.y - sipaState.height;
            sipaState.x = character.x + character.width / 2 - sipaState.width / 2 + 40;
            sipaState.velocityY = 3.8;
            sipaState.velocityX = 0;
            sipaState.bouncing = true;

            // Make sure character gif is set to idle state
            characterGif.src = CHARACTERS[selectedCharacter].idle;

            // Start background music
            bgMusic.play();

            // Start the game loop
            gameLoop();
        }

        startButton.addEventListener('click', () => {
            if (!document.getElementById('characterSelection').style.display || 
        document.getElementById('characterSelection').style.display === 'none') {
        // First click - Show character selection
        playerName = document.getElementById('playerName').value.trim();
        if (!playerName) {
            alert('Please enter your name first!');
            return;
        }
        
        document.getElementById('nameInput').style.display = 'none';
        document.getElementById('startMessage').style.display = 'none';
        document.getElementById('characterSelection').style.display = 'flex';
        startButton.textContent = 'START GAME';
        
        // Move startButton below character selection
        document.getElementById('characterSelection').appendChild(startButton);
    } else {
        // Second click - Start the game
        if (!characterSelected) {
            alert('Please select a character first!');
            return;
        }
        startCountdown(false); // Not a retry
    }
});

        window.onload = () => {
            startButton.style.display = 'block';
        };

        function gameLoop() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            updateCharacterPosition();
            updateSipaPosition();
            updateParticles();

            if (lives > 0) {
                requestAnimationFrame(gameLoop);
            } else {
                showGameOverScreen();
            }
        }

        // Add particle effects for special events
function createSpecialEffect(type) {
    const effects = {
        COMBO: { colors: ['#FFD700', '#FFA500'], particleCount: 20 },
        ACHIEVEMENT: { colors: ['#4CAF50', '#45A049'], particleCount: 30 },
        POWER_UP: { colors: ['#2196F3', '#1976D2'], particleCount: 15 }
    };
    
    const effect = effects[type];
    for (let i = 0; i < effect.particleCount; i++) {
        particles.push({
            x: Math.random() * canvas.width,
            y: Math.random() * canvas.height,
            size: Math.random() * 5 + 2,
            velocityX: (Math.random() - 0.5) * 4,
            velocityY: (Math.random() - 0.5) * 4,
            alpha: 1,
            color: effect.colors[i % effect.colors.length]
        });
    }
}

// Add this flag variable at the beginning of your script, where other state variables are defined
let isKicking = false;

// Then modify the kicking code like this:
if (!isKicking) {
    isKicking = true;
    characterGif.src = CHARACTERS[selectedCharacter].kick;
    setTimeout(() => {
        characterGif.src = idleSrc;
        isKicking = false;
    }, 500); // Reduced to 500ms for a quicker kick animation
}
    </script>
</body>
</html>
