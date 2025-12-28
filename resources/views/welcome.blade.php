<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Campus</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    <style>
        /*! tailwindcss v4.0.7 | MIT License | https://tailwindcss.com */
        @layer theme {
            :root,:host {
                --font-sans:'Instrument Sans',ui-sans-serif,system-ui,sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji";
                --font-serif:ui-serif,Georgia,Cambria,"Times New Roman",Times,serif;
                --font-mono:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono","Courier New",monospace;
                --color-red-50:oklch(.971 .013 17.38); /* ... all color variables here ... */
                --color-white:#fff;
                --spacing:.25rem;
                --radius-sm:.25rem;
                --radius-lg:.5rem;
                --text-sm:.875rem;
                --text-base:1rem;
                --text-lg:1.125rem;
                --text-xl:1.25rem;
                --text-2xl:1.5rem;
                --text-3xl:1.875rem;
                --text-4xl:2.25rem;
                --text-5xl:3rem;
                --text-6xl:3.75rem;
                --text-7xl:4.5rem;
                --text-8xl:6rem;
                --text-9xl:8rem;
                --font-weight-medium:500;
                --font-weight-bold:700;
                --leading-normal:1.5;
                --default-font-family:var(--font-sans);
                --default-mono-font-family:var(--font-mono);
            }
        }

        @layer base {
            *,:after,:before,::backdrop{box-sizing:border-box;border:0 solid;margin:0;padding:0;}
            html,body{line-height:1.5;font-family:var(--default-font-family);}
            img,video{max-width:100%;height:auto;}
            a{color:inherit;text-decoration:none;}
            button,input,select,textarea{font:inherit;color:inherit;background:transparent;border:none;}
        }

        @layer utilities {
            .flex{display:flex;}
            .flex-col{flex-direction:column;}
            .items-center{align-items:center;}
            .justify-center{justify-content:center;}
            .p-6{padding:calc(var(--spacing)*6);}
            .lg\:p-8{padding:calc(var(--spacing)*8);}
            .min-h-screen{min-height:100vh;}
            .text-sm{font-size:var(--text-sm);line-height:var(--leading-normal);}
            .text-[#1b1b18]{color:#1b1b18;}
            .bg-[#FDFDFC]{background-color:#fdfdfc;}
            .dark\:bg-[#0a0a0a]{background-color:#0a0a0a;}
        }

        @media (prefers-color-scheme:dark) {
            .dark\:bg-[#0a0a0a]{background-color:#0a0a0a;}
            .dark\:text-white{color:var(--color-white);}
        }
    </style>
</head>
<body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] flex p-6 lg:p-8 items-center lg:justify-center min-h-screen flex-col">
    <header class="w-full lg:max-w-4xl max-w-[335px] text-sm mb-6 not-has-[nav]:hidden">
        @if (Route::has('login'))
            <nav class="flex justify-end gap-4">
                @auth
                    <a href="{{ url('/dashboard') }}" class="text-sm text-[#1b1b18] dark:text-white">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="text-sm text-[#1b1b18] dark:text-white">Log in</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="text-sm text-[#1b1b18] dark:text-white">Register</a>
                    @endif
                @endauth
            </nav>
        @endif
    </header>

    <main class="w-full lg:max-w-4xl max-w-[335px] flex-1 flex flex-col items-center justify-center">
        <h1 class="text-3xl font-bold mb-4 text-[#1b1b18] dark:text-white">Welcome to My Campus</h1>
        <p class="text-sm text-[#706f6c] dark:text-[#A1A09A] mb-6 text-center">
            Manage your campus activities, presentations, and schedules in one place.
        </p>
        <div class="flex gap-3 flex-wrap justify-center">
            <a href="{{ route('dashboard') }}" class="px-5 py-2 bg-[#F53003] text-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.1)]">Go to Dashboard</a>
            <a href="{{ route('about') }}" class="px-5 py-2 bg-[#1b1b18] text-white rounded-lg shadow-[0_4px_6px_rgba(0,0,0,0.1)]">About</a>
        </div>
    </main>
</body>
</html>
