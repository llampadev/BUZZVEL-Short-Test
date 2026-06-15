<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    @include('partials.head')
</head>
<body class="h-full font-sans text-slate-900 antialiased">
    <div class="flex min-h-full flex-col justify-center px-6 py-12 lg:px-8">
        <div class="mx-auto w-full max-w-sm">
            <div class="flex justify-center">
                <span class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-brand-600 text-xl font-bold text-white">B</span>
            </div>
            <h2 class="mt-6 text-center text-2xl font-bold tracking-tight text-slate-900">@yield('title')</h2>
            @hasSection('subtitle')
                <p class="mt-2 text-center text-sm text-slate-500">@yield('subtitle')</p>
            @endif
        </div>

        <div class="mx-auto mt-8 w-full max-w-sm">
            <div class="rounded-xl bg-white px-6 py-8 shadow-sm ring-1 ring-slate-200 sm:px-10">
                @if (session('success'))
                    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                        <ul class="list-inside list-disc space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>
</body>
</html>
