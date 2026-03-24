<?php

include '../dbconnection.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2 Player Sipa Game</title>
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Press Start 2P', cursive;
            background-color: #06121f;
            background-image:
                radial-gradient(circle at 15% 20%, rgba(0, 214, 255, 0.18) 0%, transparent 40%),
                radial-gradient(circle at 85% 80%, rgba(0, 255, 170, 0.14) 0%, transparent 45%),
                linear-gradient(rgba(0, 214, 255, 0.12) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 214, 255, 0.12) 1px, transparent 1px),
                linear-gradient(135deg, #06121f 0%, #0a1e33 55%, #102943 100%);
            background-size: auto, auto, 40px 40px, 40px 40px, auto;
            background-position: center, center, 0 0, 0 0, center;
            animation: techGridShift 18s linear infinite;
            color: white;
            overflow: hidden;
        }

        @keyframes techGridShift {
            0% {
                background-position: center, center, 0 0, 0 0, center;
            }
            100% {
                background-position: center, center, 40px 40px, 40px 40px, center;
            }
        }

        #startScreen {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            z-index: 20;
            background: rgba(0, 0, 0, 0.8);
            padding: 40px;
            border-radius: 15px;
            border: 3px solid #ffcc00;
        }

        #characterSelection {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin: 30px 0;
            flex-wrap: wrap;
        }

        .character-options {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            justify-content: center;
            max-width: 800px;
            margin: 0 auto;
        }

        .character-option {
            border: 3px solid #444;
            border-radius: 10px;
            padding: 15px;
            cursor: pointer;
            transition: all 0.3s;
            background: rgba(255, 255, 255, 0.1);
            text-align: center;
            width: 150px;
            height: 180px;
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .character-option:hover {
            border-color: #ffcc00;
            transform: scale(1.05);
            box-shadow: 0 0 20px rgba(255, 204, 0, 0.5);
        }

        .character-option.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
        }

        .character-option.player1-selected {
            border-color: #00ff00;
            background: rgba(0, 255, 0, 0.2);
            box-shadow: 0 0 25px rgba(0, 255, 0, 0.7);
        }

        .character-option.player2-selected {
            border-color: #ff0080;
            background: rgba(255, 0, 128, 0.2);
            box-shadow: 0 0 25px rgba(255, 0, 128, 0.7);
        }

        .character-option.unavailable {
            opacity: 0.7;
            border: 3px dashed #666;
        }

        .character-option.unavailable:hover {
            border-color: #ff6666;
            box-shadow: 0 0 10px rgba(255, 102, 102, 0.5);
        }

        .character-option img {
            width: 80px;
            height: 100px;
            object-fit: contain;
            border-radius: 5px;
            margin-bottom: 10px;
        }

        .character-option p {
            margin: 0;
            font-size: 10px;
            color: #ffcc00;
        }

        .player-label {
            position: absolute;
            top: -10px;
            right: -10px;
            background: linear-gradient(45deg, #ffcc00, #ff9900);
            color: black;
            font-size: 10px;
            padding: 5px 8px;
            border-radius: 15px;
            font-weight: bold;
            border: 2px solid #fff;
        }

        .player1-label {
            background: linear-gradient(45deg, #00ff00, #00cc00);
        }

        .player2-label {
            background: linear-gradient(45deg, #ff0080, #cc0066);
            color: white;
        }

        .selection-title {
            font-size: 18px;
            margin-bottom: 20px;
            color: #ffcc00;
        }

        .controls-info {
            font-size: 10px;
            margin-bottom: 15px;
            color: #ccc;
            line-height: 1.5;
        }

        .select-button {
            background: linear-gradient(45deg, #ffcc00, #ff9900);
            border: none;
            padding: 15px 30px;
            font-family: 'Press Start 2P', cursive;
            font-size: 12px;
            cursor: pointer;
            border-radius: 5px;
            margin-top: 20px;
            transition: all 0.3s;
        }

        .select-button:hover {
            transform: scale(1.1);
            box-shadow: 0 0 20px rgba(255, 204, 0, 0.8);
        }

        .select-button:disabled {
            background: #666;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .reselect-hint {
            font-size: 8px;
            color: #ffcc00;
            margin-top: 5px;
            opacity: 0.8;
        }

        /* Game Area Styles - Keep exactly the same */
        #gameArea {
            display: none;
            position: relative;
            width: 100vw;
            height: 100vh;
            background-color: #06121f;
            background-image:
                radial-gradient(circle at 20% 15%, rgba(0, 214, 255, 0.18) 0%, transparent 35%),
                radial-gradient(circle at 80% 85%, rgba(0, 255, 170, 0.14) 0%, transparent 40%),
                linear-gradient(rgba(0, 214, 255, 0.12) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 214, 255, 0.12) 1px, transparent 1px),
                linear-gradient(140deg, #06121f 0%, #0a1e33 52%, #102943 100%);
            background-size: auto, auto, 36px 36px, 36px 36px, auto;
            background-position: center, center, 0 0, 0 0, center;
            animation: techGridShift 16s linear infinite;
            overflow: hidden;
        }

        #gameContainer {
            position: relative;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .player-character {
            position: absolute;
            bottom: 10px;
            width: 240px;
            height: 300px;
            z-index: 10;
        }

        .player-character img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        #player1Character {
            left: 100px;
        }

        #player2Character {
            right: 100px;
        }

        .controls-display {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            font-family: 'Press Start 2P', cursive;
            font-size: 10px;
            color: #ffcc00;
            text-align: center;
            background: rgba(0, 0, 0, 0.7);
            padding: 10px 20px;
            border-radius: 10px;
            border: 2px solid #ffcc00;
        }

        .middle-line {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            bottom: 10px;
            width: 8px;
            height: 350px;
            background: linear-gradient(
                to right,
                #8a97a3 0%,
                #d7e0e8 20%,
                #b9c4cf 45%,
                #eef3f8 55%,
                #8f9da9 80%,
                #6f7b87 100%
            );
            border: 2px solid #6b7782;
            border-radius: 4px;
            box-shadow:
                0 0 10px rgba(0, 0, 0, 0.45),
                0 0 16px rgba(200, 220, 235, 0.2);
            z-index: 5;
        }

        .net-top {
            position: absolute;
            top: -15px;
            left: 50%;
            transform: translateX(-50%);
            width: 16px;
            height: 15px;
            background: linear-gradient(to bottom, #dfe7ee, #8d9aa6);
            border-radius: 8px 8px 0 0;
            border: 2px solid #6b7782;
            box-shadow: 0 0 6px rgba(180, 205, 225, 0.35);
        }

        .net-mesh {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            bottom: 10px;
            width: 120px;
            height: 350px;
            background-image: 
                repeating-linear-gradient(
                    0deg,
                    transparent,
                    transparent 8px,
                    rgba(175, 215, 235, 0.28) 8px,
                    rgba(175, 215, 235, 0.28) 9px
                ),
                repeating-linear-gradient(
                    90deg,
                    transparent,
                    transparent 12px,
                    rgba(175, 215, 235, 0.24) 12px,
                    rgba(175, 215, 235, 0.24) 13px
                );
            opacity: 0.95;
            filter: drop-shadow(0 0 4px rgba(140, 195, 230, 0.25));
            pointer-events: none;
            z-index: 4;
        }

        .net-2d .net-mesh {
            background-image:
                repeating-linear-gradient(
                    0deg,
                    rgba(160, 210, 235, 0.18) 0px,
                    rgba(160, 210, 235, 0.18) 2px,
                    transparent 2px,
                    transparent 10px
                ),
                repeating-linear-gradient(
                    90deg,
                    rgba(160, 210, 235, 0.16) 0px,
                    rgba(160, 210, 235, 0.16) 2px,
                    transparent 2px,
                    transparent 14px
                );
            opacity: 0.75;
            filter: none;
        }

        .net-shadow {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            bottom: 5px;
            width: 12px;
            height: 8px;
            background: radial-gradient(ellipse, rgba(35,50,62,0.5) 0%, transparent 70%);
            z-index: 3;
        }

        .net-collision-debug {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            bottom: 10px;
            width: 8px;
            height: 350px;
            background: rgba(255,0,0,0.1);
            border: 1px dashed rgba(255,0,0,0.3);
            display: none;
            z-index: 6;
        }

        .ground {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 80px;
            background: linear-gradient(to bottom, rgba(0,0,0,0.3), rgba(0,0,0,0.6));
            border-top: 2px solid #ffcc00;
        }

        .hit-popup {
            position: absolute;
            font-family: 'Press Start 2P', cursive;
            font-size: 14px;
            z-index: 1000;
            pointer-events: none;
            text-shadow: 2px 2px 0px #000000;
            animation: retroHitPopup 1.5s ease-out forwards;
        }

        @keyframes retroHitPopup {
            0% {
                opacity: 1;
                transform: translateY(0px) scale(0.8);
            }
            25% {
                opacity: 1;
                transform: translateY(-20px) scale(1.3);
            }
            50% {
                opacity: 1;
                transform: translateY(-40px) scale(1.1);
            }
            100% {
                opacity: 0;
                transform: translateY(-80px) scale(0.9);
            }
        }

        .player-info {
            position: absolute;
            top: 20px;
            font-family: 'Press Start 2P', cursive;
            z-index: 15;
            background: linear-gradient(135deg, rgba(0,0,0,0.9), rgba(0,0,0,0.7));
            border: 3px solid;
            border-radius: 15px;
            padding: 15px 20px;
            box-shadow: 0 0 20px rgba(0,0,0,0.5);
            min-width: 180px;
        }

        #player1Info {
            left: 20px;
            border-color: #00FF41;
            box-shadow: 0 0 20px rgba(0,255,65,0.3);
        }

        #player2Info {
            right: 20px;
            border-color: #FF073A;
            box-shadow: 0 0 20px rgba(255,7,58,0.3);
        }

        .player-info .player-name {
            font-size: 12px;
            margin-bottom: 10px;
            text-align: center;
            letter-spacing: 1px;
        }

        #player1Info .player-name {
            color: #00FF41;
            text-shadow: 0 0 10px #00FF41;
        }

        #player2Info .player-name {
            color: #FF073A;
            text-shadow: 0 0 10px #FF073A;
        }

        .score-container {
            text-align: center;
            margin-top: 5px;
        }

        .score-label {
            font-size: 8px;
            color: #FFFFFF;
            margin-bottom: 5px;
            opacity: 0.8;
        }

        .score-value {
            font-size: 24px;
            font-weight: bold;
            color: #FFFFFF;
            text-shadow: 2px 2px 0px #000000;
        }

        #player1Info .score-value {
            color: #00FF41;
            text-shadow: 2px 2px 0px #000000, 0 0 15px #00FF41;
        }

        #player2Info .score-value {
            color: #FF073A;
            text-shadow: 2px 2px 0px #000000, 0 0 15px #FF073A;
        }

        @media (max-width: 768px) {
            .character-options {
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
                max-width: 400px;
            }
            
            .character-option {
                width: 140px;
                height: 160px;
            }
            
            .character-option img {
                width: 70px;
                height: 85px;
            }
        }
    </style>
