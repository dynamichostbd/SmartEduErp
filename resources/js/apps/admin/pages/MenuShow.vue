<template>
    <div class="flex flex-col gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Menu View</div>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="goIndex">Back</button>
                    <button type="button" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800" @click="goEdit">Edit</button>
                </div>
            </div>
        </div>

        <div v-if="error" class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ error }}</div>

        <div v-if="menu" class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                <div class="lg:col-span-6">
                    <div class="text-xs font-semibold text-slate-600">Menu Name</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">{{ menu.menu_name || '' }}</div>
                </div>
                <div class="lg:col-span-6">
                    <div class="text-xs font-semibold text-slate-600">Parent</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">{{ menu.parent_id || '' }}</div>
                </div>
                <div class="lg:col-span-6">
                    <div class="text-xs font-semibold text-slate-600">Route Name</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">{{ menu.route_name || '' }}</div>
                </div>
                <div class="lg:col-span-6">
                    <div class="text-xs font-semibold text-slate-600">Sorting</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">{{ menu.sorting ?? '' }}</div>
                </div>
                <div class="lg:col-span-12">
                    <div class="text-xs font-semibold text-slate-600">Icon</div>
                    <div class="mt-1 rounded-lg border border-slate-200 bg-white p-3 text-sm text-slate-900" v-html="menu.icon"></div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'MenuShow',
    props: {
        systems: {
            type: Object,
            default: () => ({}),
        },
        menuId: {
            type: [Number, String],
            default: null,
        },
    },
    data() {
        return {
            error: '',
            menu: null,
        }
    },
    created() {
        this.load()
    },
    methods: {
        go(path) {
            window.history.pushState({}, '', path)
            window.dispatchEvent(new PopStateEvent('popstate'))
        },
        goIndex() {
            this.go('/admin/menu')
        },
        goEdit() {
            this.go(`/admin/menu/${this.menuId}/edit`)
        },
        async load() {
            this.error = ''
            try {
                const res = await window.axios.get(`/admin/menu/${this.menuId}`)
                this.menu = res?.data || null
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load menu.'
            }
        },
    },
}
</script>
