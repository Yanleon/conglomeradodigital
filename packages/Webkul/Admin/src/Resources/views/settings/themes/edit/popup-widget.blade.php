<v-popup-widget :errors="errors">
    <x-admin::shimmer.settings.themes.static-content />
</v-popup-widget>

@pushOnce('scripts')
    <script type="text/x-template" id="v-popup-widget-template">
        <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
            <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                <div class="mb-2.5 flex items-center justify-between gap-x-2.5">
                    <div class="flex flex-col gap-1">
                        <p class="text-base font-semibold text-gray-800 dark:text-white">
                            @lang('admin::app.settings.themes.edit.popup-widget.title')
                        </p>

                        <p class="text-xs font-medium text-gray-500 dark:text-gray-300">
                            @lang('admin::app.settings.themes.edit.popup-widget.description')
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 max-md:grid-cols-1">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">
                            @lang('admin::app.settings.themes.edit.popup-widget.enabled')
                        </label>

                        <select
                            class="w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                            name="{{ $currentLocale->code }}[options][enabled]"
                            v-model="popup.enabled"
                        >
                            <option value="1">@lang('admin::app.settings.themes.edit.yes')</option>
                            <option value="0">@lang('admin::app.settings.themes.edit.no')</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">
                            @lang('admin::app.settings.themes.edit.popup-widget.content-type')
                        </label>

                        <select
                            class="w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                            name="{{ $currentLocale->code }}[options][content_type]"
                            v-model="popup.content_type"
                        >
                            <option value="image">@lang('admin::app.settings.themes.edit.popup-widget.content-image')</option>
                            <option value="html">@lang('admin::app.settings.themes.edit.popup-widget.content-html')</option>
                            <option value="link">@lang('admin::app.settings.themes.edit.popup-widget.content-link')</option>
                        </select>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-4 max-md:grid-cols-1">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">
                            @lang('admin::app.settings.themes.edit.popup-widget.start-at')
                        </label>
                        <input
                            type="datetime-local"
                            class="w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                            name="{{ $currentLocale->code }}[options][start_at]"
                            v-model="popup.start_at"
                        />
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">
                            @lang('admin::app.settings.themes.edit.popup-widget.end-at')
                        </label>
                        <input
                            type="datetime-local"
                            class="w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                            name="{{ $currentLocale->code }}[options][end_at]"
                            v-model="popup.end_at"
                        />
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-4 max-md:grid-cols-1">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">
                            @lang('admin::app.settings.themes.edit.popup-widget.display-on')
                        </label>
                        <select
                            class="w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                            name="{{ $currentLocale->code }}[options][display_on]"
                            v-model="popup.display_on"
                        >
                            <option value="all">@lang('admin::app.settings.themes.edit.popup-widget.display-on-all')</option>
                            <option value="home">@lang('admin::app.settings.themes.edit.popup-widget.display-on-home')</option>
                            <option value="urls">@lang('admin::app.settings.themes.edit.popup-widget.display-on-urls')</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">
                            @lang('admin::app.settings.themes.edit.popup-widget.frequency')
                        </label>
                        <select
                            class="w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                            name="{{ $currentLocale->code }}[options][frequency]"
                            v-model="popup.frequency"
                        >
                            <option value="always">@lang('admin::app.settings.themes.edit.popup-widget.frequency-always')</option>
                            <option value="session">@lang('admin::app.settings.themes.edit.popup-widget.frequency-session')</option>
                            <option value="once">@lang('admin::app.settings.themes.edit.popup-widget.frequency-once')</option>
                            <option value="daily">@lang('admin::app.settings.themes.edit.popup-widget.frequency-daily')</option>
                            <option value="weekly">@lang('admin::app.settings.themes.edit.popup-widget.frequency-weekly')</option>
                        </select>
                    </div>
                </div>

                <div v-if="popup.display_on === 'urls'" class="mt-4">
                    <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">
                        @lang('admin::app.settings.themes.edit.popup-widget.display-on-urls')
                    </label>

                    <textarea
                        rows="4"
                        class="w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                        name="{{ $currentLocale->code }}[options][urls]"
                        v-model="popup.urls"
                    ></textarea>

                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-300">
                        @lang('admin::app.settings.themes.edit.popup-widget.urls-hint')
                    </p>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-4 max-md:grid-cols-1">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">
                            @lang('admin::app.settings.themes.edit.popup-widget.auto-close')
                        </label>
                        <input
                            type="text"
                            class="w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                            name="{{ $currentLocale->code }}[options][auto_close_seconds]"
                            v-model="popup.auto_close_seconds"
                        />
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">
                            @lang('admin::app.settings.themes.edit.popup-widget.no-show-again')
                        </label>
                        <select
                            class="w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                            name="{{ $currentLocale->code }}[options][show_no_show_again]"
                            v-model="popup.show_no_show_again"
                        >
                            <option value="1">@lang('admin::app.settings.themes.edit.yes')</option>
                            <option value="0">@lang('admin::app.settings.themes.edit.no')</option>
                        </select>
                    </div>
                </div>

                <div class="mt-4">
                    <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">
                        @lang('admin::app.settings.themes.edit.popup-widget.overlay-click-close')
                    </label>
                    <select
                        class="w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                        name="{{ $currentLocale->code }}[options][overlay_click_close]"
                        v-model="popup.overlay_click_close"
                    >
                        <option value="1">@lang('admin::app.settings.themes.edit.yes')</option>
                        <option value="0">@lang('admin::app.settings.themes.edit.no')</option>
                    </select>
                </div>

                <div class="mt-4 rounded border border-dashed border-orange-300 bg-orange-50 p-3 dark:border-orange-800 dark:bg-gray-900">
                    <p class="text-xs text-orange-700 dark:text-orange-300">
                        @lang('admin::app.settings.themes.edit.popup-widget.testing-hint')
                    </p>

                    <button
                        type="button"
                        class="secondary-button mt-2"
                        @click="resetPopupState"
                    >
                        @lang('admin::app.settings.themes.edit.popup-widget.reset-state')
                    </button>
                </div>

                <div class="mt-6 border-t border-slate-200 pt-4 dark:border-gray-800">
                    <div v-if="popup.content_type === 'image'" class="grid gap-3">
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                            @lang('admin::app.settings.themes.edit.popup-widget.banner')
                        </label>

                        <input type="hidden" name="{{ $currentLocale->code }}[options][banner]" :value="popup.banner || ''" />

                        <input
                            type="file"
                            name="{{ $currentLocale->code }}[options][banner_file]"
                            accept="image/*"
                            class="w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                        />

                        <p v-if="popup.banner" class="text-xs text-gray-500 dark:text-gray-300">
                            @lang('admin::app.settings.themes.edit.popup-widget.current-banner')
                            <a :href="'/' + popup.banner" target="_blank" class="text-blue-600 hover:underline">@lang('admin::app.settings.themes.edit.footer-content.view')</a>
                        </p>

                        <div class="grid grid-cols-2 gap-4 max-md:grid-cols-1">
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">
                                    @lang('admin::app.settings.themes.edit.popup-widget.link-url')
                                </label>
                                <input
                                    type="text"
                                    class="w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                                    name="{{ $currentLocale->code }}[options][link_url]"
                                    v-model="popup.link_url"
                                />
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">
                                    @lang('admin::app.settings.themes.edit.popup-widget.link-text')
                                </label>
                                <input
                                    type="text"
                                    class="w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                                    name="{{ $currentLocale->code }}[options][link_text]"
                                    v-model="popup.link_text"
                                />
                            </div>
                        </div>
                    </div>

                    <div v-else-if="popup.content_type === 'html'" class="grid gap-2">
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                            @lang('admin::app.settings.themes.edit.popup-widget.html')
                        </label>

                        <!-- Always submit HTML/CSS even when preview tab is active -->
                        <input type="hidden" name="{{ $currentLocale->code }}[options][html]" :value="popup.html || ''" />
                        <input type="hidden" name="{{ $currentLocale->code }}[options][css]" :value="popup.css || ''" />

                        <div class="flex gap-2">
                            <button
                                type="button"
                                class="secondary-button"
                                :class="{'!bg-gray-200 dark:!bg-gray-800': htmlTab === 'editor'}"
                                @click="htmlTab = 'editor'"
                            >
                                @lang('admin::app.settings.themes.edit.popup-widget.editor')
                            </button>

                            <button
                                type="button"
                                class="secondary-button"
                                :class="{'!bg-gray-200 dark:!bg-gray-800': htmlTab === 'preview'}"
                                @click="htmlTab = 'preview'"
                            >
                                @lang('admin::app.settings.themes.edit.popup-widget.preview')
                            </button>
                        </div>

                        <div v-if="htmlTab === 'editor'" class="grid gap-3">
                            <div class="grid grid-cols-2 gap-4 max-md:grid-cols-1">
                                <div>
                                    <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">
                                        @lang('admin::app.settings.themes.edit.popup-widget.html')
                                    </label>

                                    <textarea
                                        rows="10"
                                        class="w-full rounded-md border px-3 py-2 font-mono text-xs text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                                        v-model="popup.html"
                                    ></textarea>
                                </div>

                                <div>
                                    <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">
                                        @lang('admin::app.settings.themes.edit.popup-widget.css')
                                    </label>

                                    <textarea
                                        rows="10"
                                        class="w-full rounded-md border px-3 py-2 font-mono text-xs text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                                        v-model="popup.css"
                                    ></textarea>
                                </div>
                            </div>

                            <div class="grid gap-2">
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                    @lang('admin::app.settings.themes.edit.popup-widget.html-images')
                                </label>

                                <template v-for="(img, index) in popup.html_images" :key="index">
                                    <input type="hidden" :name="'{{ $currentLocale->code }}[options][html_images][' + index + ']'" :value="img" />
                                </template>

                                <input
                                    type="file"
                                    name="{{ $currentLocale->code }}[options][html_image_files][]"
                                    accept="image/*"
                                    multiple
                                    class="w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                                />

                                <p class="text-xs text-gray-500 dark:text-gray-300">
                                    @lang('admin::app.settings.themes.edit.popup-widget.html-images-hint')
                                </p>

                                <div v-if="popup.html_images.length" class="grid gap-1">
                                    <p class="text-xs font-medium text-gray-600 dark:text-gray-300">
                                        @lang('admin::app.settings.themes.edit.popup-widget.current-images')
                                    </p>

                                    <div class="flex flex-wrap gap-2">
                                        <a
                                            v-for="(img, index) in popup.html_images"
                                            :key="index"
                                            class="text-xs text-blue-600 hover:underline"
                                            :href="'/' + img"
                                            target="_blank"
                                        >
                                            @{{ img }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-else class="rounded-md border bg-white p-4 dark:border-gray-800 dark:bg-gray-950">
                            <div v-html="previewHtml"></div>
                        </div>
                    </div>

                    <div v-else class="grid grid-cols-2 gap-4 max-md:grid-cols-1">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">
                                @lang('admin::app.settings.themes.edit.popup-widget.link-url')
                            </label>
                            <input
                                type="text"
                                class="w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                                name="{{ $currentLocale->code }}[options][link_url]"
                                v-model="popup.link_url"
                            />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">
                                @lang('admin::app.settings.themes.edit.popup-widget.link-text')
                            </label>
                            <input
                                type="text"
                                class="w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                                name="{{ $currentLocale->code }}[options][link_text]"
                                v-model="popup.link_text"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </script>

    <script type="module">
        app.component('v-popup-widget', {
            template: '#v-popup-widget-template',
            props: ['errors'],

            data() {
                return {
                    popup: @json($theme->translate($currentLocale->code)['options'] ?? null),
                    htmlTab: 'editor',
                };
            },

            created() {
                if (this.popup === null) {
                    this.popup = {};
                }

                this.popup = {
                    enabled: '1',
                    start_at: '',
                    end_at: '',
                    display_on: 'all',
                    urls: '',
                    frequency: 'session',
                    auto_close_seconds: '',
                    show_no_show_again: '1',
                    overlay_click_close: '1',
                    content_type: 'image',
                    banner: '',
                    link_url: '',
                    link_text: '',
                    html: '',
                    css: '',
                    html_images: [],
                    ...this.popup,
                };
            },

            computed: {
                previewHtml() {
                    const css = (this.popup.css || '').replace(/^\s*<style\b[^>]*>/i, '').replace(/<\/style>\s*$/i, '');
                    const html = this.popup.html || '';

                    return `<style>${css}</style>${html}`;
                },
            },

            methods: {
                resetPopupState() {
                    try {
                        Object.keys(localStorage).forEach((key) => {
                            if (key.includes('popup_widget') || key.includes('bagisto:popup_widget_')) {
                                localStorage.removeItem(key);
                            }
                        });

                        Object.keys(sessionStorage).forEach((key) => {
                            if (key.includes('popup_widget') || key.includes('bagisto:popup_widget_')) {
                                sessionStorage.removeItem(key);
                            }
                        });

                        document.cookie.split(';').forEach((cookie) => {
                            const name = cookie.split('=')[0].trim();
                            if (name.includes('bagisto_popup_widget_')) {
                                document.cookie = `${name}=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/`;
                            }
                        });

                        this.$emitter.emit('add-flash', {
                            type: 'success',
                            message: '@lang('admin::app.settings.themes.edit.popup-widget.reset-success')',
                        });
                    } catch (e) {
                        this.$emitter.emit('add-flash', {
                            type: 'warning',
                            message: '@lang('admin::app.settings.themes.edit.popup-widget.reset-warning')',
                        });
                    }
                },
            },
        });
    </script>
@endPushOnce
