<template>
    <div class="flex flex-col gap-4">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">{{ isEdit ? 'Edit Subject Assign' : 'Create Subject Assign' }}</div>
                    <div class="mt-1 text-sm text-slate-600">{{ isEdit ? 'Update subject assign information' : 'Create a new subject assign' }}</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-sm border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="saving" @click="goIndex">Back</button>
                    <button type="button" class="rounded-sm bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700" :disabled="saving" @click="submit">{{ saving ? '...' : isEdit ? 'Update' : 'Create' }}</button>
                </div>
            </div>
        </div>

        <div v-if="error" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ error }}</div>
        <div v-if="success" class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ success }}</div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
            <div class="space-y-4 lg:col-span-8 xl:col-span-9">
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="min-w-0">
                        <div class="text-sm font-semibold text-slate-900">Filters</div>
                        <div class="mt-1 text-xs text-slate-500">Select academic level, department/group and class</div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <div class="text-xs font-semibold text-slate-600">Academic Level</div>
                            <select v-model="form.academic_qualification_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" @change="onQualChange">
                                <option value="">Select</option>
                                <option v-for="q in qualifications" :key="'q-' + q.id" :value="String(q.id)">{{ q.name }}</option>
                            </select>
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Department/Group</div>
                            <select v-model="form.department_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                <option value="">Select</option>
                                <option v-for="d in departmentsFiltered" :key="'d-' + d.id" :value="String(d.id)">{{ d.name }}</option>
                            </select>
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Class</div>
                            <select v-model="form.academic_class_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                <option value="">Select</option>
                                <option v-for="c in classesFiltered" :key="'c-' + c.id" :value="String(c.id)">{{ c.name }}</option>
                            </select>
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Main Subject</div>
                            <input v-model.number="form.main_subject" type="number" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>

                        <div class="sm:col-span-2 lg:col-span-3">
                            <div class="text-xs font-semibold text-slate-600">Note</div>
                            <textarea v-model="form.note" rows="3" class="mt-1 w-full rounded-sm border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"></textarea>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-end gap-1 border-b border-slate-200">
                            <button
                                type="button"
                                class="rounded-t-lg border border-b-0 px-4 py-2 text-sm font-semibold"
                                :class="activeTab === 'parent' ? 'border-slate-200 bg-white text-slate-900' : 'border-transparent bg-slate-50 text-slate-600 hover:text-slate-900'"
                                @click="activeTab = 'parent'"
                            >
                                Parent Subject Assign
                            </button>
                            <button
                                type="button"
                                class="rounded-t-lg border border-b-0 px-4 py-2 text-sm font-semibold"
                                :class="activeTab === 'child' ? 'border-slate-200 bg-white text-slate-900' : 'border-transparent bg-slate-50 text-slate-600 hover:text-slate-900'"
                                @click="activeTab = 'child'"
                            >
                                Child Subject Assign
                            </button>
                        </div>

                        <button
                            v-if="activeTab === 'parent'"
                            type="button"
                            class="rounded-sm bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800"
                            @click="addRow"
                        >
                            Add Row
                        </button>
                        <button
                            v-else
                            type="button"
                            class="rounded-sm bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800"
                            @click="addChildRow"
                        >
                            Add Row
                        </button>
                    </div>

                    <div v-if="activeTab === 'parent'" class="mt-4">
                        <div class="overflow-x-auto">
                            <table class="min-w-[1400px] w-full table-fixed border-collapse border border-slate-300">
                            <thead class="bg-emerald-100">
                                <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-700">
                                    <th class="w-[420px] min-w-[420px] border border-slate-300 px-3 py-2">Subject</th>
                                    <th class="w-[72px] border border-slate-300 px-2 py-2 text-center">4th</th>
                                    <th class="w-[72px] border border-slate-300 px-2 py-2 text-center">Main</th>
                                    <th class="w-[64px] border border-slate-300 px-2 py-2 text-center">CT M.</th>
                                    <th class="w-[76px] border border-slate-300 px-2 py-2 text-center">CT Pass</th>
                                    <th class="w-[64px] border border-slate-300 px-2 py-2 text-center">CQ M.</th>
                                    <th class="w-[76px] border border-slate-300 px-2 py-2 text-center">CQ Pass</th>
                                    <th class="w-[64px] border border-slate-300 px-2 py-2 text-center">MCQ M.</th>
                                    <th class="w-[76px] border border-slate-300 px-2 py-2 text-center">MCQ Pass</th>
                                    <th class="w-[70px] border border-slate-300 px-2 py-2 text-center">Prac. M.</th>
                                    <th class="w-[76px] border border-slate-300 px-2 py-2 text-center">Prac. Pass</th>
                                    <th class="w-[70px] border border-slate-300 px-2 py-2 text-center">Total</th>
                                    <th class="w-[70px] border border-slate-300 px-2 py-2 text-center">Sorting</th>
                                    <th class="w-[70px] border border-slate-300 px-2 py-2 text-center">Amount</th>
                                    <th class="w-[64px] border border-slate-300 px-2 py-2 text-center">Act</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(d, idx) in form.details" :key="'d-' + idx" class="text-sm text-slate-800" :class="idx % 2 === 0 ? 'bg-white' : 'bg-slate-50'">
                                    <td class="border border-slate-300 px-3 py-2 align-top">
                                        <select v-model="d.subject_id" class="h-8 w-full rounded-sm border border-slate-300 bg-white px-2 text-sm shadow-sm outline-none">
                                            <option value="">Select</option>
                                            <option v-for="s in parentSubjects" :key="'s-' + s.id" :value="String(s.id)">{{ s.name_bn || s.name_en || s.name }}</option>
                                        </select>

                                        <select v-if="Number(d.fourth_subject) === 1 || Number(d.main_subject) === 1" v-model="d.except_subject_id" class="mt-2 h-8 w-full rounded-sm border border-slate-300 bg-white px-2 text-sm shadow-sm outline-none">
                                            <option value="">Select Except Subject</option>
                                            <option v-for="s in parentSubjects" :key="'es-' + s.id" :value="String(s.id)">{{ s.name_bn || s.name_en || s.name }}</option>
                                        </select>
                                    </td>
                                    <td class="border border-slate-300 px-2 py-2 text-center">
                                        <select v-model.number="d.fourth_subject" class="h-8 w-full rounded-sm border border-slate-300 bg-white px-2 text-sm shadow-sm outline-none">
                                            <option :value="0">No</option>
                                            <option :value="1">Yes</option>
                                        </select>
                                    </td>
                                    <td class="border border-slate-300 px-2 py-2 text-center">
                                        <select v-model.number="d.main_subject" class="h-8 w-full rounded-sm border border-slate-300 bg-white px-2 text-sm shadow-sm outline-none">
                                            <option :value="0">No</option>
                                            <option :value="1">Yes</option>
                                        </select>
                                    </td>
                                    <td class="border border-slate-300 px-2 py-2"><input v-model.number="d.ct_mark" type="number" class="h-8 w-full rounded-sm border border-slate-300 bg-white px-2 text-sm text-center shadow-sm outline-none" /></td>
                                    <td class="border border-slate-300 px-2 py-2"><input v-model.number="d.ct_pass_mark" type="number" class="h-8 w-full rounded-sm border border-slate-300 bg-white px-2 text-sm text-center shadow-sm outline-none" /></td>
                                    <td class="border border-slate-300 px-2 py-2"><input v-model.number="d.cq_mark" type="number" class="h-8 w-full rounded-sm border border-slate-300 bg-white px-2 text-sm text-center shadow-sm outline-none" /></td>
                                    <td class="border border-slate-300 px-2 py-2"><input v-model.number="d.cq_pass_mark" type="number" class="h-8 w-full rounded-sm border border-slate-300 bg-white px-2 text-sm text-center shadow-sm outline-none" /></td>
                                    <td class="border border-slate-300 px-2 py-2"><input v-model.number="d.mcq_mark" type="number" class="h-8 w-full rounded-sm border border-slate-300 bg-white px-2 text-sm text-center shadow-sm outline-none" /></td>
                                    <td class="border border-slate-300 px-2 py-2"><input v-model.number="d.mcq_pass_mark" type="number" class="h-8 w-full rounded-sm border border-slate-300 bg-white px-2 text-sm text-center shadow-sm outline-none" /></td>
                                    <td class="border border-slate-300 px-2 py-2"><input v-model.number="d.practical_mark" type="number" class="h-8 w-full rounded-sm border border-slate-300 bg-white px-2 text-sm text-center shadow-sm outline-none" /></td>
                                    <td class="border border-slate-300 px-2 py-2"><input v-model.number="d.practical_pass_mark" type="number" class="h-8 w-full rounded-sm border border-slate-300 bg-white px-2 text-sm text-center shadow-sm outline-none" /></td>
                                    <td class="border border-slate-300 px-2 py-2"><input v-model.number="d.total_mark" type="number" class="h-8 w-full rounded-sm border border-slate-300 bg-white px-2 text-sm text-center shadow-sm outline-none" /></td>
                                    <td class="border border-slate-300 px-2 py-2"><input v-model.number="d.sorting" type="number" class="h-8 w-full rounded-sm border border-slate-300 bg-white px-2 text-sm text-center shadow-sm outline-none" /></td>
                                    <td class="border border-slate-300 px-2 py-2"><input v-model.number="d.amount" type="number" class="h-8 w-full rounded-sm border border-slate-300 bg-white px-2 text-sm text-center shadow-sm outline-none" /></td>
                                    <td class="border border-slate-300 px-2 py-2 text-center">
                                        <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-sm border border-red-200 bg-white text-red-700 hover:bg-red-50" :disabled="form.details.length <= 1" @click="removeRow(idx)">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12h12" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                            </table>
                        </div>
                    </div>

                    <div v-else class="mt-4">
                        <div class="overflow-x-auto">
                            <table class="min-w-[1100px] w-full table-fixed border-collapse border border-slate-300">
                            <thead class="bg-emerald-100">
                                <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-700">
                                    <th class="w-[360px] min-w-[360px] border border-slate-300 px-3 py-2">Parent Subject</th>
                                    <th class="w-[360px] min-w-[360px] border border-slate-300 px-3 py-2">Child Subject</th>
                                    <th class="w-[64px] border border-slate-300 px-2 py-2 text-center">CT M.</th>
                                    <th class="w-[76px] border border-slate-300 px-2 py-2 text-center">CT Pass</th>
                                    <th class="w-[64px] border border-slate-300 px-2 py-2 text-center">CQ M.</th>
                                    <th class="w-[76px] border border-slate-300 px-2 py-2 text-center">CQ Pass</th>
                                    <th class="w-[64px] border border-slate-300 px-2 py-2 text-center">MCQ M.</th>
                                    <th class="w-[76px] border border-slate-300 px-2 py-2 text-center">MCQ Pass</th>
                                    <th class="w-[70px] border border-slate-300 px-2 py-2 text-center">Prac. M.</th>
                                    <th class="w-[76px] border border-slate-300 px-2 py-2 text-center">Prac. Pass</th>
                                    <th class="w-[70px] border border-slate-300 px-2 py-2 text-center">Total</th>
                                    <th class="w-[64px] border border-slate-300 px-2 py-2 text-center">Act</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(c, idx) in form.child_details" :key="'c-' + idx" class="text-sm text-slate-800" :class="idx % 2 === 0 ? 'bg-white' : 'bg-slate-50'">
                                    <td class="border border-slate-300 px-3 py-2">
                                        <select v-model="c.parent_subject_id" class="h-8 w-full rounded-sm border border-slate-300 bg-white px-2 text-sm shadow-sm outline-none" @change="c.subject_id = ''">
                                            <option value="">Select</option>
                                            <option v-for="s in childParentOptions" :key="'ps-' + s.id" :value="String(s.id)">{{ s.name_bn || s.name_en || s.name }}</option>
                                        </select>
                                    </td>
                                    <td class="border border-slate-300 px-3 py-2">
                                        <select v-model="c.subject_id" class="h-8 w-full rounded-sm border border-slate-300 bg-white px-2 text-sm shadow-sm outline-none" :disabled="!c.parent_subject_id">
                                            <option value="">Select</option>
                                            <option v-for="s in getChildSubjects(c.parent_subject_id)" :key="'cs-' + s.id" :value="String(s.id)">{{ s.name_bn || s.name_en || s.name }}</option>
                                        </select>
                                    </td>
                                    <td class="border border-slate-300 px-2 py-2"><input v-model.number="c.ct_mark" type="number" class="h-8 w-full rounded-sm border border-slate-300 bg-white px-2 text-sm text-center shadow-sm outline-none" /></td>
                                    <td class="border border-slate-300 px-2 py-2"><input v-model.number="c.ct_pass_mark" type="number" class="h-8 w-full rounded-sm border border-slate-300 bg-white px-2 text-sm text-center shadow-sm outline-none" /></td>
                                    <td class="border border-slate-300 px-2 py-2"><input v-model.number="c.cq_mark" type="number" class="h-8 w-full rounded-sm border border-slate-300 bg-white px-2 text-sm text-center shadow-sm outline-none" /></td>
                                    <td class="border border-slate-300 px-2 py-2"><input v-model.number="c.cq_pass_mark" type="number" class="h-8 w-full rounded-sm border border-slate-300 bg-white px-2 text-sm text-center shadow-sm outline-none" /></td>
                                    <td class="border border-slate-300 px-2 py-2"><input v-model.number="c.mcq_mark" type="number" class="h-8 w-full rounded-sm border border-slate-300 bg-white px-2 text-sm text-center shadow-sm outline-none" /></td>
                                    <td class="border border-slate-300 px-2 py-2"><input v-model.number="c.mcq_pass_mark" type="number" class="h-8 w-full rounded-sm border border-slate-300 bg-white px-2 text-sm text-center shadow-sm outline-none" /></td>
                                    <td class="border border-slate-300 px-2 py-2"><input v-model.number="c.practical_mark" type="number" class="h-8 w-full rounded-sm border border-slate-300 bg-white px-2 text-sm text-center shadow-sm outline-none" /></td>
                                    <td class="border border-slate-300 px-2 py-2"><input v-model.number="c.practical_pass_mark" type="number" class="h-8 w-full rounded-sm border border-slate-300 bg-white px-2 text-sm text-center shadow-sm outline-none" /></td>
                                    <td class="border border-slate-300 px-2 py-2"><input v-model.number="c.total_mark" type="number" class="h-8 w-full rounded-sm border border-slate-300 bg-white px-2 text-sm text-center shadow-sm outline-none" /></td>
                                    <td class="border border-slate-300 px-2 py-2 text-center">
                                        <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-sm border border-red-200 bg-white text-red-700 hover:bg-red-50" :disabled="form.child_details.length <= 1" @click="removeChildRow(idx)">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12h12" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-4 lg:col-span-4 xl:col-span-3">
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="text-sm font-semibold text-slate-900">Status</div>
                    <select v-model="form.status" class="mt-2 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="active">Active</option>
                        <option value="deactive">Deactive</option>
                        <option value="draft">Draft</option>
                    </select>

                    <button type="button" class="mt-4 h-9 w-full rounded-sm bg-slate-900 px-4 text-sm font-semibold text-white hover:bg-slate-800" :disabled="saving" @click="submit">{{ saving ? '...' : isEdit ? 'Update' : 'Create' }}</button>
                    <button type="button" class="mt-2 h-9 w-full rounded-sm border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="saving" @click="goIndex">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'SubjectAssignManage',
    props: {
        systems: {
            type: Object,
            default: () => ({}),
        },
        subjectAssignId: {
            type: [Number, String],
            default: null,
        },
    },
    data() {
        return {
            saving: false,
            error: '',
            success: '',
            subjects: [],
            subjectsById: {},
            activeTab: 'parent',
            form: {
                academic_qualification_id: '',
                department_id: '',
                academic_class_id: '',
                main_subject: null,
                note: '',
                status: 'active',
                details: [
                    {
                        id: null,
                        subject_id: '',
                        except_subject_id: '',
                        fourth_subject: 0,
                        main_subject: 0,
                        ct_mark: 0,
                        ct_pass_mark: 0,
                        cq_mark: 0,
                        cq_pass_mark: 0,
                        mcq_mark: 0,
                        mcq_pass_mark: 0,
                        practical_mark: 0,
                        practical_pass_mark: 0,
                        total_mark: 0,
                        sorting: 1,
                        amount: 0,
                    },
                ],
                child_details: [
                    {
                        id: null,
                        parent_subject_id: '',
                        subject_id: '',
                        ct_mark: 0,
                        ct_pass_mark: 0,
                        cq_mark: 0,
                        cq_pass_mark: 0,
                        mcq_mark: 0,
                        mcq_pass_mark: 0,
                        practical_mark: 0,
                        practical_pass_mark: 0,
                        total_mark: 0,
                    },
                ],
            },
        }
    },
    computed: {
        isEdit() {
            return !!this.subjectAssignId
        },
        qualifications() {
            return Array.isArray(this.systems?.global?.academic_qualifications) ? this.systems.global.academic_qualifications : []
        },
        departments() {
            return Array.isArray(this.systems?.global?.departments) ? this.systems.global.departments : []
        },
        departmentQualidactions() {
            return Array.isArray(this.systems?.global?.department_qualidactions) ? this.systems.global.department_qualidactions : []
        },
        classes() {
            return Array.isArray(this.systems?.global?.academic_classes) ? this.systems.global.academic_classes : []
        },
        departmentsFiltered() {
            const qid = String(this.form.academic_qualification_id || '')
            if (!qid) return this.departments
            const allowed = new Set(
                this.departmentQualidactions
                    .filter((m) => String(m.academic_qualification_id || '') === qid)
                    .map((m) => String(m.department_id || ''))
            )
            return this.departments.filter((d) => allowed.has(String(d.id)))
        },
        classesFiltered() {
            const qid = String(this.form.academic_qualification_id || '')
            if (!qid) return this.classes
            return this.classes.filter((c) => String(c.academic_qualification_id || '') === qid)
        },
        parentSubjects() {
            return (this.subjects || []).filter((s) => Number(s.is_child || 0) === 0)
        },
        assignedParentSubjectIdSet() {
            const set = new Set()
            for (const d of this.form.details || []) {
                const sid = String(d?.subject_id || '').trim()
                if (sid) set.add(sid)
            }
            return set
        },
        childParentOptions() {
            const set = this.assignedParentSubjectIdSet
            if (!set.size) return this.parentSubjects
            return this.parentSubjects.filter((s) => set.has(String(s.id)))
        },
    },
    async created() {
        await this.loadSubjects()
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
            this.go('/admin/subjectAssign')
        },
        onQualChange() {
            this.form.department_id = ''
            this.form.academic_class_id = ''
        },
        normalizeDetail(d, idx) {
            return {
                id: d?.id ?? null,
                subject_id: d?.subject_id != null ? String(d.subject_id) : '',
                except_subject_id: d?.except_subject_id != null ? String(d.except_subject_id) : '',
                fourth_subject: Number(d?.fourth_subject || 0),
                main_subject: Number(d?.main_subject || 0),
                ct_mark: Number(d?.ct_mark || 0),
                ct_pass_mark: Number(d?.ct_pass_mark || 0),
                cq_mark: Number(d?.cq_mark || 0),
                cq_pass_mark: Number(d?.cq_pass_mark || 0),
                mcq_mark: Number(d?.mcq_mark || 0),
                mcq_pass_mark: Number(d?.mcq_pass_mark || 0),
                practical_mark: Number(d?.practical_mark || 0),
                practical_pass_mark: Number(d?.practical_pass_mark || 0),
                total_mark: Number(d?.total_mark || 0),
                sorting: Number(d?.sorting || idx + 1),
                amount: Number(d?.amount || 0),
            }
        },
        normalizeChildDetail(d, idx) {
            return {
                id: d?.id ?? null,
                parent_subject_id: d?.parent_subject_id != null ? String(d.parent_subject_id) : '',
                subject_id: d?.subject_id != null ? String(d.subject_id) : '',
                ct_mark: Number(d?.ct_mark || 0),
                ct_pass_mark: Number(d?.ct_pass_mark || 0),
                cq_mark: Number(d?.cq_mark || 0),
                cq_pass_mark: Number(d?.cq_pass_mark || 0),
                mcq_mark: Number(d?.mcq_mark || 0),
                mcq_pass_mark: Number(d?.mcq_pass_mark || 0),
                practical_mark: Number(d?.practical_mark || 0),
                practical_pass_mark: Number(d?.practical_pass_mark || 0),
                total_mark: Number(d?.total_mark || 0),
            }
        },
        addRow() {
            const idx = this.form.details.length
            this.form.details.push(
                this.normalizeDetail(
                    {
                        id: null,
                        subject_id: '',
                        except_subject_id: '',
                        fourth_subject: 0,
                        main_subject: 0,
                        ct_mark: 0,
                        ct_pass_mark: 0,
                        cq_mark: 0,
                        cq_pass_mark: 0,
                        mcq_mark: 0,
                        mcq_pass_mark: 0,
                        practical_mark: 0,
                        practical_pass_mark: 0,
                        total_mark: 0,
                        sorting: idx + 1,
                        amount: 0,
                    },
                    idx
                )
            )
        },
        removeRow(idx) {
            if (this.form.details.length <= 1) return
            this.form.details.splice(idx, 1)
            this.form.details = this.form.details.map((d, i) => ({ ...d, sorting: d.sorting || i + 1 }))
        },
        addChildRow() {
            const idx = this.form.child_details.length
            this.form.child_details.push(
                this.normalizeChildDetail(
                    {
                        id: null,
                        parent_subject_id: '',
                        subject_id: '',
                        ct_mark: 0,
                        ct_pass_mark: 0,
                        cq_mark: 0,
                        cq_pass_mark: 0,
                        mcq_mark: 0,
                        mcq_pass_mark: 0,
                        practical_mark: 0,
                        practical_pass_mark: 0,
                        total_mark: 0,
                    },
                    idx
                )
            )
        },
        removeChildRow(idx) {
            if (this.form.child_details.length <= 1) return
            this.form.child_details.splice(idx, 1)
        },
        getChildSubjects(parentId) {
            const pid = String(parentId || '')
            if (!pid) return []
            return (this.subjects || []).filter((s) => String(s.parent_id || '') === pid)
        },
        async loadSubjects() {
            try {
                const res = await window.axios.get('/admin/subject', { params: { allData: true } })
                const list = Array.isArray(res?.data) ? res.data : []
                this.subjects = list
                const map = {}
                for (const s of list) {
                    map[String(s.id)] = s
                }
                this.subjectsById = map
            } catch {
                this.subjects = []
                this.subjectsById = {}
            }
        },
        processChildDetailsFromDetails() {
            const parents = []
            const children = []

            for (const raw of this.form.details || []) {
                const sid = String(raw?.subject_id || '')
                const subject = this.subjectsById[sid]
                const parentId = subject?.parent_id != null ? String(subject.parent_id) : ''
                if (parentId) {
                    children.push(this.normalizeChildDetail({ ...raw, parent_subject_id: parentId }, children.length))
                } else {
                    parents.push(this.normalizeDetail(raw, parents.length))
                }
            }

            this.form.details = parents.length ? parents : [this.normalizeDetail({}, 0)]
            this.form.child_details = children.length ? children : [this.normalizeChildDetail({}, 0)]
        },
        async load() {
            this.error = ''
            try {
                const res = await window.axios.get(`/admin/subjectAssign/${this.subjectAssignId}`)
                const row = res?.data?.subject_assign || {}
                const details = Array.isArray(res?.data?.details) ? res.data.details : []

                this.form.academic_qualification_id = row?.academic_qualification_id != null ? String(row.academic_qualification_id) : ''
                this.form.department_id = row?.department_id != null ? String(row.department_id) : ''
                this.form.academic_class_id = row?.academic_class_id != null ? String(row.academic_class_id) : ''
                this.form.main_subject = row?.main_subject ?? null
                this.form.note = row?.note || ''
                this.form.status = row?.status || 'active'
                this.form.details = details.length ? details.map((d, i) => this.normalizeDetail(d, i)) : [this.normalizeDetail({}, 0)]
                this.processChildDetailsFromDetails()
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load subject assign.'
            }
        },
        async submit() {
            this.saving = true
            this.error = ''
            this.success = ''
            try {
                if (!this.form.academic_qualification_id || !this.form.department_id || !this.form.academic_class_id) {
                    this.error = 'Academic Level, Department, and Class are required.'
                    this.toast(this.error, 'error')
                    return
                }

                const cleanDetails = (this.form.details || [])
                    .filter((d) => d.subject_id)
                    .map((d, idx) => ({
                        id: d.id || undefined,
                        subject_id: Number(d.subject_id),
                        except_subject_id: d.except_subject_id ? Number(d.except_subject_id) : null,
                        fourth_subject: d.fourth_subject ? 1 : 0,
                        main_subject: d.main_subject ? 1 : 0,
                        ct_mark: Number(d.ct_mark || 0),
                        ct_pass_mark: Number(d.ct_pass_mark || 0),
                        cq_mark: Number(d.cq_mark || 0),
                        cq_pass_mark: Number(d.cq_pass_mark || 0),
                        mcq_mark: Number(d.mcq_mark || 0),
                        mcq_pass_mark: Number(d.mcq_pass_mark || 0),
                        practical_mark: Number(d.practical_mark || 0),
                        practical_pass_mark: Number(d.practical_pass_mark || 0),
                        total_mark: Number(d.total_mark || 0),
                        sorting: Number(d.sorting || idx + 1),
                        amount: Number(d.amount || 0),
                    }))

                const childRows = (this.form.child_details || [])
                    .filter((c) => c.parent_subject_id && c.subject_id)
                    .map((c) => ({
                        id: c.id || undefined,
                        parent_subject_id: Number(c.parent_subject_id),
                        subject_id: Number(c.subject_id),
                        ct_mark: Number(c.ct_mark || 0),
                        ct_pass_mark: Number(c.ct_pass_mark || 0),
                        cq_mark: Number(c.cq_mark || 0),
                        cq_pass_mark: Number(c.cq_pass_mark || 0),
                        mcq_mark: Number(c.mcq_mark || 0),
                        mcq_pass_mark: Number(c.mcq_pass_mark || 0),
                        practical_mark: Number(c.practical_mark || 0),
                        practical_pass_mark: Number(c.practical_pass_mark || 0),
                        total_mark: Number(c.total_mark || 0),
                    }))

                if (childRows.length) {
                    for (const child of childRows) {
                        const parent = cleanDetails.find((p) => Number(p.subject_id) === Number(child.parent_subject_id))
                        cleanDetails.push({
                            id: child.id,
                            subject_id: child.subject_id,
                            except_subject_id: parent?.except_subject_id ?? null,
                            fourth_subject: parent?.fourth_subject ?? 0,
                            main_subject: parent?.main_subject ?? 0,
                            ct_mark: child.ct_mark,
                            ct_pass_mark: child.ct_pass_mark,
                            cq_mark: child.cq_mark,
                            cq_pass_mark: child.cq_pass_mark,
                            mcq_mark: child.mcq_mark,
                            mcq_pass_mark: child.mcq_pass_mark,
                            practical_mark: child.practical_mark,
                            practical_pass_mark: child.practical_pass_mark,
                            total_mark: child.total_mark,
                            sorting: parent?.sorting ?? (cleanDetails.length + 1),
                            amount: parent?.amount ?? 0,
                        })
                    }
                }

                if (!cleanDetails.length) {
                    this.error = 'Please select at least one subject.'
                    this.toast(this.error, 'error')
                    return
                }

                const payload = {
                    academic_qualification_id: Number(this.form.academic_qualification_id),
                    department_id: Number(this.form.department_id),
                    academic_class_id: Number(this.form.academic_class_id),
                    main_subject: this.form.main_subject,
                    note: this.form.note,
                    status: this.form.status,
                    details: cleanDetails,
                }

                let res
                if (this.isEdit) {
                    res = await window.axios.put(`/admin/subjectAssign/${this.subjectAssignId}`, payload)
                } else {
                    res = await window.axios.post('/admin/subjectAssign', payload)
                }

                if (res?.data?.error) {
                    this.error = res.data.error
                    this.toast(this.error, 'error')
                    return
                }

                const msg = res?.data?.message || (this.isEdit ? 'Update Successfully!' : 'Create Successfully!')
                this.success = msg
                this.toast(msg, 'success')

                if (!this.isEdit) {
                    const id = res?.data?.id
                    if (id) {
                        this.go(`/admin/subjectAssign/${id}/edit`)
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
