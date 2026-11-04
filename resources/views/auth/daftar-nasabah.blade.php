<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Sebagai Nasabah - Metanoia</title>

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
            padding: 40px 35px;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow-y: auto;
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

        /* KARTU DAFTAR */
        .login-card {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 520px;
            padding: 36px 36px;
            background: #efeded;
            border-radius: 22px;
            box-shadow: 0 2px 3px rgba(0, 0, 0, 0.2);
            margin: 20px 0;
        }

        .login-card h1 {
            color: #598b8b;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .subtitle {
            color: #777;
            font-size: 13px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 14px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 6px;
            color: #333;
        }

        .form-group input,
        .form-group textarea {
            display: block;
            width: 100%;
            border: 1px solid #d2d2d2;
            border-radius: 7px;
            outline: none;
            background: #fff;
            padding: 10px 14px;
            font-size: 14px;
            font-family: inherit;
        }

        .form-group input {
            height: 44px;
        }

        .form-group textarea {
            height: 60px;
            resize: none;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            box-shadow: 0 0 0 2px #83a5a5;
            border-color: #598b8b;
        }

        .form-group input::placeholder,
        .form-group textarea::placeholder {
            color: #b0b0b0;
        }

        .error-text {
            color: #c0392b;
            font-size: 12px;
            margin-top: 4px;
        }

        .btn-login {
            display: block;
            width: 100%;
            height: 44px;
            border: none;
            border-radius: 7px;
            background: #598b8b;
            color: #fff;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 20px;
            transition: background 0.2s;
        }

        .btn-login:hover {
            background: #467070;
        }

        .login-link {
            display: block;
            text-align: center;
            margin-top: 14px;
            color: #598b8b;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
        }

        .login-link:hover {
            text-decoration: underline;
        }

        @media (max-width: 700px) {
            .container {
                flex-direction: column;
            }

            .left-panel {
                width: 100%;
                min-height: 25vh;
            }

            .circle-left-top {
                width: 250px;
                height: 250px;
                top: -140px;
                left: -60px;
            }

            .circle-left-bottom {
                width: 110px;
                height: 110px;
                top: 75%;
                left: -35px;
            }

            .logo {
                width: 180px;
            }

            .right-panel {
                width: 100%;
                min-height: 75vh;
                padding: 30px 20px;
            }

            .login-card {
                padding: 28px 22px;
            }
        }
    </style>
</head>
<body>
    <main class="container">
        <!-- BAGIAN KIRI DENGAN LOGO -->
        <section class="left-panel">
            <div class="circle-left-top"></div>
            <div class="circle-left-bottom"></div>

            <img
                src="{{ asset('images/Mlogo.png') }}"
                alt="Logo Metanoia"
                class="logo"
            >
        </section>

        <!-- BAGIAN KANAN DENGAN FORMULIR PENDAFTARAN -->
        <section class="right-panel">
            <a href="{{ url('/') }}" class="back" aria-label="Kembali">&#8592;</a>

            <div class="circle-right"></div>

            <div class="login-card">
                <h1>Daftar Nasabah</h1>
                <p class="subtitle">Lengkapi formulir di bawah untuk membuka rekening Bank Sampah</p>

                @if ($errors->any())
                    <div style="background: #fadbd8; border: 1px solid #f1948a; color: #78281f; padding: 10px 14px; border-radius: 7px; margin-bottom: 14px; font-size: 13px;">
                        Mohon periksa kembali isian formulir Anda.
                    </div>
                @endif

                <form action="{{ route('nasabah.register.submit') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label for="nama">Nama Lengkap</label>
                        <input
                            type="text"
                            id="nama"
                            name="nama"
                            value="{{ old('nama') }}"
                            placeholder="Masukkan nama lengkap Anda"
                            required
                        >
                        @error('nama') <p class="error-text">{{ $message }}</p> @enderror
                    </div>

                    <div class="form-group">
                        <label for="nik">NIK (16 Digit)</label>
                        <input
                            type="text"
                            id="nik"
                            name="nik"
                            value="{{ old('nik') }}"
                            placeholder="Contoh: 3201012345670001"
                            maxlength="16"
                            pattern="[0-9]{16}"
                            required
                        >
                        @error('nik') <p class="error-text">{{ $message }}</p> @enderror
                    </div>

                    <div class="form-group">
                        <label for="alamat">Alamat Lengkap</label>
                        <textarea
                            id="alamat"
                            name="alamat"
                            placeholder="Masukkan alamat domisili lengkap"
                            required
                        >{{ old('alamat') }}</textarea>
                        @error('alamat') <p class="error-text">{{ $message }}</p> @enderror
                    </div>

                    <div class="form-group">
                        <label for="telepon">Nomor Telepon</label>
                        <input
                            type="tel"
                            id="telepon"
                            name="telepon"
                            value="{{ old('telepon') }}"
                            placeholder="Contoh: 081234567890"
                            required
                        >
                        @error('telepon') <p class="error-text">{{ $message }}</p> @enderror
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Minimal 8 karakter"
                            required
                        >
                        @error('password') <p class="error-text">{{ $message }}</p> @enderror
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Konfirmasi Password</label>
                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            placeholder="Ulangi password di atas"
                            required
                        >
                    </div>

                    <button type="submit" class="btn-login">
                        Daftar Sebagai Nasabah
                    </button>

                    <a href="{{ route('login.show', config('metanoia.portals.nasabah', 'nasabah')) }}" class="login-link">
                        Sudah punya akun? Masuk disini
                    </a>
                </form>
            </div>
        </section>
    </main>
</body>
</html>