</head>
<body>
    <!-- Character Selection Screen -->
    <div id="startScreen">
        <div id="startMessage">2 PLAYER SIPA GAME</div>
        
        <div id="characterSelection">
            <div class="selection-title">BOTH PLAYERS - SELECT YOUR CHARACTERS</div>
            <div class="controls-info">
                Click on a character to select
            </div>
            <div class="character-options">
                <div class="character-option" data-character="female" data-index="0">
                    <img src="../assets/ramona-idle.gif" alt="Female Character">
                    <p>FEMALE</p>
                </div>
                <div class="character-option" data-character="male" data-index="1">
                    <img src="../assets/male-idle.gif" alt="Male Character">
                    <p>MALE</p>
                </div>
                <div class="character-option" data-character="warrior" data-index="2">
                    <img src="../assets/warrior-idle.gif" alt="Warrior Character">
                    <p>WARRIOR</p>
                </div>
                <div class="character-option" data-character="ninja" data-index="3">
                    <img src="../assets/ninja-idle.gif" alt="Ninja Character">
                    <p>NINJA</p>
                </div>
                <div class="character-option" data-character="mage" data-index="4">
                    <img src="../assets/mage-idle.gif" alt="Mage Character">
                    <p>MAGE</p>
                </div>
                <div class="character-option" data-character="archer" data-index="5">
                    <img src="../assets/archer-idle.gif" alt="Archer Character">
                    <p>ARCHER</p>
                </div>
                <div class="character-option" data-character="knight" data-index="6">
                    <img src="../assets/knight-idle.gif" alt="Knight Character">
                    <p>KNIGHT</p>
                </div>
                <div class="character-option" data-character="rogue" data-index="7">
                    <img src="../assets/rogue-idle.gif" alt="Rogue Character">
                    <p>ROGUE</p>
                </div>
            </div>
        </div>
        
        <button id="startButton" class="select-button" disabled>WAITING FOR SELECTIONS</button>
    </div>

    <!-- Game Area - Keep exactly the same -->
    <div id="gameArea" style="display: none;">
        <div id="gameContainer">
            <div id="player1Character" class="player-character" style="left: 100px;">
                <img id="player1Sprite" src="" alt="Player 1">
            </div>
            
            <div id="player2Character" class="player-character" style="right: 100px;">
                <img id="player2Sprite" src="" alt="Player 2">
            </div>
            
            <div class="net-shadow"></div>
            <div class="net-mesh"></div>
            <div class="middle-line">
                <div class="net-top"></div>
            </div>
            <div class="net-collision-debug"></div>
            
            <div class="ground"></div>
            
            <div id="player1Info" class="player-info">
                <div class="player-name">PLAYER 1</div>
                <div class="score-container">
                    <div class="score-label">SCORE</div>
                    <div class="score-value" id="player1Score">0</div>
                </div>
            </div>
            <div id="player2Info" class="player-info">
                <div class="player-name">PLAYER 2</div>
                <div class="score-container">
                    <div class="score-label">SCORE</div>
                    <div class="score-value" id="player2Score">0</div>
                </div>
            </div>
            
            <div class="controls-display">
                PLAYER 1: A/D - MOVE | PLAYER 2: J/L - MOVE
            </div>
            
            <img id="sipa" src="../assets/sipa3.png" alt="Sipa" 
                 style="position: absolute; width: 70px; height: 70px; z-index: 15; display: none;">
            
            <div id="countdown" style="display: none;"></div>
        </div>
    </div>

    <script>
