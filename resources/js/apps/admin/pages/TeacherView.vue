<template>
    <div class="flex flex-col gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Teacher Details</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="loading" @click="goIndex">Back</button>
                    <button type="button" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800" :disabled="loading || !teacherId" @click="goEdit">Edit</button>
                </div>
            </div>
        </div>

        <div v-if="error" class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ error }}</div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div v-if="loading" class="text-sm text-slate-600">Loading...</div>
            <div v-else class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div class="rounded-xl border border-slate-200 p-4 sm:col-span-2">
                    <div class="flex items-center gap-3">
                        <img :src="row.profile || fallback" class="h-16 w-16 rounded-full border border-slate-200 object-cover" alt="profile" />
                        <div class="min-w-0">
                            <div class="truncate text-base font-semibold text-slate-900">{{ row.name || '—' }}</div>
                            <div class="truncate text-sm text-slate-600">{{ row.email || '—' }}</div>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 p-4">
                    <div class="text-xs font-semibold text-slate-500">Mobile</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">{{ row.mobile || '—' }}</div>
                </div>
                <div class="rounded-xl border border-slate-200 p-4">
                    <div class="text-xs font-semibold text-slate-500">Department</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">{{ row.department_name || '—' }}</div>
                </div>
                <div class="rounded-xl border border-slate-200 p-4">
                    <div class="text-xs font-semibold text-slate-500">Status</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">{{ row.status || '—' }}</div>
                </div>
                <div class="rounded-xl border border-slate-200 p-4">
                    <div class="text-xs font-semibold text-slate-500">Emergency Contacts</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">{{ row.emergency_contacts || '—' }}</div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'TeacherView',
    props: {
        teacherId: {
            type: [Number, String],
            default: null,
        },
    },
    data() {
        return {
            loading: false,
            error: '',
            row: {},
            fallback: window.laravel?.userimage || '',
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
            this.go('/admin/teacher')
        },
        goEdit() {
            this.go(`/admin/teacher/${this.teacherId}/edit`)
        },
        async load() {
            if (!this.teacherId) return
            this.loading = true
            this.error = ''
            try {
                const res = await window.axios.get(`/admin/teacher/${this.teacherId}`)
                this.row = res?.data?.teacher || {}
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load teacher.'
            } finally {
                this.loading = false
            }
        },
    },
}
</script>
