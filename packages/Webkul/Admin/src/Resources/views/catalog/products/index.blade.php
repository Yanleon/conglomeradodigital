<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.catalog.products.index.title')
    </x-slot>

    @php
        $viewMode = request('view') === 'table' ? 'table' : 'cards';
        $isStatusActive = request('filters.status.0') === '1';
        $isStatusDraft = request('filters.status.0') === '0';
        $isLowStock = request('stock') === 'low';
        $isAllProducts = ! $isStatusActive && ! $isStatusDraft && ! $isLowStock;
    @endphp

    <div class="{{ $viewMode === 'cards' ? 'products-cards-mode' : 'products-table-mode' }}">
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('admin::app.catalog.products.index.title')
            </p>

            <div class="flex items-center gap-x-2.5">
                <x-admin::datagrid.export :src="route('admin.catalog.products.index')" />

                {!! view_render_event('bagisto.admin.catalog.products.create.before') !!}

                @if (bouncer()->hasPermission('catalog.products.create'))
                    <v-create-product-form>
                        <button
                            type="button"
                            class="primary-button"
                        >
                            @lang('admin::app.catalog.products.index.create-btn')
                        </button>
                    </v-create-product-form>
                @endif

                {!! view_render_event('bagisto.admin.catalog.products.create.after') !!}
            </div>
        </div>

        <div class="mt-6 grid grid-cols-4 gap-3 max-xl:grid-cols-2 max-md:grid-cols-1">
            <a
                href="{{ route('admin.catalog.products.index', ['view' => $viewMode]) }}"
                class="rounded-xl border-2 bg-white p-4 transition duration-150 hover:-translate-y-0.5 hover:shadow-md dark:bg-gray-900 {{ $isAllProducts ? 'border-blue-500 ring-2 ring-blue-100 dark:ring-blue-900/40' : 'border-blue-200/70 dark:border-blue-800/40' }}"
                style="box-shadow: inset 0 3px 0 rgba(37, 99, 235, 0.22);"
            >
                <div class="flex items-center gap-3">
                    <span class="flex h-11 w-11 items-center justify-center rounded-full bg-blue-50 text-blue-600">
                        <span style="font-size: 20px; line-height: 1; color:#2563eb;">▣</span>
                    </span>

                    <div>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $kpiStats['total'] ?? 0 }}</p>
                        <p class="text-sm text-gray-500">Total productos</p>
                    </div>
                </div>
            </a>

            <a
                href="{{ route('admin.catalog.products.index', array_merge(request()->query(), ['view' => $viewMode, 'filters' => array_merge((array) request('filters', []), ['status' => [1]])])) }}"
                class="rounded-xl border-2 bg-white p-4 transition duration-150 hover:-translate-y-0.5 hover:shadow-md dark:bg-gray-900 {{ $isStatusActive ? 'border-green-500 ring-2 ring-green-100 dark:ring-green-900/40' : 'border-green-200/70 dark:border-green-800/40' }}"
                style="box-shadow: inset 0 3px 0 rgba(22, 163, 74, 0.22);"
            >
                <div class="flex items-center gap-3">
                    <span class="flex h-11 w-11 items-center justify-center rounded-full bg-green-50 text-green-600">
                        <span style="font-size: 20px; line-height: 1; color:#16a34a;">✔</span>
                    </span>

                    <div>
                        <p class="text-2xl font-semibold text-green-600">{{ $kpiStats['active'] ?? 0 }}</p>
                        <p class="text-sm text-gray-500">Activos</p>
                    </div>
                </div>
            </a>

            <a
                href="{{ route('admin.catalog.products.index', array_merge(request()->query(), ['view' => $viewMode, 'stock' => 'low'])) }}"
                class="rounded-xl border-2 bg-white p-4 transition duration-150 hover:-translate-y-0.5 hover:shadow-md dark:bg-gray-900 {{ $isLowStock ? 'border-orange-500 ring-2 ring-orange-100 dark:ring-orange-900/40' : 'border-orange-200/80 dark:border-orange-800/40' }}"
                style="box-shadow: inset 0 3px 0 rgba(249, 115, 22, 0.22);"
            >
                <div class="flex items-center gap-3">
                    <span class="flex h-11 w-11 items-center justify-center rounded-full bg-orange-50 text-orange-500">
                        <span style="font-size: 20px; line-height: 1; color:#f97316;">⚠</span>
                    </span>

                    <div>
                        <p class="text-2xl font-semibold text-orange-500">{{ $kpiStats['low_stock'] ?? 0 }}</p>
                        <p class="text-sm text-gray-500">Bajo stock</p>
                    </div>
                </div>
            </a>

            <a
                href="{{ route('admin.catalog.products.index', array_merge(request()->query(), ['view' => $viewMode, 'filters' => array_merge((array) request('filters', []), ['status' => [0]])])) }}"
                class="rounded-xl border-2 bg-white p-4 transition duration-150 hover:-translate-y-0.5 hover:shadow-md dark:bg-gray-900 {{ $isStatusDraft ? 'border-violet-500 ring-2 ring-violet-100 dark:ring-violet-900/40' : 'border-violet-200/80 dark:border-violet-800/40' }}"
                style="box-shadow: inset 0 3px 0 rgba(139, 92, 246, 0.22);"
            >
                <div class="flex items-center gap-3">
                    <span class="flex h-11 w-11 items-center justify-center rounded-full bg-violet-50 text-violet-500">
                        <span style="font-size: 20px; line-height: 1; color:#8b5cf6;">📄</span>
                    </span>

                    <div>
                        <p class="text-2xl font-semibold text-indigo-500">{{ $kpiStats['draft'] ?? 0 }}</p>
                        <p class="text-sm text-gray-500">Borradores</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="mt-4 flex items-center gap-2">
            <a
                href="{{ route('admin.catalog.products.index', array_merge(request()->query(), ['view' => 'cards'])) }}"
                class="rounded-lg border px-3 py-1.5 text-sm font-medium transition {{ $viewMode === 'cards' ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-200 text-gray-600 dark:border-gray-800 dark:text-gray-300' }}"
            >
                Tarjetas
            </a>

            <a
                href="{{ route('admin.catalog.products.index', array_merge(request()->query(), ['view' => 'table'])) }}"
                class="rounded-lg border px-3 py-1.5 text-sm font-medium transition {{ $viewMode === 'table' ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-200 text-gray-600 dark:border-gray-800 dark:text-gray-300' }}"
            >
                Tabla
            </a>

            <a
                href="{{ request()->boolean('show_variants')
                    ? route('admin.catalog.products.index', array_merge(request()->except('show_variants'), ['view' => $viewMode]))
                    : route('admin.catalog.products.index', array_merge(request()->query(), ['view' => $viewMode, 'show_variants' => 1])) }}"
                class="rounded-lg border px-3 py-1.5 text-sm font-medium transition {{ request()->boolean('show_variants') ? 'border-orange-500 bg-orange-50 text-orange-700' : 'border-gray-200 text-gray-600 dark:border-gray-800 dark:text-gray-300' }}"
            >
                {{ request()->boolean('show_variants') ? 'Ocultar variantes' : 'Mostrar variantes' }}
            </a>
        </div>

        {!! view_render_event('bagisto.admin.catalog.products.list.before') !!}

        @if ($viewMode === 'cards')
        <x-admin::datagrid
            :src="route('admin.catalog.products.index')"
            :isMultiRow="true"
            ref="productsDatagrid"
        >
        <!-- Datagrid Header -->
        @php
            $hasPermission = bouncer()->hasPermission('catalog.products.edit') || bouncer()->hasPermission('catalog.products.delete');
        @endphp

        <template #header="{ isLoading }">
            <template v-if="isLoading">
                <x-admin::shimmer.datagrid.table.head :isMultiRow="true" />
            </template>

            <template v-else>
                <div class="hidden"></div>
            </template>
        </template>

        <template #body="{
            isLoading,
            available,
            applied,
            selectAll,
            sort,
            performAction
        }">
            <template v-if="isLoading">
                <x-admin::shimmer.datagrid.table.body :isMultiRow="true" />
            </template>

            <template v-else>
                <div
                    class="mt-4 grid grid-cols-4 gap-4 max-2xl:grid-cols-3 max-xl:grid-cols-2 max-md:grid-cols-1"
                    v-if="Array.isArray(available.records) && available.records.length"
                >
                    <div
                        class="overflow-hidden rounded-xl border border-gray-200 bg-white p-2 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-gray-800 dark:bg-gray-900"
                        v-for="record in available.records"
                    >
                        <div class="relative">
                            <template v-if="record.base_image">
                                <img
                                    class="w-full rounded-lg object-cover"
                                    style="height: 170px;"
                                    :src="`{{ Storage::url('') }}${record.base_image}`"
                                />
                            </template>

                            <template v-else>
                                <div class="flex items-center justify-center rounded-lg border border-dashed border-gray-300 dark:border-gray-700" style="height: 170px;">
                                    <img
                                        class="h-10 w-10 opacity-40"
                                        src="{{ bagisto_asset('images/product-placeholders/front.svg')}}"
                                    >
                                </div>
                            </template>

                            <span
                                class="absolute left-10 top-2 rounded-full px-2 py-0.5 text-[11px] font-semibold"
                                style="background-color:#f97316;color:#fff;"
                                v-if="! ['configurable', 'bundle', 'grouped'].includes(record.type) && Number(record.quantity) > 0 && Number(record.quantity) <= 3"
                            >
                                Bajo stock
                            </span>

                            <span
                                class="absolute left-10 top-2 rounded-full px-2 py-0.5 text-[11px] font-semibold"
                                style="background-color:#dc2626;color:#fff;"
                                v-if="! ['configurable', 'bundle', 'grouped'].includes(record.type) && Number(record.quantity) <= 0"
                            >
                                Sin stock
                            </span>
                        </div>

                        <div class="mt-2 flex items-start justify-between gap-2 px-1">
                            <div>
                                <p class="line-clamp-1 text-base font-semibold text-gray-900 dark:text-white">@{{ record.name }}</p>
                                <p class="text-xs text-gray-500">SKU: @{{ record.sku }}</p>
                            </div>
                        </div>

                        <div class="mt-1 flex items-center justify-between px-1">
                            <p class="text-xl font-semibold text-gray-900 dark:text-white">
                                @{{ $admin.formatPrice(record.type === 'configurable' && Number(record.min_variant_price) > 0 ? record.min_variant_price : record.price) }}
                            </p>

                            <p class="text-sm font-medium text-gray-600 dark:text-gray-300" v-if="['configurable', 'bundle', 'grouped'].includes(record.type)">N/A</p>
                            <p
                                class="text-sm font-medium"
                                :style="Number(record.quantity) <= 3 ? 'color:#f97316' : 'color:#16a34a'"
                                v-else-if="Number(record.quantity) > 0"
                            >
                                @{{ record.quantity }} disponibles
                            </p>
                            <p class="text-sm font-medium" style="color:#dc2626;" v-else>Sin stock</p>
                        </div>

                        <div class="mt-2 flex items-center gap-1.5 px-1 text-xs text-gray-500">
                            <span class="rounded bg-gray-100 px-2 py-0.5 dark:bg-gray-800">@{{ record.category_name ?? 'N/A' }}</span>
                            <span class="rounded bg-gray-100 px-2 py-0.5 capitalize dark:bg-gray-800">@{{ record.type }}</span>
                            <span
                                class="rounded bg-blue-100 px-2 py-0.5 font-medium text-blue-700"
                                v-if="record.type === 'configurable' && Number(record.variants_count) > 0"
                            >
                                @{{ record.variants_count }} variantes
                            </span>
                            <p
                                class="ml-auto rounded-full px-2 py-0.5 text-xs font-medium"
                                :class="record.status ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-700 dark:bg-gray-800 dark:text-gray-300'"
                            >
                                @{{ record.status ? 'Activo' : 'Inactivo' }}
                            </p>
                        </div>

                        <div class="mt-2 grid grid-cols-3 overflow-hidden rounded-lg border border-gray-200 text-sm dark:border-gray-700">
                            <a
                                class="flex items-center justify-center gap-1 border-r border-gray-200 py-2 font-medium text-blue-600 transition hover:bg-blue-50 dark:border-gray-700"
                                :href="(record.actions || []).find(action => action.title === '@lang("admin::app.catalog.products.index.datagrid.edit")')?.url"
                                v-if="(record.actions || []).find(action => action.title === '@lang("admin::app.catalog.products.index.datagrid.edit")')"
                            >
                                <span class="icon-edit text-base"></span>
                                Editar
                            </a>

                            <a
                                class="flex items-center justify-center gap-1 border-r border-gray-200 py-2 font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200"
                                :href="(record.actions || []).find(action => action.title === '@lang("admin::app.catalog.products.index.datagrid.copy")')?.url"
                                v-if="(record.actions || []).find(action => action.title === '@lang("admin::app.catalog.products.index.datagrid.copy")')"
                            >
                                <span class="icon-copy text-base"></span>
                                Duplicar
                            </a>

                            <span
                                class="flex cursor-pointer items-center justify-center gap-1 py-2 font-medium text-red-600 transition hover:bg-red-50"
                                v-if="(record.actions || []).find(action => action.title === '@lang("admin::app.catalog.products.index.datagrid.delete")')"
                                @click="performAction((record.actions || []).find(action => action.title === '@lang("admin::app.catalog.products.index.datagrid.delete")'))"
                            >
                                <span class="icon-delete text-base"></span>
                                Eliminar
                            </span>
                        </div>
                    </div>
                </div>

                <div
                    class="row grid border-b px-4 py-4 text-center text-gray-600 dark:border-gray-800 dark:text-gray-300"
                    v-else
                >
                    <p>No hay productos para mostrar.</p>
                </div>
            </template>
        </template>
        </x-admin::datagrid>
        @else

        <x-admin::datagrid
            :src="route('admin.catalog.products.index')"
            :isMultiRow="true"
        />
        @endif

        {!! view_render_event('bagisto.admin.catalog.products.list.after') !!}
    </div>

    @pushOnce('scripts')
        <style>
            .products-cards-mode .table-responsive > .row {
                display: none;
            }

            .products-cards-mode .table-responsive {
                border: none;
                box-shadow: none;
                background: transparent;
                overflow: visible;
            }

            .products-table-mode .table-responsive .row {
                grid-template-columns: 40px 90px 180px 1.4fr 90px 110px 95px 150px 110px 90px !important;
            }

            .products-table-mode .table-responsive .row p,
            .products-table-mode .table-responsive .row a {
                font-size: 12px;
            }

            .products-table-mode .table-responsive .row .text-2xl {
                font-size: 22px !important;
            }

            .products-table-mode .table-responsive .row p {
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
        </style>

        <script
            type="text/x-template"
            id="v-create-product-form-template"
        >
            <div>
                <!-- Product Create Button -->
                @if (bouncer()->hasPermission('catalog.products.create'))
                    <button
                        type="button"
                        class="primary-button"
                        @click="$refs.productCreateModal.toggle()"
                    >
                        @lang('admin::app.catalog.products.index.create-btn')
                    </button>
                @endif

                <x-admin::form
                    v-slot="{ meta, errors, handleSubmit }"
                    as="div"
                >
                    <form @submit="handleSubmit($event, create)">
                        <!-- Customer Create Modal -->
                        <x-admin::modal ref="productCreateModal">
                            <!-- Modal Header -->
                            <x-slot:header>
                                <p
                                    class="text-lg font-bold text-gray-800 dark:text-white"
                                    v-if="! attributes.length"
                                >
                                    @lang('admin::app.catalog.products.index.create.title')
                                </p>

                                <p
                                    class="text-lg font-bold text-gray-800 dark:text-white"
                                    v-else
                                >
                                    @lang('admin::app.catalog.products.index.create.configurable-attributes')
                                </p>
                            </x-slot>

                            <!-- Modal Content -->
                            <x-slot:content>
                                <div v-show="! attributes.length">
                                    {!! view_render_event('bagisto.admin.catalog.products.create_form.general.controls.before') !!}

                                    <!-- Product Type -->
                                    <x-admin::form.control-group>
                                        <x-admin::form.control-group.label class="required">
                                            @lang('admin::app.catalog.products.index.create.type')
                                        </x-admin::form.control-group.label>

                                        <x-admin::form.control-group.control
                                            type="select"
                                            name="type"
                                            rules="required"
                                            :label="trans('admin::app.catalog.products.index.create.type')"
                                        >
                                            @foreach(config('product_types') as $key => $type)
                                                <option value="{{ $key }}">
                                                    @lang($type['name'])
                                                </option>
                                            @endforeach
                                        </x-admin::form.control-group.control>

                                        <x-admin::form.control-group.error control-name="type" />
                                    </x-admin::form.control-group>

                                    <!-- Attribute Family Id -->
                                    <x-admin::form.control-group>
                                        <x-admin::form.control-group.label class="required">
                                            @lang('admin::app.catalog.products.index.create.family')
                                        </x-admin::form.control-group.label>

                                        <x-admin::form.control-group.control
                                            type="select"
                                            name="attribute_family_id"
                                            rules="required"
                                            :label="trans('admin::app.catalog.products.index.create.family')"
                                        >
                                            @foreach($families as $family)
                                                <option value="{{ $family->id }}">
                                                    {{ $family->name }}
                                                </option>
                                            @endforeach
                                        </x-admin::form.control-group.control>

                                        <x-admin::form.control-group.error control-name="attribute_family_id" />
                                    </x-admin::form.control-group>

                                    <!-- SKU -->
                                    <x-admin::form.control-group>
                                        <x-admin::form.control-group.label class="required">
                                            @lang('admin::app.catalog.products.index.create.sku')
                                        </x-admin::form.control-group.label>

                                        <x-admin::form.control-group.control
                                            type="text"
                                            name="sku"
                                            ::rules="{ required: true, regex: /^[a-zA-Z0-9]+(?:-[a-zA-Z0-9]+)*$/ }"
                                            :label="trans('admin::app.catalog.products.index.create.sku')"
                                        />

                                        <x-admin::form.control-group.error control-name="sku" />
                                    </x-admin::form.control-group>

                                    {!! view_render_event('bagisto.admin.catalog.products.create_form.general.controls.before') !!}
                                </div>

                                <div v-show="attributes.length">
                                    {!! view_render_event('bagisto.admin.catalog.products.create_form.attributes.controls.before') !!}

                                    <div
                                        class="mb-2.5"
                                        v-for="attribute in attributes"
                                    >
                                        <label
                                            class="block text-xs font-medium leading-6 text-gray-800 dark:text-white"
                                            v-text="attribute.name"
                                        >
                                        </label>

                                        <div class="flex min-h-[38px] flex-wrap gap-1 rounded-md border p-1.5 dark:border-gray-800">
                                            <p
                                                class="flex items-center rounded bg-gray-600 px-2 py-1 font-semibold text-white"
                                                v-for="option in attribute.options"
                                            >
                                                @{{ option.name }}

                                                <span
                                                    class="icon-cross cursor-pointer text-lg text-white ltr:ml-1.5 rtl:mr-1.5"
                                                    @click="removeOption(option)"
                                                >
                                                </span>
                                            </p>
                                        </div>
                                    </div>

                                    {!! view_render_event('bagisto.admin.catalog.products.create_form.attributes.controls.before') !!}
                                </div>
                            </x-slot>

                            <!-- Modal Footer -->
                            <x-slot:footer>
                                <!-- Modal Submission -->
                                <div class="flex items-center gap-x-2.5">
                                    <button
                                        type="button"
                                        class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                                        v-if="attributes.length"
                                        @click="attributes = []"
                                    >
                                        @lang('admin::app.catalog.products.index.create.back-btn')
                                    </button>

                                    <button
                                        type="submit"
                                        class="primary-button"
                                    >
                                        @lang('admin::app.catalog.products.index.create.save-btn')
                                    </button>
                                </div>
                            </x-slot>
                        </x-admin::modal>
                    </form>
                </x-admin::form>
            </div>
        </script>

        <script type="module">
            app.component('v-create-product-form', {
                template: '#v-create-product-form-template',

                data() {
                    return {
                        attributes: [],

                        superAttributes: {}
                    };
                },

                methods: {
                    create(params, { resetForm, resetField, setErrors }) {
                        this.attributes.forEach(attribute => {
                            params.super_attributes ||= {};

                            params.super_attributes[attribute.code] = this.superAttributes[attribute.code];
                        });

                        this.$axios.post("{{ route('admin.catalog.products.store') }}", params)
                            .then((response) => {
                                if (response.data.data.redirect_url) {
                                    window.location.href = response.data.data.redirect_url;
                                } else {
                                    this.attributes = response.data.data.attributes;

                                    this.setSuperAttributes();
                                }
                            })
                            .catch(error => {
                                if (error.response.status == 422) {
                                    setErrors(error.response.data.errors);
                                }
                            });
                    },

                    removeOption(option) {
                        this.attributes.forEach(attribute => {
                            attribute.options = attribute.options.filter(item => item.id != option.id);
                        });

                        this.attributes = this.attributes.filter(attribute => attribute.options.length > 0);

                        this.setSuperAttributes();
                    },

                    setSuperAttributes() {
                        this.superAttributes = {};

                        this.attributes.forEach(attribute => {
                            this.superAttributes[attribute.code] = [];

                            attribute.options.forEach(option => {
                                this.superAttributes[attribute.code].push(option.id);
                            });
                        });
                    }
                }
            })
        </script>
    @endPushOnce
</x-admin::layouts>
