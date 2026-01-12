<x-guest-layout>
    <!-- Logo Sheiqaway -->
    <div class="mb-6 text-center">
        <img src="{{ asset('images/logo-sheiqaway.png') }}" alt="Logo Sheiqaway" class="w-auto h-16 mx-auto" />
    </div>

    <div class="mb-4 text-sm text-gray-600">
        {{ __('Obrigado por se registar! Antes de começar, poderia verificar o seu endereço de email clicando no link que acabamos de enviar? Se não recebeu o email, teremos todo o gosto em enviar outro.') }}
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-sm text-green-600">
            {{ __('Um novo link de verificação foi enviado para o endereço de email fornecido durante o registo.') }}
        </div>
    @endif

    @if(config('app.env') === 'local')
        <div class="mb-4 p-4 bg-yellow-100 border border-yellow-400 rounded">
            <strong>Modo de Desenvolvimento:</strong>
            <p class="mt-2">Para testar sem email real, clique no botão abaixo:</p>
            <a href="{{ route('dev.verify') }}" class="inline-block mt-2 px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                ✅ Verificar Email (DEV)
            </a>
        </div>
    @endif

    <div class="mt-4 flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <x-primary-button>
                    {{ __('Reenviar Email de Verificação') }}
                </x-primary-button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                {{ __('Sair') }}
            </button>
        </form>
    </div>
</x-guest-layout>
