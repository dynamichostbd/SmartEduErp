<template>
    <div class="flex flex-col gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">{{ pageHeading }}</div>
                    <div class="mt-1 text-xs text-slate-500">Slug: {{ slug }}</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="saving" @click="goView">
                        View
                    </button>
                    <button type="button" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700" :disabled="saving" @click="submit">
                        {{ saving ? '...' : (hasExisting ? 'Update' : 'Create') }}
                    </button>
                </div>
            </div>
        </div>

        <div v-if="error" class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ error }}</div>
        <div v-if="success" class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ success }}</div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
            <div class="lg:col-span-9">
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                        <div class="lg:col-span-9">
                            <div class="text-xs font-semibold text-slate-600">Title</div>
                            <input v-model="form.title" type="text" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>
                        <div class="lg:col-span-3">
                            <div class="text-xs font-semibold text-slate-600">Image</div>
                            <img v-if="imagePreview" :src="imagePreview" class="mt-2 h-16 w-full rounded border border-slate-200 bg-white object-contain" />
                            <div v-else class="mt-2 flex h-16 items-center justify-center rounded border border-dashed border-slate-200 bg-slate-50 text-xs text-slate-500">No image</div>
                            <input type="file" accept="image/*" class="mt-2 block w-full text-sm" @change="onImage" />
                        </div>

                        <div class="lg:col-span-12">
                            <div class="text-xs font-semibold text-slate-600">Description (HTML allowed)</div>
                            <textarea v-model="form.description" rows="10" class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"></textarea>
                        </div>
                    </div>
                </div>

                <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-5">
                    <div class="text-sm font-semibold text-slate-900">Meta</div>
                    <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-12">
                        <div class="lg:col-span-6">
                            <div class="text-xs font-semibold text-slate-600">Meta Title</div>
                            <input v-model="form.meta_title" type="text" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>
                        <div class="lg:col-span-6">
                            <div class="text-xs font-semibold text-slate-600">Meta Description</div>
                            <input v-model="form.meta_description" type="text" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-3">
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <div class="text-sm font-semibold text-slate-900">Status</div>
                    <select v-model="form.status" class="mt-3 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="draft">Draft</option>
                        <option value="active">Active</option>
                        <option value="deactive">Deactive</option>
                    </select>

                    <div class="mt-4 flex flex-col gap-2">
                        <button type="button" class="h-10 rounded-lg bg-slate-900 px-4 text-sm font-semibold text-white hover:bg-slate-800" :disabled="saving" @click="submit">
                            {{ saving ? '...' : (hasExisting ? 'Update' : 'Create') }}
                        </button>
                        <button type="button" class="h-10 rounded-lg border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="saving" @click="goView">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'ContentCreate',
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
            saving: false,
            error: '',
            success: '',
            hasExisting: false,
            imageFile: null,
            imagePreview: '',
            form: {
                title: '',
                description: '',
                status: 'active',
                meta_title: '',
                meta_description: '',
            },
        }
    },
    computed: {
        pageHeading() {
            return this.hasExisting ? 'Update Content' : 'Create Content'
        },
    },
    created() {
        this.load()
    },
    methods: {
        toast(message, type = 'success') {
            window.dispatchEvent(new CustomEvent('app-toast', { detail: { message, type } }))
        },
        go(path) {
            window.history.pushState({}, '', path)
            window.dispatchEvent(new PopStateEvent('popstate'))
        },
        goView() {
            this.go(`/admin/content/${encodeURIComponent(this.slug)}`)
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
        onImage(e) {
            const f = e?.target?.files?.[0] || null
            if (!f) return
            this.imageFile = f
            this.imagePreview = URL.createObjectURL(f)
        },
        async load() {
            this.loading = true
            this.error = ''
            try {
                const res = await window.axios.get(`/admin/content/${encodeURIComponent(this.slug)}`)
                const data = res?.data || {}
                this.hasExisting = !!(data && data.id)

                this.form.title = data?.title || ''
                this.form.description = data?.description || ''
                this.form.status = data?.status || 'active'

                const meta = data?.meta && typeof data.meta === 'object' ? data.meta : {}
                this.form.meta_title = meta?.title || ''
                this.form.meta_description = meta?.description || ''

                const img = data?.image || ''
                this.imagePreview = img ? this.resolveImage(img) : ''
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load content.'
            } finally {
                this.loading = false
            }
        },
        validate() {
            if (!this.slug) return 'Slug is missing.'
            return ''
        },
        async submit() {
            this.error = ''
            this.success = ''

            const msg = this.validate()
            if (msg) {
                this.error = msg
                return
            }

            this.saving = true
            try {
                const fd = new FormData()
                fd.append('slug', String(this.slug))
                fd.append('title', String(this.form.title || ''))
                fd.append('description', String(this.form.description || ''))
                fd.append('status', String(this.form.status || 'active'))
                fd.append('is_meta', '1')
                fd.append('meta[title]', String(this.form.meta_title || ''))
                fd.append('meta[description]', String(this.form.meta_description || ''))
                if (this.imageFile) {
                    fd.append('image', this.imageFile)
                }

                const res = await window.axios.post('/admin/content', fd, {
                    headers: { 'Content-Type': 'multipart/form-data' },
                })

                const m = res?.data?.message || (this.hasExisting ? 'Update Successfully!' : 'Create Successfully!')
                this.success = m
                this.toast(m, 'success')
                this.goView()
            } catch (e) {
                this.error = e?.response?.data?.message || 'Submit failed.'
            } finally {
                this.saving = false
            }
        },
    },
}
</script>
