<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>TBAQS | Thank You</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(to bottom, #FFD700, #000080);
            font-family: Arial, sans-serif;
            overflow: hidden;
            position: relative;
        }
        .thank-you-card {
            background-color: rgba(255, 255, 255, 0.4);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            text-align: center;
            z-index: 1;
            margin-bottom: 80px;
        }
        .thank-you-card h1 {
            color: #003366;
            font-weight: 700;
            margin-bottom: 20px;
        }
        .thank-you-card p {
            color: #555;
            font-size: 18px;
        }
        .btn-back {
            margin-top: 25px;
        }

        /* Pop-up Confetti Effect CSS */
        .confetti {
            position: absolute;
            width: 10px;
            height: 10px;
            border-radius: 2px;
            opacity: 0;
            z-index: 0;
            animation: confetti-explode 3s ease-out forwards;
            will-change: transform, opacity;
        }

        /* Keyframes for the explosion effect */
        @keyframes confetti-explode {
            0% { 
                transform: translateY(0) translateX(0) scale(0.5) rotateZ(0deg); 
                opacity: 0;
            }
            10% { 
                opacity: 1; 
            }
            100% { 
                /* Use CSS variables for dynamic end position and rotation */
                transform: translateY(var(--end-y)) translateX(var(--end-x)) scale(1.2) rotateZ(var(--random-rotate-end)); 
                opacity: 0;
            }
        }

        /* Footer Styles */
        .footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            background-color: #000080;
            color: white;
            text-align: center;
            padding: 15px 0;
            font-size: 14px;
            z-index: 10;
        }
    </style>
</head>
<body>
    <div class="thank-you-card">
        <h1>Your concern is sent successfully!</h1>
        <p>Thank you for reaching out to us. Our support team will get back to you soon.</p>
        <a href="Dashboard.php" class="btn btn-primary btn-back">Back to Dashboard</a>
    </div>

    <footer class="footer">
        © 2025 TBA Themis Bank Association. All Rights Reserved.
    </footer>

    <script>
        function createConfettiBurst(originX) {
            const colors = ['#000080', '#0000FF', '#FFFFFF'];
            const shapes = ['square', 'circle', 'triangle'];
            const numConfetti = 80;

            const originY = 95;

            for (let i = 0; i < numConfetti; i++) {
                const confetti = document.createElement('div');
                confetti.classList.add('confetti');
                
                const randomColor = colors[Math.floor(Math.random() * colors.length)];
                const randomShape = shapes[Math.floor(Math.random() * shapes.length)];
                
                confetti.style.left = originX + 'vw';
                confetti.style.top = originY + 'vh';
                confetti.style.backgroundColor = randomColor;

                const size = Math.random() * 8 + 4;
                confetti.style.width = size + 'px';
                confetti.style.height = size + 'px';

                if (randomShape === 'circle') {
                    confetti.style.borderRadius = '50%';
                } else if (randomShape === 'triangle') {
                    confetti.style.width = '0';
                    confetti.style.height = '0';
                    confetti.style.borderLeft = (size / 2) + 'px solid transparent';
                    confetti.style.borderRight = (size / 2) + 'px solid transparent';
                    confetti.style.borderBottom = size + 'px solid ' + randomColor;
                    confetti.style.backgroundColor = 'transparent';
                }

                const endYOffset = -(Math.random() * 80 + 20);
                let endXOffset;

                // Adjust horizontal spread to direct towards the middle
                if (originX === 10) { // If it's the left origin
                    endXOffset = Math.random() * 50;
                } else { // If it's the right origin
                    endXOffset = -(Math.random() * 50);
                }
                const randomRotateEnd = Math.random() * 720 - 360;

                confetti.style.setProperty('--end-x', endXOffset + 'vw');
                confetti.style.setProperty('--end-y', endYOffset + 'vh');
                confetti.style.setProperty('--random-rotate-end', randomRotateEnd + 'deg');
                
                confetti.style.animationDuration = (Math.random() * 1.5 + 2.5) + 's';

                document.body.appendChild(confetti);
                
                confetti.addEventListener('animationend', () => {
                    confetti.remove();

                });
            }
        }

        const interval = setInterval(() => {
            createConfettiBurst(10); // Burst from the left
            createConfettiBurst(90); // Burst from the right
        }, 1500);
        
        setTimeout(() => {
            clearInterval(interval);
        }, 15000); 

        setTimeout(() => {
        window.location.href = "Dashboard.php";
        }, 7000); // Redirect after 2 seconds
    </script>
</body>
</html>