// Keep all the JavaScript exactly the same as your backup
const CHARACTERS = {
    female: {
        idle: '../assets/ramona-idle.gif',
        kick: '../assets/ramona-kick.gif',
        run: '../assets/ramona-run.gif'
    },
    male: {
        idle: '../assets/male-idle.gif',
        kick: '../assets/male-kick.gif',
        run: '../assets/male-run.gif'
    },
    warrior: {
        idle: '../assets/warrior-idle.gif',
        kick: '../assets/warrior-kick.gif',
        run: '../assets/warrior-run.gif'
    },
    ninja: {
        idle: '../assets/ninja-idle.gif',
        kick: '../assets/ninja-kick.gif',
        run: '../assets/ninja-run.gif'
    },
    mage: {
        idle: '../assets/mage-idle.gif',
        kick: '../assets/mage-kick.gif',
        run: '../assets/mage-run.gif'
    },
    archer: {
        idle: '../assets/archer-idle.gif',
        kick: '../assets/archer-kick.gif',
        run: '../assets/archer-run.gif'
    },
    knight: {
        idle: '../assets/knight-idle.gif',
        kick: '../assets/knight-kick.gif',
        run: '../assets/knight-run.gif'
    },
    rogue: {
        idle: '../assets/rogue-idle.gif',
        kick: '../assets/rogue-kick.gif',
        run: '../assets/rogue-run.gif'
    }
};

let player1Character = null;
let player2Character = null;
let player1Selected = false;
let player2Selected = false;

// Game variables - Updated boundaries
let player1Position = 100;
let player2Position = 100;
const moveSpeed = 7;
const minPosition = 0;
const maxPosition = window.innerWidth / 2 - 120;
const NET_MODE = 'none'; // '2d' or 'none' (pole always stays visible)

// Sipa game variables
let currentTurn = 1;
let isGameActive = false;
let gameLoopRunning = false;
let player1Score = 0;
let player2Score = 0;

// Sipa State - FIXED: Only one declaration
let sipaState = {
    width: 70,
    height: 70,
    x: window.innerWidth / 2 - 35,
    y: 100,
    velocityY: 0,
    velocityX: 0,
    gravity: 0.38,
    bouncing: false,
    angle: 0
};

// Player States
const playerStates = [
    {
        x: player1Position,
        y: window.innerHeight - 200,
        width: 240,
        height: 300,
        moving: false,
        isKicking: false,
        direction: 1,
        currentSprite: 'idle'
    },
    {
        x: window.innerWidth - player2Position - 240,
        y: window.innerHeight - 200,
        width: 240,
        height: 300,
        moving: false,
        isKicking: false,
        direction: -1,
        currentSprite: 'idle'
    }
];

// Audio
const hitSound = new Audio('../assets/hit.mp3');
const missSound = new Audio('../assets/miss.mp3');

function playHitSound() {
    hitSound.currentTime = 0;
    hitSound.play().catch(e => console.log('Audio play failed:', e));
}

function playMissSound() {
    missSound.currentTime = 0;
    missSound.play().catch(e => console.log('Audio play failed:', e));
}

// Character selection logic
const characterOptions = document.querySelectorAll('.character-option');
const startButton = document.getElementById('startButton');
const selectionTitle = document.querySelector('.selection-title');

function updateTitle() {
    if (!player1Selected && !player2Selected) {
        selectionTitle.textContent = 'BOTH PLAYERS - SELECT YOUR CHARACTERS';
    } else if (player1Selected && !player2Selected) {
        selectionTitle.textContent = 'PLAYER 2 - SELECT YOUR CHARACTER';
    } else if (!player1Selected && player2Selected) {
        selectionTitle.textContent = 'PLAYER 1 - SELECT YOUR CHARACTER';
    } else {
        selectionTitle.textContent = 'READY TO FIGHT!';
    }
}

