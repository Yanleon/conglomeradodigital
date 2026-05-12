@props([
    'hasHeader'  => true,
    'hasFeature' => true,
    'hasFooter'  => true,
])

<!DOCTYPE html>

<html
    lang="{{ app()->getLocale() }}"
    dir="{{ core()->getCurrentLocale()->direction }}"
>
    <head>

        {!! view_render_event('bagisto.shop.layout.head.before') !!}

        <title>{{ $title ?? '' }}</title>

        <meta charset="UTF-8">

        <meta
            http-equiv="X-UA-Compatible"
            content="IE=edge"
        >
        <meta
            http-equiv="content-language"
            content="{{ app()->getLocale() }}"
        >

        <meta
            name="viewport"
            content="width=device-width, initial-scale=1"
        >
        <meta
            name="base-url"
            content="{{ url()->to('/') }}"
        >
        <meta
            name="currency"
            content="{{ core()->getCurrentCurrency()->toJson() }}"
        >

        @stack('meta')

        <link
            rel="icon"
            sizes="16x16"
            href="{{ core()->getCurrentChannel()->favicon_url ?? bagisto_asset('images/favicon.ico') }}"
        />

        @bagistoVite(['src/Resources/assets/css/app.css', 'src/Resources/assets/js/app.js'])

        @php
            $themeBg       = core()->getConfigData('general.design.theme.background_color') ?: '#ffffff';
            $themeSurface  = core()->getConfigData('general.design.theme.surface_color') ?: '#f7f7f7';
            $themeButton   = core()->getConfigData('general.design.theme.button_color') ?: '#1b4db3';
            $themeText     = core()->getConfigData('general.design.theme.text_color') ?: '#1f2937';
            $themeHeading  = core()->getConfigData('general.design.theme.heading_color') ?: '#111827';
            $themeLink     = core()->getConfigData('general.design.theme.link_color') ?: '#1b4db3';
            $themeFont     = core()->getConfigData('general.design.theme.font_family') ?: 'Poppins';
            $themeFontSize = core()->getConfigData('general.design.theme.base_font_size') ?: '16px';
            $themeRadius   = core()->getConfigData('general.design.theme.card_radius') ?: '14px';

            $customFontUrl    = core()->getConfigData('general.design.theme.custom_font_url');
            $customFontFamily = core()->getConfigData('general.design.theme.custom_font_family');
        @endphp

        @if ($customFontUrl)
            <link
                rel="preload"
                href="{{ $customFontUrl }}"
                as="style"
            >
            <link
                rel="stylesheet"
                href="{{ $customFontUrl }}"
            >
        @else
            <link
                rel="preload"
                href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap"
                as="style"
            >
            <link
                rel="stylesheet"
                href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap"
            >
        @endif

        @stack('styles')

        @if (config('services.facebook.pixel_id'))
            <script>
                !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
                n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
                n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
                t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window, document,'script','https://connect.facebook.net/en_US/fbevents.js');

                fbq('init', @json(config('services.facebook.pixel_id')));
                fbq('track', 'PageView');
            </script>
        @endif

        <style>
            :root {
                --theme-bg: {{ $themeBg }};
                --theme-surface: {{ $themeSurface }};
                --theme-button: {{ $themeButton }};
                --theme-text: {{ $themeText }};
                --theme-heading: {{ $themeHeading }};
                --theme-link: {{ $themeLink }};
                --theme-font: '{{ $customFontFamily ?: $themeFont }}', sans-serif;
                --theme-font-size: {{ $themeFontSize }};
                --theme-radius: {{ $themeRadius }};
            }

            body {
                background: var(--theme-bg);
                color: var(--theme-text);
                font-family: var(--theme-font);
                font-size: var(--theme-font-size);
            }

            a {
                color: var(--theme-link);
            }

            h1, h2, h3, h4, h5, h6 {
                color: var(--theme-heading);
            }

            .primary-button,
            .btn-primary,
            button[type="submit"],
            .rounded-2xl.bg-navyBlue,
            .rounded-lg.bg-navyBlue {
                background-color: var(--theme-button) !important;
                border-color: var(--theme-button) !important;
                border-radius: var(--theme-radius);
            }

            .box-shadow,
            .card,
            .bg-white {
                background-color: var(--theme-surface);
                border-radius: var(--theme-radius);
            }

            {!! core()->getConfigData('general.content.custom_scripts.custom_css') !!}
        </style>

        {!! view_render_event('bagisto.shop.layout.head.after') !!}

    </head>

    <body>
        @if (config('services.facebook.pixel_id'))
            <noscript>
                <img
                    height="1"
                    width="1"
                    style="display:none"
                    src="https://www.facebook.com/tr?id={{ config('services.facebook.pixel_id') }}&ev=PageView&noscript=1"
                />
            </noscript>
        @endif
        @if (core()->getConfigData('general.general.whatsapp.number'))
            <x-shop::whatsapp />
        @endif
        {!! view_render_event('bagisto.shop.layout.body.before') !!}

        <a
            href="#main"
            class="skip-to-main-content-link"
        >
            Skip to main content
        </a>

        <div id="app">
            <!-- Flash Message Blade Component -->
            <x-shop::flash-group />

            <!-- Confirm Modal Blade Component -->
            <x-shop::modal.confirm />

            <!-- Promotion Popup Widget -->
            <x-shop::popup-widget />

            <!-- Page Header Blade Component -->
            @if ($hasHeader)
                <x-shop::layouts.header />
            @endif

            {!! view_render_event('bagisto.shop.layout.content.before') !!}

            <!-- Page Content Blade Component -->
            <main id="main" class="bg-white">
                {{ $slot }}
            </main>

            {!! view_render_event('bagisto.shop.layout.content.after') !!}


            <!-- Page Services Blade Component -->
            @if ($hasFeature)
                <x-shop::layouts.services />
            @endif

            <!-- Page Footer Blade Component -->
            @if ($hasFooter)
                <x-shop::layouts.footer />
            @endif
        </div>

        {!! view_render_event('bagisto.shop.layout.body.after') !!}

        @stack('facebook-pixel')

        @stack('scripts')

        {!! view_render_event('bagisto.shop.layout.vue-app-mount.before') !!}
        <script>
            /**
             * Load event, the purpose of using the event is to mount the application
             * after all of our `Vue` components which is present in blade file have
             * been registered in the app. No matter what `app.mount()` should be
             * called in the last.
             */
            window.addEventListener("load", function (event) {
                app.mount("#app");
            });
        </script>

        {!! view_render_event('bagisto.shop.layout.vue-app-mount.after') !!}

        <script type="text/javascript">
            {!! core()->getConfigData('general.content.custom_scripts.custom_javascript') !!}
        </script>
    </body>
</html>
