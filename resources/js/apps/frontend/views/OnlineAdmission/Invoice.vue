<template>
    <div
        class="mt-6 mb-6 mx-auto w-full max-w-6xl rounded-2xl border border-slate-200 bg-white shadow-xl overflow-hidden">
        <div v-if="app.studentMe"
            class="m-6 rounded-md border border-red-200 bg-red-50 p-4 text-center text-sm font-semibold text-red-800">
            You are already logged in, logout to access this page
        </div>

        <div v-else class="flex flex-col md:flex-row min-h-[400px]">

            <!-- Left Side - Illustration & Titles -->
            <div
                class="w-full md:w-2/5 bg-[#f8fafc] p-8 sm:p-12 flex flex-col items-center justify-center relative overflow-hidden border-b md:border-b-0 md:border-r border-slate-200">
                <!-- SVG Icon (Receipt) -->
                <div class="mb-8 relative z-10">
                    <svg width="140" height="140" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <!-- Background decorative elements -->
                        <circle cx="20" cy="20" r="4" fill="#69b4bd" opacity="0.5" />
                        <circle cx="100" cy="90" r="6" fill="#12919e" opacity="0.3" />
                        <path d="M10 80 L15 75 L20 80 L15 85 Z" fill="#0d6b75" opacity="0.4" />
                        <path d="M105 30 L110 25 L115 30 L110 35 Z" fill="#69b4bd" opacity="0.6" />
                        <!-- Blob -->
                        <path d="M25 60 C25 40 40 25 60 25 C80 25 95 40 95 60 C95 80 80 95 60 95 C40 95 25 80 25 60 Z"
                            fill="#0d6b75" opacity="0.08" />
                        <!-- Receipt -->
                        <path d="M40 25 H80 V85 L75 80 L70 85 L65 80 L60 85 L55 80 L50 85 L45 80 L40 85 V25 Z"
                            fill="#ffffff" stroke="#0d6b75" stroke-width="4" stroke-linejoin="round" />
                        <line x1="50" y1="40" x2="70" y2="40" stroke="#e2e8f0" stroke-width="3"
                            stroke-linecap="round" />
                        <line x1="50" y1="50" x2="65" y2="50" stroke="#e2e8f0" stroke-width="3"
                            stroke-linecap="round" />
                        <line x1="50" y1="60" x2="60" y2="60" stroke="#e2e8f0" stroke-width="3"
                            stroke-linecap="round" />
                        <!-- Circle Icon -->
                        <circle cx="65" cy="70" r="12" fill="#0d6b75" />
                        <path d="M61 70 L64 73 L69 67" stroke="#ffffff" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                </div>

                <!-- Text -->
                <h2 class="text-3xl font-bold text-slate-800 mb-4 z-10 text-center tracking-tight">
                    Download <br />
                    <span class="bg-gradient-to-r from-[#0d6b75] to-[#20a3b2] bg-clip-text text-transparent">Pay
                        Slip</span>
                </h2>
                <div class="h-1.5 w-16 bg-gradient-to-r from-[#0d6b75] to-[#20a3b2] rounded-full z-10 mb-5"></div>
                <p class="text-sm text-slate-500 z-10 text-center leading-relaxed max-w-xs">
                    Enter your admission roll number and mobile number to download your pay slip.
                </p>

                <!-- Decorative background elements -->
                <div
                    class="absolute -top-20 -left-20 w-64 h-64 bg-[#0d6b75]/5 rounded-full blur-3xl pointer-events-none">
                </div>
                <div
                    class="absolute -bottom-20 -right-20 w-64 h-64 bg-[#20a3b2]/5 rounded-full blur-3xl pointer-events-none">
                </div>
            </div>

            <!-- Right Side - Form -->
            <div class="w-full md:w-3/5 p-8 sm:p-10 relative flex flex-col justify-center">

                <!-- Payment Button -->
                <div class="absolute top-4 right-4 sm:top-6 sm:right-6">
                    <button type="button"
                        class="rounded-md bg-gradient-to-r from-[#0d6b75] to-[#12919e] px-4 py-2 text-sm font-bold text-white shadow-md transition-all hover:-translate-y-0.5 hover:shadow-lg hover:opacity-95 flex items-center gap-2"
                        @click="app.go('/online-admission-payment')">
                        <i class="fa-regular fa-credit-card"></i> Payment
                    </button>
                </div>

                <!-- Form Header -->
                <div class="mt-12 sm:mt-6 mb-8 text-center flex flex-col items-center">
                    <div class="flex items-center justify-center gap-3 mb-3">
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-full bg-[#0d6b75]/10 text-[#0d6b75]">
                            <i class="fa-solid fa-file-invoice text-2xl"></i>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-bold text-slate-800">Download Your Pay Slip</h3>
                    </div>
                    <!-- Divider line -->
                    <div class="w-full max-w-[300px] flex items-center gap-2 my-2 justify-center">
                        <div class="h-px w-full bg-slate-200"></div>
                        <div class="h-1.5 w-1.5 rounded-full bg-[#0d6b75]"></div>
                        <div class="h-px w-full bg-slate-200"></div>
                    </div>
                    <p class="text-sm text-slate-500 mt-2">Please provide the details below to access your pay slip.</p>
                </div>

                <!-- Form Inputs -->
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 mb-6">
                    <div class="relative group">
                        <div
                            class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-[#0d6b75]/60 group-focus-within:text-[#0d6b75] transition-colors">
                            <i class="fa-regular fa-user text-lg"></i>
                        </div>
                        <input v-model="app.onlineAdmissionInvoiceSearch.admission_roll" type="text"
                            placeholder="Admission Roll / Reg No"
                            class="h-12 w-full rounded-md border border-slate-300 bg-white pl-11 pr-4 text-sm text-slate-700 outline-none focus:border-[#0d6b75] focus:ring-1 focus:ring-[#0d6b75] transition-shadow shadow-sm hover:border-slate-400" />
                    </div>
                    <div class="relative group">
                        <div
                            class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-[#0d6b75]/60 group-focus-within:text-[#0d6b75] transition-colors">
                            <i class="fa-solid fa-phone text-lg"></i>
                        </div>
                        <input v-model="app.onlineAdmissionInvoiceSearch.mobile" type="text" placeholder="Mobile Number"
                            class="h-12 w-full rounded-md border border-slate-300 bg-white pl-11 pr-4 text-sm text-slate-700 outline-none focus:border-[#0d6b75] focus:ring-1 focus:ring-[#0d6b75] transition-shadow shadow-sm hover:border-slate-400" />
                    </div>
                </div>

                <!-- Search Button -->
                <div>
                    <button v-if="!app.onlineAdmissionInvoiceLoading" type="button"
                        class="w-full rounded-md bg-gradient-to-r from-[#0d6b75] to-[#12919e] py-3.5 text-sm font-bold text-white shadow-md transition-all hover:-translate-y-0.5 hover:shadow-lg hover:opacity-95 flex items-center justify-center gap-2"
                        @click="app.onlineAdmissionSearchInvoices">
                        <i class="fa-solid fa-magnifying-glass text-lg"></i> Search
                    </button>
                    <div v-else
                        class="w-full rounded-md bg-slate-100 border border-slate-200 py-3.5 text-center text-sm font-bold text-slate-500 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-spinner fa-spin text-lg text-[#0d6b75]"></i> Processing..
                    </div>
                </div>

                <!-- Error Message -->
                <div v-if="app.onlineAdmissionInvoiceError"
                    class="mt-6 rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-800 flex items-start gap-3 shadow-sm">
                    <i class="fa-solid fa-circle-exclamation mt-0.5 text-red-500"></i>
                    <div class="font-medium">{{ app.onlineAdmissionInvoiceError }}</div>
                </div>
                <div v-else
                    class="mt-6 rounded-md bg-[#0d6b75]/5 border border-[#0d6b75]/10 p-4 text-sm text-[#0d6b75] flex flex-col sm:flex-row items-center gap-3 shadow-sm">
                    <div
                        class="flex-shrink-0 h-8 w-8 flex items-center justify-center rounded-full bg-[#0d6b75] text-white shadow-sm">
                        <i class="fa-solid fa-info text-xs"></i>
                    </div>
                    <p class="text-center sm:text-left font-medium">Please input your admission roll and your mobile
                        number for invoice.</p>
                </div>
            </div>
        </div>

    </div>

    <!-- Results Area -->
    <div id="invoicePrintArea" class="w-[60%] mx-auto border border-slate-300 p-4">
        <div v-if="app.onlineAdmissionInvoicesShow">
            <div v-if="app.onlineAdmissionInvoices.length" class="space-y-10">
                <div v-for="(inv, idx) in app.onlineAdmissionInvoices" :key="'oainv-' + idx" class="">
                    <!-- Action Buttons row -->
                    <div class="flex items-center justify-between mb-4 flex-wrap gap-2 px-1">
                        <div class="flex items-center gap-2 text-sm font-semibold text-slate-600">
                            <i class="fa-solid fa-file-invoice text-[#0d6b75]"></i> Invoice: {{ inv.invoice_number }}
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="printInvoice('printArea-' + idx)"
                                class="flex items-center gap-2 rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 shadow-sm transition">
                                <i class="fa-solid fa-print"></i> Print
                            </button>
                            <button type="button"
                                class="flex items-center gap-2 rounded-md bg-gradient-to-r from-[#0d6b75] to-[#12919e] px-4 py-2 text-sm font-bold text-white shadow-sm transition-transform hover:-translate-y-0.5 hover:shadow-md"
                                @click="app.onlineAdmissionDownloadInvoice(inv.id)">
                                <i class="fa-solid fa-download"></i> Download
                            </button>
                        </div>
                    </div>

                    <!-- Two invoice copies — stacked vertically -->
                    <div :id="'printArea-' + idx" class="w-[70%] mx-auto">
                        <div v-for="copy in 2" :key="'copy-' + copy"
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
                                        <template v-if="inv.payment_date">
                                            <span class="text-slate-500 underline"
                                                style="font-size:10px">PAYMENT:</span>
                                            <span class="ml-1">{{ inv.payment_date }}</span>
                                            <span class="mx-2">|</span>
                                        </template>
                                        <span class="text-slate-500 underline" style="font-size:10px">CREATED:</span>
                                        <span class="ml-1">{{ inv.invoice_date }}</span>
                                        <span class="mx-2">|</span>
                                        <span>{{ inv.invoice_number }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Student info (col-span-7) + Paid stamp (col-span-5) -->
                            <div class="mt-3 grid grid-cols-12 gap-4">
                                <div class="col-span-7 text-sm text-slate-800">
                                    <div><span class="font-semibold">Name :</span> {{ inv.name || '—' }}
                                    </div>
                                    <div><span class="font-semibold">Mobile :</span> {{ inv.mobile || '—'
                                        }}</div>
                                    <div class="mt-2">
                                        <span class="font-semibold">Level :</span> {{
                                            inv.qualification_name || inv.academic_qualification_name || '' }}
                                        <span v-if="inv.session_name || inv.academic_session_name">({{ inv.session_name
                                            || inv.academic_session_name
                                            }})</span>
                                    </div>
                                    <div>
                                        <span class="font-semibold">Dept. :</span> {{ inv.department_name
                                            || '' }}
                                        <span v-if="inv.class_name || inv.academic_class_name">({{ inv.class_name ||
                                            inv.academic_class_name
                                            }})</span>
                                    </div>
                                    <div v-if="inv.admission_roll" class="mt-2">
                                        <div><span class="font-semibold">Admission Roll :</span> {{
                                            inv.admission_roll }}</div>
                                    </div>
                                </div>
                                <div class="col-span-5 flex items-center justify-center">
                                    <img v-if="['success', 'paid'].includes(String(inv.status || '').toLowerCase().trim())"
                                        :src="((laravel?.baseurl || '').replace(/\/$/, '')) + '/images/paid.png'"
                                        class="h-[120px] w-[120px] object-contain" />
                                </div>
                            </div>

                            <!-- EPAYMENT INVOICE label -->
                            <div class="mt-4 text-center text-base font-semibold text-slate-900 uppercase">
                                Online Admission Invoice
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
                                            <td class="border border-slate-300 px-3 py-2">{{ inv.head_name || inv.purpose_name || 'Online Admission Fee' }}</td>
                                            <td class="border border-slate-300 px-3 py-2 text-right font-semibold">
                                                {{ Number(inv.amount || 0).toLocaleString(undefined, { minimumFractionDigits: 2 }) }}</td>
                                        </tr>   
                                    </tbody>
                                </table>
                            </div>

                            <!-- Inword -->
                            <div class="mt-3 text-xs text-slate-700">
                                <span class="font-semibold text-xs">Inword :</span> {{ Number(inv.amount ||
                                0).toFixed(2) }} taka only
                            </div>

                            <div class="mt-6 text-center text-[10px] text-slate-500">Powered By Dynamic Host BD</div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else
                class="rounded-md border border-orange-200 bg-orange-50 p-4 text-center text-sm text-orange-800 flex items-center justify-center gap-2 shadow-sm">
                <i class="fa-solid fa-triangle-exclamation text-orange-500"></i>
                Sorry!! invoice not found, please try again
            </div>
        </div>

    </div>
</template>

<script setup>
import { inject } from 'vue'
const app = inject('app')
const laravel = window.laravel

const printInvoice = (id) => {
    const el = document.getElementById(id)
    if (!el) return
    const win = window.open('', '_blank', 'width=900,height=800')
    win.document.write(`
        <html>
            <head>
                <title>Invoice Print</title>
                <script src="https://cdn.tailwindcss.com"><\/script>
                <style>
                    @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;700&display=swap');
                    body { font-family: 'Noto Sans Bengali', sans-serif; }
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
</script>