characterOptions.forEach((option, index) => {
    const hint = document.createElement('div');
    hint.className = 'reselect-hint';
    hint.textContent = 'Click to select';
    option.appendChild(hint);

    option.addEventListener('click', () => {
        const character = option.dataset.character;
        
        if (option.classList.contains('player1-selected')) {
            unselectPlayer(1, option);
            return;
        }
        
        if (option.classList.contains('player2-selected')) {
            unselectPlayer(2, option);
            return;
        }
        
        if (!option.classList.contains('player1-selected') && !option.classList.contains('player2-selected')) {
            if (!player1Selected) {
                selectPlayer(1, character, option);
            } else if (!player2Selected) {
                selectPlayer(2, character, option);
            }
        }
    });
});

function selectPlayer(player, character, option) {
    if (player === 1) {
        player1Character = character;
        player1Selected = true;
        option.classList.add('player1-selected');
        
        const label = document.createElement('div');
        label.className = 'player-label player1-label';
        label.textContent = '1P';
        option.appendChild(label);
        
        option.querySelector('.reselect-hint').textContent = 'Click to reselect';
        
    } else if (player === 2) {
        player2Character = character;
        player2Selected = true;
        option.classList.add('player2-selected');
        
        const label = document.createElement('div');
        label.className = 'player-label player2-label';
        label.textContent = '2P';
        option.appendChild(label);
        
        option.querySelector('.reselect-hint').textContent = 'Click to reselect';
    }
    
    updateAvailability();
    updateTitle();
    checkIfBothSelected();
}

function unselectPlayer(player, option) {
    if (player === 1) {
        player1Character = null;
        player1Selected = false;
        option.classList.remove('player1-selected');
        
        const label = option.querySelector('.player1-label');
        if (label) label.remove();
        
    } else if (player === 2) {
        player2Character = null;
        player2Selected = false;
        option.classList.remove('player2-selected');
        
        const label = option.querySelector('.player2-label');
        if (label) label.remove();
    }
    
    option.querySelector('.reselect-hint').textContent = 'Click to select';
    
    updateAvailability();
    updateTitle();
    checkIfBothSelected();
}

function updateAvailability() {
    characterOptions.forEach(option => {
        const isP1Selected = option.classList.contains('player1-selected');
        const isP2Selected = option.classList.contains('player2-selected');
        
        option.classList.remove('unavailable');
        
        if (player1Selected && player2Selected && !isP1Selected && !isP2Selected) {
            option.classList.add('unavailable');
            option.querySelector('.reselect-hint').textContent = 'Unavailable';
        }
    });
}

function checkIfBothSelected() {
    if (player1Selected && player2Selected) {
        startButton.disabled = false;
        startButton.textContent = 'START GAME';
    } else {
        startButton.disabled = true;
        startButton.textContent = 'WAITING FOR SELECTIONS';
    }
}

updateTitle();

startButton.addEventListener('click', () => {
    if (player1Selected && player2Selected) {
        startGame();
    }
});

function startGame() {
    document.getElementById('startScreen').style.display = 'none';
    
    // Use kick sprites as default since they exist
    document.getElementById('player1Sprite').src = CHARACTERS[player1Character].kick;
    document.getElementById('player2Sprite').src = CHARACTERS[player2Character].kick;
    
    // Determine first player with better method
    currentTurn = determineFirstPlayer();
    
    // Wait for message to show, then start round
    setTimeout(() => {
        startRound();
    }, 3500);
}

// Remove dice roll functionality and replace with better first turn determination
function determineFirstPlayer() {
    const methods = [
        'coin_flip',
        'youngest_goes_first', 
        'guest_courtesy',
        'rock_paper_scissors',
        'random_fair'
    ];
    
    const selectedMethod = methods[Math.floor(Math.random() * methods.length)];
    
    switch(selectedMethod) {
        case 'coin_flip':
            return coinFlipMethod();
        case 'youngest_goes_first':
            return youngestPlayerMethod();
        case 'guest_courtesy':
            return guestCourtesyMethod();
        case 'rock_paper_scissors':
            return rockPaperScissorsMethod();
        default:
            return randomFairMethod();
    }
}

function coinFlipMethod() {
    const result = Math.random() < 0.5 ? 1 : 2;
    showFirstTurnMessage(`🪙 COIN FLIP: Player ${result} wins the toss!`);
    return result;
}

function youngestPlayerMethod() {
    // Simulate age comparison
    const player1Age = Math.floor(Math.random() * 30) + 18;
    const player2Age = Math.floor(Math.random() * 30) + 18;
    
    if (player1Age < player2Age) {
        showFirstTurnMessage(`👶 YOUNGEST GOES FIRST: Player 1 (${player1Age}) vs Player 2 (${player2Age})`);
        return 1;
    } else if (player2Age < player1Age) {
        showFirstTurnMessage(`👶 YOUNGEST GOES FIRST: Player 2 (${player2Age}) vs Player 1 (${player1Age})`);
        return 2;
    } else {
        showFirstTurnMessage(`👶 SAME AGE! Random selection...`);
        return Math.random() < 0.5 ? 1 : 2;
    }
}

function guestCourtesyMethod() {
    const guest = Math.random() < 0.5 ? 1 : 2;
    showFirstTurnMessage(`🎩 GUEST COURTESY: Player ${guest} is the guest and goes first!`);
    return guest;
}

function rockPaperScissorsMethod() {
    const choices = ['Rock', 'Paper', 'Scissors'];
    const p1Choice = choices[Math.floor(Math.random() * 3)];
    const p2Choice = choices[Math.floor(Math.random() * 3)];
    
    if (p1Choice === p2Choice) {
        showFirstTurnMessage(`✂️ ROCK PAPER SCISSORS: Tie! (${p1Choice} vs ${p2Choice}) Random selection...`);
        return Math.random() < 0.5 ? 1 : 2;
    }
    
    const p1Wins = (p1Choice === 'Rock' && p2Choice === 'Scissors') ||
                   (p1Choice === 'Paper' && p2Choice === 'Rock') ||
                   (p1Choice === 'Scissors' && p2Choice === 'Paper');
    
    const winner = p1Wins ? 1 : 2;
    showFirstTurnMessage(`✂️ ROCK PAPER SCISSORS: Player ${winner} wins! (${p1Choice} vs ${p2Choice})`);
    return winner;
}

