<template>
<div  class="mt-3 rounded-sm border border-slate-300 bg-white p-4 sm:p-6">
                <div class="mx-auto w-full max-w-6xl">
                    <div v-if="app.onlineAdmissionStatusMessage" class="mb-4 rounded-sm border p-3 text-sm" :class="app.onlineAdmissionStatusClass">
                        {{ app.onlineAdmissionStatusMessage }}
                        <span v-if="app.onlineAdmissionTranId" class="font-semibold"> ({{ app.onlineAdmissionTranId }})</span>
                    </div>

                    <div v-if="app.studentMe" class="rounded-sm border border-amber-200 bg-amber-50 p-4 text-center">
                        <div class="text-sm font-semibold text-amber-800">You are already logged in as <span class="font-extrabold">{{ app.studentMe?.name }}</span>.</div>
                        <div class="mt-3 flex flex-wrap items-center justify-center gap-3">
                            <button
                                type="button"
                                class="rounded-sm bg-[#2f855a] px-5 py-2 text-sm font-extrabold text-white hover:bg-[#276f4a]"
                                @click="app.go('/dashboard')"
                            >Go to Dashboard</button>
                            <button
                                type="button"
                                class="rounded-sm bg-[#d20808] px-5 py-2 text-sm font-extrabold text-white hover:bg-[#b60606]"
                                :disabled="app.studentAuthLoading"
                                @click="app.doStudentLogout()"
                            >Logout</button>
                        </div>
                    </div>

                    <template v-else>
                        <div class="mb-4">
                            <div class="text-xl font-extrabold text-slate-900 sm:text-2xl">ONLINE ADMISSION</div>
                            <div class="mt-2 h-[2px] w-full max-w-4xl bg-red-500"></div>
                        </div>

                        <div class="mt-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                            <div class="mb-4 flex items-center justify-between gap-3 border-b border-slate-100 pb-2">
                                <div class="text-sm font-semibold text-slate-900">CHECK APPLICATION FEES / ADMISSION ROLL / REG NO</div>
                                <button
                                    v-if="app.onlineAdmissionShowForm || app.onlineAdmissionCheckApplicationFees || app.onlineAdmissionCheckRollVerify"
                                    type="button"
                                    class="rounded-full bg-[#d20808] px-4 py-1.5 text-xs font-extrabold text-white shadow-sm hover:bg-[#b60606]"
                                    @click="app.onlineAdmissionResetForm"
                                >
                                    RESET FORM
                                </button>
                            </div>

                            <div v-if="app.onlineAdmissionLoading" class="p-4 text-sm font-semibold text-slate-600">Loading...</div>
                            <div v-else>
                                <div v-if="app.onlineAdmissionError" class="m-4 rounded-sm border border-red-200 bg-red-50 p-3 text-sm font-semibold text-red-800">{{ app.onlineAdmissionError }}</div>

                                <div v-if="!app.onlineAdmissionShowForm" class="py-10 text-center">
                                    <div class="mb-4 text-sm font-semibold text-slate-600">Please select your Academic Level to start the admission process.</div>
                                    <button type="button" @click="app.openOnlineAdmissionModal()" class="rounded-sm bg-[#0d6b75] px-6 py-2.5 text-sm font-extrabold text-white shadow-sm hover:bg-[#0b5b63]">Start Online Admission</button>
                                </div>
                            </div>
                        </div>

                        <template v-if="app.onlineAdmissionShowForm">
                            <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-12">
                                <div class="lg:col-span-6">
                                    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm h-full">
                                        <div class="mb-4 border-b border-slate-100 pb-2 text-sm font-semibold text-slate-900">PERSONAL INFORMATION</div>
                                        <div class="grid grid-cols-1 gap-4">
                                            <div>
                                                <div class="text-xs font-semibold text-slate-600">Full Name <span class="text-red-600">*</span></div>
                                                <input v-model="app.onlineAdmissionForm.name" type="text" :readonly="app.onlineAdmissionDisabledFields.includes('name')" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10 disabled:bg-slate-50" />
                                            </div>
                                            <div>
                                                <div class="text-xs font-semibold text-slate-600">Father's Name <span class="text-red-600">*</span></div>
                                                <input v-model="app.onlineAdmissionForm.fathers_name" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" />
                                            </div>
                                            <div>
                                                <div class="text-xs font-semibold text-slate-600">Mother's Name <span class="text-red-600">*</span></div>
                                                <input v-model="app.onlineAdmissionForm.mothers_name" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" />
                                            </div>

                                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                                <div>
                                                    <div class="text-xs font-semibold text-slate-600">Gender</div>
                                                    <div class="mt-2 flex flex-wrap items-center gap-4">
                                                        <label class="inline-flex items-center gap-2 text-sm font-semibold text-slate-700">
                                                            <input type="radio" value="Male" v-model="app.onlineAdmissionForm.gender" class="accent-[#0d6b75]" />
                                                            Male
                                                        </label>
                                                        <label class="inline-flex items-center gap-2 text-sm font-semibold text-slate-700">
                                                            <input type="radio" value="Female" v-model="app.onlineAdmissionForm.gender" class="accent-[#0d6b75]" />
                                                            Female
                                                        </label>
                                                        <label class="inline-flex items-center gap-2 text-sm font-semibold text-slate-700">
                                                            <input type="radio" value="Others" v-model="app.onlineAdmissionForm.gender" class="accent-[#0d6b75]" />
                                                            Others
                                                        </label>
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="text-xs font-semibold text-slate-600">Date of Birth <span class="text-red-600">*</span></div>
                                                    <input v-model="app.onlineAdmissionForm.dob" type="date" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" />
                                                </div>
                                            </div>

                                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                                <div>
                                                    <div class="text-xs font-semibold text-slate-600">Religion</div>
                                                    <select v-model="app.onlineAdmissionForm.religion" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10">
                                                        <option value="">--Select--</option>
                                                        <option v-for="r in app.onlineAdmissionReligions" :key="'oar-' + r" :value="r">{{ r }}</option>
                                                    </select>
                                                </div>
                                                <div>
                                                    <div class="text-xs font-semibold text-slate-600">Blood Group</div>
                                                    <select v-model="app.onlineAdmissionForm.blood_group" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10">
                                                        <option value="">--Select--</option>
                                                        <option v-for="b in app.onlineAdmissionBloodGroups" :key="'oab-' + b" :value="b">{{ b }}</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div>
                                                <div class="text-xs font-semibold text-slate-600">NID / Birth Reg No</div>
                                                <input v-model="app.onlineAdmissionForm.nid" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" />
                                            </div>
                                            <div>
                                                <div class="text-xs font-semibold text-slate-600">Profile Picture</div>
                                                <input ref="app.onlineAdmissionProfileInput" type="file" accept="image/*" class="mt-1 block w-full text-sm text-slate-700 file:mr-3 file:rounded-sm file:border-0 file:bg-[#0d6b75] file:px-3 file:py-2 file:font-semibold file:text-white hover:file:bg-[#0d6b75]/90" @change="app.onPickOnlineAdmissionProfile" />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="lg:col-span-6">
                                    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm h-full">
                                        <div class="mb-4 border-b border-slate-100 pb-2 text-sm font-semibold text-slate-900">ACADEMIC INFORMATION</div>
                                        <div class="grid grid-cols-1 gap-4">
                                            <div>
                                                <div class="text-xs font-semibold text-slate-600">Admission Roll / Reg No</div>
                                                <input v-model="app.onlineAdmissionForm.admission_roll" type="text" :readonly="app.onlineAdmissionDisabledFields.includes('admission_roll')" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10 disabled:bg-slate-50" />
                                            </div>
                                            <div>
                                                <div class="text-xs font-semibold text-slate-600">SSC GPA</div>
                                                <input v-model="app.onlineAdmissionForm.ssc_gpa" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" />
                                            </div>
                                            <div>
                                                <div class="text-xs font-semibold text-slate-600">Registration No</div>
                                                <input v-model="app.onlineAdmissionForm.registration_no" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" />
                                            </div>
                                            <div>
                                                <div class="text-xs font-semibold text-slate-600">Academic Session</div>
                                                <select v-model="app.onlineAdmissionForm.academic_session_id" :disabled="app.onlineAdmissionDisabledFields.includes('academic_session_id')" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10 disabled:bg-slate-50">
                                                    <option value="">--Select--</option>
                                                    <option v-for="s in app.onlineAdmissionSessions" :key="'oas2-' + s.id" :value="String(s.id)">{{ s.name }}</option>
                                                </select>
                                            </div>
                                            <div>
                                                <div class="mb-1 text-xs font-semibold text-slate-600">Qualification</div>
                                                <div class="rounded-sm border border-[#0d6b75]/30 bg-[#0d6b75]/5 px-3 py-1.5 text-sm font-semibold text-[#0d6b75]">
                                                    {{ app.onlineAdmissionSelectedQualificationName || '—' }}
                                                </div>
                                            </div>
                                            <div>
                                                <div class="text-xs font-semibold text-slate-600">Department/Group</div>
                                                <select v-model="app.onlineAdmissionForm.department_id" :disabled="app.onlineAdmissionDisabledFields.includes('department_id')" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10 disabled:bg-slate-50">
                                                    <option value="">--Select--</option>
                                                    <option v-for="d in app.onlineAdmissionFilteredDepartments" :key="'oad2-' + d.id" :value="String(d.id)">{{ d.name }}</option>
                                                </select>
                                            </div>
                                            <div>
                                                <div class="text-xs font-semibold text-slate-600">Class</div>
                                                <select v-model="app.onlineAdmissionForm.academic_class_id" :disabled="app.onlineAdmissionDisabledFields.includes('academic_class_id')" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10 disabled:bg-slate-50">
                                                    <option value="">--Select--</option>
                                                    <option v-for="c in app.onlineAdmissionFilteredClasses" :key="'oac2-' + c.id" :value="String(c.id)">{{ c.name }}</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-12">
                                <div class="lg:col-span-6">
                                    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm h-full">
                                        <div class="mb-4 border-b border-slate-100 pb-2 text-sm font-semibold text-slate-900">CONTACT INFORMATION</div>
                                        <div class="grid grid-cols-1 gap-4">
                                            <div>
                                                <div class="text-xs font-semibold text-slate-600">Mobile <span class="text-red-600">*</span></div>
                                                <input v-model="app.onlineAdmissionForm.mobile" type="text" :readonly="app.onlineAdmissionDisabledFields.includes('mobile')" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10 disabled:bg-slate-50" />
                                            </div>
                                            <div>
                                                <div class="text-xs font-semibold text-slate-600">Email</div>
                                                <input v-model="app.onlineAdmissionForm.email" type="email" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" />
                                            </div>
                                            <!-- Present Address -->
                                            <div class="rounded-sm border border-slate-200">
                                                <div class="bg-slate-50 px-3 py-2 text-xs font-bold uppercase tracking-wider text-slate-600">
                                                    Present Address
                                                </div>
                                                <div class="grid grid-cols-1 gap-3 p-3">
                                                    <div>
                                                        <div class="text-xs font-semibold text-slate-600">Division</div>
                                                        <select v-model="app.onlineAdmissionForm.division_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10">
                                                            <option value="">-- Division --</option>
                                                            <option v-for="item in app.onlineAdmissionDivisionsAll" :key="'div-' + item.id" :value="String(item.id)">{{ item.name }}</option>
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <div class="text-xs font-semibold text-slate-600">District</div>
                                                        <select v-model="app.onlineAdmissionForm.district_id" :disabled="!app.onlineAdmissionForm.division_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10 disabled:bg-slate-50 disabled:text-slate-400">
                                                            <option value="">-- District --</option>
                                                            <option v-for="item in app.onlineAdmissionDistrictsList" :key="'dis-' + item.id" :value="String(item.id)">{{ item.name }}</option>
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <div class="text-xs font-semibold text-slate-600">Upazila</div>
                                                        <select v-model="app.onlineAdmissionForm.upazila_id" :disabled="!app.onlineAdmissionForm.district_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10 disabled:bg-slate-50 disabled:text-slate-400">
                                                            <option value="">-- Upazila --</option>
                                                            <option v-for="item in app.onlineAdmissionUpazilasList" :key="'upa-' + item.id" :value="String(item.id)">{{ item.name }}</option>
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <div class="text-xs font-semibold text-slate-600">Union</div>
                                                        <select v-model="app.onlineAdmissionForm.union_id" :disabled="!app.onlineAdmissionForm.upazila_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10 disabled:bg-slate-50 disabled:text-slate-400">
                                                            <option value="">-- Union --</option>
                                                            <option v-for="item in app.onlineAdmissionUnionsList" :key="'uni-' + item.id" :value="String(item.id)">{{ item.name }}</option>
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <div class="text-xs font-semibold text-slate-600">Village/Area, Post Office & Postal Code</div>
                                                        <textarea v-model="app.onlineAdmissionForm.address" rows="2" placeholder="Enter Village/Area, Post Office & Postal Code" class="mt-1 w-full rounded-sm border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10"></textarea>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Permanent Address -->
                                            <div class="rounded-sm border border-slate-200">
                                                <div class="flex items-center justify-between bg-slate-50 px-3 py-2">
                                                    <div class="text-xs font-bold uppercase tracking-wider text-slate-600">Permanent Address</div>
                                                    <label class="inline-flex cursor-pointer items-center gap-1.5 text-xs font-semibold text-slate-600">
                                                        <input type="checkbox" v-model="app.onlineAdmissionSameAsPresent" @change="app.copyOnlineAdmissionAddress" class="h-3.5 w-3.5 accent-[#0d6b75]" />
                                                        Same as present
                                                    </label>
                                                </div>
                                                <div class="grid grid-cols-1 gap-3 p-3">
                                                    <div>
                                                        <div class="text-xs font-semibold text-slate-600">Division</div>
                                                        <select v-model="app.onlineAdmissionForm.permanent_division_id" :disabled="app.onlineAdmissionSameAsPresent" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10 disabled:bg-slate-50 disabled:text-slate-400">
                                                            <option value="">-- Division --</option>
                                                            <option v-for="item in app.onlineAdmissionDivisionsAll" :key="'pdiv-' + item.id" :value="String(item.id)">{{ item.name }}</option>
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <div class="text-xs font-semibold text-slate-600">District</div>
                                                        <select v-model="app.onlineAdmissionForm.permanent_district_id" :disabled="!app.onlineAdmissionForm.permanent_division_id || app.onlineAdmissionSameAsPresent" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10 disabled:bg-slate-50 disabled:text-slate-400">
                                                            <option value="">-- District --</option>
                                                            <option v-for="item in app.onlineAdmissionPermanentDistrictsList" :key="'pdis-' + item.id" :value="String(item.id)">{{ item.name }}</option>
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <div class="text-xs font-semibold text-slate-600">Upazila</div>
                                                        <select v-model="app.onlineAdmissionForm.permanent_upazila_id" :disabled="!app.onlineAdmissionForm.permanent_district_id || app.onlineAdmissionSameAsPresent" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10 disabled:bg-slate-50 disabled:text-slate-400">
                                                            <option value="">-- Upazila --</option>
                                                            <option v-for="item in app.onlineAdmissionPermanentUpazilasList" :key="'pupa-' + item.id" :value="String(item.id)">{{ item.name }}</option>
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <div class="text-xs font-semibold text-slate-600">Union</div>
                                                        <select v-model="app.onlineAdmissionForm.permanent_union_id" :disabled="!app.onlineAdmissionForm.permanent_upazila_id || app.onlineAdmissionSameAsPresent" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10 disabled:bg-slate-50 disabled:text-slate-400">
                                                            <option value="">-- Union --</option>
                                                            <option v-for="item in app.onlineAdmissionPermanentUnionsList" :key="'puni-' + item.id" :value="String(item.id)">{{ item.name }}</option>
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <div class="text-xs font-semibold text-slate-600">Village/Area, Post Office & Postal Code</div>
                                                        <textarea v-model="app.onlineAdmissionForm.permanent_address" rows="2" :readonly="app.onlineAdmissionSameAsPresent" placeholder="Enter Village/Area, Post Office & Postal Code" class="mt-1 w-full rounded-sm border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10 read-only:bg-slate-50"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="lg:col-span-6">
                                    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                                        <div class="mb-4 border-b border-slate-100 pb-2 text-sm font-semibold text-slate-900">GUARDIAN INFORMATION</div>
                                        <div class="grid grid-cols-1 gap-4">
                                            <div>
                                                <div class="text-xs font-semibold text-slate-600">Guardian Type</div>
                                                <div class="mt-2 flex flex-wrap items-center gap-4 text-sm text-slate-700">
                                                    <label class="inline-flex items-center gap-2"><input type="radio" value="Father" v-model="app.onlineAdmissionForm.guardian_type" @change="onOnlineAdmissionGuardianType" class="accent-[#0d6b75]" /> Father</label>
                                                    <label class="inline-flex items-center gap-2"><input type="radio" value="Mother" v-model="app.onlineAdmissionForm.guardian_type" @change="onOnlineAdmissionGuardianType" class="accent-[#0d6b75]" /> Mother</label>
                                                    <label class="inline-flex items-center gap-2"><input type="radio" value="Other" v-model="app.onlineAdmissionForm.guardian_type" @change="onOnlineAdmissionGuardianType" class="accent-[#0d6b75]" /> Other</label>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="text-xs font-semibold text-slate-600">Guardian Name</div>
                                                <input v-model="app.onlineAdmissionForm.guardian_name" :readonly="app.onlineAdmissionRelationReadonly" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10 disabled:bg-slate-50" />
                                            </div>
                                            <div class="grid grid-cols-2 gap-4">
                                                <div>
                                                    <div class="text-xs font-semibold text-slate-600">Guardian Mobile</div>
                                                    <input v-model="app.onlineAdmissionForm.guardian_mobile" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" />
                                                </div>
                                                <div>
                                                    <div class="text-xs font-semibold text-slate-600">Guardian Relations</div>
                                                    <input v-model="app.onlineAdmissionForm.guardian_relations" :readonly="app.onlineAdmissionRelationReadonly" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10 disabled:bg-slate-50" />
                                                </div>
                                            </div>

                                            <div class="border-t border-slate-100 pt-4 text-sm font-semibold text-slate-900">OTHER'S INFORMATION</div>

                                            <div class="grid grid-cols-2 gap-4">
                                                <div>
                                                    <div class="text-xs font-semibold text-slate-600">Passing Year</div>
                                                    <input v-model="app.onlineAdmissionForm.passing_year" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" />
                                                </div>
                                                <div>
                                                    <div class="text-xs font-semibold text-slate-600">Nationality</div>
                                                    <input v-model="app.onlineAdmissionForm.nationality" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" />
                                                </div>
                                            </div>
                                            <div>
                                                <div class="text-xs font-semibold text-slate-600">Extra Curricular Activity</div>
                                                <select v-model="app.onlineAdmissionForm.extra_curricular_activity" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10">
                                                    <option value="">--Select--</option>
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
                                            <div class="grid grid-cols-2 gap-4">
                                                <div>
                                                    <div class="text-xs font-semibold text-slate-600">Quota</div>
                                                    <div class="mt-2 flex items-center gap-4 text-sm text-slate-700">
                                                        <label class="inline-flex items-center gap-2"><input type="radio" value="Yes" v-model="app.onlineAdmissionForm.quota" class="accent-[#0d6b75]" /> Yes</label>
                                                        <label class="inline-flex items-center gap-2"><input type="radio" value="No" v-model="app.onlineAdmissionForm.quota" class="accent-[#0d6b75]" /> No</label>
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="text-xs font-semibold text-slate-600">Marital Status</div>
                                                    <div class="mt-2 flex items-center gap-4 text-sm text-slate-700">
                                                        <label class="inline-flex items-center gap-2"><input type="radio" value="Married" v-model="app.onlineAdmissionForm.marital_status" class="accent-[#0d6b75]" /> Married</label>
                                                        <label class="inline-flex items-center gap-2"><input type="radio" value="Unmarried" v-model="app.onlineAdmissionForm.marital_status" class="accent-[#0d6b75]" /> Unmarried</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-if="app.onlineAdmissionUploadDocuments.length" class="mt-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                                <div class="mb-4 border-b border-slate-100 pb-2 text-sm font-semibold text-slate-900">UPLOAD DOCUMENTS</div>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full text-sm">
                                        <tbody>
                                            <tr v-for="(doc, idx) in app.onlineAdmissionUploadDocuments" :key="'oadoc-' + idx" class="border-b border-slate-100 last:border-0 hover:bg-slate-50 transition-colors">
                                                <td class="py-3 pr-3 align-middle">
                                                    <div class="font-semibold text-slate-800">{{ doc.name_bn }}</div>
                                                    <div class="mt-0.5 text-xs text-red-600">File type: jpg, jpeg, png</div>
                                                    <div v-if="doc.optional" class="mt-0.5 text-xs font-extrabold text-slate-500">(Optional)</div>
                                                </td>
                                                <td class="py-3 align-middle w-[300px]">
                                                    <input type="file" accept="image/*" class="block w-full text-sm text-slate-700 file:mr-3 file:rounded-sm file:border-0 file:bg-[#0d6b75] file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-[#0d6b75]/90" @change="(e) => app.onlineAdmissionDocumentUpload(e, idx)" />
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="mt-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                                <label class="inline-flex cursor-pointer items-center gap-2 text-sm font-semibold text-slate-700">
                                    <input type="checkbox" v-model="app.onlineAdmissionAgree" class="h-4 w-4 rounded border-slate-300 accent-[#0d6b75]" />
                                    I agree with terms & conditions
                                </label>
                            </div>

                            <div class="mt-6 text-center">
                                <div v-if="app.onlineAdmissionSubmitting" class="text-sm font-semibold text-slate-600">processing..</div>
                                <button v-else type="button" class="rounded-sm bg-[#0b1d4d] px-10 py-2.5 text-sm font-extrabold text-white shadow-sm hover:bg-[#09163c]" @click="app.onlineAdmissionSubmit">SUBMIT APPLICATION</button>
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