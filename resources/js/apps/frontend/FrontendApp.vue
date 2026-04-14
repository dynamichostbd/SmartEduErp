<template>
    <div class="min-h-screen bg-[#eef6f8] text-slate-900 flex flex-col">
        <div class="bg-white">
            <div class="mx-auto max-w-[1700px] px-2 sm:px-3">
                <div class="grid grid-cols-1 items-center gap-2 py-2 sm:grid-cols-3 sm:py-3">
                    <button type="button" class="flex items-center gap-3 text-left" @click="go('/')">
                        <img v-if="site.logo" :src="site.logo" class="h-10 w-auto" alt="logo" />
                        <div class="text-base font-extrabold leading-tight text-slate-900 sm:text-lg">{{ collegeName }}</div>
                    </button>

                    <div class="text-center text-sm font-bold text-slate-800 sm:text-base">
                        <span class="font-extrabold">{{ hotline || '—' }}</span>
                    </div>

                    <div class="flex flex-wrap items-center justify-start gap-2 sm:justify-end">
                        <template v-if="studentMe">
                            <button
                                type="button"
                                class="rounded-sm bg-[#2f855a] px-4 py-2 text-xs font-extrabold text-white hover:bg-[#276f4a] sm:text-sm"
                                @click="go('/student/profile')"
                            >
                                My Profile
                            </button>
                            <button
                                type="button"
                                class="rounded-sm bg-[#d20808] px-4 py-2 text-xs font-extrabold text-white hover:bg-[#b60606] sm:text-sm"
                                @click="doStudentLogout"
                                :disabled="studentAuthLoading"
                            >
                                Logout
                            </button>
                        </template>
                        <template v-else>
                            <a
                                v-for="b in topButtons"
                                :key="b.label"
                                :href="b.href"
                                class="rounded-full bg-[#0d6b75] px-4 py-2 text-xs font-extrabold text-white hover:bg-[#0b5b63] sm:text-sm"
                            >
                                {{ b.label }}
                            </a>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <div class="mx-auto w-full max-w-[1700px] flex-1 px-2 sm:px-3">
            <div class="mt-2 overflow-hidden rounded-sm border border-slate-300 bg-white">
                <div class="flex items-stretch">
                    <div class="flex items-center bg-[#0d6b75] px-4 py-2 text-sm font-extrabold text-white">
                        College Notice
                    </div>
                    <div class="relative flex min-w-0 flex-1 items-center overflow-hidden px-4 py-2">
                        <div class="w-full overflow-hidden">
                            <div
                                v-if="noticeText"
                                class="whitespace-nowrap text-sm font-semibold text-slate-800"
                                :style="tickerStyle"
                            >
                                {{ noticeText }}
                            </div>
                            <div v-else class="text-sm text-slate-600">No notice available.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="path !== '/' && !studentMe" class="mt-3 rounded-sm border border-slate-300 bg-white p-4">
                <button type="button" class="text-sm font-bold text-[#0d6b75] hover:underline" @click="go('/')">← Back to Home</button>
            </div>

            <div :key="path + search">

            <div v-if="path === '/online-admission'" class="mt-3 rounded-sm border border-slate-300 bg-white p-4 sm:p-6">
                <div class="mx-auto w-full max-w-6xl">
                    <div v-if="onlineAdmissionStatusMessage" class="mb-4 rounded-sm border p-3 text-sm" :class="onlineAdmissionStatusClass">
                        {{ onlineAdmissionStatusMessage }}
                        <span v-if="onlineAdmissionTranId" class="font-semibold"> ({{ onlineAdmissionTranId }})</span>
                    </div>

                    <div v-if="studentMe" class="rounded-sm border border-red-200 bg-red-50 p-4 text-center text-sm font-semibold text-red-800">
                        You are already logged in, logout to access this page
                    </div>

                    <template v-else>
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <div class="text-xl font-extrabold text-slate-900 sm:text-2xl">ONLINE ADMISSION</div>
                                <div class="mt-2 h-[2px] w-full max-w-4xl bg-red-500"></div>
                            </div>

                            <div class="flex flex-wrap items-center gap-2">
                                <button type="button" class="rounded-sm bg-[#2b6cb0] px-4 py-2 text-sm font-extrabold text-white hover:bg-[#245a94]" @click="go('/online-admission-download-form')">Download Form</button>
                                <button type="button" class="rounded-sm bg-[#2f855a] px-4 py-2 text-sm font-extrabold text-white hover:bg-[#276f4a]" @click="go('/online-admission-invoice')">Pay Slip</button>
                                <button type="button" class="rounded-sm bg-[#2b6cb0] px-4 py-2 text-sm font-extrabold text-white hover:bg-[#245a94]" @click="go('/online-admission-payment')">Payment</button>
                            </div>
                        </div>

                        <div class="mt-4 rounded-sm border border-slate-300 bg-white">
                            <div class="flex items-center justify-between gap-3 border-b border-slate-300 bg-slate-50 px-4 py-3">
                                <div class="text-sm font-extrabold text-slate-800">CHECK APPLICATION FEES / ADMISSION ROLL / REG NO</div>
                                <button
                                    v-if="onlineAdmissionShowForm || onlineAdmissionCheckApplicationFees || onlineAdmissionCheckRollVerify"
                                    type="button"
                                    class="rounded-full bg-[#d20808] px-4 py-1.5 text-xs font-extrabold text-white hover:bg-[#b60606]"
                                    @click="onlineAdmissionResetForm"
                                >
                                    RESET FORM
                                </button>
                            </div>

                            <div v-if="onlineAdmissionLoading" class="p-4 text-sm text-slate-600">Loading...</div>
                            <div v-else>
                                <div v-if="onlineAdmissionError" class="m-4 rounded-sm border border-red-200 bg-red-50 p-3 text-sm text-red-800">{{ onlineAdmissionError }}</div>

                                <div v-if="!onlineAdmissionCheckApplicationFees && !onlineAdmissionCheckRollVerify && !onlineAdmissionShowForm" class="p-4">
                                    <div class="grid grid-cols-1 items-center gap-3 sm:grid-cols-12">
                                        <div class="sm:col-span-4 sm:text-right">
                                            <div class="text-sm font-extrabold text-slate-800">Academic Level</div>
                                        </div>
                                        <div class="sm:col-span-4">
                                            <select v-model="onlineAdmissionForm.academic_qualification_id" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10">
                                                <option value="">--Select One--</option>
                                                <option v-for="q in onlineAdmissionQualifications" :key="'oaq-' + q.id" :value="String(q.id)">{{ q.name }}</option>
                                            </select>
                                        </div>
                                        <div class="sm:col-span-4">
                                            <button type="button" class="rounded-sm bg-[rgb(39,112,154)] px-8 py-2 text-sm font-extrabold text-white hover:bg-[rgb(32,93,128)]" @click="onlineAdmissionSelectAcademicLevel">NEXT</button>
                                        </div>
                                    </div>
                                </div>

                                <div v-else-if="onlineAdmissionCheckApplicationFees" class="p-4">
                                    <div class="grid grid-cols-1 items-center gap-3 sm:grid-cols-12">
                                        <div class="sm:col-span-4">
                                            <input v-model="onlineAdmissionForm.application_invoice_no" type="text" placeholder="Application Fee Invoice No." class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" />
                                        </div>
                                        <div class="sm:col-span-4">
                                            <input v-model="onlineAdmissionForm.admission_roll" type="text" placeholder="Admission Roll" :readonly="onlineAdmissionDisabledFields.includes('admission_roll')" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" />
                                        </div>
                                        <div class="sm:col-span-4">
                                            <button v-if="!onlineAdmissionCheckSpinner" type="button" class="rounded-sm bg-[rgb(39,112,154)] px-6 py-2 text-sm font-extrabold text-white hover:bg-[rgb(32,93,128)]" @click="onlineAdmissionCheckFees">CHECK APPLICATION FEES</button>
                                            <div v-else class="text-sm text-slate-600">processing..</div>
                                        </div>
                                    </div>
                                </div>

                                <div v-else-if="onlineAdmissionCheckRollVerify && !onlineAdmissionDoubleVerify" class="p-4">
                                    <div class="grid grid-cols-1 items-center gap-3 sm:grid-cols-12">
                                        <div class="sm:col-span-3">
                                            <select v-model="onlineAdmissionVerifyForm.academic_session_id" :disabled="onlineAdmissionDisabledFields.includes('academic_session_id')" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10">
                                                <option value="">--Academic Session--</option>
                                                <option v-for="s in onlineAdmissionSessions" :key="'oas-' + s.id" :value="String(s.id)">{{ s.name }}</option>
                                            </select>
                                        </div>
                                        <div class="sm:col-span-3">
                                            <select v-model="onlineAdmissionVerifyForm.department_id" :disabled="onlineAdmissionDisabledFields.includes('department_id')" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10">
                                                <option value="">--Department/Group--</option>
                                                <option v-for="d in onlineAdmissionFilteredDepartments" :key="'oad-' + d.id" :value="String(d.id)">{{ d.name }}</option>
                                            </select>
                                        </div>
                                        <div class="sm:col-span-3">
                                            <select v-model="onlineAdmissionVerifyForm.academic_class_id" :disabled="onlineAdmissionDisabledFields.includes('academic_class_id')" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10">
                                                <option value="">--Class--</option>
                                                <option v-for="c in onlineAdmissionFilteredClasses" :key="'oac-' + c.id" :value="String(c.id)">{{ c.name }}</option>
                                            </select>
                                        </div>
                                        <div class="sm:col-span-3">
                                            <input v-model="onlineAdmissionVerifyForm.admission_roll" type="text" placeholder="SSC / Admission Roll" :readonly="onlineAdmissionDisabledFields.includes('admission_roll')" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" />
                                        </div>
                                        <div class="sm:col-span-12 pt-1">
                                            <button v-if="!onlineAdmissionCheckSpinner" type="button" class="rounded-sm bg-[rgb(39,112,154)] px-6 py-2 text-sm font-extrabold text-white hover:bg-[rgb(32,93,128)]" @click="onlineAdmissionVerifyRoll">VERIFY ROLL</button>
                                            <div v-else class="text-sm text-slate-600">processing..</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <template v-if="onlineAdmissionShowForm">
                            <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-12">
                                <div class="lg:col-span-6">
                                    <div class="rounded-sm border border-slate-300 bg-white">
                                        <div class="border-b border-slate-300 bg-slate-50 px-4 py-3 text-sm font-extrabold text-slate-800">PERSONAL INFORMATION</div>
                                        <div class="p-4">
                                            <div class="space-y-3">
                                                <input v-model="onlineAdmissionForm.name" type="text" placeholder="Full Name" :readonly="onlineAdmissionDisabledFields.includes('name')" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" />
                                                <input v-model="onlineAdmissionForm.fathers_name" type="text" placeholder="Father's Name" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" />
                                                <input v-model="onlineAdmissionForm.mothers_name" type="text" placeholder="Mother's Name" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" />

                                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-12">
                                                    <div class="sm:col-span-7 flex flex-wrap items-center gap-3">
                                                        <label class="inline-flex items-center gap-2 text-sm font-semibold text-slate-700">
                                                            <input type="radio" value="Male" v-model="onlineAdmissionForm.gender" />
                                                            Male
                                                        </label>
                                                        <label class="inline-flex items-center gap-2 text-sm font-semibold text-slate-700">
                                                            <input type="radio" value="Female" v-model="onlineAdmissionForm.gender" />
                                                            Female
                                                        </label>
                                                        <label class="inline-flex items-center gap-2 text-sm font-semibold text-slate-700">
                                                            <input type="radio" value="Others" v-model="onlineAdmissionForm.gender" />
                                                            Others
                                                        </label>
                                                    </div>
                                                    <div class="sm:col-span-5">
                                                        <input v-model="onlineAdmissionForm.dob" type="date" placeholder="Date of Birth" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" />
                                                    </div>
                                                </div>

                                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                                    <select v-model="onlineAdmissionForm.religion" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10">
                                                        <option value="">--Religion--</option>
                                                        <option v-for="r in onlineAdmissionReligions" :key="'oar-' + r" :value="r">{{ r }}</option>
                                                    </select>
                                                    <select v-model="onlineAdmissionForm.blood_group" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10">
                                                        <option value="">--Blood Group--</option>
                                                        <option v-for="b in onlineAdmissionBloodGroups" :key="'oab-' + b" :value="b">{{ b }}</option>
                                                    </select>
                                                </div>

                                                <input v-model="onlineAdmissionForm.nid" type="text" placeholder="NID / Birth Reg No" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" />
                                                <input ref="onlineAdmissionProfileInput" type="file" accept="image/*" class="block w-full text-sm text-slate-700 file:mr-3 file:rounded-sm file:border-0 file:bg-emerald-600 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-emerald-700" @change="onPickOnlineAdmissionProfile" />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="lg:col-span-6">
                                    <div class="rounded-sm border border-slate-300 bg-white">
                                        <div class="border-b border-slate-300 bg-slate-50 px-4 py-3 text-sm font-extrabold text-slate-800">ACADEMIC INFORMATION</div>
                                        <div class="p-4">
                                            <div class="space-y-3">
                                                <input v-model="onlineAdmissionForm.admission_roll" type="text" placeholder="Admission Roll / Reg No" :readonly="onlineAdmissionDisabledFields.includes('admission_roll')" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" />
                                                <input v-model="onlineAdmissionForm.ssc_gpa" type="text" placeholder="SSC GPA" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" />
                                                <input v-model="onlineAdmissionForm.registration_no" type="text" placeholder="Registration No" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" />

                                                <select v-model="onlineAdmissionForm.academic_session_id" :disabled="onlineAdmissionDisabledFields.includes('academic_session_id')" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10">
                                                    <option value="">--Academic Session--</option>
                                                    <option v-for="s in onlineAdmissionSessions" :key="'oas2-' + s.id" :value="String(s.id)">{{ s.name }}</option>
                                                </select>

                                                <div class="rounded-sm border border-emerald-500 bg-emerald-50 px-3 py-2 text-sm font-bold text-emerald-800">
                                                    {{ onlineAdmissionSelectedQualificationName || '—' }}
                                                </div>

                                                <select v-model="onlineAdmissionForm.department_id" :disabled="onlineAdmissionDisabledFields.includes('department_id')" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10">
                                                    <option value="">--Department/Group--</option>
                                                    <option v-for="d in onlineAdmissionFilteredDepartments" :key="'oad2-' + d.id" :value="String(d.id)">{{ d.name }}</option>
                                                </select>

                                                <select v-model="onlineAdmissionForm.academic_class_id" :disabled="onlineAdmissionDisabledFields.includes('academic_class_id')" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10">
                                                    <option value="">--Class--</option>
                                                    <option v-for="c in onlineAdmissionFilteredClasses" :key="'oac2-' + c.id" :value="String(c.id)">{{ c.name }}</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-12">
                                <div class="lg:col-span-6">
                                    <div class="rounded-sm border border-slate-300 bg-white">
                                        <div class="border-b border-slate-300 bg-slate-50 px-4 py-3 text-sm font-extrabold text-slate-800">CONTACT INFORMATION</div>
                                        <div class="p-4 space-y-3">
                                            <input v-model="onlineAdmissionForm.mobile" type="text" placeholder="Mobile" :readonly="onlineAdmissionDisabledFields.includes('mobile')" class="h-10 w-full rounded-sm border border-slate-300 bg-[#eaf2ff] px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" />
                                            <input v-model="onlineAdmissionForm.email" type="email" placeholder="Email" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" />
                                            <textarea v-model="onlineAdmissionForm.address" rows="2" placeholder="Present Address - Vill: , Post: , Upozila: , District:" class="w-full rounded-sm border border-slate-300 bg-white px-3 py-2 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10"></textarea>
                                            <textarea v-model="onlineAdmissionForm.permanent_address" rows="2" placeholder="Permanent Address - Vill: , Post: , Upozila: , District:" class="w-full rounded-sm border border-slate-300 bg-white px-3 py-2 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10"></textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="lg:col-span-6">
                                    <div class="rounded-sm border border-slate-300 bg-white">
                                        <div class="border-b border-slate-300 bg-slate-50 px-4 py-3 text-sm font-extrabold text-slate-800">GUARDIAN INFORMATION</div>
                                        <div class="p-4 space-y-3">
                                            <div class="flex flex-wrap items-center gap-4 text-sm font-semibold text-slate-700">
                                                <label class="inline-flex items-center gap-2"><input type="radio" value="Father" v-model="onlineAdmissionForm.guardian_type" @change="onOnlineAdmissionGuardianType" />Father</label>
                                                <label class="inline-flex items-center gap-2"><input type="radio" value="Mother" v-model="onlineAdmissionForm.guardian_type" @change="onOnlineAdmissionGuardianType" />Mother</label>
                                                <label class="inline-flex items-center gap-2"><input type="radio" value="Other" v-model="onlineAdmissionForm.guardian_type" @change="onOnlineAdmissionGuardianType" />Other</label>
                                            </div>
                                            <input v-model="onlineAdmissionForm.guardian_name" :readonly="onlineAdmissionRelationReadonly" type="text" placeholder="Guardian Name" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" />
                                            <input v-model="onlineAdmissionForm.guardian_mobile" type="text" placeholder="Guardian Mobile" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" />
                                            <input v-model="onlineAdmissionForm.guardian_relations" :readonly="onlineAdmissionRelationReadonly" type="text" placeholder="Guardian Relations" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 rounded-sm border border-slate-300 bg-white">
                                <div class="border-b border-slate-300 bg-slate-50 px-4 py-3 text-sm font-extrabold text-slate-800">OTHER'S INFORMATION</div>
                                <div class="p-4 grid grid-cols-1 gap-3 sm:grid-cols-12">
                                    <div class="sm:col-span-6">
                                        <input v-model="onlineAdmissionForm.passing_year" type="text" placeholder="Passing Year" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" />
                                    </div>
                                    <div class="sm:col-span-6">
                                        <input v-model="onlineAdmissionForm.nationality" type="text" placeholder="Nationality" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" />
                                    </div>
                                    <div class="sm:col-span-6">
                                        <select v-model="onlineAdmissionForm.extra_curricular_activity" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10">
                                            <option value="">Select Extra Curricular Activity</option>
                                            <option value="Cultural activities">Cultural activities</option>
                                            <option value="Drawing and graffiti">Drawing and graffiti</option>
                                            <option value="Debate and public speaking">Debate and public speaking</option>
                                            <option value="Sports and physical exercise">Sports and physical exercise</option>
                                            <option value="Social service activities">Social service activities</option>
                                            <option value="Technological and creative activities">Technological and creative activities</option>
                                            <option value="Academic clubs">Academic clubs</option>
                                            <option value="Others">Others</option>
                                        </select>
                                    </div>
                                    <div class="sm:col-span-3">
                                        <div class="text-xs font-extrabold text-slate-700">Quota</div>
                                        <div class="mt-2 flex flex-wrap items-center gap-4 text-sm font-semibold text-slate-700">
                                            <label class="inline-flex items-center gap-2"><input type="radio" value="Yes" v-model="onlineAdmissionForm.quota" />Yes</label>
                                            <label class="inline-flex items-center gap-2"><input type="radio" value="No" v-model="onlineAdmissionForm.quota" />No</label>
                                        </div>
                                    </div>
                                    <div class="sm:col-span-3">
                                        <div class="text-xs font-extrabold text-slate-700">Marital Status</div>
                                        <div class="mt-2 flex flex-wrap items-center gap-4 text-sm font-semibold text-slate-700">
                                            <label class="inline-flex items-center gap-2"><input type="radio" value="Married" v-model="onlineAdmissionForm.marital_status" />Married</label>
                                            <label class="inline-flex items-center gap-2"><input type="radio" value="Unmarried" v-model="onlineAdmissionForm.marital_status" />Unmarried</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-if="onlineAdmissionUploadDocuments.length" class="mt-4 rounded-sm border border-slate-300 bg-white">
                                <div class="border-b border-slate-300 bg-slate-50 px-4 py-3 text-sm font-extrabold text-slate-800">UPLOAD DOCUMENTS</div>
                                <div class="p-4">
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full text-sm">
                                            <tbody>
                                                <tr v-for="(doc, idx) in onlineAdmissionUploadDocuments" :key="'oadoc-' + idx" class="border-b border-slate-100">
                                                    <td class="py-2 pr-3">
                                                        <div class="font-semibold text-slate-800">{{ doc.name_bn }}</div>
                                                        <div class="text-xs text-red-600">file type ( jpg, jpeg, png )</div>
                                                        <div v-if="doc.optional" class="text-xs font-extrabold text-red-600">(Optional)</div>
                                                    </td>
                                                    <td class="py-2">
                                                        <input type="file" accept="image/*" class="block w-full text-sm text-slate-700 file:mr-3 file:rounded-sm file:border-0 file:bg-slate-700 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-slate-800" @change="(e) => onlineAdmissionDocumentUpload(e, idx)" />
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 rounded-sm border border-slate-300 bg-white">
                                <div class="border-b border-slate-300 bg-slate-50 px-4 py-3 text-sm font-extrabold text-slate-800">AGREE</div>
                                <div class="p-4">
                                    <label class="inline-flex items-center gap-2 text-sm font-semibold text-slate-700">
                                        <input type="checkbox" v-model="onlineAdmissionAgree" />
                                        I agree with terms & conditions
                                    </label>
                                </div>
                            </div>

                            <div class="mt-4 text-center">
                                <div v-if="onlineAdmissionSubmitting" class="text-sm text-slate-600">processing..</div>
                                <button v-else type="button" class="rounded-sm bg-[#0b1d4d] px-10 py-2.5 text-sm font-extrabold text-white hover:bg-[#09163c]" @click="onlineAdmissionSubmit">SUBMIT</button>
                            </div>
                        </template>
                    </template>
                </div>
            </div>
            <div v-else-if="path === '/online-admission-payment'" class="mt-3 rounded-sm border border-slate-300 bg-white p-4 sm:p-6">
                <div class="mx-auto w-full max-w-3xl">
                    <div v-if="onlineAdmissionStatusMessage" class="mb-4 rounded-sm border p-3 text-sm" :class="onlineAdmissionStatusClass">
                        {{ onlineAdmissionStatusMessage }}
                        <span v-if="onlineAdmissionTranId" class="font-semibold"> ({{ onlineAdmissionTranId }})</span>
                    </div>

                    <div v-if="studentMe" class="rounded-sm border border-red-200 bg-red-50 p-4 text-center text-sm font-semibold text-red-800">
                        You are already logged in, logout to access this page
                    </div>

                    <template v-else>
                        <div class="flex items-center justify-between gap-3 border-b border-slate-300 pb-3">
                            <div class="text-lg font-extrabold text-slate-900">ADMISSION PAYMENT</div>
                            <button type="button" class="rounded-sm bg-[#2f855a] px-4 py-2 text-sm font-extrabold text-white hover:bg-[#276f4a]" @click="go('/online-admission-invoice')">Pay Slip</button>
                        </div>

                        <div v-if="onlineAdmissionPaymentError" class="mt-4 rounded-sm border border-red-200 bg-red-50 p-3 text-sm text-red-800">{{ onlineAdmissionPaymentError }}</div>

                        <div class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-12">
                            <div class="sm:col-span-9 space-y-3">
                                <input v-model="onlineAdmissionPaymentForm.mobile" type="text" placeholder="Mobile" :readonly="onlineAdmissionPaymentReadonly" class="h-10 w-full rounded-sm border border-slate-300 bg-[#eaf2ff] px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" />
                                <input v-model="onlineAdmissionPaymentForm.admission_roll" type="text" placeholder="Admission Roll / Reg No" :readonly="onlineAdmissionPaymentReadonly" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" />
                            </div>
                            <div class="sm:col-span-3 flex flex-col items-stretch justify-start gap-2">
                                <button v-if="onlineAdmissionPaymentReadonly" type="button" class="rounded-sm bg-[#d20808] px-4 py-2 text-sm font-extrabold text-white hover:bg-[#b60606]" @click="onlineAdmissionPaymentReset">RESET</button>
                                <button v-else-if="!onlineAdmissionPaymentHeadsLoading" type="button" class="rounded-sm bg-[rgb(39,112,154)] px-4 py-2 text-sm font-extrabold text-white hover:bg-[rgb(32,93,128)]" @click="onlineAdmissionGetPaymentHeads">CHECK</button>
                                <div v-else class="text-sm text-slate-600">processing..</div>
                            </div>
                        </div>

                        <div v-if="onlineAdmissionPaymentHeads.length" class="mt-4 space-y-3">
                            <select v-model="onlineAdmissionPaymentForm.account_head_id" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none" @change="onlineAdmissionSelectPaymentPurpose">
                                <option value="">Fees</option>
                                <option v-for="p in onlineAdmissionPaymentHeads" :key="'oaph-' + p.account_head_id" :value="String(p.account_head_id)">{{ p.account_head?.name || '' }}</option>
                            </select>
                            <input v-model="onlineAdmissionPaymentForm.amount" type="text" placeholder="Amount" :readonly="onlineAdmissionPaymentAmountReadonly" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none" />
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-12">
                                <div class="sm:col-span-4 pt-2 text-sm font-semibold text-slate-700">Service Charge</div>
                                <div class="sm:col-span-8">
                                    <input :value="onlineAdmissionPaymentChargeAmount" readonly type="text" placeholder="Service Charge" class="h-10 w-full rounded-sm border border-slate-300 bg-slate-50 px-3 text-sm outline-none" />
                                </div>
                            </div>
                        </div>

                        <div v-if="Number(onlineAdmissionPaymentForm.amount || 0) > 0" class="mt-6 text-center">
                            <button
                                v-if="!onlineAdmissionPaymentSubmitting"
                                type="button"
                                class="rounded-sm bg-[#0b1d4d] px-10 py-2.5 text-sm font-extrabold text-white hover:bg-[#09163c]"
                                :disabled="onlineAdmissionPaymentPayFirst"
                                @click="onlineAdmissionPayNow"
                            >
                                PAY NOW
                            </button>
                            <div v-else class="text-sm text-slate-600">processing..</div>
                        </div>
                    </template>
                </div>
            </div>

            <div v-else-if="path === '/online-admission-invoice'" class="mt-3 rounded-sm border border-slate-300 bg-white p-4 sm:p-6">
                <div class="mx-auto w-full max-w-5xl">
                    <div v-if="studentMe" class="rounded-sm border border-red-200 bg-red-50 p-4 text-center text-sm font-semibold text-red-800">
                        You are already logged in, logout to access this page
                    </div>
                    <template v-else>
                        <div class="flex items-center justify-between gap-3 border-b border-slate-300 pb-3">
                            <div class="text-lg font-extrabold text-slate-900">PAY SLIP</div>
                            <button type="button" class="rounded-sm bg-[#2b6cb0] px-4 py-2 text-sm font-extrabold text-white hover:bg-[#245a94]" @click="go('/online-admission-payment')">Payment</button>
                        </div>

                        <div class="mt-6 grid grid-cols-1 items-center gap-3 sm:grid-cols-12">
                            <div class="sm:col-span-4">
                                <input v-model="onlineAdmissionInvoiceSearch.admission_roll" type="text" placeholder="Admission Roll / Reg No" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none" />
                            </div>
                            <div class="sm:col-span-4">
                                <input v-model="onlineAdmissionInvoiceSearch.mobile" type="text" placeholder="Mobile Number" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none" />
                            </div>
                            <div class="sm:col-span-4">
                                <button v-if="!onlineAdmissionInvoiceLoading" type="button" class="rounded-sm bg-[#2f855a] px-5 py-2 text-sm font-extrabold text-white hover:bg-[#276f4a]" @click="onlineAdmissionSearchInvoices">Search</button>
                                <div v-else class="text-sm text-slate-600">processing..</div>
                            </div>
                        </div>

                        <div v-if="onlineAdmissionInvoiceError" class="mt-4 rounded-sm border border-red-200 bg-red-50 p-3 text-sm text-red-800">{{ onlineAdmissionInvoiceError }}</div>

                        <div class="mt-6 space-y-4">
                            <div v-if="onlineAdmissionInvoicesShow">
                                <div v-if="onlineAdmissionInvoices.length">
                                    <div v-for="(inv, idx) in onlineAdmissionInvoices" :key="'oainv-' + idx" class="rounded-sm border border-slate-300 p-4">
                                        <div class="flex flex-wrap items-center justify-between gap-3">
                                            <div class="text-sm font-semibold text-slate-700">
                                                <div><span class="font-extrabold">Invoice:</span> {{ inv.invoice_number }}</div>
                                                <div><span class="font-extrabold">Date:</span> {{ inv.invoice_date || '—' }}</div>
                                                <div><span class="font-extrabold">Status:</span> {{ inv.status }}</div>
                                            </div>
                                            <div class="flex flex-wrap items-center gap-2">
                                                <button type="button" class="rounded-sm bg-[#2b6cb0] px-3 py-2 text-xs font-extrabold text-white hover:bg-[#245a94]" @click="onlineAdmissionOpenInvoice(inv.id)">PRINT</button>
                                                <button type="button" class="rounded-sm bg-[#2f855a] px-3 py-2 text-xs font-extrabold text-white hover:bg-[#276f4a]" @click="onlineAdmissionDownloadInvoice(inv.id)">Download Invoice</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="rounded-sm border border-slate-300 bg-slate-50 p-4 text-center text-sm text-slate-700">Sorry!! invoice not found, please try again</div>
                            </div>
                            <div v-else class="rounded-sm border border-slate-300 bg-slate-50 p-4 text-center text-sm text-slate-700">Please input your admission roll and your mobile number for invoice</div>
                        </div>
                    </template>
                </div>
            </div>

            <div v-else-if="path === '/online-admission-download-form'" class="mt-3 rounded-sm border border-slate-300 bg-white p-4 sm:p-6">
                <div class="mx-auto w-full max-w-5xl">
                    <div v-if="studentMe" class="rounded-sm border border-red-200 bg-red-50 p-4 text-center text-sm font-semibold text-red-800">
                        You are already logged in, logout to access this page
                    </div>
                    <template v-else>
                        <div class="flex items-center justify-between gap-3 border-b border-slate-300 pb-3">
                            <div class="text-lg font-extrabold text-slate-900">DOWNLOAD FORM</div>
                            <button type="button" class="rounded-sm bg-[#2b6cb0] px-4 py-2 text-sm font-extrabold text-white hover:bg-[#245a94]" @click="go('/online-admission-payment')">Payment</button>
                        </div>

                        <div class="mt-6 grid grid-cols-1 items-center gap-3 sm:grid-cols-12">
                            <div class="sm:col-span-4">
                                <input v-model="onlineAdmissionDownloadSearch.admission_roll" type="text" placeholder="Admission Roll / Reg No" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none" />
                            </div>
                            <div class="sm:col-span-4">
                                <input v-model="onlineAdmissionDownloadSearch.mobile" type="text" placeholder="Mobile Number" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none" />
                            </div>
                            <div class="sm:col-span-4">
                                <button v-if="!onlineAdmissionDownloadLoading" type="button" class="rounded-sm bg-[#2f855a] px-5 py-2 text-sm font-extrabold text-white hover:bg-[#276f4a]" @click="onlineAdmissionSearchDownloadForm">Search</button>
                                <div v-else class="text-sm text-slate-600">processing..</div>
                            </div>
                        </div>

                        <div v-if="onlineAdmissionDownloadError" class="mt-4 rounded-sm border border-red-200 bg-red-50 p-3 text-sm text-red-800">{{ onlineAdmissionDownloadError }}</div>

                        <div class="mt-6">
                            <template v-if="onlineAdmissionDownloadShow">
                                <template v-if="onlineAdmissionDownloadData">
                                    <div class="text-center">
                                        <button v-if="!onlineAdmissionDownloadPdfLoading" type="button" class="rounded-sm bg-[#2b6cb0] px-5 py-2 text-sm font-extrabold text-white hover:bg-[#245a94]" @click="onlineAdmissionDownloadPdf">Download Admission Form</button>
                                        <div v-else class="text-sm text-slate-600">processing..</div>
                                    </div>
                                    <div class="mt-4 rounded-sm border border-slate-300 bg-white p-4">
                                        <table class="w-full text-sm">
                                            <tbody>
                                                <tr class="border-b"><th class="py-2 pr-3 text-left">Admission Roll</th><td class="py-2">{{ onlineAdmissionDownloadData.admission_roll }}</td></tr>
                                                <tr class="border-b"><th class="py-2 pr-3 text-left">Mobile</th><td class="py-2">{{ onlineAdmissionDownloadData.mobile }}</td></tr>
                                                <tr class="border-b"><th class="py-2 pr-3 text-left">Fathers Name</th><td class="py-2">{{ onlineAdmissionDownloadData.fathers_name }}</td></tr>
                                                <tr class="border-b"><th class="py-2 pr-3 text-left">Mothers Name</th><td class="py-2">{{ onlineAdmissionDownloadData.mothers_name }}</td></tr>
                                                <tr><th class="py-2 pr-3 text-left">Address</th><td class="py-2">{{ onlineAdmissionDownloadData.address }}</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </template>
                                <div v-else class="rounded-sm border border-slate-300 bg-slate-50 p-4 text-center text-sm text-slate-700">Sorry!! admission form not found</div>
                            </template>
                            <div v-else class="rounded-sm border border-slate-300 bg-slate-50 p-4 text-center text-sm text-slate-700">Please input your admission roll and your mobile number for admission form</div>
                        </div>
                    </template>
                </div>
            </div>

            <div v-else-if="path === '/login'" class="mt-3 rounded-sm border border-slate-300 bg-white p-4 sm:p-6">
                <div class="mx-auto w-full max-w-xl">
                    <div class="text-center text-xl font-extrabold text-slate-900 sm:text-2xl">STUDENT LOGIN</div>
                    <div class="mx-auto mt-3 h-[2px] w-full max-w-md bg-red-500"></div>

                    <div v-if="studentAuthError" class="mt-6 rounded-sm border border-red-200 bg-red-50 p-3 text-sm text-red-800">
                        {{ studentAuthError }}
                    </div>

                    <form class="mt-8 space-y-4" @submit.prevent="submitStudentLogin">
                        <input
                            v-model="studentLoginForm.login"
                            type="text"
                            placeholder="Email or Mobile"
                            class="h-10 w-full rounded-sm border border-slate-300 bg-[#eaf2ff] px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10"
                            required
                        />

                        <input
                            v-model="studentLoginForm.password"
                            type="password"
                            placeholder="Password"
                            class="h-10 w-full rounded-sm border border-slate-300 bg-[#eaf2ff] px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10"
                            required
                        />

                        <div class="flex items-center justify-end">
                            <button
                                type="button"
                                class="rounded-sm border border-red-500 px-3 py-1 text-xs font-bold text-red-600 hover:bg-red-50"
                                @click="go('/forgot-password')"
                            >
                                Forgot password?
                            </button>
                        </div>

                        <div class="pt-2 text-center">
                            <button
                                type="submit"
                                class="rounded-sm bg-[#0b1d4d] px-10 py-2.5 text-sm font-extrabold text-white hover:bg-[#09163c]"
                                :disabled="studentAuthLoading"
                            >
                                {{ studentAuthLoading ? 'Logging in...' : 'Login' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <StudentDashboardPage v-else-if="path === '/dashboard'" />
            <StudentInvoiceViewPage v-else-if="studentInvoiceId" />
            <StudentPayNowPage v-else-if="path === '/student/pay-now'" />
            <StudentPaymentHistoryPage v-else-if="path === '/student/payment-history'" />
            <StudentFeesPage v-else-if="path === '/student/fees'" />
            <StudentAdmitCardPage v-else-if="path === '/student/admit-card'" />
            <StudentResultPage v-else-if="path === '/student/result'" />
            <StudentSubjectsPage v-else-if="path === '/student/subjects'" />
            <StudentChangePasswordPage v-else-if="path === '/student/change-password'" />
            <StudentProfilePage v-else-if="path === '/student/profile'" />
            <StudentProfileEditPage v-else-if="path === '/student/profile/edit'" />
            <div v-else-if="path === '/forgot-password'" class="mt-3 rounded-sm border border-slate-300 bg-white p-4 sm:p-6">
                <div class="mx-auto w-full max-w-xl">
                    <div class="text-center text-xl font-extrabold text-slate-900 sm:text-2xl">FORGOT PASSWORD</div>
                    <div class="mx-auto mt-3 h-[2px] w-full max-w-md bg-red-500"></div>

                    <div class="mt-6 rounded-sm border border-slate-300 bg-slate-50 p-4 text-sm text-slate-700">
                        This feature will be implemented next.
                    </div>

                    <div class="mt-6 text-center">
                        <button type="button" class="text-sm font-bold text-[#0d6b75] hover:underline" @click="go('/login')">Back to Login</button>
                    </div>
                </div>
            </div>

            <div v-else-if="path === '/apply-fees'" class="mt-3 rounded-sm border border-slate-300 bg-white p-4 sm:p-6">
                <div class="mx-auto w-full max-w-6xl">
                    <div class="flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <div class="text-xl font-extrabold text-slate-900 sm:text-2xl">STUDENT PAYMENT</div>
                            <div class="mt-2 h-[2px] w-full max-w-3xl bg-red-500"></div>
                        </div>

                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                class="rounded-t-md border border-b-0 px-4 py-2 text-sm font-extrabold"
                                :class="applyFeesTab === 'fees' ? 'border-blue-500 bg-white text-blue-700' : 'border-slate-300 bg-slate-50 text-slate-600 hover:bg-white'"
                                @click="setApplyFeesTab('fees')"
                            >
                                APPLICATION FEES
                            </button>
                            <button
                                type="button"
                                class="rounded-t-md border border-b-0 px-4 py-2 text-sm font-extrabold"
                                :class="applyFeesTab === 'certificate' ? 'border-blue-500 bg-white text-blue-700' : 'border-slate-300 bg-slate-50 text-slate-600 hover:bg-white'"
                                @click="setApplyFeesTab('certificate')"
                            >
                                APPLY FOR CERTIFICATE
                            </button>
                        </div>
                    </div>

                    <div class="mt-4 rounded-sm border border-slate-300 bg-white p-4 sm:p-6">

                    <div v-if="applyFeesStatusMessage" class="mt-6 rounded-sm border p-3 text-sm" :class="applyFeesStatusClass">
                        {{ applyFeesStatusMessage }}
                        <span v-if="applyFeesTranId" class="font-semibold"> ({{ applyFeesTranId }})</span>
                    </div>

                        <div v-if="applyFeesTab === 'fees'">
                            <div v-if="applyFeesLoading" class="mt-6 text-sm text-slate-600">Loading...</div>
                            <div v-else>
                                <div v-if="applyFeesError" class="mt-6 rounded-sm border border-red-200 bg-red-50 p-3 text-sm text-red-800">
                                    {{ applyFeesError }}
                                </div>

                                <form class="mt-8" @submit.prevent="submitApplyFees">
                                    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                                        <div class="space-y-4">
                                            <input v-model="applyFeesForm.name" type="text" placeholder="Full Name" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" required />
                                            <input v-model="applyFeesForm.mobile" type="text" placeholder="Mobile" class="h-10 w-full rounded-sm border border-slate-300 bg-[#eaf2ff] px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" required />
                                            <input v-model="applyFeesForm.admission_roll" type="text" placeholder="Application ID / Admission Roll" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" required />
                                        </div>

                                        <div class="space-y-4">
                                            <select v-model="applyFeesForm.academic_session_id" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" required>
                                                <option value="">Session</option>
                                                <option v-for="s in applyFeesSessionsSorted" :key="'afs-' + s.id" :value="String(s.id)">{{ s.name }}</option>
                                            </select>

                                            <select v-model="applyFeesForm.academic_qualification_id" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" required>
                                                <option value="">Academic Level</option>
                                                <option v-for="q in applyFeesQualifications" :key="'afq-' + q.id" :value="String(q.id)">{{ q.name }}</option>
                                            </select>

                                            <select v-model="applyFeesForm.department_id" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" required>
                                                <option value="">Department/Group</option>
                                                <option v-for="d in applyFeesFilteredDepartments" :key="'afd-' + d.id" :value="String(d.id)">{{ d.name }}</option>
                                            </select>

                                            <select v-model="applyFeesForm.academic_class_id" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" required>
                                                <option value="">Class</option>
                                                <option v-for="c in applyFeesFilteredClasses" :key="'afc-' + c.id" :value="String(c.id)">{{ c.name }}</option>
                                            </select>
                                        </div>

                                        <div class="space-y-4">
                                            <select v-model="applyFeesForm.purpose_id" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" required>
                                                <option value="">Fees</option>
                                                <option v-for="p in applyFeesPurposes" :key="'afp-' + p.id" :value="String(p.id)">{{ p?.head?.name || '' }}</option>
                                            </select>

                                            <div class="rounded-sm border border-slate-300 bg-slate-50 p-4 text-sm text-slate-700">
                                                <div class="font-semibold">Amount</div>
                                                <div class="mt-1 text-lg font-extrabold text-slate-900">{{ applyFeesSelectedAmountText }}</div>
                                            </div>

                                            <div v-if="applyFeesInvoiceError" class="rounded-sm border border-red-200 bg-red-50 p-3 text-sm text-red-800">
                                                {{ applyFeesInvoiceError }}
                                            </div>
                                            <div v-else-if="applyFeesInvoice" class="rounded-sm border border-slate-300 bg-white p-3 text-sm text-slate-700">
                                                <div class="font-semibold text-slate-900">Invoice: {{ applyFeesInvoice.invoice_number || '—' }}</div>
                                                <div class="mt-1">Status: <span class="font-semibold">{{ applyFeesInvoice.status || '—' }}</span></div>
                                                <div class="mt-1">Amount: <span class="font-semibold">{{ Number(applyFeesInvoice.amount || 0).toFixed(2) }}</span></div>
                                                <button
                                                    v-if="String(applyFeesInvoice.status || '') !== 'success' && applyFeesInvoice.invoice_number"
                                                    type="button"
                                                    class="mt-3 rounded-sm bg-[#0b1d4d] px-4 py-2 text-xs font-extrabold text-white hover:bg-[#09163c]"
                                                    :disabled="applyFeesPayExistingLoading"
                                                    @click="payApplyFeesExisting"
                                                >
                                                    {{ applyFeesPayExistingLoading ? 'Processing...' : 'Pay This Invoice' }}
                                                </button>
                                            </div>

                                            <div class="mt-2 flex items-center justify-between gap-3">
                                                <button
                                                    type="button"
                                                    class="rounded-sm border border-emerald-600 bg-white px-4 py-2 text-sm font-extrabold text-emerald-700 hover:bg-emerald-50"
                                                    :disabled="applyFeesInvoiceLoading"
                                                    @click="checkApplyFeesInvoice"
                                                >
                                                    {{ applyFeesInvoiceLoading ? 'Checking...' : 'Check Invoice' }}
                                                </button>

                                                <button type="submit" class="rounded-sm bg-[#0b1d4d] px-10 py-2.5 text-sm font-extrabold text-white hover:bg-[#09163c]" :disabled="applyFeesSubmitting">
                                                    {{ applyFeesSubmitting ? 'Processing...' : 'Pay Now' }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div v-else>
                            <div v-if="certificateLoading" class="mt-6 text-sm text-slate-600">Loading...</div>
                            <div v-else>
                                <div v-if="certificateError" class="mt-6 rounded-sm border border-red-200 bg-red-50 p-3 text-sm text-red-800">
                                    {{ certificateError }}
                                </div>

                                <form class="mt-8" @submit.prevent="submitCertificate">
                                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                                        <div class="rounded-sm border border-slate-300 bg-white p-4">
                                            <div class="border-l-4 border-orange-500 pl-3 text-sm font-extrabold text-slate-900">STUDENT INFO</div>

                                            <div class="mt-4 flex items-center gap-2">
                                                <input
                                                    v-model="certificateForm.mobile"
                                                    type="text"
                                                    placeholder="Mobile"
                                                    class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10"
                                                    required
                                                />
                                                <button
                                                    type="button"
                                                    class="h-10 w-12 rounded-sm border border-slate-300 bg-slate-50 text-sm font-extrabold text-slate-700 hover:bg-white"
                                                    :disabled="certificateLookupLoading"
                                                    @click="certificateLookup"
                                                >
                                                    {{ certificateLookupLoading ? '…' : 'Go' }}
                                                </button>
                                            </div>
                                            <div v-if="certificateLookupError" class="mt-3 rounded-sm border border-red-200 bg-red-50 p-2 text-xs text-red-800">{{ certificateLookupError }}</div>

                                            <div class="mt-4 text-sm font-semibold text-slate-800">Certificate Type :</div>
                                            <div class="mt-2 flex flex-wrap items-center gap-4 text-sm text-slate-700">
                                                <label class="inline-flex items-center gap-2">
                                                    <input v-model="certificateForm.certificate_type" type="radio" name="certificate_type" value="en" />
                                                    English
                                                </label>
                                                <label class="inline-flex items-center gap-2">
                                                    <input v-model="certificateForm.certificate_type" type="radio" name="certificate_type" value="bn" />
                                                    Bangla
                                                </label>
                                                <label class="inline-flex items-center gap-2">
                                                    <input v-model="certificateForm.certificate_type" type="radio" name="certificate_type" value="both" />
                                                    Both
                                                </label>
                                            </div>

                                            <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                                <input v-model="certificateForm.student_name_en" type="text" placeholder="Student name (EN)" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" :disabled="certificateEnDisabled" :required="!certificateEnDisabled" />
                                                <input v-model="certificateForm.student_name_bn" type="text" placeholder="Student name (BN)" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" :disabled="certificateBnDisabled" :required="!certificateBnDisabled" />

                                                <input v-model="certificateForm.fathers_name_en" type="text" placeholder="Father's name (EN)" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" :disabled="certificateEnDisabled" :required="!certificateEnDisabled" />
                                                <input v-model="certificateForm.fathers_name_bn" type="text" placeholder="Father's name (BN)" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" :disabled="certificateBnDisabled" :required="!certificateBnDisabled" />

                                                <input v-model="certificateForm.mothers_name_en" type="text" placeholder="Mother's name (EN)" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" :disabled="certificateEnDisabled" :required="!certificateEnDisabled" />
                                                <input v-model="certificateForm.mothers_name_bn" type="text" placeholder="Mother's name (BN)" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" :disabled="certificateBnDisabled" :required="!certificateBnDisabled" />

                                                <input v-model="certificateForm.academic_year_en" type="text" placeholder="Academic year (EN)" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" :disabled="certificateEnDisabled" :required="!certificateEnDisabled" />
                                                <input v-model="certificateForm.academic_year_bn" type="text" placeholder="Academic year (BN)" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" :disabled="certificateBnDisabled" :required="!certificateBnDisabled" />

                                                <input v-model="certificateForm.registration_no_en" type="text" placeholder="Registration no (EN)" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" :disabled="certificateEnDisabled" :required="!certificateEnDisabled" />
                                                <input v-model="certificateForm.registration_no_bn" type="text" placeholder="Registration no (BN)" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" :disabled="certificateBnDisabled" :required="!certificateBnDisabled" />

                                                <input v-model="certificateForm.exam_roll_en" type="text" placeholder="Exam roll (EN)" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" :disabled="certificateEnDisabled" :required="!certificateEnDisabled" />
                                                <input v-model="certificateForm.exam_roll_bn" type="text" placeholder="Exam roll (BN)" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" :disabled="certificateBnDisabled" :required="!certificateBnDisabled" />

                                                <input v-model="certificateForm.exam_year_en" type="text" placeholder="Exam year (EN)" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" :disabled="certificateEnDisabled" :required="!certificateEnDisabled" />
                                                <input v-model="certificateForm.exam_year_bn" type="text" placeholder="Exam year (BN)" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" :disabled="certificateBnDisabled" :required="!certificateBnDisabled" />

                                                <input v-model="certificateForm.gpa_en" type="text" placeholder="GPA (EN)" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" :disabled="certificateEnDisabled" :required="!certificateEnDisabled" />
                                                <input v-model="certificateForm.gpa_bn" type="text" placeholder="GPA (BN)" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" :disabled="certificateBnDisabled" :required="!certificateBnDisabled" />

                                                <input v-model="certificateForm.division_en" type="text" placeholder="Division (EN)" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" :disabled="certificateEnDisabled" :required="!certificateEnDisabled" />
                                                <input v-model="certificateForm.division_bn" type="text" placeholder="Division (BN)" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" :disabled="certificateBnDisabled" :required="!certificateBnDisabled" />
                                            </div>
                                        </div>

                                        <div class="rounded-sm border border-slate-300 bg-white p-4">
                                            <div class="border-l-4 border-orange-500 pl-3 text-sm font-extrabold text-slate-900">APPLY FOR CERTIFICATE</div>

                                            <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                                <select v-model="certificateForm.academic_session_id" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" required>
                                                    <option value="">--Select Session--</option>
                                                    <option v-for="s in certificateSessionsSorted" :key="'cs-' + s.id" :value="String(s.id)">{{ s.name }}</option>
                                                </select>

                                                <select v-model="certificateForm.template_id" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" required>
                                                    <option value="">--Select Certificate--</option>
                                                    <option v-for="t in certificateFilteredTemplates" :key="'ct-' + t.id" :value="String(t.id)">{{ t.title }}</option>
                                                </select>

                                                <select v-model="certificateForm.academic_qualification_id" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" required>
                                                    <option value="">--Select Academic Level--</option>
                                                    <option v-for="q in certificateQualifications" :key="'cq-' + q.id" :value="String(q.id)">{{ q.name }}</option>
                                                </select>

                                                <select v-model="certificateForm.department_id" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" :required="certificateDepartmentRequired">
                                                    <option value="">--Select Department/Group--</option>
                                                    <option v-for="d in certificateFilteredDepartments" :key="'cd-' + d.id" :value="String(d.id)">{{ d.name }}</option>
                                                </select>
                                            </div>

                                            <div v-if="certificateClasses && certificateClasses.length" class="mt-3">
                                                <select v-model="certificateForm.academic_class_id" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" :required="certificateClassRequired">
                                                    <option value="">--Select Class--</option>
                                                    <option v-for="c in certificateFilteredClasses" :key="'cc-' + c.id" :value="String(c.id)">{{ c.name }}</option>
                                                </select>
                                            </div>

                                            <div class="mt-4 rounded-sm border border-slate-300 bg-slate-50 p-4 text-sm text-slate-700">
                                                <div class="font-semibold">Amount</div>
                                                <div class="mt-1 text-lg font-extrabold text-slate-900">{{ certificateSelectedAmountText }}</div>
                                            </div>

                                            <div class="mt-6 text-center">
                                                <button type="submit" class="rounded-sm bg-[#0b1d4d] px-10 py-2.5 text-sm font-extrabold text-white hover:bg-[#09163c]" :disabled="certificateSubmitting">
                                                    {{ certificateSubmitting ? 'Submitting...' : 'SUBMIT' }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="path === '/registration'" class="mt-3 rounded-sm border border-slate-300 bg-white p-4 sm:p-6">
                <div class="mx-auto w-full max-w-6xl">
                    <div class="text-center text-xl font-extrabold text-slate-900 sm:text-2xl">STUDENT REGISTRATION</div>
                    <div class="mx-auto mt-3 h-[2px] w-full max-w-3xl bg-red-500"></div>

                    <div v-if="registrationLoading" class="mt-6 text-sm text-slate-600">Loading...</div>

                    <div v-else>
                        <div v-if="registrationError" class="mt-6 rounded-sm border border-red-200 bg-red-50 p-3 text-sm text-red-800">
                            {{ registrationError }}
                        </div>

                        <div v-if="registrationSuccess" class="mt-6 rounded-sm border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-900">
                            {{ registrationSuccess }}
                        </div>

                        <form class="mt-8" @submit.prevent="submitRegistration">
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                                <div class="space-y-4">
                                    <input v-model="registrationForm.name" type="text" placeholder="Full Name" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" required />

                                    <div class="flex flex-wrap items-center gap-4 text-sm text-slate-700">
                                        <label class="inline-flex items-center gap-2">
                                            <input v-model="registrationForm.gender" type="radio" name="gender" value="Male" />
                                            Male
                                        </label>
                                        <label class="inline-flex items-center gap-2">
                                            <input v-model="registrationForm.gender" type="radio" name="gender" value="Female" />
                                            Female
                                        </label>
                                        <label class="inline-flex items-center gap-2">
                                            <input v-model="registrationForm.gender" type="radio" name="gender" value="Others" />
                                            Others
                                        </label>
                                    </div>

                                    <select v-model="registrationForm.blood_group" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10">
                                        <option value="">Blood Group</option>
                                        <option v-for="b in registrationBloodGroups" :key="'bg-' + b" :value="b">{{ b }}</option>
                                    </select>

                                    <input v-model="registrationForm.fathers_name" type="text" placeholder="Father's Name" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" />
                                    <input v-model="registrationForm.mothers_name" type="text" placeholder="Mother's Name" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" />
                                    <input v-model="registrationForm.admission_id" type="text" placeholder="Admission Roll (optional)" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" />
                                    <input v-model="registrationForm.college_roll" type="text" placeholder="College Roll" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" />
                                    <input v-model="registrationForm.reg_no" type="text" placeholder="Registration No" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" />
                                </div>

                                <div class="space-y-4">
                                    <select v-model="registrationForm.academic_session_id" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" required>
                                        <option value="">Academic Session</option>
                                        <option v-for="s in registrationSessionsSorted" :key="'ses-' + s.id" :value="String(s.id)">{{ s.name }}</option>
                                    </select>

                                    <select v-model="registrationForm.academic_qualification_id" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" required>
                                        <option value="">Academic Level</option>
                                        <option v-for="q in registrationQualifications" :key="'q-' + q.id" :value="String(q.id)">{{ q.name }}</option>
                                    </select>

                                    <select v-model="registrationForm.department_id" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" :required="registrationDepartmentRequired">
                                        <option value="">Department/Group</option>
                                        <option v-for="d in registrationFilteredDepartments" :key="'d-' + d.id" :value="String(d.id)">{{ d.name }}</option>
                                    </select>

                                    <select v-model="registrationForm.academic_class_id" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" required>
                                        <option value="">Class</option>
                                        <option v-for="c in registrationFilteredClasses" :key="'c-' + c.id" :value="String(c.id)">{{ c.name }}</option>
                                    </select>

                                    <select v-model="registrationForm.student_type" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" required>
                                        <option value="">Student Type</option>
                                        <option v-for="t in registrationStudentTypes" :key="'t-' + t" :value="t">{{ t }}</option>
                                    </select>

                                    <div class="pt-2">
                                        <div class="text-sm font-semibold text-slate-800">Living :</div>
                                        <div class="mt-2 flex flex-wrap items-center gap-5 text-sm text-slate-700">
                                            <label class="inline-flex items-center gap-2">
                                                <input v-model="registrationForm.living_type" type="radio" name="living_type" value="Hostel" />
                                                Hostel
                                            </label>
                                            <label class="inline-flex items-center gap-2">
                                                <input v-model="registrationForm.living_type" type="radio" name="living_type" value="Others" />
                                                Others
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <input v-model="registrationForm.mobile" type="text" placeholder="Mobile" class="h-10 w-full rounded-sm border border-slate-300 bg-[#eaf2ff] px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" required />

                                    <div class="flex items-center gap-2">
                                        <input v-model="registrationForm.password" type="password" class="h-10 w-full rounded-sm border border-slate-300 bg-[#eaf2ff] px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" required />
                                        <button type="button" class="shrink-0 rounded-sm bg-slate-100 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-200" @click="regenPassword">Auto</button>
                                    </div>

                                    <input v-model="registrationForm.email" type="email" placeholder="Email (optional)" class="h-10 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" />

                                    <textarea v-model="registrationForm.address" rows="4" placeholder="Vill, Post, Upozila, Dist" class="w-full rounded-sm border border-slate-300 bg-white px-3 py-2 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10"></textarea>

                                    <div>
                                        <div class="text-sm font-semibold text-slate-800">Picture <span class="text-xs font-semibold text-red-600">( passport size )</span></div>
                                        <input ref="registrationProfileInput" type="file" class="mt-2 block w-full text-sm" accept="image/*" required @change="onPickRegistrationProfile" />
                                        <div v-if="!registrationProfileFile" class="mt-1 text-xs font-semibold text-red-600">Picture is required</div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-10 flex flex-col items-center gap-3">
                                <button type="submit" class="rounded-sm bg-[#0b1d4d] px-10 py-2.5 text-sm font-extrabold text-white hover:bg-[#09163c]" :disabled="registrationSaving">
                                    {{ registrationSaving ? 'Submitting...' : 'SUBMIT' }}
                                </button>
                                <div class="flex flex-wrap items-center justify-center gap-4">
                                    <button type="button" class="text-sm font-bold text-slate-600 hover:underline" :disabled="registrationSaving" @click="resetRegistrationForm">Reset</button>
                                    <button type="button" class="text-sm font-bold text-[#0d6b75] hover:underline" @click="go('/login')">Already Registered? Login</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div v-if="path === '/'" class="mt-3 rounded-sm border border-slate-300 bg-white">
                <div class="relative rounded-t-md">
                    <div class="overflow-hidden rounded-t-md">
                        <div class="aspect-[16/5] w-full bg-slate-200">
                            <img
                                :src="activeSlideUrl"
                                class="h-full w-full object-cover"
                                alt="slider"
                                @error="onSlideError(slideIndex)"
                            />
                        </div>
                    </div>

                    <div class="absolute right-4 top-1/2 flex -translate-y-1/2 flex-col items-center gap-2">
                        <button
                            v-for="(s, idx) in slides"
                            :key="'dot-' + (s.id || idx)"
                            type="button"
                            class="h-2.5 w-2.5 rounded-full border border-white/70"
                            :class="idx === slideIndex ? 'bg-white' : 'bg-white/40'"
                            @click="setSlide(idx)"
                            aria-label="slide"
                        />
                    </div>

                    <div class="pointer-events-none absolute bottom-0 left-1/2 w-full max-w-6xl -translate-x-1/2 translate-y-1/2 px-2 sm:px-4">
                        <div class="pointer-events-auto grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
                            <a
                                v-for="c in quickCards"
                                :key="c.label"
                                :href="c.href"
                                class="group flex flex-col items-center gap-3 text-center"
                                @click.prevent="onCardClick(c.href)"
                            >
                                <div class="grid h-[92px] w-[92px] place-items-center rounded-full border-[5px] border-[#0d6b75] bg-white shadow-sm transition group-hover:scale-[1.02]">
                                    <div class="text-xl font-extrabold text-[#0d6b75]">{{ c.icon }}</div>
                                </div>
                                <div class="text-xs font-bold text-slate-800 sm:text-sm">{{ c.label }}</div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>


            <div v-if="path === '/notices'" class="mt-3 rounded-sm border border-slate-300 bg-white p-4">
                <div class="text-lg font-extrabold text-slate-900">College Notice</div>
                <div v-if="pageLoading" class="mt-3 text-sm text-slate-600">Loading...</div>
                <div v-else-if="pageError" class="mt-3 text-sm text-red-700">{{ pageError }}</div>
                <div v-else class="mt-3 space-y-2">
                    <button
                        v-for="n in noticeList"
                        :key="'n-' + n.id"
                        type="button"
                        class="w-full rounded-sm border border-slate-300 bg-white px-3 py-2 text-left text-sm hover:bg-slate-50"
                        @click="go(`/notices/${n.id}`)"
                    >
                        <div class="font-semibold text-slate-900">{{ n.title }}</div>
                        <div class="text-xs text-slate-600">{{ n.date || '' }}</div>
                    </button>
                </div>
            </div>

            <div v-else-if="noticeId" class="mt-3 rounded-sm border border-slate-300 bg-white p-4">
                <div v-if="pageLoading" class="text-sm text-slate-600">Loading...</div>
                <div v-else-if="pageError" class="text-sm text-red-700">{{ pageError }}</div>
                <div v-else>
                    <div class="text-lg font-extrabold text-slate-900">{{ noticeDetail?.title || 'Notice' }}</div>
                    <div class="mt-1 text-xs text-slate-600">{{ noticeDetail?.date || '' }}</div>
                    <div v-if="noticeDetail?.description" class="prose prose-sm mt-4 max-w-none" v-html="noticeDetail.description"></div>

                    <a
                        v-if="noticeDetail?.file_url"
                        :href="noticeDetail.file_url"
                        target="_blank"
                        rel="noopener"
                        class="mt-4 inline-flex rounded-sm bg-[#0d6b75] px-4 py-2 text-sm font-bold text-white hover:bg-[#0b5b63]"
                    >
                        Download
                    </a>
                </div>
            </div>

            <div v-else-if="contentSlug" class="mt-3 rounded-sm border border-slate-300 bg-white p-4">
                <div v-if="pageLoading" class="text-sm text-slate-600">Loading...</div>
                <div v-else-if="pageError" class="text-sm text-red-700">{{ pageError }}</div>
                <div v-else>
                    <div class="text-lg font-extrabold text-slate-900">{{ content?.title || '' }}</div>
                    <img v-if="content?.image_url" :src="content.image_url" class="mt-3 w-full rounded-sm border border-slate-300" />
                    <div class="prose prose-sm mt-4 max-w-none" v-html="content?.description || ''"></div>
                </div>
            </div>

            </div>
        </div>

        <a
            v-if="links.live_chat"
            :href="links.live_chat"
            target="_blank"
            rel="noopener"
            class="fixed bottom-6 right-6 z-50 rounded-full bg-[#0d6b75] px-5 py-3 text-sm font-extrabold text-white shadow-lg hover:bg-[#0b5b63]"
        >
            Live Chat
        </a>

        <div class="bg-[#e7efef] pb-0" :class="path === '/' ? 'pt-28' : 'pt-10'">
            <div class="mx-auto max-w-[1700px] px-2 sm:px-3">
                <div class="mt-6 flex flex-wrap items-center justify-center text-[13px] font-semibold text-slate-600 sm:text-sm md:text-base">
                    <a class="px-3 hover:underline" href="#">About ePayment</a>
                    <span class="mx-1 text-slate-400">|</span>
                    <a class="px-3 hover:underline" href="#">Terms &amp; Conditions</a>
                    <span class="mx-1 text-slate-400">|</span>
                    <a class="px-3 hover:underline" href="#">Refund &amp; Return Policy</a>
                    <span class="mx-1 text-slate-400">|</span>
                    <a class="px-3 hover:underline" href="#">Support Policy</a>
                    <span class="mx-1 text-slate-400">|</span>
                    <a class="px-3 hover:underline" href="#">Privacy Policy</a>
                </div>

                <div class="mt-6 flex flex-col items-center justify-center gap-3 pb-6 sm:flex-row">
                    <img
                        v-if="payWithImageOk"
                        :src="payWithImageUrl"
                        class="h-14 w-full max-w-[980px] object-contain sm:h-16 md:h-20"
                        alt="pay with"
                        @error="payWithImageOk = false"
                    />
                    <div v-else class="flex flex-wrap items-center justify-center gap-2">
                        <div class="rounded-sm border border-slate-300 bg-white px-3 py-2 text-xs font-extrabold text-slate-700">SSLCommerz</div>
                        <div class="rounded-sm border border-slate-300 bg-white px-3 py-2 text-xs font-extrabold text-slate-700">bKash</div>
                        <div class="rounded-sm border border-slate-300 bg-white px-3 py-2 text-xs font-extrabold text-slate-700">Nagad</div>
                        <div class="rounded-sm border border-slate-300 bg-white px-3 py-2 text-xs font-extrabold text-slate-700">Rocket</div>
                        <div class="rounded-sm border border-slate-300 bg-white px-3 py-2 text-xs font-extrabold text-slate-700">Visa</div>
                        <div class="rounded-sm border border-slate-300 bg-white px-3 py-2 text-xs font-extrabold text-slate-700">MasterCard</div>
                    </div>
                </div>
            </div>

            <div class="bg-[#0d6b75]">
                <div class="mx-auto max-w-[1700px] px-2 sm:px-3">
                    <div class="py-3 text-center text-sm font-semibold text-white">
                        Copyright {{ new Date().getFullYear() }} {{ collegeName }} | Support by : {{ developedByText }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import StudentInvoiceViewPage from './pages/student/StudentInvoiceViewPage.vue'
import StudentPayNowPage from './pages/student/StudentPayNowPage.vue'
import StudentDashboardPage from './pages/student/StudentDashboardPage.vue'
import StudentProfilePage from './pages/student/StudentProfilePage.vue'
import StudentProfileEditPage from './pages/student/StudentProfileEditPage.vue'
import StudentPaymentHistoryPage from './pages/student/StudentPaymentHistoryPage.vue'
import StudentFeesPage from './pages/student/StudentFeesPage.vue'
import StudentAdmitCardPage from './pages/student/StudentAdmitCardPage.vue'
import StudentResultPage from './pages/student/StudentResultPage.vue'
import StudentSubjectsPage from './pages/student/StudentSubjectsPage.vue'
import StudentChangePasswordPage from './pages/student/StudentChangePasswordPage.vue'

export default {
    name: 'FrontendApp',
    components: {
        StudentInvoiceViewPage,
        StudentPayNowPage,
        StudentDashboardPage,
        StudentProfilePage,
        StudentProfileEditPage,
        StudentPaymentHistoryPage,
        StudentFeesPage,
        StudentAdmitCardPage,
        StudentResultPage,
        StudentSubjectsPage,
        StudentChangePasswordPage,
    },
    provide() {
        return {
            app: this,
        }
    },
    data() {
        return {
            appName: window.laravel?.app_name ?? 'ERP',
            loading: false,
            error: '',
            site: {},
            notices: [],
            slides: [],
            links: {
                student_registration: '',
                student_login: '',
                live_chat: '',
            },
            slideIndex: 0,
            tickerX: 0,
            tickerTimer: null,
            sliderTimer: null,
            path: '/',
            search: '',
            pageLoading: false,
            pageError: '',
            content: null,
            noticeList: [],
            noticeDetail: null,
            payWithImageOk: true,
            registrationSystems: {},
            registrationLoading: false,
            registrationSaving: false,
            registrationError: '',
            registrationSuccess: '',
            registrationProfileFile: null,
            registrationForm: {
                academic_class_id: '',
                academic_qualification_id: '',
                academic_session_id: '',
                department_id: '',
                student_type: '',
                name: '',
                mobile: '',
                email: '',
                password: '',
                reg_no: '',
                admission_id: '',
                college_roll: '',
                gender: 'Male',
                blood_group: '',
                address: '',
                fathers_name: '',
                mothers_name: '',
                living_type: 'Others',
            },

            studentAuthLoading: false,
            studentAuthError: '',
            studentMe: null,
            studentLoginForm: {
                login: '',
                password: '',
            },

            studentDashboardLoading: false,
            studentDashboardError: '',
            studentDashboardInvoices: [],
            studentDashboardNoticeLoading: false,
            studentDashboardRecentNotices: [],

            studentPaymentHistoryLoading: false,
            studentPaymentHistoryError: '',
            studentPaymentHistoryInvoices: [],

            studentFeesLoading: false,
            studentFeesError: '',
            studentFees: [],

            studentAdmitCardLoading: false,
            studentAdmitCardError: '',
            studentAdmitCards: [],

            studentSidebarActive: 'dashboard',

            studentProfileSystems: {},
            studentProfileSystemsLoading: false,
            studentProfileSystemsError: '',
            studentProfileSaving: false,
            studentProfileSaveError: '',
            studentProfileSaveSuccess: '',
            studentProfileEditForm: {
                name: '',
                reg_no: '',
                email: '',
                mobile: '',
                gender: 'Male',
                ssc_gpa: '',
                blood_group: '',
                dob: '',
                religion: '',
                nid: '',
                fathers_name: '',
                mothers_name: '',
                passing_year: '',
                nationality: '',
                extra_curricular_activity: '',
                quota: '',
                marital_status: '',
                address: '',
                permanent_address: '',
                hostel_id: '',
                hostel_room_no: '',
                guardian_type: 'Father',
                guardian_name: '',
                guardian_mobile: '',
                guardian_relations: '',
            },
            studentProfileImageFile: null,

            applyFeesSystems: {},
            applyFeesLoading: false,
            applyFeesSubmitting: false,
            applyFeesError: '',
            applyFeesForm: {
                purpose_id: '',
                academic_session_id: '',
                academic_qualification_id: '',
                department_id: '',
                academic_class_id: '',
                name: '',
                mobile: '',
                admission_roll: '',
            },

            applyFeesTab: 'fees',
            applyFeesInvoiceLoading: false,
            applyFeesInvoiceError: '',
            applyFeesInvoice: null,
            applyFeesPayExistingLoading: false,

            onlineAdmissionSystems: {},
            onlineAdmissionLoading: false,
            onlineAdmissionSubmitting: false,
            onlineAdmissionError: '',
            onlineAdmissionCheckSpinner: false,
            onlineAdmissionCheckApplicationFees: false,
            onlineAdmissionCheckRollVerify: false,
            onlineAdmissionShowForm: false,
            onlineAdmissionDoubleVerify: false,
            onlineAdmissionSubjectChoose: false,
            onlineAdmissionAgree: false,
            onlineAdmissionDisabledFields: ['academic_qualification_id'],
            onlineAdmissionRelationReadonly: false,
            onlineAdmissionUploadDocuments: [],
            onlineAdmissionProfileFile: null,
            onlineAdmissionVerifyForm: {
                academic_session_id: '',
                department_id: '',
                academic_class_id: '',
                admission_roll: '',
            },
            onlineAdmissionForm: {
                application_invoice_no: '',
                academic_session_id: '',
                academic_qualification_id: '',
                department_id: '',
                academic_class_id: '',
                admission_roll: '',
                ssc_gpa: '',
                registration_no: '',
                name: '',
                fathers_name: '',
                mothers_name: '',
                gender: 'Male',
                dob: '',
                religion: '',
                blood_group: '',
                nid: '',
                mobile: '',
                email: '',
                address: '',
                permanent_address: '',
                guardian_type: '',
                guardian_name: '',
                guardian_mobile: '',
                guardian_relations: '',
                passing_year: '',
                nationality: '',
                extra_curricular_activity: '',
                quota: '',
                marital_status: '',
                subject_choose: [],
                documents: [],
            },

            onlineAdmissionPaymentHeadsLoading: false,
            onlineAdmissionPaymentSubmitting: false,
            onlineAdmissionPaymentError: '',
            onlineAdmissionPaymentReadonly: false,
            onlineAdmissionPaymentPayFirst: false,
            onlineAdmissionPaymentAmountReadonly: false,
            onlineAdmissionPaymentChargeAmount: '0.00',
            onlineAdmissionPaymentHeads: [],
            onlineAdmissionPaymentForm: {
                mobile: '',
                admission_roll: '',
                account_head_id: '',
                amount: '',
            },

            onlineAdmissionInvoiceLoading: false,
            onlineAdmissionInvoiceError: '',
            onlineAdmissionInvoicesShow: false,
            onlineAdmissionInvoices: [],
            onlineAdmissionInvoiceSearch: {
                mobile: '',
                admission_roll: '',
            },

            onlineAdmissionDownloadLoading: false,
            onlineAdmissionDownloadError: '',
            onlineAdmissionDownloadShow: false,
            onlineAdmissionDownloadData: null,
            onlineAdmissionDownloadPdfLoading: false,
            onlineAdmissionDownloadSearch: {
                mobile: '',
                admission_roll: '',
            },
            
            certificateSystems: {},
            certificateLoading: false,
            certificateSubmitting: false,
            certificateError: '',
            certificateLookupLoading: false,
            certificateLookupError: '',
            certificateForm: {
                template_id: '',
                academic_session_id: '',
                academic_qualification_id: '',
                department_id: '',
                academic_class_id: '',
                certificate_type: 'en',
                mobile: '',
                application_id: '',
                name: '',
                student_name_en: '',
                student_name_bn: '',
                fathers_name_en: '',
                fathers_name_bn: '',
                mothers_name_en: '',
                mothers_name_bn: '',
                academic_year_en: '',
                academic_year_bn: '',
                registration_no_en: '',
                registration_no_bn: '',
                exam_roll_en: '',
                exam_roll_bn: '',
                exam_year_en: '',
                exam_year_bn: '',
                gpa_en: '',
                gpa_bn: '',
                division_en: '',
                division_bn: '',
            },
        }
    },
    computed: {
        collegeName() {
            return this.site?.college_name || this.site?.title || this.appName
        },
        payWithImageUrl() {
            return '/images/ssl-logo.webp'
        },
        hotline() {
            return this.site?.mobile1 || this.site?.college_phone || ''
        },
        developedByText() {
            return this.site?.developed_by || 'Dynamic Host BD'
        },
        noticeText() {
            const titles = (this.notices || []).map((n) => (n?.title ? String(n.title) : '')).filter(Boolean)
            return titles.join('    ►    ')
        },
        tickerStyle() {
            return {
                transform: `translateX(${this.tickerX}px)`,
                transition: 'transform 0.03s linear',
            }
        },
        activeSlideUrl() {
            const s = Array.isArray(this.slides) ? this.slides[this.slideIndex] : null
            return s?.url || '/images/Scholarship-Slider-1920x600.png'
        },
        contentSlug() {
            const m = String(this.path || '').match(/^\/content\/(.+)$/)
            return m ? decodeURIComponent(m[1]) : ''
        },
        noticeId() {
            const m = String(this.path || '').match(/^\/notices\/(\d+)$/)
            return m ? Number(m[1]) : 0
        },
        studentInvoiceId() {
            const m = String(this.path || '').match(/^\/student\/invoices\/(\d+)$/)
            return m ? Number(m[1]) : 0
        },
        topButtons() {
            const btns = []
            if (this.studentMe) {
                return btns
            }
            btns.push({ label: 'শিক্ষার্থী রেজিস্ট্রেশন', href: '/registration' })
            btns.push({ label: 'শিক্ষার্থী লগইন', href: '/login' })
            return btns
        },
        quickCards() {
            const base = [
                { label: 'College Notice', icon: 'N', href: '/notices' },
                { label: 'Application Fee', icon: 'F', href: '/apply-fees' },
                { label: 'Online Admission', icon: 'A', href: '/online-admission' },
            ]

            if (this.studentMe) {
                base.push({ label: 'Dashboard', icon: 'D', href: '/dashboard' })
            } else {
                base.push({ label: 'Student Registration', icon: 'R', href: '/registration' })
                base.push({ label: 'Student Login', icon: 'L', href: '/login' })
            }

            base.push({ label: 'eLarning', icon: 'E', href: '/content/elearning' })
            return base
        },
        registrationSessionsSorted() {
            const list = Array.isArray(this.registrationSystems?.academic_sessions) ? [...this.registrationSystems.academic_sessions] : []
            return list.sort((a, b) => this.registrationSessionSortKey(b) - this.registrationSessionSortKey(a))
        },
        registrationQualifications() {
            const list = this.registrationSystems?.academic_qualifications
            return Array.isArray(list) ? list : []
        },
        registrationDepartments() {
            const list = this.registrationSystems?.departments
            return Array.isArray(list) ? list : []
        },
        registrationClasses() {
            const list = this.registrationSystems?.academic_classes
            return Array.isArray(list) ? list : []
        },
        registrationDepartmentQualidactions() {
            const list = this.registrationSystems?.department_qualidactions
            return Array.isArray(list) ? list : []
        },
        registrationStudentTypes() {
            const list = this.registrationSystems?.student_types
            return Array.isArray(list) ? list : ['Regular', 'Irregular']
        },
        registrationBloodGroups() {
            const list = this.registrationSystems?.blood_groups
            return Array.isArray(list) ? list : []
        },
        registrationSelectedQualificationName() {
            const id = this.registrationForm?.academic_qualification_id
            const q = (this.registrationQualifications || []).find((x) => String(x?.id) === String(id))
            return String(q?.name || '')
        },
        registrationDepartmentRequired() {
            const name = this.registrationSelectedQualificationName
            if (!name) return true
            const n = name.trim().toLowerCase()
            if (n === 'hsc' || n === 'degree') return false
            return true
        },
        registrationFilteredDepartments() {
            const qualificationId = this.registrationForm?.academic_qualification_id
            if (!qualificationId) return []
            const allowedDeptIds = new Set(
                this.registrationDepartmentQualidactions
                    .filter((r) => String(r.academic_qualification_id) === String(qualificationId))
                    .map((r) => String(r.department_id))
            )
            return this.registrationDepartments.filter((d) => allowedDeptIds.has(String(d.id)))
        },
        registrationFilteredClasses() {
            const qualificationId = this.registrationForm?.academic_qualification_id
            if (!qualificationId) return []
            return this.registrationClasses.filter((c) => String(c.academic_qualification_id) === String(qualificationId))
        },
        applyFeesSessionsSorted() {
            const list = Array.isArray(this.applyFeesSystems?.academic_sessions) ? [...this.applyFeesSystems.academic_sessions] : []
            return list.sort((a, b) => this.registrationSessionSortKey(b) - this.registrationSessionSortKey(a))
        },
        applyFeesQualifications() {
            const list = this.applyFeesSystems?.academic_qualifications
            return Array.isArray(list) ? list : []
        },
        applyFeesDepartments() {
            const list = this.applyFeesSystems?.departments
            return Array.isArray(list) ? list : []
        },
        applyFeesClasses() {
            const list = this.applyFeesSystems?.academic_classes
            return Array.isArray(list) ? list : []
        },
        applyFeesDepartmentQualidactions() {
            const list = this.applyFeesSystems?.department_qualidactions
            return Array.isArray(list) ? list : []
        },
        applyFeesPurposes() {
            const list = this.applyFeesSystems?.purposes
            return Array.isArray(list) ? list : []
        },
        applyFeesFilteredDepartments() {
            const qId = this.applyFeesForm?.academic_qualification_id
            if (!qId) return this.applyFeesDepartments
            const allowed = this.applyFeesDepartmentQualidactions
                .filter((x) => String(x?.academic_qualification_id) === String(qId))
                .map((x) => String(x?.department_id))
            if (!allowed.length) return this.applyFeesDepartments
            return this.applyFeesDepartments.filter((d) => allowed.includes(String(d?.id)))
        },
        applyFeesFilteredClasses() {
            const qId = this.applyFeesForm?.academic_qualification_id
            if (!qId) return this.applyFeesClasses
            return this.applyFeesClasses.filter((c) => String(c?.academic_qualification_id) === String(qId))
        },
        applyFeesSelectedAmountText() {
            const id = this.applyFeesForm?.purpose_id
            const p = this.applyFeesPurposes.find((x) => String(x?.id) === String(id))
            const amt = Number(p?.amount || 0) || 0
            return amt > 0 ? amt.toFixed(2) + ' BDT' : '—'
        },

        certificateSessionsSorted() {
            const list = Array.isArray(this.certificateSystems?.academic_sessions) ? [...this.certificateSystems.academic_sessions] : []
            return list.sort((a, b) => this.registrationSessionSortKey(b) - this.registrationSessionSortKey(a))
        },
        certificateQualifications() {
            const list = this.certificateSystems?.academic_qualifications
            return Array.isArray(list) ? list : []
        },
        certificateDepartments() {
            const list = this.certificateSystems?.departments
            return Array.isArray(list) ? list : []
        },
        certificateClasses() {
            const list = this.certificateSystems?.academic_classes
            return Array.isArray(list) ? list : []
        },
        certificateDepartmentQualidactions() {
            const list = this.certificateSystems?.department_qualidactions
            return Array.isArray(list) ? list : []
        },
        certificateTemplates() {
            const list = this.certificateSystems?.templates
            return Array.isArray(list) ? list : []
        },
        certificateFilteredDepartments() {
            const qId = this.certificateForm?.academic_qualification_id
            if (!qId) return this.certificateDepartments
            const allowed = this.certificateDepartmentQualidactions
                .filter((x) => String(x?.academic_qualification_id) === String(qId))
                .map((x) => String(x?.department_id))
            if (!allowed.length) return this.certificateDepartments
            return this.certificateDepartments.filter((d) => allowed.includes(String(d?.id)))
        },
        certificateFilteredClasses() {
            const qId = this.certificateForm?.academic_qualification_id
            if (!qId) return this.certificateClasses
            return this.certificateClasses.filter((c) => String(c?.academic_qualification_id) === String(qId))
        },
        certificateFilteredTemplates() {
            const qId = this.certificateForm?.academic_qualification_id
            if (!qId) return this.certificateTemplates
            const list = this.certificateTemplates
            return list.filter((t) => !t?.academic_qualification_id || String(t.academic_qualification_id) === String(qId))
        },
        certificateSelectedAmountText() {
            const id = this.certificateForm?.template_id
            const t = this.certificateTemplates.find((x) => String(x?.id) === String(id))
            const amt = Number(t?.amount || 0) || 0
            return amt > 0 ? amt.toFixed(2) + ' BDT' : '—'
        },
        certificateDepartmentRequired() {
            return (this.certificateDepartments || []).length > 0
        },
        certificateClassRequired() {
            return (this.certificateClasses || []).length > 0
        },
        certificateEnDisabled() {
            return String(this.certificateForm?.certificate_type || 'en') === 'bn'
        },
        certificateBnDisabled() {
            return String(this.certificateForm?.certificate_type || 'en') === 'en'
        },
        applyFeesQuery() {
            const qs = String(this.search || '')
            try {
                return new URLSearchParams(qs.startsWith('?') ? qs : `?${qs}`)
            } catch {
                return new URLSearchParams()
            }
        },
        applyFeesStatus() {
            return String(this.applyFeesQuery.get('status') || '')
        },
        applyFeesTranId() {
            return String(this.applyFeesQuery.get('tran_id') || '')
        },
        applyFeesStatusMessage() {
            const st = this.applyFeesStatus
            if (st === 'success') return 'Payment successful'
            if (st === 'failed') return 'Payment failed'
            if (st === 'cancelled') return 'Payment cancelled'
            return ''
        },
        applyFeesStatusClass() {
            const st = this.applyFeesStatus
            if (st === 'success') return 'border-emerald-200 bg-emerald-50 text-emerald-900'
            if (st === 'failed') return 'border-red-200 bg-red-50 text-red-800'
            if (st === 'cancelled') return 'border-amber-200 bg-amber-50 text-amber-900'
            return 'border-slate-300 bg-slate-50 text-slate-700'
        },
        applyFeesTabQuery() {
            return String(this.applyFeesQuery.get('tab') || '')
        },

        onlineAdmissionQuery() {
            const qs = String(this.search || '')
            try {
                return new URLSearchParams(qs.startsWith('?') ? qs : `?${qs}`)
            } catch {
                return new URLSearchParams()
            }
        },
        onlineAdmissionStatus() {
            return String(this.onlineAdmissionQuery.get('status') || '')
        },
        onlineAdmissionTranId() {
            return String(this.onlineAdmissionQuery.get('tran_id') || '')
        },
        onlineAdmissionStatusMessage() {
            const st = this.onlineAdmissionStatus
            if (st === 'success') return 'Payment successful'
            if (st === 'failed') return 'Payment failed'
            if (st === 'cancelled') return 'Payment cancelled'
            return ''
        },
        onlineAdmissionStatusClass() {
            const st = this.onlineAdmissionStatus
            if (st === 'success') return 'border-emerald-200 bg-emerald-50 text-emerald-900'
            if (st === 'failed') return 'border-red-200 bg-red-50 text-red-800'
            if (st === 'cancelled') return 'border-amber-200 bg-amber-50 text-amber-900'
            return 'border-slate-300 bg-slate-50 text-slate-700'
        },
        onlineAdmissionSystemsGlobal() {
            return this.onlineAdmissionSystems?.global || {}
        },
        onlineAdmissionQualifications() {
            const list = this.onlineAdmissionSystemsGlobal?.academic_qualifications
            const all = Array.isArray(list) ? list : []
            return all.filter((q) => (q?.online_admission === undefined ? true : String(q?.online_admission || 0) === '1'))
        },
        onlineAdmissionSessions() {
            const list = this.onlineAdmissionSystemsGlobal?.academic_sessions
            const all = Array.isArray(list) ? list : []
            return all.filter((s) => (s?.online_admission === undefined ? true : String(s?.online_admission || 0) === '1'))
        },
        onlineAdmissionDepartments() {
            const list = this.onlineAdmissionSystemsGlobal?.departments
            return Array.isArray(list) ? list : []
        },
        onlineAdmissionClasses() {
            const list = this.onlineAdmissionSystemsGlobal?.academic_classes
            return Array.isArray(list) ? list : []
        },
        onlineAdmissionDepartmentQualidactions() {
            const list = this.onlineAdmissionSystemsGlobal?.department_qualidactions
            return Array.isArray(list) ? list : []
        },
        onlineAdmissionReligions() {
            const list = this.onlineAdmissionSystemsGlobal?.religions
            return Array.isArray(list) ? list : []
        },
        onlineAdmissionBloodGroups() {
            const list = this.onlineAdmissionSystemsGlobal?.blood_groups
            return Array.isArray(list) ? list : []
        },
        onlineAdmissionSelectedQualification() {
            const id = this.onlineAdmissionForm?.academic_qualification_id
            return (this.onlineAdmissionQualifications || []).find((x) => String(x?.id) === String(id)) || null
        },
        onlineAdmissionSelectedQualificationName() {
            return String(this.onlineAdmissionSelectedQualification?.name || '')
        },
        onlineAdmissionFilteredDepartments() {
            const qualificationId = this.onlineAdmissionForm?.academic_qualification_id
            if (!qualificationId) return []
            const allowedDeptIds = new Set(
                this.onlineAdmissionDepartmentQualidactions
                    .filter((r) => String(r.academic_qualification_id) === String(qualificationId))
                    .map((r) => String(r.department_id))
            )
            return this.onlineAdmissionDepartments.filter((d) => allowedDeptIds.has(String(d.id)))
        },
        onlineAdmissionFilteredClasses() {
            const qualificationId = this.onlineAdmissionForm?.academic_qualification_id
            if (!qualificationId) return []
            return this.onlineAdmissionClasses.filter((c) => String(c.academic_qualification_id) === String(qualificationId))
        },
    },
    async created() {
        this.path = window.location.pathname || '/'
        this.search = window.location.search || ''
        window.addEventListener('popstate', this.onPop)

        await this.load()
        this.startTicker()
        this.startSliderAuto()
        await this.routeChanged()
    },
    beforeUnmount() {
        window.removeEventListener('popstate', this.onPop)
        if (this.tickerTimer) window.clearInterval(this.tickerTimer)
        if (this.sliderTimer) window.clearInterval(this.sliderTimer)
        this.tickerTimer = null
        this.sliderTimer = null
    },
    methods: {
        setApplyFeesTab(tab) {
            const next = tab === 'certificate' ? 'certificate' : 'fees'
            this.applyFeesTab = next

            const params = new URLSearchParams(String(this.search || ''))
            if (next === 'certificate') {
                params.set('tab', 'certificate')
            } else {
                params.delete('tab')
            }

            const qs = params.toString()
            const nextSearch = qs ? `?${qs}` : ''
            const full = this.path + nextSearch
            if ((window.location.pathname + window.location.search) !== full) {
                window.history.pushState({}, '', full)
            }
            this.search = nextSearch

            if (next === 'certificate') {
                this.loadCertificateSystems()
            } else {
                this.loadApplyFeesSystems()
            }
        },
        syncApplyFeesTabFromQuery() {
            const q = this.applyFeesTabQuery
            if (q === 'certificate') {
                this.applyFeesTab = 'certificate'
            } else {
                this.applyFeesTab = 'fees'
            }
        },
        onCardClick(href) {
            this.go(href)
        },
        async load() {
            if (this.loading) return
            this.loading = true
            this.error = ''
            try {
                const res = await window.axios.get('/api/public/home')
                this.site = res?.data?.site || {}
                this.notices = Array.isArray(res?.data?.notices) ? res.data.notices : []
                this.slides = Array.isArray(res?.data?.sliders) ? res.data.sliders : []
                this.links = res?.data?.links || this.links
                if (!Array.isArray(this.slides)) this.slides = []
                if (this.slideIndex >= this.slides.length) this.slideIndex = 0
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load site data.'
                this.site = {}
                this.notices = []
                this.slides = []
            } finally {
                this.loading = false
            }
        },
        setSlide(idx) {
            const n = Number(idx)
            if (!Number.isFinite(n)) return
            if (!Array.isArray(this.slides) || this.slides.length === 0) return
            if (n < 0 || n >= this.slides.length) return
            this.slideIndex = n
            this.startSliderAuto()
        },
        onSlideError(idx) {
            const n = Number(idx)
            if (!Number.isFinite(n)) return
            if (!Array.isArray(this.slides)) return
            const s = this.slides[n]
            if (!s) return
            if (typeof s === 'object') {
                s.url = '/images/Scholarship-Slider-1920x600.png'
            }
        },
        registrationSessionSortKey(s) {
            const name = String(s?.name || '')
            const m = name.match(/(\d{4})/)
            if (m && m[1]) return Number(m[1])
            return 0
        },
        async loadRegistrationSystems() {
            if (this.registrationLoading) return
            this.registrationLoading = true
            this.registrationError = ''
            try {
                const res = await window.axios.get('/api/public/registration/systems')
                this.registrationSystems = res?.data?.global || {}
            } catch (e) {
                this.registrationError = e?.response?.data?.message || 'Failed to load registration data.'
                this.registrationSystems = {}
            } finally {
                this.registrationLoading = false
            }
        },
        onPickRegistrationProfile(e) {
            const f = e?.target?.files?.[0]
            this.registrationProfileFile = f || null
        },
        regenPassword() {
            const n = Math.floor(100000 + Math.random() * 900000)
            this.registrationForm.password = String(n)
        },
        resetRegistrationForm() {
            this.registrationError = ''
            this.registrationSuccess = ''
            this.registrationProfileFile = null
            this.registrationForm = {
                academic_class_id: '',
                academic_qualification_id: '',
                academic_session_id: '',
                department_id: '',
                student_type: '',
                name: '',
                mobile: '',
                email: '',
                password: '',
                reg_no: '',
                admission_id: '',
                college_roll: '',
                gender: 'Male',
                blood_group: '',
                address: '',
                fathers_name: '',
                mothers_name: '',
                living_type: 'Others',
            }
            const input = this.$refs?.registrationProfileInput
            if (input) {
                try {
                    input.value = ''
                } catch {
                }
            }
        },
        async submitRegistration() {
            if (this.registrationSaving) return
            this.registrationSaving = true
            this.registrationError = ''
            this.registrationSuccess = ''
            try {
                const fd = new FormData()
                const form = this.registrationForm || {}
                Object.keys(form).forEach((k) => {
                    fd.append(k, form[k] ?? '')
                })
                if (this.registrationProfileFile) {
                    fd.append('profile', this.registrationProfileFile)
                }
                await window.axios.post('/api/public/registration', fd, {
                    headers: { 'Content-Type': 'multipart/form-data' },
                })
                this.registrationSuccess = 'Registration successful.'
                this.resetRegistrationForm()
                this.go('/login')
            } catch (e) {
                if (Number(e?.response?.status) === 422) {
                    const errors = e?.response?.data?.errors
                    if (errors && typeof errors === 'object') {
                        const firstKey = Object.keys(errors)[0]
                        const firstMsg = Array.isArray(errors[firstKey]) ? errors[firstKey][0] : errors[firstKey]
                        this.registrationError = String(firstMsg || e?.response?.data?.message || 'Validation failed.')
                    } else {
                        this.registrationError = String(e?.response?.data?.message || 'Validation failed.')
                    }
                } else {
                    this.registrationError = e?.response?.data?.message || 'Registration failed.'
                }
            } finally {
                this.registrationSaving = false
            }
        },
        async loadStudentMe() {
            if (this.studentAuthLoading) return !!this.studentMe
            this.studentAuthLoading = true
            this.studentAuthError = ''
            try {
                const res = await window.axios.get('/api/public/auth/me')
                this.studentMe = res?.data?.student || res?.data || null
                return !!this.studentMe
            } catch (e) {
                this.studentMe = null
                return false
            } finally {
                this.studentAuthLoading = false
            }
        },
        async submitStudentLogin() {
            if (this.studentAuthLoading) return
            this.studentAuthLoading = true
            this.studentAuthError = ''
            try {
                const res = await window.axios.post('/api/public/auth/login', this.studentLoginForm)
                this.studentMe = res?.data?.student || res?.data || null
                this.go('/dashboard')
            } catch (e) {
                this.studentAuthError = e?.response?.data?.message || 'Login failed.'
            } finally {
                this.studentAuthLoading = false
            }
        },
        async doStudentLogout() {
            if (this.studentAuthLoading) return
            this.studentAuthLoading = true
            this.studentAuthError = ''
            try {
                await window.axios.post('/api/public/auth/logout')
            } catch {
            } finally {
                this.studentMe = null
                this.studentAuthLoading = false
                this.go('/')
            }
        },
        async loadStudentDashboard() {
            if (this.studentDashboardLoading) return
            this.studentDashboardLoading = true
            this.studentDashboardError = ''
            try {
                const res = await window.axios.get('/api/public/student/dashboard')
                this.studentDashboardInvoices = Array.isArray(res?.data?.invoices) ? res.data.invoices : []
            } catch (e) {
                this.studentDashboardInvoices = []
                this.studentDashboardError = e?.response?.data?.message || 'Failed to load dashboard data.'
            } finally {
                this.studentDashboardLoading = false
            }
        },
        async loadStudentDashboardNotices() {
            if (this.studentDashboardNoticeLoading) return
            this.studentDashboardNoticeLoading = true
            try {
                const res = await window.axios.get('/api/public/notices')
                const list = Array.isArray(res?.data?.data) ? res.data.data : []
                this.studentDashboardRecentNotices = list.slice(0, 10)
            } catch {
                this.studentDashboardRecentNotices = []
            } finally {
                this.studentDashboardNoticeLoading = false
            }
        },
        async loadStudentPaymentHistory() {
            if (this.studentPaymentHistoryLoading) return
            this.studentPaymentHistoryLoading = true
            this.studentPaymentHistoryError = ''
            try {
                const res = await window.axios.get('/api/public/student/payment-history')
                const d = res?.data
                if (Array.isArray(d?.invoices)) {
                    this.studentPaymentHistoryInvoices = d.invoices
                } else if (Array.isArray(d?.data)) {
                    this.studentPaymentHistoryInvoices = d.data
                } else {
                    this.studentPaymentHistoryInvoices = []
                }
            } catch (e) {
                this.studentPaymentHistoryInvoices = []
                this.studentPaymentHistoryError = e?.response?.data?.message || 'Failed to load payment history.'
            } finally {
                this.studentPaymentHistoryLoading = false
            }
        },
        async loadStudentFees() {
            if (this.studentFeesLoading) return
            this.studentFeesLoading = true
            this.studentFeesError = ''
            try {
                const res = await window.axios.get('/api/public/student/fees')
                this.studentFees = Array.isArray(res?.data?.fees) ? res.data.fees : []
            } catch (e) {
                this.studentFees = []
                this.studentFeesError = e?.response?.data?.message || 'Failed to load fees list.'
            } finally {
                this.studentFeesLoading = false
            }
        },
        async loadStudentAdmitCards() {
            if (this.studentAdmitCardLoading) return
            this.studentAdmitCardLoading = true
            this.studentAdmitCardError = ''
            try {
                const res = await window.axios.get('/api/public/student/admit-cards')
                this.studentAdmitCards = Array.isArray(res?.data?.admit_cards) ? res.data.admit_cards : []
            } catch (e) {
                this.studentAdmitCards = []
                this.studentAdmitCardError = e?.response?.data?.message || 'Failed to load admit card.'
            } finally {
                this.studentAdmitCardLoading = false
            }
        },
        studentDashboardViewInvoice(inv) {
            const id = inv?.id
            if (!id) return
            this.go(`/student/invoices/${encodeURIComponent(String(id))}`)
        },
        downloadStudentInvoice(id) {
            if (!id) return
            const url = `/api/public/student/invoices/${encodeURIComponent(String(id))}/download`
            window.open(url, '_blank')
        },
        printStudentInvoice() {
            const iframe = this.$refs?.studentInvoiceIframe
            const win = iframe?.contentWindow
            if (!win) return
            try {
                win.focus()
                win.print()
            } catch {
            }
        },
        studentSidebarGo(key, href) {
            this.studentSidebarActive = String(key || 'dashboard')
            this.go(href)
        },
        studentProfileNameById(list, id) {
            const needle = String(id || '')
            if (!needle) return ''
            const arr = Array.isArray(list) ? list : []
            const row = arr.find((x) => String(x?.id ?? '') === needle)
            return row?.name || ''
        },
        async loadStudentProfileSystems() {
            if (this.studentProfileSystemsLoading) return
            this.studentProfileSystemsLoading = true
            this.studentProfileSystemsError = ''
            try {
                const res = await window.axios.get('/api/public/student/profile/systems')
                this.studentProfileSystems = res?.data?.global || {}
            } catch (e) {
                this.studentProfileSystemsError = e?.response?.data?.message || 'Failed to load profile systems.'
                this.studentProfileSystems = {}
            } finally {
                this.studentProfileSystemsLoading = false
            }
        },
        initStudentProfileEditForm() {
            const s = this.studentMe || {}
            this.studentProfileEditForm = {
                ...this.studentProfileEditForm,
                name: s?.name || '',
                reg_no: s?.reg_no || '',
                email: s?.email || '',
                mobile: s?.mobile || '',
                gender: s?.gender || 'Male',
                ssc_gpa: s?.ssc_gpa != null ? String(s.ssc_gpa) : '',
                blood_group: s?.blood_group || '',
                dob: s?.dob || '',
                religion: s?.religion || '',
                nid: s?.nid || '',
                fathers_name: s?.fathers_name || '',
                mothers_name: s?.mothers_name || '',
                passing_year: s?.passing_year != null ? String(s.passing_year) : '',
                nationality: s?.nationality || '',
                extra_curricular_activity: s?.extra_curricular_activity || '',
                quota: s?.quota || '',
                marital_status: s?.marital_status || '',
                address: s?.address || '',
                permanent_address: s?.permanent_address || '',
                hostel_id: s?.hostel_id != null ? String(s.hostel_id) : '',
                hostel_room_no: s?.hostel_room_no || '',
                guardian_type: s?.guardian_type || 'Father',
                guardian_name: s?.guardian_name || '',
                guardian_mobile: s?.guardian_mobile || '',
                guardian_relations: s?.guardian_relations || '',
            }
            this.studentProfileSaveError = ''
            this.studentProfileSaveSuccess = ''
            this.studentProfileImageFile = null
            const input = this.$refs?.studentProfileFileInput
            if (input) {
                try {
                    input.value = ''
                } catch {
                }
            }
        },
        onPickStudentProfileImage(e) {
            const f = e?.target?.files?.[0]
            this.studentProfileImageFile = f || null
        },
        async submitStudentProfileUpdate() {
            if (this.studentProfileSaving) return
            this.studentProfileSaving = true
            this.studentProfileSaveError = ''
            this.studentProfileSaveSuccess = ''
            try {
                const fd = new FormData()
                const form = this.studentProfileEditForm || {}
                Object.keys(form).forEach((k) => {
                    fd.append(k, form[k] ?? '')
                })
                if (this.studentProfileImageFile) {
                    fd.append('profile', this.studentProfileImageFile)
                }
                const res = await window.axios.post('/api/public/student/profile', fd, {
                    headers: { 'Content-Type': 'multipart/form-data' },
                })
                const st = res?.data?.student || null
                if (st) {
                    this.studentMe = st
                }
                this.studentProfileSaveSuccess = 'Profile updated successfully.'
                this.go('/student/profile')
            } catch (e) {
                if (Number(e?.response?.status) === 422) {
                    const errors = e?.response?.data?.errors
                    if (errors && typeof errors === 'object') {
                        const firstKey = Object.keys(errors)[0]
                        const firstMsg = Array.isArray(errors[firstKey]) ? errors[firstKey][0] : errors[firstKey]
                        this.studentProfileSaveError = String(firstMsg || e?.response?.data?.message || 'Validation failed.')
                    } else {
                        this.studentProfileSaveError = String(e?.response?.data?.message || 'Validation failed.')
                    }
                } else {
                    this.studentProfileSaveError = e?.response?.data?.message || 'Failed to update profile.'
                }
            } finally {
                this.studentProfileSaving = false
            }
        },
        async loadApplyFeesSystems() {
            if (this.applyFeesLoading) return
            this.applyFeesLoading = true
            this.applyFeesError = ''
            try {
                const res = await window.axios.get('/api/public/apply-fees/systems')
                this.applyFeesSystems = res?.data?.global || {}
            } catch (e) {
                this.applyFeesError = e?.response?.data?.message || 'Failed to load application fee data.'
                this.applyFeesSystems = {}
            } finally {
                this.applyFeesLoading = false
            }
        },
        async loadOnlineAdmissionSystems() {
            if (this.onlineAdmissionLoading) return
            this.onlineAdmissionLoading = true
            this.onlineAdmissionError = ''
            try {
                const res = await window.axios.get('/api/public/online-admission/systems')
                this.onlineAdmissionSystems = res?.data || {}
            } catch (e) {
                this.onlineAdmissionError = e?.response?.data?.message || 'Failed to load online admission data.'
                this.onlineAdmissionSystems = {}
            } finally {
                this.onlineAdmissionLoading = false
            }
        },
        onlineAdmissionSelectAcademicLevel() {
            const aqId = String(this.onlineAdmissionForm?.academic_qualification_id || '')
            if (!aqId) {
                this.onlineAdmissionError = 'Academic Level is required'
                return
            }

            this.onlineAdmissionError = ''
            this.onlineAdmissionShowForm = false
            this.onlineAdmissionCheckApplicationFees = false
            this.onlineAdmissionCheckRollVerify = false

            const qua = this.onlineAdmissionSelectedQualification
            this.onlineAdmissionCheckApplicationFees = !!Number(qua?.application_fees || 0)
            this.onlineAdmissionCheckRollVerify = !!Number(qua?.admission_roll_verify || 0)
            this.onlineAdmissionDoubleVerify = !!(Number(qua?.application_fees || 0) && Number(qua?.admission_roll_verify || 0))
            this.onlineAdmissionShowForm = !(this.onlineAdmissionCheckApplicationFees || this.onlineAdmissionCheckRollVerify)
            this.onlineAdmissionSubjectChoose = !!Number(qua?.subject_choose || 0)

            this.onlineAdmissionUploadDocuments = []
            const admissionFiles = qua?.admission_files
            if (Array.isArray(admissionFiles)) {
                this.onlineAdmissionUploadDocuments = admissionFiles
            } else if (typeof admissionFiles === 'string' && admissionFiles) {
                try {
                    const parsed = JSON.parse(admissionFiles)
                    if (Array.isArray(parsed)) this.onlineAdmissionUploadDocuments = parsed
                } catch {
                }
            }

            this.onlineAdmissionForm.documents = []
            if (this.onlineAdmissionUploadDocuments && this.onlineAdmissionUploadDocuments.length) {
                this.onlineAdmissionUploadDocuments.forEach((el) => {
                    this.onlineAdmissionForm.documents.push({
                        doc_name: el?.name_bn || el?.name_en || '',
                        file: el?.optional ? 'optional' : null,
                    })
                })
            }
        },
        async onlineAdmissionCheckFees() {
            const invoice = String(this.onlineAdmissionForm?.application_invoice_no || '')
            const roll = String(this.onlineAdmissionForm?.admission_roll || '')
            if (!invoice) {
                this.onlineAdmissionError = 'Application Invoice No. is required'
                return
            }
            if (!roll) {
                this.onlineAdmissionError = 'Admission Roll is required'
                return
            }
            this.onlineAdmissionCheckSpinner = true
            this.onlineAdmissionError = ''
            try {
                const res = await window.axios.post('/api/public/online-admission/check-application-fees', {
                    application_invoice_no: invoice,
                    admission_roll: roll,
                    academic_qualification_id: this.onlineAdmissionForm?.academic_qualification_id,
                })
                const admission = res?.data?.admission || null
                if (this.onlineAdmissionDoubleVerify) {
                    this.onlineAdmissionCheckApplicationFees = false
                    this.onlineAdmissionDoubleVerify = false
                } else {
                    this.onlineAdmissionShowForm = true
                    if (admission?.academic_session_id) this.onlineAdmissionForm.academic_session_id = String(admission.academic_session_id)
                    if (admission?.name) this.onlineAdmissionForm.name = String(admission.name)
                    if (admission?.mobile) this.onlineAdmissionForm.mobile = String(admission.mobile)
                }
                this.onlineAdmissionDisabledFields = Array.from(new Set([...this.onlineAdmissionDisabledFields, 'academic_session_id', 'admission_roll']))
            } catch (e) {
                this.onlineAdmissionShowForm = false
                this.onlineAdmissionError = e?.response?.data?.message || 'Sorry!! Applicatioin Fees Not Found'
            } finally {
                this.onlineAdmissionCheckSpinner = false
            }
        },
        async onlineAdmissionVerifyRoll() {
            const f = this.onlineAdmissionVerifyForm || {}
            if (!String(f.academic_session_id || '')) {
                this.onlineAdmissionError = 'Academic Session is required'
                return
            }
            if (!String(f.department_id || '')) {
                this.onlineAdmissionError = 'Department/Group is required'
                return
            }
            if (!String(f.academic_class_id || '')) {
                this.onlineAdmissionError = 'Academic Class is required'
                return
            }
            if (!String(f.admission_roll || '')) {
                this.onlineAdmissionError = 'SSC / Admission Roll is required'
                return
            }

            this.onlineAdmissionCheckSpinner = true
            this.onlineAdmissionError = ''
            try {
                const payload = {
                    academic_session_id: f.academic_session_id,
                    academic_qualification_id: this.onlineAdmissionForm?.academic_qualification_id,
                    department_id: f.department_id,
                    academic_class_id: f.academic_class_id,
                    admission_roll: f.admission_roll,
                }
                const res = await window.axios.post('/api/public/online-admission/check-admission-roll', payload)
                const name = res?.data?.data?.name
                this.onlineAdmissionShowForm = true
                if (name) this.onlineAdmissionForm.name = String(name)
                this.onlineAdmissionForm.admission_roll = String(f.admission_roll)
                this.onlineAdmissionForm.academic_session_id = String(f.academic_session_id)
                this.onlineAdmissionForm.department_id = String(f.department_id)
                this.onlineAdmissionForm.academic_class_id = String(f.academic_class_id)
                this.onlineAdmissionDisabledFields = Array.from(new Set([...this.onlineAdmissionDisabledFields, 'name', 'admission_roll', 'academic_session_id', 'department_id', 'academic_class_id']))
            } catch (e) {
                this.onlineAdmissionShowForm = false
                this.onlineAdmissionError = e?.response?.data?.message || 'Sorry!! Roll Not Found'
            } finally {
                this.onlineAdmissionCheckSpinner = false
            }
        },
        onPickOnlineAdmissionProfile(e) {
            const f = e?.target?.files?.[0]
            this.onlineAdmissionProfileFile = f || null
        },
        onOnlineAdmissionGuardianType() {
            const val = String(this.onlineAdmissionForm?.guardian_type || '')
            this.onlineAdmissionRelationReadonly = true
            if (val === 'Father') {
                this.onlineAdmissionForm.guardian_name = this.onlineAdmissionForm.fathers_name
                this.onlineAdmissionForm.guardian_relations = val
            } else if (val === 'Mother') {
                this.onlineAdmissionForm.guardian_name = this.onlineAdmissionForm.mothers_name
                this.onlineAdmissionForm.guardian_relations = val
            } else {
                this.onlineAdmissionForm.guardian_name = ''
                this.onlineAdmissionForm.guardian_relations = ''
                this.onlineAdmissionRelationReadonly = false
            }
        },
        async onlineAdmissionDocumentUpload(e, idx) {
            const file = e?.target?.files?.[0]
            if (!file) return
            const name = String(file?.name || '')
            const ext = name.split('.').pop().toLowerCase()
            if (!['jpeg', 'jpg', 'png'].includes(ext)) {
                this.onlineAdmissionError = 'Only formats are allowed : jpeg, jpg, png'
                return
            }

            this.onlineAdmissionError = ''
            const fd = new FormData()
            fd.append('file', file)
            try {
                const res = await window.axios.post('/api/public/online-admission/document-upload', fd, {
                    headers: { 'Content-Type': 'multipart/form-data' },
                })
                const p = res?.data
                if (p && this.onlineAdmissionForm?.documents?.[idx]) {
                    this.onlineAdmissionForm.documents[idx].file = p
                }
            } catch (e2) {
                this.onlineAdmissionError = e2?.response?.data?.message || 'Failed to upload file'
            }
        },
        onlineAdmissionResetForm() {
            const ok = window.confirm('Are you sure ?')
            if (!ok) return

            this.onlineAdmissionDisabledFields = ['academic_qualification_id']
            this.onlineAdmissionShowForm = false
            this.onlineAdmissionDoubleVerify = false
            this.onlineAdmissionCheckRollVerify = false
            this.onlineAdmissionCheckApplicationFees = false
            this.onlineAdmissionSubjectChoose = false
            this.onlineAdmissionAgree = false
            this.onlineAdmissionRelationReadonly = false
            this.onlineAdmissionUploadDocuments = []
            this.onlineAdmissionVerifyForm = {
                academic_session_id: '',
                department_id: '',
                academic_class_id: '',
                admission_roll: '',
            }
            this.onlineAdmissionProfileFile = null
            const input = this.$refs?.onlineAdmissionProfileInput
            if (input) {
                try {
                    input.value = ''
                } catch {
                }
            }
            this.onlineAdmissionForm = {
                application_invoice_no: '',
                academic_session_id: '',
                academic_qualification_id: '',
                department_id: '',
                academic_class_id: '',
                admission_roll: '',
                ssc_gpa: '',
                registration_no: '',
                name: '',
                fathers_name: '',
                mothers_name: '',
                gender: 'Male',
                dob: '',
                religion: '',
                blood_group: '',
                nid: '',
                mobile: '',
                email: '',
                address: '',
                permanent_address: '',
                guardian_type: '',
                guardian_name: '',
                guardian_mobile: '',
                guardian_relations: '',
                passing_year: '',
                nationality: '',
                extra_curricular_activity: '',
                quota: '',
                marital_status: '',
                subject_choose: [],
                documents: [],
            }
            this.onlineAdmissionError = ''
        },
        async onlineAdmissionSubmit() {
            if (this.onlineAdmissionSubmitting) return

            if (!this.onlineAdmissionAgree) {
                this.onlineAdmissionError = 'Please checked terms & conditions'
                return
            }

            if (!this.onlineAdmissionProfileFile) {
                this.onlineAdmissionError = 'Picture is required'
                return
            }
            if (!String(this.onlineAdmissionForm?.academic_qualification_id || '')) {
                this.onlineAdmissionError = 'Academic Level is required'
                return
            }
            if (!String(this.onlineAdmissionForm?.academic_session_id || '')) {
                this.onlineAdmissionError = 'Academic Session is required'
                return
            }
            if (!String(this.onlineAdmissionForm?.department_id || '')) {
                this.onlineAdmissionError = 'Department/Group is required'
                return
            }
            if (!String(this.onlineAdmissionForm?.academic_class_id || '')) {
                this.onlineAdmissionError = 'Academic Class is required'
                return
            }
            if (!String(this.onlineAdmissionForm?.admission_roll || '')) {
                this.onlineAdmissionError = 'Admission Roll is required'
                return
            }
            if (!String(this.onlineAdmissionForm?.name || '')) {
                this.onlineAdmissionError = 'Name is required'
                return
            }
            if (!String(this.onlineAdmissionForm?.fathers_name || '')) {
                this.onlineAdmissionError = 'Father name is required'
                return
            }
            if (!String(this.onlineAdmissionForm?.mothers_name || '')) {
                this.onlineAdmissionError = 'Mother name is required'
                return
            }
            if (!String(this.onlineAdmissionForm?.mobile || '')) {
                this.onlineAdmissionError = 'Mobile is required'
                return
            }
            if (!String(this.onlineAdmissionForm?.guardian_type || '')) {
                this.onlineAdmissionError = 'Guardian Type is required'
                return
            }
            if (!String(this.onlineAdmissionForm?.religion || '')) {
                this.onlineAdmissionError = 'Religion is required'
                return
            }
            if (!String(this.onlineAdmissionForm?.address || '')) {
                this.onlineAdmissionError = 'Present Address is required'
                return
            }

            const docs = Array.isArray(this.onlineAdmissionForm?.documents) ? this.onlineAdmissionForm.documents : []
            const missingDoc = docs.find((d) => !d?.file)
            if (missingDoc) {
                this.onlineAdmissionError = 'You need to fill empty mandatory fields'
                return
            }

            const fd = new FormData()
            fd.append('profile', this.onlineAdmissionProfileFile)
            fd.append('data', JSON.stringify({
                ...this.onlineAdmissionForm,
                check_application_fees: this.onlineAdmissionCheckApplicationFees,
            }))

            this.onlineAdmissionSubmitting = true
            this.onlineAdmissionError = ''
            try {
                const ok = window.confirm('Are sure want to submit this form ?')
                if (!ok) {
                    this.onlineAdmissionSubmitting = false
                    return
                }
                const res = await window.axios.post('/api/public/online-admission/submit', fd, {
                    headers: { 'Content-Type': 'multipart/form-data' },
                })
                const msg = res?.data?.message
                if (msg) {
                    this.onlineAdmissionError = ''
                }
                this.go('/online-admission-payment')
            } catch (e) {
                if (Number(e?.response?.status) === 422) {
                    const errors = e?.response?.data?.errors
                    if (errors && typeof errors === 'object') {
                        const firstKey = Object.keys(errors)[0]
                        const firstMsg = Array.isArray(errors[firstKey]) ? errors[firstKey][0] : errors[firstKey]
                        this.onlineAdmissionError = String(firstMsg || e?.response?.data?.message || 'Validation failed.')
                    } else {
                        this.onlineAdmissionError = String(e?.response?.data?.message || 'Validation failed.')
                    }
                } else {
                    this.onlineAdmissionError = e?.response?.data?.message || 'Failed to submit.'
                }
            } finally {
                this.onlineAdmissionSubmitting = false
            }
        },

        async onlineAdmissionGetPaymentHeads() {
            const mobile = String(this.onlineAdmissionPaymentForm?.mobile || '')
            const roll = String(this.onlineAdmissionPaymentForm?.admission_roll || '')
            if (!mobile) {
                this.onlineAdmissionPaymentError = 'Mobile No. is required'
                return
            }
            if (!roll) {
                this.onlineAdmissionPaymentError = 'Admission Roll is required'
                return
            }
            this.onlineAdmissionPaymentHeadsLoading = true
            this.onlineAdmissionPaymentError = ''
            try {
                const res = await window.axios.post('/api/public/online-admission/payment-heads', { mobile, admission_roll: roll })
                this.onlineAdmissionPaymentHeads = Array.isArray(res?.data) ? res.data : []
                this.onlineAdmissionPaymentReadonly = this.onlineAdmissionPaymentHeads.length > 0
                if (this.onlineAdmissionPaymentHeads.length === 0) {
                    this.onlineAdmissionPaymentError = 'You have already paid all the fees'
                }
            } catch (e) {
                this.onlineAdmissionPaymentHeads = []
                this.onlineAdmissionPaymentReadonly = false
                this.onlineAdmissionPaymentError = e?.response?.data?.error || e?.response?.data?.message || 'Sorry!! application not found'
            } finally {
                this.onlineAdmissionPaymentHeadsLoading = false
            }
        },
        onlineAdmissionPaymentReset() {
            this.onlineAdmissionPaymentReadonly = false
            this.onlineAdmissionPaymentForm.account_head_id = ''
            this.onlineAdmissionPaymentForm.amount = ''
            this.onlineAdmissionPaymentHeads = []
            this.onlineAdmissionPaymentPayFirst = false
            this.onlineAdmissionPaymentAmountReadonly = false
            this.onlineAdmissionPaymentChargeAmount = '0.00'
            this.onlineAdmissionPaymentError = ''
        },
        async onlineAdmissionSelectPaymentPurpose() {
            this.onlineAdmissionPaymentChargeAmount = '0.00'
            this.onlineAdmissionPaymentForm.amount = ''

            const headId = String(this.onlineAdmissionPaymentForm?.account_head_id || '')
            const find = this.onlineAdmissionPaymentHeads.find((e) => String(e?.account_head_id) === String(headId))
            if (!find) return

            const needDepend = !!Number(find?.depend_head_id || 0)
            if (needDepend) {
                this.onlineAdmissionPaymentSubmitting = true
                try {
                    const res = await window.axios.post('/api/public/online-admission/check-depend-head', {
                        id: find.id,
                        mobile: this.onlineAdmissionPaymentForm?.mobile,
                    })
                    if (res?.data?.status) {
                        this.onlineAdmissionPaymentError = String(res?.data?.message || '')
                    }
                    this.onlineAdmissionPaymentPayFirst = !!res?.data?.status
                } catch {
                    this.onlineAdmissionPaymentPayFirst = false
                } finally {
                    this.onlineAdmissionPaymentSubmitting = false
                }
            } else {
                this.onlineAdmissionPaymentPayFirst = false
            }

            this.onlineAdmissionPaymentForm.amount = String(find?.amount || '')
            if (Number(find?.service_charge || 0)) {
                const chargePer = Number(this.onlineAdmissionSystemsGlobal?.site?.admission_fees_charge_percent || this.site?.admission_fees_charge_percent || 0)
                const amt = Number(find?.amount || 0)
                this.onlineAdmissionPaymentChargeAmount = ((amt * chargePer) / 100).toFixed(2)
            }
            this.onlineAdmissionPaymentAmountReadonly = Number(find?.amount || 0) > 0
        },
        async onlineAdmissionPayNow() {
            if (this.onlineAdmissionPaymentSubmitting) return
            if (this.onlineAdmissionPaymentPayFirst) return
            const payload = {
                mobile: this.onlineAdmissionPaymentForm?.mobile,
                admission_roll: this.onlineAdmissionPaymentForm?.admission_roll,
                account_head_id: this.onlineAdmissionPaymentForm?.account_head_id,
                amount: this.onlineAdmissionPaymentForm?.amount,
            }
            this.onlineAdmissionPaymentSubmitting = true
            this.onlineAdmissionPaymentError = ''
            try {
                const res = await window.axios.post('/api/public/online-admission/payments', payload)
                const url = res?.data?.pay?.url
                if (url) {
                    window.location.href = url
                    return
                }
                this.onlineAdmissionPaymentError = res?.data?.error || 'Sorry!! Payment cannot proceed at this time, please try again'
            } catch (e) {
                this.onlineAdmissionPaymentError = e?.response?.data?.error || e?.response?.data?.message || 'Failed to submit'
            } finally {
                this.onlineAdmissionPaymentSubmitting = false
            }
        },

        async onlineAdmissionSearchInvoices() {
            const payload = {
                mobile: this.onlineAdmissionInvoiceSearch?.mobile,
                admission_roll: this.onlineAdmissionInvoiceSearch?.admission_roll,
            }
            this.onlineAdmissionInvoiceLoading = true
            this.onlineAdmissionInvoiceError = ''
            this.onlineAdmissionInvoicesShow = true
            this.onlineAdmissionInvoices = []
            try {
                const res = await window.axios.post('/api/public/online-admission/invoice', payload)
                this.onlineAdmissionInvoices = Array.isArray(res?.data) ? res.data : []
            } catch (e) {
                this.onlineAdmissionInvoices = []
                this.onlineAdmissionInvoiceError = e?.response?.data?.error || e?.response?.data?.message || 'Failed to load invoices'
            } finally {
                this.onlineAdmissionInvoiceLoading = false
            }
        },
        onlineAdmissionDownloadInvoice(id) {
            const url = `/api/public/online-admission/download-invoice/${encodeURIComponent(String(id))}`
            window.location.href = url
        },
        onlineAdmissionOpenInvoice(id) {
            const url = `/api/public/online-admission/download-invoice/${encodeURIComponent(String(id))}`
            window.open(url, '_blank')
        },

        async onlineAdmissionSearchDownloadForm() {
            const roll = String(this.onlineAdmissionDownloadSearch?.admission_roll || '')
            const mobile = String(this.onlineAdmissionDownloadSearch?.mobile || '')
            this.onlineAdmissionDownloadLoading = true
            this.onlineAdmissionDownloadError = ''
            this.onlineAdmissionDownloadShow = true
            this.onlineAdmissionDownloadData = null
            try {
                const res = await window.axios.get('/api/public/online-admission/download-form', {
                    params: { admission_roll: roll, mobile, json_data: 1 },
                })
                this.onlineAdmissionDownloadData = res?.data || null
            } catch (e) {
                this.onlineAdmissionDownloadData = null
                this.onlineAdmissionDownloadError = e?.response?.data?.message || 'Sorry!! admission form not found'
            } finally {
                this.onlineAdmissionDownloadLoading = false
            }
        },
        onlineAdmissionDownloadPdf() {
            const roll = String(this.onlineAdmissionDownloadSearch?.admission_roll || '')
            const mobile = String(this.onlineAdmissionDownloadSearch?.mobile || '')
            const url = `/api/public/online-admission/download-form?admission_roll=${encodeURIComponent(roll)}&mobile=${encodeURIComponent(mobile)}`
            this.onlineAdmissionDownloadPdfLoading = true
            window.location.href = url
            window.setTimeout(() => {
                this.onlineAdmissionDownloadPdfLoading = false
            }, 1200)
        },
        async loadCertificateSystems() {
            if (this.certificateLoading) return
            this.certificateLoading = true
            this.certificateError = ''
            try {
                const res = await window.axios.get('/api/public/certificate/systems')
                this.certificateSystems = res?.data?.global || {}
            } catch (e) {
                this.certificateError = e?.response?.data?.message || 'Failed to load certificate fees.'
                this.certificateSystems = {}
            } finally {
                this.certificateLoading = false
            }
        },
        async submitApplyFees() {
            this.applyFeesSubmitting = true
            this.applyFeesError = ''
            try {
                const res = await window.axios.post('/api/public/apply-fees/init', this.applyFeesForm)
                const url = res?.data?.gateway_url
                if (url) {
                    window.location.href = url
                    return
                }
                this.applyFeesError = 'Failed to initiate payment gateway.'
            } catch (e) {
                if (Number(e?.response?.status) === 422) {
                    const errors = e?.response?.data?.errors
                    if (errors && typeof errors === 'object') {
                        const firstKey = Object.keys(errors)[0]
                        const firstMsg = Array.isArray(errors[firstKey]) ? errors[firstKey][0] : errors[firstKey]
                        this.applyFeesError = String(firstMsg || e?.response?.data?.message || 'Validation failed.')
                    } else {
                        this.applyFeesError = String(e?.response?.data?.message || 'Validation failed.')
                    }
                } else {
                    this.applyFeesError = e?.response?.data?.message || 'Failed to submit.'
                }
            } finally {
                this.applyFeesSubmitting = false
            }
        },
        async checkApplyFeesInvoice() {
            if (this.applyFeesInvoiceLoading) return
            this.applyFeesInvoiceLoading = true
            this.applyFeesInvoiceError = ''
            this.applyFeesInvoice = null
            try {
                const payload = {
                    admission_roll: this.applyFeesForm?.admission_roll,
                    mobile: this.applyFeesForm?.mobile,
                    purpose_id: this.applyFeesForm?.purpose_id,
                    academic_session_id: this.applyFeesForm?.academic_session_id,
                    academic_qualification_id: this.applyFeesForm?.academic_qualification_id,
                    department_id: this.applyFeesForm?.department_id,
                    academic_class_id: this.applyFeesForm?.academic_class_id,
                }
                const res = await window.axios.post('/api/public/apply-fees/check-invoice', payload)
                this.applyFeesInvoice = res?.data || null
            } catch (e) {
                this.applyFeesInvoiceError = e?.response?.data?.message || 'Invoice not found.'
                this.applyFeesInvoice = null
            } finally {
                this.applyFeesInvoiceLoading = false
            }
        },
        async payApplyFeesExisting() {
            const inv = this.applyFeesInvoice?.invoice_number
            if (!inv) return
            if (this.applyFeesPayExistingLoading) return
            this.applyFeesPayExistingLoading = true
            try {
                const res = await window.axios.post('/api/public/apply-fees/pay-existing', { invoice_number: inv })
                const url = res?.data?.gateway_url
                if (url) {
                    window.location.href = url
                    return
                }
                this.applyFeesInvoiceError = 'Failed to initiate payment gateway.'
            } catch (e) {
                this.applyFeesInvoiceError = e?.response?.data?.message || 'Failed to initiate payment gateway.'
            } finally {
                this.applyFeesPayExistingLoading = false
            }
        },
        async submitCertificate() {
            this.certificateSubmitting = true
            this.certificateError = ''
            try {
                const payload = { ...this.certificateForm }
                payload.name = payload.name || payload.student_name_en || payload.student_name_bn || ''
                const res = await window.axios.post('/api/public/certificate/init', payload)
                const url = res?.data?.gateway_url
                if (url) {
                    window.location.href = url
                    return
                }
                this.certificateError = 'Failed to initiate payment gateway.'
            } catch (e) {
                if (Number(e?.response?.status) === 422) {
                    const errors = e?.response?.data?.errors
                    if (errors && typeof errors === 'object') {
                        const firstKey = Object.keys(errors)[0]
                        const firstMsg = Array.isArray(errors[firstKey]) ? errors[firstKey][0] : errors[firstKey]
                        this.certificateError = String(firstMsg || e?.response?.data?.message || 'Validation failed.')
                    } else {
                        this.certificateError = String(e?.response?.data?.message || 'Validation failed.')
                    }
                } else {
                    this.certificateError = e?.response?.data?.message || 'Failed to submit.'
                }
            } finally {
                this.certificateSubmitting = false
            }
        },
        async certificateLookup() {
            if (this.certificateLookupLoading) return
            this.certificateLookupLoading = true
            this.certificateLookupError = ''
            try {
                const res = await window.axios.post('/api/public/certificate/lookup', { mobile: this.certificateForm?.mobile })
                const st = res?.data?.student || {}

                if (st?.academic_session_id) this.certificateForm.academic_session_id = String(st.academic_session_id)
                if (st?.academic_qualification_id) this.certificateForm.academic_qualification_id = String(st.academic_qualification_id)
                if (st?.department_id) this.certificateForm.department_id = String(st.department_id)
                if (st?.academic_class_id) this.certificateForm.academic_class_id = String(st.academic_class_id)

                const name = String(st?.name || '')
                if (name) {
                    this.certificateForm.student_name_en = this.certificateForm.student_name_en || name
                }
                const fn = String(st?.fathers_name || '')
                if (fn) {
                    this.certificateForm.fathers_name_en = this.certificateForm.fathers_name_en || fn
                }
                const mn = String(st?.mothers_name || '')
                if (mn) {
                    this.certificateForm.mothers_name_en = this.certificateForm.mothers_name_en || mn
                }

                const reg = String(st?.reg_no || '')
                if (reg) {
                    this.certificateForm.registration_no_en = this.certificateForm.registration_no_en || reg
                }

                const yr = String(st?.passing_year || '')
                if (yr) {
                    this.certificateForm.academic_year_en = this.certificateForm.academic_year_en || yr
                }

                const appId = String(st?.college_roll || st?.reg_no || '')
                if (appId) {
                    this.certificateForm.application_id = this.certificateForm.application_id || appId
                }
            } catch (e) {
                this.certificateLookupError = e?.response?.data?.message || 'Student not found.'
            } finally {
                this.certificateLookupLoading = false
            }
        },
        onPop() {
            this.path = window.location.pathname || '/'
            this.search = window.location.search || ''
            this.routeChanged()
        },
        go(p) {
            const path = String(p || '')
            if (!path.startsWith('/')) {
                window.location.href = path
                return
            }
            let nextPath = path
            let nextSearch = ''
            try {
                const u = new URL(path, window.location.origin)
                nextPath = u.pathname
                nextSearch = u.search || ''
            } catch {
                nextPath = path.split('?')[0] || path
                nextSearch = path.includes('?') ? '?' + (path.split('?')[1] || '') : ''
            }
            const full = nextPath + nextSearch
            if ((window.location.pathname + window.location.search) !== full) {
                window.history.pushState({}, '', full)
            }
            this.path = nextPath
            this.search = nextSearch
            this.routeChanged()
        },
        async routeChanged() {
            this.pageError = ''
            this.pageLoading = false
            this.content = null
            this.noticeDetail = null
            this.noticeList = []

            if (this.path === '/') {
                return
            }

            if (this.path === '/login') {
                this.studentAuthError = ''
                const ok = await this.loadStudentMe()
                if (ok) {
                    this.go('/dashboard')
                }
                return
            }

            if (this.path === '/dashboard') {
                this.studentSidebarActive = 'dashboard'
                const ok = await this.loadStudentMe()
                if (!ok) {
                    this.go('/login')
                    return
                }
                await Promise.all([this.loadStudentDashboard(), this.loadStudentDashboardNotices()])
                return
            }

            if (this.path === '/student/profile') {
                this.studentSidebarActive = 'profile'
                const ok = await this.loadStudentMe()
                if (!ok) {
                    this.go('/login')
                    return
                }
                await this.loadStudentProfileSystems()
                return
            }

            if (this.path === '/student/profile/edit') {
                this.studentSidebarActive = 'profile'
                const ok = await this.loadStudentMe()
                if (!ok) {
                    this.go('/login')
                    return
                }
                await this.loadStudentProfileSystems()
                this.initStudentProfileEditForm()
                return
            }

            if (this.studentInvoiceId) {
                this.studentSidebarActive = 'dashboard'
                const ok = await this.loadStudentMe()
                if (!ok) {
                    this.go('/login')
                    return
                }
                return
            }

            if (this.path === '/student/pay-now') {
                this.studentSidebarActive = 'pay'
                const ok = await this.loadStudentMe()
                if (!ok) {
                    this.go('/login')
                    return
                }
                return
            }

            if (this.path === '/student/payment-history') {
                this.studentSidebarActive = 'history'
                const ok = await this.loadStudentMe()
                if (!ok) {
                    this.go('/login')
                    return
                }
                await this.loadStudentPaymentHistory()
                return
            }

            if (this.path === '/student/fees') {
                this.studentSidebarActive = 'fees'
                const ok = await this.loadStudentMe()
                if (!ok) {
                    this.go('/login')
                    return
                }
                await this.loadStudentFees()
                return
            }

            if (this.path === '/student/admit-card') {
                this.studentSidebarActive = 'admit'
                const ok = await this.loadStudentMe()
                if (!ok) {
                    this.go('/login')
                    return
                }
                await this.loadStudentAdmitCards()
                return
            }

            if (this.path === '/student/result') {
                this.studentSidebarActive = 'result'
                const ok = await this.loadStudentMe()
                if (!ok) {
                    this.go('/login')
                    return
                }
                await this.loadStudentProfileSystems()
                return
            }

            if (this.path === '/student/subjects') {
                this.studentSidebarActive = 'subject'
                const ok = await this.loadStudentMe()
                if (!ok) {
                    this.go('/login')
                    return
                }
                return
            }

            if (this.path === '/student/change-password') {
                this.studentSidebarActive = 'password'
                const ok = await this.loadStudentMe()
                if (!ok) {
                    this.go('/login')
                    return
                }
                return
            }

            if (this.path === '/forgot-password') {
                return
            }

            if (this.path === '/apply-fees') {
                this.syncApplyFeesTabFromQuery()
                if (this.applyFeesTab === 'certificate') {
                    await Promise.all([this.loadApplyFeesSystems(), this.loadCertificateSystems()])
                } else {
                    await this.loadApplyFeesSystems()
                }
                return
            }

            if (this.path === '/online-admission' || this.path === '/online-admission-payment' || this.path === '/online-admission-invoice' || this.path === '/online-admission-download-form') {
                await this.loadOnlineAdmissionSystems()
                await this.loadStudentMe()
                return
            }

            if (this.path === '/registration') {
                await this.loadRegistrationSystems()
                return
            }

            if (this.path === '/notices') {
                this.pageLoading = true
                try {
                    const res = await window.axios.get('/api/public/notices')
                    this.noticeList = Array.isArray(res?.data?.data) ? res.data.data : []
                } catch (e) {
                    this.pageError = e?.response?.data?.message || 'Failed to load notices.'
                } finally {
                    this.pageLoading = false
                }
                return
            }

            if (this.noticeId) {
                this.pageLoading = true
                try {
                    const res = await window.axios.get(`/api/public/notices/${this.noticeId}`)
                    this.noticeDetail = res?.data || null
                } catch (e) {
                    this.pageError = e?.response?.data?.message || 'Failed to load notice.'
                } finally {
                    this.pageLoading = false
                }
                return
            }

            if (this.contentSlug) {
                this.pageLoading = true
                try {
                    const res = await window.axios.get(`/api/public/content/${encodeURIComponent(this.contentSlug)}`)
                    this.content = res?.data || null
                } catch (e) {
                    this.pageError = e?.response?.data?.message || 'Failed to load content.'
                } finally {
                    this.pageLoading = false
                }
                return
            }
        },
        startSliderAuto() {
            if (this.sliderTimer) window.clearInterval(this.sliderTimer)
            this.sliderTimer = null
            if (!Array.isArray(this.slides) || this.slides.length <= 1) return

            this.sliderTimer = window.setInterval(() => {
                if (!Array.isArray(this.slides) || this.slides.length <= 1) return
                this.slideIndex = (this.slideIndex + 1) % this.slides.length
            }, 4000)
        },
        startTicker() {
            if (this.tickerTimer) window.clearInterval(this.tickerTimer)
            this.tickerTimer = null
            this.tickerX = 0
            if (!this.noticeText) return

            this.tickerTimer = window.setInterval(() => {
                this.tickerX -= 1
                if (this.tickerX < -2000) {
                    this.tickerX = 0
                }
            }, 30)
        },
    },
}
</script>
