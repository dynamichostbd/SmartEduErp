<template>
    <div class="rounded-sm border border-slate-300 bg-white shadow-sm overflow-hidden">
        <div class="border-b border-slate-200 bg-slate-50/50 px-4 py-3 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wide">My Subjects</h2>
            <button 
                v-if="!editing && assignSubjects.length > 0"
                class="text-[11px] font-black text-[#0d6b75] hover:underline uppercase"
                @click="startEdit"
            >
                Change Subjects
            </button>
        </div>
        <div class="p-4 sm:p-6">
            <div v-if="loading" class="text-center py-10">
                <div class="inline-block h-6 w-6 animate-spin rounded-full border-2 border-[#0d6b75] border-t-transparent"></div>
                <div class="mt-2 text-xs font-bold text-slate-500 uppercase">Loading subjects...</div>
            </div>
            
            <div v-else-if="!assignSubjects.length && !editing" class="text-center py-12 border-2 border-dashed border-slate-100 rounded-sm bg-slate-50/30">
                <div class="text-slate-300 mb-3">
                    <svg class="mx-auto h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                </div>
                <div class="text-[13px] font-bold text-slate-500 mb-5">You haven't assigned any subjects yet.</div>
                <button class="rounded-md bg-[#0d6b75] px-6 py-2.5 text-xs font-bold text-white shadow-md hover:bg-[#0b5b63] transition-all" @click="startEdit">Select Subjects Now</button>
            </div>

            <div v-else class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Left Column: Compulsory Subjects -->
                <div class="space-y-4">
                    <div class="rounded-md border border-slate-200 bg-white shadow-sm overflow-hidden">
                        <div class="bg-slate-50 px-4 py-2 border-b border-slate-200">
                            <h3 class="text-xs font-black text-slate-700 uppercase tracking-wider">Compulsory Subjects</h3>
                        </div>
                        <div class="p-0">
                            <table class="w-full text-left text-sm border-collapse">
                                <thead>
                                    <tr class="bg-white border-b border-slate-100">
                                        <th class="w-12 border-r border-slate-100 py-2"></th>
                                        <th class="px-4 py-2 font-bold text-slate-500 text-[11px] uppercase tracking-tighter">Subject Name</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(item, key) in mainSubjects" :key="'cs-' + key" class="border-b border-slate-100 last:border-0 hover:bg-slate-50/50 transition-colors">
                                        <td class="text-center border-r border-slate-100 py-2.5">
                                            <svg class="mx-auto h-5 w-5 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                        </td>
                                        <td class="px-4 py-2.5 font-bold text-slate-800">
                                            {{ item.subject_name || '' }}
                                        </td>
                                    </tr>
                                    <tr v-if="!mainSubjects.length">
                                        <td colspan="2" class="px-4 py-8 text-center text-slate-400 italic text-xs">No compulsory subjects</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Right Column: 4th / Main Subjects -->
                <div class="space-y-4">
                    <div class="rounded-md border border-slate-200 bg-white shadow-sm overflow-hidden">
                        <div class="bg-slate-50 px-4 py-2 border-b border-slate-200">
                            <h3 class="text-xs font-black text-slate-700 uppercase tracking-wider">4th / Main Subject</h3>
                        </div>
                        <div class="p-4 sm:p-6">
                            <!-- Editing Mode -->
                            <div v-if="editing" class="space-y-6">
                                <div v-if="subjectAssignData?.note" class="bg-rose-50 border-l-4 border-rose-400 p-4 rounded-r-md text-[13px] font-bold text-rose-800 italic shadow-sm">
                                    {{ subjectAssignData.note }}
                                </div>

                                <!-- 4th Subject Dropdown -->
                                <div class="space-y-2">
                                    <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest">Select 4th Subject <span class="text-rose-500">*</span></label>
                                    <select v-model="fourthSubjectId" class="h-11 w-full rounded-md border border-slate-300 bg-white px-3 text-sm font-bold text-slate-800 outline-none focus:border-[#0d6b75] focus:ring-4 focus:ring-[#0d6b75]/5 transition-all shadow-sm">
                                        <option value="">--Select 4th Subject--</option>
                                        <option v-for="(sub, key) in fourthSubjectOptions" :key="'fo-' + key" :value="String(sub.subject_id)">
                                            {{ sub.subject_name || '' }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Main Subjects Selection -->
                                <div class="space-y-3">
                                    <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest">Select Main Subject (Choose {{ subjectAssignData?.main_subject || 0 }}) <span class="text-rose-500">*</span></label>
                                    
                                    <div v-if="subjectLoader" class="text-center py-6">
                                        <div class="h-5 w-5 animate-spin rounded-full border-2 border-slate-300 border-t-[#0d6b75] mx-auto"></div>
                                    </div>
                                    <div v-else class="border border-slate-200 rounded-md overflow-hidden">
                                        <table class="w-full text-left text-sm">
                                            <tbody>
                                                <tr v-for="(item, idx) in mainSubjectsSelectionList" :key="'ms-' + idx" class="border-b border-slate-100 last:border-0 hover:bg-slate-50/50 transition-colors">
                                                    <td class="w-12 text-center py-2.5 border-r border-slate-100 bg-slate-50/30">
                                                        <input 
                                                            type="checkbox" 
                                                            v-model="item.checked"
                                                            @change="selectAdditionalSubject(item.subject_id)"
                                                            class="h-4 w-4 rounded border-slate-300 text-[#0d6b75] focus:ring-[#0d6b75]/20 disabled:opacity-30"
                                                            :disabled="(!item.checked && selectionDisabled) || exceptSubjects.includes(item.subject_id)"
                                                        />
                                                    </td>
                                                    <td class="px-4 py-2.5 font-bold text-slate-800" :class="exceptSubjects.includes(item.subject_id) ? 'opacity-30 line-through' : ''">
                                                        {{ item.subject_name || '' }}
                                                    </td>
                                                </tr>
                                                <tr v-if="!mainSubjectsSelectionList.length">
                                                    <td colspan="2" class="px-4 py-8 text-center text-slate-400 italic text-[11px] uppercase font-bold tracking-widest">Select 4th subject first</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="pt-4 flex items-center justify-center gap-3">
                                    <button 
                                        @click="submitSubjectAssign" 
                                        class="min-w-[120px] rounded-md bg-[#0d6b75] px-6 py-2.5 text-xs font-bold text-white shadow-md hover:bg-[#0b5b63] transition-all active:scale-[0.98] disabled:opacity-70 flex items-center justify-center gap-2"
                                        :disabled="submitting"
                                    >
                                        <svg v-if="submitting" class="h-3.5 w-3.5 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><circle cx="12" cy="12" r="10" opacity="0.25"></circle><path d="M12 2a10 10 0 0 1 10 10" opacity="0.75"></path></svg>
                                        {{ submitting ? 'PROCESSING...' : 'SUBMIT' }}
                                    </button>
                                    <button 
                                        v-if="assignSubjects.length > 0"
                                        @click="editing = false" 
                                        class="rounded-md border border-slate-300 bg-white px-6 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-50 transition-all active:scale-[0.98]"
                                        :disabled="submitting"
                                    >
                                        CANCEL
                                    </button>
                                </div>
                            </div>

                            <!-- View Mode -->
                            <div v-else class="space-y-4">
                                <div class="border-b border-slate-100 pb-2 mb-2">
                                    <span class="text-[11px] font-black text-[#0d6b75] uppercase tracking-widest">4th Subject</span>
                                </div>
                                <div v-for="(item, key) in assignedFourthSubject" :key="'avf-' + key" class="flex items-center gap-3 rounded-md border border-emerald-100 p-3 bg-emerald-50/30">
                                    <svg class="h-4 w-4 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    <span class="text-[13px] font-bold text-slate-800">{{ item.subject_name || '' }}</span>
                                </div>

                                <div class="border-b border-slate-100 pb-2 mt-6 mb-2">
                                    <span class="text-[11px] font-black text-[#0d6b75] uppercase tracking-widest">Main Subjects</span>
                                </div>
                                <div v-for="(item, key) in assignedMainSubjects" :key="'avm-' + key" class="flex items-center gap-3 rounded-md border border-emerald-100 p-3 bg-emerald-50/30">
                                    <svg class="h-4 w-4 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    <span class="text-[13px] font-bold text-slate-800">{{ item.subject_name || '' }}</span>
                                </div>

                                <div v-if="!assignSubjects.length" class="text-center py-6 text-slate-400 text-xs italic">
                                    No subjects assigned
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, inject, computed, onMounted, watch } from 'vue'

