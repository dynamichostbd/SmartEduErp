<template>
    <div class="min-h-screen bg-slate-50">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <div class="mb-6">
                <h1 class="text-2xl font-semibold text-slate-900">Generate Hostel Fees</h1>
                <p class="text-slate-600">Generate hostel fees for multiple months and hostels</p>
            </div>

            <div class="border border-slate-300 bg-white p-5">
                <form @submit.prevent="submit">
                    <!-- Header Row -->
                    <div class="grid grid-cols-12 gap-2 mb-3">
                        <div class="col-span-1 text-center text-xs font-semibold text-slate-600">Select</div>
                        <div class="col-span-2 text-xs font-semibold text-slate-600">Month</div>
                        <div class="col-span-2 text-xs font-semibold text-slate-600">Hostel</div>
                        <div class="col-span-2 text-xs font-semibold text-slate-600">Purpose</div>
                        <div class="col-span-2 text-center text-xs font-semibold text-slate-600">Amount</div>
                        <div class="col-span-3 text-center text-xs font-semibold text-slate-600">Action</div>
                    </div>

                    <!-- Fee Generation Rows -->
                    <div v-for="(item, key) in feeData" :key="key" class="grid grid-cols-12 gap-2 mb-3 items-center">
                        <!-- Checkbox -->
                        <div class="col-span-1 text-center">
                            <input type="checkbox" v-model="item.check" value="1" class="rounded border-slate-300" />
                        </div>

                        <!-- Month -->
                        <div class="col-span-2">
                            <select v-model="item.month" class="h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm">
                                <option v-for="month in months" :key="month.key" :value="month.key">
                                    {{ month.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Hostel -->
                        <div class="col-span-2">
                            <select v-model="item.hostel_id" class="h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm">
                                <option value="">--Select Hostel--</option>
                                <option v-for="hostel in hostels" :key="hostel.id" :value="hostel.id">
                                    {{ hostel.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Purpose (Account Head) -->
                        <div class="col-span-2">
                            <select v-model="item.account_head_id" class="h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm">
                                <option value="">--Select Purpose--</option>
                                <option v-for="(name, id) in accountHeads" :key="id" :value="id">
                                    {{ name }}
                                </option>
                            </select>
                        </div>

                        <!-- Amount -->
                        <div class="col-span-2">
                            <input type="text" v-model="item.amount" class="h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm text-center" />
                        </div>

                        <!-- Actions -->
                        <div class="col-span-3 flex justify-center gap-2">
                            <button 
                                v-if="feeData.length > 1"
                                type="button"
                                @click="removeRow(key)"
                                class="inline-flex h-8 w-8 items-center justify-center rounded-sm border border-red-200 bg-white text-red-700 hover:bg-red-50"
                                title="Remove"
                            >
                                <i class="fas fa-minus"></i>
                            </button>
                            <button 
                                v-if="feeData.length === key + 1"
                                type="button"
                                @click="addRow"
                                class="inline-flex h-8 w-8 items-center justify-center rounded-sm border border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100"
                                title="Add"
                            >
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-center mt-6">
                        <div class="text-center">
                            <button
                                type="button"
                                :disabled="loading"
                                @click="submit()"
                                class="rounded-sm bg-emerald-600 px-6 py-2 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-50"
                            >
                                <span v-if="loading">
                                    <i class="fas fa-spinner fa-spin mr-2"></i>
                                    Processing...
                                </span>
                                <span v-else>
                                    <i class="fas fa-save mr-2"></i>
                                    Generate Fees
                                </span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'HostelFeeGenerate',
    props: ['systems'],
    data() {
        return {
            loading: false,
            feeData: []
        }
    },
    computed: {
        hostels() {
            return this.systems?.global?.hostels || []
        },
        accountHeads() {
            return this.systems?.global?.hostel_heads || {}
        },
        months() {
            return [
                { key: '01', name: 'January' },
                { key: '02', name: 'February' },
                { key: '03', name: 'March' },
                { key: '04', name: 'April' },
                { key: '05', name: 'May' },
                { key: '06', name: 'June' },
                { key: '07', name: 'July' },
                { key: '08', name: 'August' },
                { key: '09', name: 'September' },
                { key: '10', name: 'October' },
                { key: '11', name: 'November' },
                { key: '12', name: 'December' }
            ]
        }
    },
    methods: {
        addRow() {
            this.feeData.push({
                account_head_id: '',
                hostel_id: '',
                amount: 0,
                month: '01',
                check: 1,
            })
        },
        removeRow(key) {
            this.feeData.splice(key, 1)
        },
        async submit() {
            // Validate that at least one row is checked and has required fields
            const checkedRows = this.feeData.filter(item => item.check)
            
            if (checkedRows.length === 0) {
                this.$toast.error('Please select at least one row to generate fees')
                return
            }

            // Validate checked rows
            for (const row of checkedRows) {
                if (!row.hostel_id || !row.account_head_id || !row.amount || parseFloat(row.amount) <= 0) {
                    this.$toast.error('Please fill all required fields for selected rows')
                    return
                }
            }

            if (!confirm('Are you sure want to generate invoice?')) {
                return
            }

            this.loading = true
            
            try {
                const response = await window.axios.post('/admin/hostelFeeGenerate', {
                    fee_data: checkedRows
                })
                
                this.$toast.success(response.data.message || 'Fees generated successfully')
                
                // Reset form
                this.feeData = []
                this.feesGenerate()
                
            } catch (error) {
                this.$toast.error('Failed to generate fees')
            } finally {
                this.loading = false
            }
        },
        feesGenerate() {
            // Initialize with 12 months
            for (let i = 0; i < 12; i++) {
                this.feeData.push({
                    month: String(i + 1).padStart(2, '0'),
                    hostel_id: '',
                    account_head_id: '',
                    amount: 0,
                    check: 1,
                })
            }
        }
    },
    mounted() {
        this.feesGenerate()
    }
}
</script>
