
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Metanoia</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #fff;
            font-family: Arial, Helvetica, sans-serif;
            color: #111;
        }

        .login-container {
            position: relative;
            width: 100%;
            min-height: 100vh;
            background: #fff;
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
            border: none;
        }

        /* Ornamen lingkaran */
        .circle {
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
        }

        .circle-top {
            width: 75vw;
            height: 70vh;
            background: #598b8b;
            top: -25vh;
            left: -5vw;
        }

        .circle-left {
            width: 30vw;
            height: 30vw;
            background: #9bb5b4;
            top: 25vh;
            left: -12vw;
        }

        .circle-right {
            width: 25vw;
            height: 25vw;
            background: #9bb5b4;
            bottom: -12vw;
            right: -8vw;
        }

        .login-content {
            position: relative;
            z-index: 1;
            width: 90%;
            max-width: 410px;
            margin: 0 auto;
            text-align: center;
        }

        .logo {
            width: 200px;
            height: 230px;
            object-fit: contain;
            margin-bottom: 0;
        }

        .welcome-text {
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 12px;
            line-height: 1.5;
        }

        .button-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            min-height: 37px;
            border-radius: 15px;
            text-decoration: none;
            color: #fff;
            font-size: 15px;
            font-weight: 600;
            transition: 0.2s ease;
        }

        .btn-yellow {
            background: #f7c323;
        }

        .btn-green {
            background: #598b8b;
        }

        .btn:hover {
            transform: translateY(-2px);
            filter: brightness(0.95);
        }

        .register-text {
            font-size: 15px;
            font-weight: 600;
            margin: 10px 0 7px;
        }

        @media (max-width: 480px) {
            .login-container {
                width: 100%;
                min-height: 100vh;
                border: none;
            }

            .circle-top {
                width: 120%;
                height: 330px;
                left: -10%;
                top: -100px;
            }

            .circle-left {
                width: 200px;
                height: 200px;
                left: -100px;
                top: 80px;
            }

            .circle-right {
                width: 180px;
                height: 180px;
            }

            .login-content {
                width: calc(100% - 40px);
                max-width: 410px;
                margin-top: 35px;
            }

            .logo {
                width: 165px;
                height: 220px;
            }

            .welcome-text {
                font-size: 14px;
            }

            .btn {
                font-size: 14px;
            }
        }
    </style>
</head>

<body>
    <main class="login-container">

        <div class="circle circle-top"></div>
        <div class="circle circle-left"></div>
        <div class="circle circle-right"></div>

        <section class="login-content">

            <img
                src="{{ asset('images/metanoia.webp') }}"
                alt="Logo Metanoia"
                class="logo"
            >

            <p class="welcome-text">
                Selamat datang di metanoia ingin masuk sebagai?
            </p>

            <div class="button-group">
                <a href="{{ url('/login/pengelola') }}"
                   class="btn btn-yellow">
                    Masuk Sebagai Pengelola
                </a>

                <a href="{{ url('/login/bendahara') }}"
                   class="btn btn-yellow">
                    Masuk sebagai Bendahara
                </a>

                <a href="{{ url('/login/nasabah') }}"
                   class="btn btn-green">
                    Masuk sebagai Nasabah
                </a>
            </div>

            <p class="register-text">
                Atau belum memiliki akun?
            </p>

            <a href="{{ url('/register/nasabah') }}"
               class="btn btn-green">
                Daftar Sebagai Nasabah
            </a>

        </section>
    </main>
</body>
</html>
