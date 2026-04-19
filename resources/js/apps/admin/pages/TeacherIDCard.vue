<template>
    <div class="flex flex-col gap-4">
        <div class=" border border-slate-300 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Teacher ID Card</div>
                    <div class="mt-1 text-sm text-slate-600">Search and print teacher ID cards</div>
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
                        :disabled="loading || teachers.length === 0"
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
            <div class="text-sm font-semibold text-slate-900">Search Teachers</div>

            <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-12">
                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Academic Level</div>
                    <select v-model="search.academic_qualification_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">Select</option>
                        <option v-for="q in qualifications" :key="'q-' + q.id" :value="String(q.id)">{{ q.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Department</div>
                    <select v-model="search.department_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">Select</option>
                        <option v-for="d in filteredDepartments" :key="'d-' + d.id" :value="String(d.id)">{{ d.name }}</option>
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
                        <option value="name">Name</option>
                        <option value="mobile">Mobile</option>
                        <option value="email">Email</option>
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
            <div class="text-sm font-semibold text-slate-900">Teacher List</div>

            <div class="col-12" id="id-card">
                <div class="id-card">
                    <template v-for="(teacher, key1) in teachers" :key="'t-' + (teacher.id || key1)">
                        <div class="id-bg">
                            <div class="id-front">
                                <img v-if="frontPart" :src="frontPart" />

                                <p class="college-name">{{ collegeName }}</p>

                                <div class="profile-image">
                                    <img :src="teacher.profile" />
                                </div>

                                <div class="std-name" :style="{ fontSize: getNameFontSize(teacher.name) }">
                                    {{ teacher.name }}
                                </div>

                                <div class="std-designation">
                                    {{ teacher?.teacher?.designation?.name || '' }}
                                </div>

                                <div class="front-info">
                                    <p :style="{ fontSize: teacher?.department?.name && teacher.department.name.length > 20 ? '8.5px' : '10px' }">
                                        {{ teacher?.department?.name || '--' }}
                                    </p>
                                    <p>{{ teacher?.teacher?.index_number || '--' }}</p>
                                    <p>{{ teacher?.teacher?.bcs_batch || '--' }}</p>
                                    <p class="text-danger">{{ teacher?.teacher?.blood_group || '--' }}</p>
                                </div>

                                <div class="principle-signature" v-if="principleSignature">
                                    <img :src="principleSignature" style="height: auto; width: 40px" />
                                </div>

                                <div class="front-footer">
                                    <p>Phone: {{ teacher.mobile || '--' }},</p>
                                    <p style="margin-left: 3px">Emergency Contact: {{ teacher.emergency_contacts || '--' }}</p>
                                </div>
                            </div>

                            <div class="id-back">
                                <img v-if="backPart" :src="backPart" />

                                <div class="back-logo">
                                    <img :src="logo" alt="" />
                                </div>

                                <div class="back-info">
                                    <div class="back-info-text">
                                        <p>{{ collegeName }}</p>
                                        <p><span class="text-success">Phone:</span> {{ collegePhone || '' }}</p>
                                        <p><span class="text-success">Email:</span> {{ collegeEmail || '' }}</p>
                                        <p><span class="text-success">Web:</span> {{ collegeWeb || '' }}</p>
                                    </div>
                                </div>

                                <div v-if="teacher.qr_code" class="qr-code">
                                    <img :src="'data:image/svg+xml;base64,' + teacher.qr_code" />
                                </div>

                                <div class="back-footer">
                                    <p style="font-size: 10px">{{ collegeName }}</p>
                                    <p>{{ collegeAddress || '' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="page-break" v-if="(key1 + 1) % 5 == 0"></div>
                    </template>
                </div>
            </div>

            <div v-if="!teachers.length" class="mt-4 rounded-xl border border-dashed border-slate-300 bg-slate-50 p-5 text-sm text-slate-600">
                No teachers loaded
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'TeacherIDCard',
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
            teachers: [],
            expiredDate: '',
            search: {
                skip: 0,
                take: '',
                field_name: 'name',
                value: '',
                order_by: 'asc',
                department_id: '',
                academic_qualification_id: '',
            },
        }
    },
    computed: {
        frontPart() {
            return this.systems?.site?.teacher_id_card_front || ''
        },
        backPart() {
            return this.systems?.site?.teacher_id_card_back || ''
        },
        principleSignature() {
            return this.systems?.site?.principle_signature || ''
        },
        logo() {
            return this.systems?.site?.logo || ''
        },
        collegeName() {
            return this.systems?.site?.college_name || ''
        },
        collegePhone() {
            return this.systems?.site?.college_phone || ''
        },
        collegeEmail() {
            return this.systems?.site?.college_email || ''
        },
        collegeWeb() {
            return this.systems?.site?.college_web || ''
        },
        collegeAddress() {
            return this.systems?.site?.address || ''
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
            if (!qualificationId) return this.departments

            const allowedDeptIds = new Set(
                this.departmentQualidactions
                    .filter((r) => String(r.academic_qualification_id) === String(qualificationId))
                    .map((r) => String(r.department_id))
            )

            const filtered = this.departments.filter((d) => allowedDeptIds.has(String(d.id)))
            return filtered.length ? filtered : this.departments
        },
    },
    watch: {
        'search.academic_qualification_id'(next, prev) {
            if (String(next || '') !== String(prev || '')) {
                this.search.department_id = ''
            }
        },
    },
    methods: {
        goBack() {
            window.location.href = '/admin/teacher'
        },
        getNameFontSize(name) {
            if (!name) return '12px'
            const length = String(name).length
            if (length > 34) return '7.5px'
            if (length > 30) return '8px'
            if (length > 25) return '8.5px'
            if (length > 20) return '10px'
            return '13px'
        },
        async searchClick() {
            this.loading = true
            this.error = ''
            try {
                const res = await window.axios.get('/admin/teacher-idcard', { params: this.search })
                this.teachers = Array.isArray(res?.data) ? res.data : []
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to search teachers.'
                this.teachers = []
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
 html, body { margin: 0; padding: 0; font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", "Liberation Sans", sans-serif; line-height: 1.5; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
 * { box-sizing: border-box; }
 p { letter-spacing: 0px !important; }
 .text-danger { color: #dc3545 !important; }
 .text-success { color: #198754 !important; }
 .id-card { width: 100%; float: left; position: relative; }
 .id-bg { position: relative; height: 658.5px; width: 208.2px; float: left; clear: right; }
 .id-card img { height: 100%; width: 100%; }
 .id-card .id-front { position: relative; padding: 0px; float: left; height: 328.27px; width: 100%; }
 .id-card .id-front .std-name { position: absolute; top: 186px; text-align: center; margin: auto; width: 100%; font-weight: bold; font-size: 12px; padding: 0px 10px; height: 40px; display: flex; justify-content: center; align-items: center; text-transform: uppercase; color: #0e4c88 !important; }
 .id-card .id-front .front-info { position: absolute; top: 227px; left: 91px; font-size: 10px; color: #000; }
 .id-card .id-front .front-footer { position: absolute; bottom: -4px; font-size: 6px; display: flex; justify-content: center; color: white; width: 100%; }
 .id-card .id-front .front-info p { margin-bottom: -1.8px !important; height: 14.1px; }
 .id-card .id-front .std-designation { position: absolute; top: 213px; width: 100%; text-align: center; font-size: 9px; font-weight: bold; color: green; }
 .id-card .id-front .principle-signature { position: absolute; bottom: 47px; right: 19px; }
 .id-card .id-front .college-name { font-size: 10px; position: absolute; top: 74px; font-family: Bahnschrift; font-weight: bold; color: rgb(242, 44, 44); text-align: center; width: 100%; }
 .id-card .id-front .front-info .dept-name { font-size: 7px; margin-top: 3px; margin-bottom: 0px !important; }
 .id-card .id-front .profile-image img { position: absolute; height: 88.1px; width: 73.8px; top: 107.1px; left: 66.7px; border-radius: 8px; }
 .id-card .id-back { position: relative; height: 100%; padding: 0px; float: left; height: 328.27px; width: 100%; }
 .id-card .id-back .back-info { position: absolute; top: 95px; font-size: 9px; color: #000; text-align: center; width: 100%; line-height: 12px; }
 .id-card .id-back .back-footer { position: absolute; bottom: 12px; left: 10px; font-size: 7.5px; color: white; text-align: center; width: 189px; }
 .id-card .id-back .back-footer p { margin-bottom: 0px !important; }
 .id-card .id-back .back-info-text { margin-top: 10px; }
 .id-card .id-back .back-logo { position: absolute; top: 15px; left: 0; right: 0; width: 100%; text-align: center; }
 .id-card .id-back .back-logo img { width: 32%; display: block; margin: 0 auto; }
 .id-card .id-back .back-info p { margin-bottom: -1px !important; }
 .id-card .id-back .qr-code { position: absolute; left: 65px; bottom: 49px; }
 .id-card .id-back .qr-code img { height: 80px; width: 77px; }
 .expired_date { color: red; font-weight: bold; }
 @media print { .page-break { page-break-after: always; } }
 `
        },
    },
}
</script>

<style>
p {
    letter-spacing: 0px !important;
}

.text-danger {
    color: #dc3545 !important;
}

.text-success {
    color: #198754 !important;
}

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
    top: 186px;
    text-align: center;
    margin: auto;
    width: 100%;
    font-weight: bold;
    font-size: 12px;
    padding: 0px 10px;
    height: 40px;
    display: flex;
    justify-content: center;
    align-items: center;
    text-transform: uppercase;
    color: #0e4c88 !important;
}
.id-card .id-front .front-info {
    position: absolute;
    top: 227px;
    left: 91px;
    font-size: 10px;
    color: #000;
}
.id-card .id-front .front-footer {
    position: absolute;
    bottom: -4px;
    font-size: 6px;
    display: flex;
    justify-content: center;
    color: white;
    width: 100%;
}
.id-card .id-front .front-info p {
    margin-bottom: -1.8px !important;
    height: 14.1px;
}
.id-card .id-front .std-designation {
    position: absolute;
    top: 213px;
    width: 100%;
    text-align: center;
    font-size: 9px;
    font-weight: bold;
    color: green;
}
.id-card .id-front .principle-signature {
    position: absolute;
    bottom: 47px;
    right: 19px;
}
.id-card .id-front .college-name {
    font-size: 10px;
    position: absolute;
    top: 74px;
    font-family: Bahnschrift;
    font-weight: bold;
    color: rgb(242, 44, 44);
    text-align: center;
    width: 100%;
}
.id-card .id-front .front-info .dept-name {
    font-size: 7px;
    margin-top: 3px;
    margin-bottom: 0px !important;
}
.id-card .id-front .profile-image img {
    position: absolute;
    height: 88.1px;
    width: 73.8px;
    top: 107.1px;
    left: 66.7px;
    border-radius: 8px;
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
    top: 95px;
    font-size: 9px;
    color: #000;
    text-align: center;
    width: 100%;
    line-height: 12px;
}
.id-card .id-back .back-footer {
    position: absolute;
    bottom: 12px;
    left: 10px;
    font-size: 7.5px;
    color: white;
    text-align: center;
    width: 189px;
}
.id-card .id-back .back-footer p {
    margin-bottom: 0px !important;
}
.id-card .id-back .back-info-text {
    margin-top: 10px;
}
.id-card .id-back .back-logo {
    position: absolute;
    top: 15px;
    left: 0;
    right: 0;
    width: 100%;
    text-align: center;
}
.id-card .id-back .back-logo img {
    width: 32%;
    display: block;
    margin: 0 auto;
}
.id-card .id-back .back-info p {
    margin-bottom: -1px !important;
}
.id-card .id-back .qr-code {
    position: absolute;
    left: 65px;
    bottom: 49px;
}
.id-card .id-back .qr-code img {
    height: 80px;
    width: 77px;
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
