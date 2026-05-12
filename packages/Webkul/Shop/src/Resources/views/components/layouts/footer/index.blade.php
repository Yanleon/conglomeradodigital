{!! view_render_event('bagisto.shop.layout.footer.before') !!}

<!--
    The category repository is injected directly here because there is no way
    to retrieve it from the view composer, as this is an anonymous component.
-->
@inject('themeCustomizationRepository', 'Webkul\Theme\Repositories\ThemeCustomizationRepository')

<!--
    This code needs to be refactored to reduce the amount of PHP in the Blade
    template as much as possible.
-->
@php
    $footerLinksCustomization = $themeCustomizationRepository->findOneWhere([
        'type'       => 'footer_links',
        'status'     => 1,
        'channel_id' => core()->getCurrentChannel()->id,
    ]);

    $footerContentCustomization = $themeCustomizationRepository->findOneWhere([
        'type'       => 'footer_content',
        'status'     => 1,
        'channel_id' => core()->getCurrentChannel()->id,
    ]);

    $footerContent = $footerContentCustomization?->options ?? [];

    $showLogo = (bool) ($footerContent['show_logo'] ?? true);
    $showContacts = (bool) ($footerContent['show_contacts'] ?? true);
    $showLinks = (bool) ($footerContent['show_links'] ?? true);
    $showNewsletter = (bool) ($footerContent['show_newsletter'] ?? true);
    $showSocial = (bool) ($footerContent['show_social'] ?? true);

    $contacts = $footerContent['contacts'] ?? [];
    if (is_array($contacts)) {
        usort($contacts, function ($a, $b) {
            return ($a['sort_order'] ?? 0) - ($b['sort_order'] ?? 0);
        });
    } else {
        $contacts = [];
    }

    $columnTitles = [
        'column_1' => $footerContent['column_1_title'] ?? null,
        'column_2' => $footerContent['column_2_title'] ?? null,
        'column_3' => $footerContent['column_3_title'] ?? null,
    ];

    $hexColorRegex = '/^#(?:[0-9a-fA-F]{3}){1,2}$/';
    $footerBg = isset($footerContent['footer_bg']) && is_string($footerContent['footer_bg']) && preg_match($hexColorRegex, $footerContent['footer_bg'])
        ? $footerContent['footer_bg']
        : null;
    $footerBottomBg = isset($footerContent['footer_bottom_bg']) && is_string($footerContent['footer_bottom_bg']) && preg_match($hexColorRegex, $footerContent['footer_bottom_bg'])
        ? $footerContent['footer_bottom_bg']
        : null;

    $socialTitle = isset($footerContent['social_title']) && is_string($footerContent['social_title'])
        ? trim($footerContent['social_title'])
        : '';

    if ($socialTitle === '') {
        // Backwards-compatible: if a dedicated title isn't set, reuse column 3 title.
        $socialTitle = isset($footerContent['column_3_title']) && is_string($footerContent['column_3_title'])
            ? trim($footerContent['column_3_title'])
            : '';
    }

    $bottomText = $footerContent['bottom_text'] ?? null;
    $yearStart = isset($footerContent['year_start']) ? (int) $footerContent['year_start'] : null;
    $currentYear = (int) date('Y');
    $yearRange = $yearStart && $yearStart > 0 && $yearStart <= $currentYear
        ? ($yearStart === $currentYear ? (string) $currentYear : ($yearStart.'-'.$currentYear))
        : (string) $currentYear;

    if (is_string($bottomText) && $bottomText !== '') {
        $bottomText = str_replace(['{year_range}', '{current_year}'], [$yearRange, (string) $currentYear], $bottomText);
    }
@endphp

<footer
    class="mt-9 bg-lightOrange max-sm:mt-10"
    @if ($footerBg)
        style="background-color: {{ $footerBg }}"
    @endif
