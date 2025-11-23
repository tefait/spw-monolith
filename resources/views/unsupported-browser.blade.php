<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Browser Tidak Didukung</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root {
            --bg-color: #181818;
            --text-color: #ffffff;
            --accent-color: #a40c0b;
            --button-bg: rgba(0, 152, 0, 0.1);
            --button-text: rgba(0, 152, 0, 1);
        }

        body {
            margin: 0;
            padding: 2rem;
            background-color: var(--bg-color);
            color: var(--text-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            text-align: center;
        }

        h1 {
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        p {
            font-size: 1rem;
            margin-bottom: 1.5rem;
        }

        .browser-list {
            list-style: none;
            padding: 0;
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-bottom: 2rem;
        }

        .browser-list li {
            background-color: #282828;
            padding: 1rem;
            border-radius: 8px;
            width: 80px;
            display: flex;
            flex-direction: column;
            align-items: center;
            font-size: 0.9rem;
        }

        .browser-list img {
            width: 40px;
            height: 40px;
            margin-bottom: 0.5rem;
        }

        .actions {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            justify-content: center;
            width: 100%;
            max-width: 400px;
        }

        .actions button,
        .actions a {
            background: var(--button-bg);
            color: var(--button-text);
            border: none;
            padding: 1rem;
            border-radius: 10px;
            font-size: 1rem;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            transition: background-color 0.3s;
        }

        .actions button:hover,
        .actions a:hover {
            background-color: #e0ac1c;
            color: #ffffff;
        }

        @media (max-width: 600px) {
            .browser-list {

                align-items: center;
            }

            .browser-list li {
                padding: 8vw;
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <h1>Browser Tidak Didukung</h1>
    <p>Untuk pengalaman terbaik, silakan gunakan salah satu browser berikut:</p>
    <ul class="browser-list">
        <li onclick="openIn('chrome')">
            <img src="https://www.google.com/chrome/static/images/favicons/favicon-96x96.png" alt="Chrome">
            Chrome
        </li>
        <li onclick="openIn('firefox')">
            <img src="https://upload.wikimedia.org/wikipedia/commons/a/a0/Firefox_logo%2C_2019.svg" alt="Firefox">
            Firefox
        </li>
        <li onclick="openIn('edge')">
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/9/98/Microsoft_Edge_logo_%282019%29.svg/96px-Microsoft_Edge_logo_%282019%29.svg.png" alt="Edge">
            Edge
        </li>
    </ul>

    <div class="actions">
        <button onclick="openIn('chrome')">Buka di Chrome</button>
        <button onclick="openIn('firefox')">Buka di Firefox</button>
        <button onclick="openIn('edge')">Buka di Edge</button>
    </div>

    <script>
        function openIn(browser) {
            const currentUrl = window.location.host.replace(/^https?:\/\//, '');
            let intentUrl = '';

            switch (browser) {
                case 'chrome':
                    intentUrl = `intent://${currentUrl}#Intent;scheme=https;package=com.android.chrome;end;`;
                    break;
                case 'firefox':
                    intentUrl = `intent://${currentUrl}#Intent;scheme=https;package=org.mozilla.firefox;end;`;
                    break;
                case 'edge':
                    intentUrl = `intent://${currentUrl}#Intent;scheme=https;package=com.microsoft.emmx;end;`;
                    break;
                default:
                    intentUrl = `intent://${currentUrl}#Intent;scheme=https;end;`;
                    break;
            }
            window.location.href = intentUrl;
        }
    </script>
</body>

</html>