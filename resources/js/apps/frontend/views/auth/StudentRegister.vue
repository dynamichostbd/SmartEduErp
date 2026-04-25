<template>
<div  class="mt-3 rounded-sm border border-slate-300 bg-white p-4 sm:p-6">
                <div class="mx-auto w-full max-w-6xl">
                    <div class="text-center text-xl font-extrabold text-slate-900 sm:text-2xl">STUDENT REGISTRATION</div>
                    <div class="mx-auto mt-3 h-[2px] w-full max-w-3xl bg-red-500"></div>

                    <div v-if="app.registrationLoading" class="mt-6 text-sm text-slate-600">Loading...</div>

                    <div v-else>
                        <div v-if="app.registrationError" class="mt-6 rounded-sm border border-red-200 bg-red-50 p-3 text-sm text-red-800">
                            {{ app.registrationError }}
                        </div>

                        <div v-if="app.registrationSuccess" class="mt-6 rounded-sm border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-900">
                            {{ app.registrationSuccess }}
                        </div>

                        <form class="mt-8 flex flex-col gap-6" @submit.prevent="submitRegistration">
                            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                                <div class="min-w-0">
                                    <div class="text-sm font-semibold text-slate-900">Academic Information</div>
                                    <div class="mt-1 text-xs text-slate-500">Session, level, department and class selection</div>
                                </div>

                                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                    <div>
                                        <div class="text-xs font-semibold text-slate-600">Select Session <span class="text-red-600">*</span></div>
                                        <select v-model="app.registrationForm.academic_session_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" required>
                                            <option value="">Select Session</option>
                                            <option v-for="s in app.registrationSessionsSorted" :key="'ses-' + s.id" :value="String(s.id)">{{ s.name }}</option>
                                        </select>
                                    </div>

                                    <div>
                                        <div class="text-xs font-semibold text-slate-600">Academic Level <span class="text-red-600">*</span></div>
                                        <select v-model="app.registrationForm.academic_qualification_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" required>
                                            <option value="">Select Level</option>
                                            <option v-for="q in app.registrationQualifications" :key="'q-' + q.id" :value="String(q.id)">{{ q.name }}</option>
                                        </select>
                                    </div>

                                    <div>
                                        <div class="text-xs font-semibold text-slate-600">Department/Group <span v-if="app.registrationDepartmentRequired" class="text-red-600">*</span></div>
                                        <select v-model="app.registrationForm.department_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" :required="app.registrationDepartmentRequired">
                                            <option value="">Select Department</option>
                                            <option v-for="d in app.registrationFilteredDepartments" :key="'d-' + d.id" :value="String(d.id)">{{ d.name }}</option>
                                        </select>
                                    </div>

                                    <div>
                                        <div class="text-xs font-semibold text-slate-600">Class <span class="text-red-600">*</span></div>
                                        <select v-model="app.registrationForm.academic_class_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" required>
                                            <option value="">Select Class</option>
                                            <option v-for="c in app.registrationFilteredClasses" :key="'c-' + c.id" :value="String(c.id)">{{ c.name }}</option>
                                        </select>
                                    </div>

                                    <div>
                                        <div class="text-xs font-semibold text-slate-600">Student Type <span class="text-red-600">*</span></div>
                                        <select v-model="app.registrationForm.student_type" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" required>
                                            <option value="">Select Type</option>
                                            <option v-for="t in app.registrationStudentTypes" :key="'t-' + t" :value="t">{{ t }}</option>
                                        </select>
                                    </div>

                                    <div>
                                        <div class="text-xs font-semibold text-slate-600">Admission Roll (optional)</div>
                                        <input v-model="app.registrationForm.admission_id" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" />
                                    </div>

                                    <div>
                                        <div class="text-xs font-semibold text-slate-600">College Roll</div>
                                        <input v-model="app.registrationForm.college_roll" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" />
                                    </div>

                                    <div>
                                        <div class="text-xs font-semibold text-slate-600">Registration No</div>
                                        <input v-model="app.registrationForm.reg_no" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" />
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                                <div class="min-w-0">
                                    <div class="text-sm font-semibold text-slate-900">Personal Information & Family</div>
                                    <div class="mt-1 text-xs text-slate-500">Identity, personal details and family</div>
                                </div>

                                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                    <div>
                                        <div class="text-xs font-semibold text-slate-600">Full Name <span class="text-red-600">*</span></div>
                                        <input v-model="app.registrationForm.name" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" required />
                                    </div>

                                    <div>
                                        <div class="text-xs font-semibold text-slate-600">Gender</div>
                                        <div class="mt-2 flex flex-wrap items-center gap-4 text-sm text-slate-700">
                                            <label class="inline-flex items-center gap-2">
                                                <input v-model="app.registrationForm.gender" class="accent-[#0d6b75]" type="radio" name="gender" value="Male" />
                                                Male
                                            </label>
                                            <label class="inline-flex items-center gap-2">
                                                <input v-model="app.registrationForm.gender" class="accent-[#0d6b75]" type="radio" name="gender" value="Female" />
                                                Female
                                            </label>
                                            <label class="inline-flex items-center gap-2">
                                                <input v-model="app.registrationForm.gender" class="accent-[#0d6b75]" type="radio" name="gender" value="Others" />
                                                Others
                                            </label>
                                        </div>
                                    </div>

                                    <div>
                                        <div class="text-xs font-semibold text-slate-600">Blood Group</div>
                                        <select v-model="app.registrationForm.blood_group" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10">
                                            <option value="">Select</option>
                                            <option v-for="b in app.registrationBloodGroups" :key="'bg-' + b" :value="b">{{ b }}</option>
                                        </select>
                                    </div>

                                    <div>
                                        <div class="text-xs font-semibold text-slate-600">Father's Name</div>
                                        <input v-model="app.registrationForm.fathers_name" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" />
                                    </div>

                                    <div>
                                        <div class="text-xs font-semibold text-slate-600">Mother's Name</div>
                                        <input v-model="app.registrationForm.mothers_name" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" />
                                    </div>
                                    
                                    <div>
                                        <div class="text-xs font-semibold text-slate-600">Living</div>
                                        <div class="mt-2 flex flex-wrap items-center gap-4 text-sm text-slate-700">
                                            <label class="inline-flex items-center gap-2">
                                                <input v-model="app.registrationForm.living_type" class="accent-[#0d6b75]" type="radio" name="living_type" value="Hostel" />
                                                Hostel
                                            </label>
                                            <label class="inline-flex items-center gap-2">
                                                <input v-model="app.registrationForm.living_type" class="accent-[#0d6b75]" type="radio" name="living_type" value="Others" />
                                                Others
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                                    <div class="min-w-0 mb-4">
                                        <div class="text-sm font-semibold text-slate-900">Present Address</div>
                                        <div class="mt-1 text-xs text-slate-500">Current living location</div>
                                    </div>

                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div>
                                            <div class="text-xs font-semibold text-slate-600">Division</div>
                                            <select v-model="app.registrationForm.division_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10">
                                                <option value="">Select Division</option>
                                                <option v-for="item in app.registrationDivisions" :key="'r-div-' + item.id" :value="String(item.id)">{{ item.name }}</option>
                                            </select>
                                        </div>

                                        <div>
                                            <div class="text-xs font-semibold text-slate-600">District</div>
                                            <select v-model="app.registrationForm.district_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" :disabled="!app.registrationForm.division_id">
                                                <option value="">Select District</option>
                                                <option v-for="item in app.registrationDistrictsList" :key="'r-dis-' + item.id" :value="String(item.id)">{{ item.name }}</option>
                                            </select>
                                        </div>

                                        <div>
                                            <div class="text-xs font-semibold text-slate-600">Upazila</div>
                                            <select v-model="app.registrationForm.upazila_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" :disabled="!app.registrationForm.district_id">
                                                <option value="">Select Upazila</option>
                                                <option v-for="item in app.registrationUpazilasList" :key="'r-upa-' + item.id" :value="String(item.id)">{{ item.name }}</option>
                                            </select>
                                        </div>

                                        <div>
                                            <div class="text-xs font-semibold text-slate-600">Union</div>
                                            <select v-model="app.registrationForm.union_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" :disabled="!app.registrationForm.upazila_id">
                                                <option value="">Select Union</option>
                                                <option v-for="item in app.registrationUnionsList" :key="'r-uni-' + item.id" :value="String(item.id)">{{ item.name }}</option>
                                            </select>
                                        </div>

                                        <div class="sm:col-span-2">
                                            <div class="text-xs font-semibold text-slate-600">Address (Vill/Area, Post & Code) <span class="text-red-600">*</span></div>
                                            <textarea v-model="app.registrationForm.address" rows="2" class="mt-1 w-full rounded-sm border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" required></textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                                    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                                        <div class="min-w-0">
                                            <div class="text-sm font-semibold text-slate-900">Permanent Address</div>
                                            <div class="mt-1 text-xs text-slate-500">Fixed living location</div>
                                        </div>
                                        <label class="inline-flex items-center gap-2 text-xs font-semibold text-slate-600">
                                            <input v-model="app.registrationSameAsPresent" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-[#0d6b75] focus:ring-[#0d6b75]" @change="copyRegistrationAddress" />
                                            Same as present
                                        </label>
                                    </div>

                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div>
                                            <div class="text-xs font-semibold text-slate-600">Division</div>
                                            <select v-model="app.registrationForm.permanent_division_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" :disabled="app.registrationSameAsPresent">
                                                <option value="">Select Division</option>
                                                <option v-for="item in app.registrationDivisions" :key="'rp-div-' + item.id" :value="String(item.id)">{{ item.name }}</option>
                                            </select>
                                        </div>

                                        <div>
                                            <div class="text-xs font-semibold text-slate-600">District</div>
                                            <select v-model="app.registrationForm.permanent_district_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" :disabled="!app.registrationForm.permanent_division_id || app.registrationSameAsPresent">
                                                <option value="">Select District</option>
                                                <option v-for="item in app.registrationPermanentDistrictsList" :key="'rp-dis-' + item.id" :value="String(item.id)">{{ item.name }}</option>
                                            </select>
                                        </div>

                                        <div>
                                            <div class="text-xs font-semibold text-slate-600">Upazila</div>
                                            <select v-model="app.registrationForm.permanent_upazila_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" :disabled="!app.registrationForm.permanent_district_id || app.registrationSameAsPresent">
                                                <option value="">Select Upazila</option>
                                                <option v-for="item in app.registrationPermanentUpazilasList" :key="'rp-upa-' + item.id" :value="String(item.id)">{{ item.name }}</option>
                                            </select>
                                        </div>

                                        <div>
                                            <div class="text-xs font-semibold text-slate-600">Union</div>
                                            <select v-model="app.registrationForm.permanent_union_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" :disabled="!app.registrationForm.permanent_upazila_id || app.registrationSameAsPresent">
                                                <option value="">Select Union</option>
                                                <option v-for="item in app.registrationPermanentUnionsList" :key="'rp-uni-' + item.id" :value="String(item.id)">{{ item.name }}</option>
                                            </select>
                                        </div>

                                        <div class="sm:col-span-2">
                                            <div class="text-xs font-semibold text-slate-600">Address (Vill/Area, Post & Code)</div>
                                            <textarea v-model="app.registrationForm.permanent_address" rows="2" class="mt-1 w-full rounded-sm border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" :readonly="app.registrationSameAsPresent"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                                <div class="min-w-0 mb-4">
                                    <div class="text-sm font-semibold text-slate-900">Account & Media</div>
                                    <div class="mt-1 text-xs text-slate-500">Contact details and picture</div>
                                </div>

                                <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                                    <div class="space-y-4">
                                        <div>
                                            <div class="text-xs font-semibold text-slate-600">Mobile <span class="text-red-600">*</span></div>
                                            <input v-model="app.registrationForm.mobile" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-[#eaf2ff] px-3 text-sm shadow-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" required />
                                        </div>

                                        <div>
                                            <div class="text-xs font-semibold text-slate-600">Password <span class="text-red-600">*</span></div>
                                            <div class="mt-1 flex items-center gap-2">
                                                <input v-model="app.registrationForm.password" type="password" class="h-9 w-full rounded-sm border border-slate-300 bg-[#eaf2ff] px-3 text-sm shadow-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" required />
                                                <button type="button" class="shrink-0 rounded-sm bg-slate-100 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-200" @click="regenPassword">Auto</button>
                                            </div>
                                        </div>

                                        <div>
                                            <div class="text-xs font-semibold text-slate-600">Email (Optional)</div>
                                            <input v-model="app.registrationForm.email" type="email" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10" />
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <div class="text-xs font-semibold text-slate-600">Profile Picture <span class="text-red-600">*</span></div>
                                        <div class="mt-1 text-[11px] text-slate-500">Passport size photo</div>
                                        <input ref="app.registrationProfileInput" type="file" class="mt-2 block w-full text-sm file:mr-3 file:rounded-sm file:border-0 file:bg-slate-900 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-white hover:file:bg-slate-800" accept="image/*" required @change="onPickRegistrationProfile" />
                                        <div v-if="!app.registrationProfileFile" class="mt-1 text-xs font-semibold text-red-600">Picture is required</div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 flex flex-col items-center gap-3">
                                <button type="submit" class="rounded-sm bg-[#0b1d4d] px-10 py-2.5 text-sm font-extrabold text-white hover:bg-[#09163c]" :disabled="app.registrationSaving">
                                    {{ app.registrationSaving ? 'Submitting...' : 'SUBMIT' }}
                                </button>
                                <div class="flex flex-wrap items-center justify-center gap-4">
                                    <button type="button" class="text-sm font-bold text-slate-600 hover:underline" :disabled="app.registrationSaving" @click="resetRegistrationForm">Reset</button>
                                    <button type="button" class="text-sm font-bold text-[#0d6b75] hover:underline" @click="app.go('/login')">Already Registered? Login</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            
</template>

<script setup>
import { inject } from 'vue'
const app = inject('app')
</script>