const app = inject('app')

const loading = ref(false)
const editing = ref(false)
const submitting = ref(false)
const subjectLoader = ref(false)

const subjectAssignData = ref(null)
const assignSubjects = ref([])

const fourthSubjectId = ref('')
const mainSubjectsSelectionList = ref([])
const exceptSubjects = ref([])
const selectionDisabled = ref(false)

const loadData = async () => {
    loading.value = true
    try {
        const res = await window.axios.get('/api/public/student/subjects')
        subjectAssignData.value = res.data.subjectAssign
        assignSubjects.value = Array.isArray(res.data.assign_subjects) ? res.data.assign_subjects : []
        
        if (assignSubjects.value.length === 0) {
            editing.value = true
        }
    } catch (e) {
        console.error('Failed to load subjects', e)
    } finally {
        loading.value = false
    }
}

const mainSubjects = computed(() => {
    if (!subjectAssignData.value) return []
    const details = Array.isArray(subjectAssignData.value.details) ? subjectAssignData.value.details : []
    return details.filter(e => !e.fourth_subject && !e.main_subject)
})

const fourthSubjectOptions = computed(() => {
    if (!subjectAssignData.value) return []
    const details = Array.isArray(subjectAssignData.value.details) ? subjectAssignData.value.details : []
    return details.filter(e => e.fourth_subject)
})

