<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-authenticated" content="{{ Auth::check() ? 'true' : 'false' }}">
    @auth
    <meta name="user-name" content="{{ Auth::user()->name }}">
    @endauth
    <title>sheiqaway - Meu Perfil</title>
    @vite(['resources/css/style.css', 'resources/js/global.js'])
    <style>
        .profile-grid {
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 30px;
            margin-top: 20px;
        }
        
        .profile-sidebar {
            background: var(--bg-body);
            padding: 20px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }
        
        .profile-nav a {
            display: block;
            padding: 12px 15px;
            color: var(--text-secondary);
            text-decoration: none;
            border-radius: 6px;
            margin-bottom: 5px;
            transition: all 0.2s ease;
        }
        
        .profile-nav a:hover, .profile-nav a.active {
            background: white;
            color: var(--primary-color);
            font-weight: 500;
        }
        
        .form-card {
            background: white;
            padding: 25px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            margin-bottom: 20px;
        }
        
        .form-card h3 {
            margin-top: 0;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 16px;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0,98,255,0.1);
        }
        
        .btn-save {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .btn-save:hover {
            background: #004ecc;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .btn-danger:hover {
            background: #c82333;
        }
        
        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    @include('navbar')
    
    <main>
        <section class="content-card">
            <h2>Meu Perfil</h2>
            
            @if(session('status') === 'profile-updated')
                <div class="alert alert-success">
                    ✅ Perfil atualizado com sucesso!
                </div>
            @endif
            
            @if(session('status') === 'password-updated')
                <div class="alert alert-success">
                    ✅ Palavra-passe atualizada com sucesso!
                </div>
            @endif
            
            @if($errors->any())
                <div class="alert alert-error">
                    @foreach($errors->all() as $error)
                        <p>❌ {{ $error }}</p>
                    @endforeach
                </div>
            @endif
            
            <div class="profile-grid">
                <!-- Sidebar -->
                <div class="profile-sidebar">
                    <div style="text-align: center; margin-bottom: 25px;">
                        <div style="width: 80px; height: 80px; background: var(--primary-color); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2em; margin: 0 auto 15px;">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <h4 style="margin: 0;">{{ Auth::user()->name }}</h4>
                        <p style="color: var(--text-secondary); font-size: 0.9em; margin: 5px 0 0 0;">{{ Auth::user()->email }}</p>
                    </div>
                    
                    <nav class="profile-nav">
                        <a href="#informacoes" class="active">📝 Informações Pessoais</a>
                        <a href="#password">🔒 Alterar Palavra-passe</a>
                        <a href="{{ route('profile.reservas') }}">🎫 Minhas Reservas</a>
                        <a href="{{ route('dashboard') }}">📊 Dashboard</a>
                    </nav>
                </div>
                
                <!-- Conteúdo -->
                <div>
                    <!-- Formulário de Informações Pessoais -->
                    <div id="informacoes" class="form-card">
                        <h3>Informações Pessoais</h3>
                        <form method="POST" action="{{ route('profile.update') }}">
                            @csrf
                            @method('patch')
                            
                            <div class="form-group">
                                <label for="name">Nome</label>
                                <input type="text" id="name" name="name" value="{{ old('name', Auth::user()->name) }}" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">E-mail</label>
                                <input type="email" id="email" name="email" value="{{ old('email', Auth::user()->email) }}" required>
                            </div>
                            
                            <button type="submit" class="btn-save">Guardar Alterações</button>
                        </form>
                    </div>
                    
                    <!-- Formulário de Palavra-passe -->
                    <div id="password" class="form-card">
                        <h3>Alterar Palavra-passe</h3>
                        <form method="POST" action="{{ route('password.update') }}">
                            @csrf
                            @method('put')
                            
                            <div class="form-group">
                                <label for="current_password">Palavra-passe Atual</label>
                                <input type="password" id="current_password" name="current_password" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="password">Nova Palavra-passe</label>
                                <input type="password" id="password" name="password" required>
                                <small style="color: var(--text-secondary); font-size: 0.85em;">Mínimo 8 caracteres</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="password_confirmation">Confirmar Nova Palavra-passe</label>
                                <input type="password" id="password_confirmation" name="password_confirmation" required>
                            </div>
                            
                            <button type="submit" class="btn-save">Alterar Palavra-passe</button>
                        </form>
                    </div>
                    
                    <!-- Eliminar Conta -->
                    <div class="form-card" style="border-color: #dc3545;">
                        <h3 style="color: #dc3545;">Zona Perigosa</h3>
                        <p style="color: var(--text-secondary); margin-bottom: 20px;">
                            Uma vez eliminada, a sua conta não poderá ser recuperada. 
                            Todos os seus dados e reservas serão permanentemente apagados.
                        </p>
                        
                        <form method="POST" action="{{ route('profile.destroy') }}" onsubmit="return confirm('Tem certeza que deseja eliminar a sua conta? Esta ação não pode ser desfeita.')">
                            @csrf
                            @method('delete')
                            
                            <div class="form-group">
                                <label for="delete_password">Para confirmar, insira a sua palavra-passe:</label>
                                <input type="password" id="delete_password" name="password" required>
                            </div>
                            
                            <button type="submit" class="btn-danger">Eliminar Minha Conta</button>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; 2025 sheiqaway. Trabalho Prático DSOS.</p>
    </footer>
    
    <script>
        // Navegação suave na página
        document.querySelectorAll('.profile-nav a').forEach(link => {
            link.addEventListener('click', function(e) {
                if (this.getAttribute('href').startsWith('#')) {
                    e.preventDefault();
                    const targetId = this.getAttribute('href').substring(1);
                    const targetElement = document.getElementById(targetId);
                    
                    if (targetElement) {
                        targetElement.scrollIntoView({ behavior: 'smooth' });
                        
                        // Atualizar link ativo
                        document.querySelectorAll('.profile-nav a').forEach(a => {
                            a.classList.remove('active');
                        });
                        this.classList.add('active');
                    }
                }
            });
        });
    </script>
</body>
</html>
