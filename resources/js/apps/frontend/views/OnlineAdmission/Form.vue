<template>
    <div class="mt-3 rounded-sm border border-slate-300 bg-emerald-50 p-4 sm:p-6">
        <div class="mx-auto w-full max-w-6xl">
            <div v-if="app.onlineAdmissionStatusMessage" class="mb-4 rounded-sm border p-3 text-sm"
                :class="app.onlineAdmissionStatusClass">
                {{ app.onlineAdmissionStatusMessage }}
                <span v-if="app.onlineAdmissionTranId" class="font-semibold"> ({{ app.onlineAdmissionTranId }})</span>
            </div>

            <div v-if="app.studentMe" class="rounded-sm border border-amber-200 bg-amber-50 p-4 text-center">
                <div class="text-sm font-semibold text-amber-800">You are already logged in as <span
                        class="font-bold">{{ app.studentMe?.name }}</span>.</div>
                <div class="mt-3 flex flex-wrap items-center justify-center gap-3">
                    <button type="button"
                        class="rounded-sm bg-[#2f855a] px-5 py-2 text-sm font-bold text-white hover:bg-[#276f4a]"
                        @click="app.go('/dashboard')">Go to Dashboard</button>
                    <button type="button"
                        class="rounded-sm bg-[#d20808] px-5 py-2 text-sm font-bold text-white hover:bg-[#b60606]"
                        :disabled="app.studentAuthLoading" @click="app.doStudentLogout()">Logout</button>
                </div>
            </div>

            <template v-else>
                <div class="mb-4 flex items-center justify-between gap-3 pb-2">
                    <div class="text-xl font-bold text-slate-900 sm:text-2xl">ONLINE ADMISSION</div>
                    <button
                        v-if="app.onlineAdmissionShowForm || app.onlineAdmissionCheckApplicationFees || app.onlineAdmissionCheckRollVerify"
                        type="button"
                        class="rounded-full bg-[#d20808] px-4 py-1.5 text-xs font-bold text-white  hover:bg-[#b60606]"
                        @click="app.onlineAdmissionResetForm">
                        RESET FORM
                    </button>
                </div>

                <div v-show="app.onlineAdmissionError || !app.onlineAdmissionLoading || !app.onlineAdmissionShowForm" class="mt-4 rounded-xl bg-white p-5 ">
                    <div v-if="app.onlineAdmissionLoading" class="p-4 text-sm font-semibold text-slate-600">Loading...
                    </div>
                    <div v-else>
                        <div v-if="app.onlineAdmissionError"
                            class="m-4 rounded-sm border border-red-200 bg-red-50 p-3 text-sm font-semibold text-red-800">
                            {{ app.onlineAdmissionError }}</div>

                        <div v-if="!app.onlineAdmissionShowForm" class="py-10 text-center">
                            <div class="mb-4 text-sm font-semibold text-slate-600">Please select your Academic Level to
                                start the admission process.</div>
                            <button type="button" @click="app.openOnlineAdmissionModal()"
                                class="rounded-sm bg-[#0d6b75] px-6 py-2.5 text-sm font-bold text-white  hover:bg-[#0b5b63]">Start
                                Online Admission</button>
                        </div>
                    </div>
                </div>

                <template v-if="app.onlineAdmissionShowForm">
                    <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-12">
                        <div class="lg:col-span-6">
                            <div class="rounded-xl border border-emerald-300 bg-white p-5 h-full">
                                <div class="mb-4 border-b border-slate-100 pb-2 text-sm font-semibold text-slate-900">
                                    PERSONAL INFORMATION</div>
                                <div class="grid grid-cols-1 gap-4">
                                    <div>
                                        <div class="text-xs font-semibold text-slate-600">Full Name <span
                                                class="text-red-600">*</span></div>
                                        <div class="relative mt-1">
                                            <input v-model="app.onlineAdmissionForm.name" type="text" id="field-name"
                                                placeholder="Enter full name"
                                                :readonly="app.onlineAdmissionDisabledFields.includes('name')"
                                                class="h-9 w-full rounded-sm border bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10 disabled:bg-slate-50 transition-all"
                                                :class="app.onlineAdmissionValidationErrors.name ? 'border-red-500 pr-10' : 'border-slate-300'" />
                                            <div v-if="app.onlineAdmissionValidationErrors.name"
                                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                                <svg class="h-4 w-4 text-red-500" fill="currentColor"
                                                    viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        </div>
                                        <div v-if="app.onlineAdmissionValidationErrors.name"
                                            class="mt-1 text-xs text-red-600">{{
                                                app.onlineAdmissionValidationErrors.name }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs font-semibold text-slate-600">Father's Name <span
                                                class="text-red-600">*</span></div>
                                        <div class="relative mt-1">
                                            <input v-model="app.onlineAdmissionForm.fathers_name" type="text"
                                                id="field-fathers_name" placeholder="Enter father's name"
                                                class="h-9 w-full rounded-sm border bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10 transition-all"
                                                :class="app.onlineAdmissionValidationErrors.fathers_name ? 'border-red-500 pr-10' : 'border-slate-300'" />
                                            <div v-if="app.onlineAdmissionValidationErrors.fathers_name"
                                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                                <svg class="h-4 w-4 text-red-500" fill="currentColor"
                                                    viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        </div>
                                        <div v-if="app.onlineAdmissionValidationErrors.fathers_name"
                                            class="mt-1 text-xs text-red-600">{{
                                                app.onlineAdmissionValidationErrors.fathers_name }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs font-semibold text-slate-600">Mother's Name <span
                                                class="text-red-600">*</span></div>
                                        <div class="relative mt-1">
                                            <input v-model="app.onlineAdmissionForm.mothers_name" type="text"
                                                id="field-mothers_name" placeholder="Enter mother's name"
                                                class="h-9 w-full rounded-sm border bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10 transition-all"
                                                :class="app.onlineAdmissionValidationErrors.mothers_name ? 'border-red-500 pr-10' : 'border-slate-300'" />
                                            <div v-if="app.onlineAdmissionValidationErrors.mothers_name"
                                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                                <svg class="h-4 w-4 text-red-500" fill="currentColor"
                                                    viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        </div>
                                        <div v-if="app.onlineAdmissionValidationErrors.mothers_name"
                                            class="mt-1 text-xs text-red-600">{{
                                                app.onlineAdmissionValidationErrors.mothers_name }}</div>
                                    </div>

                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div>
                                            <div class="text-xs font-semibold text-slate-600">Gender</div>
                                            <div class="mt-2 flex flex-wrap items-center gap-4">
                                                <label
                                                    class="inline-flex items-center gap-2 text-sm font-semibold text-slate-700">
                                                    <input type="radio" value="Male"
                                                        v-model="app.onlineAdmissionForm.gender"
                                                        class="accent-[#0d6b75]" />
                                                    Male
                                                </label>
                                                <label
                                                    class="inline-flex items-center gap-2 text-sm font-semibold text-slate-700">
                                                    <input type="radio" value="Female"
                                                        v-model="app.onlineAdmissionForm.gender"
                                                        class="accent-[#0d6b75]" />
                                                    Female
                                                </label>
                                                <label
                                                    class="inline-flex items-center gap-2 text-sm font-semibold text-slate-700">
                                                    <input type="radio" value="Others"
                                                        v-model="app.onlineAdmissionForm.gender"
                                                        class="accent-[#0d6b75]" />
                                                    Others
                                                </label>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="text-xs font-semibold text-slate-600">Date of Birth <span
                                                    class="text-red-600">*</span></div>
                                            <div class="relative mt-1">
                                                <input v-model="app.onlineAdmissionForm.dob" type="date" id="field-dob"
                                                    class="h-9 w-full rounded-sm border bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10 transition-all"
                                                    :class="app.onlineAdmissionValidationErrors.dob ? 'border-red-500 pr-10' : 'border-slate-300'" />
                                                <div v-if="app.onlineAdmissionValidationErrors.dob"
                                                    class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                                    <svg class="h-4 w-4 text-red-500" fill="currentColor"
                                                        viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd"
                                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                </div>
                                            </div>
                                            <div v-if="app.onlineAdmissionValidationErrors.dob"
                                                class="mt-1 text-xs text-red-600">{{
                                                app.onlineAdmissionValidationErrors.dob }}</div>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div>
                                            <div class="text-xs font-semibold text-slate-600">Religion <span
                                                    class="text-red-600">*</span></div>
                                            <div class="mt-1">
                                                <select v-model="app.onlineAdmissionForm.religion" id="field-religion"
                                                    class="h-9 w-full rounded-sm border bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10 transition-all"
                                                    :class="app.onlineAdmissionValidationErrors.religion ? 'border-red-500' : 'border-slate-300'">
                                                    <option value="">--Select--</option>
                                                    <option v-for="r in app.onlineAdmissionReligions" :key="'oar-' + r"
                                                        :value="r">{{ r }}</option>
                                                </select>
                                            </div>
                                            <div v-if="app.onlineAdmissionValidationErrors.religion"
                                                class="mt-1 text-xs text-red-600">{{
                                                    app.onlineAdmissionValidationErrors.religion }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs font-semibold text-slate-600">Blood Group</div>
                                            <select v-model="app.onlineAdmissionForm.blood_group"
                                                class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10">
                                                <option value="">--Select--</option>
                                                <option v-for="b in app.onlineAdmissionBloodGroups" :key="'oab-' + b"
                                                    :value="b">{{ b }}</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div>
                                        <div class="text-xs font-semibold text-slate-600">NID / Birth Reg No</div>
                                        <input v-model="app.onlineAdmissionForm.nid" type="text"
                                            placeholder="Enter NID or Birth Reg No"
                                            class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" />
                                    </div>
                                    <div>
                                        <div class="text-xs font-semibold text-slate-600">Profile Picture <span
                                                class="text-red-600">*</span></div>
                                        <input ref="app.onlineAdmissionProfileInput" type="file" accept="image/*"
                                            id="field-profile"
                                            class="mt-1 block w-full text-sm text-slate-700 file:mr-3 file:rounded-sm file:border-0 file:bg-[#0d6b75] file:px-3 file:py-2 file:font-semibold file:text-white hover:file:bg-[#0d6b75]/90 border"
                                            :class="app.onlineAdmissionValidationErrors.profile ? 'border-red-500 rounded-sm' : 'border-transparent'"
                                            @change="app.onPickOnlineAdmissionProfile" />
                                        <div v-if="app.onlineAdmissionValidationErrors.profile"
                                            class="mt-1 text-xs text-red-600">{{
                                                app.onlineAdmissionValidationErrors.profile }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="lg:col-span-6">
                            <div class="rounded-xl border border-emerald-300 bg-white p-5 h-full">
                                <div class="mb-4 border-b border-slate-100 pb-2 text-sm font-semibold text-slate-900">
                                    ACADEMIC INFORMATION</div>
                                <div class="grid grid-cols-1 gap-4">
                                    <div>
                                        <div class="text-xs font-semibold text-slate-600">Admission Roll / Reg No <span
                                                class="text-red-600">*</span></div>
                                        <div class="relative mt-1">
                                            <input v-model="app.onlineAdmissionForm.admission_roll" type="text"
                                                id="field-admission_roll" placeholder="Enter admission roll or reg no"
                                                :readonly="app.onlineAdmissionDisabledFields.includes('admission_roll')"
                                                class="h-9 w-full rounded-sm border bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10 disabled:bg-slate-50 transition-all"
                                                :class="app.onlineAdmissionValidationErrors.admission_roll ? 'border-red-500 pr-10' : 'border-slate-300'" />
                                            <div v-if="app.onlineAdmissionValidationErrors.admission_roll"
                                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                                <svg class="h-4 w-4 text-red-500" fill="currentColor"
                                                    viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        </div>
                                        <div v-if="app.onlineAdmissionValidationErrors.admission_roll"
                                            class="mt-1 text-xs text-red-600">{{
                                                app.onlineAdmissionValidationErrors.admission_roll }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs font-semibold text-slate-600">SSC GPA</div>
                                        <input v-model="app.onlineAdmissionForm.ssc_gpa" type="text"
                                            placeholder="Enter SSC GPA"
                                            class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" />
                                    </div>
                                    <div>
                                        <div class="text-xs font-semibold text-slate-600">Registration No</div>
                                        <input v-model="app.onlineAdmissionForm.registration_no" type="text"
                                            placeholder="Enter registration no"
                                            class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" />
                                    </div>
                                    <div>
                                        <div class="text-xs font-semibold text-slate-600">Academic Session <span
                                                class="text-red-600">*</span></div>
                                        <div class="mt-1">
                                            <select v-model="app.onlineAdmissionForm.academic_session_id"
                                                id="field-academic_session_id"
                                                :disabled="app.onlineAdmissionDisabledFields.includes('academic_session_id')"
                                                class="h-9 w-full rounded-sm border bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10 disabled:bg-slate-50 transition-all"
                                                :class="app.onlineAdmissionValidationErrors.academic_session_id ? 'border-red-500' : 'border-slate-300'">
                                                <option value="">--Select--</option>
                                                <option v-for="s in app.onlineAdmissionSessions" :key="'oas2-' + s.id"
                                                    :value="String(s.id)">{{ s.name }}</option>
                                            </select>
                                        </div>
                                        <div v-if="app.onlineAdmissionValidationErrors.academic_session_id"
                                            class="mt-1 text-xs text-red-600">{{
                                                app.onlineAdmissionValidationErrors.academic_session_id }}</div>
                                    </div>
                                    <div>
                                        <div class="mb-1 text-xs font-semibold text-slate-600">Qualification</div>
                                        <div
                                            class="rounded-sm border border-[#0d6b75]/30 bg-[#0d6b75]/5 px-3 py-1.5 text-sm font-semibold text-[#0d6b75]">
                                            {{ app.onlineAdmissionSelectedQualificationName || '—' }}
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-xs font-semibold text-slate-600">Department/Group <span
                                                class="text-red-600">*</span></div>
                                        <div class="mt-1">
                                            <select v-model="app.onlineAdmissionForm.department_id"
                                                id="field-department_id"
                                                :disabled="app.onlineAdmissionDisabledFields.includes('department_id')"
                                                class="h-9 w-full rounded-sm border bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10 disabled:bg-slate-50 transition-all"
                                                :class="app.onlineAdmissionValidationErrors.department_id ? 'border-red-500' : 'border-slate-300'">
                                                <option value="">--Select--</option>
                                                <option v-for="d in app.onlineAdmissionFilteredDepartments"
                                                    :key="'oad2-' + d.id" :value="String(d.id)">{{ d.name }}</option>
                                            </select>
                                        </div>
                                        <div v-if="app.onlineAdmissionValidationErrors.department_id"
                                            class="mt-1 text-xs text-red-600">{{
                                                app.onlineAdmissionValidationErrors.department_id }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs font-semibold text-slate-600">Class <span
                                                class="text-red-600">*</span></div>
                                        <div class="mt-1">
                                            <select v-model="app.onlineAdmissionForm.academic_class_id"
                                                id="field-academic_class_id"
                                                :disabled="app.onlineAdmissionDisabledFields.includes('academic_class_id')"
                                                class="h-9 w-full rounded-sm border bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10 disabled:bg-slate-50 transition-all"
                                                :class="app.onlineAdmissionValidationErrors.academic_class_id ? 'border-red-500' : 'border-slate-300'">
                                                <option value="">--Select--</option>
                                                <option v-for="c in app.onlineAdmissionFilteredClasses"
                                                    :key="'oac2-' + c.id" :value="String(c.id)">{{ c.name }}</option>
                                            </select>
                                        </div>
                                        <div v-if="app.onlineAdmissionValidationErrors.academic_class_id"
                                            class="mt-1 text-xs text-red-600">{{
                                                app.onlineAdmissionValidationErrors.academic_class_id }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-12">
                        <div class="lg:col-span-12">
                            <div class="rounded-xl border border-emerald-300 bg-white p-5 h-full">
                                <div class="mb-4 border-b border-slate-100 pb-2 text-sm font-semibold text-slate-900">
                                    CONTACT INFORMATION</div>
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                    <div>
                                        <div class="text-xs font-semibold text-slate-600">Mobile <span
                                                class="text-red-600">*</span></div>
                                        <div class="relative mt-1">
                                            <input v-model="app.onlineAdmissionForm.mobile" type="text"
                                                id="field-mobile" placeholder="Enter mobile number"
                                                :readonly="app.onlineAdmissionDisabledFields.includes('mobile')"
                                                class="h-9 w-full rounded-sm border bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10 disabled:bg-slate-50 transition-all"
                                                :class="app.onlineAdmissionValidationErrors.mobile ? 'border-red-500 pr-10' : 'border-slate-300'" />
                                            <div v-if="app.onlineAdmissionValidationErrors.mobile"
                                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                                <svg class="h-4 w-4 text-red-500" fill="currentColor"
                                                    viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        </div>
                                        <div v-if="app.onlineAdmissionValidationErrors.mobile"
                                            class="mt-1 text-xs text-red-600">{{
                                                app.onlineAdmissionValidationErrors.mobile }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs font-semibold text-slate-600">Email</div>
                                        <input v-model="app.onlineAdmissionForm.email" type="email" id="field-email"
                                            placeholder="Enter email address"
                                            class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm  outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" />
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="lg:col-span-6 bg-white">
                            <!-- Present Address -->
                            <div class="rounded-sm border border-emerald-300">
                                <div
                                    class="bg-emerald-50 px-3 py-2 text-xs font-bold uppercase tracking-wider text-emerald-600">
                                    Present Address
                                </div>
                                <div class="grid grid-cols-1 gap-3 p-3">
                                    <div>
                                        <div class="text-xs font-semibold text-slate-600">Division <span
                                                class="text-red-600">*</span></div>
                                        <div class="mt-1">
                                            <select v-model="app.onlineAdmissionForm.division_id" id="field-division_id"
                                                class="h-9 w-full rounded-sm border bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10 transition-all"
                                                :class="app.onlineAdmissionValidationErrors.division_id ? 'border-red-500' : 'border-slate-300'">
                                                <option value="">-- Division --</option>
                                                <option v-for="item in app.onlineAdmissionDivisionsAll"
                                                    :key="'div-' + item.id" :value="String(item.id)">{{ item.name }}
                                                </option>
                                            </select>
                                        </div>
                                        <div v-if="app.onlineAdmissionValidationErrors.division_id"
                                            class="mt-1 text-xs text-red-600">{{
                                                app.onlineAdmissionValidationErrors.division_id }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs font-semibold text-slate-600">District <span
                                                class="text-red-600">*</span></div>
                                        <div class="mt-1">
                                            <select v-model="app.onlineAdmissionForm.district_id" id="field-district_id"
                                                :disabled="!app.onlineAdmissionForm.division_id"
                                                class="h-9 w-full rounded-sm border bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10 disabled:bg-slate-50 disabled:text-slate-400 transition-all"
                                                :class="app.onlineAdmissionValidationErrors.district_id ? 'border-red-500' : 'border-slate-300'">
                                                <option value="">-- District --</option>
                                                <option v-for="item in app.onlineAdmissionDistrictsList"
                                                    :key="'dis-' + item.id" :value="String(item.id)">{{ item.name }}
                                                </option>
                                            </select>
                                        </div>
                                        <div v-if="app.onlineAdmissionValidationErrors.district_id"
                                            class="mt-1 text-xs text-red-600">{{
                                                app.onlineAdmissionValidationErrors.district_id }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs font-semibold text-slate-600">Upazila <span
                                                class="text-red-600">*</span></div>
                                        <div class="mt-1">
                                            <select v-model="app.onlineAdmissionForm.upazila_id" id="field-upazila_id"
                                                :disabled="!app.onlineAdmissionForm.district_id"
                                                class="h-9 w-full rounded-sm border bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10 disabled:bg-slate-50 disabled:text-slate-400 transition-all"
                                                :class="app.onlineAdmissionValidationErrors.upazila_id ? 'border-red-500' : 'border-slate-300'">
                                                <option value="">-- Upazila --</option>
                                                <option v-for="item in app.onlineAdmissionUpazilasList"
                                                    :key="'upa-' + item.id" :value="String(item.id)">{{ item.name }}
                                                </option>
                                            </select>
                                        </div>
                                        <div v-if="app.onlineAdmissionValidationErrors.upazila_id"
                                            class="mt-1 text-xs text-red-600">{{
                                                app.onlineAdmissionValidationErrors.upazila_id }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs font-semibold text-slate-600">Union</div>
                                        <select v-model="app.onlineAdmissionForm.union_id"
                                            class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm  outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10 disabled:bg-slate-50 disabled:text-slate-400">
                                            <option value="">-- Union --</option>
                                            <option v-for="item in app.onlineAdmissionUnionsList"
                                                :key="'uni-' + item.id" :value="String(item.id)">{{ item.name }}
                                            </option>
                                        </select>
                                    </div>
                                    <div>
                                        <div class="text-xs font-semibold text-slate-600">Village/Area, Post
                                            Office & Postal Code <span class="text-red-600">*</span></div>
                                        <div class="relative mt-1">
                                            <textarea v-model="app.onlineAdmissionForm.address" rows="2"
                                                id="field-address"
                                                placeholder="Enter Village/Area, Post Office & Postal Code"
                                                class="w-full rounded-sm border bg-white px-3 py-2 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10 transition-all"
                                                :class="app.onlineAdmissionValidationErrors.address ? 'border-red-500 pr-10' : 'border-slate-300'"></textarea>
                                            <div v-if="app.onlineAdmissionValidationErrors.address"
                                                class="pointer-events-none absolute top-3 right-3">
                                                <svg class="h-4 w-4 text-red-500" fill="currentColor"
                                                    viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        </div>
                                        <div v-if="app.onlineAdmissionValidationErrors.address"
                                            class="mt-1 text-xs text-red-600">{{
                                                app.onlineAdmissionValidationErrors.address }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="lg:col-span-6 bg-white">
                            <!-- Permanent Address -->
                            <div class="rounded-sm border border-emerald-300">
                                <div class="flex items-center justify-between bg-emerald-50 px-3 py-2">
                                    <div class="text-xs font-bold uppercase tracking-wider text-emerald-600">
                                        Permanent Address</div>
                                    <label
                                        class="inline-flex cursor-pointer items-center gap-1.5 text-xs font-semibold text-emerald-600">
                                        <input type="checkbox" v-model="app.onlineAdmissionSameAsPresent"
                                            @change="app.copyOnlineAdmissionAddress"
                                            class="h-3.5 w-3.5 accent-[#0d6b75]" />
                                        Same as present
                                    </label>
                                </div>
                                <div class="grid grid-cols-1 gap-3 p-3">
                                    <div>
                                        <div class="text-xs font-semibold text-slate-600">Division</div>
                                        <select v-model="app.onlineAdmissionForm.permanent_division_id"
                                            id="field-permanent_division_id"
                                            :disabled="app.onlineAdmissionSameAsPresent"
                                            class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm  outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10 disabled:bg-slate-50 disabled:text-slate-400">
                                            <option value="">-- Division --</option>
                                            <option v-for="item in app.onlineAdmissionDivisionsAll"
                                                :key="'pdiv-' + item.id" :value="String(item.id)">{{ item.name
                                                }}</option>
                                        </select>
                                    </div>
                                    <div>
                                        <div class="text-xs font-semibold text-slate-600">District</div>
                                        <select v-model="app.onlineAdmissionForm.permanent_district_id"
                                            :disabled="!app.onlineAdmissionForm.permanent_division_id || app.onlineAdmissionSameAsPresent"
                                            class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm  outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10 disabled:bg-slate-50 disabled:text-slate-400">
                                            <option value="">-- District --</option>
                                            <option v-for="item in app.onlineAdmissionPermanentDistrictsList"
                                                :key="'pdis-' + item.id" :value="String(item.id)">{{ item.name
                                                }}</option>
                                        </select>
                                    </div>
                                    <div>
                                        <div class="text-xs font-semibold text-slate-600">Upazila</div>
                                        <select v-model="app.onlineAdmissionForm.permanent_upazila_id"
                                            :disabled="!app.onlineAdmissionForm.permanent_district_id || app.onlineAdmissionSameAsPresent"
                                            class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm  outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10 disabled:bg-slate-50 disabled:text-slate-400">
                                            <option value="">-- Upazila --</option>
                                            <option v-for="item in app.onlineAdmissionPermanentUpazilasList"
                                                :key="'pupa-' + item.id" :value="String(item.id)">{{ item.name
                                                }}</option>
                                        </select>
                                    </div>
                                    <div>
                                        <div class="text-xs font-semibold text-slate-600">Union</div>
                                        <select v-model="app.onlineAdmissionForm.permanent_union_id"
                                            :disabled="!app.onlineAdmissionForm.permanent_upazila_id || app.onlineAdmissionSameAsPresent"
                                            class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm  outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10 disabled:bg-slate-50 disabled:text-slate-400">
                                            <option value="">-- Union --</option>
                                            <option v-for="item in app.onlineAdmissionPermanentUnionsList"
                                                :key="'puni-' + item.id" :value="String(item.id)">{{ item.name
                                                }}</option>
                                        </select>
                                    </div>
                                    <div>
                                        <div class="text-xs font-semibold text-slate-600">Village/Area, Post
                                            Office & Postal Code</div>
                                        <textarea v-model="app.onlineAdmissionForm.permanent_address" rows="2"
                                            id="field-permanent_address" :readonly="app.onlineAdmissionSameAsPresent"
                                            placeholder="Enter Village/Area, Post Office & Postal Code"
                                            class="mt-1 w-full rounded-sm border bg-white px-3 py-2 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10 read-only:bg-slate-50 border-slate-300"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="lg:col-span-12">
                            <div class="rounded-xl border border-emerald-300 bg-white p-5 ">
                                <div class="mb-4 border-b border-slate-100 pb-2 text-sm font-semibold text-slate-900">
                                    GUARDIAN INFORMATION</div>
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                    <div>
                                        <div class="text-xs font-semibold text-slate-600">Guardian Type <span
                                                class="text-red-600">*</span></div>
                                        <div class="mt-2 flex flex-wrap items-center gap-4 text-sm text-slate-700">
                                            <label class="inline-flex items-center gap-2"><input type="radio"
                                                    value="Father" v-model="app.onlineAdmissionForm.guardian_type"
                                                    @change="onOnlineAdmissionGuardianType" class="accent-[#0d6b75]" />
                                                Father</label>
                                            <label class="inline-flex items-center gap-2"><input type="radio"
                                                    value="Mother" v-model="app.onlineAdmissionForm.guardian_type"
                                                    @change="onOnlineAdmissionGuardianType" class="accent-[#0d6b75]" />
                                                Mother</label>
                                            <label class="inline-flex items-center gap-2"><input type="radio"
                                                    value="Other" v-model="app.onlineAdmissionForm.guardian_type"
                                                    @change="onOnlineAdmissionGuardianType" class="accent-[#0d6b75]" />
                                                Other</label>
                                        </div>
                                        <div v-if="app.onlineAdmissionValidationErrors.guardian_type"
                                            class="mt-1 text-xs text-red-600">{{
                                                app.onlineAdmissionValidationErrors.guardian_type }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs font-semibold text-slate-600">Guardian Name <span
                                                class="text-red-600">*</span></div>
                                        <div class="relative mt-1">
                                            <input v-model="app.onlineAdmissionForm.guardian_name"
                                                id="field-guardian_name" placeholder="Enter guardian name"
                                                :readonly="app.onlineAdmissionRelationReadonly" type="text"
                                                class="h-9 w-full rounded-sm border bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10 disabled:bg-slate-50 transition-all"
                                                :class="app.onlineAdmissionValidationErrors.guardian_name ? 'border-red-500 pr-10' : 'border-slate-300'" />
                                            <div v-if="app.onlineAdmissionValidationErrors.guardian_name"
                                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                                <svg class="h-4 w-4 text-red-500" fill="currentColor"
                                                    viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        </div>
                                        <div v-if="app.onlineAdmissionValidationErrors.guardian_name"
                                            class="mt-1 text-xs text-red-600">{{
                                                app.onlineAdmissionValidationErrors.guardian_name }}</div>
                                    </div>

                                    <div>
                                        <div class="text-xs font-semibold text-slate-600">Guardian Mobile <span
                                                class="text-red-600">*</span></div>
                                        <div class="relative mt-1">
                                            <input v-model="app.onlineAdmissionForm.guardian_mobile" type="text"
                                                id="field-guardian_mobile" placeholder="Enter guardian mobile number"
                                                class="h-9 w-full rounded-sm border bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10 transition-all"
                                                :class="app.onlineAdmissionValidationErrors.guardian_mobile ? 'border-red-500 pr-10' : 'border-slate-300'" />
                                            <div v-if="app.onlineAdmissionValidationErrors.guardian_mobile"
                                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                                <svg class="h-4 w-4 text-red-500" fill="currentColor"
                                                    viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        </div>
                                        <div v-if="app.onlineAdmissionValidationErrors.guardian_mobile"
                                            class="mt-1 text-xs text-red-600">{{
                                                app.onlineAdmissionValidationErrors.guardian_mobile }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs font-semibold text-slate-600">Guardian Relations</div>
                                        <input v-model="app.onlineAdmissionForm.guardian_relations"
                                            id="field-guardian_relations" placeholder="Relation with guardian"
                                            :readonly="app.onlineAdmissionRelationReadonly" type="text"
                                            class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm  outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10 disabled:bg-slate-50" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="lg:col-span-12">
                            <div class="rounded-xl border border-emerald-300 bg-white p-5 ">
                                <div class="mb-4 border-b border-slate-100 pb-2 text-sm font-semibold text-slate-900">
                                    OTHERS INFORMATION</div>
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

                                    <div>
                                        <div class="text-xs font-semibold text-slate-600">Passing Year <span
                                                class="text-red-600">*</span></div>
                                        <div class="relative mt-1">
                                            <input v-model="app.onlineAdmissionForm.passing_year" type="text"
                                                id="field-passing_year" placeholder="Enter passing year"
                                                class="h-9 w-full rounded-sm border bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10 transition-all"
                                                :class="app.onlineAdmissionValidationErrors.passing_year ? 'border-red-500 pr-10' : 'border-slate-300'" />
                                            <div v-if="app.onlineAdmissionValidationErrors.passing_year"
                                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                                <svg class="h-4 w-4 text-red-500" fill="currentColor"
                                                    viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        </div>
                                        <div v-if="app.onlineAdmissionValidationErrors.passing_year"
                                            class="mt-1 text-xs text-red-600">{{
                                                app.onlineAdmissionValidationErrors.passing_year }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs font-semibold text-slate-600">Nationality <span
                                                class="text-red-600">*</span></div>
                                        <div class="relative mt-1">
                                            <input v-model="app.onlineAdmissionForm.nationality" type="text"
                                                id="field-nationality" placeholder="Enter nationality"
                                                class="h-9 w-full rounded-sm border bg-white px-3 text-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10 transition-all"
                                                :class="app.onlineAdmissionValidationErrors.nationality ? 'border-red-500 pr-10' : 'border-slate-300'" />
                                            <div v-if="app.onlineAdmissionValidationErrors.nationality"
                                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                                <svg class="h-4 w-4 text-red-500" fill="currentColor"
                                                    viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        </div>
                                        <div v-if="app.onlineAdmissionValidationErrors.nationality"
                                            class="mt-1 text-xs text-red-600">{{
                                                app.onlineAdmissionValidationErrors.nationality }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs font-semibold text-slate-600">Extra Curricular Activity
                                        </div>
                                        <select v-model="app.onlineAdmissionForm.extra_curricular_activity"
                                            class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm  outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10">
                                            <option value="">--Select--</option>
                                            <option value="Cultural activities">Cultural activities</option>
                                            <option value="Drawing and graffiti">Drawing and graffiti</option>
                                            <option value="Debate and public speaking">Debate and public speaking
                                            </option>
                                            <option value="Sports and physical exercise">Sports and physical exercise
                                            </option>
                                            <option value="Social service activities">Social service activities</option>
                                            <option value="Technological and creative activities">Technological and
                                                creative activities</option>
                                            <option value="Academic clubs">Academic clubs</option>
                                            <option value="Others">Others</option>
                                        </select>
                                    </div>
                                    <div>
                                        <div class="text-xs font-semibold text-slate-600">Quota</div>
                                        <div class="mt-2 flex items-center gap-4 text-sm text-slate-700">
                                            <label class="inline-flex items-center gap-2"><input type="radio"
                                                    value="Yes" v-model="app.onlineAdmissionForm.quota"
                                                    class="accent-[#0d6b75]" />
                                                Yes</label>
                                            <label class="inline-flex items-center gap-2"><input type="radio" value="No"
                                                    v-model="app.onlineAdmissionForm.quota" class="accent-[#0d6b75]" />
                                                No</label>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-xs font-semibold text-slate-600">Marital Status</div>
                                        <div class="mt-2 flex items-center gap-4 text-sm text-slate-700">
                                            <label class="inline-flex items-center gap-2"><input type="radio"
                                                    value="Married" v-model="app.onlineAdmissionForm.marital_status"
                                                    class="accent-[#0d6b75]" /> Married</label>
                                            <label class="inline-flex items-center gap-2"><input type="radio"
                                                    value="Unmarried" v-model="app.onlineAdmissionForm.marital_status"
                                                    class="accent-[#0d6b75]" /> Unmarried</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="app.onlineAdmissionUploadDocuments.length"
                        class="mt-4 rounded-xl border border-slate-200 bg-white p-5 ">
                        <div class="mb-4 border-b border-slate-100 pb-2 text-sm font-semibold text-slate-900">UPLOAD
                            DOCUMENTS</div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <tbody>
                                    <tr v-for="(doc, idx) in app.onlineAdmissionUploadDocuments" :key="'oadoc-' + idx"
                                        class="border-b border-slate-100 last:border-0 hover:bg-slate-50 transition-colors">
                                        <td class="py-3 pr-3 align-middle">
                                            <div class="font-semibold text-slate-800">{{ doc.name_bn }}</div>
                                            <div class="mt-0.5 text-xs text-red-600">File type: jpg, jpeg, png</div>
                                            <div v-if="doc.optional" class="mt-0.5 text-xs font-bold text-slate-500">
                                                (Optional)</div>
                                        </td>
                                        <td class="py-3 align-middle w-[300px]">
                                            <input type="file" accept="image/*"
                                                class="block w-full text-sm text-slate-700 file:mr-3 file:rounded-sm file:border-0 file:bg-[#0d6b75] file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-[#0d6b75]/90"
                                                @change="(e) => app.onlineAdmissionDocumentUpload(e, idx)" />
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-4 rounded-xl border border-emerald-300 bg-white p-5 ">
                        <label
                            class="inline-flex cursor-pointer items-center gap-2 text-sm font-semibold text-slate-700">
                            <input type="checkbox" v-model="app.onlineAdmissionAgree"
                                class="h-4 w-4 rounded border-slate-300 accent-[#0d6b75]" />
                            I agree with terms & conditions
                        </label>
                    </div>

                    <div class="mt-6 text-center">
                        <div v-if="app.onlineAdmissionSubmitting" class="text-sm font-semibold text-slate-600">
                            processing..</div>
                        <button v-else type="button"
                            class="rounded-sm bg-[#0d6b75] px-10 py-2.5 text-sm font-bold text-white  hover:bg-[#09163c]"
                            @click="app.onlineAdmissionSubmit">SUBMIT APPLICATION</button>
                    </div>
                </template>
            </template>
        </div>
    </div>

</template>

<script setup>
import { inject } from 'vue'
const app = inject('app')
</script>