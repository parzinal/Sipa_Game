<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Just One Kick!</title>
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background: url('../assets/bgsipa.png') no-repeat center center fixed;
            background-size: cover; /* changed to cover for full fit */
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start; /* align items to top */
        }
        .title-img {
            display: block;
            margin: 40px auto 20px auto;
            max-width: 28vw; /* increased from 22vw */
            height: auto;
            animation: float 3s ease-in-out infinite;
            filter: drop-shadow(0 0 15px rgba(255, 255, 255, 0.6));
        }

        @keyframes float {
            0% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0); }
        }

        .button-group {
            display: flex;
            flex-direction: column;
            gap: 16px; /* reduced gap between buttons */
            align-items: center;
            margin-top: 20px;
        }
        .button-group button {
            width: 200px; /* reduced from 240px */
            padding: 12px 0; /* reduced from 15px */
            font-size: 1rem; /* reduced from 1.2rem */
            border: 3px solid #4FC3F7;
            border-radius: 10px;
            background: linear-gradient(to bottom, #2196F3, #1976D2);
            color: #fff;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Press Start 2P', cursive;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 6px 0 #0D47A1,
                       0 8px 10px rgba(0,0,0,0.3);
            text-shadow: 2px 2px 0 #0D47A1;
            position: relative;
            overflow: hidden;
        }

        .button-group button:before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                120deg,
                transparent,
                rgba(255, 255, 255, 0.3),
                transparent
            );
            transition: 0.5s;
        }

        .button-group button:hover {
            transform: translateY(2px);
            box-shadow: 0 4px 0 #0D47A1,
                       0 6px 8px rgba(0,0,0,0.3);
            background: linear-gradient(to bottom, #42A5F5, #2196F3);
        }

        .button-group button:hover:before {
            left: 100%;
        }

        .button-group button:active {
            transform: translateY(6px);
            box-shadow: 0 0 0 #0D47A1,
                       0 0 4px rgba(0,0,0,0.3);
        }

        #instructionContainer {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .instruction-box {
            background: #1a1a1a;
            border: 4px solid #4FC3F7;
            border-radius: 15px;
            padding: 30px;
            width: 80%;
            max-width: 500px;
            color: white;
            font-family: 'Press Start 2P', cursive;
            text-align: center;
        }

        .instruction-box h2 {
            color: #4FC3F7;
            margin-bottom: 20px;
            font-size: 24px;
        }

        .instruction-box h3 {
            color: #ffcc00;
            margin: 15px 0;
            font-size: 18px;
        }

        .controls, .mechanics {
            text-align: left;
            margin: 20px 0;
        }

        .controls p, .mechanics p {
            margin: 10px 0;
            font-size: 14px;
            line-height: 1.5;
        }

        .close-btn {
            margin-top: 20px;
            padding: 10px 20px;
            background: #4FC3F7;
            border: none;
            border-radius: 5px;
            color: white;
            font-family: 'Press Start 2P', cursive;
            cursor: pointer;
            transition: background 0.3s;
        }

        .close-btn:hover {
            background: #0D47A1;
        }

        .leaderboard-container {
            background: rgba(0, 0, 0, 0.8);
            border: 4px solid #4FC3F7;
            border-radius: 15px;
            padding: 20px;
            width: 80%;
            max-width: 500px;
            margin: 20px auto;
            box-shadow: 0 0 20px rgba(79, 195, 247, 0.3);
            opacity: 1;
            transition: opacity 0.3s ease;
        }

        .leaderboard-container[style*="display: none"] {
            opacity: 0;
        }

        .leaderboard-container h2 {
            color: #4FC3F7;
            font-family: 'Press Start 2P', cursive;
            text-align: center;
            margin-bottom: 20px;
            font-size: 24px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }

        .leaderboard-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .leaderboard-item {
            display: grid;
            grid-template-columns: 60px 1fr 100px;
            align-items: center;
            padding: 10px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 5px;
            font-family: 'Press Start 2P', cursive;
            transition: transform 0.2s;
        }

        .leaderboard-item:hover {
            transform: scale(1.02);
            background: rgba(255, 255, 255, 0.2);
        }

        .rank {
            color: #808080; /* Default gray color for ranks 4-10 */
            font-size: 16px;
            text-align: center;
        }

        /* Special colors for top 3 */
        .leaderboard-item:nth-child(1) .rank { color: #FFD700; } /* Gold */
        .leaderboard-item:nth-child(2) .rank { color: #C0C0C0; } /* Silver */
        .leaderboard-item:nth-child(3) .rank { color: #CD7F32; } /* Bronze */

        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .mode-select-box {
            background: #1a1a1a;
            border: 4px solid #4FC3F7;
            border-radius: 15px;
            padding: 24px;
            width: 80%;
            max-width: 460px;
            color: #fff;
            text-align: center;
            font-family: 'Press Start 2P', cursive;
            box-shadow: 0 0 20px rgba(79, 195, 247, 0.35);
        }

        .mode-select-box h2 {
            color: #4FC3F7;
            margin-bottom: 20px;
            font-size: 20px;
        }

        .mode-actions {
            display: flex;
            flex-direction: column;
            gap: 12px;
            align-items: center;
        }

        .mode-btn {
            width: 100%;
            max-width: 300px;
            padding: 12px 10px;
            border: 3px solid #4FC3F7;
            border-radius: 10px;
            background: linear-gradient(to bottom, #2196F3, #1976D2);
            color: #fff;
            cursor: pointer;
            font-family: 'Press Start 2P', cursive;
            font-size: 0.9rem;
            text-transform: uppercase;
            box-shadow: 0 5px 0 #0D47A1;
            transition: all 0.2s ease;
        }

        .mode-btn:hover {
            transform: translateY(2px);
            box-shadow: 0 3px 0 #0D47A1;
            background: linear-gradient(to bottom, #42A5F5, #2196F3);
        }

        .mode-btn:active {
            transform: translateY(5px);
            box-shadow: 0 0 0 #0D47A1;
        }

        .mode-btn.cancel {
            border-color: #90A4AE;
            background: linear-gradient(to bottom, #607D8B, #455A64);
            box-shadow: 0 5px 0 #263238;
        }

        .mode-btn.cancel:hover {
            box-shadow: 0 3px 0 #263238;
            background: linear-gradient(to bottom, #78909C, #607D8B);
        }

        .button-group.hidden {
            display: none;
        }

        /* Removed the blur effect for the title image */

        .leaderboard-item .player-name,
        .leaderboard-item .score {
            color: #fff;
            font-size: 14px;
            padding: 0 10px;
        }

        /* Special colors and effects for top 3 */
        .leaderboard-item:nth-child(1) .player-name,
        .leaderboard-item:nth-child(1) .score {
            color: #FFD700; /* Gold */
            text-shadow: 0 0 5px rgba(255, 215, 0, 0.5);
            animation: glow 1.5s ease-in-out infinite alternate;
        }

        .leaderboard-item:nth-child(2) .player-name,
        .leaderboard-item:nth-child(2) .score {
            color: #C0C0C0; /* Silver */
            text-shadow: 0 0 5px rgba(192, 192, 192, 0.5);
        }

        .leaderboard-item:nth-child(3) .player-name,
        .leaderboard-item:nth-child(3) .score {
            color: #CD7F32; /* Bronze */
            text-shadow: 0 0 5px rgba(205, 127, 50, 0.5);
        }

        /* Add glow animation for first place */
        @keyframes glow {
            from {
                text-shadow: 0 0 5px rgba(255, 215, 0, 0.5);
            }
            to {
                text-shadow: 0 0 10px rgba(255, 215, 0, 0.8),
                             0 0 20px rgba(255, 215, 0, 0.3);
            }
        }
    </style>
</head>
<body>
    <img src="../assets/lastkicks.png" alt="Just One Kick!" class="title-img">
    
    <!-- Add instruction container -->
    <div id="instructionContainer" style="display: none;">
        <div class="instruction-box">
            <h2>How to Play</h2>
            <div class="controls">
                <h3>Controls</h3>
                <p>🎮 Move Left: A or ← Left Arrow</p>
                <p>🎮 Move Right: D or → Right Arrow</p>
            </div>
            <div class="mechanics">
                <h3>Game Mechanics</h3>
                <p>• Keep the sipa in the air, catch it to hit it</p>
                <p>• Build combos for special effects</p>
                <p>• You have 3 lives</p>
                <p>• Missing the sipa costs 1 life</p>
                <p>• Score increases with each successful hit</p>
            </div>
            <button onclick="closeInstructions()" class="close-btn">Close</button>
        </div>
    </div>

    <div class="leaderboard-container" id="leaderboardContainer" style="display: none;">
        <h2>LEADERBOARD</h2>
        <div class="leaderboard-list" id="leaderboardList">
            <div class="no-scores" id="leaderboardMessage">Loading scores...</div>
        </div>
        <button onclick="toggleLeaderboard()" class="close-btn">Close</button>
    </div>

    <div class="modal-overlay" id="modeSelectModal">
        <div class="mode-select-box">
            <h2>Select Game Mode</h2>
            <div class="mode-actions">
                <button class="mode-btn" onclick="startSinglePlayer()">Single Player</button>
                <button class="mode-btn" onclick="startTwoPlayer()">2 Player</button>
                <button class="mode-btn cancel" onclick="closeModeSelect()">Cancel</button>
            </div>
        </div>
    </div>

    <div class="button-group">
        <button onclick="showModeSelect()">Start Game</button>
        <button onclick="toggleLeaderboard()">Leaderboard</button>
        <button onclick="showInstructions()">Instructions</button>
        <button onclick="window.close()">Quit</button>
    </div>

    <!-- Add these scripts -->
    <script>
        const buttonGroup = document.querySelector('.button-group');
        const leaderboardList = document.getElementById('leaderboardList');
        let leaderboardLoaded = false;

        function escapeHtml(value) {
            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        async function loadLeaderboard() {
            if (leaderboardLoaded) {
                return;
            }

            leaderboardList.innerHTML = '<div class="no-scores">Loading scores...</div>';

            try {
                const response = await fetch('../api/get_top_scores.php', { cache: 'no-store' });
                if (!response.ok) {
                    throw new Error('Failed to fetch leaderboard');
                }

                const scores = await response.json();
                if (!Array.isArray(scores) || scores.length === 0) {
                    leaderboardList.innerHTML = '<div class="no-scores">No scores yet!</div>';
                    leaderboardLoaded = true;
                    return;
                }

                leaderboardList.innerHTML = scores.map((row, index) => {
                    const rank = index + 1;
                    const name = escapeHtml(row.player_name || 'Unknown');
                    const score = Number(row.score || 0);
                    return `
                        <div class="leaderboard-item">
                            <span class="rank">#${rank}</span>
                            <span class="player-name">${name}</span>
                            <span class="score">${score}</span>
                        </div>
                    `;
                }).join('');

                leaderboardLoaded = true;
            } catch (error) {
                leaderboardList.innerHTML = '<div class="no-scores">Unable to load leaderboard.</div>';
            }
        }

        function showInstructions() {
            document.getElementById('instructionContainer').style.display = 'flex';
            buttonGroup.classList.add('hidden');
        }

        function closeInstructions() {
            document.getElementById('instructionContainer').style.display = 'none';
            buttonGroup.classList.remove('hidden');
        }

        function showModeSelect() {
            document.getElementById('instructionContainer').style.display = 'none';
            document.getElementById('leaderboardContainer').style.display = 'none';
            document.getElementById('modeSelectModal').style.display = 'flex';
            buttonGroup.classList.add('hidden');
        }

        function closeModeSelect() {
            document.getElementById('modeSelectModal').style.display = 'none';
            buttonGroup.classList.remove('hidden');
        }

        function startSinglePlayer() {
            location.href = 'sipa-gameplay.php';
        }

        function startTwoPlayer() {
            location.href = '2playergame.php';
        }

        async function toggleLeaderboard() {
            const leaderboard = document.getElementById('leaderboardContainer');
            const instructions = document.getElementById('instructionContainer');
            const modeSelect = document.getElementById('modeSelectModal');
            
            instructions.style.display = 'none';
            modeSelect.style.display = 'none';
            
            if (leaderboard.style.display === 'none') {
                await loadLeaderboard();
                leaderboard.style.display = 'block';
                buttonGroup.classList.add('hidden');
            } else {
                leaderboard.style.display = 'none';
                buttonGroup.classList.remove('hidden');
            }
        }
    </script>
</body>
</html>