<template>
    <div class="flex flex-col gap-4">
        <div class=" border border-slate-300 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Popup Details</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-sm border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="goIndex">Back</button>
                    <button type="button" class="rounded-sm border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="reload">Reload</button>
                    <button type="button" class="rounded-sm bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700" @click="goEdit">Edit</button>
                </div>
            </div>
        </div>

        <div v-if="error" class=" border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ error }}</div>

        <div class=" border border-slate-300 bg-white p-5">
            <div class="text-lg font-semibold text-slate-900">{{ data.title || '—' }}</div>
            <div class="mt-1 text-xs text-slate-500">Type: {{ data.type || '—' }} | Status: {{ data.status || '—' }}</div>

            <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-12">
                <div class="lg:col-span-8">
                    <div class="prose max-w-none text-sm" v-html="data.description || ''"></div>
                </div>
                <div class="lg:col-span-4">
                    <img v-if="data.image" :src="resolveImage(data.image)" class="h-56 w-full rounded border border-slate-300 bg-white object-contain" />
                    <div v-else class="flex h-56 items-center justify-center rounded border border-dashed border-slate-300 bg-slate-50 text-xs text-slate-500">No image</div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'PopupView',
    props: {
        systems: {
            type: Object,
            default: () => ({}),
        },
        popupId: {
            type: [Number, String],
            default: null,
        },
    },
    data() {
        return {
            loading: false,
            error: '',
            data: {},
        }
    },
    created() {
        this.reload()
    },
    methods: {
        go(path) {
            window.history.pushState({}, '', path)
            window.dispatchEvent(new PopStateEvent('popstate'))
        },
        goIndex() {
            this.go('/admin/popup')
        },
        goEdit() {
            this.go(`/admin/popup/${this.popupId}/edit`)
        },
        resolveImage(v) {
            if (!v) return ''
            if (String(v).match(/^https?:\/\//i)) return v
            const path = String(v)
                .replace(/^storage\//i, '')
                .replace(/^upload\//i, '')
                .replace(/^public\//i, '')
                .replace(/^\//, '')
            return `/storage/upload/${path}`
        },
        async reload() {
            if (!this.popupId) return
            this.loading = true
            this.error = ''
            try {
                const res = await window.axios.get(`/admin/popup/${this.popupId}`)
                this.data = res?.data || {}
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load.'
            } finally {
                this.loading = false
            }
        },
    },
}
</script>
