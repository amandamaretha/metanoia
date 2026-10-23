
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode Verifikasi - Metanoia</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: white;
        }

        .container {
            display: flex;
            min-height: 100vh;
            width: 100%;
        }

        /* PANEL KIRI */
        .left-panel {
            position: relative;
            width: 50%;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: white;
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
            position: relative;
            width: 50%;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: #efeded;
        }

        .header {
            position: relative;
            min-height: 100px;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px 55px;
            border-bottom: 3px solid white;
        }

        .back {
            position: absolute;
            left: 25px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 36px;
            color: #222;
            text-decoration: none;
        }

        .header h1 {
            font-size: clamp(25px, 3vw, 38px);
            color: #598b8b;
            text-align: center;
        }

        .content {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 38px 5% 24px;
        }

        .description {
            color: #999;
            text-align: center;
            font-size: 18px;
            line-height: 1.4;
            margin-bottom: 50px;
        }

        /* INPUT OTP */
        .otp-container {
            display: flex;
            justify-content: center;
            gap: 12px;
            width: 100%;
        }

        .otp-input {
            width: 68px;
            height: 62px;
            min-width: 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: white;
            text-align: center;
            font-size: 30px;
            color: #598b8b;
            outline: none;
        }

        .otp-input:focus {
            border: 2px solid #598b8b;
        }

        .button-area {
            margin-top: auto;
            padding-top: 40px;
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
        @media (max-width: 900px) {
            .otp-container {
                gap: 8px;
            }

            .otp-input {
                width: 52px;
                height: 55px;
            }
        }

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
                padding: 30px 20px 22px;
            }

            .description {
                font-size: 15px;
                margin-bottom: 35px;
            }

            .otp-container {
                gap: 8px;
            }

            .otp-input {
                flex: 1;
                max-width: 55px;
                height: 48px;
                font-size: 23px;
            }

            .button-area {
                padding-top: 35px;
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

        <!-- PANEL KIRI -->
        <section class="left-panel">
            <div class="circle-top"></div>
            <div class="circle-bottom"></div>

            <img
                src="{{ asset('images/Mlogo.png') }}"
                alt="Logo Metanoia"
                class="logo"
            >
        </section>

        <!-- PANEL KANAN -->
        <section class="right-panel">

            <header class="header">
                <a href="{{ route('password.forgot') }}"
                   class="back"
                   aria-label="Kembali">
                    &#8592;
                </a>

                <h1>Kode Verifikasi</h1>
            </header>

            <form class="content"
                  action="{{ route('verification.check') }}"
                  method="POST">
                @csrf

                <p class="description">
                    Masukkan kode verifikasi yang telah dikirim ke nomor
                    <br>
                    {{ session('telepon_masked', '08xx xxxx xx45') }}
                </p>

                <div class="otp-container">
                    @for ($i = 0; $i < 6; $i++)
                        <input
                            type="text"
                            name="otp[]"
                            class="otp-input"
                            inputmode="numeric"
                            pattern="[0-9]"
                            maxlength="1"
                            aria-label="Digit {{ $i + 1 }}"
                            required
                        >
                    @endfor
                </div>

                @if ($errors->any())
                    <p style="color: #c0392b; margin-top: 15px;">
                        {{ $errors->first() }}
                    </p>
                @endif

                <div class="button-area">
                    <button type="submit" class="btn-send">
                        Kirim
                    </button>
                </div>
            </form>

        </section>
    </main>

    <script>
        const inputs = document.querySelectorAll('.otp-input');

        inputs.forEach((input, index) => {
            input.addEventListener('input', function () {
                this.value = this.value.replace(/\D/g, '').slice(0, 1);

                if (this.value && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
            });

            input.addEventListener('keydown', function (event) {
                if (
                    event.key === 'Backspace' &&
                    !this.value &&
                    index > 0
                ) {
                    inputs[index - 1].focus();
                }
            });

            input.addEventListener('paste', function (event) {
                event.preventDefault();

                const digits = event.clipboardData
                    .getData('text')
                    .replace(/\D/g, '')
                    .slice(0, 6);

                digits.split('').forEach((digit, i) => {
                    if (inputs[i]) inputs[i].value = digit;
                });

                inputs[Math.min(digits.length, 5)].focus();
            });
        });
    </script>
</body>
</html>
