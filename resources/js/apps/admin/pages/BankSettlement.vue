<template>
    <div class="flex flex-col gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">SSLCommerz Settlement Records</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800" :disabled="loading || !apiResponse" @click="generatePdf">Download PDF</button>
                    <button type="button" class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700" :disabled="loading || !apiResponse" @click="exportExcel">Export as Excel</button>
                </div>
            </div>

            <form class="mt-4 grid grid-cols-1 gap-3 lg:grid-cols-12" @submit.prevent="search">
                <div class="lg:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Account No.</div>
                    <select v-model="search_data.account" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">Select Account No.</option>
                        <option v-for="acc in accounts" :key="'acc-' + acc.account_no" :value="String(acc.account_no)">
                            {{ acc.account_no }}
                        </option>
                    </select>
                </div>

                <div class="lg:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Start Date</div>
                    <input v-model="search_data.start_date" type="datetime-local" step="1" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                </div>

                <div class="lg:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">End Date</div>
                    <input v-model="search_data.end_date" type="datetime-local" step="1" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                </div>

                <div class="lg:col-span-1">
                    <div class="text-xs font-semibold text-slate-600">Page</div>
                    <input v-model.number="search_data.page" type="number" min="1" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                </div>

                <div class="lg:col-span-1">
                    <div class="text-xs font-semibold text-slate-600">Limit</div>
                    <input v-model.number="search_data.limit" type="number" min="1" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                </div>

                <div class="lg:col-span-1 flex items-end">
                    <button type="submit" class="mt-1 h-9 w-full rounded-lg bg-emerald-600 px-4 text-sm font-semibold text-white hover:bg-emerald-700" :disabled="loading">
                        {{ loading ? '...' : 'Search' }}
                    </button>
                </div>
            </form>

            <div v-if="error" class="mt-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-800">{{ error }}</div>

            <div v-if="apiResponse" class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm text-slate-700">
                <div class="grid grid-cols-1 gap-2 md:grid-cols-4">
                    <div><span class="font-semibold">Status:</span> {{ apiResponse.status }}</div>
                    <div><span class="font-semibold">Transactions:</span> {{ apiResponse?.data?.no_of_trans }}</div>
                    <div><span class="font-semibold">Pages:</span> {{ apiResponse?.data?.total_pages }}</div>
                    <div><span class="font-semibold">Time Zone:</span> {{ apiResponse?.data?.time_zone }}</div>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div v-if="!apiResponse && !loading" class="py-10 text-center text-sm text-slate-600">Use the form above to search for settlement records.</div>
            <div v-else-if="apiResponse && !combinedData.length" class="py-10 text-center text-sm text-slate-600">No settlement data found for the selected criteria.</div>

            <div v-if="combinedData.length" class="overflow-x-auto rounded-xl border border-slate-200">
                <table id="pdf-table" class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-3 py-2 text-center font-semibold text-slate-700">Student Name</th>
                            <th class="px-3 py-2 text-center font-semibold text-slate-700">Mobile</th>
                            <th class="px-3 py-2 text-center font-semibold text-slate-700">Level</th>
                            <th class="px-3 py-2 text-center font-semibold text-slate-700">Dept</th>
                            <th class="px-3 py-2 text-center font-semibold text-slate-700">Class</th>
                            <th class="px-3 py-2 text-center font-semibold text-slate-700">Add ID</th>
                            <th class="px-3 py-2 text-center font-semibold text-slate-700">Roll</th>
                            <th class="px-3 py-2 text-center font-semibold text-slate-700">Reg No</th>
                            <th class="px-3 py-2 text-center font-semibold text-slate-700">Purpose</th>
                            <th class="px-3 py-2 text-center font-semibold text-slate-700">Sett Date</th>
                            <th class="px-3 py-2 text-center font-semibold text-slate-700">Trans Date</th>
                            <th class="px-3 py-2 text-center font-semibold text-slate-700">Trans ID</th>
                            <th class="px-3 py-2 text-right font-semibold text-slate-700">BDT</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        <tr v-for="(row, idx) in combinedData" :key="'r-' + idx">
                            <td class="px-3 py-2 text-center">{{ safeStudent(row)?.name || 'N/A' }}</td>
                            <td class="px-3 py-2 text-center">{{ safeStudent(row)?.mobile || 'N/A' }}</td>
                            <td class="px-3 py-2 text-center">{{ row?.invoice?.qualification?.name || 'N/A' }}</td>
                            <td class="px-3 py-2 text-center">{{ row?.invoice?.department?.name || 'N/A' }}</td>
                            <td class="px-3 py-2 text-center">{{ row?.invoice?.academic_class?.name || 'N/A' }}</td>
                            <td class="px-3 py-2 text-center">{{ admissionId(row) }}</td>
                            <td class="px-3 py-2 text-center">{{ safeStudent(row)?.college_roll || 'N/A' }}</td>
                            <td class="px-3 py-2 text-center">{{ safeStudent(row)?.reg_no || 'N/A' }}</td>
                            <td class="px-3 py-2 text-center">{{ row?.invoice?.head?.name_en || 'N/A' }}</td>
                            <td class="px-3 py-2 text-center">{{ formatDate(row?.settlement?.sett_date) }}</td>
                            <td class="px-3 py-2 text-center">{{ formatDate(row?.settlement?.tran_date) }}</td>
                            <td class="px-3 py-2 text-center">{{ row?.settlement?.transactionID || 'N/A' }}</td>
                            <td class="px-3 py-2 text-right font-semibold">{{ money(row?.settlement?.cr_amount) }}</td>
                        </tr>
                    </tbody>
                    <tfoot class="bg-slate-50">
                        <tr>
                            <td colspan="12" class="px-3 py-2 text-right font-semibold text-slate-700">Total</td>
                            <td class="px-3 py-2 text-right font-semibold text-slate-900">{{ money(totalAmount) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div v-if="apiResponse && totalPages > 1" class="mt-4 flex items-center justify-center gap-2">
                <button type="button" class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="search_data.page <= 1" @click="changePage(search_data.page - 1)">
                    Previous
                </button>
                <div class="text-sm font-semibold text-slate-700">{{ search_data.page }} / {{ totalPages }}</div>
                <button type="button" class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="search_data.page >= totalPages" @click="changePage(search_data.page + 1)">
                    Next
                </button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'BankSettlement',
    data() {
        return {
            loading: false,
            error: '',
            accounts: [],
            apiResponse: null,
            search_data: {
                account: '',
                start_date: this.defaultStart(),
                end_date: this.defaultEnd(),
                page: 1,
                limit: 10,
            },
        }
    },
    computed: {
        combinedData() {
            const details = this.apiResponse?.details
            if (!Array.isArray(details)) return []
            return details.map((item) => {
                return {
                    settlement: item?.settlement || {},
                    invoice: item?.invoice || {},
                }
            })
        },
        totalPages() {
            return Number(this.apiResponse?.data?.total_pages || this.apiResponse?.pagination?.total_pages || 1)
        },
        totalAmount() {
            return this.combinedData.reduce((sum, r) => {
                const n = parseFloat(r?.settlement?.cr_amount || 0)
                return sum + (Number.isFinite(n) ? n : 0)
            }, 0)
        },
    },
    mounted() {
        this.allAccount()
    },
    methods: {
        defaultStart() {
            const d = new Date()
            d.setDate(d.getDate() - 7)
            return this.toDatetimeLocal(d, '00:00:00')
        },
        defaultEnd() {
            const d = new Date()
            return this.toDatetimeLocal(d, '23:59:59')
        },
        toDatetimeLocal(date, timeStr) {
            const yyyy = date.getFullYear()
            const mm = String(date.getMonth() + 1).padStart(2, '0')
            const dd = String(date.getDate()).padStart(2, '0')
            const hhmmss = String(timeStr || '00:00:00')
            return `${yyyy}-${mm}-${dd}T${hhmmss}`
        },
        apiDatetime(v) {
            if (!v) return ''
            const s = String(v)
            return s.replace('T', ' ')
        },
        money(v) {
            const n = Number(v || 0)
            const val = Number.isFinite(n) ? n : 0
            return val.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
        },
        safeStudent(row) {
            return row?.invoice?.student || row?.invoice?.student || null
        },
        admissionId(row) {
            const s = this.safeStudent(row)
            if (!s) return 'N/A'
            return s.admission_id || s.admission_roll || 'N/A'
        },
        formatDate(v) {
            if (!v) return 'N/A'
            try {
                return new Date(v).toLocaleString()
            } catch {
                return 'N/A'
            }
        },
        async allAccount() {
            try {
                const res = await window.axios.get('/admin/all-account')
                const list = res?.data?.data
                this.accounts = Array.isArray(list) ? list : []
            } catch {
                this.accounts = []
            }
        },
        async search() {
            this.error = ''
            if (!this.search_data.account) {
                this.error = 'Account is required'
                return
            }
            this.loading = true
            try {
                const payload = {
                    account: this.search_data.account,
                    start_date: this.apiDatetime(this.search_data.start_date),
                    end_date: this.apiDatetime(this.search_data.end_date),
                    page: this.search_data.page,
                    limit: this.search_data.limit,
                }
                const res = await window.axios.post('/sslcommerz/settlement', payload)
                this.apiResponse = res?.data || null
            } catch (e) {
                this.apiResponse = null
                this.error = e?.response?.data?.message || e?.response?.data?.error || 'Failed to fetch settlement data'
            } finally {
                this.loading = false
            }
        },
        changePage(page) {
            const p = Number(page)
            if (!Number.isFinite(p)) return
            if (p < 1 || p > this.totalPages) return
            this.search_data.page = p
            this.search()
        },
        async generatePdf() {
            const actionButtons = Array.from(document.querySelectorAll('button'))
            const prev = actionButtons.map((b) => b.style.display)
            actionButtons.forEach((b) => {
                if (b && b.textContent) b.style.display = 'none'
            })

            try {
                const { jsPDF } = await import('jspdf')
                const auto = await import('jspdf-autotable')
                const autoTable = auto?.default
                if (!jsPDF || !autoTable) return

                const doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' })
                autoTable(doc, {
                    html: '#pdf-table',
                    margin: { top: 10, right: 2, bottom: 2, left: 2 },
                    styles: { fontSize: 8 },
                    didDrawPage: () => {
                        doc.setFontSize(14)
                        doc.text('BANK SETTLEMENT REPORT', doc.internal.pageSize.width / 2, 7, { align: 'center' })
                    },
                })
                doc.save('bank_settlement.pdf')
            } finally {
                actionButtons.forEach((b, i) => {
                    b.style.display = prev[i] || ''
                })
            }
        },
        exportExcel() {
            if (!this.combinedData.length) return

            const escape = (v) => {
                const s = String(v ?? '')
                return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;')
            }

            const html =
                '<html><head><meta charset="utf-8"></head><body>' +
                '<table border="1">' +
                '<tr>' +
                '<th>Student Name</th><th>Mobile</th><th>Level</th><th>Dept</th><th>Class</th><th>Add ID</th><th>Roll</th><th>Reg No</th><th>Purpose</th><th>Sett Date</th><th>Trans Date</th><th>Trans ID</th><th>Amount (BDT)</th>' +
                '</tr>' +
                this.combinedData
                    .map((r) => {
                        const s = r?.invoice?.student || {}
                        return (
                            '<tr>' +
                            `<td>${escape(s.name)}</td>` +
                            `<td>${escape(s.mobile)}</td>` +
                            `<td>${escape(r?.invoice?.qualification?.name)}</td>` +
                            `<td>${escape(r?.invoice?.department?.name)}</td>` +
                            `<td>${escape(r?.invoice?.academic_class?.name)}</td>` +
                            `<td>${escape(this.admissionId(r))}</td>` +
                            `<td>${escape(s.college_roll)}</td>` +
                            `<td>${escape(s.reg_no)}</td>` +
                            `<td>${escape(r?.invoice?.head?.name_en)}</td>` +
                            `<td>${escape(this.formatDate(r?.settlement?.sett_date))}</td>` +
                            `<td>${escape(this.formatDate(r?.settlement?.tran_date))}</td>` +
                            `<td>${escape(r?.settlement?.transactionID)}</td>` +
                            `<td>${escape(this.money(r?.settlement?.cr_amount))}</td>` +
                            '</tr>'
                        )
                    })
                    .join('') +
                '</table></body></html>'

            const blob = new Blob([html], { type: 'application/vnd.ms-excel' })
            const url = URL.createObjectURL(blob)
            const a = document.createElement('a')
            a.href = url
            a.download = `Bank_Settlement_Report_${new Date().toISOString().slice(0, 10).replace(/-/g, '')}.xls`
            document.body.appendChild(a)
            a.click()
            document.body.removeChild(a)
            URL.revokeObjectURL(url)
        },
    },
}
</script>
