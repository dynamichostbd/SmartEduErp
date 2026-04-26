<template>
    <div
        class="mt-6 mb-6 mx-auto w-full max-w-6xl rounded-2xl border border-slate-200 bg-white shadow-xl overflow-hidden">

        <!-- Status Message -->
        <div v-if="app.onlineAdmissionStatusMessage"
            class="m-6 mb-0 rounded-md border p-4 text-sm font-medium flex items-center gap-3 shadow-sm"
            :class="app.onlineAdmissionStatusClass">
            <i class="fa-solid fa-circle-info text-lg"></i>
            <div>
                {{ app.onlineAdmissionStatusMessage }}
                <span v-if="app.onlineAdmissionTranId" class="font-bold ml-1"> ({{ app.onlineAdmissionTranId }})</span>
            </div>
        </div>
        <div v-if="app.studentMe"
            class="m-6 rounded-md border border-red-200 bg-red-50 p-4 text-center text-sm font-semibold text-red-800">
            You are already logged in, logout to access this page
        </div>
        <div v-else class="flex flex-col md:flex-row min-h-[400px]">

            <!-- Left Side - Illustration & Titles -->
            <div
                class="w-full md:w-2.5/5 bg-[#f8fafc] p-8 sm:p-12 flex flex-col items-center justify-center relative overflow-hidden border-b md:border-b-0 md:border-r border-slate-200">
                <!-- SVG Icon (Credit Card/Payment) -->
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
                        <!-- Credit Card -->
                        <rect x="25" y="35" width="70" height="50" rx="8" fill="#ffffff" stroke="#0d6b75"
                            stroke-width="4" />
                        <rect x="25" y="45" width="70" height="10" fill="#0d6b75" />
                        <rect x="35" y="65" width="20" height="6" rx="2" fill="#e2e8f0" />
                        <rect x="60" y="65" width="10" height="6" rx="2" fill="#e2e8f0" />
                        <!-- Check/Lock Icon -->
                        <circle cx="85" cy="75" r="14" fill="#0d6b75" />
                        <path d="M79 75 L83 79 L91 71" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                </div>

                <!-- Text -->
                <h2 class="text-3xl font-bold text-slate-800 mb-4 z-10 text-center tracking-tight">
                    Admission <br />
                    <span
                        class="bg-gradient-to-r from-[#0d6b75] to-[#20a3b2] bg-clip-text text-transparent">Payment</span>
                </h2>
                <div class="h-1.5 w-16 bg-gradient-to-r from-[#0d6b75] to-[#20a3b2] rounded-full z-10 mb-5"></div>
                <p class="text-sm text-slate-500 z-10 text-center leading-relaxed max-w-xs">
                    Enter your details to verify and complete your admission payment securely.
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
            <div class="w-full md:w-2.5/5 p-8 sm:p-10 relative flex flex-col justify-center">

                <!-- Pay Slip Button -->
                <div class="absolute top-4 right-4 sm:top-6 sm:right-6">
                    <button type="button"
                        class="rounded-md bg-white border border-[#0d6b75] px-4 py-2 text-sm font-bold text-[#0d6b75] shadow-sm transition-all hover:bg-[#0d6b75]/5 flex items-center gap-2"
                        @click="app.go('/online-admission-invoice')">
                        <i class="fa-solid fa-file-invoice"></i> Pay Slip
                    </button>
                </div>
                <!-- Form Header -->
                <div class="mt-12 sm:mt-6 mb-8 text-center flex flex-col items-center">
                    <div class="flex items-center justify-center gap-3 mb-3">
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-full bg-[#0d6b75]/10 text-[#0d6b75]">
                            <i class="fa-solid fa-credit-card text-2xl"></i>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-bold text-slate-800">Secure Payment</h3>
                    </div>
                    <!-- Divider line -->
                    <div class="w-full max-w-[300px] flex items-center gap-2 my-2 justify-center">
                        <div class="h-px w-full bg-slate-200"></div>
                        <div class="h-1.5 w-1.5 rounded-full bg-[#0d6b75]"></div>
                        <div class="h-px w-full bg-slate-200"></div>
                    </div>
                    <p class="text-sm text-slate-500 mt-2">Verify your student info to proceed with payment.</p>
                </div>
                <!-- Form Inputs -->
                <div class="space-y-4 mb-8">
                    <div class="relative group">
                        <div
                            class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-[#0d6b75]/60 group-focus-within:text-[#0d6b75] transition-colors">
                            <i class="fa-solid fa-phone text-lg"></i>
                        </div>
                        <input v-model="app.onlineAdmissionPaymentForm.mobile" type="text" placeholder="Mobile Number"
                            :readonly="app.onlineAdmissionPaymentReadonly"
                            class="h-12 w-full rounded-md border border-slate-300 bg-white pl-11 pr-4 text-sm text-slate-700 outline-none focus:border-[#0d6b75] focus:ring-1 focus:ring-[#0d6b75] transition-shadow shadow-sm disabled:bg-slate-100 read-only:bg-slate-50 read-only:text-slate-500" />
                    </div>
                    <div class="relative group">
                        <div
                            class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-[#0d6b75]/60 group-focus-within:text-[#0d6b75] transition-colors">
                            <i class="fa-regular fa-user text-lg"></i>
                        </div>
                        <input v-model="app.onlineAdmissionPaymentForm.admission_roll" type="text"
                            placeholder="Admission Roll / Reg No" :readonly="app.onlineAdmissionPaymentReadonly"
                            class="h-12 w-full rounded-md border border-slate-300 bg-white pl-11 pr-4 text-sm text-slate-700 outline-none focus:border-[#0d6b75] focus:ring-1 focus:ring-[#0d6b75] transition-shadow shadow-sm disabled:bg-slate-100 read-only:bg-slate-50 read-only:text-slate-500" />
                    </div>

                    <div class="flex justify-center pt-2">
                        <button v-if="app.onlineAdmissionPaymentReadonly" type="button"
                            class="h-12 w-full sm:w-48 rounded-md bg-rose-600 px-6 py-2 text-sm font-bold text-white shadow-sm transition-all hover:bg-rose-700 flex items-center justify-center gap-2"
                            @click="app.onlineAdmissionPaymentReset">
                            <i class="fa-solid fa-rotate-left"></i> Reset Form
                        </button>
                        <button v-else-if="!app.onlineAdmissionPaymentHeadsLoading" type="button"
                            class="h-12 w-full sm:w-48 rounded-md bg-[#0d6b75] px-6 py-2 text-sm font-bold text-white shadow-sm transition-all hover:bg-[#0a4d54] flex items-center justify-center gap-2"
                            @click="app.onlineAdmissionGetPaymentHeads">
                            <i class="fa-solid fa-check"></i> Check Now
                        </button>
                        <div v-else
                            class="h-12 w-full sm:w-48 rounded-md bg-slate-100 border border-slate-200 px-6 py-2 text-center text-sm font-bold text-slate-500 flex items-center justify-center gap-2">
                            <i class="fa-solid fa-spinner fa-spin text-[#0d6b75]"></i>
                        </div>
                    </div>
                </div>
                <!-- Error Message -->
                <div v-if="app.onlineAdmissionPaymentError"
                    class="mt-2 mb-6 rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-800 flex items-start gap-3 shadow-sm">
                    <i class="fa-solid fa-circle-exclamation mt-0.5 text-red-500"></i>
                    <div class="font-medium">{{ app.onlineAdmissionPaymentError }}</div>
                </div>
                <!-- Payment Details Area -->
                <div v-if="app.onlineAdmissionPaymentHeads.length"
                    class="mt-2 pt-6 border-t border-slate-100 space-y-4">
                    <div class="relative group">
                        <div
                            class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-[#0d6b75]/60">
                            <i class="fa-solid fa-list-check text-lg"></i>
                        </div>
                        <select v-model="app.onlineAdmissionPaymentForm.account_head_id"
                            class="h-12 w-full rounded-md border border-slate-300 bg-white pl-11 pr-4 text-sm text-slate-700 outline-none focus:border-[#0d6b75] focus:ring-1 focus:ring-[#0d6b75] transition-shadow shadow-sm appearance-none"
                            @change="app.onlineAdmissionSelectPaymentPurpose">
                            <option value="">Select Fees/Purpose</option>
                            <option v-for="p in app.onlineAdmissionPaymentHeads" :key="'oaph-' + p.account_head_id"
                                :value="String(p.account_head_id)">{{ p.account_head?.name || '' }}</option>
                        </select>
                        <div
                            class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400">
                            <i class="fa-solid fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <div class="relative group flex-1">
                            <div
                                class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-[#0d6b75]/60 group-focus-within:text-[#0d6b75] transition-colors">
                                <i class="fa-solid fa-bangladeshi-taka-sign text-lg"></i>
                            </div>
                            <input v-model="app.onlineAdmissionPaymentForm.amount" type="text" placeholder="Amount"
                                :readonly="app.onlineAdmissionPaymentAmountReadonly"
                                class="h-12 w-full rounded-md border border-slate-300 bg-white pl-11 pr-4 text-sm font-bold text-slate-800 outline-none focus:border-[#0d6b75] focus:ring-1 focus:ring-[#0d6b75] transition-shadow shadow-sm read-only:bg-slate-50 read-only:text-slate-500" />
                        </div>
                        <div class="relative flex-1">
                            <div
                                class="flex items-center justify-between h-12 w-full rounded-md border border-slate-200 bg-slate-50 px-4 text-sm">
                                <span class="font-semibold text-slate-500">Service Charge</span>
                                <span
                                    class="font-bold text-slate-800 flex items-center gap-1 bg-white px-2 py-1 rounded shadow-sm border border-slate-200">
                                    <i class="fa-solid fa-bangladeshi-taka-sign text-xs text-slate-400"></i>
                                    {{ app.onlineAdmissionPaymentChargeAmount || '0' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Pay Now Button -->
                <div v-if="Number(app.onlineAdmissionPaymentForm.amount || 0) > 0"
                    class="mt-8 pt-6 border-t border-slate-100 flex justify-end">
                    <button v-if="!app.onlineAdmissionPaymentSubmitting" type="button"
                        class="w-full sm:w-auto rounded-md bg-gradient-to-r from-[#0d6b75] to-[#12919e] px-10 py-3.5 text-base font-bold text-white shadow-lg transition-transform hover:-translate-y-0.5 hover:shadow-xl hover:opacity-95 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none flex items-center justify-center gap-3"
                        :disabled="app.onlineAdmissionPaymentPayFirst" @click="app.onlineAdmissionPayNow">
                        <span>PAY NOW</span> <i class="fa-solid fa-arrow-right"></i>
                    </button>
                    <div v-else
                        class="w-full sm:w-auto rounded-md bg-slate-100 border border-slate-200 px-10 py-3.5 text-center text-sm font-bold text-slate-500 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-spinner fa-spin text-[#0d6b75]"></i> Processing..
                    </div>
                </div>
            </div>
        </div>
    </div>

</template>

<script setup>
import { inject } from 'vue'
const app = inject('app')
</script>