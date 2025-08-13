<x-guest-layout>
    <head>
        <style>
            html, body {
                margin: 0;
                padding: 0;
                width: 100%;
                height: 100%;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background-color: #f5f7fa;
            }

            /* Elimina restricciones del layout base */
            body > div {
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .login-container {
                display: flex;
                height: 100vh;
                width: 100%;
            }

            .login-form-panel {
                width: 60%;
                background: #ffffff;
                padding: 4rem;
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: 2px 0 10px rgba(0,0,0,0.05);
            }

            .form-wrapper {
                width: 100%;
                padding: 0 3rem;
            }

            .logo img {
                height: 80px;
                margin: 0 auto 2rem;
                display: block;
            }

            .status-message {
                color: #b91c1c;
                background: #fee2e2;
                padding: 0.75rem;
                margin-bottom: 1rem;
                border-radius: 0.5rem;
                text-align: center;
                font-weight: 600;
            }

            .form-group {
                margin-bottom: 1.5rem;
            }

            .form-group label {
                display: block;
                font-weight: 600;
                margin-bottom: 0.5rem;
                color: #374151;
            }

            .input-control {
                width: 100%;
                padding: 0.75rem;
                border: 1px solid #d1d5db;
                border-radius: 0.375rem;
                background: #f9fafb;
            }

            .form-options {
                display: flex;
                justify-content: space-between;
                align-items: center;
                font-size: 0.9rem;
                margin-bottom: 2rem;
                color: #4b5563;
            }

            .form-options a {
                color: #047857;
                text-decoration: none;
            }

            .submit-btn {
                width: 100%;
                background-color: #047857;
                color: #ffffff;
                padding: 0.75rem;
                border-radius: 0.375rem;
                text-align: center;
                font-weight: bold;
                cursor: pointer;
                transition: background 0.3s ease;
                border: none;
            }

            .submit-btn:hover {
                background-color: #065f46;
            }

            .info-panel {
                width: 40%;
                background: linear-gradient(135deg, #065f46, #047857);
                color: #ffffff;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 3rem;
                border-top-left-radius: 4rem;
                border-bottom-left-radius: 4rem;
            }

            .info-content {
                max-width: 500px;
                text-align: center;
            }

            .info-content h2 {
                font-size: 2.5rem;
                font-weight: 700;
                margin-bottom: 1rem;
            }

            .info-content p {
                font-size: 1.125rem;
                margin-bottom: 1.5rem;
            }

            .info-content ul {
                text-align: left;
                font-size: 0.95rem;
                list-style: none;
                padding: 0;
            }

            .info-content ul li {
                margin-bottom: 0.5rem;
                padding-left: 1rem;
                position: relative;
            }

            .info-content ul li::before {
                content: "✔";
                color: #d1fae5;
                position: absolute;
                left: 0;
            }

            @media (max-width: 768px) {
                .login-container {
                    flex-direction: column;
                }

                .login-form-panel,
                .info-panel {
                    width: 100%;
                    border-radius: 0;
                }

                .info-panel {
                    border-top-left-radius: 2rem;
                    border-top-right-radius: 2rem;
                    border-bottom-left-radius: 0;
                }
            }
        </style>
    </head>

    <div class="login-container">
        <!-- Left Panel -->
        <div class="login-form-panel">
            <div class="form-wrapper">
                <div class="logo">
                    <img src="{{ asset('images/espe logo.png') }}" alt="Logo ESPE">
                </div>

                @if(session('status'))
                    <div class="status-message">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="form-group">
                        <label for="email">Correo institucional</label>
                        <x-text-input id="email" class="input-control" type="email" name="email" :value="old('email')" required autofocus />
                        <x-input-error :messages="$errors->get('email')" />
                    </div>

                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <x-text-input id="password" class="input-control" type="password" name="password" required />
                        <x-input-error :messages="$errors->get('password')" />
                    </div>

                    <div class="form-options">
                        <label>
                            <input type="checkbox" name="remember">
                            Recordarme
                        </label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a>
                        @endif
                    </div>

                    <button type="submit" class="submit-btn">Iniciar Sesión</button>
                </form>
            </div>
        </div>

        <!-- Right Panel -->
        <div class="info-panel">
            <div class="info-content">
                <h2>PREMAEST</h2>
                <p>Sistema integral de prematrículas para estudiantes de la ESPE. Accede a tu perfil y planifica tu semestre académico.</p>
                <ul>
                    <li>Registro de Asignaturas Aprobadas</li>
                    <li>Previsualización de Cursos Disponibles</li>
                    <li>Planificación de Matrículas</li>
                </ul>
            </div>
        </div>
    </div>
</x-guest-layout>
