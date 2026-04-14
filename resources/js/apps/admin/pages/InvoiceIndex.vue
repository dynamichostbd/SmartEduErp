<template>
    <div class="flex flex-col gap-4">
        <div class=" border border-slate-300 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">{{ pageHeading }}</div>
                    <div class="mt-1 text-sm text-slate-600">Invoices</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-sm border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="goDashboard">
                        <i class="fas fa-tachometer-alt mr-2"></i>
                        Dashboard
                    </button>
                    <button type="button" class="rounded-sm bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700" @click="goCreate">
                        <i class="fas fa-plus mr-2"></i>
                        Create
                    </button>
                </div>
            </div>
        </div>

        <div v-if="error" class=" border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ error }}</div>

        <div class=" border border-slate-300 bg-white p-5">
            <div class="grid grid-cols-1 gap-3 lg:grid-cols-12">
                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Status</div>
                    <select v-model="filters.status" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">All</option>
                        <option value="pending">Dues</option>
                        <option value="success">Paid</option>
                        <option value="failed">Failed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Session</div>
                    <select v-model="filters.academic_session_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">All</option>
                        <option v-for="s in sessionsSorted" :key="'ses-' + s.id" :value="String(s.id)">{{ s.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Academic Level</div>
                    <select v-model="filters.academic_qualification_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">All</option>
                        <option v-for="q in qualifications" :key="'q-' + q.id" :value="String(q.id)">{{ q.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Department</div>
                    <select v-model="filters.department_id" :disabled="!filters.academic_qualification_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">All</option>
                        <option v-for="d in filteredDepartments" :key="'d-' + d.id" :value="String(d.id)">{{ d.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Class</div>
                    <select v-model="filters.academic_class_id" :disabled="!filters.academic_qualification_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">All</option>
                        <option v-for="c in filteredClasses" :key="'c-' + c.id" :value="String(c.id)">{{ c.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Student Type</div>
                    <select v-model="filters.student_type" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">All</option>
                        <option v-for="t in studentTypes" :key="'t-' + t" :value="t">{{ t }}</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">From Date</div>
                    <input v-model="filters.from_date" type="date" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">To Date</div>
                    <input v-model="filters.to_date" type="date" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                </div>

                <div v-if="mode === 'accountHeadWise'" class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">All Account Head</div>
                    <select v-model="filters.account_head_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">All</option>
                        <option v-for="h in accountHeads" :key="'ah-' + h.id" :value="String(h.id)">{{ h.name }}</option>
                    </select>
                </div>

                <div v-if="mode === 'accountWise'" class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">All Account</div>
                    <select v-model="filters.payment_gateway_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">All</option>
                        <option v-for="a in accountsCommons" :key="'acc-' + a.id" :value="String(a.id)">{{ a.account_no }}</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Exam</div>
                    <select v-model="filters.exam_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">All</option>
                        <option v-for="e in exams" :key="'ex-' + e.id" :value="String(e.id)">{{ e.name }}</option>
                    </select>
                </div>

                <div v-if="filters.exam_id" class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Exam Year</div>
                    <select v-model="filters.examination_year" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">--Select Exam Year--</option>
                        <option v-for="y in years" :key="'y-' + y" :value="y">{{ y }}</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Search Field</div>
                    <select v-model="filters.field_name" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" :disabled="loading">
                        <option value="">Select Field</option>
                        <option value="name">Name</option>
                        <option value="mobile">Mobile</option>
                        <option value="college_roll">College Roll</option>
                        <option value="admission_id">Admission ID</option>
                        <option value="reg_no">Reg No</option>
                        <option value="invoice_number">Invoice Number</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <div class="text-xs font-semibold text-slate-600">Search</div>
                    <input v-model="filters.value" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" @keydown.enter.prevent="load(1)" />
                </div>

                <div class="lg:col-span-1 flex items-end gap-2">
                    <button type="button" class="h-9 w-full rounded-sm bg-emerald-600 px-4 text-sm font-semibold text-white hover:bg-emerald-700" :disabled="loading" @click="load(1)">
                        <i v-if="loading" class="fas fa-spinner fa-spin mr-2"></i>
                        <i v-else class="fas fa-search mr-2"></i>
                        {{ loading ? '...' : 'Go' }}
                    </button>
                </div>
            </div>

            <div class="mt-4 flex flex-wrap items-center gap-2">
                <button type="button" class="h-9 rounded-sm border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="printInvoice">
                    <i class="fas fa-print mr-2"></i>
                    Print Invoice
                </button>

                <button type="button" class="h-9 rounded-sm bg-sky-600 px-4 text-sm font-semibold text-white hover:bg-sky-700" :disabled="isGeneratingReport" @click="generateReport('excel')">
                    <i v-if="isGeneratingReport && reportType === 'excel'" class="fas fa-spinner fa-spin mr-2"></i>
                    <i v-else class="fas fa-file-excel mr-2"></i>
                    Head Excel
                </button>

                <button type="button" class="h-9 rounded-sm bg-rose-600 px-4 text-sm font-semibold text-white hover:bg-rose-700" :disabled="isGeneratingReport" @click="generateReport('pdf')">
                    <i v-if="isGeneratingReport && reportType === 'pdf'" class="fas fa-spinner fa-spin mr-2"></i>
                    <i v-else class="fas fa-file-pdf mr-2"></i>
                    Head PDF
                </button>

                <button
                    v-if="filters.exam_id && filters.examination_year && !uniqueTable.show"
                    type="button"
                    class="h-9 rounded-sm bg-emerald-600 px-4 text-sm font-semibold text-white hover:bg-emerald-700"
                    :disabled="uniqueTable.loading"
                    @click="searchUnique"
                >
                    <i v-if="uniqueTable.loading" class="fas fa-spinner fa-spin mr-2"></i>
                    <i v-else class="fas fa-object-group mr-2"></i>
                    Merge Invoices
                </button>

                <button
                    v-if="uniqueTable.show && filters.exam_id && filters.examination_year"
                    type="button"
                    class="h-9 rounded-sm bg-amber-500 px-4 text-sm font-semibold text-white hover:bg-amber-600"
                    @click="closeUniqueTable"
                >
                    <i class="fas fa-times mr-2"></i>
                    Close Merge
                </button>
            </div>
        </div>

        <div v-if="uniqueTable.show" class="border border-slate-300 bg-white">
            <div class="flex flex-col gap-2 border-b border-slate-300 p-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="text-sm font-semibold text-slate-900">Merge Student Invoice Summary</div>
                    <div class="mt-1 flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-700">
                        <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-emerald-700">{{ uniqueTotalStudents }} unique students</span>
                        <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-slate-700">Grand Total: {{ uniqueTotalAmount }}</span>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button
                        v-if="!uniqueTable.loading && uniqueTable.allDatas.length"
                        type="button"
                        class="h-9 rounded-sm border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                        @click="exportMergeExcel"
                    >
                        <i class="fas fa-file-excel mr-2"></i>
                        Export Excel
                    </button>
                </div>
            </div>

            <div class="p-4">
                <div v-if="uniqueTable.loading" class="py-10 text-center text-sm text-slate-600">Loading...</div>
                <div v-else class="overflow-x-auto">
                    <table class="min-w-[1100px] w-full border-collapse border border-slate-300 text-sm">
                        <thead class="bg-emerald-100">
                            <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-700">
                                <th class="border border-slate-300 px-1 py-2 whitespace-nowrap">#</th>
                                <th class="border border-slate-300 px-1 py-2 whitespace-nowrap">Student</th>
                                <th class="border border-slate-300 px-1 py-2 whitespace-nowrap text-center">Admission ID</th>
                                <th class="border border-slate-300 px-1 py-2 whitespace-nowrap text-center">College Roll</th>
                                <th class="border border-slate-300 px-1 py-2 whitespace-nowrap">Dept. / Group</th>
                                <th class="border border-slate-300 px-1 py-2 whitespace-nowrap">Academic Level / Class</th>
                                <th class="border border-slate-300 px-1 py-2 whitespace-nowrap">Purpose</th>
                                <th class="border border-slate-300 px-1 py-2 whitespace-nowrap">Invoice (Date / Payment Date)</th>
                                <th class="border border-slate-300 px-1 py-2 whitespace-nowrap text-center">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(r, idx) in uniquePagedDatas" :key="'uq-' + idx" :class="idx % 2 === 0 ? 'bg-white' : 'bg-slate-50'">
                                <td class="border border-slate-300 px-1 py-1 text-center">{{ uniqueRowSerial(idx) }}</td>
                                <td class="border border-slate-300 px-1 py-1">
                                    <div class="font-semibold">{{ r.software_id || '--' }}</div>
                                    <div class="text-xs text-slate-600">{{ r.student_name || '--' }}</div>
                                    <div class="text-xs text-slate-600">{{ r.mobile || '' }}</div>
                                </td>
                                <td class="border border-slate-300 px-1 py-1 text-center">{{ r.admission_id || '' }}</td>
                                <td class="border border-slate-300 px-1 py-1 text-center">{{ r.college_roll || '' }}</td>
                                <td class="border border-slate-300 px-1 py-1">{{ r.department || '' }}</td>
                                <td class="border border-slate-300 px-1 py-1">
                                    {{ r.qualification || '' }}
                                    <div class="text-xs text-slate-600">({{ r.academic_class || '' }})</div>
                                </td>
                                <td class="border border-slate-300 px-1 py-1">
                                    <span v-for="(p, i) in r.purposes" :key="'p-' + idx + '-' + i">{{ p }}<span v-if="i < r.purposes.length - 1">, </span></span>
                                </td>
                                <td class="border border-slate-300 px-1 py-1">
                                    <div>
                                        <span class="text-xs text-slate-600">Inv:&nbsp;</span>
                                        <span v-for="(d, i) in r.invoice_dates" :key="'inv-' + idx + '-' + i">{{ d }}<span v-if="i < r.invoice_dates.length - 1">, </span></span>
                                    </div>
                                    <div>
                                        <span class="text-xs text-slate-600">Pay:&nbsp;</span>
                                        <span v-for="(d, i) in r.payment_dates" :key="'pay-' + idx + '-' + i">{{ d }}<span v-if="i < r.payment_dates.length - 1">, </span></span>
                                    </div>
                                </td>
                                <td class="border border-slate-300 px-1 py-1 text-center font-semibold">{{ money(r.amount) }}</td>
                            </tr>

                            <tr v-if="!uniqueTable.allDatas.length" class="text-sm text-slate-600">
                                <td colspan="9" class="border border-slate-300 px-1 py-10 text-center">No data found</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="!uniqueTable.loading && uniqueTotalStudents > 0" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="text-sm text-slate-600">Showing {{ uniqueMeta.from }} to {{ uniqueMeta.to }} of {{ uniqueMeta.total }} entries</div>
                    <div class="flex items-center gap-2">
                        <button type="button" class="rounded-sm border border-slate-300 bg-white px-3 py-1.5 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="uniqueMeta.current_page <= 1" @click="setUniquePage(uniqueMeta.current_page - 1)">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <div class="text-sm font-semibold text-slate-700">{{ uniqueMeta.current_page }} / {{ uniqueMeta.last_page }}</div>
                        <button type="button" class="rounded-sm border border-slate-300 bg-white px-3 py-1.5 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="uniqueMeta.current_page >= uniqueMeta.last_page" @click="setUniquePage(uniqueMeta.current_page + 1)">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="border border-slate-300 bg-white">
            <div class="overflow-x-auto">
                <table class="min-w-[600px] w-full border-collapse border border-slate-300 text-sm">
                    <thead class="bg-emerald-100">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-700">
                            <th class="border border-slate-300 px-1 py-2 text-slate-700 whitespace-nowrap">#</th>
                            <th class="border border-slate-300 px-1 py-2 text-slate-700 whitespace-nowrap">Student</th>
                            <th class="border border-slate-300 px-1 py-2 text-slate-700 whitespace-nowrap text-center">Admission ID</th>
                            <th class="border border-slate-300 px-1 py-2 text-slate-700 whitespace-nowrap text-center">College Roll</th>
                            <th class="border border-slate-300 px-1 py-2 text-slate-700 whitespace-nowrap">Dept. / Group</th>
                            <th class="border border-slate-300 px-1 py-2 text-slate-700 whitespace-nowrap">Academic Level / Class</th>
                            <th class="border border-slate-300 px-1 py-2 text-slate-700 whitespace-nowrap">Purpose</th>
                            <th class="border border-slate-300 px-1 py-2 text-slate-700 whitespace-nowrap">Invoice</th>
                            <th class="border border-slate-300 px-1 py-2 text-center text-slate-700 whitespace-nowrap">Amount</th>
                            <th class="border border-slate-300 px-1 py-2 text-center text-slate-700 whitespace-nowrap">Status</th>
                            <th class="border border-slate-300 px-1 py-2 text-center text-slate-700 whitespace-nowrap">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        <tr v-for="(row, idx) in rows" :key="'r-' + row.id" :class="idx % 2 === 0 ? 'bg-white' : 'bg-slate-50'">
                            <td class="border border-slate-300 px-1 py-1 text-center">{{ idx + 1 }}</td>
                            <td class="border border-slate-300 px-1 py-1">
                                <div v-if="row.student">
                                    <div class="font-semibold text-slate-900 whitespace-nowrap">{{ row.student.name }}</div>
                                    <div class="text-xs text-slate-600">{{ row.student.mobile }}</div>
                                </div>
                                <div v-else-if="row.online_admission">
                                    <div class="font-semibold text-slate-900">{{ row.online_admission.name }}</div>
                                    <div class="text-xs text-slate-600">{{ row.online_admission.mobile }}</div>
                                </div>
                            </td>
                            <td class="border border-slate-300 px-1 py-1 text-center">
                                <span v-if="row.student">{{ row.student.admission_id }}</span>
                                <span v-else-if="row.online_admission">{{ row.online_admission.admission_roll }}</span>
                            </td>
                            <td class="border border-slate-300 px-1 py-1 text-center">
                                <span v-if="row.student">{{ row.student.college_roll || '' }}</span>
                                <span v-else-if="row.online_admission">{{ row.online_admission.college_roll || '' }}</span>
                            </td>
                            <td class="border border-slate-300 px-1 py-1">{{ row.department ? row.department.name : '' }}</td>
                            <td class="border border-slate-300 px-1 py-1">
                                <div>{{ row.qualification ? row.qualification.name : '' }}</div>
                                <div class="text-xs text-slate-600">({{ row.academic_class ? row.academic_class.name : '' }})</div>
                            </td>
                            <td class="border border-slate-300 px-1 py-1">{{ row.head ? row.head.name : '' }}</td>
                            <td class="border border-slate-300 px-1 py-1">
                                <div class="space-y-1">
                                    <div class="font-semibold text-emerald-700 hover:underline whitespace-nowrap">{{ row.invoice_number }}</div>
                                    <div class="font-semibold text-emerald-700 whitespace-nowrap">{{ formatDate(row.invoice_date) }}</div>
                                </div>
                            </td>
                            <td class="border border-slate-300 px-1 py-1 text-center font-semibold">{{ money(row.amount) }}</td>
                            <td class="border border-slate-300 px-1 py-1 text-center">
                                <span
                                    class="inline-flex rounded-full px-1 py-0.5 text-xs font-semibold"
                                    :class="row.status === 'success' ? 'bg-emerald-50 text-emerald-700' : row.status === 'pending' ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-rose-700'"
                                >
                                    {{ (row.status || '').toUpperCase() }}
                                </span>
                            </td>
                            <td class="border border-slate-300 px-1 py-1 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button
                                        type="button"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-sm border border-slate-300 bg-white text-slate-700 hover:bg-slate-50"
                                        title="Edit"
                                        @click="editInvoice(row.id)"
                                    >
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button
                                        v-if="row.status !== 'success'"
                                        type="button"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-sm border border-red-200 bg-white text-red-700 hover:bg-red-50"
                                        title="Delete"
                                        @click="deleteInvoice(row.id)"
                                    >
                                        <i v-if="deletingId === row.id" class="fas fa-spinner fa-spin"></i>
                                        <i v-else class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!rows.length">
                            <td colspan="11" class="px-3 py-10 text-center text-slate-500">No Data Found</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="meta.total > 0" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="text-sm text-slate-600">Showing {{ meta.from }} to {{ meta.to }} of {{ meta.total }} entries</div>

                <div class="flex items-center gap-2">
                    <button type="button" class="rounded-sm border border-slate-300 bg-white px-3 py-1.5 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="meta.current_page <= 1" @click="load(meta.current_page - 1)">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <div class="text-sm font-semibold text-slate-700">{{ meta.current_page }} / {{ meta.last_page }}</div>
                    <button type="button" class="rounded-sm border border-slate-300 bg-white px-3 py-1.5 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="meta.current_page >= meta.last_page" @click="load(meta.current_page + 1)">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <div v-if="showEditModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/30">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-2xl rounded-sm bg-white p-6 shadow-xl">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-slate-900">Edit Invoice</h3>
                        <button type="button" class="text-slate-400 hover:text-slate-600" @click="showEditModal = false">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="md:col-span-2 border-b pb-3">
                            <div class="text-sm text-slate-600">
                                <div>Invoice No: <span class="font-semibold text-slate-900">{{ editInvoiceData.invoice_number }}</span></div>
                                <div>Current Status: <span class="font-semibold text-slate-900">{{ (editInvoiceData.current_status || '').toUpperCase() }}</span></div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Amount</label>
                            <input v-model="editInvoiceData.amount" type="number" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Account Head</label>
                            <select v-model="editInvoiceData.account_head_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                <option value="">Select Account Head</option>
                                <option v-for="head in accountHeads" :key="head.id" :value="head.id">{{ head.name }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Session</label>
                            <select v-model="editInvoiceData.academic_session_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                <option value="">Select Session</option>
                                <option v-for="session in sessionsSorted" :key="session.id" :value="session.id">{{ session.name }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Academic Level</label>
                            <select v-model="editInvoiceData.academic_qualification_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                <option value="">Select Academic Level</option>
                                <option v-for="qual in qualifications" :key="qual.id" :value="qual.id">{{ qual.name }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Department/Group</label>
                            <select v-model="editInvoiceData.department_id" :disabled="!editInvoiceData.academic_qualification_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                <option value="">Select Department</option>
                                <option v-for="dept in filteredEditDepartments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Class</label>
                            <select v-model="editInvoiceData.academic_class_id" :disabled="!editInvoiceData.academic_qualification_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                <option value="">Select Class</option>
                                <option v-for="cls in filteredEditClasses" :key="cls.id" :value="cls.id">{{ cls.name }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Exam</label>
                            <select v-model="editInvoiceData.exam_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                <option value="">Select Exam</option>
                                <option v-for="exam in exams" :key="exam.id" :value="exam.id">{{ exam.name }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Exam Year</label>
                            <select v-model="editInvoiceData.examination_year" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                <option value="">--Select Exam Year--</option>
                                <option v-for="y in years" :key="'ey-' + y" :value="y">{{ y }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Admission Roll</label>
                            <input v-model="editInvoiceData.admission_roll" type="number" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">College Roll</label>
                            <input v-model="editInvoiceData.college_roll" type="number" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Reg No</label>
                            <input v-model="editInvoiceData.reg_no" type="number" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Refund Amount</label>
                            <div class="mt-1 flex items-center">
                                <input v-model="refunded" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" />
                                <label class="ml-2 text-sm text-slate-700">Are you sure?</label>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Payment Date</label>
                            <input v-model="editInvoiceData.payment_date" type="date" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>

                        <div v-if="refunded">
                            <label class="block text-sm font-medium text-slate-700">Refund Amount</label>
                            <input v-model="editInvoiceData.refund_amount" type="number" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" placeholder="Refund Amount" @input="calculateRefundAmount" />
                        </div>

                        <div v-if="refunded">
                            <label class="block text-sm font-medium text-slate-700">Refund Date</label>
                            <input v-model="editInvoiceData.refund_date" type="date" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>

                        <div v-if="refunded" class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700">Refund Note</label>
                            <textarea v-model="editInvoiceData.refund_note" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 py-2 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" rows="3" placeholder="Enter refund note..."></textarea>
                        </div>
                    </div>

                    <div class="mt-6 grid grid-cols-1 gap-3 md:grid-cols-2">
                        <button type="button" class="rounded-sm border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="saving" @click="updateInfoOnly">
                            <i v-if="saving" class="fas fa-spinner fa-spin mr-2"></i>
                            <i v-else class="fas fa-edit mr-2"></i>
                            {{ saving ? 'Processing...' : 'Update Info Only' }}
                        </button>
                        <button type="button" class="rounded-sm bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700" :disabled="saving" @click="paidAndUpdate">
                            <i v-if="saving" class="fas fa-spinner fa-spin mr-2"></i>
                            <i v-else class="fas fa-save mr-2"></i>
                            {{ saving ? 'Processing...' : 'Paid & Update Info' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'InvoiceIndex',
    props: {
        systems: {
            type: Object,
            default: () => ({}),
        },
        mode: {
            type: String,
            default: '',
        },
    },
    data() {
        return {
            loading: false,
            error: '',
            rows: [],
            meta: { from: 0, to: 0, total: 0, current_page: 1, last_page: 1 },
            deletingId: null,
            showEditModal: false,
            saving: false,
            refunded: false,
            totalAmount: 0,
            searchTimer: null,
            examLists: [],

            uniqueTable: {
                show: false,
                loading: false,
                allDatas: [],
                currentPage: 1,
                totalRawCount: 0,
            },

            isGeneratingReport: false,
            reportType: null,
            editInvoiceData: {
                id: null,
                amount: '',
                status: '',
                current_status: '',
                invoice_number: '',
                account_head_id: '',
                academic_session_id: '',
                department_id: '',
                academic_qualification_id: '',
                academic_class_id: '',
                exam_id: '',
                examination_year: '',
                payment_date: '',
                refund_amount: '',
                refund_date: '',
                refund_note: '',
                admission_roll: '',
                college_roll: '',
                reg_no: '',
            },
            filters: {
                pagination: 15,
                status: 'success',
                date: false,
                academic_session_id: '',
                academic_qualification_id: '',
                department_id: '',
                academic_class_id: '',
                student_type: '',
                from_date: '',
                to_date: '',
                exam_id: '',
                examination_year: '',
                field_name: 'invoice_number',
                value: '',
                account_head_id: '',
                payment_gateway_id: '',
            },
        }
    },
    computed: {
        pageHeading() {
            if (this.mode === 'accountWise') return 'Account Wise Payment Report'
            if (this.mode === 'accountHeadWise') return 'Account Head Wise Payment Report'
            return 'Student Payment Report'
        },
        sessions() {
            const list = this.systems?.global?.academic_sessions
            return Array.isArray(list) ? list : []
        },
        sessionsSorted() {
            const list = [...this.sessions]
            const sortKey = (s) => {
                const name = String(s?.name || '').trim()
                const m = name.match(/(19|20)\d{2}/)
                return m ? Number(m[0]) : 0
            }
            return list.sort((a, b) => sortKey(b) - sortKey(a))
        },
        qualifications() {
            const list = this.systems?.global?.academic_qualifications
            return Array.isArray(list) ? list : []
        },
        classes() {
            const list = this.systems?.global?.academic_classes
            return Array.isArray(list) ? list : []
        },
        departments() {
            const list = this.systems?.global?.departments
            return Array.isArray(list) ? list : []
        },
        departmentQualifications() {
            const list = this.systems?.global?.department_qualidactions || this.systems?.global?.department_qualifications
            return Array.isArray(list) ? list : []
        },
        filteredDepartments() {
            const qid = this.filters?.academic_qualification_id
            if (!qid) return []
            const allowed = new Set(
                this.departmentQualifications
                    .filter((r) => String(r.academic_qualification_id) === String(qid))
                    .map((r) => String(r.department_id)),
            )
            return this.departments.filter((d) => allowed.has(String(d.id)))
        },
        filteredClasses() {
            const qid = this.filters?.academic_qualification_id
            if (!qid) return []
            return this.classes.filter((c) => String(c.academic_qualification_id) === String(qid))
        },
        exams() {
            if (Array.isArray(this.examLists) && this.examLists.length) return this.examLists
            const list = this.systems?.global?.exams
            return Array.isArray(list) ? list : []
        },
        years() {
            const now = new Date().getFullYear()
            const out = []
            for (let y = now; y >= now - 30; y--) out.push(String(y))
            return out
        },
        studentTypes() {
            const list = this.systems?.global?.student_types
            return Array.isArray(list) ? list : []
        },
        accountHeads() {
            const list = this.systems?.global?.account_heads
            if (list && typeof list === 'object' && !Array.isArray(list)) {
                return Object.keys(list).map((id) => ({ id, name: list[id] }))
            }
            return Array.isArray(list) ? list : []
        },
        accountsCommons() {
            const list = this.systems?.global?.accounts_commons
            return Array.isArray(list) ? list : []
        },
        filteredEditDepartments() {
            const qid = this.editInvoiceData?.academic_qualification_id
            if (!qid) return []
            const allowed = new Set(
                this.departmentQualifications
                    .filter((r) => String(r.academic_qualification_id) === String(qid))
                    .map((r) => String(r.department_id)),
            )
            return this.departments.filter((d) => allowed.has(String(d.id)))
        },
        filteredEditClasses() {
            const qid = this.editInvoiceData?.academic_qualification_id
            if (!qid) return []
            return this.classes.filter((c) => String(c.academic_qualification_id) === String(qid))
        },

        uniqueTotalStudents() {
            return Array.isArray(this.uniqueTable.allDatas) ? this.uniqueTable.allDatas.length : 0
        },
        uniqueTotalPages() {
            const perPage = parseInt(this.filters.pagination) || 10
            return Math.max(1, Math.ceil(this.uniqueTotalStudents / perPage))
        },
        uniqueMeta() {
            const perPage = parseInt(this.filters.pagination) || 10
            const current = this.uniqueTable.currentPage
            const lastPage = this.uniqueTotalPages
            const from = this.uniqueTotalStudents === 0 ? 0 : (current - 1) * perPage + 1
            const to = Math.min(current * perPage, this.uniqueTotalStudents)
            const total = this.uniqueTable.totalRawCount > 0 ? this.uniqueTable.totalRawCount : this.uniqueTotalStudents
            return { current_page: current, last_page: lastPage, total, from, to }
        },
        uniquePagedDatas() {
            const perPage = parseInt(this.filters.pagination) || 10
            const start = (this.uniqueTable.currentPage - 1) * perPage
            return this.uniqueTable.allDatas.slice(start, start + perPage)
        },
        uniqueTotalAmount() {
            if (!Array.isArray(this.uniqueTable.allDatas) || this.uniqueTable.allDatas.length === 0) return '0.00'
            const sum = this.uniqueTable.allDatas.reduce((s, r) => s + parseFloat(r.amount || 0), 0)
            return sum.toFixed(2)
        },
        headWiseReportHeader() {
            let deptName = ''
            let qualName = ''
            if (this.filters.department_id) {
                const d = this.departments.find((x) => String(x.id) === String(this.filters.department_id))
                if (d) deptName = d.name
            }
            if (this.filters.academic_qualification_id) {
                const q = this.qualifications.find((x) => String(x.id) === String(this.filters.academic_qualification_id))
                if (q) qualName = q.name
            }
            if (deptName || qualName) return [deptName, qualName].filter(Boolean).join(' - ')
            return 'All Items'
        },
    },
    watch: {
        'filters.exam_id'(next, prev) {
            if (String(next || '') !== String(prev || '')) {
                this.filters.examination_year = ''
                if (this.uniqueTable.show) this.closeUniqueTable()
            }
        },
        'editInvoiceData.exam_id'(next, prev) {
            if (String(next || '') !== String(prev || '')) {
                this.editInvoiceData.examination_year = ''
            }
        },
        'filters.academic_qualification_id'(next, prev) {
            if (String(next || '') !== String(prev || '')) {
                this.filters.department_id = ''
                this.filters.academic_class_id = ''
                this.scheduleLoad(true)
            }
        },
        'editInvoiceData.academic_qualification_id'(next, prev) {
            if (String(next || '') !== String(prev || '')) {
                this.editInvoiceData.department_id = ''
                this.editInvoiceData.academic_class_id = ''
            }
        },
        filters: {
            handler() {
                this.scheduleLoad(true)
            },
            deep: true,
        },
    },
    created() {
        this.loadExamsOthers()
        this.load(1)
    },
    methods: {
        async loadExamsOthers() {
            try {
                const res = await window.axios.get('/admin/get-exam', {
                    params: { allData: true, exam_type: 'others' },
                })
                const data = res?.data
                this.examLists = Array.isArray(data) ? data : []
            } catch (e) {
                this.examLists = []
            }
        },
        scheduleLoad(resetPage) {
            if (this.searchTimer) clearTimeout(this.searchTimer)
            this.searchTimer = setTimeout(() => {
                const page = resetPage ? 1 : Number(this.meta?.current_page || 1)
                this.load(page)
            }, 250)
        },
        money(v) {
            const n = Number(v || 0)
            const val = Number.isFinite(n) ? n : 0
            return val.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
        },
        goDashboard() {
            window.history.pushState({}, '', '/admin/dashboard')
            window.dispatchEvent(new PopStateEvent('popstate'))
        },
        goCreate() {
            window.history.pushState({}, '', '/admin/invoice/create')
            window.dispatchEvent(new PopStateEvent('popstate'))
        },
        openInvoice(id) {
            window.history.pushState({}, '', `/admin/invoice/${id}`)
            window.dispatchEvent(new PopStateEvent('popstate'))
        },
        buildPrintQuery() {
            const q = { ...this.filters }
            delete q.pagination
            q.page = undefined
            const params = new URLSearchParams()
            Object.keys(q).forEach((k) => {
                const v = q[k]
                if (v === '' || v == null) return
                params.set(k, String(v))
            })
            return params.toString()
        },
        printInvoice() {
            const qs = this.buildPrintQuery()
            const url = qs ? `/admin/invoice-print?${qs}` : '/admin/invoice-print'
            window.location.href = url
        },
        uniqueRowSerial(idx) {
            const perPage = parseInt(this.filters.pagination) || 10
            return (this.uniqueTable.currentPage - 1) * perPage + idx + 1
        },
        setUniquePage(page) {
            const p = Number(page || 1)
            if (p >= 1 && p <= this.uniqueTotalPages) this.uniqueTable.currentPage = p
        },
        closeUniqueTable() {
            this.uniqueTable.show = false
            this.uniqueTable.loading = false
            this.uniqueTable.allDatas = []
            this.uniqueTable.currentPage = 1
            this.uniqueTable.totalRawCount = 0
        },
        buildUniqueRows(items) {
            const map = {}
            ;(items || []).forEach((item) => {
                const purposeName = item.head ? (item.head.name || item.head.name_en || 'Unknown') : 'Unknown'
                const paymentGatewayId = item.payment_gateway_id || 'unknown_gateway'
                const student = item.student
                const studentKey = student
                    ? (student.student_id || student.id || item.admission_id || 'unknown')
                    : (item.admission_id || item.id || 'unknown')
                const key = String(studentKey) + '_' + String(purposeName) + '_' + String(paymentGatewayId)

                if (!map[key]) {
                    map[key] = {
                        software_id: student ? (student.student_id || '') : '',
                        admission_id: item.admission_id || (item.online_admission ? item.online_admission.admission_roll : ''),
                        college_roll: student ? (student.college_roll || item.college_roll || '') : (item.college_roll || ''),
                        reg_no: student ? (student.reg_no || item.reg_no || '') : (item.reg_no || ''),
                        student_name: student ? student.name : (item.online_admission ? item.online_admission.name : ''),
                        gender: student ? (student.gender || '') : '',
                        religion: student ? (student.religion || '') : '',
                        mobile: student ? (student.mobile || '') : (item.online_admission ? item.online_admission.mobile : ''),
                        department: item.department ? item.department.name : '',
                        qualification: item.qualification ? item.qualification.name : '',
                        academic_class: item.academic_class ? item.academic_class.name : '',
                        purposes: [],
                        invoice_dates: [],
                        payment_dates: [],
                        amount: 0,
                    }
                }

                const row = map[key]
                if (purposeName && !row.purposes.includes(purposeName)) row.purposes.push(purposeName)

                const invDate = item.invoice_date || ''
                if (invDate && !row.invoice_dates.includes(invDate)) row.invoice_dates.push(invDate)

                const payDate = item.payment_date || ''
                if (payDate && !row.payment_dates.includes(payDate)) row.payment_dates.push(payDate)

                row.amount += parseFloat(item.amount || 0)
            })

            return Object.values(map).map((row) => ({
                ...row,
                amount: parseFloat((row.amount || 0).toFixed(2)),
            }))
        },
        async searchUnique() {
            if (!this.filters.exam_id) {
                this.error = 'Please select an Exam first.'
                return
            }
            if (!this.filters.examination_year) {
                this.error = 'Please select an Examination Year.'
                return
            }

            this.uniqueTable.show = true
            this.uniqueTable.loading = true
            this.uniqueTable.allDatas = []
            this.uniqueTable.currentPage = 1
            this.uniqueTable.totalRawCount = 0

            const FETCH_PER_PAGE = 500
            const baseParams = { ...this.filters, pagination: FETCH_PER_PAGE }

            const fetchAllPages = async (page, collected, backendTotal) => {
                const res = await window.axios.get('/admin/invoice', { params: { ...baseParams, page } })
                const data = res?.data || {}
                const pageData = Array.isArray(data.data) ? data.data : []
                const lastPage = data.last_page || 1
                const totalCount = backendTotal != null ? backendTotal : (data.total || null)
                const allSoFar = collected.concat(pageData)
                if (page < lastPage) {
                    return fetchAllPages(page + 1, allSoFar, totalCount)
                }
                return { items: allSoFar, totalRaw: totalCount != null ? totalCount : allSoFar.length }
            }

            try {
                const result = await fetchAllPages(1, [], null)
                this.uniqueTable.totalRawCount = result.totalRaw
                this.uniqueTable.allDatas = this.buildUniqueRows(result.items)
                this.uniqueTable.loading = false
            } catch (e) {
                this.closeUniqueTable()
                this.error = 'Failed to fetch invoices for merge.'
            }
        },
        exportMergeExcel() {
            const rows = Array.isArray(this.uniqueTable.allDatas) ? this.uniqueTable.allDatas : []
            if (!rows.length) return

            let html = '<html><head><meta charset="UTF-8"></head><body>'
            html += '<table border="1" style="border-collapse:collapse;">'
            html += '<thead><tr>'
            html += '<th>Software ID</th><th>Admission ID</th><th>College Roll</th><th>Reg No</th><th>Student Name</th><th>Mobile</th><th>Department</th><th>Academic Level</th><th>Academic Class</th><th>Purpose</th><th>Invoice Date</th><th>Payment Date</th><th>Amount</th>'
            html += '</tr></thead><tbody>'

            rows.forEach((r) => {
                const purposes = Array.isArray(r.purposes) ? r.purposes.join(', ') : (r.purposes || '')
                const invDates = Array.isArray(r.invoice_dates) ? r.invoice_dates.join(', ') : (r.invoice_dates || '')
                const payDates = Array.isArray(r.payment_dates) ? r.payment_dates.join(', ') : (r.payment_dates || '')
                html += '<tr>'
                html += `<td>${r.software_id || ''}</td>`
                html += `<td>${r.admission_id || ''}</td>`
                html += `<td>${r.college_roll || ''}</td>`
                html += `<td>${r.reg_no || ''}</td>`
                html += `<td>${r.student_name || ''}</td>`
                html += `<td>${r.mobile || ''}</td>`
                html += `<td>${r.department || ''}</td>`
                html += `<td>${r.qualification || ''}</td>`
                html += `<td>${r.academic_class || ''}</td>`
                html += `<td>${purposes}</td>`
                html += `<td>${invDates}</td>`
                html += `<td>${payDates}</td>`
                html += `<td>${parseFloat(r.amount || 0).toFixed(2)}</td>`
                html += '</tr>'
            })

            html += '</tbody></table></body></html>'

            const blob = new Blob([html], { type: 'application/vnd.ms-excel' })
            const url = URL.createObjectURL(blob)
            const a = document.createElement('a')
            a.href = url
            a.download = 'merge-invoices.xls'
            document.body.appendChild(a)
            a.click()
            document.body.removeChild(a)
            URL.revokeObjectURL(url)
        },
        getHeadWiseReportHTML(uniqueDatas, forPdf = false) {
            const heads = {}
            let totalCount = 0
            let totalSum = 0

            ;(uniqueDatas || []).forEach((row) => {
                const headName = (row.purposes && row.purposes.length > 0) ? row.purposes[0] : 'Unknown'
                const amt = parseFloat(row.amount || 0)
                if (!heads[headName]) heads[headName] = { count: 0, sum: 0, amounts: {} }
                heads[headName].count += 1
                heads[headName].sum += amt
                if (!heads[headName].amounts[amt]) heads[headName].amounts[amt] = { count: 0, sum: 0 }
                heads[headName].amounts[amt].count += 1
                heads[headName].amounts[amt].sum += amt
                totalCount += 1
                totalSum += amt
            })

            let html = `
            <html ${forPdf ? '' : 'xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"'}>
            <head>
            <meta http-equiv="content-type" content="text/html; charset=UTF-8">
            <style>
                @page { size: A4 portrait; margin: 15mm; }
                body { font-family: Arial, sans-serif; }
                table { border-collapse: collapse; width: 100%; }
                td, th { border: 1px solid black; padding: 5px; }
            </style>
            </head>
            <body>
                <table border="1" style="border-collapse: collapse; width: 100%;">
                    <thead>
                        <tr>
                            <th colspan="3" style="text-align: center; font-size: 16px; font-weight: bold; padding: 10px; border: 1px solid black;">
                                ${this.headWiseReportHeader}
                            </th>
                        </tr>
                        <tr>
                            <th style="border: 1px solid black;">Row Labels</th>
                            <th style="border: 1px solid black;">Count of Invoice Date</th>
                            <th style="border: 1px solid black;">Sum of Amount</th>
                        </tr>
                    </thead>
                    <tbody>`

            Object.keys(heads).forEach((head) => {
                html += `
                    <tr>
                        <td colspan="3" style="text-align: center; font-weight: bold; background-color: #f2f2f2; border: 1px solid black;">${head}</td>
                    </tr>`
                Object.keys(heads[head].amounts).forEach((amt) => {
                    const row = heads[head].amounts[amt]
                    html += `
                    <tr>
                        <td style="border: 1px solid black; text-align: right;">${amt}</td>
                        <td style="border: 1px solid black; text-align: right;">${row.count}</td>
                        <td style="border: 1px solid black; text-align: right;">${row.sum.toFixed(2)}</td>
                    </tr>`
                })
                html += `
                    <tr>
                        <td style="font-weight: bold; text-align: right; border: 1px solid black;">Total</td>
                        <td style="font-weight: bold; text-align: right; border: 1px solid black;">${heads[head].count}</td>
                        <td style="font-weight: bold; text-align: right; border: 1px solid black;">${heads[head].sum.toFixed(2)}</td>
                    </tr>`
            })

            html += `
                    <tr>
                        <td style="font-weight: bold; text-align: right; border: 1px solid black;">Grand Total</td>
                        <td style="font-weight: bold; text-align: right; border: 1px solid black;">${totalCount}</td>
                        <td style="font-weight: bold; text-align: right; border: 1px solid black;">${totalSum.toFixed(2)}</td>
                    </tr>
                </tbody>
            </table>
            </body>
            </html>`

            return html
        },
        async generateReport(type) {
            this.isGeneratingReport = true
            this.reportType = type

            let printWindow = null
            if (type === 'pdf') {
                printWindow = window.open('', '_blank')
                if (!printWindow) {
                    this.error = 'Please allow pop-ups for this website to generate PDF.'
                    this.isGeneratingReport = false
                    this.reportType = null
                    return
                }
                printWindow.document.write("<h3 style='font-family:sans-serif; text-align:center; padding-top:20px;'>Loading report data, please wait...</h3>")
            }

            const FETCH_PER_PAGE = 500
            const baseParams = { ...this.filters, pagination: FETCH_PER_PAGE }

            const fetchAllPages = async (page, collected) => {
                const res = await window.axios.get('/admin/invoice', { params: { ...baseParams, page } })
                const data = res?.data || {}
                const pageData = Array.isArray(data.data) ? data.data : []
                const lastPage = data.last_page || 1
                const allSoFar = collected.concat(pageData)
                if (page < lastPage) return fetchAllPages(page + 1, allSoFar)
                return allSoFar
            }

            try {
                const allItems = await fetchAllPages(1, [])
                const uniqueDatas = this.buildUniqueRows(allItems)
                const html = this.getHeadWiseReportHTML(uniqueDatas, type === 'pdf')

                if (type === 'excel') {
                    const blob = new Blob([html], { type: 'application/vnd.ms-excel' })
                    const url = URL.createObjectURL(blob)
                    const a = document.createElement('a')
                    a.href = url
                    a.download = 'head-wise-report.xls'
                    document.body.appendChild(a)
                    a.click()
                    document.body.removeChild(a)
                    URL.revokeObjectURL(url)
                } else if (type === 'pdf') {
                    if (printWindow) {
                        printWindow.document.open()
                        printWindow.document.write(html)
                        printWindow.document.close()
                        printWindow.focus()
                        setTimeout(() => {
                            printWindow.print()
                            printWindow.close()
                        }, 500)
                    }
                }
            } catch (e) {
                this.error = 'Failed to fetch data for report.'
                if (printWindow) printWindow.close()
            } finally {
                this.isGeneratingReport = false
                this.reportType = null
            }
        },
        async load(page = 1) {
            this.loading = true
            this.error = ''
            try {
                const res = await window.axios.get('/admin/invoice', {
                    params: { ...this.filters, page },
                })
                const data = res?.data || {}
                this.rows = Array.isArray(data.data) ? data.data : []
                this.meta = {
                    from: data.from || 0,
                    to: data.to || 0,
                    total: data.total || 0,
                    current_page: data.current_page || 1,
                    last_page: data.last_page || 1,
                }
            } catch (e) {
                this.rows = []
                this.meta = { from: 0, to: 0, total: 0, current_page: 1, last_page: 1 }
                this.error = e?.response?.data?.message || 'Failed to load invoices.'
            } finally {
                this.loading = false
            }
        },
        editInvoice(id) {
            const invoice = this.rows.find(row => row.id === id)
            if (!invoice) return

            const today = new Date().toISOString().slice(0, 10)
            
            // Calculate total amount (original amount + refund amount)
            this.totalAmount = parseFloat(invoice.amount || 0) + parseFloat(invoice.refund_amount || 0)
            
            // Set refund checkbox state
            this.refunded = invoice.refund_amount ? true : false
            
            this.editInvoiceData = {
                id: invoice.id,
                amount: invoice.amount || '',
                status: invoice.status || '',
                current_status: invoice.status || '',
                invoice_number: invoice.invoice_number || '',
                account_head_id: invoice.account_head_id || invoice.head?.id || '',
                academic_session_id: invoice.academic_session_id || invoice.academic_session?.id || '',
                department_id: invoice.department_id || invoice.department?.id || '',
                academic_qualification_id: invoice.academic_qualification_id || invoice.qualification?.id || '',
                academic_class_id: invoice.academic_class_id || invoice.academic_class?.id || '',
                exam_id: invoice.exam_id || '',
                examination_year: invoice.examination_year || '',
                payment_date: invoice.payment_date || today,
                refund_amount: invoice.refund_amount || '',
                refund_date: invoice.refund_date || today,
                refund_note: invoice.refund_note || '',
                admission_roll: invoice.admission_id || '',
                college_roll: invoice.college_roll || '',
                reg_no: invoice.reg_no || '',
            }

            this.showEditModal = true
        },
        calculateRefundAmount() {
            // Calculate amount after refund
            const refundAmount = parseFloat(this.editInvoiceData.refund_amount || 0)
            this.editInvoiceData.amount = parseFloat(this.totalAmount) - refundAmount
        },
        async updateInfoOnly() {
            if (!confirm("Are you sure you want to update information without changing payment status?")) {
                return
            }
            
            this.saving = true
            try {
                const payload = { ...this.editInvoiceData, update_type: 'info_only' }
                await window.axios.put(`/admin/invoice/${this.editInvoiceData.id}`, payload)
                this.showEditModal = false
                this.load(this.meta.current_page)
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to update invoice.'
            } finally {
                this.saving = false
            }
        },
        async paidAndUpdate() {
            if (!confirm("Are you sure you want to mark as paid and update information?")) {
                return
            }
            
            this.saving = true
            try {
                const payload = { ...this.editInvoiceData, status: 'success', update_type: 'paid_and_update' }
                await window.axios.put(`/admin/invoice/${this.editInvoiceData.id}`, payload)
                this.showEditModal = false
                this.load(this.meta.current_page)
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to update invoice.'
            } finally {
                this.saving = false
            }
        },
        formatDate(dateString) {
            if (!dateString) return ''
            
            const date = new Date(dateString)
            const options = { year: 'numeric', month: 'short', day: 'numeric' }
            return date.toLocaleDateString('en-US', options)
        },
        async deleteInvoice(id) {
            if (!confirm('Are you sure you want to delete this invoice?')) {
                return
            }
            
            this.deletingId = id
            try {
                await window.axios.delete(`/admin/invoice/${id}`)
                this.load(this.meta.current_page)
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to delete invoice.'
            } finally {
                this.deletingId = null
            }
        },
    },
}
</script>