function randomFairMethod() {
    const result = Math.random() < 0.5 ? 1 : 2;
    showFirstTurnMessage(`🎲 RANDOM SELECTION: Player ${result} goes first!`);
    return result;
}

function showFirstTurnMessage(message) {
    const messageDiv = document.createElement('div');
    messageDiv.style.cssText = `
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: linear-gradient(135deg, rgba(0,0,0,0.95), rgba(0,0,0,0.8));
        color: #FFD700;
        font-family: 'Press Start 2P', cursive;
        font-size: 14px;
        padding: 30px 40px;
        border-radius: 15px;
        border: 3px solid #FFD700;
        box-shadow: 0 0 30px rgba(255,215,0,0.5);
        z-index: 1000;
        text-align: center;
        max-width: 80%;
        line-height: 1.5;
        text-shadow: 2px 2px 0px #000000;
    `;
    messageDiv.innerHTML = message;
    document.body.appendChild(messageDiv);
    
    setTimeout(() => {
        messageDiv.remove();
    }, 3000);
}

// Update startGame function to use new first turn determination
function startGame() {
    document.getElementById('startScreen').style.display = 'none';
    
    // Use kick sprites as default since they exist
    document.getElementById('player1Sprite').src = CHARACTERS[player1Character].kick;
    document.getElementById('player2Sprite').src = CHARACTERS[player2Character].kick;
    
    // Determine first player with better method
    currentTurn = determineFirstPlayer();
    
    // Wait for message to show, then start round
    setTimeout(() => {
        startRound();
    }, 3500);
}

// Update startRound function to remove dice roll references
function startRound() {
    console.log('Starting round...');
    
    const gameArea = document.getElementById('gameArea');
    gameArea.style.display = 'block';
    
    gameArea.innerHTML = `
        <div id="player1Info" class="player-info">
            <div class="player-name">PLAYER 1</div>
            <div class="score-container">
                <div class="score-label">SCORE</div>
                <div class="score-value" id="player1Score">0</div>
            </div>
        </div>
        
        <div id="player2Info" class="player-info">
            <div class="player-name">PLAYER 2</div>
            <div class="score-container">
                <div class="score-label">SCORE</div>
                <div class="score-value" id="player2Score">0</div>
            </div>
        </div>

        <div id="gameContainer">
            <div class="player-character" id="player1Character" style="position: absolute; bottom: 10px; left: ${player1Position}px; z-index: 5; width: 240px; height: 300px;">
                <img id="player1Sprite" src="${CHARACTERS[player1Character].idle}" alt="Player 1" style="width: 100%; height: 100%; object-fit: contain;">
            </div>
            
            <div class="player-character" id="player2Character" style="position: absolute; bottom: 10px; right: ${player2Position}px; z-index: 5; width: 240px; height: 300px; transform: scaleX(-1);">
                <img id="player2Sprite" src="${CHARACTERS[player2Character].idle}" alt="Player 2" style="width: 100%; height: 100%; object-fit: contain;">
            </div>
            
            <div class="net-shadow"></div>
            <div class="net-mesh"></div>
            <div class="middle-line">
                <div class="net-top"></div>
            </div>
            <div class="net-collision-debug"></div>
            
            <div class="ground"></div>
            
            <img id="sipa" src="../assets/sipa3.png" style="position: absolute; width: 70px; height: 70px; display: none; z-index: 10">
            
            <div id="countdown" style="display: none; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 72px; color: #FFD700; font-family: 'Press Start 2P', cursive; text-shadow: 4px 4px 0px #000000; z-index: 20;"></div>
        </div>

        <div class="controls-display">
            PLAYER 1: A/D - MOVE | PLAYER 2: J/L - MOVE
        </div>
    `;

    if (NET_MODE === 'none') {
        gameArea.querySelectorAll('.net-shadow, .net-mesh, .net-collision-debug').forEach(el => {
            el.style.display = 'none';
        });
    } else {
        gameArea.classList.add('net-2d');
    }
    
    initializeGameplay();
    setTimeout(() => {
        startCountdownAndDrop();
    }, 1000);
}

