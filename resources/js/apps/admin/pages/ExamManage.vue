<template>
    <div class="flex flex-col gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">{{ isEdit ? 'Edit Exam' : 'Create Exam' }}</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="saving" @click="goIndex">Back</button>
                    <button type="button" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700" :disabled="saving" @click="submit">{{ saving ? '...' : isEdit ? 'Update' : 'Create' }}</button>
                </div>
            </div>
        </div>

        <div v-if="error" class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ error }}</div>
        <div v-if="success" class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ success }}</div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
            <div class="lg:col-span-9">
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                        <div class="lg:col-span-12">
                            <div class="text-xs font-semibold text-slate-600">Name</div>
                            <input v-model="form.name" type="text" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>

                        <div class="lg:col-span-12">
                            <div class="text-xs font-semibold text-slate-600">Exam Type</div>
                            <div class="mt-2 flex flex-wrap gap-4 text-sm text-slate-700">
                                <label class="inline-flex items-center gap-2">
                                    <input type="radio" v-model="form.exam_type" value="term" />
                                    <span>Term Exam</span>
                                </label>
                                <label class="inline-flex items-center gap-2">
                                    <input type="radio" v-model="form.exam_type" value="ct" />
                                    <span>Class Test</span>
                                </label>
                                <label class="inline-flex items-center gap-2">
                                    <input type="radio" v-model="form.exam_type" value="others" />
                                    <span>Others Exam</span>
                                </label>
                            </div>
                        </div>

                        <div v-if="form.exam_type === 'term'" class="lg:col-span-6">
                            <div class="text-xs font-semibold text-slate-600">Class Test (optional)</div>
                            <select v-model="form.class_test_exam_id" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                <option value="">Select</option>
                                <option v-for="e in ctExams" :key="'ct-' + e.id" :value="String(e.id)">{{ e.name }}</option>
                            </select>
                        </div>

                        <div class="lg:col-span-12">
                            <div class="text-xs font-semibold text-slate-600">Description</div>
                            <textarea v-model="form.description" rows="4" class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-3">
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <div class="text-sm font-semibold text-slate-900">Status</div>
                    <select v-model="form.status" class="mt-2 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="active">Active</option>
                        <option value="deactive">Deactive</option>
                        <option value="draft">Draft</option>
                    </select>

                    <button type="button" class="mt-4 h-9 w-full rounded-lg bg-slate-900 px-4 text-sm font-semibold text-white hover:bg-slate-800" :disabled="saving" @click="submit">{{ saving ? '...' : isEdit ? 'Update' : 'Create' }}</button>
                    <button type="button" class="mt-2 h-9 w-full rounded-lg border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="saving" @click="goIndex">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'ExamManage',
    props: {
        systems: {
            type: Object,
            default: () => ({}),
        },
        examId: {
            type: [Number, String],
            default: null,
        },
    },
    data() {
        return {
            saving: false,
            error: '',
            success: '',
            ctExams: [],
            form: {
                name: '',
                exam_type: 'term',
                class_test_exam_id: '',
                description: '',
                status: 'active',
            },
        }
    },
    computed: {
        isEdit() {
            return !!this.examId
        },
    },
    async created() {
        await this.loadCtExams()
        if (this.isEdit) {
            await this.load()
        }
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
            this.go('/admin/exam')
        },
        async loadCtExams() {
            try {
                const res = await window.axios.get('/admin/exam', { params: { allData: true, exam_type: 'ct' } })
                const list = Array.isArray(res?.data) ? res.data : Array.isArray(res?.data?.data) ? res.data.data : []
                this.ctExams = list
            } catch {
                this.ctExams = []
            }
        },
        async load() {
            this.error = ''
            try {
                const res = await window.axios.get(`/admin/exam/${this.examId}`)
                const row = res?.data?.exam || res?.data || {}
                this.form.name = row?.name || ''
                this.form.exam_type = row?.exam_type || 'term'
                this.form.class_test_exam_id = row?.class_test_exam_id != null ? String(row.class_test_exam_id) : ''
                this.form.description = row?.description || ''
                this.form.status = row?.status || 'active'
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load exam.'
            }
        },
        async submit() {
            this.saving = true
            this.error = ''
            this.success = ''
            try {
                const payload = {
                    name: this.form.name,
                    exam_type: this.form.exam_type,
                    class_test_exam_id:
                        this.form.exam_type === 'term' && this.form.class_test_exam_id
                            ? Number(this.form.class_test_exam_id)
                            : null,
                    description: this.form.description,
                    status: this.form.status,
                }

                let res
                if (this.isEdit) {
                    res = await window.axios.put(`/admin/exam/${this.examId}`, payload)
                } else {
                    res = await window.axios.post('/admin/exam', payload)
                }

                const msg = res?.data?.message || (this.isEdit ? 'Update Successfully!' : 'Create Successfully!')
                this.success = msg
                this.toast(msg, 'success')

                if (!this.isEdit) {
                    const id = res?.data?.id
                    if (id) {
                        this.go(`/admin/exam/${id}/edit`)
                    } else {
                        this.goIndex()
                    }
                }
            } catch (e) {
                this.error = e?.response?.data?.message || e?.response?.data?.exception || 'Save failed.'
                this.toast(this.error, 'error')
            } finally {
                this.saving = false
            }
        },
    },
}
</script>
