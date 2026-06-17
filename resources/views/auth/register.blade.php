@extends('layouts.app')

<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-gray-50">
    <div class="max-w-md w-full space-y-8 bg-white p-10 rounded-xl shadow-2xl border border-gray-100">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-indigo-600">Crear cuenta en Sabi</h2>
            <p class="mt-2 text-center text-sm text-gray-600">Únete al núcleo médico más avanzado</p>
        </div>
        
        <form class="mt-8 space-y-4" action="{{ route('register.post') }}" method="POST">
            @csrf
            
            {{-- Error General de Transacción --}}
            @if($errors->has('error'))
                <div class="p-3 bg-red-100 border-l-4 border-red-500 text-red-700 text-xs rounded">
                    {{ $errors->first('error') }}
                </div>
            @endif

            <div class="rounded-md shadow-sm space-y-3">
                {{-- Nombre --}}
                <div>
                    <input name="nombre" type="text" value="{{ old('nombre') }}" required 
                        class="appearance-none relative block w-full px-3 py-2 border @error('nombre') border-red-500 @else border-gray-300 @enderror placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
                        placeholder="Nombre completo">
                    @error('nombre') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                </div>
                
                {{-- Email --}}
                <div>
                    <input name="email" type="email" value="{{ old('email') }}" required 
                        class="appearance-none relative block w-full px-3 py-2 border @error('email') border-red-500 @else border-gray-300 @enderror placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
                        placeholder="Correo electrónico">
                    @error('email') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                </div>
                
                {{-- Selector de País --}}
                <div class="relative">
                    <label class="text-[10px] text-gray-500 ml-1">Selecciona tu país</label>
                    <select id="country-select" name="country_code" class="appearance-none block w-full px-3 py-2 border border-gray-300 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="">Cargando países...</option>
                    </select>
                </div>

                {{-- Teléfono --}}
                <div class="flex space-x-2">
                    <input id="phone-prefix" name="phone_prefix" type="text" value="{{ old('phone_prefix') }}" readonly 
                        class="w-20 bg-gray-50 px-3 py-2 border border-gray-300 text-gray-500 text-sm rounded-md cursor-not-allowed" placeholder="+00">
                    <input name="telefono" type="text" value="{{ old('telefono') }}" required 
                        class="flex-1 px-3 py-2 border @error('telefono') border-red-500 @else border-gray-300 @enderror placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
                        placeholder="Número de teléfono">
                </div>
                @error('telefono') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror

                {{-- Datos API --}}
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="text-[10px] text-gray-500 ml-1">Idioma(s)</label>
                        <input id="idioma" name="idioma" type="text" value="{{ old('idioma') }}" readonly class="bg-gray-50 w-full px-3 py-2 border border-gray-300 text-gray-500 text-[10px] rounded-md cursor-not-allowed">
                    </div>
                    <div>
                        <label class="text-[10px] text-gray-500 ml-1">Zona Horaria</label>
                        <input id="zona-horaria" name="zona_horaria" type="text" value="{{ old('zona_horaria') }}" readonly class="bg-gray-50 w-full px-3 py-2 border border-gray-300 text-gray-500 text-[10px] rounded-md cursor-not-allowed">
                    </div>
                </div>

                {{-- Contraseñas --}}
                <div>
                    <input id="password" name="password" type="password" required 
                        class="appearance-none relative block w-full px-3 py-2 border @error('password') border-red-500 @else border-gray-300 @enderror placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
                        placeholder="Contraseña">
                    @error('password') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                </div>
                
                <div>
                    <input id="password_confirmation" name="password_confirmation" type="password" required 
                        class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
                        placeholder="Confirmar contraseña">
                    <p id="match-msg" class="text-[10px] mt-1 hidden"></p>
                </div>
            </div>

            <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                Registrarse
            </button>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const pass = document.getElementById('password');
        const confirm = document.getElementById('password_confirmation');
        const msg = document.getElementById('match-msg');

        // Validación en tiempo real
        function checkPasswords() {
            if (confirm.value.length > 0) {
                msg.classList.remove('hidden');
                if (pass.value === confirm.value) {
                    msg.textContent = '✓ Las contraseñas coinciden';
                    msg.className = 'text-[10px] mt-1 text-green-600 font-bold';
                    confirm.classList.replace('border-gray-300', 'border-green-500');
                    confirm.classList.remove('border-red-500');
                } else {
                    msg.textContent = '✗ Las contraseñas no coinciden';
                    msg.className = 'text-[10px] mt-1 text-red-600 font-bold';
                    confirm.classList.replace('border-gray-300', 'border-red-500');
                    confirm.classList.remove('border-green-500');
                }
            } else {
                msg.classList.add('hidden');
                confirm.classList.remove('border-green-500', 'border-red-500');
            }
        }

        pass.addEventListener('input', checkPasswords);
        confirm.addEventListener('input', checkPasswords);

        // Lógica de Tom Select y API Rest Countries
        const prefixInput = document.getElementById('phone-prefix');
        const idiomaInput = document.getElementById('idioma');
        const zonaInput = document.getElementById('zona-horaria');

        const control = new TomSelect('#country-select', {
            valueField: 'cca2',
            labelField: 'name',
            searchField: 'name',
            onChange: function(val) {
                const data = this.options[val];
                if (data) {
                    prefixInput.value = data.prefix || '';
                    idiomaInput.value = data.idioma || '';
                    zonaInput.value = data.timezone || '';
                }
            },
            render: {
                option: (data, escape) => `<div class="flex items-center py-1"><img class="w-6 h-4 mr-2 rounded-sm" src="${data.flag_url}"><span class="text-xs text-gray-700">${escape(data.name)}</span></div>`,
                item: (data, escape) => `<div class="flex items-center"><img class="w-5 h-3 mr-2 rounded-sm" src="${data.flag_url}"><span class="text-gray-900 text-xs">${escape(data.name)}</span></div>`
            }
        });

        fetch('https://restcountries.com/v3.1/all?fields=name,flags,idd,cca2,languages,timezones')
            .then(res => res.json())
            .then(data => {
                const countries = data.sort((a,b) => a.name.common.localeCompare(b.name.common)).map(c => ({
                    cca2: c.cca2,
                    name: c.name.common,
                    flag_url: c.flags.svg,
                    prefix: c.idd.root + (c.idd.suffixes ? c.idd.suffixes[0] : ''),
                    idioma: c.languages ? Object.values(c.languages).join(', ') : 'N/A',
                    timezone: c.timezones ? c.timezones[0] : 'UTC'
                }));
                control.addOptions(countries);
            });
    });
</script>
@endsection