function initializeGameplay() {
    console.log('Initializing gameplay...');
    
    const keys = {};
    
    // Update player states with correct positions
    playerStates[0].x = player1Position;
    playerStates[0].y = window.innerHeight - 200;
    playerStates[0].width = 240;
    playerStates[0].height = 300;
    playerStates[0].direction = 1;
    playerStates[0].moving = false;
    playerStates[0].isKicking = false;
    playerStates[0].currentSprite = 'idle'; // Track current sprite state
    
    playerStates[1].x = window.innerWidth - player2Position - 240;
    playerStates[1].y = window.innerHeight - 200;
    playerStates[1].width = 240;
    playerStates[1].height = 300;
    playerStates[1].direction = -1;
    playerStates[1].moving = false;
    playerStates[1].isKicking = false;
    playerStates[1].currentSprite = 'idle'; // Track current sprite state
    
    const player1Sprite = document.getElementById('player1Sprite');
    const player2Sprite = document.getElementById('player2Sprite');
    const player1Element = document.getElementById('player1Character');
    const player2Element = document.getElementById('player2Character');
    
    // Position players on the yellow line
    if (player1Element) {
        player1Element.style.left = player1Position + 'px';
        player1Element.style.bottom = '10px';
        player1Element.style.transform = 'scaleX(1)';
    }
    
    if (player2Element) {
        player2Element.style.right = player2Position + 'px';
        player2Element.style.bottom = '10px';
        player2Element.style.transform = 'scaleX(-1)';
    }
    
    // Remove any existing event listeners to prevent duplicates
    document.removeEventListener('keydown', handleKeyDown);
    document.removeEventListener('keyup', handleKeyUp);
    
    // Set up movement controls with proper animation handling
    function handleKeyDown(e) {
        const key = e.key.toLowerCase();
        keys[key] = true;
        
        // Player 1 movement (A/D keys)
        if (key === 'a' && player1Position > minPosition) {
            if (!playerStates[0].isKicking) {
                playerStates[0].moving = true;
                if (player1Element) player1Element.style.transform = 'scaleX(-1)';
                // Only change to run sprite once
                if (playerStates[0].currentSprite !== 'run') {
                    playerStates[0].currentSprite = 'run';
                    if (player1Sprite && CHARACTERS[player1Character]?.run) {
                        player1Sprite.src = CHARACTERS[player1Character].run;
                    }
                }
            }
        } else if (key === 'd' && player1Position < maxPosition) {
            if (!playerStates[0].isKicking) {
                playerStates[0].moving = true;
                if (player1Element) player1Element.style.transform = 'scaleX(1)';
                // Only change to run sprite once
                if (playerStates[0].currentSprite !== 'run') {
                    playerStates[0].currentSprite = 'run';
                    if (player1Sprite && CHARACTERS[player1Character]?.run) {
                        player1Sprite.src = CHARACTERS[player1Character].run;
                    }
                }
            }
        }
        
        // Player 2 movement (J/L keys)
        if (key === 'j' && player2Position < maxPosition) {
            if (!playerStates[1].isKicking) {
                playerStates[1].moving = true;
                if (player2Element) player2Element.style.transform = 'scaleX(-1)';
                // Only change to run sprite once
                if (playerStates[1].currentSprite !== 'run') {
                    playerStates[1].currentSprite = 'run';
                    if (player2Sprite && CHARACTERS[player2Character]?.run) {
                        player2Sprite.src = CHARACTERS[player2Character].run;
                    }
                }
            }
        } else if (key === 'l' && player2Position > minPosition) {
            if (!playerStates[1].isKicking) {
                playerStates[1].moving = true;
                if (player2Element) player2Element.style.transform = 'scaleX(1)';
                // Only change to run sprite once
                if (playerStates[1].currentSprite !== 'run') {
                    playerStates[1].currentSprite = 'run';
                    if (player2Sprite && CHARACTERS[player2Character]?.run) {
                        player2Sprite.src = CHARACTERS[player2Character].run;
                    }
                }
            }
        }
    }
    
    function handleKeyUp(e) {
        const key = e.key.toLowerCase();
        keys[key] = false;
        
        // Player 1 stop animation
        if (key === 'a' || key === 'd') {
            if (!keys['a'] && !keys['d']) {
                playerStates[0].moving = false;
                // Only change to idle sprite once
                if (playerStates[0].currentSprite !== 'idle' && !playerStates[0].isKicking) {
                    playerStates[0].currentSprite = 'idle';
                    if (player1Sprite && CHARACTERS[player1Character]?.idle) {
                        player1Sprite.src = CHARACTERS[player1Character].idle;
                    }
                }
            } else {
                // Still moving, update direction based on currently pressed key
                if (keys['a'] && !playerStates[0].isKicking) {
                    if (player1Element) player1Element.style.transform = 'scaleX(-1)';
                } else if (keys['d'] && !playerStates[0].isKicking) {
                    if (player1Element) player1Element.style.transform = 'scaleX(1)';
                }
            }
        }
        
        // Player 2 stop animation
        if (key === 'j' || key === 'l') {
            if (!keys['j'] && !keys['l']) {
                playerStates[1].moving = false;
                // Only change to idle sprite once
                if (playerStates[1].currentSprite !== 'idle' && !playerStates[1].isKicking) {
                    playerStates[1].currentSprite = 'idle';
                    if (player2Sprite && CHARACTERS[player2Character]?.idle) {
                        player2Sprite.src = CHARACTERS[player2Character].idle;
                    }
                }
            } else {
                // Still moving, update direction based on currently pressed key
                if (keys['j'] && !playerStates[1].isKicking) {
                    if (player2Element) player2Element.style.transform = 'scaleX(-1)';
                } else if (keys['l'] && !playerStates[1].isKicking) {
                    if (player2Element) player2Element.style.transform = 'scaleX(1)';
                }
            }
        }
    }
    
    document.addEventListener('keydown', handleKeyDown);
    document.addEventListener('keyup', handleKeyUp);
    
    // Start movement loop - removed redundant animation changes
    function gameLoop() {
        // Player 1 movement with boundaries
        if (keys['a'] && player1Position > minPosition) {
            player1Position -= moveSpeed;
            if (player1Position < minPosition) player1Position = minPosition;
            playerStates[0].x = player1Position;
            if (player1Element) {
                player1Element.style.left = player1Position + 'px';
            }
        }
        if (keys['d'] && player1Position < maxPosition) {
            player1Position += moveSpeed;
            if (player1Position > maxPosition) player1Position = maxPosition;
            playerStates[0].x = player1Position;
            if (player1Element) {
                player1Element.style.left = player1Position + 'px';
            }
        }
        
        // Player 2 movement with boundaries
        if (keys['j'] && player2Position < maxPosition) {
            player2Position += moveSpeed;
            if (player2Position > maxPosition) player2Position = maxPosition;
            playerStates[1].x = window.innerWidth - player2Position - 240;
            if (player2Element) {
                player2Element.style.right = player2Position + 'px';
            }
        }
        if (keys['l'] && player2Position > minPosition) {
            player2Position -= moveSpeed;
            if (player2Position < minPosition) player2Position = minPosition;
            playerStates[1].x = window.innerWidth - player2Position - 240;
            if (player2Element) {
                player2Element.style.right = player2Position + 'px';
            }
        }
        
        if (isGameActive) {
            requestAnimationFrame(gameLoop);
        }
    }
    
    isGameActive = true;
    gameLoop();
}

