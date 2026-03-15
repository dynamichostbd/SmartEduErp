<template>
    <div class="flex flex-col gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Subject Cluster Details</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="loading" @click="goIndex">Back</button>
                    <button type="button" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800" :disabled="loading || !subjectClusterId" @click="goEdit">Edit</button>
                </div>
            </div>
        </div>

        <div v-if="error" class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ error }}</div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div v-if="loading" class="text-sm text-slate-600">Loading...</div>
            <div v-else class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div class="rounded-xl border border-slate-200 p-4">
                    <div class="text-xs font-semibold text-slate-500">Academic Qualification Id</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">{{ row.academic_qualification_id ?? '—' }}</div>
                </div>
                <div class="rounded-xl border border-slate-200 p-4">
                    <div class="text-xs font-semibold text-slate-500">Department Id</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">{{ row.department_id ?? '—' }}</div>
                </div>
                <div class="rounded-xl border border-slate-200 p-4">
                    <div class="text-xs font-semibold text-slate-500">Name (en)</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">{{ row.name_en || '—' }}</div>
                </div>
                <div class="rounded-xl border border-slate-200 p-4">
                    <div class="text-xs font-semibold text-slate-500">Name (bn)</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">{{ row.name_bn || '—' }}</div>
                </div>
                <div class="rounded-xl border border-slate-200 p-4 sm:col-span-2">
                    <div class="text-xs font-semibold text-slate-500">Subjects</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">{{ subjectsText || '—' }}</div>
                </div>
                <div class="rounded-xl border border-slate-200 p-4">
                    <div class="text-xs font-semibold text-slate-500">Min. Select</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">{{ row.minimum_select ?? '—' }}</div>
                </div>
                <div class="rounded-xl border border-slate-200 p-4">
                    <div class="text-xs font-semibold text-slate-500">Fixed Subject</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">{{ Number(row.fixed_subject || 0) === 1 ? 'Yes' : 'No' }}</div>
                </div>
                <div class="rounded-xl border border-slate-200 p-4">
                    <div class="text-xs font-semibold text-slate-500">Status</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">{{ row.status || '—' }}</div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'SubjectClusterView',
    props: {
        subjectClusterId: {
            type: [Number, String],
            default: null,
        },
    },
    data() {
        return {
            loading: false,
            error: '',
            row: {},
            subjectsById: {},
        }
    },
    computed: {
        subjectsText() {
            const ids = this.parseSubjectsJson(this.row?.subjects_json)
            if (!ids.length) return ''
            const names = ids
                .map((id) => {
                    const s = this.subjectsById[String(id)]
                    return s?.name_bn || s?.name_en || s?.name || null
                })
                .filter(Boolean)
            return names.join(', ')
        },
    },
    async created() {
        await this.loadSubjects()
        await this.load()
    },
    methods: {
        go(path) {
            window.history.pushState({}, '', path)
            window.dispatchEvent(new PopStateEvent('popstate'))
        },
        goIndex() {
            this.go('/admin/subjectCluster')
        },
        goEdit() {
            this.go(`/admin/subjectCluster/${this.subjectClusterId}/edit`)
        },
        parseSubjectsJson(v) {
            if (!v) return []
            if (Array.isArray(v)) return v
            if (typeof v === 'string') {
                try {
                    const out = JSON.parse(v)
                    return Array.isArray(out) ? out : []
                } catch {
                    return []
                }
            }
            return []
        },
        async loadSubjects() {
            try {
                const res = await window.axios.get('/admin/subject', { params: { allData: true } })
                const list = Array.isArray(res?.data) ? res.data : []
                const map = {}
                for (const s of list) {
                    map[String(s.id)] = s
                }
                this.subjectsById = map
            } catch {
                this.subjectsById = {}
            }
        },
        async load() {
            if (!this.subjectClusterId) return
            this.loading = true
            this.error = ''
            try {
                const res = await window.axios.get(`/admin/subjectCluster/${this.subjectClusterId}`)
                this.row = res?.data?.subject_cluster || res?.data || {}
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load subject cluster.'
            } finally {
                this.loading = false
            }
        },
    },
}
</script>