const assignedFourthSubject = computed(() => {
    return assignSubjects.value.filter(e => String(e.main_subject) === '0')
})

const assignedMainSubjects = computed(() => {
    return assignSubjects.value.filter(e => String(e.main_subject) === '1')
})

const startEdit = () => {
    editing.value = true
    // If already assigned, pre-select if possible (logic can be complex based on requirements)
    // For now, we clear to allow fresh selection as in the old ERP flow when "Change" is clicked
}

const selectAdditionalSubject = (id) => {
    const list = mainSubjectsSelectionList.value
    const find = list.find(e => String(e.subject_id) === String(id))
    if (!find) return

    if (find.except_subject_id) {
        const eid = String(find.except_subject_id)
        if (exceptSubjects.value.includes(eid)) {
            exceptSubjects.value = exceptSubjects.value.filter(e => e !== eid)
        } else {
            exceptSubjects.value.push(eid)
        }
    }

    const selectedCount = list.filter(e => e.checked).length
    const limit = Number(subjectAssignData.value?.main_subject || 0)
    selectionDisabled.value = selectedCount >= limit
}

const submitSubjectAssign = async () => {
    if (!fourthSubjectId.value) {
        alert('Please select 4th subject')
        return
    }

    const selectedMain = mainSubjectsSelectionList.value.filter(e => e.checked)
    const limit = Number(subjectAssignData.value?.main_subject || 0)

    if (selectedMain.length === 0) {
        alert('Please select main subjects')
        return
    }

    if (selectedMain.length !== limit) {
        alert(`Please select exactly ${limit} main subjects`)
        return
    }

    if (!confirm('Are you sure you want to submit these subjects?')) return

    submitting.value = true
    try {
        const payload = selectedMain.map(e => ({
            subject_id: e.subject_id,
            main_subject: 1
        }))
        payload.unshift({
            subject_id: fourthSubjectId.value,
            main_subject: 0
        })

        const res = await window.axios.post('/api/public/student/subjects/assign', payload)
        alert(res.data.message || 'Subjects assigned successfully')
        editing.value = false
        await loadData()
    } catch (e) {
        alert(e.response?.data?.message || 'Something went wrong')
    } finally {
        submitting.value = false
    }
}

watch(fourthSubjectId, (id) => {
    if (!id || !subjectAssignData.value) {
        mainSubjectsSelectionList.value = []
        return
    }

    exceptSubjects.value = [String(id)]
    selectionDisabled.value = false
    subjectLoader.value = true

    const details = Array.isArray(subjectAssignData.value.details) ? subjectAssignData.value.details : []
    const current4th = details.find(e => String(e.subject_id) === String(id))

    const mainList = details.filter(e => 
        e.main_subject && 
        String(e.subject_id) !== String(id) &&
        (!current4th?.except_subject_id || String(e.subject_id) !== String(current4th.except_subject_id))
    )

    // Reset checked state
    const formatted = mainList.map(e => ({ ...e, checked: false }))

    setTimeout(() => {
        mainSubjectsSelectionList.value = formatted
        subjectLoader.value = false
    }, 300)
})

onMounted(() => {
    loadData()
})
</script>

<style scoped>
/* Any custom styles if needed */
</style>
