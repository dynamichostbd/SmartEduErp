<template>
    <div class="flex flex-col gap-4">
        <div class=" border border-slate-300 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Student ID Card</div>
                    <div class="mt-1 text-sm text-slate-600">Search and print student ID cards</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button
                        type="button"
                        class="rounded-sm border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                        :disabled="loading"
                        @click="goBack"
                    >
                        Back
                    </button>
                    <button
                        type="button"
                        class="rounded-sm bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700"
                        :disabled="loading || students.length === 0"
                        @click="printCards"
                    >
                        Print
                    </button>
                </div>
            </div>
        </div>

        <div v-if="error" class=" border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            {{ error }}
        </div>

        <div class=" border border-slate-300 bg-white p-5">
            <div class="text-sm font-semibold text-slate-900">Search Students</div>

            <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-12">
                <div class="lg:col-span-3">
                    <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" v-model="search.custom_rolls" :true-value="1" :false-value="0" />
                        Custom Rolls Input
                    </label>
                </div>

                <div class="lg:col-span-3">
                    <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" v-model="search.custom_mobiles" :true-value="1" :false-value="0" />
                        Custom Mobile Numbers
                    </label>
                </div>

                <div v-if="search.custom_rolls" class="lg:col-span-12">
                    <textarea
                        v-model="search.roll_lists"
                        class="w-full rounded-sm border border-slate-300 bg-white p-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                        placeholder="ex: 111111,111112,111113 (max-input - 100)"
                        rows="2"
                    ></textarea>
                </div>

                <div v-if="search.custom_mobiles" class="lg:col-span-12">
                    <textarea
                        v-model="search.mobile_lists"
                        class="w-full rounded-sm border border-slate-300 bg-white p-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                        placeholder="ex: 01712345678,01812345678 (max-input - 100)"
                        rows="2"
                    ></textarea>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Session <span class="text-red-600">*</span></div>
                    <select v-model="search.academic_session_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">Select</option>
                        <option v-for="s in sessionsSorted" :key="'ses-' + s.id" :value="String(s.id)">{{ s.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Academic Level <span class="text-red-600">*</span></div>
                    <select v-model="search.academic_qualification_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">Select</option>
                        <option v-for="q in qualifications" :key="'q-' + q.id" :value="String(q.id)">{{ q.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Department <span class="text-red-600">*</span></div>
                    <select v-model="search.department_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">Select</option>
                        <option v-for="d in filteredDepartments" :key="'d-' + d.id" :value="String(d.id)">{{ d.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Class <span class="text-red-600">*</span></div>
                    <select v-model="search.academic_class_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">Select</option>
                        <option v-for="c in filteredClasses" :key="'c-' + c.id" :value="String(c.id)">{{ c.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Validity Date</div>
                    <input v-model="expiredDate" type="date" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Skip</div>
                    <select v-model.number="search.skip" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option :value="0">100</option>
                        <option :value="100">200</option>
                        <option :value="200">300</option>
                        <option :value="300">400</option>
                        <option :value="400">500</option>
                        <option :value="500">600</option>
                        <option :value="600">700</option>
                        <option :value="700">800</option>
                        <option :value="800">900</option>
                        <option :value="900">1000</option>
                        <option :value="1000">1100</option>
                        <option :value="1100">1200</option>
                        <option :value="1200">1300</option>
                        <option :value="1300">1400</option>
                        <option :value="1400">1500</option>
                        <option :value="1500">1600</option>
                        <option :value="1600">1700</option>
                        <option :value="1700">1800</option>
                        <option :value="1800">1900</option>
                        <option :value="1900">2000</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Order</div>
                    <select v-model="search.order_by" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="asc">ASC</option>
                        <option value="desc">DESC</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Take</div>
                    <input v-model="search.take" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" placeholder="Take" />
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Field</div>
                    <select v-model="search.field_name" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="0">Select One</option>
                        <option value="student_id">Software ID</option>
                        <option value="name">Name</option>
                        <option value="mobile">Mobile</option>
                        <option value="reg_no">Reg No.</option>
                        <option value="admission_id">Admission ID</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Value</div>
                    <input v-model="search.value" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" placeholder="Type your text" />
                </div>

                <div class="lg:col-span-2 flex items-end">
                    <button
                        type="button"
                        class="h-9 w-full rounded-sm bg-slate-900 px-4 text-sm font-semibold text-white hover:bg-slate-800"
                        :disabled="loading"
                        @click="searchClick"
                    >
                        {{ loading ? 'Searching...' : 'Search' }}
                    </button>
                </div>
            </div>
        </div>

        <div class=" border border-slate-300 bg-white p-5">
            <div class="text-sm font-semibold text-slate-900">Student List</div>

            <div class="mt-4" id="id-card">
                <div class="id-card">
                    <template v-for="(student, key1) in students" :key="'std-' + (student.id || key1)">
                        <div class="id-bg">
                            <div class="id-front">
                                <img v-if="frontPart" :src="frontPart" />
                                <div class="profile-image">
                                    <img :src="student.profile" />
                                </div>
                                <div class="std-name">
                                    {{ student.name }}
                                </div>
                                <div class="front-info">
                                    <p :style="{ fontSize: student.department_name && student.department_name.length > 20 ? '9px' : '10px' }">
                                        {{ student.department_name || '--' }}
                                    </p>
                                    <p>{{ student.academic_session_name || '--' }}</p>
                                    <p>{{ student.academic_qualification_name || '--' }}</p>
                                    <p>{{ student.college_roll || '--' }}</p>
                                    <p>{{ student.mobile || '--' }}</p>
                                    <p>{{ student.student_id || '--' }}</p>
                                </div>
                            </div>

                            <div class="id-back">
                                <img v-if="backPart" :src="backPart" />

                                <div class="back-info">
                                    <p :style="{ fontSize: student.fathers_name && student.fathers_name.length >= 22 ? '7.5px' : student.fathers_name && student.fathers_name.length > 20 ? '8px' : '9px' }">
                                        {{ student.fathers_name || '--' }}
                                    </p>
                                    <p :style="{ fontSize: student.mothers_name && student.mothers_name.length >= 22 ? '7.5px' : student.mothers_name && student.mothers_name.length > 20 ? '8px' : '9px' }">
                                        {{ student.mothers_name || '--' }}
                                    </p>
                                    <p>{{ student.blood_group || '--' }}</p>
                                    <p>{{ student.dob || '--' }}</p>
                                    <p class="expired_date">
                                        {{ expiredDate || '--' }}
                                    </p>
                                </div>

                                <div v-if="student.qr_code" class="qr-code">
                                    <img :src="'data:image/svg+xml;base64,' + student.qr_code" />
                                </div>
                            </div>
                        </div>
                        <div class="page-break" v-if="(key1 + 1) % 5 == 0"></div>
                    </template>
                </div>
            </div>

            <div v-if="!students.length" class="mt-4 rounded-xl border border-dashed border-slate-300 bg-slate-50 p-5 text-sm text-slate-600">
                No students loaded
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'StudentIDCard',
    props: {
        systems: {
            type: Object,
            default: () => ({}),
        },
    },
    data() {
        return {
            loading: false,
            error: '',
            students: [],
            expiredDate: '',
            search: {
                custom_rolls: 0,
                custom_mobiles: 0,
                roll_lists: '',
                mobile_lists: '',
                skip: 0,
                take: '',
                field_name: 'mobile',
                value: '',
                order_by: 'asc',
                academic_class_id: '',
                academic_session_id: '',
                department_id: '',
                academic_qualification_id: '',
            },
        }
    },
    computed: {
        frontPart() {
            return this.systems?.site?.idcard_front_part || ''
        },
        backPart() {
            return this.systems?.site?.idcard_back_part || ''
        },
        classes() {
            const list = this.systems?.global?.academic_classes
            if (!Array.isArray(list)) return []
            return list.filter((r) => r?.status === undefined || r?.status === null || String(r.status) === 'active' || String(r.status) === '1')
        },
        sessions() {
            const list = this.systems?.global?.academic_sessions
            if (!Array.isArray(list)) return []
            return list.filter((r) => r?.status === undefined || r?.status === null || String(r.status) === 'active' || String(r.status) === '1')
        },
        sessionsSorted() {
            const list = Array.isArray(this.sessions) ? [...this.sessions] : []
            return list.sort((a, b) => this.sessionSortKey(b) - this.sessionSortKey(a))
        },
        qualifications() {
            const list = this.systems?.global?.academic_qualifications
            if (!Array.isArray(list)) return []
            return list.filter((r) => r?.status === undefined || r?.status === null || String(r.status) === 'active' || String(r.status) === '1')
        },
        departments() {
            const list = this.systems?.global?.departments
            if (!Array.isArray(list)) return []
            return list.filter((r) => r?.status === undefined || r?.status === null || String(r.status) === 'active' || String(r.status) === '1')
        },
        departmentQualidactions() {
            const list = this.systems?.global?.department_qualidactions || this.systems?.global?.department_qualifications
            return Array.isArray(list) ? list : []
        },
        filteredDepartments() {
            const qualificationId = this.search?.academic_qualification_id
            if (!qualificationId) return []

            const allowedDeptIds = new Set(
                this.departmentQualidactions
                    .filter((r) => String(r.academic_qualification_id) === String(qualificationId))
                    .map((r) => String(r.department_id))
            )

            return this.departments.filter((d) => allowedDeptIds.has(String(d.id)))
        },
        filteredClasses() {
            const qualificationId = this.search?.academic_qualification_id
            if (!qualificationId) return []
            return this.classes.filter((c) => String(c.academic_qualification_id) === String(qualificationId))
        },
    },
    watch: {
        'search.custom_rolls'(val) {
            if (val) {
                this.search.custom_mobiles = 0
            }
        },
        'search.custom_mobiles'(val) {
            if (val) {
                this.search.custom_rolls = 0
            }
        },
        'search.academic_qualification_id'(next, prev) {
            if (String(next || '') !== String(prev || '')) {
                this.search.department_id = ''
                this.search.academic_class_id = ''
            }
        },
    },
    methods: {
        sessionSortKey(s) {
            const name = String(s?.name || '')
            const m = name.match(/(\d{4})\s*[-/]\s*(\d{4})/)
            if (m) {
                const start = Number(m[1] || 0)
                const end = Number(m[2] || 0)
                return start * 10000 + end
            }
            const n = Number(name)
            if (!Number.isNaN(n) && Number.isFinite(n)) return n
            const id = Number(s?.id || 0)
            return Number.isFinite(id) ? id : 0
        },
        goBack() {
            window.location.href = '/admin/student'
        },
        async searchClick() {
            this.loading = true
            this.error = ''
            try {
                if (this.search.custom_rolls && (!this.search.roll_lists || !this.search.roll_lists.trim())) {
                    this.error = 'Please enter roll numbers.'
                    return
                }
                if (this.search.custom_mobiles && (!this.search.mobile_lists || !this.search.mobile_lists.trim())) {
                    this.error = 'Please enter mobile numbers.'
                    return
                }

                if (!this.search.custom_rolls && !this.search.custom_mobiles) {
                    for (const k of ['academic_session_id', 'academic_qualification_id', 'department_id', 'academic_class_id']) {
                        if (!this.search[k]) {
                            this.error = 'Please select search criteria.'
                            return
                        }
                    }
                }

                const res = await window.axios.get('/admin/student-idcard', { params: this.search })
                this.students = Array.isArray(res?.data) ? res.data : []
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to search students.'
                this.students = []
            } finally {
                this.loading = false
            }
        },
        async printCards() {
            const el = document.getElementById('id-card')
            if (!el) return

            try {
                const imgs = Array.from(el.querySelectorAll('img') || [])
                await Promise.all(
                    imgs.map(
                        (img) =>
                            new Promise((resolve) => {
                                if (img.complete) return resolve(true)
                                img.onload = () => resolve(true)
                                img.onerror = () => resolve(true)
                            })
                    )
                )

                if (document.fonts && document.fonts.ready) {
                    await document.fonts.ready
                }
            } catch (e) {
                // ignore
            }

            window.print()
        },
        printCss() {
            return `
.id-card { width: 100%; float: left; position: relative; }
.id-bg { position: relative; height: 658.5px; width: 208.2px; float: left; clear: right; }
.id-card img { height: 100%; width: 100%; }
.id-card .id-front { position: relative; padding: 0px; float: left; height: 328.27px; width: 100%; }
.id-card .id-front .std-name { position: absolute; top: 166px; text-align: center; margin: auto; width: 100%; font-weight: bold; font-size: 11px; padding: 0px 10px; height: 40px; display: flex; justify-content: center; align-items: center; }
.id-card .id-front .front-info { position: absolute; top: 204.3px; left: 91px; font-size: 10px; color: #000; }
.id-card .id-front .front-info p { margin-bottom: -2.1px !important; }
.id-card .id-front .profile-image img { position: absolute; height: 88.4px; width: 78px; top: 80.3px; left: 65.7px; border-radius: 7px; }
.id-card .id-back { position: relative; padding: 0px; float: left; height: 328.27px; width: 100%; }
.id-card .id-back .back-info { position: absolute; top: 33.5px; left: 81px; font-size: 9px; color: #000; }
.id-card .id-back .back-info p { margin-bottom: -3px !important; height: 13.2px; }
.id-card .id-back .qr-code { position: absolute; left: 72px; top: 94px; }
.id-card .id-back .qr-code img { height: 66px; width: 66px; }
.expired_date { color: red; font-weight: bold; }
@media print { .page-break { page-break-after: always; } }
`
        },
    },
}
</script>

<style>
.id-card {
    width: 100%;
    float: left;
    position: relative;
}
.id-bg {
    position: relative;
    height: 658.5px;
    width: 208.2px;
    float: left;
    clear: right;
}
.id-card img {
    height: 100%;
    width: 100%;
}
.id-card .id-front {
    position: relative;
    padding: 0px;
    float: left;
    height: 328.27px;
    width: 100%;
}
.id-card .id-front .std-name {
    position: absolute;
    top: 166px;
    text-align: center;
    margin: auto;
    width: 100%;
    font-weight: bold;
    font-size: 11px;
    padding: 0px 10px;
    height: 40px;
    display: flex;
    justify-content: center;
    align-items: center;
}
.id-card .id-front .front-info {
    position: absolute;
    top: 204.3px;
    left: 91px;
    font-size: 10px;
    color: #000;
}
.id-card .id-front .front-info p {
    margin-bottom: -2.1px !important;
}
.id-card .id-front .profile-image img {
    position: absolute;
    height: 88.4px;
    width: 78px;
    top: 80.3px;
    left: 65.7px;
    border-radius: 7px;
}

.id-card .id-back {
    position: relative;
    height: 100%;
    padding: 0px;
    float: left;
    height: 328.27px;
    width: 100%;
}

.id-card .id-back .back-info {
    position: absolute;
    top: 33.5px;
    left: 81px;
    font-size: 9px;
    color: #000;
}

.id-card .id-back .back-info p {
    margin-bottom: -3px !important;
    height: 13.2px;
}

.id-card .id-back .qr-code {
    position: absolute;
    left: 72px;
    top: 94px;
}
.id-card .id-back .qr-code img {
    height: 66px;
    width: 66px;
}
.expired_date {
    color: red;
    font-weight: bold;
}

@media print {
    @page {
        margin: 0;
    }

    html,
    body {
        margin: 0 !important;
        padding: 0 !important;
    }

    body * {
        visibility: hidden;
    }

    #id-card,
    #id-card * {
        visibility: visible;
    }

    #id-card {
        position: absolute;
        left: 0;
        top: 0;
    }

    .page-break {
        page-break-after: always;
    }
}
</style>
