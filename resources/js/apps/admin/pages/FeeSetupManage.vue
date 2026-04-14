<template>
    <div class="flex flex-col gap-4">
        <div class=" border border-slate-300 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">{{ isEdit ? 'Edit Fee Setup' : 'Create Fee Setup' }}</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-sm border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="submitting" @click="goIndex">Back</button>
                    <button type="button" class="rounded-sm bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700" :disabled="submitting || setupExist" @click="submit">{{ submitting ? '...' : 'Save' }}</button>
                </div>
            </div>
        </div>

        <div v-if="setupExist" class=" border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">Setup already exists for selected Department/Level/Class.</div>
        <div v-if="error" class=" border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ error }}</div>

        <div class=" border border-slate-300 bg-white p-5">
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Academic Level</div>
                    <select v-model="form.academic_qualification_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none" @change="onQualificationChange">
                        <option value="">Select</option>
                        <option v-for="q in qualifications" :key="'q-' + q.id" :value="String(q.id)">{{ q.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Department/Group</div>
                    <select v-model="form.department_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none" @change="onDepartmentChange">
                        <option value="">Select</option>
                        <option v-for="d in departmentsFiltered" :key="'d-' + d.id" :value="String(d.id)">{{ d.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Class</div>
                    <select v-model="form.academic_class_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none" @change="checkExist">
                        <option value="">Select</option>
                        <option v-for="c in classOptions" :key="'c-' + c.id" :value="String(c.id)">{{ c.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-4">
                    <div class="text-xs font-semibold text-slate-600">Remarks</div>
                    <textarea v-model="form.description" rows="2" class="mt-1 w-full rounded-sm border border-slate-300 bg-white px-3 py-2 text-sm outline-none"></textarea>
                </div>

                <div class="lg:col-span-2">
                    <div class="mt-6 flex items-center gap-2">
                        <input id="select_all" v-model="selectAllActive" type="checkbox" class="h-4 w-4" @change="toggleAllStatus" />
                        <label for="select_all" class="text-sm font-semibold text-slate-700">Select All</label>
                    </div>
                </div>
            </div>
        </div>

        <div class=" border border-slate-300 bg-white">
            <div class="flex items-center justify-between border-b border-slate-300 p-4">
                <div class="text-sm font-semibold text-slate-900">Fee Setup Details</div>
                <div class="text-xs text-slate-500">Drag and drop to reorder items</div>
            </div>

            <div class="p-5">
                <div class="grid grid-cols-12 gap-3 pb-3 text-xs font-semibold uppercase tracking-wider text-slate-700">
                    <div class="col-span-12 lg:col-span-2">Account Head</div>
                    <div class="col-span-6 lg:col-span-1">Amount</div>
                    <div class="col-span-6 lg:col-span-2">Gateway Account</div>
                    <div class="col-span-6 lg:col-span-2">Start Date</div>
                    <div class="col-span-6 lg:col-span-2">Expired Date</div>
                    <div class="col-span-6 lg:col-span-2">Additional Date</div>
                    <div class="col-span-6 lg:col-span-1">Actions</div>
                </div>

                <div class="flex flex-col gap-4">
                    <div
                        v-for="(d, idx) in form.details"
                        :key="d._key"
                        class="rounded-xl border p-4"
                        :class="Number(d.status) === 1 ? 'border-emerald-200 bg-emerald-50/40' : 'border-rose-200 bg-rose-50/40'"
                        @dragover.prevent
                        @drop="drop(idx)"
                    >
                        <div class="grid grid-cols-12 gap-3">
                            <div class="col-span-12 lg:col-span-2">
                                <select v-model.number="d.account_head_id" class="h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none">
                                    <option :value="null">--Select Any--</option>
                                    <option v-for="h in accountHeadOptions" :key="'h-' + h.id" :value="h.id">{{ h.name }}</option>
                                </select>

                                <div class="relative mt-2">
                                    <button type="button" class="h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-left text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="toggleDependDropdown(idx)">
                                        Depend On
                                    </button>
                                    <div v-if="dependOpen[idx]" class="absolute z-20 mt-2 w-full rounded-xl border border-slate-300 bg-white p-3 shadow-xl">
                                        <input v-model="dependSearch[idx]" type="text" class="h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none" placeholder="Search account heads..." />
                                        <div class="mt-2 max-h-56 overflow-auto">
                                            <label v-for="opt in filteredDependOptions(idx)" :key="'dep-' + idx + '-' + opt.id" class="flex items-center gap-2 py-1 text-sm text-slate-700">
                                                <input type="checkbox" class="h-4 w-4" :checked="isDependSelected(idx, opt.id)" :disabled="opt.id === d.account_head_id" @change="toggleDepend(idx, opt.id, $event)" />
                                                <span>{{ opt.name }}</span>
                                            </label>
                                        </div>
                                        <div class="mt-2 flex items-center justify-between border-t border-slate-100 pt-2">
                                            <button type="button" class="h-8 rounded-sm bg-slate-900 px-3 text-xs font-semibold text-white" @click="selectAllDepend(idx)">Select All</button>
                                            <button type="button" class="h-8 rounded-sm border border-slate-300 bg-white px-3 text-xs font-semibold text-slate-700" @click="clearAllDepend(idx)">Clear</button>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="selectedDependNames(idx).length" class="mt-2 flex flex-wrap gap-2">
                                    <span v-for="(nm, j) in selectedDependNames(idx)" :key="'tag-' + idx + '-' + j" class="inline-flex items-center gap-2 rounded-full border border-slate-300 bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-700">
                                        {{ nm }}
                                        <button type="button" class="text-slate-500" @click="removeDepend(idx, j)">x</button>
                                    </span>
                                </div>
                            </div>

                            <div class="col-span-6 lg:col-span-1">
                                <input v-model.number="d.amount" type="number" min="0" class="h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none" placeholder="Amount" />

                                <select v-model="d.payment_duration" class="mt-2 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none">
                                    <option :value="null">--Durations--</option>
                                    <option v-for="(label, key) in paymentDurations" :key="'pd-' + key" :value="key">{{ label }}</option>
                                </select>
                            </div>

                            <div class="col-span-6 lg:col-span-2 relative">
                                <select v-model.number="d.payment_gateway_id" class="h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none">
                                    <option :value="null">--Select Any--</option>
                                    <option v-for="g in gatewayAccounts" :key="'g-' + g.id" :value="Number(g.id)">{{ g.title || g.store_id }}</option>
                                </select>

                                <button type="button" class="mt-2 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-left text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="toggleApplicable(idx)">
                                    Applicable For
                                </button>

                                <div v-if="applicableOpen === idx" class="mt-2 rounded-xl border border-slate-300 bg-white p-3">
                                    <label class="flex items-center gap-2 text-sm text-slate-700"><input v-model.number="d.online_addmission_fees" type="checkbox" class="h-4 w-4" :true-value="1" :false-value="0" /> <span>Online Admission Fee</span></label>
                                    <label class="mt-2 flex items-center gap-2 text-sm text-slate-700"><input v-model.number="d.service_charge" type="checkbox" class="h-4 w-4" :true-value="1" :false-value="0" /> <span>Charge Applicable</span></label>
                                    <label class="mt-2 flex items-center gap-2 text-sm text-slate-700"><input v-model.number="d.college_fee" type="checkbox" class="h-4 w-4" :true-value="1" :false-value="0" /> <span>College Fee</span></label>
                                    <label class="mt-2 flex items-center gap-2 text-sm text-slate-700"><input v-model.number="d.migration_fee" type="checkbox" class="h-4 w-4" :true-value="1" :false-value="0" /> <span>Migration Fee</span></label>
                                    <label class="mt-2 flex items-center gap-2 text-sm text-slate-700"><input v-model.number="d.check_registration_no" type="checkbox" class="h-4 w-4" :true-value="1" :false-value="0" /> <span>Check Registration No.</span></label>
                                    <label class="mt-2 flex items-center gap-2 text-sm text-slate-700"><input v-model.number="d.improvement" type="checkbox" class="h-4 w-4" :true-value="1" :false-value="0" /> <span>Improvement Student</span></label>
                                </div>
                            </div>

                            <div class="col-span-6 lg:col-span-2">
                                <input v-model="d.start_date" type="datetime-local" step="1" class="h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none" />

                                <select v-model.number="d.exam_id" class="mt-2 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none">
                                    <option :value="null">--Select Exam--</option>
                                    <option v-for="e in exams" :key="'e-' + e.id" :value="Number(e.id)">{{ e.name }}</option>
                                </select>
                            </div>

                            <div class="col-span-6 lg:col-span-2">
                                <input v-model="d.expire_date" type="datetime-local" step="1" class="h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none" />

                                <select v-if="d.exam_id" v-model="d.examination_year" class="mt-2 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none">
                                    <option :value="null">--Select Exam Year--</option>
                                    <option v-for="y in years" :key="'y-' + y" :value="y">{{ y }}</option>
                                </select>
                            </div>

                            <div class="col-span-6 lg:col-span-2">
                                <input v-model="d.additional_date" type="datetime-local" step="1" class="h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none" />

                                <select v-if="Number(d.improvement) === 1" v-model.number="d.academic_class_id_improvment" class="mt-2 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none">
                                    <option :value="null">--Select Class--</option>
                                    <option v-for="c in improvementClassOptions" :key="'ic-' + c.id" :value="Number(c.id)">{{ c.name }}</option>
                                </select>
                            </div>

                            <div class="col-span-6 lg:col-span-1 flex items-start justify-center gap-2">
                               
                                <button type="button" class="inline-flex h-9 w-10 items-center justify-center rounded-sm border border-rose-200 bg-white text-rose-700 hover:bg-rose-50" :disabled="form.details.length <= 1" @click="removeDetail(idx)">-</button>
                                <button type="button" class="inline-flex h-9 w-10 items-center justify-center rounded-sm border border-emerald-200 bg-white text-emerald-700 hover:bg-emerald-50" v-if="idx === form.details.length - 1" @click="addDetail">+</button>
                            </div>

                            <div class="col-span-12 mt-3 flex flex-wrap items-center justify-center gap-4 border-t border-slate-300 pt-3">

                                <div class="flex flex-wrap items-center gap-4">
                                    <label class="flex items-center gap-2 text-sm font-semibold" :class="d.is_maker == 1 ? 'text-emerald-700' : 'text-slate-700'">
                                        <input type="checkbox" class="h-4 w-4" v-model.number="d.is_maker" :true-value="1" :false-value="0" @change="handleMakerChange(d)" />
                                        <span>Maker</span>
                                        <span v-if="d.maker && d.maker.name" class="text-xs font-semibold text-slate-500">({{ d.maker.name }})</span>
                                    </label>

                                    <label class="flex items-center gap-2 text-sm font-semibold" :class="d.is_checker == 1 ? 'text-emerald-700' : 'text-slate-700'">
                                        <input type="checkbox" class="h-4 w-4" v-model.number="d.is_checker" :true-value="1" :false-value="0" :disabled="isCheckerDisabled(d)" @change="handleCheckerChange(d)" />
                                        <span>Checker</span>
                                        <span v-if="d.checker && d.checker.name" class="text-xs font-semibold text-slate-500">({{ d.checker.name }})</span>
                                    </label>

                                    <label class="flex items-center gap-2 text-sm font-semibold" :class="d.is_approver == 1 ? 'text-emerald-700' : 'text-slate-700'">
                                        <input type="checkbox" class="h-4 w-4" v-model.number="d.is_approver" :true-value="1" :false-value="0" :disabled="isApproverDisabled(d)" @change="handleApproverChange(d)" />
                                        <span>Approver</span>
                                        <span v-if="d.approver && d.approver.name" class="text-xs font-semibold text-slate-500">({{ d.approver.name }})</span>
                                    </label>
                                </div>
                            </div>

                            <div class="col-span-12 mt-3 flex flex-wrap items-center justify-center gap-4 border-t border-slate-300 pt-3">
                                <label class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                                    <input v-model.number="d.status" type="checkbox" class="h-4 w-4" :true-value="1" :false-value="0" />
                                    <span>{{ Number(d.status) === 1 ? 'Active' : 'Deactive' }}</span>
                                </label>

                                 <button
                                    type="button"
                                    class="inline-flex h-9 w-10 items-center justify-center rounded-sm border border-slate-300 bg-white text-slate-700 hover:bg-slate-50"
                                    draggable="true"
                                    title="Drag"
                                    @dragstart.stop="dragStart(idx)"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 6h.01M10 12h.01M10 18h.01M14 6h.01M14 12h.01M14 18h.01" />
                                    </svg>
                                </button>


                                <div class="text-sm font-semibold" :class="onlineNow(d) ? 'text-emerald-700' : 'text-rose-700'">
                                    <span class="mr-2 inline-block h-2 w-2 rounded-full" :class="onlineNow(d) ? 'bg-emerald-600' : 'bg-rose-600'"></span>
                                    {{ onlineNow(d) ? 'ONLINE' : 'OFFLINE' }}
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="pt-4 text-center">
                    <button type="button" class="h-9 rounded-sm bg-emerald-600 px-6 text-sm font-semibold text-white hover:bg-emerald-700" :disabled="submitting || setupExist" @click="submit">{{ submitting ? 'processing...' : 'Save' }}</button>
                </div>
            </div>
        </div>

        <div v-if="submitting" class=" border border-slate-300 bg-white p-5 text-sm text-slate-600">Saving...</div>
    </div>
</template>

<script>
export default {
    name: 'FeeSetupManage',
    props: {
        systems: {
            type: Object,
            default: () => ({}),
        },
        feeSetupId: {
            type: [Number, String],
            default: null,
        },
    },
    data() {
        return {
            loading: false,
            submitting: false,
            error: '',
            setupExist: false,
            selectAllActive: true,
            paymentGateways: [],
            applicableOpen: null,
            dragFrom: null,
            dependOpen: {},
            dependSearch: {},
            form: {
                academic_qualification_id: '',
                department_id: '',
                academic_class_id: '',
                description: '',
                details: [],
            },
            classOptionsApi: null,
        }
    },
    computed: {
        isEdit() {
            return !!this.feeSetupId
        },
        qualifications() {
            const list = this.systems?.global?.academic_qualifications
            return Array.isArray(list) ? list : []
        },
        departments() {
            const list = this.systems?.global?.departments
            return Array.isArray(list) ? list : []
        },
        departmentQualidactions() {
            const list = this.systems?.global?.department_qualidactions
            return Array.isArray(list) ? list : []
        },
        classes() {
            const list = this.systems?.global?.academic_classes
            return Array.isArray(list) ? list : []
        },
        departmentsFiltered() {
            const qid = String(this.form.academic_qualification_id || '')
            if (!qid) return []
            const allowed = new Set(
                this.departmentQualidactions
                    .filter((m) => String(m.academic_qualification_id) === qid)
                    .map((m) => String(m.department_id))
            )
            return this.departments.filter((d) => allowed.has(String(d.id)))
        },
        classesFiltered() {
            const qid = String(this.form.academic_qualification_id || '')
            if (!qid) return []
            return this.classes.filter((c) => String(c.academic_qualification_id) === qid)
        },
        classOptions() {
            if (this.classOptionsApi && Array.isArray(this.classOptionsApi) && this.classOptionsApi.length > 0) {
                return this.classOptionsApi
            }
            return this.classesFiltered
        },
        improvementClassOptions() {
            return this.classesFiltered
        },
        accountHeadOptions() {
            const raw = this.systems?.global?.account_heads || {}
            return Object.keys(raw)
                .map((k) => ({ id: Number(k), name: raw[k] }))
                .filter((x) => Number.isFinite(x.id))
        },
        paymentDurations() {
            return this.systems?.global?.payment_durations || {}
        },
        exams() {
            const list = this.systems?.global?.exams
            return Array.isArray(list) ? list : []
        },
        years() {
            const now = new Date().getFullYear()
            const out = []
            for (let y = now; y >= now - 30; y--) out.push(String(y))
            return out
        },
        gatewayAccounts() {
            const did = String(this.form.department_id || '')
            if (!did) return []
            return (this.paymentGateways || []).filter((g) => String(g.department_id || '') === did)
        },
        loggedId() {
            return Number(this.systems?.global?.auth_user?.id || 0)
        },
        loggedName() {
            return this.systems?.global?.auth_user?.name || ''
        },
    },
    async created() {
        await this.loadPaymentGateways()
        this.ensureOneDetail()
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
            this.go('/admin/feeSetup')
        },
        apiDatetime(v) {
            if (!v) return null
            return String(v).replace('T', ' ')
        },
        ensureOneDetail() {
            if (!Array.isArray(this.form.details)) this.form.details = []
            if (this.form.details.length === 0) {
                this.addDetail()
            }
        },
        makeDetail() {
            return {
                _key: `${Date.now()}-${Math.random()}`,
                account_head_id: null,
                depend_head_id: [],
                amount: null,
                payment_duration: null,
                payment_gateway_id: null,
                start_date: '',
                expire_date: '',
                additional_date: '',
                exam_id: null,
                examination_year: null,
                academic_class_id_improvment: null,
                online_addmission_fees: 0,
                service_charge: 0,
                college_fee: 0,
                migration_fee: 0,
                check_registration_no: 0,
                improvement: 0,
                is_maker: 0,
                maker_id: null,
                maker: null,
                is_checker: 0,
                checker_id: null,
                checker: null,
                is_approver: 0,
                approver_id: null,
                approver: null,
                status: 1,
            }
        },
        addDetail() {
            this.form.details.push(this.makeDetail())
            this.checkSelectAllState()
        },
        removeDetail(idx) {
            if (this.form.details.length <= 1) return
            this.form.details.splice(idx, 1)
            this.checkSelectAllState()
        },
        toggleApplicable(idx) {
            this.applicableOpen = this.applicableOpen === idx ? null : idx
        },
        toggleAllStatus() {
            const v = this.selectAllActive ? 1 : 0
            this.form.details = (this.form.details || []).map((d) => ({ ...d, status: v }))
        },
        checkSelectAllState() {
            const ds = this.form.details || []
            if (ds.length === 0) {
                this.selectAllActive = true
                return
            }
            this.selectAllActive = ds.every((d) => Number(d.status) === 1)
        },
        onlineNow(d) {
            if (Number(d?.status) !== 1) return false
            if (!d?.start_date || !d?.additional_date) return false
            const now = new Date()
            const s = new Date(String(d.start_date).replace(' ', 'T'))
            const e = new Date(String(d.additional_date).replace(' ', 'T'))
            return now >= s && now <= e
        },
        dragStart(idx) {
            this.dragFrom = idx
        },
        drop(idx) {
            if (this.dragFrom === null || this.dragFrom === idx) return
            const arr = [...this.form.details]
            const [item] = arr.splice(this.dragFrom, 1)
            arr.splice(idx, 0, item)
            this.form.details = arr
            this.dragFrom = null
        },
        toggleDependDropdown(idx) {
            this.dependOpen = { ...this.dependOpen, [idx]: !this.dependOpen[idx] }
        },
        filteredDependOptions(idx) {
            const q = String(this.dependSearch?.[idx] || '').toLowerCase()
            const list = this.accountHeadOptions
            if (!q) return list
            return list.filter((o) => String(o.name || '').toLowerCase().includes(q))
        },
        isDependSelected(idx, id) {
            const d = this.form.details[idx]
            const arr = Array.isArray(d?.depend_head_id) ? d.depend_head_id : []
            return arr.map((x) => Number(x)).includes(Number(id))
        },
        toggleDepend(idx, id, ev) {
            const d = this.form.details[idx]
            const arr = Array.isArray(d?.depend_head_id) ? [...d.depend_head_id] : []
            const checked = ev?.target?.checked
            const n = Number(id)
            if (checked) {
                if (!arr.map((x) => Number(x)).includes(n)) arr.push(n)
            } else {
                const i = arr.map((x) => Number(x)).indexOf(n)
                if (i > -1) arr.splice(i, 1)
            }
            d.depend_head_id = arr
        },
        selectAllDepend(idx) {
            const d = this.form.details[idx]
            const all = this.accountHeadOptions
                .map((o) => Number(o.id))
                .filter((id) => Number(id) !== Number(d.account_head_id))
            d.depend_head_id = all
        },
        clearAllDepend(idx) {
            const d = this.form.details[idx]
            d.depend_head_id = []
        },
        selectedDependNames(idx) {
            const d = this.form.details[idx]
            const ids = Array.isArray(d?.depend_head_id) ? d.depend_head_id : []
            const map = new Map(this.accountHeadOptions.map((o) => [Number(o.id), o.name]))
            return ids.map((id) => map.get(Number(id)) || String(id))
        },
        removeDepend(idx, j) {
            const d = this.form.details[idx]
            const arr = Array.isArray(d?.depend_head_id) ? [...d.depend_head_id] : []
            arr.splice(j, 1)
            d.depend_head_id = arr
        },
        async loadPaymentGateways() {
            try {
                const res = await window.axios.get('/admin/paymentGateway', { params: { allData: true } })
                this.paymentGateways = Array.isArray(res?.data) ? res.data : []
            } catch {
                this.paymentGateways = []
            }
        },
        async loadClassesByAssign() {
            this.classOptionsApi = null
            if (!this.form.department_id || !this.form.academic_qualification_id) return
            try {
                const res = await window.axios.get('/admin/get-classes', {
                    params: {
                        department_id: Number(this.form.department_id),
                        academic_qualification_id: Number(this.form.academic_qualification_id),
                    },
                })
                const obj = res?.data || {}
                const arr = Object.keys(obj).map((k) => ({ id: Number(k), name: obj[k] }))
                this.classOptionsApi = arr.filter((x) => Number.isFinite(x.id))
            } catch {
                this.classOptionsApi = null
            }
        },
        onQualificationChange() {
            this.form.department_id = ''
            this.form.academic_class_id = ''
            this.classOptionsApi = null
            this.checkExist()
        },
        async onDepartmentChange() {
            this.form.academic_class_id = ''
            await this.loadClassesByAssign()
            this.checkExist()
        },
        async checkExist() {
            this.setupExist = false
            if (!this.form.department_id || !this.form.academic_qualification_id || !this.form.academic_class_id) return
            try {
                const res = await window.axios.get('/admin/exist-feeSetup', {
                    params: {
                        id: this.isEdit ? Number(this.feeSetupId) : null,
                        department_id: Number(this.form.department_id),
                        academic_qualification_id: Number(this.form.academic_qualification_id),
                        academic_class_id: Number(this.form.academic_class_id),
                    },
                })
                this.setupExist = !!res?.data
            } catch {
                this.setupExist = false
            }
        },
        async load() {
            this.loading = true
            this.error = ''
            try {
                const res = await window.axios.get(`/admin/feeSetup/${this.feeSetupId}`)
                const fs = res?.data?.fee_setup || {}
                this.form.academic_qualification_id = fs?.academic_qualification_id != null ? String(fs.academic_qualification_id) : ''
                this.form.department_id = fs?.department_id != null ? String(fs.department_id) : ''
                this.form.academic_class_id = fs?.academic_class_id != null ? String(fs.academic_class_id) : ''
                this.form.description = fs?.description || ''

                const ds = Array.isArray(res?.data?.details) ? res.data.details : []
                this.form.details = ds.map((d) => {
                    const depend = Array.isArray(d?.depend_head_id) ? d.depend_head_id : []
                    return {
                        _key: `${Date.now()}-${Math.random()}`,
                        account_head_id: d?.account_head_id != null ? Number(d.account_head_id) : null,
                        depend_head_id: depend,
                        amount: d?.amount != null ? Number(d.amount) : null,
                        payment_duration: d?.payment_duration ?? null,
                        payment_gateway_id: d?.payment_gateway_id != null ? Number(d.payment_gateway_id) : null,
                        start_date: d?.start_date ? String(d.start_date).replace(' ', 'T') : '',
                        expire_date: d?.expire_date ? String(d.expire_date).replace(' ', 'T') : '',
                        additional_date: d?.additional_date ? String(d.additional_date).replace(' ', 'T') : '',
                        exam_id: d?.exam_id != null ? Number(d.exam_id) : null,
                        examination_year: d?.examination_year ?? null,
                        academic_class_id_improvment: d?.academic_class_id_improvment != null ? Number(d.academic_class_id_improvment) : null,
                        online_addmission_fees: Number(d?.online_addmission_fees || 0),
                        service_charge: Number(d?.service_charge || 0),
                        college_fee: Number(d?.college_fee || 0),
                        migration_fee: Number(d?.migration_fee || 0),
                        check_registration_no: Number(d?.check_registration_no || 0),
                        improvement: Number(d?.improvement || 0),
                        is_maker: Number(d?.is_maker || 0),
                        maker_id: d?.maker_id != null ? Number(d.maker_id) : null,
                        maker: d?.maker_id ? { name: d?.maker_name || '' } : null,
                        is_checker: Number(d?.is_checker || 0),
                        checker_id: d?.checker_id != null ? Number(d.checker_id) : null,
                        checker: d?.checker_id ? { name: d?.checker_name || '' } : null,
                        is_approver: Number(d?.is_approver || 0),
                        approver_id: d?.approver_id != null ? Number(d.approver_id) : null,
                        approver: d?.approver_id ? { name: d?.approver_name || '' } : null,
                        status: Number(d?.status ?? 1),
                    }
                })

                await this.loadClassesByAssign()
                this.ensureOneDetail()
                this.checkSelectAllState()
                await this.checkExist()
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load fee setup.'
            } finally {
                this.loading = false
            }
        },
        validateBeforeSubmit() {
            if (!this.form.academic_qualification_id || !this.form.department_id || !this.form.academic_class_id) {
                return 'Academic Level, Department and Class are required.'
            }
            if (!Array.isArray(this.form.details) || this.form.details.length === 0) {
                return 'Fee setup details are required.'
            }
            for (let i = 0; i < this.form.details.length; i++) {
                const d = this.form.details[i]
                if (!d.account_head_id) return `Row ${i + 1}: Account Head is required.`
                if (d.amount === null || d.amount === '' || Number(d.amount) <= 0) return `Row ${i + 1}: Amount is required.`
                if (!d.payment_gateway_id) return `Row ${i + 1}: Gateway Account is required.`
                if (!d.start_date) return `Row ${i + 1}: Start Date is required.`
                if (!d.expire_date) return `Row ${i + 1}: Expired Date is required.`
                if (!d.additional_date) return `Row ${i + 1}: Additional Date is required.`
            }
            return ''
        },
        async submit() {
            const msg = this.validateBeforeSubmit()
            if (msg) {
                this.error = msg
                this.toast(msg, 'error')
                return
            }
            await this.checkExist()
            if (this.setupExist) {
                this.toast('Setup already exists for selected criteria.', 'error')
                return
            }

            this.submitting = true
            this.error = ''
            try {
                const payload = {
                    academic_qualification_id: Number(this.form.academic_qualification_id),
                    department_id: Number(this.form.department_id),
                    academic_class_id: Number(this.form.academic_class_id),
                    description: this.form.description,
                    details: (this.form.details || []).map((d) => ({
                        account_head_id: Number(d.account_head_id),
                        depend_head_id: Array.isArray(d.depend_head_id) ? d.depend_head_id.map((x) => Number(x)) : [],
                        amount: Number(d.amount),
                        payment_duration: d.payment_duration,
                        payment_gateway_id: Number(d.payment_gateway_id),
                        start_date: this.apiDatetime(d.start_date),
                        expire_date: this.apiDatetime(d.expire_date),
                        additional_date: this.apiDatetime(d.additional_date),
                        exam_id: d.exam_id ? Number(d.exam_id) : null,
                        examination_year: d.examination_year,
                        academic_class_id_improvment: d.academic_class_id_improvment ? Number(d.academic_class_id_improvment) : null,
                        online_addmission_fees: Number(d.online_addmission_fees || 0),
                        service_charge: Number(d.service_charge || 0),
                        college_fee: Number(d.college_fee || 0),
                        migration_fee: Number(d.migration_fee || 0),
                        check_registration_no: Number(d.check_registration_no || 0),
                        improvement: Number(d.improvement || 0),
                        is_maker: Number(d.is_maker || 0),
                        maker_id: d.maker_id != null ? Number(d.maker_id) : null,
                        is_checker: Number(d.is_checker || 0),
                        checker_id: d.checker_id != null ? Number(d.checker_id) : null,
                        is_approver: Number(d.is_approver || 0),
                        approver_id: d.approver_id != null ? Number(d.approver_id) : null,
                        status: Number(d.status || 0),
                    })),
                }

                let res
                if (this.isEdit) {
                    res = await window.axios.put(`/admin/feeSetup/${this.feeSetupId}`, payload)
                } else {
                    res = await window.axios.post('/admin/feeSetup', payload)
                }

                this.toast(res?.data?.message || 'Saved', 'success')
                this.goIndex()
            } catch (e) {
                this.error = e?.response?.data?.message || e?.response?.data?.exception || 'Save failed.'
                this.toast(this.error, 'error')
            } finally {
                this.submitting = false
            }
        },
        handleMakerChange(d) {
            if (Number(d.is_maker) === 1) {
                d.maker_id = this.loggedId || null
                d.maker = this.loggedName ? { name: this.loggedName } : null
                d.is_checker = 0
                d.checker_id = null
                d.checker = null
                d.is_approver = 0
                d.approver_id = null
                d.approver = null
                d.status = 0
            } else {
                d.maker_id = null
                d.maker = null
                d.is_checker = 0
                d.checker_id = null
                d.checker = null
                d.is_approver = 0
                d.approver_id = null
                d.approver = null
                d.status = 0
            }
        },
        handleCheckerChange(d) {
            if (Number(d.is_checker) === 1) {
                d.checker_id = this.loggedId || null
                d.checker = this.loggedName ? { name: this.loggedName } : null
            } else {
                d.checker_id = null
                d.checker = null
                d.is_approver = 0
                d.approver_id = null
                d.approver = null
                d.status = 0
            }
        },
        handleApproverChange(d) {
            if (Number(d.is_approver) === 1) {
                d.approver_id = this.loggedId || null
                d.approver = this.loggedName ? { name: this.loggedName } : null
            } else {
                d.approver_id = null
                d.approver = null
                d.status = 0
            }
        },
        isCheckerDisabled(d) {
            if (Number(d.is_maker) !== 1) return true
            if (Number(d.maker_id || 0) === Number(this.loggedId || 0)) return true
            if (d.checker_id && Number(d.checker_id) !== Number(this.loggedId || 0)) return true
            return false
        },
        isApproverDisabled(d) {
            if (Number(d.is_maker) !== 1) return true
            if (Number(d.is_checker) !== 1) return true
            if (Number(d.maker_id || 0) === Number(this.loggedId || 0)) return true
            if (d.approver_id && Number(d.approver_id) !== Number(this.loggedId || 0)) return true
            return false
        },
    },
}
</script>
