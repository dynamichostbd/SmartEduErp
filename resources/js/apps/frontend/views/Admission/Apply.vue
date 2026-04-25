<template>
    <div class="mt-3 rounded-sm border border-slate-300 bg-white p-4 sm:p-6">
        <div class="mx-auto w-full max-w-6xl">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <div class="text-xl font-extrabold text-slate-900 sm:text-2xl">STUDENT PAYMENT</div>
                    <div class="mt-2 h-[2px] w-full max-w-3xl bg-red-500"></div>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" class="rounded-t-md border border-b-0 px-4 py-2 text-sm font-extrabold"
                        :class="app.applyFeesTab === 'fees' ? 'border-blue-500 bg-white text-blue-700' : 'border-slate-300 bg-slate-50 text-slate-600 hover:bg-white'"
                        @click="app.applyFeesTab = 'fees'">
                        APPLICATION FEES
                    </button>
                    <button type="button" class="rounded-t-md border border-b-0 px-4 py-2 text-sm font-extrabold"
                        :class="app.applyFeesTab === 'certificate' ? 'border-blue-500 bg-white text-blue-700' : 'border-slate-300 bg-slate-50 text-slate-600 hover:bg-white'"
                        @click="app.applyFeesTab = 'certificate'">
                        APPLY FOR CERTIFICATE
                    </button>
                    <button type="button" class="rounded-t-md border border-b-0 px-4 py-2 text-sm font-extrabold"
                        :class="app.applyFeesTab === 'invoice' ? 'border-blue-500 bg-white text-blue-700' : 'border-slate-300 bg-slate-50 text-slate-600 hover:bg-white'"
                        @click="app.applyFeesTab = 'invoice'">
                        CHECK INVOICE
                    </button>
                </div>
            </div>

            <div class="mt-4 rounded-sm border border-slate-300 bg-white p-4 sm:p-6">

                <div v-if="app.applyFeesStatusMessage" class="mt-6 rounded-sm border p-3 text-sm"
                    :class="app.applyFeesStatusClass">
                    {{ app.applyFeesStatusMessage }}
                    <span v-if="app.applyFeesTranId" class="font-semibold"> ({{ app.applyFeesTranId }})</span>
                </div>

                <div v-if="app.applyFeesTab === 'fees'">
                    <div v-if="app.applyFeesLoading" class="mt-6 text-sm text-slate-600">Loading...</div>
                    <div v-else>
                        <div v-if="app.applyFeesError"
                            class="mt-6 rounded-sm border border-red-200 bg-red-50 p-3 text-sm text-red-800">
                            {{ app.applyFeesError }}
                        </div>

                        <div v-if="!selectedFeeId" class="mt-6">
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                <div v-for="p in app.applyFeesPurposes" :key="'fee-card-' + p.id"
                                    class="flex flex-col justify-between rounded-xl border border-slate-200 bg-white p-5 shadow-md transition hover:shadow-lg transform hover:scale-102">
                                    <div>
                                        <div class="text-lg font-extrabold text-[#0d6b75]">{{ p?.head?.name || 'Fee' }}
                                        </div>
                                        <div class="mt-2 text-2xl font-bold text-slate-900">{{ Number(p?.amount ||
                                            0).toFixed(2) }} <span class="text-sm font-bold text-slate-900">BDT</span>
                                        </div>
                                    </div>
                                    <button type="button" @click="proceedToPay(p.id)"
                                        class="mt-4 ml-auto w-[150px] flex items-center justify-center gap-2 rounded-md bg-gradient-to-r from-[#0d6b75] to-[#12919e] py-2.5 text-sm font-bold text-white shadow-md transition-transform hover:-translate-y-0.5 hover:shadow-lg">
                                        Proceed to Pay <i class="fa-solid fa-arrow-right text-xs"></i>
                                    </button>
                                </div>
                            </div>
                            <div v-if="!app.applyFeesPurposes || app.applyFeesPurposes.length === 0"
                                class="text-sm text-slate-600">
                                No fees available.
                            </div>
                        </div>

                        <div v-else class="mt-6">
                            <div class="mb-4">
                                <button type="button" @click="selectedFeeId = null"
                                    class="text-sm font-bold text-[#0d6b75] hover:underline">← Back to Fees</button>
                            </div>
                            <form @submit.prevent="app.submitApplyFees">
                                <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                                    <div class="space-y-4">
                                        <input v-model="app.applyFeesForm.name" type="text" placeholder="Full Name"
                                            class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10"
                                            required />
                                        <input v-model="app.applyFeesForm.mobile" type="text" placeholder="Mobile"
                                            class="h-10 w-full rounded-sm border border-slate-300 bg-[#eaf2ff] px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10"
                                            required />
                                        <input v-model="app.applyFeesForm.admission_roll" type="text"
                                            placeholder="Application ID / Admission Roll"
                                            class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10"
                                            required />
                                    </div>

                                    <div class="space-y-4">
                                        <select v-model="app.applyFeesForm.academic_session_id"
                                            class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10"
                                            required>
                                            <option value="">Session</option>
                                            <option v-for="s in app.applyFeesSessionsSorted" :key="'afs-' + s.id"
                                                :value="String(s.id)">{{ s.name }}</option>
                                        </select>

                                        <select v-model="app.applyFeesForm.academic_qualification_id"
                                            class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10"
                                            required>
                                            <option value="">Academic Level</option>
                                            <option v-for="q in app.applyFeesQualifications" :key="'afq-' + q.id"
                                                :value="String(q.id)">{{ q.name }}</option>
                                        </select>

                                        <select v-model="app.applyFeesForm.department_id"
                                            :disabled="!app.applyFeesForm.academic_qualification_id"
                                            class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10 disabled:bg-slate-50 disabled:text-slate-400"
                                            required>
                                            <option value="">Department/Group</option>
                                            <option v-for="d in app.applyFeesFilteredDepartments" :key="'afd-' + d.id"
                                                :value="String(d.id)">{{ d.name }}</option>
                                        </select>

                                        <select v-model="app.applyFeesForm.academic_class_id"
                                            :disabled="!app.applyFeesForm.academic_qualification_id"
                                            class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10 disabled:bg-slate-50 disabled:text-slate-400"
                                            required>
                                            <option value="">Class</option>
                                            <option v-for="c in app.applyFeesFilteredClasses" :key="'afc-' + c.id"
                                                :value="String(c.id)">{{ c.name }}</option>
                                        </select>
                                    </div>

                                    <div class="space-y-4">
                                        <select v-model="app.applyFeesForm.purpose_id"
                                            class="h-10 w-full rounded-sm border border-slate-300 bg-slate-100 px-3 text-sm outline-none"
                                            disabled required>
                                            <option value="">Fees</option>
                                            <option v-for="p in app.applyFeesPurposes" :key="'afp-' + p.id"
                                                :value="String(p.id)">{{ p?.head?.name || '' }}</option>
                                        </select>

                                        <div
                                            class="rounded-sm border border-slate-300 bg-slate-50 p-4 text-sm text-slate-700">
                                            <div class="font-semibold">Amount</div>
                                            <div class="mt-1 text-lg font-extrabold text-slate-900">{{
                                                app.applyFeesSelectedAmountText }}</div>
                                        </div>

                                        <div v-if="app.applyFeesInvoiceError"
                                            class="rounded-sm border border-red-200 bg-red-50 p-3 text-sm text-red-800">
                                            {{ app.applyFeesInvoiceError }}
                                        </div>
                                        <div v-else-if="app.applyFeesInvoice"
                                            class="rounded-sm border border-slate-300 bg-white p-3 text-sm text-slate-700">
                                            <div class="font-semibold text-slate-900">Invoice: {{
                                                app.applyFeesInvoice.invoice_number || '—' }}</div>
                                            <div class="mt-1">Status: <span class="font-semibold">{{
                                                app.applyFeesInvoice.status || '—' }}</span></div>
                                            <div class="mt-1">Amount: <span class="font-semibold">{{
                                                Number(app.applyFeesInvoice.amount || 0).toFixed(2) }}</span></div>
                                            <button
                                                v-if="String(app.applyFeesInvoice.status || '') !== 'success' && app.applyFeesInvoice.invoice_number"
                                                type="button"
                                                class="mt-3 rounded-sm bg-[#0b1d4d] px-4 py-2 text-xs font-extrabold text-white hover:bg-[#09163c]"
                                                :disabled="app.applyFeesPayExistingLoading"
                                                @click="app.payApplyFeesExisting">
                                                {{ app.applyFeesPayExistingLoading ? 'Processing...' : 'Pay This Invoice' }}
                                            </button>
                                        </div>

                                        <div class="mt-4 flex items-center justify-end">
                                            <button type="submit"
                                                class="rounded-sm bg-[#0b1d4d] px-10 py-2.5 text-sm font-extrabold text-white hover:bg-[#09163c]"
                                                :disabled="app.applyFeesSubmitting">
                                                {{ app.applyFeesSubmitting ? 'Processing...' : 'Pay Now' }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div v-else-if="app.applyFeesTab === 'certificate'">
                    <div v-if="app.certificateLoading" class="mt-6 text-sm text-slate-600">Loading...</div>
                    <div v-else>
                        <div v-if="app.certificateError"
                            class="mt-6 rounded-sm border border-red-200 bg-red-50 p-3 text-sm text-red-800">
                            {{ app.certificateError }}
                        </div>

                        <form class="mt-8" @submit.prevent="app.submitCertificate">
                            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                                <div class="rounded-sm border border-slate-300 bg-white p-4">
                                    <div
                                        class="border-l-4 border-orange-500 pl-3 text-sm font-extrabold text-slate-900">
                                        STUDENT INFO</div>

                                    <div class="mt-4 flex items-center gap-2">
                                        <input v-model="app.certificateForm.mobile" type="text" placeholder="Mobile"
                                            class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10"
                                            required />
                                        <button type="button"
                                            class="h-10 w-12 rounded-sm border border-slate-300 bg-slate-50 text-sm font-extrabold text-slate-700 hover:bg-white"
                                            :disabled="app.certificateLookupLoading" @click="app.certificateLookup">
                                            {{ app.certificateLookupLoading ? '…' : 'Go' }}
                                        </button>
                                    </div>
                                    <div v-if="app.certificateLookupError"
                                        class="mt-3 rounded-sm border border-red-200 bg-red-50 p-2 text-xs text-red-800">
                                        {{ app.certificateLookupError }}</div>

                                    <div class="mt-4 text-sm font-semibold text-slate-800">Certificate Type :</div>
                                    <div class="mt-2 flex flex-wrap items-center gap-4 text-sm text-slate-700">
                                        <label class="inline-flex items-center gap-2">
                                            <input v-model="app.certificateForm.certificate_type" type="radio"
                                                name="certificate_type" value="en" />
                                            English
                                        </label>
                                        <label class="inline-flex items-center gap-2">
                                            <input v-model="app.certificateForm.certificate_type" type="radio"
                                                name="certificate_type" value="bn" />
                                            Bangla
                                        </label>
                                        <label class="inline-flex items-center gap-2">
                                            <input v-model="app.certificateForm.certificate_type" type="radio"
                                                name="certificate_type" value="both" />
                                            Both
                                        </label>
                                    </div>

                                    <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                        <input v-model="app.certificateForm.student_name_en" type="text"
                                            placeholder="Student name (EN)"
                                            class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10"
                                            :disabled="app.certificateEnDisabled"
                                            :required="!app.certificateEnDisabled" />
                                        <input v-model="app.certificateForm.student_name_bn" type="text"
                                            placeholder="Student name (BN)"
                                            class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10"
                                            :disabled="app.certificateBnDisabled"
                                            :required="!app.certificateBnDisabled" />

                                        <input v-model="app.certificateForm.fathers_name_en" type="text"
                                            placeholder="Father's name (EN)"
                                            class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10"
                                            :disabled="app.certificateEnDisabled"
                                            :required="!app.certificateEnDisabled" />
                                        <input v-model="app.certificateForm.fathers_name_bn" type="text"
                                            placeholder="Father's name (BN)"
                                            class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10"
                                            :disabled="app.certificateBnDisabled"
                                            :required="!app.certificateBnDisabled" />

                                        <input v-model="app.certificateForm.mothers_name_en" type="text"
                                            placeholder="Mother's name (EN)"
                                            class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10"
                                            :disabled="app.certificateEnDisabled"
                                            :required="!app.certificateEnDisabled" />
                                        <input v-model="app.certificateForm.mothers_name_bn" type="text"
                                            placeholder="Mother's name (BN)"
                                            class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10"
                                            :disabled="app.certificateBnDisabled"
                                            :required="!app.certificateBnDisabled" />

                                        <input v-model="app.certificateForm.academic_year_en" type="text"
                                            placeholder="Academic year (EN)"
                                            class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10"
                                            :disabled="app.certificateEnDisabled"
                                            :required="!app.certificateEnDisabled" />
                                        <input v-model="app.certificateForm.academic_year_bn" type="text"
                                            placeholder="Academic year (BN)"
                                            class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10"
                                            :disabled="app.certificateBnDisabled"
                                            :required="!app.certificateBnDisabled" />

                                        <input v-model="app.certificateForm.registration_no_en" type="text"
                                            placeholder="Registration no (EN)"
                                            class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10"
                                            :disabled="app.certificateEnDisabled"
                                            :required="!app.certificateEnDisabled" />
                                        <input v-model="app.certificateForm.registration_no_bn" type="text"
                                            placeholder="Registration no (BN)"
                                            class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10"
                                            :disabled="app.certificateBnDisabled"
                                            :required="!app.certificateBnDisabled" />

                                        <input v-model="app.certificateForm.exam_roll_en" type="text"
                                            placeholder="Exam roll (EN)"
                                            class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10"
                                            :disabled="app.certificateEnDisabled"
                                            :required="!app.certificateEnDisabled" />
                                        <input v-model="app.certificateForm.exam_roll_bn" type="text"
                                            placeholder="Exam roll (BN)"
                                            class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10"
                                            :disabled="app.certificateBnDisabled"
                                            :required="!app.certificateBnDisabled" />

                                        <input v-model="app.certificateForm.exam_year_en" type="text"
                                            placeholder="Exam year (EN)"
                                            class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10"
                                            :disabled="app.certificateEnDisabled"
                                            :required="!app.certificateEnDisabled" />
                                        <input v-model="app.certificateForm.exam_year_bn" type="text"
                                            placeholder="Exam year (BN)"
                                            class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10"
                                            :disabled="app.certificateBnDisabled"
                                            :required="!app.certificateBnDisabled" />

                                        <input v-model="app.certificateForm.gpa_en" type="text" placeholder="GPA (EN)"
                                            class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10"
                                            :disabled="app.certificateEnDisabled"
                                            :required="!app.certificateEnDisabled" />
                                        <input v-model="app.certificateForm.gpa_bn" type="text" placeholder="GPA (BN)"
                                            class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10"
                                            :disabled="app.certificateBnDisabled"
                                            :required="!app.certificateBnDisabled" />

                                        <input v-model="app.certificateForm.division_en" type="text"
                                            placeholder="Division (EN)"
                                            class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10"
                                            :disabled="app.certificateEnDisabled"
                                            :required="!app.certificateEnDisabled" />
                                        <input v-model="app.certificateForm.division_bn" type="text"
                                            placeholder="Division (BN)"
                                            class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10"
                                            :disabled="app.certificateBnDisabled"
                                            :required="!app.certificateBnDisabled" />
                                    </div>
                                </div>

                                <div class="rounded-sm border border-slate-300 bg-white p-4">
                                    <div
                                        class="border-l-4 border-orange-500 pl-3 text-sm font-extrabold text-slate-900">
                                        APPLY FOR CERTIFICATE</div>

                                    <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                        <select v-model="app.certificateForm.academic_session_id"
                                            class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10"
                                            required>
                                            <option value="">--Select Session--</option>
                                            <option v-for="s in app.certificateSessionsSorted" :key="'cs-' + s.id"
                                                :value="String(s.id)">{{ s.name }}</option>
                                        </select>

                                        <select v-model="app.certificateForm.template_id"
                                            class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10"
                                            required>
                                            <option value="">--Select Certificate--</option>
                                            <option v-for="t in app.certificateFilteredTemplates" :key="'ct-' + t.id"
                                                :value="String(t.id)">{{ t.title }}</option>
                                        </select>

                                        <select v-model="app.certificateForm.academic_qualification_id"
                                            class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10"
                                            required>
                                            <option value="">--Select Academic Level--</option>
                                            <option v-for="q in app.certificateQualifications" :key="'cq-' + q.id"
                                                :value="String(q.id)">{{ q.name }}</option>
                                        </select>

                                        <select v-model="app.certificateForm.department_id"
                                            class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10"
                                            :required="app.certificateDepartmentRequired">
                                            <option value="">--Select Department/Group--</option>
                                            <option v-for="d in app.certificateFilteredDepartments" :key="'cd-' + d.id"
                                                :value="String(d.id)">{{ d.name }}</option>
                                        </select>
                                    </div>

                                    <div v-if="app.certificateClasses && app.certificateClasses.length" class="mt-3">
                                        <select v-model="app.certificateForm.academic_class_id"
                                            class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10"
                                            :required="app.certificateClassRequired">
                                            <option value="">--Select Class--</option>
                                            <option v-for="c in app.certificateFilteredClasses" :key="'cc-' + c.id"
                                                :value="String(c.id)">{{ c.name }}</option>
                                        </select>
                                    </div>

                                    <div
                                        class="mt-4 rounded-sm border border-slate-300 bg-slate-50 p-4 text-sm text-slate-700">
                                        <div class="font-semibold">Amount</div>
                                        <div class="mt-1 text-lg font-extrabold text-slate-900">{{
                                            app.certificateSelectedAmountText }}</div>
                                    </div>

                                    <div class="mt-6 text-center">
                                        <button type="submit"
                                            class="rounded-sm bg-[#0b1d4d] px-10 py-2.5 text-sm font-extrabold text-white hover:bg-[#09163c]"
                                            :disabled="app.certificateSubmitting">
                                            {{ app.certificateSubmitting ? 'Submitting...' : 'SUBMIT' }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div v-else-if="app.applyFeesTab === 'invoice'">
                    <div
                        class="mt-6 flex flex-col md:flex-row min-h-[400px] rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                        <!-- Left Side -->
                        <div
                            class="w-full md:w-2/5 bg-[#f8fafc] p-8 sm:p-12 flex flex-col items-center justify-center relative border-b md:border-b-0 md:border-r border-slate-200">
                            <div class="mb-8 relative z-10">
                                <svg width="120" height="120" viewBox="0 0 120 120" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="20" cy="20" r="4" fill="#69b4bd" opacity="0.5" />
                                    <circle cx="100" cy="90" r="6" fill="#12919e" opacity="0.3" />
                                    <path d="M10 80 L15 75 L20 80 L15 85 Z" fill="#0d6b75" opacity="0.4" />
                                    <path
                                        d="M40 25 H80 V85 L75 80 L70 85 L65 80 L60 85 L55 80 L50 85 L45 80 L40 85 V25 Z"
                                        fill="#ffffff" stroke="#0d6b75" stroke-width="4" stroke-linejoin="round" />
                                    <line x1="50" y1="40" x2="70" y2="40" stroke="#e2e8f0" stroke-width="3"
                                        stroke-linecap="round" />
                                    <line x1="50" y1="50" x2="65" y2="50" stroke="#e2e8f0" stroke-width="3"
                                        stroke-linecap="round" />
                                    <line x1="50" y1="60" x2="60" y2="60" stroke="#e2e8f0" stroke-width="3"
                                        stroke-linecap="round" />
                                    <circle cx="65" cy="70" r="12" fill="#0d6b75" />
                                    <path d="M61 70 L64 73 L69 67" stroke="#ffffff" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>
                            <h2 class="text-3xl font-extrabold text-slate-800 mb-4 z-10 text-center tracking-tight">
                                Check <br />
                                <span
                                    class="bg-gradient-to-r from-[#0d6b75] to-[#20a3b2] bg-clip-text text-transparent">Invoice</span>
                            </h2>
                            <div class="h-1.5 w-16 bg-gradient-to-r from-[#0d6b75] to-[#20a3b2] rounded-full z-10 mb-5">
                            </div>
                            <p class="text-sm text-slate-500 z-10 text-center leading-relaxed">
                                Select invoice type and enter your details to verify status.
                            </p>
                        </div>

                        <!-- Right Side - Form -->
                        <div class="w-full md:w-3/5 p-8 sm:p-10 flex flex-col justify-center bg-white">
                            <div class="mb-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <label
                                    class="flex items-center gap-4 cursor-pointer rounded-xl border-2 p-4 transition-all"
                                    :class="invoiceCheckType === 'application' ? 'border-[#0d6b75] bg-[#0d6b75]/5 shadow-sm' : 'border-slate-200 bg-white hover:border-[#0d6b75]/40'">
                                    <input type="radio" value="application" v-model="invoiceCheckType"
                                        class="accent-[#0d6b75] w-4 h-4 shrink-0" />
                                    <div>
                                        <div class="font-bold text-slate-800 text-sm">Application Fee</div>
                                        <div class="text-xs text-slate-500 mt-0.5">Check admission application invoice
                                        </div>
                                    </div>
                                    <div class="ml-auto shrink-0 w-9 h-9 flex items-center justify-center rounded-full"
                                        :class="invoiceCheckType === 'application' ? 'bg-[#0d6b75] text-white' : 'bg-slate-100 text-slate-400'">
                                        <i class="fa-solid fa-file-invoice text-sm"></i>
                                    </div>
                                </label>
                                <label
                                    class="flex items-center gap-4 cursor-pointer rounded-xl border-2 p-4 transition-all"
                                    :class="invoiceCheckType === 'certificate' ? 'border-[#0d6b75] bg-[#0d6b75]/5 shadow-sm' : 'border-slate-200 bg-white hover:border-[#0d6b75]/40'">
                                    <input type="radio" value="certificate" v-model="invoiceCheckType"
                                        class="accent-[#0d6b75] w-4 h-4 shrink-0" />
                                    <div>
                                        <div class="font-bold text-slate-800 text-sm">Certificate Fee</div>
                                        <div class="text-xs text-slate-500 mt-0.5">Check certificate application invoice
                                        </div>
                                    </div>
                                    <div class="ml-auto shrink-0 w-9 h-9 flex items-center justify-center rounded-full"
                                        :class="invoiceCheckType === 'certificate' ? 'bg-[#0d6b75] text-white' : 'bg-slate-100 text-slate-400'">
                                        <i class="fa-solid fa-certificate text-sm"></i>
                                    </div>
                                </label>
                            </div>

                            <div v-if="invoiceCheckType" class="space-y-4">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="relative group">
                                        <div
                                            class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-[#0d6b75]/60">
                                            <i class="fa-solid fa-phone text-lg"></i>
                                        </div>
                                        <input v-model="invoiceCheckMobile" type="text" placeholder="Mobile Number"
                                            class="h-12 w-full rounded-md border border-slate-300 bg-white pl-11 pr-4 text-sm outline-none focus:border-[#0d6b75] focus:ring-1 focus:ring-[#0d6b75]" />
                                    </div>
                                    <div class="relative group">
                                        <div
                                            class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-[#0d6b75]/60">
                                            <i class="fa-regular fa-user text-lg"></i>
                                        </div>
                                        <input v-model="invoiceCheckRoll" type="text"
                                            placeholder="Admission Roll / Reg No"
                                            class="h-12 w-full rounded-md border border-slate-300 bg-white pl-11 pr-4 text-sm outline-none focus:border-[#0d6b75] focus:ring-1 focus:ring-[#0d6b75]" />
                                    </div>
                                </div>

                                <button @click="handleInvoiceCheck" type="button"
                                    class="w-full sm:w-auto mt-2 rounded-md bg-gradient-to-r from-[#0d6b75] to-[#12919e] px-8 py-3 text-sm font-bold text-white shadow-md hover:shadow-lg transition-transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
                                    <span v-if="invoiceChecking"><i class="fa-solid fa-spinner fa-spin"></i>
                                        Checking...</span>
                                    <span v-else><i class="fa-solid fa-magnifying-glass"></i> Check Invoice</span>
                                </button>

                                <div v-if="invoiceCheckError"
                                    class="mt-4 rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-800 flex items-center gap-2">
                                    <i class="fa-solid fa-circle-exclamation"></i> {{ invoiceCheckError }}
                                </div>
                            </div>
                            <div v-else class="flex flex-col items-center justify-center py-10 text-slate-400">
                                <i class="fa-solid fa-arrow-up text-2xl mb-2 animate-bounce"></i>
                                <p class="text-sm font-medium">Please select an invoice type above</p>
                            </div>
                        </div>
                    </div>

                    <!-- Invoice Result: full-width below the card -->
                    <div v-if="invoiceResult" class="mt-6">
                        <!-- Action Buttons row -->
                        <div class="flex items-center justify-between mb-4 flex-wrap gap-2 px-1">
                            <div class="flex items-center gap-2 text-sm font-semibold text-slate-600">
                                <i class="fa-solid fa-file-invoice text-[#0d6b75]"></i> Invoice Found
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="printInvoice"
                                    class="flex items-center gap-2 rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 shadow-sm transition">
                                    <i class="fa-solid fa-print"></i> Print
                                </button>
                            </div>
                        </div>

                        <!-- Two invoice copies — stacked vertically like admin panel -->
                        <div id="invoicePrintArea" class="w-[60%] mx-auto">
                            <div v-for="copy in 2" :key="copy"
                                class="invoice mb-4 border border-slate-300 bg-white p-4">

                                <!-- Header: logo left, title + dates right (grid-cols-12) -->
                                <div class="grid grid-cols-12 gap-4 border-b-2 border-slate-300 pb-2">
                                    <div class="col-span-3">
                                                <img v-if="app.site?.logo"
                                                    :src="app.site.logo?.startsWith('http') ? app.site.logo : ((laravel?.baseurl || '').replace(/\/$/, '')) + '/storage/' + (app.site.logo || '').replace(/^\//, '')"
                                                    class="h-[75px] w-auto object-contain" />
                                    </div>
                                    <div class="col-span-9">
                                        <div class="text-lg font-semibold text-slate-900">{{ app.collegeName ||
                                            '' }}</div>
                                        <div class="mt-1 text-right text-xs font-semibold text-slate-800">
                                            <template v-if="invoiceResult.payment_date">
                                                <span class="text-slate-500 underline"
                                                    style="font-size:10px">PAYMENT:</span>
                                                <span class="ml-1">{{ invoiceResult.payment_date }}</span>
                                                <span class="mx-2">|</span>
                                            </template>
                                            <span class="text-slate-500 underline"
                                                style="font-size:10px">CREATED:</span>
                                            <span class="ml-1">{{ invoiceResult.invoice_date }}</span>
                                            <span class="mx-2">|</span>
                                            <span>{{ invoiceResult.invoice_number }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Student info (col-span-7) + Paid stamp (col-span-5) -->
                                <div class="mt-3 grid grid-cols-12 gap-4">
                                    <div class="col-span-7 text-sm text-slate-800">
                                        <div><span class="font-semibold">Name :</span> {{ invoiceResult.name || '—' }}
                                        </div>
                                        <div><span class="font-semibold">Mobile :</span> {{ invoiceResult.mobile || '—'
                                        }}</div>
                                        <div class="mt-2">
                                            <span class="font-semibold">Level :</span> {{
                                                invoiceResult.qualification_name || '' }}
                                            <span v-if="invoiceResult.session_name">({{ invoiceResult.session_name
                                            }})</span>
                                        </div>
                                        <div>
                                            <span class="font-semibold">Dept. :</span> {{ invoiceResult.department_name
                                                || '' }}
                                            <span v-if="invoiceResult.class_name">({{ invoiceResult.class_name
                                            }})</span>
                                        </div>
                                        <div v-if="invoiceResult.admission_roll" class="mt-2">
                                            <div><span class="font-semibold">Admission Roll :</span> {{
                                                invoiceResult.admission_roll }}</div>
                                        </div>
                                    </div>
                                    <div class="col-span-5 flex items-center justify-center">
                                        <img v-if="['success', 'paid'].includes(String(invoiceResult.status || '').toLowerCase().trim())"
                                            :src="((laravel?.baseurl || '').replace(/\/$/, '')) + '/images/paid.png'"
                                            class="h-[120px] w-[120px] object-contain" />
                                    </div>
                                </div>

                                <!-- EPAYMENT INVOICE label -->
                                <div class="mt-4 text-center text-base font-semibold text-slate-900">EPAYMENT INVOICE
                                </div>

                                <!-- Fee table -->
                                <div class="mt-3 overflow-hidden rounded-xl border border-slate-300">
                                    <table class="w-full border-collapse text-sm">
                                        <thead class="bg-emerald-100">
                                            <tr>
                                                <th class="border border-slate-300 px-3 py-2 text-center font-semibold"
                                                    style="width:90px">Sl No.</th>
                                                <th class="border border-slate-300 px-3 py-2 text-left font-semibold">
                                                    Description</th>
                                                <th class="border border-slate-300 px-3 py-2 text-right font-semibold"
                                                    style="width:130px">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white">
                                            <tr>
                                                <td class="border border-slate-300 px-3 py-2 text-center">01</td>
                                                <td class="border border-slate-300 px-3 py-2">{{ invoiceResult.head_name
                                                    || (invoiceCheckType ===
                                                        'certificate' ? 'Certificate Fee' : 'Application Fee') }}</td>
                                                <td class="border border-slate-300 px-3 py-2 text-right font-semibold">
                                                    {{ Number(invoiceResult.amount ||
                                                        0).toLocaleString(undefined, { minimumFractionDigits: 2 }) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Inword -->
                                <div class="mt-3 text-xs text-slate-700">
                                    <span class="font-semibold">Inword :</span> {{ Number(invoiceResult.amount ||
                                        0).toFixed(2) }} taka only
                                </div>

                                <div class="mt-6 text-center text-xs text-slate-500">Powered By Dynamic Host BD</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



</template>

<script setup>
import { inject, ref } from 'vue'
const app = inject('app')
const laravel = window.laravel

const selectedFeeId = ref(null)

const proceedToPay = (feeId) => {
    app.applyFeesForm.purpose_id = String(feeId)
    selectedFeeId.value = String(feeId)
}

const invoiceCheckType = ref('')
const invoiceCheckMobile = ref('')
const invoiceCheckRoll = ref('')
const invoiceChecking = ref(false)
const invoiceCheckError = ref('')
const invoiceResult = ref(null)
const invoicePaying = ref(false)

const printInvoice = () => {
    const el = document.getElementById('invoicePrintArea')
    if (!el) return
    const win = window.open('', '_blank', 'width=900,height=800')
    win.document.write(`
        <html>
            <head>
                <title>Invoice Print</title>
                <script src="https://cdn.tailwindcss.com"><\/script>
                <style>
                    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
                    body { font-family: 'Inter', sans-serif; }
                    @media print {
                        @page { margin: 5mm; }
                        body { margin: 0; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                    }
                    /* Ensure grid works in print */
                    .grid { display: grid !important; }
                    .grid-cols-12 { grid-template-columns: repeat(12, minmax(0, 1fr)) !important; }
                    .col-span-3 { grid-column: span 3 / span 3 !important; }
                    .col-span-9 { grid-column: span 9 / span 9 !important; }
                    .col-span-7 { grid-column: span 7 / span 7 !important; }
                    .col-span-5 { grid-column: span 5 / span 5 !important; }
                </style>
            </head>
            <body class="p-6 bg-white">
                <div class="w-full max-w-[800px] mx-auto">
                    ${el.innerHTML}
                </div>
                <script>
                    window.onload = function() {
                        setTimeout(() => {
                            window.print();
                            window.close();
                        }, 800);
                    };
                <\/script>
            </body>
        </html>
    `)
    win.document.close()
}

const handleInvoiceCheck = async () => {
    if (!invoiceCheckMobile.value || !invoiceCheckRoll.value) {
        invoiceCheckError.value = "Please enter both Mobile and Admission Roll."
        return
    }
    invoiceChecking.value = true
    invoiceCheckError.value = ''
    invoiceResult.value = null

    try {
        let endpoint = ''
        if (invoiceCheckType.value === 'application') {
            endpoint = '/api/public/apply-fees/check-invoice-by-roll'
        } else if (invoiceCheckType.value === 'certificate') {
            endpoint = '/api/public/certificate/check-invoice-by-roll'
        }

        const res = await window.axios.post(endpoint, {
            mobile: invoiceCheckMobile.value,
            admission_roll: invoiceCheckRoll.value
        })
        invoiceResult.value = res.data
    } catch (e) {
        invoiceCheckError.value = e?.response?.data?.message || 'Invoice not found or invalid details.'
    } finally {
        invoiceChecking.value = false
    }
}

const handleInvoicePay = async () => {
    if (!invoiceResult.value?.invoice_number) return
    invoicePaying.value = true
    try {
        let endpoint = ''
        if (invoiceCheckType.value === 'application') {
            endpoint = '/api/public/apply-fees/pay-existing'
        } else if (invoiceCheckType.value === 'certificate') {
            endpoint = '/api/public/certificate/pay-existing'
        }

        const res = await window.axios.post(endpoint, {
            invoice_number: invoiceResult.value.invoice_number
        })
        if (res.data?.gateway_url) {
            window.location.href = res.data.gateway_url
        } else {
            alert('Payment gateway URL not returned. Please try again.')
        }
    } catch (e) {
        alert(e?.response?.data?.message || 'Failed to initiate payment.')
    } finally {
        invoicePaying.value = false
    }
}
</script>