function startCountdownAndDrop() {
    console.log('Starting countdown...');
    
    // Position sipa above the current player
    if (currentTurn === 1) {
        sipaState.x = playerStates[0].x + 60 - sipaState.width / 2;
    } else {
        sipaState.x = playerStates[1].x + 60 - sipaState.width / 2;
    }
    
    sipaState.y = 100;
    sipaState.velocityX = 0;
    sipaState.velocityY = 0;
    sipaState.bouncing = false;
    sipaState.angle = 0;
    
    // Make sure sipa is visible
    const sipaImg = document.getElementById('sipa');
    if (sipaImg) {
        sipaImg.style.display = 'block';
        sipaImg.style.left = sipaState.x + 'px';
        sipaImg.style.top = sipaState.y + 'px';
        sipaImg.style.transform = 'rotate(0deg)';
    }
    
    let count = 3;
    const countdownElement = document.getElementById('countdown');
    if (countdownElement) {
        countdownElement.style.display = 'block';
        countdownElement.textContent = count;
        
        const countdownInterval = setInterval(() => {
            count--;
            if (count > 0) {
                countdownElement.textContent = count;
            } else if (count === 0) {
                countdownElement.textContent = 'GO!';
            } else {
                countdownElement.style.display = 'none';
                clearInterval(countdownInterval);
                dropBall();
            }
        }, 1000);
    } else {
        // If no countdown element, just drop the ball after a short delay
        setTimeout(() => {
            dropBall();
        }, 1000);
    }
}

function dropBall() {
    console.log('Dropping ball...');
    isGameActive = true;
    sipaState.bouncing = true;
    sipaState.velocityY = 1.8; // Slower initial drop
    sipaState.velocityX = 0; // Start with no horizontal movement
    
    // Make sure the physics loop starts
    if (!gameLoopRunning) {
        gameLoopRunning = true;
        sipaPhysicsLoop();
    }
}

function sipaPhysicsLoop() {
    if (!sipaState.bouncing) return;
    
    // Update player positions in the loop
    playerStates[0].x = player1Position;
    playerStates[0].y = window.innerHeight - 200;
    playerStates[1].x = window.innerWidth - player2Position - 240;
    playerStates[1].y = window.innerHeight - 200;
    
    sipaState.velocityY += sipaState.gravity;
    sipaState.x += sipaState.velocityX;
    sipaState.y += sipaState.velocityY;
    
    // Pole collision is always active so what players see matches gameplay.
    const netCenterX = window.innerWidth / 2;
    const netWidth = 8;
    const netHeight = NET_MODE === 'none' ? 350 : 250;
    const netLeft = netCenterX - netWidth / 2;
    const netRight = netCenterX + netWidth / 2;
    const netTop = window.innerHeight - 10 - netHeight;
    const netBottom = window.innerHeight - 10;

    if (sipaState.y + sipaState.height > netTop && sipaState.y < netBottom) {
        if (sipaState.x + sipaState.width > netLeft && sipaState.x < netRight) {
            if (sipaState.velocityX > 0) {
                sipaState.x = netLeft - sipaState.width;
                sipaState.velocityX = -Math.abs(sipaState.velocityX) * 0.8;
            } else {
                sipaState.x = netRight;
                sipaState.velocityX = Math.abs(sipaState.velocityX) * 0.8;
            }
            sipaState.velocityY *= 0.6;
        }
    }
    
    // Bounce off left and right walls (corners allowed)
    if (sipaState.x <= 0) {
        sipaState.x = 0;
        sipaState.velocityX = Math.abs(sipaState.velocityX) * 0.8;
    }
    if (sipaState.x + sipaState.width >= window.innerWidth) {
        sipaState.x = window.innerWidth - sipaState.width;
        sipaState.velocityX = -Math.abs(sipaState.velocityX) * 0.8;
    }
    
    sipaState.angle += 5;
    
    const sipaImg = document.getElementById('sipa');
    if (sipaImg) {
        sipaImg.style.left = sipaState.x + 'px';
        sipaImg.style.top = sipaState.y + 'px';
        sipaImg.style.transform = `rotate(${sipaState.angle}deg)`;
        sipaImg.style.display = 'block';
    }
    
    checkPlayerCollisions();
    
    // Sipa hits yellow line
    if (sipaState.y + sipaState.height >= window.innerHeight - 10) {
        handleMiss();
        return;
    }
    
    if (gameLoopRunning && sipaState.bouncing) {
        requestAnimationFrame(sipaPhysicsLoop);
    }
}

function checkPlayerCollisions() {
    for (let i = 0; i < 2; i++) {
        const player = playerStates[i];
        
        // Define player collision bounds - slightly smaller for 50% overlap requirement
        const playerLeft = player.x + 20; // Small padding
        const playerRight = player.x + player.width - 20;
        const playerTop = player.y + 20;
        const playerBottom = player.y + player.height - 20;
        
        // Check if sipa overlaps 50% with player body
        const overlapLeft = Math.max(sipaState.x, playerLeft);
        const overlapRight = Math.min(sipaState.x + sipaState.width, playerRight);
        const overlapTop = Math.max(sipaState.y, playerTop);
        const overlapBottom = Math.min(sipaState.y + sipaState.height, playerBottom);
        
        if (overlapLeft < overlapRight && overlapTop < overlapBottom) {
            const overlapWidth = overlapRight - overlapLeft;
            const overlapHeight = overlapBottom - overlapTop;
            const overlapArea = overlapWidth * overlapHeight;
            const sipaArea = sipaState.width * sipaState.height;
            const overlapPercentage = overlapArea / sipaArea;
            
            // Trigger collision if sipa overlaps 50% or more with player
            if (overlapPercentage >= 0.5) {
                console.log(`Player ${i + 1} collision detected! Overlap: ${(overlapPercentage * 100).toFixed(1)}%`);
                triggerAutoKick(i);
                break;
            }
        }
    }
}

