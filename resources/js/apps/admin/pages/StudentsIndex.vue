<template>
    <div class="flex flex-col gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <div class="text-xl font-semibold text-slate-900">Student List</div>
                </div>

                <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
                    <select
                        v-model="filters.field_name"
                        class="h-9 rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-slate-300 focus:ring-2 focus:ring-slate-200"
                        :disabled="loading"
                    >
                        <option value="name">Name</option>
                        <option value="admission_id">Admission ID</option>
                        <option value="college_roll">College Roll</option>
                        <option value="reg_no">Reg No</option>
                        <option value="mobile">Mobile</option>
                    </select>

                    <input
                        v-model="filters.value"
                        type="text"
                        class="h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-slate-300 focus:ring-2 focus:ring-slate-200 sm:w-72"
                        placeholder="Search..."
                        :disabled="loading"
                        @keyup.enter="load(1)"
                    />

                    <div class="flex items-center gap-2">
                        <button
                            class="inline-flex h-9 w-10 items-center justify-center rounded-lg bg-emerald-600 text-white hover:bg-emerald-700"
                            :disabled="loading"
                            @click="load(1)"
                        >
                            <span class="sr-only">Search</span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m1.35-5.15a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </button>
                        <button
                            class="h-9 rounded-lg border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                            :disabled="loading"
                            @click="reset"
                        >
                            Reset
                        </button>

                        <div class="relative" ref="bulkMenu">
                            <button
                                type="button"
                                class="inline-flex h-9 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-slate-50"
                                :disabled="loading"
                                @click="toggleBulkMenu"
                            >
                                <span class="sr-only">Bulk Actions</span>
                                <svg viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5">
                                    <circle cx="12" cy="5" r="1.8" />
                                    <circle cx="12" cy="12" r="1.8" />
                                    <circle cx="12" cy="19" r="1.8" />
                                </svg>
                            </button>

                            <div
                                v-if="bulkMenuOpen"
                                class="absolute right-0 z-40 mt-2 w-56 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg"
                            >
                                <button
                                    type="button"
                                    class="block w-full px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-50"
                                    @click="bulkPrint"
                                >
                                    Print
                                </button>
                                <button
                                    type="button"
                                    class="block w-full px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-50"
                                    @click="bulkDownloadPdf"
                                >
                                    Download PDF
                                </button>
                                <button
                                    type="button"
                                    class="block w-full px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-50"
                                    @click="bulkExportExcel"
                                >
                                    Excel
                                </button>
                                <button
                                    type="button"
                                    class="block w-full px-4 py-2 text-left text-sm text-red-700 hover:bg-red-50"
                                    @click="bulkDeactivate"
                                >
                                    Bulk Delete
                                </button>
                                <button
                                    type="button"
                                    class="block w-full px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-50"
                                    @click="bulkDownloadZip"
                                >
                                    Download ZIP Image
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-6">
                <select
                    v-model="advanced.status"
                    class="h-9 rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-slate-300 focus:ring-2 focus:ring-slate-200"
                    :disabled="loading"
                >
                    <option value="">Status</option>
                    <option value="active">Active</option>
                    <option value="deactive">Deactive</option>
                </select>
                 <select
                    v-model="advanced.academic_session_id"
                    class="h-9 rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-slate-300 focus:ring-2 focus:ring-slate-200"
                    :disabled="loading"
                >
                    <option value="">Select Session</option>
                    <option v-for="s in sessionsSorted" :key="s.id" :value="s.id">{{ s.name }}</option>
                </select>
                <select
                    v-model="advanced.academic_qualification_id"
                    class="h-9 rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-slate-300 focus:ring-2 focus:ring-slate-200"
                    :disabled="loading"
                >
                    <option value="">Select Qualification</option>
                    <option v-for="q in qualifications" :key="q.id" :value="q.id">{{ q.name }}</option>
                </select>

                <select
                    v-model="advanced.department_id"
                    class="h-9 rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-slate-300 focus:ring-2 focus:ring-slate-200"
                    :disabled="loading"
                >
                    <option value="">Select Department</option>
                    <option v-for="d in filteredDepartments" :key="d.id" :value="d.id">{{ d.name }}</option>
                </select>

                <select
                    v-model="advanced.academic_class_id"
                    class="h-9 rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-slate-300 focus:ring-2 focus:ring-slate-200"
                    :disabled="loading"
                >
                    <option value="">Select Class</option>
                    <option v-for="c in filteredClasses" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>

                <select
                    v-model="advanced.student_type"
                    class="h-9 rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-slate-300 focus:ring-2 focus:ring-slate-200"
                    :disabled="loading"
                >
                    <option value="">Student Type</option>
                    <option v-for="t in studentTypes" :key="t" :value="t">{{ t }}</option>
                </select>

                <select
                    v-model="advanced.fourth_subject_id"
                    class="h-9 rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-slate-300 focus:ring-2 focus:ring-slate-200"
                    :disabled="loading || fourthSubjectOptions.length === 0"
                >
                    <option value="">4th Subject</option>
                    <option v-for="s in fourthSubjectOptions" :key="s.subject_id" :value="s.subject_id">
                        {{ s.subject_name }}
                    </option>
                </select>

                <div class="relative" ref="mainSubjectFilter">
                    <button
                        type="button"
                        class="flex h-9 w-full items-center justify-between rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-700 outline-none focus:border-slate-300 focus:ring-2 focus:ring-slate-200"
                        :disabled="loading || mainSubjectOptions.length === 0"
                        @click="toggleMainSubjectDropdown"
                    >
                        <span class="truncate">{{ mainSubjectFilterLabel }}</span>
                        <span class="ml-2 text-xs text-slate-500">▼</span>
                    </button>

                    <div
                        v-if="mainSubjectDropdownOpen"
                        class="absolute z-30 mt-2 max-h-60 w-full overflow-auto rounded-xl border border-slate-200 bg-white p-2 shadow-lg"
                    >
                        <label
                            v-for="s in mainSubjectOptions"
                            :key="'msf-' + s.subject_id"
                            class="flex cursor-pointer items-center gap-2 rounded-lg px-2 py-1 text-sm text-slate-800 hover:bg-slate-50"
                        >
                            <input type="checkbox" class="h-4 w-4" :value="s.subject_id" v-model="advanced.subject_ids" />
                            <span class="truncate">{{ s.subject_name }}</span>
                        </label>
                    </div>
                </div>

                <select
                    v-model="advanced.hostel_id"
                    class="h-9 rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-slate-300 focus:ring-2 focus:ring-slate-200"
                    :disabled="loading"
                >
                    <option value="">Hostel</option>
                    <option v-for="h in hostels" :key="h.id" :value="h.id">{{ h.name }}</option>
                </select>

                

                <select
                    v-model="advanced.gender"
                    class="h-9 rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-slate-300 focus:ring-2 focus:ring-slate-200"
                    :disabled="loading"
                >
                    <option value="">Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>

                <select
                    v-model="pagination"
                    class="h-9 rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-slate-300 focus:ring-2 focus:ring-slate-200"
                    :disabled="loading"
                >
                    <option :value="10">10 / page</option>
                    <option :value="25">25 / page</option>
                    <option :value="50">50 / page</option>
                    <option :value="100">100 / page</option>
                </select>
            </div>
        </div>

        <div v-if="error" class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            {{ error }}
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
            <div>
                <table id="pdf-table" class="min-w-full border-collapse border border-slate-200">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                            <th class="border border-slate-200 bg-slate-50 px-4 py-3">#</th>
                            <th class="border border-slate-200 bg-slate-50 px-4 py-3">Software ID</th>
                            <th class="border border-slate-200 bg-slate-50 px-4 py-3">Student</th>
                            <th class="border border-slate-200 bg-slate-50 px-4 py-3">Admission ID</th>
                            <th class="border border-slate-200 bg-slate-50 px-4 py-3">Roll</th>
                            <th class="border border-slate-200 bg-slate-50 px-4 py-3">Reg No.</th>
                            <th class="border border-slate-200 bg-slate-50 px-4 py-3">Session</th>
                            <th class="border border-slate-200 bg-slate-50 px-4 py-3">Dept. / Group</th>
                            <th class="border border-slate-200 bg-slate-50 px-4 py-3">Academic Level / Class</th>
                            <th class="border border-slate-200 bg-slate-50 px-4 py-3">Subjects</th>
                            <th class="border border-slate-200 bg-slate-50 px-4 py-3">Status</th>
                            <th class="border border-slate-200 bg-slate-50 px-4 py-3 action">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="loading" class="text-sm text-slate-600">
                            <td class="border border-slate-200 px-4 py-6" colspan="12">Loading students...</td>
                        </tr>
                        <tr v-else-if="rows.length === 0" class="text-sm text-slate-600">
                            <td class="border border-slate-200 px-4 py-6" colspan="12">No students found.</td>
                        </tr>
                        <tr
                            v-for="(row, idx) in rows"
                            :key="row.id"
                            class="text-sm text-slate-800"
                            :class="idx % 2 === 0 ? 'bg-white' : 'bg-slate-50'"
                        >
                            <td class="border border-slate-200 px-4 py-3 text-slate-600">{{ rowSerial(idx) }}</td>
                            <td class="border border-slate-200 px-4 py-3 text-center">
                                <div class="flex flex-col items-center gap-1">
                                    <div class="h-9 w-10 overflow-hidden rounded-md bg-slate-100">
                                        <img v-if="row.profile" class="h-full w-full object-cover" :src="row.profile" />
                                        <div v-else class="flex h-full w-full items-center justify-center text-xs font-semibold text-slate-500">
                                            {{ (row.student_id || '').slice(0, 2) || 'NA' }}
                                        </div>
                                    </div>
                                    <div class="text-xs font-semibold text-slate-900">{{ row.student_id }}</div>
                                </div>
                            </td>
                            <td class="border border-slate-200 px-4 py-3">
                                <div class="font-medium text-slate-900">{{ row.name }}</div>
                                <div class="text-xs text-slate-600">{{ row.mobile }}</div>
                                <div class="text-xs text-slate-600">{{ row.student_type }}</div>
                            </td>
                            <td class="border border-slate-200 px-4 py-3 text-center">{{ row.admission_id }}</td>
                            <td class="border border-slate-200 px-4 py-3 text-center">{{ row.college_roll }}</td>
                            <td class="border border-slate-200 px-4 py-3 text-center">{{ row.reg_no }}</td>
                            <td class="border border-slate-200 px-4 py-3">{{ row.academic_session_name || '' }}</td>
                            <td class="border border-slate-200 px-4 py-3">{{ row.department_name || '' }}</td>
                            <td class="border border-slate-200 px-4 py-3">
                                {{ row.academic_qualification_name || '' }}
                                <div class="text-xs text-slate-600">({{ row.academic_class_name || '' }})</div>
                            </td>
                            <td class="border border-slate-200 px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <span
                                        class="inline-flex h-6 min-w-8 items-center justify-center rounded-full px-2 text-xs font-semibold"
                                        :class="Number(row.subjects_count || 0) > 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'"
                                    >
                                        {{ row.subjects_count || 0 }}
                                    </span>
                                    <button
                                        class="rounded-md border border-slate-200 bg-white px-2 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50"
                                        type="button"
                                        @click="openSubjectModal(row)"
                                    >
                                        Edit
                                    </button>
                                </div>
                            </td>
                            <td class="border border-slate-200 px-4 py-3">
                                <span
                                    class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold"
                                    :class="row.status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-200 text-red-700'"
                                >
                                    {{ row.status || 'n/a' }}
                                </span>
                            </td>

                            <td class="border border-slate-200 px-4 py-3 action">
                                <div class="flex items-center gap-2">
                                    <button
                                        type="button"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-slate-200 bg-white text-slate-700 hover:bg-slate-50"
                                        title="View"
                                        @click="viewStudent(row.id)"
                                    >
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12s3.5-7 9.5-7 9.5 7 9.5 7-3.5 7-9.5 7-9.5-7-9.5-7z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15a3 3 0 100-6 3 3 0 000 6z" />
                                        </svg>
                                    </button>
                                    <button
                                        type="button"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-slate-200 bg-white text-slate-700 hover:bg-slate-50"
                                        title="Edit"
                                        @click="editStudent(row.id)"
                                    >
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 3.487a2.25 2.25 0 013.182 3.182L8.25 18.463 3 21l2.538-5.25L16.862 3.487z" />
                                        </svg>
                                    </button>
                                    <button
                                        type="button"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-red-200 bg-white text-red-700 hover:bg-red-50"
                                        title="Delete"
                                        @click="deactivateSingle(row.id)"
                                    >
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 7V5a1 1 0 011-1h4a1 1 0 011 1v2" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 11v6" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 11v6" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 7l1 14h8l1-14" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col gap-3 border-t border-slate-200 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="text-xs text-slate-600">
                    Showing {{ meta.from || 0 }} to {{ meta.to || 0 }} of {{ meta.total || 0 }} entries
                </div>

                <div class="flex items-center justify-end gap-1 overflow-x-auto">
                    <button
                        type="button"
                        class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-slate-200 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50"
                        :disabled="loading || meta.current_page <= 1"
                        @click="load(meta.current_page - 1)"
                    >
                        «
                    </button>

                    <template v-for="p in paginationPages" :key="'p-' + p">
                        <span v-if="p === '...'
                            " class="inline-flex h-8 min-w-8 items-center justify-center px-2 text-xs font-semibold text-slate-500">
                            ...
                        </span>
                        <button
                            v-else
                            type="button"
                            class="inline-flex h-8 min-w-8 items-center justify-center rounded-md border px-2 text-xs font-semibold"
                            :class="Number(p) === Number(meta.current_page) ? 'border-blue-600 bg-blue-600 text-white' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'"
                            :disabled="loading"
                            @click="load(Number(p))"
                        >
                            {{ p }}
                        </button>
                    </template>

                    <button
                        type="button"
                        class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-slate-200 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50"
                        :disabled="loading || meta.current_page >= meta.last_page"
                        @click="load(meta.current_page + 1)"
                    >
                        »
                    </button>
                </div>
            </div>
        </div>

        <div v-if="subjectModal.open" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/40" @click="closeSubjectModal"></div>

            <div class="relative w-full max-w-3xl rounded-2xl border border-slate-200 bg-white shadow-xl">
                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                    <div>
                        <div class="text-sm font-semibold text-slate-900">Add / Edit Subject</div>
                        <div class="text-xs text-slate-600">{{ subjectModal.student?.name || '' }}</div>
                    </div>
                    <button
                        type="button"
                        class="rounded-md border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50"
                        @click="closeSubjectModal"
                    >
                        Close
                    </button>
                </div>

                <div class="max-h-[70vh] overflow-auto p-5">
                    <div v-if="subjectModal.loading" class="text-sm text-slate-600">Loading...</div>
                    <div v-else-if="!subjectModal.subjectAssignDetails || subjectModal.subjectAssignDetails.length === 0" class="text-sm text-slate-600">
                        No subject configuration found for this student.
                    </div>
                    <div v-else class="space-y-6">
                        <div v-if="subjectModal.error" class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                            {{ subjectModal.error }}
                        </div>

                        <div v-if="subjectModal.note" class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                            {{ subjectModal.note }}
                        </div>

                        <div>
                            <div class="text-sm font-semibold text-slate-900">Compulsory Subjects</div>
                            <div class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2">
                                <div
                                    v-for="s in modalCompulsorySubjects"
                                    :key="'comp-' + s.subject_id"
                                    class="flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-800"
                                >
                                    <input type="checkbox" class="h-4 w-4" checked disabled />
                                    <span>{{ s.subject_name }}</span>
                                </div>
                                <div v-if="modalCompulsorySubjects.length === 0" class="text-sm text-slate-600">No compulsory subjects</div>
                            </div>
                        </div>

                        <div>
                            <div class="text-sm font-semibold text-slate-900">4th Subject</div>
                            <div class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2">
                                <label
                                    v-for="s in modalFourthSubjects"
                                    :key="'fourth-' + s.subject_id"
                                    class="flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-800"
                                >
                                    <input
                                        type="radio"
                                        name="fourth_subject"
                                        class="h-4 w-4"
                                        :value="s.subject_id"
                                        v-model="subjectModal.selectedFourth"
                                    />
                                    <span>{{ s.subject_name }}</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between gap-3">
                                <div class="text-sm font-semibold text-slate-900">Main Subjects</div>
                                <div v-if="subjectModal.requiredMainCount" class="text-xs font-semibold text-slate-600">
                                    Required: {{ subjectModal.requiredMainCount }}
                                </div>
                            </div>
                            <div class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2">
                                <label
                                    v-for="s in modalMainSubjects"
                                    :key="'main-' + s.subject_id"
                                    class="flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-800"
                                >
                                    <input
                                        type="checkbox"
                                        class="h-4 w-4"
                                        :value="s.subject_id"
                                        v-model="subjectModal.selectedMain"
                                        :disabled="isMainSubjectDisabled(s.subject_id)"
                                    />
                                    <span>{{ s.subject_name }}</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-slate-200 px-5 py-4">
                    <button
                        type="button"
                        class="rounded-md border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50"
                        :disabled="subjectModal.saving"
                        @click="closeSubjectModal"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        class="rounded-md bg-slate-900 px-4 py-2 text-xs font-semibold text-white hover:bg-slate-800"
                        :disabled="subjectModal.saving || subjectModal.loading"
                        @click="saveSubjectModal"
                    >
                        {{ subjectModal.saving ? 'Saving...' : 'Save' }}
                    </button>
                </div>
            </div>
        </div>

        <div v-if="bulkDeleteModal.open" class="fixed inset-0 z-50">
            <div class="absolute inset-0 bg-slate-900/40" @click="closeBulkDeleteModal"></div>
            <div class="absolute left-1/2 top-1/2 w-[92vw] max-w-3xl -translate-x-1/2 -translate-y-1/2 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                    <div>
                        <div class="text-sm font-semibold text-slate-900">Bulk Delete</div>
                        <div class="mt-1 text-xs text-slate-600">Select students from the current search result.</div>
                    </div>
                    <button class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50" @click="closeBulkDeleteModal">
                        Close
                    </button>
                </div>

                <div class="px-5 py-4">
                    <div class="flex items-center justify-between gap-3">
                        <div class="text-xs text-slate-600">Showing: {{ rows.length }} students</div>
                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50"
                                @click="bulkDeleteSelectAll"
                            >
                                Select All
                            </button>
                            <button
                                type="button"
                                class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50"
                                @click="bulkDeleteClear"
                            >
                                Clear
                            </button>
                        </div>
                    </div>

                    <div class="mt-4 max-h-[55vh] overflow-auto rounded-xl border border-slate-200">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                    <th class="px-4 py-3 text-center" style="width:48px;">
                                        <input
                                            type="checkbox"
                                            class="h-4 w-4"
                                            :checked="bulkDeleteAllSelected"
                                            :disabled="rows.length === 0"
                                            @change="toggleBulkDeleteAll($event)"
                                        />
                                    </th>
                                    <th class="px-4 py-3">Software ID</th>
                                    <th class="px-4 py-3">Student</th>
                                    <th class="px-4 py-3 text-center">Roll</th>
                                    <th class="px-4 py-3 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                <tr v-for="r in rows" :key="'bd-' + r.id" class="text-sm text-slate-800">
                                    <td class="px-4 py-3 text-center">
                                        <input type="checkbox" class="h-4 w-4" :value="String(r.id)" v-model="bulkDeleteModal.selectedIds" />
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="text-xs font-semibold text-slate-900">{{ r.student_id }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-semibold">{{ r.name }}</div>
                                        <div class="text-xs text-slate-600">{{ r.mobile }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-center">{{ r.college_roll }}</td>
                                    <td class="px-4 py-3 text-center">{{ r.status }}</td>
                                </tr>
                                <tr v-if="rows.length === 0">
                                    <td class="px-4 py-6 text-center text-sm text-slate-600" colspan="5">No students found.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 flex items-center justify-end gap-2">
                        <button
                            type="button"
                            class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                            @click="closeBulkDeleteModal"
                        >
                            Cancel
                        </button>
                        <button
                            type="button"
                            class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-60"
                            :disabled="bulkDeleteModal.saving || bulkDeleteModal.selectedIds.length === 0"
                            @click="confirmBulkDelete"
                        >
                            {{ bulkDeleteModal.saving ? 'Deleting...' : 'Delete Selected' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'StudentsIndex',
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
            rows: [],
            meta: {
                current_page: 1,
                last_page: 1,
                from: 0,
                to: 0,
                total: 0,
                per_page: 10,
            },
            filters: {
                field_name: 'mobile',
                value: '',
            },
            advanced: {
                academic_class_id: '',
                academic_session_id: '',
                academic_qualification_id: '',
                department_id: '',
                student_type: '',
                hostel_id: '',
                subject_ids: [],
                fourth_subject_id: '',
                status: '',
                gender: '',
            },
            pagination: 10,
            subjects: [],
            mainSubjectDropdownOpen: false,
            bulkMenuOpen: false,
            downloadSpinner: false,
            bulkDeleteModal: {
                open: false,
                saving: false,
                selectedIds: [],
            },
            subjectModal: {
                open: false,
                loading: false,
                saving: false,
                initializing: false,
                student: null,
                subjectAssignDetails: [],
                selectedMain: [],
                selectedFourth: '',
                requiredMainCount: 0,
                note: '',
                error: '',
                exceptSubjectCounts: {},
            },
        }
    },
    computed: {
        bulkDeleteAllSelected() {
            if (!Array.isArray(this.rows) || this.rows.length === 0) return false
            const set = new Set((this.bulkDeleteModal.selectedIds || []).map((v) => String(v)))
            return this.rows.every((r) => set.has(String(r.id)))
        },
        classes() {
            const list = this.systems?.global?.academic_classes
            if (!Array.isArray(list)) return []
            return list.filter((r) => r?.status === undefined || r?.status === null || String(r.status) === 'active' || String(r.status) === '1')
        },
        filteredClasses() {
            const qualificationId = this.advanced?.academic_qualification_id
            if (!qualificationId) return []
            return this.classes.filter((c) => String(c.academic_qualification_id) === String(qualificationId))
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
            const list = this.systems?.global?.department_qualidactions
            return Array.isArray(list) ? list : []
        },
        filteredDepartments() {
            const qualificationId = this.advanced?.academic_qualification_id
            if (!qualificationId) return []

            const allowedDeptIds = new Set(
                this.departmentQualidactions
                    .filter((r) => String(r.academic_qualification_id) === String(qualificationId))
                    .map((r) => String(r.department_id))
            )

            return this.departments.filter((d) => allowedDeptIds.has(String(d.id)))
        },
        hostels() {
            const list = this.systems?.global?.hostels
            if (!Array.isArray(list)) return []
            return list.filter((r) => r?.status === undefined || r?.status === null || String(r.status) === 'active' || String(r.status) === '1')
        },
        studentTypes() {
            const list = this.systems?.global?.student_types
            return Array.isArray(list) ? list : []
        },
        mainSubjectOptions() {
            return this.subjects
                .filter((s) => Number(s.main_subject) === 1)
                .map((s) => ({
                    subject_id: s.subject_id,
                    subject_name: s.subject_name,
                }))
        },
        fourthSubjectOptions() {
            return this.subjects
                .filter((s) => Number(s.fourth_subject) === 1)
                .map((s) => ({
                    subject_id: s.subject_id,
                    subject_name: s.subject_name,
                }))
        },

        mainSubjectFilterLabel() {
            const ids = Array.isArray(this.advanced?.subject_ids) ? this.advanced.subject_ids : []
            if (ids.length === 0) return '--Main Subject--'

            const idSet = new Set(ids.map((v) => String(v)))
            const names = (this.mainSubjectOptions || [])
                .filter((s) => idSet.has(String(s.subject_id)))
                .map((s) => s.subject_name)

            return names.length > 0 ? names.join(', ') : `${ids.length} selected`
        },

        modalCompulsorySubjects() {
            return (this.subjectModal.subjectAssignDetails || [])
                .filter((s) => Number(s.fourth_subject) === 0 && Number(s.main_subject) === 0)
                .map((s) => ({
                    subject_id: s.subject_id,
                    subject_name: s.subject_name,
                }))
        },

        modalMainSubjects() {
            const all = Array.isArray(this.subjectModal.subjectAssignDetails) ? this.subjectModal.subjectAssignDetails : []
            const selectedFourth = this.subjectModal.selectedFourth
            const fourthObj = all.find((s) => String(s.subject_id) === String(selectedFourth))
            const excluded = new Set()
            if (selectedFourth) excluded.add(String(selectedFourth))
            if (fourthObj?.except_subject_id) excluded.add(String(fourthObj.except_subject_id))

            return all
                .filter((s) => Number(s.main_subject) === 1)
                .filter((s) => !excluded.has(String(s.subject_id)))
                .map((s) => ({
                    subject_id: s.subject_id,
                    subject_name: s.subject_name,
                    except_subject_id: s.except_subject_id,
                }))
        },
        modalFourthSubjects() {
            return (this.subjectModal.subjectAssignDetails || [])
                .filter((s) => Number(s.fourth_subject) === 1)
                .map((s) => ({
                    subject_id: s.subject_id,
                    subject_name: s.subject_name,
                }))
        },
        paginationPages() {
            const current = Number(this.meta?.current_page || 1)
            const last = Number(this.meta?.last_page || 1)
            return this.buildPaginationPages(current, last)
        },
    },
    watch: {
        'advanced.academic_qualification_id': {
            handler(next, prev) {
                const changed = String(next || '') !== String(prev || '')
                if (changed) {
                    this.advanced.academic_class_id = ''
                    this.advanced.department_id = ''
                    this.clearSubjectFilters()
                }

                const qualificationId = this.advanced?.academic_qualification_id

                if (!qualificationId) {
                    this.advanced.department_id = ''
                    this.advanced.academic_class_id = ''
                    this.clearSubjectFilters()
                    return
                }

                const classIds = new Set((this.filteredClasses || []).map((c) => String(c.id)))
                if (this.advanced.academic_class_id && !classIds.has(String(this.advanced.academic_class_id))) {
                    this.advanced.academic_class_id = ''
                    this.clearSubjectFilters()
                }

                const deptIds = new Set((this.filteredDepartments || []).map((d) => String(d.id)))
                if (this.advanced.department_id && !deptIds.has(String(this.advanced.department_id))) {
                    this.advanced.department_id = ''
                    this.clearSubjectFilters()
                }
            },
        },
        'advanced.department_id'(next, prev) {
            if (String(next || '') !== String(prev || '')) {
                this.clearSubjectFilters()
                this.fetchClasswiseSubjects()
            }
        },
        'advanced.academic_class_id'(next, prev) {
            if (String(next || '') !== String(prev || '')) {
                this.clearSubjectFilters()
                this.fetchClasswiseSubjects()
            }
        },
        'subjectModal.selectedFourth'(next, prev) {
            if (this.subjectModal.initializing) return
            if (String(next || '') !== String(prev || '')) {
                this.subjectModal.selectedMain = []
                this.subjectModal.exceptSubjectCounts = {}
            }
        },
        'subjectModal.selectedMain': {
            handler() {
                this.recomputeExceptSubjectCounts()
            },
            deep: true,
        },
    },
    mounted() {
        window.addEventListener('click', this.onWindowClick)
        this.load(1)
    },
    beforeUnmount() {
        window.removeEventListener('click', this.onWindowClick)
    },
    methods: {
        rowSerial(idx) {
            const i = Number(idx || 0)
            const from = Number(this.meta?.from ?? 0)
            if (Number.isFinite(from) && from > 0) return from + i
            return i + 1
        },
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
        buildPaginationPages(current, last) {
            const c = Number(current || 1)
            const l = Number(last || 1)
            if (!Number.isFinite(c) || !Number.isFinite(l) || l <= 1) return [1]

            const clamp = (v) => Math.max(1, Math.min(l, v))
            const pages = new Set([1, l])

            for (let i = -2; i <= 2; i++) {
                pages.add(clamp(c + i))
            }

            const sorted = Array.from(pages).sort((a, b) => a - b)
            const out = []
            for (let i = 0; i < sorted.length; i++) {
                const p = sorted[i]
                const prev = out.length ? out[out.length - 1] : null
                if (typeof prev === 'number' && p - prev > 1) {
                    out.push('...')
                }
                out.push(p)
            }
            return out
        },
        onWindowClick(e) {
            if (this.mainSubjectDropdownOpen) {
                const el = this.$refs.mainSubjectFilter
                if (el && !el.contains(e.target)) {
                    this.mainSubjectDropdownOpen = false
                }
            }

            if (this.bulkMenuOpen) {
                const menu = this.$refs.bulkMenu
                if (menu && !menu.contains(e.target)) {
                    this.bulkMenuOpen = false
                }
            }
        },
        toggleMainSubjectDropdown() {
            this.mainSubjectDropdownOpen = !this.mainSubjectDropdownOpen
        },
        toggleBulkMenu() {
            this.bulkMenuOpen = !this.bulkMenuOpen
        },
        buildQueryString(extra = {}) {
            const params = {
                field_name: this.filters.field_name,
                value: this.filters.value,
                academic_class_id: this.advanced.academic_class_id,
                academic_session_id: this.advanced.academic_session_id,
                academic_qualification_id: this.advanced.academic_qualification_id,
                department_id: this.advanced.department_id,
                student_type: this.advanced.student_type,
                hostel_id: this.advanced.hostel_id,
                subject_ids: this.advanced.subject_ids,
                fourth_subject_id: this.advanced.fourth_subject_id,
                status: this.advanced.status,
                gender: this.advanced.gender,
                ...extra,
            }

            const qs = new URLSearchParams()
            Object.keys(params).forEach((k) => {
                const v = params[k]
                if (v === null || v === undefined || v === '') return
                if (Array.isArray(v)) {
                    v.forEach((av) => {
                        if (av !== null && av !== undefined && av !== '') qs.append(`${k}[]`, av)
                    })
                } else {
                    qs.set(k, v)
                }
            })

            return qs.toString()
        },
        bulkPrint() {
            this.bulkMenuOpen = false
            const qs = this.buildQueryString()
            window.open(`/admin/student/print?${qs}`, '_blank')
        },
        async bulkDownloadPdf() {
            this.bulkMenuOpen = false

            const table = document.getElementById('pdf-table')
            if (!table) return

            const actionEls = Array.from(table.querySelectorAll('.action'))
            const prevDisplay = actionEls.map((el) => el.style.display)
            actionEls.forEach((el) => {
                el.style.display = 'none'
            })

            try {
                const mod = await import('jspdf')
                const jsPDF = mod?.jsPDF
                const auto = await import('jspdf-autotable')
                const autoTable = auto?.default
                if (!jsPDF || !autoTable) return

                const doc = new jsPDF({
                    orientation: 'landscape',
                    unit: 'mm',
                    format: 'a4',
                })

                autoTable(doc, {
                    html: '#pdf-table',
                    margin: { top: 10, right: 2, bottom: 2, left: 2 },
                    styles: { fontSize: 8 },
                    didDrawPage: () => {
                        doc.setFontSize(14)
                        doc.text('STUDENT LIST', doc.internal.pageSize.width / 2, 7, { align: 'center' })
                    },
                })

                doc.save('students.pdf')
            } finally {
                actionEls.forEach((el, i) => {
                    el.style.display = prevDisplay[i] || ''
                })
            }
        },
        bulkExportExcel() {
            this.bulkMenuOpen = false

            const rows = Array.isArray(this.rows) ? this.rows : []
            const escape = (v) => {
                const s = String(v ?? '')
                return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;')
            }

            const html =
                '<html><head><meta charset="utf-8"></head><body>' +
                '<table border="1">' +
                '<tr>' +
                '<th>Software ID</th>' +
                '<th>Name</th>' +
                '<th>Mobile</th>' +
                '<th>Admission ID</th>' +
                '<th>Roll</th>' +
                '<th>Reg No</th>' +
                '<th>Session</th>' +
                '<th>Department</th>' +
                '<th>Qualification</th>' +
                '<th>Class</th>' +
                '<th>Subjects</th>' +
                '<th>Status</th>' +
                '</tr>' +
                rows
                    .map((r) => {
                        return (
                            '<tr>' +
                            `<td>${escape(r.student_id)}</td>` +
                            `<td>${escape(r.name)}</td>` +
                            `<td>${escape(r.mobile)}</td>` +
                            `<td>${escape(r.admission_id)}</td>` +
                            `<td>${escape(r.college_roll)}</td>` +
                            `<td>${escape(r.reg_no)}</td>` +
                            `<td>${escape(r.academic_session_name)}</td>` +
                            `<td>${escape(r.department_name)}</td>` +
                            `<td>${escape(r.academic_qualification_name)}</td>` +
                            `<td>${escape(r.academic_class_name)}</td>` +
                            `<td>${escape(r.subjects_count)}</td>` +
                            `<td>${escape(r.status)}</td>` +
                            '</tr>'
                        )
                    })
                    .join('') +
                '</table></body></html>'

            const blob = new Blob([html], { type: 'application/vnd.ms-excel' })
            const url = URL.createObjectURL(blob)
            const a = document.createElement('a')
            a.href = url
            a.download = `students_${new Date().toISOString().slice(0, 19).replace(/[:T]/g, '_')}.xls`
            document.body.appendChild(a)
            a.click()
            document.body.removeChild(a)
            URL.revokeObjectURL(url)
        },
        async bulkDeactivate() {
            this.bulkMenuOpen = false
            this.openBulkDeleteModal()
        },
        async bulkDownloadZip() {
            this.bulkMenuOpen = false
            if (!confirm('Are you sure want to download ZIP image?')) return
            const payload = {
                pagination: this.pagination,
                field_name: this.filters.field_name,
                value: this.filters.value,
                academic_class_id: this.advanced.academic_class_id,
                academic_session_id: this.advanced.academic_session_id,
                academic_qualification_id: this.advanced.academic_qualification_id,
                department_id: this.advanced.department_id,
                student_type: this.advanced.student_type,
                hostel_id: this.advanced.hostel_id,
                subject_ids: this.advanced.subject_ids,
                fourth_subject_id: this.advanced.fourth_subject_id,
                status: this.advanced.status,
                gender: this.advanced.gender,
            }
            const res = await window.axios.post('/admin/download-student-zip-image', payload)
            const zipName = res?.data?.zip_name
            const downloadUrl = res?.data?.download_url
            if (!zipName || !downloadUrl) return

            const statusUrl = `/admin/check-zip-status/${encodeURIComponent(zipName)}`
            const timeoutMs = 10 * 60 * 1000
            const intervalMs = 5000
            const start = Date.now()

            const tick = async () => {
                const elapsed = Date.now() - start
                if (elapsed > timeoutMs) {
                    clearInterval(timer)
                    alert('Zip generation timed out. Try again later.')
                    return
                }

                try {
                    const check = await window.axios.get(statusUrl)
                    if (check?.data?.ready) {
                        clearInterval(timer)
                        window.location.href = check?.data?.downloadUrl || downloadUrl
                    }
                } catch (e) {
                    // keep polling
                }
            }

            const timer = setInterval(tick, intervalMs)
            await tick()
        },
        viewStudent(id) {
            window.location.href = `/admin/student/${id}`
        },
        editStudent(id) {
            window.location.href = `/admin/student/${id}/edit`
        },
        async deactivateSingle(id) {
            if (!confirm('Are you sure want to delete?')) return
            await window.axios.post('/admin/student/bulk-deactivate', { ids: [id] })
            await this.load(this.meta.current_page || 1)
        },
        openBulkDeleteModal() {
            this.bulkDeleteModal.open = true
            this.bulkDeleteModal.selectedIds = []
        },
        closeBulkDeleteModal() {
            if (this.bulkDeleteModal.saving) return
            this.bulkDeleteModal.open = false
        },
        bulkDeleteSelectAll() {
            this.bulkDeleteModal.selectedIds = (this.rows || []).map((r) => String(r.id))
        },
        bulkDeleteClear() {
            this.bulkDeleteModal.selectedIds = []
        },
        toggleBulkDeleteAll(e) {
            const checked = !!e?.target?.checked
            if (checked) this.bulkDeleteSelectAll()
            else this.bulkDeleteClear()
        },
        async confirmBulkDelete() {
            const ids = (this.bulkDeleteModal.selectedIds || []).map((v) => Number(v)).filter((v) => Number.isFinite(v))
            if (ids.length === 0) return
            if (!confirm('Are you sure want to bulk delete?')) return

            this.bulkDeleteModal.saving = true
            try {
                await window.axios.post('/admin/student/bulk-deactivate', { ids })
                this.bulkDeleteModal.open = false
                this.bulkDeleteModal.selectedIds = []
                await this.load(this.meta.current_page || 1)
            } finally {
                this.bulkDeleteModal.saving = false
            }
        },
        clearSubjectFilters() {
            this.subjects = []
            this.advanced.subject_ids = []
            this.advanced.fourth_subject_id = ''
        },
        async fetchClasswiseSubjects() {
            const academicQualificationId = this.advanced.academic_qualification_id
            const academicClassId = this.advanced.academic_class_id
            const departmentId = this.advanced.department_id

            if (!academicClassId) {
                this.subjects = []
                return
            }

            try {
                const res = await window.axios.get('/admin/classwise-subjects', {
                    params: {
                        academic_qualification_id: academicQualificationId,
                        academic_class_id: academicClassId,
                        department_id: departmentId,
                    },
                })
                this.subjects = Array.isArray(res?.data) ? res.data : []
            } catch (e) {
                this.subjects = []
            }
        },

        async openSubjectModal(student) {
            this.subjectModal.open = true
            this.subjectModal.loading = true
            this.subjectModal.saving = false
            this.subjectModal.initializing = true
            this.subjectModal.student = student
            this.subjectModal.subjectAssignDetails = []
            this.subjectModal.selectedMain = []
            this.subjectModal.selectedFourth = ''
            this.subjectModal.requiredMainCount = 0
            this.subjectModal.note = ''
            this.subjectModal.error = ''
            this.subjectModal.exceptSubjectCounts = {}

            try {
                const res = await window.axios.get(`/admin/get-student-subjects/${student.id}`)
                const payload = res?.data || {}

                const details = payload?.subjectAssign?.details
                this.subjectModal.subjectAssignDetails = Array.isArray(details) ? details : []

                this.subjectModal.requiredMainCount = Number(payload?.subjectAssign?.main_subject || 0)
                this.subjectModal.note = payload?.subjectAssign?.note || ''

                const assigned = Array.isArray(payload?.assign_subjects) ? payload.assign_subjects : []
                this.subjectModal.selectedMain = assigned
                    .filter((s) => Number(s.main_subject) === 1)
                    .map((s) => s.subject_id)

                const fourth = assigned.find((s) => Number(s.main_subject) === 0)
                this.subjectModal.selectedFourth = fourth ? fourth.subject_id : ''
                this.recomputeExceptSubjectCounts()
            } catch (e) {
                this.subjectModal.subjectAssignDetails = []
            } finally {
                this.subjectModal.initializing = false
                this.subjectModal.loading = false
            }
        },
        closeSubjectModal() {
            this.subjectModal.open = false
            this.subjectModal.error = ''
        },
        async saveSubjectModal() {
            if (!this.subjectModal.student?.id) return

            this.subjectModal.error = ''

            if (this.modalFourthSubjects.length > 0 && !this.subjectModal.selectedFourth) {
                this.subjectModal.error = 'Please select 4th subject.'
                return
            }

            if (this.subjectModal.requiredMainCount) {
                const required = Number(this.subjectModal.requiredMainCount)
                if ((this.subjectModal.selectedMain || []).length !== required) {
                    this.subjectModal.error = `Please select minimum ${required} subject.`
                    return
                }
            }

            this.subjectModal.saving = true

            try {
                const data = []
                ;(this.subjectModal.selectedMain || []).forEach((id) => {
                    data.push({ subject_id: id, main_subject: 1 })
                })
                if (this.subjectModal.selectedFourth) {
                    data.push({ subject_id: this.subjectModal.selectedFourth, main_subject: 0 })
                }

                await window.axios.post('/admin/subject-assign', {
                    student_id: this.subjectModal.student.id,
                    data,
                })

                this.closeSubjectModal()
                await this.load(this.meta.current_page || 1)
            } catch (e) {
                // keep modal open
            } finally {
                this.subjectModal.saving = false
            }
        },

        recomputeExceptSubjectCounts() {
            const all = Array.isArray(this.subjectModal.subjectAssignDetails) ? this.subjectModal.subjectAssignDetails : []
            const selected = Array.isArray(this.subjectModal.selectedMain) ? this.subjectModal.selectedMain : []
            const counts = {}

            selected.forEach((id) => {
                const obj = all.find((s) => String(s.subject_id) === String(id))
                const ex = obj?.except_subject_id
                if (ex) {
                    const key = String(ex)
                    counts[key] = (counts[key] || 0) + 1
                }
            })

            this.subjectModal.exceptSubjectCounts = counts
        },
        isMainSubjectDisabled(subjectId) {
            const required = Number(this.subjectModal.requiredMainCount || 0)
            const selected = Array.isArray(this.subjectModal.selectedMain) ? this.subjectModal.selectedMain : []
            const isChecked = selected.map(String).includes(String(subjectId))
            const maxReached = required > 0 && selected.length >= required

            const exceptCounts = this.subjectModal.exceptSubjectCounts || {}
            const isExceptBlocked = (exceptCounts[String(subjectId)] || 0) > 0

            if (!isChecked && maxReached) return true
            if (!isChecked && isExceptBlocked) return true
            return false
        },
        sl(idx) {
            const from = Number(this.meta?.from || 0)
            return from + idx
        },
        async load(page = 1) {
            this.loading = true
            this.error = ''

            try {
                const res = await window.axios.get('/admin/student', {
                    params: {
                        page,
                        pagination: this.pagination,
                        field_name: this.filters.field_name,
                        value: this.filters.value,
                        academic_class_id: this.advanced.academic_class_id,
                        academic_session_id: this.advanced.academic_session_id,
                        academic_qualification_id: this.advanced.academic_qualification_id,
                        department_id: this.advanced.department_id,
                        student_type: this.advanced.student_type,
                        hostel_id: this.advanced.hostel_id,
                        subject_ids: this.advanced.subject_ids,
                        fourth_subject_id: this.advanced.fourth_subject_id,
                        status: this.advanced.status,
                        gender: this.advanced.gender,
                    },
                })

                const payload = res?.data || {}
                this.rows = Array.isArray(payload?.data) ? payload.data : []
                this.meta = {
                    current_page: payload?.current_page || 1,
                    last_page: payload?.last_page || 1,
                    from: payload?.from ?? 0,
                    to: payload?.to ?? 0,
                    total: payload?.total ?? 0,
                    per_page: payload?.per_page || this.pagination,
                }
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load students.'
            } finally {
                this.loading = false
            }
        },
        reset() {
            this.filters.field_name = 'mobile'
            this.filters.value = ''
            this.advanced.academic_class_id = ''
            this.advanced.academic_session_id = ''
            this.advanced.academic_qualification_id = ''
            this.advanced.department_id = ''
            this.advanced.student_type = ''
            this.advanced.hostel_id = ''
            this.advanced.subject_ids = []
            this.advanced.fourth_subject_id = ''
            this.advanced.status = ''
            this.advanced.gender = ''
            this.pagination = 10
            this.subjects = []
            this.load(1)
        },
    },
}
</script>
