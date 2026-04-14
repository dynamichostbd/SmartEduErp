<template>
    <div class="flex flex-col gap-4">
        <div class=" border border-slate-300 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Subject Assign Details</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-sm border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="loading" @click="goIndex">Back</button>
                    <button type="button" class="rounded-sm bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800" :disabled="loading || !subjectAssignId" @click="goEdit">Edit</button>
                </div>
            </div>
        </div>

        <div v-if="error" class=" border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ error }}</div>

        <div class=" border border-slate-300 bg-white p-5">
            <div v-if="loading" class="text-sm text-slate-600">Loading...</div>
            <div v-else>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="rounded-xl border border-slate-300 p-4">
                        <div class="text-xs font-semibold text-slate-500">Academic Qualification Id</div>
                        <div class="mt-1 text-sm font-semibold text-slate-900">{{ row.academic_qualification_id ?? '—' }}</div>
                    </div>
                    <div class="rounded-xl border border-slate-300 p-4">
                        <div class="text-xs font-semibold text-slate-500">Department Id</div>
                        <div class="mt-1 text-sm font-semibold text-slate-900">{{ row.department_id ?? '—' }}</div>
                    </div>
                    <div class="rounded-xl border border-slate-300 p-4">
                        <div class="text-xs font-semibold text-slate-500">Academic Class Id</div>
                        <div class="mt-1 text-sm font-semibold text-slate-900">{{ row.academic_class_id ?? '—' }}</div>
                    </div>
                    <div class="rounded-xl border border-slate-300 p-4">
                        <div class="text-xs font-semibold text-slate-500">Main Subject</div>
                        <div class="mt-1 text-sm font-semibold text-slate-900">{{ row.main_subject ?? '—' }}</div>
                    </div>
                    <div class="rounded-xl border border-slate-300 p-4 sm:col-span-2">
                        <div class="text-xs font-semibold text-slate-500">Note</div>
                        <div class="mt-1 whitespace-pre-wrap text-sm font-semibold text-slate-900">{{ row.note || '—' }}</div>
                    </div>
                </div>

                <div class="mt-6">
                    <div class="text-lg font-semibold text-slate-900">Details</div>
                    <div class="mt-3">
                        <table class="w-full table-fixed border-collapse border border-slate-300">
                            <thead class="bg-emerald-100">
                                <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-700">
                                    <th class="w-[260px] border border-slate-300 px-3 py-2">Subject</th>
                                    <th class="w-[70px] border border-slate-300 px-3 py-2 text-center">4th</th>
                                    <th class="w-[70px] border border-slate-300 px-3 py-2 text-center">Main</th>
                                    <th class="w-[60px] border border-slate-300 px-3 py-2 text-center">CT M.</th>
                                    <th class="w-[60px] border border-slate-300 px-3 py-2 text-center">CT Pass</th>
                                    <th class="w-[60px] border border-slate-300 px-3 py-2 text-center">CQ M.</th>
                                    <th class="w-[60px] border border-slate-300 px-3 py-2 text-center">CQ Pass</th>
                                    <th class="w-[60px] border border-slate-300 px-3 py-2 text-center">MCQ M.</th>
                                    <th class="w-[60px] border border-slate-300 px-3 py-2 text-center">MCQ Pass</th>
                                    <th class="w-[60px] border border-slate-300 px-3 py-2 text-center">Prac. M.</th>
                                    <th class="w-[60px] border border-slate-300 px-3 py-2 text-center">Prac. Pass</th>
                                    <th class="w-[60px] border border-slate-300 px-3 py-2 text-center">Total</th>
                                    <th class="w-[60px] border border-slate-300 px-3 py-2 text-center">Sorting</th>
                                    <th class="w-[60px] border border-slate-300 px-3 py-2 text-center">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="details.length === 0" class="text-sm text-slate-600">
                                    <td class="border border-slate-300 px-4 py-6" colspan="14">No details.</td>
                                </tr>
                                <tr v-for="(d, idx) in details" v-else :key="'dd-' + idx" class="text-sm text-slate-800" :class="idx % 2 === 0 ? 'bg-white' : 'bg-slate-50'">
                                    <td class="border border-slate-300 px-3 py-2">
                                        {{ subjectName(d.subject_id) }}
                                        <div v-if="d.except_subject_id" class="mt-1 text-xs text-slate-500">Except: {{ subjectName(d.except_subject_id) }}</div>
                                    </td>
                                    <td class="border border-slate-300 px-3 py-2 text-center">{{ Number(d.fourth_subject || 0) === 1 ? 'Yes' : 'No' }}</td>
                                    <td class="border border-slate-300 px-3 py-2 text-center">{{ Number(d.main_subject || 0) === 1 ? 'Yes' : 'No' }}</td>
                                    <td class="border border-slate-300 px-3 py-2 text-center">{{ d.ct_mark ?? 0 }}</td>
                                    <td class="border border-slate-300 px-3 py-2 text-center">{{ d.ct_pass_mark ?? 0 }}</td>
                                    <td class="border border-slate-300 px-3 py-2 text-center">{{ d.cq_mark ?? 0 }}</td>
                                    <td class="border border-slate-300 px-3 py-2 text-center">{{ d.cq_pass_mark ?? 0 }}</td>
                                    <td class="border border-slate-300 px-3 py-2 text-center">{{ d.mcq_mark ?? 0 }}</td>
                                    <td class="border border-slate-300 px-3 py-2 text-center">{{ d.mcq_pass_mark ?? 0 }}</td>
                                    <td class="border border-slate-300 px-3 py-2 text-center">{{ d.practical_mark ?? 0 }}</td>
                                    <td class="border border-slate-300 px-3 py-2 text-center">{{ d.practical_pass_mark ?? 0 }}</td>
                                    <td class="border border-slate-300 px-3 py-2 text-center">{{ d.total_mark ?? 0 }}</td>
                                    <td class="border border-slate-300 px-3 py-2 text-center">{{ d.sorting ?? 0 }}</td>
                                    <td class="border border-slate-300 px-3 py-2 text-center">{{ d.amount ?? 0 }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'SubjectAssignView',
    props: {
        subjectAssignId: {
            type: [Number, String],
            default: null,
        },
    },
    data() {
        return {
            loading: false,
            error: '',
            row: {},
            details: [],
            subjectsById: {},
        }
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
            this.go('/admin/subjectAssign')
        },
        goEdit() {
            this.go(`/admin/subjectAssign/${this.subjectAssignId}/edit`)
        },
        subjectName(id) {
            const s = this.subjectsById[String(id)]
            return s?.name_bn || s?.name_en || s?.name || id || '—'
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
            if (!this.subjectAssignId) return
            this.loading = true
            this.error = ''
            try {
                const res = await window.axios.get(`/admin/subjectAssign/${this.subjectAssignId}`)
                this.row = res?.data?.subject_assign || {}
                this.details = Array.isArray(res?.data?.details) ? res.data.details : []
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load subject assign.'
            } finally {
                this.loading = false
            }
        },
    },
}
</script>
