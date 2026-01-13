<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="theme-color" content="#0ea5e9">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Utilizadores - Admin</title>
    @vite(['resources/css/pages.css', 'resources/js/global.js'])
    <style>
        .admin-header { display: flex; flex-direction: column; gap: 1rem; margin-bottom: 1.5rem; }
        .admin-header h1 { margin: 0; font-size: 1.75rem; }
        .card { background: white; padding: 1.25rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .admin-table-wrapper { overflow-x: auto; -webkit-overflow-scrolling: touch; margin: 0 -1.25rem; padding: 0 1.25rem; }
        .admin-table { width: 100%; border-collapse: collapse; min-width: 800px; }
        .admin-table th { text-align: left; padding: 0.625rem; background: #f9fafb; border-bottom: 2px solid #e5e7eb; font-weight: 600; font-size: 0.875rem; white-space: nowrap; }
        .admin-table td { padding: 0.625rem; border-bottom: 1px solid #e5e7eb; font-size: 0.875rem; }
        .admin-table tr:hover { background: #f9fafb; }
        .badge { padding: 0.25rem 0.625rem; border-radius: 4px; font-size: 0.75rem; font-weight: 500; white-space: nowrap; display: inline-block; }
        .badge-confirmado { background: #d1fae5; color: #065f46; }
        .badge-cancelado { background: #fee2e2; color: #991b1b; }
        .btn-action { padding: 0.375rem 0.625rem; border: none; border-radius: 4px; cursor: pointer; font-size: 1rem; min-width: 36px; min-height: 36px; }
        .btn-danger { background: #fee2e2; color: #991b1b; }
        .btn-danger:hover { background: #fecaca; }
        .btn-secondary { background: #f8f9fa; color: #6c757d; border: 1px solid #dee2e6; padding: 0.75rem 1.25rem; border-radius: 6px; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; min-height: 44px; }
        .btn-secondary:hover { background: #dee2e6; }
        .alert { padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
        .alert-success { background: #d1fae5; border: 1px solid #10b981; color: #065f46; }
        .alert-error { background: #fee2e2; border: 1px solid #ef4444; color: #991b1b; }
        
        body.dark-mode .card { background: #2d2d2d; }
        body.dark-mode .admin-table th { background: #1f1f1f; color: #f3f4f6; }
        body.dark-mode .admin-table td { color: #d1d5db; border-bottom-color: #404040; }
        body.dark-mode .admin-table tr:hover { background: #1f1f1f; }
        
        @media (min-width: 768px) {
            .admin-header { flex-direction: row; justify-content: space-between; align-items: center; }
            .card { padding: 1.5rem; }
            .admin-table-wrapper { margin: 0; padding: 0; }
        }
    </style>
</head>
<body class="admin-page">
    @include('navbar')
    
    <main style="max-width: 1400px; margin: 0 auto; padding: 1.5rem 1rem;">
        <div class="admin-header">
            <h1>⚇ Gestão de Utilizadores</h1>
            <a href="/admin" class="btn-secondary">← Voltar</a>
        </div>

        @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-error">
            {{ session('error') }}
        </div>
        @endif

        <div class="card">
            <div class="admin-table-wrapper">
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
                            <td style="white-space: nowrap;">
                                <a href="{{ route('admin.users.edit', $user->id) }}" class="btn-action" style="background: #fef3c7; color: #92400e;" title="Editar">✎</a>
                                <form method="POST" action="{{ route('admin.users.toggle', $user->id) }}" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn-action" style="background: #dbeafe; color: #1e40af;" title="Alternar Admin">
                                        @if($user->is_admin) ⚇ @else ⚙ @endif
                                    </button>
                                </form>
                                @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.users.delete', $user->id) }}" style="display: inline;" onsubmit="return confirm('Eliminar este utilizador?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-action btn-danger" title="Eliminar">×</button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 1.5rem;">
                {{ $users->links() }}
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; 2025 sheiqaway · Trabalho Prático DSOS</p>
    </footer>
</body>
</html>
