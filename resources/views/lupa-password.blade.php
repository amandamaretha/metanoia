
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Kata Sandi - Metanoia</title>

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
            position: relative;
            width: 50%;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: #efeded;
        }

        .header {
            position: relative;
            width: 100%;
            min-height: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px 60px;
            border-bottom: 3px solid #fff;
        }

        .back {
            position: absolute;
            left: 25px;
            top: 50%;
            transform: translateY(-50%);
            text-decoration: none;
            color: #222;
            font-size: 36px;
            line-height: 1;
        }

        .back:hover {
            color: #598b8b;
        }

        .header h1 {
            color: #598b8b;
            font-size: clamp(26px, 3vw, 38px);
            text-align: center;
        }

        /* FORM */
        .form-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 38px 7% 25px;
        }

        .description {
            color: #999;
            font-size: 18px;
            line-height: 1.4;
            text-align: center;
            margin-bottom: 40px;
        }

        .phone-input {
            display: block;
            width: 100%;
            height: 55px;
            padding: 0 25px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: #fff;
            outline: none;
            font-size: 17px;
        }

        .phone-input::placeholder {
            color: #d3d3d3;
        }

        .phone-input:focus {
            border-color: #598b8b;
            box-shadow: 0 0 0 2px rgba(89, 139, 139, 0.15);
        }

        .button-area {
            margin-top: auto;
            padding-top: 35px;
        }

        .btn-send {
            display: block;
            width: 100%;
            height: 68px;
            border: none;
            border-radius: 8px;
            background: #598b8b;
            color: #fff;
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
            box-shadow: inset 0 5px 5px rgba(0, 0, 0, 0.12);
        }

        .btn-send:hover {
            background: #477777;
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

            .form-content {
                padding: 30px 22px 22px;
            }

            .description {
                font-size: 15px;
                margin-bottom: 30px;
            }

            .phone-input {
                height: 48px;
                padding: 0 16px;
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
                <a href="{{ route('password.forgot') }}"
                   class="back"
                   aria-label="Kembali ke halaman sebelumnya">
                    &#8592;
                </a>

                <h1>Lupa Kata Sandi</h1>
            </header>

            <form
                class="form-content"
                action="{{ route('password.send') }}"
                method="POST"
            >
                @csrf

                <p class="description">
                    Kami akan mengirimkan 6 digit kode ke nomor telepon
                    di bawah ini
                </p>

                <input
                    type="tel"
                    name="telepon"
                    class="phone-input"
                    placeholder="Masukkan no Telephone"
                    autocomplete="tel"
                    required
                >

                @error('telepon')
                    <p style="color: #c0392b; margin-top: 8px;">
                        {{ $message }}
                    </p>
                @enderror

                <div class="button-area">
                    <button type="submit" class="btn-send">
                        Kirim
                    </button>
                </div>
            </form>

        </section>
    </main>
</body>
</html>