function createHighKickEffect(x, y) {
    for (let i = 0; i < 15; i++) {
        const sparkle = document.createElement('div');
        sparkle.style.position = 'absolute';
        sparkle.style.left = x + 'px';
        sparkle.style.top = y + 'px';
        sparkle.style.width = '6px';
        sparkle.style.height = '6px';
        sparkle.style.background = '#ffff00';
        sparkle.style.borderRadius = '50%';
        sparkle.style.pointerEvents = 'none';
        sparkle.style.zIndex = '20';
        sparkle.style.boxShadow = '0 0 10px #ffff00';
        document.body.appendChild(sparkle);
        
        const angle = (Math.PI * 2 * i) / 15;
        const speed = 8 + Math.random() * 6;
        let sparkleX = 0;
        let sparkleY = 0;
        
        const animateSparkle = () => {
            sparkleX += Math.cos(angle) * speed;
            sparkleY += Math.sin(angle) * speed - 2;
            sparkle.style.transform = `translate(${sparkleX}px, ${sparkleY}px)`;
            sparkle.style.opacity = parseFloat(sparkle.style.opacity || 1) - 0.03;
            
            if (parseFloat(sparkle.style.opacity) > 0) {
                requestAnimationFrame(animateSparkle);
            } else {
                sparkle.remove();
            }
        };
        
        requestAnimationFrame(animateSparkle);
    }
}

function triggerAutoKick(playerIndex) {
    const player = playerStates[playerIndex];
    
    if (player.isKicking) return;
    
    player.isKicking = true;
    player.currentSprite = 'kick'; // Track kick sprite state
    
    const spriteElement = document.getElementById(`player${playerIndex + 1}Sprite`);
    const characterName = playerIndex === 0 ? player1Character : player2Character;
    
    // Change to kick sprite
    if (CHARACTERS[characterName]?.kick) {
        spriteElement.src = CHARACTERS[characterName].kick;
    }
    
    // Raise launch point and kick arc so volleys stay alive longer.
    sipaState.y = player.y + player.height * 0.22;
    
    const kickPower = 19 + Math.random() * 4;
    const angle = -68 * Math.PI / 180;
    
    sipaState.velocityY = Math.sin(angle) * kickPower;
    sipaState.velocityX = Math.cos(angle) * kickPower * player.direction;
    
    sipaState.velocityY -= 3.2;
    sipaState.angle = 180;
    
    if (player.direction > 0) {
        sipaState.x = player.x + player.width * 0.8;
    } else {
        sipaState.x = player.x + player.width * 0.2 - sipaState.width;
    }
    
    sipaState.velocityY += (Math.random() - 0.5) * 0.9;
    sipaState.velocityX += (Math.random() - 0.5) * 1.2;
    
    // Play sound and create retro popup
    playHitSound();
    createHitPopup(sipaState.x + sipaState.width / 2, sipaState.y);
    createHighKickEffect(sipaState.x + sipaState.width / 2, sipaState.y + sipaState.height / 2);
    
    console.log(`Player ${playerIndex + 1} kicked with power: ${kickPower}`);
    
    // Enhanced reset logic
    setTimeout(() => {
        player.isKicking = false;
        
        // Return to appropriate sprite based on current movement state
        if (playerStates[playerIndex].moving) {
            player.currentSprite = 'run';
            if (CHARACTERS[characterName]?.run) {
                spriteElement.src = CHARACTERS[characterName].run;
            }
        } else {
            player.currentSprite = 'idle';
            if (CHARACTERS[characterName]?.idle) {
                spriteElement.src = CHARACTERS[characterName].idle;
            }
        }
    }, 600);
}

// Enhanced hit popup function with retro styles
function createHitPopup(x, y) {
    const hitTexts = ["EXCELLENT!", "GREAT!", "GOOD!", "NICE HIT!", "COMBO!", "PERFECT!"];
    const colors = ["#FFD700", "#FF6B35", "#4ECDC4", "#45B7D1", "#96CEB4", "#FFEAA7"];
    
    const randomIndex = Math.floor(Math.random() * hitTexts.length);
    const popup = document.createElement('div');
    popup.textContent = hitTexts[randomIndex];
    popup.style.cssText = `
        position: absolute;
        left: ${x - 30}px;
        top: ${y - 20}px;
        font-family: 'Press Start 2P', cursive;
        font-size: 14px;
        font-weight: bold;
        color: ${colors[randomIndex]};
        text-shadow: 2px 2px 0px #000000;
        z-index: 1000;
        pointer-events: none;
        animation: popupFade 1.5s ease-out forwards;
    `;
    
    // Add animation style if not exists
    if (!document.getElementById('popupAnimation')) {
        const style = document.createElement('style');
        style.id = 'popupAnimation';
        style.textContent = `
            @keyframes popupFade {
                0% { transform: translateY(0) scale(0.8); opacity: 0; }
                30% { transform: translateY(-15px) scale(1.1); opacity: 1; }
                100% { transform: translateY(-60px) scale(1); opacity: 0; }
            }
        `;
        document.head.appendChild(style);
    }
    
    document.getElementById('gameContainer').appendChild(popup);
    
    setTimeout(() => {
        if (popup.parentNode) {
            popup.parentNode.removeChild(popup);
        }
    }, 1500);
}

function handleMiss() {
    console.log('Ball hit the ground!');
    playMissSound();
    
    gameLoopRunning = false;
    sipaState.bouncing = false;
    
    // Determine which side the ball landed on and award points
    const centerX = window.innerWidth / 2;
    if (sipaState.x + sipaState.width / 2 < centerX) {
        // Ball landed on Player 1's side - Player 2 gets point
        player2Score++;
        const scoreElement = document.getElementById('player2Score');
        if (scoreElement) scoreElement.textContent = player2Score;
    } else {
        // Ball landed on Player 2's side - Player 1 gets point
        player1Score++;
        const scoreElement = document.getElementById('player1Score');
        if (scoreElement) scoreElement.textContent = player1Score;
    }
    
    // Switch turns
    currentTurn = currentTurn === 1 ? 2 : 1;
    
    setTimeout(() => {
        resetRound();
    }, 2000);
}

function resetRound() {
    console.log('Resetting round...');
    
    sipaState.x = window.innerWidth / 2 - 35;
    sipaState.y = 100;
    sipaState.velocityX = 0;
    sipaState.velocityY = 0;
    sipaState.bouncing = false;
    sipaState.angle = 0;
    
    // Keep characters moveable
    isGameActive = true;
    gameLoopRunning = false;
    
    setTimeout(() => {
        startCountdownAndDrop();
    }, 1000);
}
    </script>
</body>
</html>