<?php
session_start();

// 初始化排行榜
if (!isset($_SESSION['scores'])) {
    $_SESSION['scores'] = [];
}

// 接收分數
if (isset($_POST['score'])) {
    $score = (int)$_POST['score'];
    $_SESSION['scores'][] = $score;

    // 排序（高到低）
    rsort($_SESSION['scores']);

    // 只保留前10名
    $_SESSION['scores'] = array_slice($_SESSION['scores'], 0, 10);

    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>貪食蛇</title>

    <style>
        body {
            margin: 0;
            background: #111;
            color: white;
            font-family: Arial;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            gap: 30px;
        }

        .container {
            display: flex;
            gap: 20px;
        }

        canvas {
            background: #000;
            border: 2px solid #333;
        }

        .panel {
            width: 200px;
        }

        button {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            cursor: pointer;
        }
    </style>
</head>

<body>

<div class="container">

    <canvas id="game" width="400" height="400"></canvas>

    <div class="panel">
        <h3>排行榜</h3>
        <ol id="board">
            <?php foreach ($_SESSION['scores'] as $s): ?>
                <li><?= $s ?></li>
            <?php endforeach; ?>
        </ol>

        <button onclick="restart()">重新開始</button>
    </div>

</div>

<script>
    const canvas = document.getElementById("game");
    const ctx = canvas.getContext("2d");

    const grid = 20;

    let snake, dir, food, gameOver, score;

    function init() {
        snake = [{x: 5, y: 5}];
        dir = {x: 1, y: 0};
        food = spawnFood();
        gameOver = false;
        score = 0;
    }

    init();

    document.addEventListener("keydown", e => {
        if (e.key === "ArrowUp" && dir.y === 0) dir = {x: 0, y: -1};
        if (e.key === "ArrowDown" && dir.y === 0) dir = {x: 0, y: 1};
        if (e.key === "ArrowLeft" && dir.x === 0) dir = {x: -1, y: 0};
        if (e.key === "ArrowRight" && dir.x === 0) dir = {x: 1, y: 0};
    });

    function spawnFood() {
        return {
            x: Math.floor(Math.random() * (canvas.width / grid)),
            y: Math.floor(Math.random() * (canvas.height / grid))
        };
    }

    function loop() {
        if (gameOver) return;

        const head = {
            x: snake[0].x + dir.x,
            y: snake[0].y + dir.y
        };

        // 撞牆
        if (
            head.x < 0 || head.y < 0 ||
            head.x >= canvas.width / grid ||
            head.y >= canvas.height / grid
        ) {
            endGame();
            return;
        }

        // 撞自己
        for (let s of snake) {
            if (head.x === s.x && head.y === s.y) {
                endGame();
                return;
            }
        }

        snake.unshift(head);

        // 吃食物
        if (head.x === food.x && head.y === food.y) {
            food = spawnFood();
            score += 10;
        } else {
            snake.pop();
        }

        draw();
    }

    function draw() {
        ctx.fillStyle = "#000";
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        // food
        ctx.fillStyle = "red";
        ctx.fillRect(food.x * grid, food.y * grid, grid, grid);

        // snake
        ctx.fillStyle = "lime";
        snake.forEach(s => {
            ctx.fillRect(s.x * grid, s.y * grid, grid - 2, grid - 2);
        });

        // score
        ctx.fillStyle = "white";
        ctx.fillText("Score: " + score, 10, 20);

        if (gameOver) {
            ctx.fillStyle = "white";
            ctx.font = "20px Arial";
            ctx.fillText("GAME OVER", 130, 200);
        }
    }

    function endGame() {
        gameOver = true;

        // 丟分數到 PHP
        fetch("", {
            method: "POST",
            headers: {"Content-Type": "application/x-www-form-urlencoded"},
            body: "score=" + score
        }).then(() => {
            location.reload();
        });
    }

    function restart() {
        init();
    }

    setInterval(loop, 120);
</script>

</body>
</html>