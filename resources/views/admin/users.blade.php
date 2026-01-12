<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Utilizadores - Admin</title>
    @vite(['resources/css/pages.css', 'resources/js/global.js'])
</head>
<body class="admin-page">
    @include('navbar')
    
    <main style="max-width: 1400px; margin: 0 auto; padding: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1>👥 Gestão de Utilizadores</h1>
            <a href="/admin" class="btn-secondary">← Voltar ao Dashboard</a>
        </div>

        @if(session('success'))
        <div style="background: #d1fae5; border: 1px solid #10b981; color: #065f46; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
            {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div style="background: #fee2e2; border: 1px solid #ef4444; color: #991b1b; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
            {{ session('error') }}
        </div>
        @endif

        <div class="card">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Pontos</th>
                        <th>Reservas</th>
                        <th>Admin</th>
                        <th>Registado</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td><strong>{{ $user->points ?? 0 }}</strong> pts</td>
                        <td>{{ $user->reservations_count }}</td>
                        <td>
                            @if($user->is_admin)
                            <span class="badge badge-confirmado">✓ Admin</span>
                            @else
                            <span class="badge badge-cancelado">✗ User</span>
                            @endif
                        </td>
                        <td>{{ $user->created_at->format('d/m/Y') }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.users.toggle', $user->id) }}" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn-action btn-warning" title="Alternar Admin">
                                    @if($user->is_admin) 👤 @else 🔧 @endif
                                </button>
                            </form>
                            @if($user->id !== auth()->id())
                            <form method="POST" action="{{ route('admin.users.delete', $user->id) }}" style="display: inline;" onsubmit="return confirm('Eliminar este utilizador?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-action btn-danger" title="Eliminar">🗑️</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="margin-top: 2rem;">
                {{ $users->links() }}
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; 2025 sheiqaway · Trabalho Prático DSOS</p>
    </footer>
</body>
</html>