>
    <div class="flex justify-between gap-x-6 gap-y-8 p-[60px] max-1060:flex-col-reverse max-md:gap-5 max-md:p-8 max-sm:px-4 max-sm:py-5">
        @if ($showLogo || $showContacts)
            <div class="grid content-start gap-4 max-1060:justify-items-center max-1060:text-center">
                @if ($showLogo)
                    <div class="grid gap-2">
                        @if (! empty($footerContent['logo']))
                            <img
                                src="/{{ $footerContent['logo'] }}"
                                alt="Footer Logo"
                                class="h-10 w-auto max-1060:mx-auto"
                            />
                        @endif

                        @if (! empty($footerContent['about_heading']))
                            <p class="text-lg font-semibold text-navyBlue">
                                {{ $footerContent['about_heading'] }}
                            </p>
                        @endif

                        @if (! empty($footerContent['about_text']))
                            <p class="max-w-[360px] text-sm text-zinc-700">
                                {{ $footerContent['about_text'] }}
                            </p>
                        @endif
                    </div>
                @endif

                @if ($showContacts && count($contacts))
                    <div class="grid gap-2 max-1060:justify-items-center">
                        @foreach ($contacts as $c)
                            @php
                                $label = $c['label'] ?? '';
                                $value = $c['value'] ?? '';
                                $url = $c['url'] ?? '';
                            @endphp

                            @if ($value)
                                <p class="text-sm text-zinc-700">
                                    @if ($label)
                                        <span class="font-semibold">{{ $label }}:</span>
                                    @endif

                                    @if ($url)
                                        <a href="{{ $url }}" class="hover:underline">{{ $value }}</a>
                                    @else
                                        {{ $value }}
                                    @endif
                                </p>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

        <!-- For Desktop View -->
        @if ($showLinks)
            <div class="flex flex-wrap items-start gap-24 max-1180:gap-6 max-1060:hidden">
                @if ($footerLinksCustomization?->options)
                    @foreach ($footerLinksCustomization->options as $columnKey => $footerLinkSection)
                        @php
                            usort($footerLinkSection, function ($a, $b) {
                                return ($a['sort_order'] ?? 0) - ($b['sort_order'] ?? 0);
                            });
                        @endphp

                        <div class="grid gap-4">
                            @if (! empty($columnTitles[$columnKey]))
                                <p class="text-sm font-semibold text-navyBlue">
                                    {{ $columnTitles[$columnKey] }}
                                </p>
                            @endif

                            <ul class="grid gap-3 text-sm">
                                @foreach ($footerLinkSection as $link)
                                    <li>
                                        <a href="{{ $link['url'] }}" class="hover:underline">
                                            {{ $link['title'] }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                @endif
            </div>
        @endif

        <!-- For Mobile view -->
        <x-shop::accordion
            :is-active="false"
            class="hidden !w-full rounded-xl !border-2 !border-[#e9decc] max-1060:block max-sm:rounded-lg"
        >
            <x-slot:header class="rounded-t-lg bg-[#F1EADF] font-medium max-md:p-2.5 max-sm:px-3 max-sm:py-2 max-sm:text-sm">
                @lang('shop::app.components.layouts.footer.footer-content')
            </x-slot>

            <x-slot:content class="flex justify-between !bg-transparent !p-4">
                @if ($showLinks && $footerLinksCustomization?->options)
                    @foreach ($footerLinksCustomization->options as $columnKey => $footerLinkSection)
                        @php
                            usort($footerLinkSection, function ($a, $b) {
                                return ($a['sort_order'] ?? 0) - ($b['sort_order'] ?? 0);
                            });
                        @endphp

                        <div class="grid gap-3">
                            @if (! empty($columnTitles[$columnKey]))
                                <p class="text-sm font-semibold text-navyBlue">
                                    {{ $columnTitles[$columnKey] }}
                                </p>
                            @endif

                            <ul class="grid gap-3 text-sm">
                                @foreach ($footerLinkSection as $link)
                                    <li>
                                        <a href="{{ $link['url'] }}" class="text-sm font-medium hover:underline max-sm:text-xs">
                                            {{ $link['title'] }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                @endif
            </x-slot>
        </x-shop::accordion>

        <!-- Social Media -->
        @if ($showSocial)
            <div class="grid justify-items-center gap-2">
                @if ($socialTitle)
                    <p class="text-sm font-semibold text-navyBlue">
                        {{ $socialTitle }}
                    </p>
                @endif

                <x-shop::social-media />
            </div>
        @endif

        {!! view_render_event('bagisto.shop.layout.footer.newsletter_subscription.before') !!}

        <!-- News Letter subscription -->
        @if ($showNewsletter && core()->getConfigData('customer.settings.newsletter.subscription'))
            <div class="grid gap-2.5">
                <p
                    class="max-w-[288px] text-3xl italic leading-[45px] text-navyBlue max-md:text-2xl max-sm:text-lg"
                    role="heading"
                    aria-level="2"
                >
                    @lang('shop::app.components.layouts.footer.newsletter-text')
                </p>

                <p class="text-xs">
                    @lang('shop::app.components.layouts.footer.subscribe-stay-touch')
                </p>

                <div>
                    <x-shop::form
                        :action="route('shop.subscription.store')"
                        class="mt-2.5 rounded max-sm:mt-0"
                    >
                        <div class="relative w-full">
                            <x-shop::form.control-group.control
                                type="email"
                                class="block w-[420px] max-w-full rounded-xl border-2 border-[#e9decc] bg-[#F1EADF] px-5 py-4 text-base max-1060:w-full max-md:p-3.5 max-sm:mb-0 max-sm:rounded-lg max-sm:border-2 max-sm:p-2 max-sm:text-sm"
                                name="email"
                                rules="required|email"
                                label="Email"
                                :aria-label="trans('shop::app.components.layouts.footer.email')"
                                placeholder="email@example.com"
                            />

                            <x-shop::form.control-group.error control-name="email" />

                            <button
                                type="submit"
                                class="absolute top-1.5 flex w-max items-center rounded-xl bg-white px-7 py-2.5 font-medium hover:bg-zinc-100 max-md:top-1 max-md:px-5 max-md:text-xs max-sm:mt-0 max-sm:rounded-lg max-sm:px-4 max-sm:py-2 ltr:right-2 rtl:left-2"
                            >
                                @lang('shop::app.components.layouts.footer.subscribe')
                            </button>
                        </div>
                    </x-shop::form>
                </div>
            </div>
        @endif

        {!! view_render_event('bagisto.shop.layout.footer.newsletter_subscription.after') !!}
    </div>

    <div
        class="flex justify-between bg-[#F1EADF] px-[60px] py-3.5 max-md:justify-center max-sm:px-5"
        @if ($footerBottomBg)
            style="background-color: {{ $footerBottomBg }}"
        @endif
    >
        {!! view_render_event('bagisto.shop.layout.footer.footer_text.before') !!}

        <p class="text-sm text-zinc-600 max-md:text-center">
            @if (! empty($bottomText))
                {{ $bottomText }}
            @else
                @lang('shop::app.components.layouts.footer.footer-text', ['current_year'=> date('Y') ])
            @endif
        </p>

        {!! view_render_event('bagisto.shop.layout.footer.footer_text.after') !!}
    </div>
</footer>

{!! view_render_event('bagisto.shop.layout.footer.after') !!}
