<template>
    <form class="flex flex-col gap-4" @submit.prevent="submit">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">{{ heading }}</div>
                </div>
            </div>
        </div>

        <div v-if="error" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ error }}</div>
        <div v-if="success" class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ success }}</div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
            <div class="flex flex-col gap-4 lg:col-span-9">
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="text-sm font-semibold text-slate-900">Details</div>

                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div class="sm:col-span-2 lg:col-span-3">
                            <div class="text-xs font-semibold text-slate-600">Title</div>
                            <input
                                v-model="form.title"
                                type="text"
                                class="mt-1 h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                            />
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">File Type</div>
                            <select
                                v-model="form.file_type"
                                class="mt-1 h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                            >
                                <option value="Image">Image</option>
                                <option value="PDF">PDF</option>
                            </select>
                        </div>

                        <div class="sm:col-span-2">
                            <div class="text-xs font-semibold text-slate-600">File</div>
                            <input
                                type="file"
                                accept=".png,.jpg,.jpeg,.pdf"
                                class="mt-1 block h-9 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm"
                                @change="onFile"
                            />
                        </div>

                        <div class="sm:col-span-2 lg:col-span-3">
                            <div class="mt-2 rounded-xl border border-slate-200 bg-slate-50 p-3">
                                <div class="text-xs font-semibold text-slate-600">Preview</div>
                                <div class="mt-2">
                                    <img v-if="form.file_type === 'Image' && previewUrl" :src="previewUrl" class="h-48 w-full rounded-lg border border-slate-200 bg-white object-contain" />
                                    <img v-else-if="form.file_type === 'Image' && form.existing_file" :src="resolveFile(form.existing_file)" class="h-48 w-full rounded-lg border border-slate-200 bg-white object-contain" />

                                    <a v-else-if="form.file_type === 'PDF' && form.existing_file" :href="resolveFile(form.existing_file)" class="text-emerald-700 hover:underline" target="_blank">View PDF</a>
                                    <div v-else class="text-xs text-slate-500">No preview</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-4 lg:col-span-3">
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="text-sm font-semibold text-slate-900">Status</div>
                    <select
                        v-model="form.status"
                        class="mt-3 h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                    >
                        <option value="active">Active</option>
                        <option value="deactive">Deactive</option>
                        <option value="draft">Draft</option>
                    </select>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="text-sm font-semibold text-slate-900">Actions</div>

                    <div class="mt-4 flex flex-col gap-2">
                        <button type="button" class="h-9 w-full rounded-lg border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="saving" @click="goIndex">
                            Back
                        </button>

                        <button type="submit" class="h-9 w-full rounded-lg bg-emerald-600 px-4 text-sm font-semibold text-white hover:bg-emerald-700" :disabled="saving">
                            {{ saving ? '...' : (isEdit ? 'Update' : 'Create') }}
                        </button>

                        <button type="button" class="h-9 w-full rounded-lg border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="saving" @click="goIndex">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</template>

<script>
export default {
    name: 'WebsiteFileModuleManage',
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
            saving: false,
            error: '',
            success: '',
            file: null,
            previewUrl: '',
            form: {
                title: '',
                file_type: 'Image',
                status: 'active',
                existing_file: '',
            },
        }
    },
    computed: {
        isEdit() {
            return !!this.id
        },
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
            return this.isEdit ? `Edit ${base}` : `Create ${base}`
        },
    },
    watch: {
        id: {
            handler() {
                if (this.isEdit) {
                    this.load()
                }
            },
            immediate: true,
        },
        module: {
            handler() {
                if (this.isEdit) {
                    this.load()
                }
            },
        },
    },
    methods: {
        toast(message, type = 'success') {
            window.dispatchEvent(new CustomEvent('app-toast', { detail: { message, type } }))
        },
        go(path) {
            window.history.pushState({}, '', path)
            window.dispatchEvent(new PopStateEvent('popstate'))
        },
        goIndex() {
            this.go(`/admin/${this.module}`)
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
        onFile(e) {
            const f = e?.target?.files?.[0] || null
            this.file = f
            if (this.previewUrl) {
                try {
                    URL.revokeObjectURL(this.previewUrl)
                } catch {
                }
            }
            this.previewUrl = f ? URL.createObjectURL(f) : ''
        },
        async load() {
            if (!this.module || !this.id) return
            this.loading = true
            this.error = ''
            try {
                const res = await window.axios.get(`/admin/${this.module}/${this.id}`)
                const r = res?.data || {}
                this.form.title = r?.title || ''
                this.form.file_type = r?.file_type || 'Image'
                this.form.status = r?.status || 'active'
                this.form.existing_file = r?.file || ''
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load.'
            } finally {
                this.loading = false
            }
        },
        async submit() {
            if (!this.module) return

            this.saving = true
            this.error = ''
            this.success = ''

            try {
                const fd = new FormData()
                fd.append('title', String(this.form.title || ''))
                fd.append('file_type', String(this.form.file_type || ''))
                fd.append('status', String(this.form.status || 'active'))
                if (this.file) {
                    fd.append('file', this.file)
                } else if (this.form.existing_file) {
                    fd.append('existing_file', String(this.form.existing_file))
                }

                let res
                if (this.isEdit) {
                    res = await window.axios.post(`/admin/${this.module}/${this.id}?_method=PUT`, fd, { headers: { 'Content-Type': 'multipart/form-data' } })
                } else {
                    res = await window.axios.post(`/admin/${this.module}`, fd, { headers: { 'Content-Type': 'multipart/form-data' } })
                }

                const m = res?.data?.message || (this.isEdit ? 'Update Successfully!' : 'Create Successfully!')
                this.success = m
                this.toast(m, 'success')

                if (!this.isEdit) {
                    this.goIndex()
                }
            } catch (e) {
                this.error = e?.response?.data?.message || 'Submit failed.'
            } finally {
                this.saving = false
            }
        },
    },
}
</script>
