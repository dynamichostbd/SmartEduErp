<template>
    <div class="flex flex-col gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Site Settings</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button
                        type="button"
                        class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                        :disabled="saving"
                        @click="reload"
                    >
                        Reload
                    </button>
                    <button
                        type="button"
                        class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700"
                        :disabled="saving"
                        @click="save"
                    >
                        {{ saving ? '...' : 'Update' }}
                    </button>
                </div>
            </div>
        </div>

        <div v-if="error" class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ error }}</div>
        <div v-if="success" class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ success }}</div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
            <div class="lg:col-span-6">
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <div class="text-sm font-semibold text-slate-900">Systems Info</div>
                    <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-12">
                        <div class="lg:col-span-6">
                            <div class="text-xs font-semibold text-slate-600">Title</div>
                            <input v-model="form.title" type="text" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>
                        <div class="lg:col-span-6">
                            <div class="text-xs font-semibold text-slate-600">Title (en)</div>
                            <input v-model="form.title_en" type="text" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>
                        <div class="lg:col-span-6">
                            <div class="text-xs font-semibold text-slate-600">Short Title</div>
                            <input v-model="form.short_title" type="text" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>
                        <div class="lg:col-span-6">
                            <div class="text-xs font-semibold text-slate-600">Contact Email</div>
                            <input v-model="form.contact_email" type="email" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>
                        <div class="lg:col-span-6">
                            <div class="text-xs font-semibold text-slate-600">Feedback Email</div>
                            <input v-model="form.feedback_email" type="email" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>
                        <div class="lg:col-span-6">
                            <div class="text-xs font-semibold text-slate-600">Mobile One</div>
                            <input v-model="form.mobile1" type="text" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>
                        <div class="lg:col-span-6">
                            <div class="text-xs font-semibold text-slate-600">Mobile Two</div>
                            <input v-model="form.mobile2" type="text" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>
                        <div class="lg:col-span-6">
                            <div class="text-xs font-semibold text-slate-600">Web</div>
                            <input v-model="form.web" type="text" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>
                        <div class="lg:col-span-6">
                            <div class="text-xs font-semibold text-slate-600">Developed By</div>
                            <input v-model="form.developed_by" type="text" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>
                        <div class="lg:col-span-6">
                            <div class="text-xs font-semibold text-slate-600">Developed By URL</div>
                            <input v-model="form.developed_by_url" type="url" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>
                        <div class="lg:col-span-12">
                            <div class="text-xs font-semibold text-slate-600">Address</div>
                            <textarea v-model="form.address" rows="3" class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"></textarea>
                        </div>
                    </div>
                </div>

                <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-5">
                    <div class="text-sm font-semibold text-slate-900">College Info</div>
                    <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-12">
                        <div class="lg:col-span-6">
                            <div class="text-xs font-semibold text-slate-600">College Name</div>
                            <input v-model="form.college_name" type="text" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>
                        <div class="lg:col-span-6">
                            <div class="text-xs font-semibold text-slate-600">Phone</div>
                            <input v-model="form.college_phone" type="text" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>
                        <div class="lg:col-span-6">
                            <div class="text-xs font-semibold text-slate-600">Email</div>
                            <input v-model="form.college_email" type="email" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>
                        <div class="lg:col-span-6">
                            <div class="text-xs font-semibold text-slate-600">Web</div>
                            <input v-model="form.college_web" type="text" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>
                    </div>
                </div>

                <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-5">
                    <div class="text-sm font-semibold text-slate-900">Images</div>
                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div class="rounded-xl border border-slate-200 p-4">
                            <div class="text-xs font-semibold text-slate-600">Logo</div>
                            <img :src="previews.logo || resolveImage(form.logo)" class="mt-2 h-20 w-auto max-w-full rounded border border-slate-200 bg-white object-contain" alt="logo" />
                            <input type="file" accept="image/*" class="mt-2 block w-full text-sm" @change="onFile('logo', $event)" />
                        </div>
                        <div class="rounded-xl border border-slate-200 p-4">
                            <div class="text-xs font-semibold text-slate-600">Logo Small</div>
                            <img :src="previews.logo_small || resolveImage(form.logo_small)" class="mt-2 h-20 w-auto max-w-full rounded border border-slate-200 bg-white object-contain" alt="logo small" />
                            <input type="file" accept="image/*" class="mt-2 block w-full text-sm" @change="onFile('logo_small', $event)" />
                        </div>
                        <div class="rounded-xl border border-slate-200 p-4">
                            <div class="text-xs font-semibold text-slate-600">Favicon Logo</div>
                            <img :src="previews.favicon || resolveImage(form.favicon)" class="mt-2 h-20 w-20 rounded border border-slate-200 bg-white object-contain" alt="favicon" />
                            <input type="file" accept="image/*" class="mt-2 block w-full text-sm" @change="onFile('favicon', $event)" />
                        </div>

                        <div class="rounded-xl border border-slate-200 p-4">
                            <div class="text-xs font-semibold text-slate-600">ID Card Front</div>
                            <img :src="previews.idcard_front_part || resolveImage(form.idcard_front_part)" class="mt-2 h-24 w-full rounded border border-slate-200 bg-white object-cover" alt="idcard front" />
                            <input type="file" accept="image/*" class="mt-2 block w-full text-sm" @change="onFile('idcard_front_part', $event)" />
                        </div>
                        <div class="rounded-xl border border-slate-200 p-4">
                            <div class="text-xs font-semibold text-slate-600">ID Card Back</div>
                            <img :src="previews.idcard_back_part || resolveImage(form.idcard_back_part)" class="mt-2 h-24 w-full rounded border border-slate-200 bg-white object-cover" alt="idcard back" />
                            <input type="file" accept="image/*" class="mt-2 block w-full text-sm" @change="onFile('idcard_back_part', $event)" />
                        </div>
                        <div class="rounded-xl border border-slate-200 p-4">
                            <div class="text-xs font-semibold text-slate-600">Admit Card Design</div>
                            <img :src="previews.admit_card_image || resolveImage(form.admit_card_image)" class="mt-2 h-24 w-full rounded border border-slate-200 bg-white object-cover" alt="admit" />
                            <input type="file" accept="image/*" class="mt-2 block w-full text-sm" @change="onFile('admit_card_image', $event)" />
                        </div>
                        <div class="rounded-xl border border-slate-200 p-4">
                            <div class="text-xs font-semibold text-slate-600">Marksheet Design</div>
                            <img :src="previews.marksheet_image || resolveImage(form.marksheet_image)" class="mt-2 h-24 w-full rounded border border-slate-200 bg-white object-cover" alt="marksheet" />
                            <input type="file" accept="image/*" class="mt-2 block w-full text-sm" @change="onFile('marksheet_image', $event)" />
                        </div>
                        <div class="rounded-xl border border-slate-200 p-4">
                            <div class="text-xs font-semibold text-slate-600">Online Admission</div>
                            <img :src="previews.online_admission_form_image || resolveImage(form.online_admission_form_image)" class="mt-2 h-24 w-full rounded border border-slate-200 bg-white object-cover" alt="online admission" />
                            <input type="file" accept="image/*" class="mt-2 block w-full text-sm" @change="onFile('online_admission_form_image', $event)" />
                        </div>
                        <div class="rounded-xl border border-slate-200 p-4">
                            <div class="text-xs font-semibold text-slate-600">Teacher ID Card Front</div>
                            <img :src="previews.teacher_id_card_front || resolveImage(form.teacher_id_card_front)" class="mt-2 h-24 w-full rounded border border-slate-200 bg-white object-cover" alt="teacher front" />
                            <input type="file" accept="image/*" class="mt-2 block w-full text-sm" @change="onFile('teacher_id_card_front', $event)" />
                        </div>
                        <div class="rounded-xl border border-slate-200 p-4">
                            <div class="text-xs font-semibold text-slate-600">Teacher ID Card Back</div>
                            <img :src="previews.teacher_id_card_back || resolveImage(form.teacher_id_card_back)" class="mt-2 h-24 w-full rounded border border-slate-200 bg-white object-cover" alt="teacher back" />
                            <input type="file" accept="image/*" class="mt-2 block w-full text-sm" @change="onFile('teacher_id_card_back', $event)" />
                        </div>
                        <div class="rounded-xl border border-slate-200 p-4">
                            <div class="text-xs font-semibold text-slate-600">Principle Signature</div>
                            <img :src="previews.principle_signature || resolveImage(form.principle_signature)" class="mt-2 h-24 w-full rounded border border-slate-200 bg-white object-contain" alt="signature" />
                            <input type="file" accept="image/*" class="mt-2 block w-full text-sm" @change="onFile('principle_signature', $event)" />
                        </div>
                        <div class="rounded-xl border border-slate-200 p-4">
                            <div class="text-xs font-semibold text-slate-600">Admit Card Four In One</div>
                            <img :src="previews.admin_admit_card || resolveImage(form.admin_admit_card)" class="mt-2 h-24 w-full rounded border border-slate-200 bg-white object-cover" alt="four in one" />
                            <input type="file" accept="image/*" class="mt-2 block w-full text-sm" @change="onFile('admin_admit_card', $event)" />
                        </div>
                        <div class="rounded-xl border border-slate-200 p-4">
                            <div class="text-xs font-semibold text-slate-600">Seat Card</div>
                            <img :src="previews.seat_card || resolveImage(form.seat_card)" class="mt-2 h-24 w-full rounded border border-slate-200 bg-white object-cover" alt="seat card" />
                            <input type="file" accept="image/*" class="mt-2 block w-full text-sm" @change="onFile('seat_card', $event)" />
                        </div>
                        <div class="rounded-xl border border-slate-200 p-4">
                            <div class="text-xs font-semibold text-slate-600">Admit Card Two In One (Front)</div>
                            <img :src="previews.admin_admit_card_front || resolveImage(form.admin_admit_card_front)" class="mt-2 h-24 w-full rounded border border-slate-200 bg-white object-cover" alt="two in one front" />
                            <input type="file" accept="image/*" class="mt-2 block w-full text-sm" @change="onFile('admin_admit_card_front', $event)" />
                        </div>
                        <div class="rounded-xl border border-slate-200 p-4">
                            <div class="text-xs font-semibold text-slate-600">Admit Card Two In One (Back)</div>
                            <img :src="previews.admin_admit_card_back || resolveImage(form.admin_admit_card_back)" class="mt-2 h-24 w-full rounded border border-slate-200 bg-white object-cover" alt="two in one back" />
                            <input type="file" accept="image/*" class="mt-2 block w-full text-sm" @change="onFile('admin_admit_card_back', $event)" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-6">
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <div class="text-sm font-semibold text-slate-900">Application Info</div>
                    <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-12">
                        <div class="lg:col-span-6">
                            <div class="text-xs font-semibold text-slate-600">Registration</div>
                            <select v-model="form.registration" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                <option :value="1">OPEN</option>
                                <option :value="0">CLOSE</option>
                            </select>
                        </div>
                        <div class="lg:col-span-6">
                            <div class="text-xs font-semibold text-slate-600">Login Form</div>
                            <select v-model="form.login_form" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                <option :value="1">OPEN</option>
                                <option :value="0">CLOSE</option>
                            </select>
                        </div>
                        <div class="lg:col-span-6">
                            <div class="text-xs font-semibold text-slate-600">Apply Fees</div>
                            <select v-model="form.apply_fees" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                <option :value="1">OPEN</option>
                                <option :value="0">CLOSE</option>
                            </select>
                        </div>
                        <div class="lg:col-span-6">
                            <div class="text-xs font-semibold text-slate-600">Apply Fees Roll Generate</div>
                            <select v-model="form.apply_fees_roll_generate" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                <option :value="1">YES</option>
                                <option :value="0">NO</option>
                            </select>
                        </div>
                        <div class="lg:col-span-6">
                            <div class="text-xs font-semibold text-slate-600">Online Admission</div>
                            <select v-model="form.online_admission" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                <option :value="1">OPEN</option>
                                <option :value="0">CLOSE</option>
                            </select>
                        </div>
                        <div class="lg:col-span-6">
                            <div class="text-xs font-semibold text-slate-600">Service Balance</div>
                            <select v-model="form.service_balance" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                <option :value="1">OPEN</option>
                                <option :value="0">CLOSE</option>
                            </select>
                        </div>
                        <div class="lg:col-span-6">
                            <div class="text-xs font-semibold text-slate-600">App Service Fee</div>
                            <select v-model="form.app_service_fee" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                <option :value="1">OPEN</option>
                                <option :value="0">CLOSE</option>
                            </select>
                        </div>
                        <div v-if="String(form.app_service_fee) === '1'" class="lg:col-span-6">
                            <div class="text-xs font-semibold text-slate-600">App Service Fee Amount</div>
                            <input v-model="form.app_service_fee_amount" type="text" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>
                    </div>
                </div>

                <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-5">
                    <div class="text-sm font-semibold text-slate-900">Payments Info</div>
                    <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-12">
                        <div class="lg:col-span-6">
                            <div class="text-xs font-semibold text-slate-600">SMS Cost</div>
                            <input v-model="form.sms_cost" type="text" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>
                        <div class="lg:col-span-6">
                            <div class="text-xs font-semibold text-slate-600">Fine Amount</div>
                            <input v-model="form.fine_amount" type="text" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>
                        <div class="lg:col-span-6">
                            <div class="text-xs font-semibold text-slate-600">Service Charge (%)</div>
                            <input v-model="form.service_charge_percent" type="text" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>
                        <div class="lg:col-span-6">
                            <div class="text-xs font-semibold text-slate-600">Hostel Service Charge (%)</div>
                            <input v-model="form.hostel_service_charge_percent" type="text" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>
                        <div class="lg:col-span-6">
                            <div class="text-xs font-semibold text-slate-600">Admission Fee Charge (%)</div>
                            <input v-model="form.admission_fees_charge_percent" type="text" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>
                        <div class="lg:col-span-6">
                            <div class="text-xs font-semibold text-slate-600">Admission Fee Gateway (%)</div>
                            <input v-model="form.admission_fees_gateway_percent" type="text" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>
                        <div class="lg:col-span-6">
                            <div class="text-xs font-semibold text-slate-600">Admission Fee College (%)</div>
                            <input v-model="form.admission_fees_college_percent" type="text" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'SiteSettingEdit',
    props: {
        systems: {
            type: Object,
            default: () => ({}),
        },
        settingId: {
            type: [Number, String],
            default: 1,
        },
    },
    data() {
        return {
            loading: false,
            saving: false,
            error: '',
            success: '',
            files: {},
            previews: {},
            form: {
                title: '',
                title_en: '',
                short_title: '',
                contact_email: '',
                feedback_email: '',
                mobile1: '',
                mobile2: '',
                address: '',
                web: '',
                developed_by: '',
                developed_by_url: '',

                college_name: '',
                college_phone: '',
                college_email: '',
                college_web: '',

                logo: '',
                logo_small: '',
                favicon: '',
                idcard_front_part: '',
                idcard_back_part: '',
                admit_card_image: '',
                marksheet_image: '',
                online_admission_form_image: '',
                teacher_id_card_front: '',
                teacher_id_card_back: '',
                admin_admit_card_front: '',
                admin_admit_card_back: '',
                seat_card: '',
                principle_signature: '',

                admin_admit_card: '',

                registration: 0,
                login_form: 0,
                apply_fees: 0,
                apply_fees_roll_generate: 0,
                online_admission: 0,
                service_balance: 0,
                app_service_fee: 0,
                app_service_fee_amount: '',

                sms_cost: '',
                fine_amount: '',
                service_charge_percent: '',
                hostel_service_charge_percent: '',
                admission_fees_charge_percent: '',
                admission_fees_gateway_percent: '',
                admission_fees_college_percent: '',
            },
        }
    },
    created() {
        this.reload()
    },
    methods: {
        toast(message, type = 'success') {
            window.dispatchEvent(new CustomEvent('app-toast', { detail: { message, type } }))
        },
        resolveImage(v) {
            if (!v) return ''
            if (String(v).match(/^https?:\/\//i)) return v
            const path = String(v)
                .replace(/^storage\//i, '')
                .replace(/^upload\//i, '')
                .replace(/^public\//i, '')
                .replace(/^\//, '')
            return `/storage/${path}`
        },
        onFile(key, e) {
            const f = e?.target?.files?.[0] || null
            if (!f) return
            this.files[key] = f
            this.previews[key] = URL.createObjectURL(f)
        },
        async reload() {
            this.loading = true
            this.error = ''
            this.success = ''
            try {
                const res = await window.axios.get(`/admin/siteSetting/${this.settingId}`)
                const s = res?.data || {}

                Object.keys(this.form).forEach((k) => {
                    if (k in s) this.form[k] = s[k] ?? ''
                })
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load site settings.'
            } finally {
                this.loading = false
            }
        },
        async save() {
            this.saving = true
            this.error = ''
            this.success = ''

            try {
                const fd = new FormData()
                Object.keys(this.form).forEach((k) => {
                    const v = this.form[k]
                    if (v === null || v === undefined) return
                    fd.append(k, String(v))
                })
                Object.keys(this.files).forEach((k) => {
                    if (this.files[k]) fd.append(k, this.files[k])
                })

                const res = await window.axios.post(`/admin/siteSetting/${this.settingId}?_method=PUT`, fd, {
                    headers: { 'Content-Type': 'multipart/form-data' },
                })

                const m = res?.data?.message || 'Update Successfully!'
                this.success = m
                this.toast(m, 'success')

                await this.reload()
            } catch (e) {
                this.error = e?.response?.data?.message || 'Update failed.'
            } finally {
                this.saving = false
            }
        },
    },
}
</script>
