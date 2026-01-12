<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Editar Utilizador - Admin</title>
    @vite(['resources/css/pages.css', 'resources/js/global.js'])
</head>
<body class="admin-page">
    @include('navbar')
    
    <main style="max-width: 800px; margin: 0 auto; padding: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1>✎ Editar Utilizador</h1>
            <a href="/admin/users" class="btn-secondary">← Voltar</a>
        </div>

        <div class="card">
            <form method="POST" action="{{ route('admin.users.update', $user->id) }}">
                @csrf
                @method('PUT')

                <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                    <p style="margin: 0.5rem 0;"><strong>ID:</strong> {{ $user->id }}</p>
                    <p style="margin: 0.5rem 0;"><strong>Registado:</strong> {{ $user->created_at->format('d/m/Y H:i') }}</p>
                    <p style="margin: 0.5rem 0;"><strong>Total Reservas:</strong> {{ $user->reservations_count }}</p>
                </div>

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #2c3e50;">Nome</label>
                    <input type="text" name="name" value="{{ $user->name }}" required 
                           style="width: 100%; padding: 0.75rem; border: 1px solid #dee2e6; border-radius: 6px; font-size: 1rem;">
                </div>

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #2c3e50;">Email</label>
                    <input type="email" name="email" value="{{ $user->email }}" required 
                           style="width: 100%; padding: 0.75rem; border: 1px solid #dee2e6; border-radius: 6px; font-size: 1rem;">
                </div>

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #2c3e50;">Pontos</label>
                    <input type="number" name="points" value="{{ $user->points ?? 0 }}" min="0" required 
                           style="width: 100%; padding: 0.75rem; border: 1px solid #dee2e6; border-radius: 6px; font-size: 1rem;">
                </div>

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label style="display: flex; align-items: center; cursor: pointer;">
                        <input type="checkbox" name="is_admin" value="1" {{ $user->is_admin ? 'checked' : '' }} 
                               style="margin-right: 0.5rem; width: 20px; height: 20px;">
                        <span style="font-weight: 600; color: #2c3e50;">Administrador</span>
                    </label>
                </div>

                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn" style="flex: 1;">✓ Guardar Alterações</button>
                    <a href="/admin/users" class="btn-secondary" style="flex: 1; text-align: center; padding: 0.75rem;">Cancelar</a>
                </div>
            </form>
        </div>
    </main>

    <footer>
        <p>&copy; 2025 sheiqaway · Trabalho Prático DSOS</p>
    </footer>
</body>
</html>
