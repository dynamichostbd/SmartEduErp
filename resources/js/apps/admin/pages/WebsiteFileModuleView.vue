<template>
    <div class="flex flex-col gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">{{ heading }}</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="goIndex">Back</button>
                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="reload">Reload</button>
                    <button type="button" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700" @click="goEdit">Edit</button>
                </div>
            </div>
        </div>

        <div v-if="error" class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ error }}</div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="text-lg font-semibold text-slate-900">{{ data.title || '—' }}</div>

            <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-12">
                <div class="lg:col-span-8">
                    <div class="rounded-xl border border-slate-200 p-4">
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <div class="text-xs font-semibold text-slate-600">File Type</div>
                                <div class="mt-1 text-sm text-slate-800">{{ data.file_type || '—' }}</div>
                            </div>
                            <div>
                                <div class="text-xs font-semibold text-slate-600">Status</div>
                                <div class="mt-1 text-sm text-slate-800">{{ data.status || '—' }}</div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <div class="text-xs font-semibold text-slate-600">File</div>
                            <div class="mt-1">
                                <a v-if="data.file" class="text-emerald-700 hover:underline" :href="resolveFile(data.file)" target="_blank">View / Download</a>
                                <span v-else class="text-sm text-slate-500">—</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-4">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                        <div class="text-xs font-semibold text-slate-600">Preview</div>
                        <div class="mt-2">
                            <img v-if="String(data.file_type || '') === 'Image' && data.file" :src="resolveFile(data.file)" class="h-56 w-full rounded border border-slate-200 bg-white object-contain" />
                            <div v-else class="flex h-56 items-center justify-center rounded border border-dashed border-slate-200 bg-white text-xs text-slate-500">No preview</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'WebsiteFileModuleView',
    props: {
        systems: {
            type: Object,
            default: () => ({}),
        },
        module: {
            type: String,
            default: '',
        },
        id: {
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
    computed: {
        heading() {
            const map = {
                slider: 'Slider',
                videoSlider: 'Video Slider',
                bus: 'Bus',
                calender: 'Calender',
                class: 'Class Routine',
                examR: 'Exam Routine',
            }
            const base = map[this.module] || 'Website'
            return `${base} Details`
        },
    },
    watch: {
        id: {
            handler() {
                this.reload()
            },
            immediate: true,
        },
        module: {
            handler() {
                this.reload()
            },
        },
    },
    methods: {
        go(path) {
            window.history.pushState({}, '', path)
            window.dispatchEvent(new PopStateEvent('popstate'))
        },
        goIndex() {
            this.go(`/admin/${this.module}`)
        },
        goEdit() {
            this.go(`/admin/${this.module}/${this.id}/edit`)
        },
        resolveFile(v) {
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
            if (!this.module || !this.id) return
            this.loading = true
            this.error = ''
            try {
                const res = await window.axios.get(`/admin/${this.module}/${this.id}`)
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
