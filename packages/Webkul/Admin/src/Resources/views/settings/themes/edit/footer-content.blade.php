<v-footer-content :errors="errors">
    <x-admin::shimmer.settings.themes.footer-links />
</v-footer-content>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-footer-content-template"
    >
        <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
            <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                <div class="mb-2.5 flex items-center justify-between gap-x-2.5">
                    <div class="flex flex-col gap-1">
                        <p class="text-base font-semibold text-gray-800 dark:text-white">
                            @lang('admin::app.settings.themes.edit.footer-content.title')
                        </p>

                        <p class="text-xs font-medium text-gray-500 dark:text-gray-300">
                            @lang('admin::app.settings.themes.edit.footer-content.description')
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 max-md:grid-cols-1">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">
                            @lang('admin::app.settings.themes.edit.footer-content.show-logo')
                        </label>

                        <select
                            class="w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                            name="{{ $currentLocale->code }}[options][show_logo]"
                            v-model="footerContent.show_logo"
                        >
                            <option value="1">@lang('admin::app.settings.themes.edit.yes')</option>
                            <option value="0">@lang('admin::app.settings.themes.edit.no')</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">
                            @lang('admin::app.settings.themes.edit.footer-content.logo')
                        </label>

                        <input type="hidden" name="{{ $currentLocale->code }}[options][logo]" :value="footerContent.logo || ''" />

                        <input
                            type="file"
                            name="{{ $currentLocale->code }}[options][logo_file]"
                            accept="image/*"
                            class="w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                        />

                        <p v-if="footerContent.logo" class="mt-1 text-xs text-gray-500 dark:text-gray-300">
                            @lang('admin::app.settings.themes.edit.footer-content.current-logo'):
                            <a :href="'/' + footerContent.logo" target="_blank" class="text-blue-600 hover:underline">
                                @lang('admin::app.settings.themes.edit.footer-content.view')
                            </a>
                        </p>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-4 max-md:grid-cols-1">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">
                            @lang('admin::app.settings.themes.edit.footer-content.footer-bg')
                        </label>

                        <input
                            type="color"
                            class="h-10 w-full rounded-md border bg-white px-2 py-1 dark:border-gray-800 dark:bg-gray-900"
                            name="{{ $currentLocale->code }}[options][footer_bg]"
                            v-model="footerContent.footer_bg"
                        />
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">
                            @lang('admin::app.settings.themes.edit.footer-content.footer-bottom-bg')
                        </label>

                        <input
                            type="color"
                            class="h-10 w-full rounded-md border bg-white px-2 py-1 dark:border-gray-800 dark:bg-gray-900"
                            name="{{ $currentLocale->code }}[options][footer_bottom_bg]"
                            v-model="footerContent.footer_bottom_bg"
                        />
                    </div>
                </div>

                <div class="mt-4 grid gap-4">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">
                            @lang('admin::app.settings.themes.edit.footer-content.about-heading')
                        </label>

                        <input
                            type="text"
                            class="w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                            name="{{ $currentLocale->code }}[options][about_heading]"
                            v-model="footerContent.about_heading"
                        />
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">
                            @lang('admin::app.settings.themes.edit.footer-content.about-text')
                        </label>

                        <textarea
                            rows="4"
                            class="w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                            name="{{ $currentLocale->code }}[options][about_text]"
                            v-model="footerContent.about_text"
                        ></textarea>
                    </div>
                </div>
            </div>

            <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                <div class="mb-2.5 flex items-center justify-between gap-x-2.5">
                    <div class="flex flex-col gap-1">
                        <p class="text-base font-semibold text-gray-800 dark:text-white">
                            @lang('admin::app.settings.themes.edit.footer-content.contacts')
                        </p>

                        <p class="text-xs font-medium text-gray-500 dark:text-gray-300">
                            @lang('admin::app.settings.themes.edit.footer-content.contacts-description')
                        </p>
                    </div>

                    <div class="secondary-button" @click="isUpdatingContact=false;$refs.contactModal.toggle()">
                        @lang('admin::app.settings.themes.edit.footer-content.add-contact')
                    </div>
                </div>

                <div class="mb-3 grid grid-cols-2 gap-4 max-md:grid-cols-1">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">
                            @lang('admin::app.settings.themes.edit.footer-content.show-contacts')
                        </label>

                        <select
                            class="w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                            name="{{ $currentLocale->code }}[options][show_contacts]"
                            v-model="footerContent.show_contacts"
                        >
                            <option value="1">@lang('admin::app.settings.themes.edit.yes')</option>
                            <option value="0">@lang('admin::app.settings.themes.edit.no')</option>
                        </select>
                    </div>
                </div>

                <template v-for="(c, index) in footerContent.contacts" :key="index">
                    <input type="hidden" :name="'{{ $currentLocale->code }}[options][contacts][' + index + '][label]'" :value="c.label" />
                    <input type="hidden" :name="'{{ $currentLocale->code }}[options][contacts][' + index + '][value]'" :value="c.value" />
                    <input type="hidden" :name="'{{ $currentLocale->code }}[options][contacts][' + index + '][url]'" :value="c.url" />
                    <input type="hidden" :name="'{{ $currentLocale->code }}[options][contacts][' + index + '][sort_order]'" :value="c.sort_order" />
                </template>

                <div v-if="footerContent.contacts.length" class="grid">
                    <div
                        v-for="(c, index) in footerContent.contacts"
                        :key="index"
                        class="flex items-start justify-between gap-2.5 border-b border-slate-300 py-4 last:border-b-0 dark:border-gray-800"
                    >
                        <div class="grid gap-1">
                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                <span class="font-semibold">@{{ c.label }}</span>
                                <span class="text-gray-600 dark:text-gray-300">: @{{ c.value }}</span>
                            </p>
                            <p v-if="c.url" class="text-xs text-gray-500 dark:text-gray-300">
                                @{{ c.url }}
                            </p>
                        </div>

                        <div class="flex items-center gap-x-5">
                            <p class="cursor-pointer text-blue-600 transition-all hover:underline" @click="editContact(c, index)">
                                @lang('admin::app.settings.themes.edit.edit')
                            </p>
                            <p class="cursor-pointer text-red-600 transition-all hover:underline" @click="removeContact(index)">
                                @lang('admin::app.settings.themes.edit.delete')
                            </p>
                        </div>
                    </div>
                </div>

                <div v-else class="text-sm text-gray-500 dark:text-gray-300">
                    @lang('admin::app.settings.themes.edit.footer-content.no-contacts')
                </div>

                <x-admin::form v-slot="{ handleSubmit }" as="div" ref="contactForm">
                    <form @submit="handleSubmit($event, saveContact)">
                        <x-admin::modal ref="contactModal">
                            <x-slot:header>
                                <p class="text-lg font-bold text-gray-800 dark:text-white">
                                    @lang('admin::app.settings.themes.edit.footer-content.contact-form-title')
                                </p>
                            </x-slot>

                            <x-slot:content>
                                <x-admin::form.control-group.control type="hidden" name="key" />

                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label class="required">
                                        @lang('admin::app.settings.themes.edit.footer-content.contact-label')
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control
                                        type="text"
                                        name="label"
                                        rules="required"
                                        :label="trans('admin::app.settings.themes.edit.footer-content.contact-label')"
                                    />
                                </x-admin::form.control-group>

                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label class="required">
                                        @lang('admin::app.settings.themes.edit.footer-content.contact-value')
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control
                                        type="text"
                                        name="value"
                                        rules="required"
                                        :label="trans('admin::app.settings.themes.edit.footer-content.contact-value')"
                                    />
                                </x-admin::form.control-group>

                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label>
                                        @lang('admin::app.settings.themes.edit.footer-content.contact-url')
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control
                                        type="text"
                                        name="url"
                                        :label="trans('admin::app.settings.themes.edit.footer-content.contact-url')"
                                    />
                                </x-admin::form.control-group>

                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label class="required">
                                        @lang('admin::app.settings.themes.edit.footer-content.sort-order')
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control
                                        type="text"
                                        name="sort_order"
                                        rules="required|numeric"
                                        :label="trans('admin::app.settings.themes.edit.footer-content.sort-order')"
                                    />
                                </x-admin::form.control-group>
                            </x-slot>

                            <x-slot:footer>
                                <button type="submit" class="cursor-pointer rounded-md border border-blue-700 bg-blue-600 px-3 py-1.5 font-semibold text-gray-50">
                                    @lang('admin::app.settings.themes.edit.save-btn')
                                </button>
                            </x-slot>
                        </x-admin::modal>
                    </form>
                </x-admin::form>
            </div>

            <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                <p class="mb-2 text-base font-semibold text-gray-800 dark:text-white">
                    @lang('admin::app.settings.themes.edit.footer-content.sections')
                </p>

                <div class="grid grid-cols-2 gap-4 max-md:grid-cols-1">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">
                            @lang('admin::app.settings.themes.edit.footer-content.show-links')
                        </label>
                        <select class="w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                            name="{{ $currentLocale->code }}[options][show_links]"
                            v-model="footerContent.show_links"
                        >
                            <option value="1">@lang('admin::app.settings.themes.edit.yes')</option>
                            <option value="0">@lang('admin::app.settings.themes.edit.no')</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">
                            @lang('admin::app.settings.themes.edit.footer-content.show-newsletter')
                        </label>
                        <select class="w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                            name="{{ $currentLocale->code }}[options][show_newsletter]"
                            v-model="footerContent.show_newsletter"
                        >
                            <option value="1">@lang('admin::app.settings.themes.edit.yes')</option>
                            <option value="0">@lang('admin::app.settings.themes.edit.no')</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">
                            @lang('admin::app.settings.themes.edit.footer-content.show-social')
                        </label>
                        <select class="w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                            name="{{ $currentLocale->code }}[options][show_social]"
                            v-model="footerContent.show_social"
                        >
                            <option value="1">@lang('admin::app.settings.themes.edit.yes')</option>
                            <option value="0">@lang('admin::app.settings.themes.edit.no')</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">
                            @lang('admin::app.settings.themes.edit.footer-content.social-title')
                        </label>
                        <input
                            type="text"
                            class="w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                            name="{{ $currentLocale->code }}[options][social_title]"
                            v-model="footerContent.social_title"
                        />
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">
                            @lang('admin::app.settings.themes.edit.footer-content.bottom-text')
                        </label>
                        <input
                            type="text"
                            class="w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                            name="{{ $currentLocale->code }}[options][bottom_text]"
                            v-model="footerContent.bottom_text"
                        />

                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-300">
                            @lang('admin::app.settings.themes.edit.footer-content.bottom-text-hint')
                        </p>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-4 max-md:grid-cols-1">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">
                            @lang('admin::app.settings.themes.edit.footer-content.year-start')
                        </label>
                        <input
                            type="text"
                            class="w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                            name="{{ $currentLocale->code }}[options][year_start]"
                            v-model="footerContent.year_start"
                        />
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-3 gap-4 max-md:grid-cols-1">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">
                            @lang('admin::app.settings.themes.edit.footer-content.column-1-title')
                        </label>
                        <input type="text" class="w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300" name="{{ $currentLocale->code }}[options][column_1_title]" v-model="footerContent.column_1_title" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">
                            @lang('admin::app.settings.themes.edit.footer-content.column-2-title')
                        </label>
                        <input type="text" class="w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300" name="{{ $currentLocale->code }}[options][column_2_title]" v-model="footerContent.column_2_title" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">
                            @lang('admin::app.settings.themes.edit.footer-content.column-3-title')
                        </label>
                        <input type="text" class="w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300" name="{{ $currentLocale->code }}[options][column_3_title]" v-model="footerContent.column_3_title" />
                    </div>
                </div>
            </div>
        </div>
    </script>

    <script type="module">
        app.component('v-footer-content', {
            template: '#v-footer-content-template',
            props: ['errors'],

            data() {
                return {
                    footerContent: @json($theme->translate($currentLocale->code)['options'] ?? null),
                    isUpdatingContact: false,
                    selectedContactIndex: null,
                };
            },

            created() {
                if (this.footerContent === null) {
                    this.footerContent = {};
                }

                this.footerContent = {
                    show_logo: '1',
                    logo: '',
                    about_heading: '',
                    about_text: '',
                    footer_bg: '#f7e9d1',
                    footer_bottom_bg: '#f1eadf',
                    show_contacts: '1',
                    contacts: [],
                    show_links: '1',
                    show_newsletter: '1',
                    show_social: '1',
                    social_title: '',
                    bottom_text: '',
                    year_start: '2022',
                    column_1_title: 'Navigation',
                    column_2_title: 'Company',
                    column_3_title: 'More Links',
                    ...this.footerContent,
                };

                if (!Array.isArray(this.footerContent.contacts)) {
                    this.footerContent.contacts = [];
                }
            },

            methods: {
                saveContact(params) {
                    const contact = {
                        label: params.label,
                        value: params.value,
                        url: params.url || '',
                        sort_order: params.sort_order,
                    };

                    if (params.key !== undefined && params.key !== null && params.key !== '') {
                        this.footerContent.contacts.splice(Number(params.key), 1, contact);
                    } else {
                        this.footerContent.contacts.push(contact);
                    }

                    this.$refs.contactModal.toggle();
                },

                editContact(contact, index) {
                    this.isUpdatingContact = true;
                    this.selectedContactIndex = index;

                    this.$refs.contactForm.setValues({
                        key: index,
                        label: contact.label,
                        value: contact.value,
                        url: contact.url,
                        sort_order: contact.sort_order,
                    });

                    this.$refs.contactModal.toggle();
                },

                removeContact(index) {
                    this.footerContent.contacts.splice(index, 1);
                },
            },
        });
    </script>
@endPushOnce
