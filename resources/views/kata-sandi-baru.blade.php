
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kata Sandi Baru - Metanoia</title>

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

        /* PANEL KIRI */
        .left-panel {
            position: relative;
            width: 50%;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #fff;
            overflow: hidden;
        }

        .circle-top {
            position: absolute;
            width: 500px;
            height: 500px;
            border-radius: 50%;
            background: #9bb5b4;
            top: -75px;
            left: -140px;
        }

        .circle-bottom {
            position: absolute;
            width: 270px;
            height: 270px;
            border-radius: 50%;
            background: #598b8b;
            top: 43%;
            left: -70px;
            transform: translateY(-50%);
        }

        .logo {
            position: relative;
            z-index: 1;
            width: min(80%, 400px);
            height: auto;
            object-fit: contain;
        }

        /* PANEL KANAN */
        .right-panel {
            width: 50%;
            min-height: 100vh;
            background: #efeded;
            display: flex;
            flex-direction: column;
        }

        .header {
            position: relative;
            min-height: 100px;
            padding: 20px 65px;
            display: flex;
            justify-content: center;
            align-items: center;
            border-bottom: 3px solid #fff;
        }

        .header h1 {
            color: #598b8b;
            font-size: clamp(26px, 3vw, 38px);
            text-align: center;
        }

        .back {
            position: absolute;
            left: 25px;
            top: 50%;
            transform: translateY(-50%);
            text-decoration: none;
            color: #222;
            font-size: 36px;
        }

        .content {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 36px 5% 25px;
        }

        .description {
            text-align: center;
            color: #999;
            font-size: 18px;
            margin: 0 0 60px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .password-wrapper {
            position: relative;
            width: 100%;
        }

        .password-wrapper input {
            width: 100%;
            height: 55px;
            padding: 0 55px 0 25px;
            border: 1px solid #ddd;
            border-radius: 8px;
            outline: none;
            background: #fff;
            font-size: 17px;
        }

        .password-wrapper input::placeholder {
            color: #d3d3d3;
        }

        .password-wrapper input:focus {
            border-color: #598b8b;
            box-shadow: 0 0 0 2px rgba(89, 139, 139, 0.15);
        }

        /* TOMBOL MATA */
        .toggle-password {
            position: absolute;
            top: 50%;
            right: 17px;
            transform: translateY(-50%);
            display: flex;
            justify-content: center;
            align-items: center;
            width: 30px;
            height: 35px;
            border: none;
            background: transparent;
            color: #cfcfcf;
            cursor: pointer;
        }

        .toggle-password:hover {
            color: #598b8b;
        }

        .toggle-password svg {
            width: 27px;
            height: 27px;
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .error {
            display: block;
            color: #c0392b;
            font-size: 13px;
            margin-top: 7px;
        }

        .success-message {
            color: #477777;
            font-size: 14px;
            margin-bottom: 15px;
        }

        .button-area {
            margin-top: auto;
            padding-top: 35px;
        }

        .btn-send {
            width: 100%;
            height: 68px;
            border: none;
            border-radius: 8px;
            background: #598b8b;
            color: white;
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
            box-shadow: inset 0 5px 5px rgba(0, 0, 0, 0.12);
        }

        .btn-send:hover {
            background: #477777;
        }

        /* RESPONSIVE */
        @media (max-width: 700px) {
            .container {
                flex-direction: column;
            }

            .left-panel {
                width: 100%;
                min-height: 30vh;
            }

            .circle-top {
                width: 270px;
                height: 270px;
                top: -150px;
                left: -70px;
            }

            .circle-bottom {
                width: 130px;
                height: 130px;
                left: -40px;
                top: 75%;
            }

            .logo {
                width: 230px;
                max-width: 65%;
            }

            .right-panel {
                width: 100%;
                min-height: 70vh;
                flex: 1;
            }

            .header {
                min-height: 75px;
                padding: 15px 50px;
            }

            .header h1 {
                font-size: 25px;
            }

            .back {
                left: 18px;
                font-size: 30px;
            }

            .content {
                padding: 30px 22px 22px;
            }

            .description {
                font-size: 15px;
                margin-bottom: 35px;
            }

            .password-wrapper input {
                height: 48px;
                padding-left: 16px;
                font-size: 15px;
            }

            .button-area {
                padding-top: 30px;
            }

            .btn-send {
                height: 52px;
                font-size: 19px;
            }
        }
    </style>
</head>

<body>
<main class="container">

    <section class="left-panel">
        <div class="circle-top"></div>
        <div class="circle-bottom"></div>

        <img
            src="{{ asset('images/Mlogo.png') }}"
            alt="Logo Metanoia"
            class="logo"
        >
    </section>

    <section class="right-panel">

        <header class="header">
            <a href="{{ route('verification.page') }}"
               class="back"
               aria-label="Kembali">&#8592;</a>

            <h1>Kata Sandi Baru</h1>
        </header>

        <form action="{{ route('password.update') }}"
              method="POST"
              class="content">
            @csrf

            <p class="description">
                Silahkan masukkan kata sandi baru anda
            </p>

            @if (session('success'))
                <p class="success-message">
                    {{ session('success') }}
                </p>
            @endif

            <div class="form-group">
                <div class="password-wrapper">
                    <input
                        type="password"
                        name="password"
                        id="password"
                        placeholder="Masukkan kata sandi baru"
                        autocomplete="new-password"
                        required
                    >

                    <button
                        type="button"
                        class="toggle-password"
                        onclick="togglePassword('password', this)"
                        aria-label="Tampilkan kata sandi"
                        aria-pressed="false"
                    >
                        <svg viewBox="0 0 24 24">
                            <path d="M3 3l18 18"></path>
                            <path d="M10.6 10.6a2 2 0 002.8 2.8"></path>
                            <path d="M9.9 5.2A10.8 10.8 0 0112 5c5 0 8.5 4.5 9.5 7-.4 1-1.2 2.2-2.3 3.3"></path>
                            <path d="M6.2 6.2C3.9 7.7 2.7 9.6 2.5 12c.8 2.5 4.4 7 9.5 7 1.5 0 2.8-.4 4-1.1"></path>
                        </svg>
                    </button>
                </div>

                @error('password')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <div class="password-wrapper">
                    <input
                        type="password"
                        name="password_confirmation"
                        id="password_confirmation"
                        placeholder="Konfirmasi kata sandi"
                        autocomplete="new-password"
                        required
                    >

                    <button
                        type="button"
                        class="toggle-password"
                        onclick="togglePassword('password_confirmation', this)"
                        aria-label="Tampilkan konfirmasi kata sandi"
                        aria-pressed="false"
                    >
                        <svg viewBox="0 0 24 24">
                            <path d="M3 3l18 18"></path>
                            <path d="M10.6 10.6a2 2 0 002.8 2.8"></path>
                            <path d="M9.9 5.2A10.8 10.8 0 0112 5c5 0 8.5 4.5 9.5 7-.4 1-1.2 2.2-2.3 3.3"></path>
                            <path d="M6.2 6.2C3.9 7.7 2.7 9.6 2.5 12c.8 2.5 4.4 7 9.5 7 1.5 0 2.8-.4 4-1.1"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="button-area">
                <button type="submit" class="btn-send">
                    Kirim
                </button>
            </div>
        </form>

    </section>
</main>

<script>
    function togglePassword(inputId, button) {
        const input = document.getElementById(inputId);
        const isHidden = input.type === 'password';

        input.type = isHidden ? 'text' : 'password';
        button.setAttribute('aria-pressed', String(isHidden));
        button.setAttribute(
            'aria-label',
            isHidden ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'
        );

        // Ganti ikon mata dicoret menjadi mata terbuka, dan sebaliknya.
        button.innerHTML = isHidden
            ? `<svg viewBox="0 0 24 24">
                   <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"></path>
                   <circle cx="12" cy="12" r="3"></circle>
               </svg>`
            : `<svg viewBox="0 0 24 24">
                   <path d="M3 3l18 18"></path>
                   <path d="M10.6 10.6a2 2 0 002.8 2.8"></path>
                   <path d="M9.9 5.2A10.8 10.8 0 0112 5c5 0 8.5 4.5 9.5 7-.4 1-1.2 2.2-2.3 3.3"></path>
                   <path d="M6.2 6.2C3.9 7.7 2.7 9.6 2.5 12c.8 2.5 4.4 7 9.5 7 1.5 0 2.8-.4 4-1.1"></path>
               </svg>`;
    }
</script>

</body>
</html>
