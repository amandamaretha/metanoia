
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login {{ $role }} - Metanoia</title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #fff;
            color: #222;
        }

        .container {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        /* BAGIAN KIRI */
        .left-panel {
            position: relative;
            width: 50%;
            min-height: 100vh;
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #fff;
        }

        .circle-left-top {
            position: absolute;
            width: 500px;
            height: 500px;
            border-radius: 50%;
            background: #83a5a5;
            top: -75px;
            left: -90px;
        }

        .circle-left-bottom {
            position: absolute;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: #598b8b;
            left: -75px;
            top: 50%;
            transform: translateY(-50%);
        }

        .logo {
            position: relative;
            z-index: 1;
            width: min(80%, 330px);
            height: auto;
            object-fit: contain;
        }

        /* BAGIAN KANAN */
        .right-panel {
            position: relative;
            width: 50%;
            min-height: 100vh;
            padding: 60px 35px;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            background: #fff;
        }

        .back {
            position: absolute;
            top: 20px;
            left: 22px;
            z-index: 2;
            font-size: 30px;
            color: #222;
            text-decoration: none;
        }

        .back:hover {
            color: #598b8b;
        }

        .circle-right {
            position: absolute;
            width: 230px;
            height: 230px;
            border-radius: 50%;
            background: #9bb5b4;
            right: -85px;
            bottom: -110px;
        }

        /* KARTU LOGIN */
        .login-card {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 520px;
            min-height: 480px;
            padding: 48px 42px;
            background: #efeded;
            border-radius: 22px;
            box-shadow: 0 2px 3px rgba(0, 0, 0, 0.2);
        }

        .login-card h1 {
            color: #598b8b;
            font-size: 34px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .subtitle {
            color: #aaa;
            font-size: 14px;
            margin-bottom: 28px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            margin-bottom: 9px;
        }

        .form-group input {
            display: block;
            width: 100%;
            height: 48px;
            border: none;
            border-radius: 7px;
            outline: none;
            background: #fff;
            padding: 0 16px;
            font-size: 15px;
        }

        .form-group input:focus {
            box-shadow: 0 0 0 2px #83a5a5;
        }

        .form-group input::placeholder {
            color: #d2d2d2;
        }

        .forgot {
            display: block;
            text-align: right;
            margin-top: -5px;
            margin-bottom: 32px;
            color: #598b8b;
            text-decoration: none;
            font-size: 14px;
        }

        .btn-login {
            display: block;
            width: 100%;
            height: 43px;
            border: none;
            border-radius: 7px;
            background: #598b8b;
            color: #fff;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
        }


        /* RESPONSIVE HP */
        @media (max-width: 700px) {
            .container {
                flex-direction: column;
            }

            .left-panel {
                width: 100%;
                min-height: 30vh;
            }

            .circle-left-top {
                width: 270px;
                height: 270px;
                top: -155px;
                left: -70px;
            }

            .circle-left-bottom {
                width: 125px;
                height: 125px;
                top: 75%;
                left: -45px;
            }

            .logo {
                width: 210px;
                max-width: 65%;
            }

            .right-panel {
                width: 100%;
                min-height: 70vh;
                padding: 55px 22px 35px;
            }

            .login-card {
                width: 100%;
                max-width: 480px;
                min-height: 400px;
                padding: 35px 28px;
            }
        }

    </style>
</head>

<body>
    <main class="container">

        <section class="left-panel">
            <div class="circle-left-top"></div>
            <div class="circle-left-bottom"></div>

            <img
                src="{{ asset('images/Mlogo.png') }}"
                alt="Logo Metanoia"
                class="logo"
            >
        </section>

        <section class="right-panel">
            <a href="{{ url('/') }}" class="back" aria-label="Kembali">
                &#8592;
            </a>

            <div class="circle-right"></div>

            <div class="login-card">
                <h1>Selamat Datang</h1>
                <p class="subtitle">
                    Silahkan masuk ke akun anda!
                </p>

                <form
                    action="{{ $role === 'Pengelola'
                        ? url('/login/pengelola')
                        : url('/login/bendahara') }}"
                    method="POST"
                >
                    @csrf

                    <div class="form-group">
                        <label for="telepon">Login</label>
                        <input
                            type="tel"
                            id="telepon"
                            name="telepon"
                            placeholder="Masukkan no Telephone"
                            autocomplete="username"
                            value="{{ old('telepon') }}"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Masukkan password"
                            autocomplete="current-password"
                            required
                        >
                    </div>

                    <a href="{{ route('password.forgot') }}" class="forgot">
                        Lupa password?
                    </a>

                    <button type="submit" class="btn-login">
                        Masuk
                    </button>
                </form>
            </div>
        </section>

    </main>
</body>
</html>
