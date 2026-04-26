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
                <!-- SVG Icon (Clipboard + Download) -->
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
                        <!-- Clipboard -->
                        <rect x="35" y="30" width="50" height="65" rx="6" fill="#ffffff" stroke="#0d6b75"
                            stroke-width="4" />
                        <path d="M45 25 C45 22.2386 47.2386 20 50 20 H70 C72.7614 20 75 22.2386 75 25 V30 H45 V25 Z"
                            fill="#0d6b75" />
                        <line x1="45" y1="45" x2="75" y2="45" stroke="#e2e8f0" stroke-width="3"
                            stroke-linecap="round" />
                        <line x1="45" y1="55" x2="65" y2="55" stroke="#e2e8f0" stroke-width="3"
                            stroke-linecap="round" />
                        <!-- Download Icon Bubble -->
                        <circle cx="60" cy="70" r="14" fill="#0d6b75" />
                        <path d="M60 63 V75 M55 70 L60 75 L65 70" stroke="#ffffff" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" />
                        <line x1="55" y1="79" x2="65" y2="79" stroke="#ffffff" stroke-width="2"
                            stroke-linecap="round" />
                    </svg>
                </div>

                <!-- Text -->
                <h2 class="text-3xl font-bold text-slate-800 mb-4 z-10 text-center tracking-tight">
                    Download <br />
                    <span class="bg-gradient-to-r from-[#0d6b75] to-[#20a3b2] bg-clip-text text-transparent">Admission
                        Form</span>
                </h2>
                <div class="h-1.5 w-16 bg-gradient-to-r from-[#0d6b75] to-[#20a3b2] rounded-full z-10 mb-5"></div>
                <p class="text-sm text-slate-500 z-10 text-center leading-relaxed max-w-xs">
                    Enter your admission roll number and mobile number to download your admission form.
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
                            <i class="fa-regular fa-file-lines text-2xl"></i>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-bold text-slate-800">Download Your Admission Form</h3>
                    </div>
                    <!-- Divider line -->
                    <div class="w-full max-w-[300px] flex items-center gap-2 my-2 justify-center">
                        <div class="h-px w-full bg-slate-200"></div>
                        <div class="h-1.5 w-1.5 rounded-full bg-[#0d6b75]"></div>
                        <div class="h-px w-full bg-slate-200"></div>
                    </div>
                    <p class="text-sm text-slate-500 mt-2">Please provide the details below to access your admission
                        form.</p>
                </div>

                <!-- Form Inputs -->
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 mb-6">
                    <div class="relative group">
                        <div
                            class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-[#0d6b75]/60 group-focus-within:text-[#0d6b75] transition-colors">
                            <i class="fa-regular fa-user text-lg"></i>
                        </div>
                        <input v-model="app.onlineAdmissionDownloadSearch.admission_roll" type="text"
                            placeholder="Admission Roll / Reg No"
                            class="h-12 w-full rounded-md border border-slate-300 bg-white pl-11 pr-4 text-sm text-slate-700 outline-none focus:border-[#0d6b75] focus:ring-1 focus:ring-[#0d6b75] transition-shadow shadow-sm hover:border-slate-400" />
                    </div>
                    <div class="relative group">
                        <div
                            class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-[#0d6b75]/60 group-focus-within:text-[#0d6b75] transition-colors">
                            <i class="fa-solid fa-phone text-lg"></i>
                        </div>
                        <input v-model="app.onlineAdmissionDownloadSearch.mobile" type="text"
                            placeholder="Mobile Number"
                            class="h-12 w-full rounded-md border border-slate-300 bg-white pl-11 pr-4 text-sm text-slate-700 outline-none focus:border-[#0d6b75] focus:ring-1 focus:ring-[#0d6b75] transition-shadow shadow-sm hover:border-slate-400" />
                    </div>
                </div>

                <!-- Search Button -->
                <div>
                    <button v-if="!app.onlineAdmissionDownloadLoading" type="button"
                        class="w-full rounded-md bg-gradient-to-r from-[#0d6b75] to-[#12919e] py-3.5 text-sm font-bold text-white shadow-md transition-all hover:-translate-y-0.5 hover:shadow-lg hover:opacity-95 flex items-center justify-center gap-2"
                        @click="app.onlineAdmissionSearchDownloadForm">
                        <i class="fa-solid fa-magnifying-glass text-lg"></i> Search
                    </button>
                    <div v-else
                        class="w-full rounded-md bg-slate-100 border border-slate-200 py-3.5 text-center text-sm font-bold text-slate-500 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-spinner fa-spin text-lg text-[#0d6b75]"></i> Processing..
                    </div>
                </div>

                <!-- Error Message -->
                <div v-if="app.onlineAdmissionDownloadError"
                    class="mt-6 rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-800 flex items-start gap-3 shadow-sm">
                    <i class="fa-solid fa-circle-exclamation mt-0.5 text-red-500"></i>
                    <div class="font-medium">{{ app.onlineAdmissionDownloadError }}</div>
                </div>

                <!-- Results Area -->
                <div class="mt-6">
                    <template v-if="app.onlineAdmissionDownloadShow">
                        <template v-if="app.onlineAdmissionDownloadData">
                            <div
                                class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm relative overflow-hidden">
                                <!-- Top decorative border -->
                                <div
                                    class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-[#0d6b75] to-[#12919e]">
                                </div>

                                <div
                                    class="flex flex-col xl:flex-row xl:items-center justify-between gap-4 mb-6 pb-4 border-b border-slate-100">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-10 w-10 items-center justify-center rounded-full bg-[#0d6b75]/10 text-[#0d6b75]">
                                            <i class="fa-solid fa-user-check text-lg"></i>
                                        </div>
                                        <div>
                                            <h4 class="text-base font-bold text-slate-800">Student Details</h4>
                                            <p class="text-xs text-slate-500">Admission form found successfully.</p>
                                        </div>
                                    </div>
                                    <div>
                                        <button v-if="!app.onlineAdmissionDownloadPdfLoading" type="button"
                                            class="rounded-md bg-gradient-to-r from-[#0d6b75] to-[#12919e] px-4 py-2 text-sm font-bold text-white shadow-sm transition-transform hover:-translate-y-0.5 hover:shadow-md flex items-center gap-2 w-full xl:w-auto justify-center"
                                            @click="app.onlineAdmissionDownloadPdf">
                                            <i class="fa-solid fa-download"></i> Download Admission Form
                                        </button>
                                        <div v-else
                                            class="rounded-md bg-slate-100 px-4 py-2 text-sm font-bold text-slate-500 flex items-center justify-center gap-2 w-full xl:w-auto">
                                            <i class="fa-solid fa-spinner fa-spin text-[#0d6b75]"></i> Processing..
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-4 gap-x-6 text-sm">
                                    <div class="flex flex-col border-b border-slate-50 pb-2 sm:border-none sm:pb-0">
                                        <span
                                            class="text-slate-400 text-xs font-medium uppercase tracking-wider mb-0.5">Admission
                                            Roll</span>
                                        <span class="font-bold text-slate-800">{{
                                            app.onlineAdmissionDownloadData.admission_roll }}</span>
                                    </div>
                                    <div class="flex flex-col border-b border-slate-50 pb-2 sm:border-none sm:pb-0">
                                        <span
                                            class="text-slate-400 text-xs font-medium uppercase tracking-wider mb-0.5">Mobile</span>
                                        <span class="font-bold text-slate-800">{{ app.onlineAdmissionDownloadData.mobile
                                            }}</span>
                                    </div>
                                    <div class="flex flex-col border-b border-slate-50 pb-2 sm:border-none sm:pb-0">
                                        <span
                                            class="text-slate-400 text-xs font-medium uppercase tracking-wider mb-0.5">Fathers
                                            Name</span>
                                        <span class="font-bold text-slate-800">{{
                                            app.onlineAdmissionDownloadData.fathers_name }}</span>
                                    </div>
                                    <div class="flex flex-col border-b border-slate-50 pb-2 sm:border-none sm:pb-0">
                                        <span
                                            class="text-slate-400 text-xs font-medium uppercase tracking-wider mb-0.5">Mothers
                                            Name</span>
                                        <span class="font-bold text-slate-800">{{
                                            app.onlineAdmissionDownloadData.mothers_name }}</span>
                                    </div>
                                    <div class="flex flex-col sm:col-span-2">
                                        <span
                                            class="text-slate-400 text-xs font-medium uppercase tracking-wider mb-0.5">Address</span>
                                        <span class="font-bold text-slate-800">{{
                                            app.onlineAdmissionDownloadData.address }}</span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </template>
                    <div v-else-if="!app.onlineAdmissionDownloadData && app.onlineAdmissionDownloadShow"
                        class="rounded-md border border-orange-200 bg-orange-50 p-4 text-center text-sm text-orange-800 flex items-center justify-center gap-2 shadow-sm">
                        <i class="fa-solid fa-triangle-exclamation text-orange-500"></i>
                        Sorry!! admission form not found
                    </div>
                    <div v-else
                        class="rounded-md bg-[#0d6b75]/5 border border-[#0d6b75]/10 p-4 text-sm text-[#0d6b75] flex flex-col sm:flex-row items-center gap-3 shadow-sm">
                        <div
                            class="flex-shrink-0 h-8 w-8 flex items-center justify-center rounded-full bg-[#0d6b75] text-white shadow-sm">
                            <i class="fa-solid fa-info text-xs"></i>
                        </div>
                        <p class="text-center sm:text-left font-medium">Please input your admission roll and your mobile
                            number for
                            admission form.</p>
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