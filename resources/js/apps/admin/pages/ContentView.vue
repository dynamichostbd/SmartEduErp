<template>
    <div class="flex flex-col gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Content</div>
                    <div class="mt-1 text-xs text-slate-500">Slug: {{ slug }}</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="reload">Reload</button>
                    <button type="button" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700" @click="goEdit">{{ data?.id ? 'Edit' : 'Create' }}</button>
                </div>
            </div>
        </div>

        <div v-if="error" class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ error }}</div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="text-lg font-semibold text-slate-900">{{ data.title || '—' }}</div>

            <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-12">
                <div class="lg:col-span-9">
                    <div class="prose max-w-none text-sm" v-html="data.description || ''"></div>
                </div>
                <div class="lg:col-span-3">
                    <img v-if="data.image" :src="resolveImage(data.image)" class="h-40 w-full rounded border border-slate-200 bg-white object-contain" />
                    <div v-else class="flex h-40 items-center justify-center rounded border border-dashed border-slate-200 bg-slate-50 text-xs text-slate-500">No image</div>

                    <div class="mt-3 rounded-xl border border-slate-200 p-3">
                        <div class="text-xs font-semibold text-slate-600">Status</div>
                        <div class="mt-1 text-sm text-slate-800">{{ data.status || '—' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="contentFiles.length" class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="mb-3 text-lg font-semibold text-slate-900">Content Files</div>

            <div class="overflow-x-auto rounded-xl border border-slate-200">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">#</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">Title</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-700">File</th>
                            <th class="px-3 py-2 text-right font-semibold text-slate-700">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <tr v-for="(f, idx) in contentFiles" :key="'cf-' + (f.id || idx)">
                            <td class="px-3 py-2">{{ idx + 1 }}</td>
                            <td class="px-3 py-2">{{ f.title || '—' }}</td>
                            <td class="px-3 py-2">
                                <a v-if="f.file" class="text-emerald-700 hover:underline" :href="resolveFile(f.file)" target="_blank">View</a>
                                <span v-else>—</span>
                            </td>
                            <td class="px-3 py-2 text-right">
                                <button type="button" class="rounded-lg border border-red-200 bg-white px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-50" @click="removeFile(f)">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'ContentView',
    props: {
        systems: {
            type: Object,
            default: () => ({}),
        },
        slug: {
            type: String,
            default: '',
        },
    },
    data() {
        return {
            loading: false,
            error: '',
            data: {
                title: '',
                description: '',
                image: null,
                status: 'active',
                content_files: [],
            },
        }
    },
    computed: {
        contentFiles() {
            const a = this.data?.content_files
            const b = this.data?.contentFiles
            if (Array.isArray(a)) return a
            if (Array.isArray(b)) return b
            return []
        },
    },
    created() {
        this.reload()
    },
    methods: {
        toast(message, type = 'success') {
            window.dispatchEvent(new CustomEvent('app-toast', { detail: { message, type } }))
        },
        go(path) {
            window.history.pushState({}, '', path)
            window.dispatchEvent(new PopStateEvent('popstate'))
        },
        goEdit() {
            this.go(`/admin/content/${encodeURIComponent(this.slug)}/create`)
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
            this.loading = true
            this.error = ''
            try {
                const res = await window.axios.get(`/admin/content/${encodeURIComponent(this.slug)}`)
                this.data = res?.data || {}
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load content.'
            } finally {
                this.loading = false
            }
        },
        async removeFile(f) {
            const id = Number(f?.id || 0)
            if (!id) return

            try {
                const res = await window.axios.delete(`/admin/content/${id}`)
                const m = res?.data?.message || 'Delete Successfully!'
                this.toast(m, 'success')
                await this.reload()
            } catch (e) {
                this.toast(e?.response?.data?.message || 'Delete failed.', 'error')
            }
        },
    },
}
</script>
