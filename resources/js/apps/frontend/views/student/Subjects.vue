<template>
    <div class="rounded-sm border border-slate-300 bg-white shadow-sm overflow-hidden">
        <div class="border-b border-slate-200 bg-slate-50/50 p-4 flex items-center justify-between">
            <h2 class="text-lg font-bold text-slate-800">My Subjects</h2>
            <button 
                v-if="!editing"
                class="text-xs font-bold text-emerald-600 hover:text-emerald-700"
                @click="startEdit"
            >
                Change Subjects
            </button>
        </div>
        <div class="p-6">
            <div v-if="loading" class="text-center py-10">Loading subjects...</div>
            <div v-else-if="!assignedSubjects.length && !editing" class="text-center py-10 text-slate-400">
                You haven't selected any subjects yet.
                <button class="mt-4 block mx-auto rounded-sm border border-emerald-600 px-4 py-2 text-xs font-bold text-emerald-600 hover:bg-emerald-50" @click="startEdit">Select Subjects Now</button>
            </div>
            
            <div v-else-if="!editing" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div v-for="sub in assignedSubjects" :key="sub.id" class="flex items-center gap-3 rounded-sm border border-slate-100 p-4 bg-slate-50/30">
                    <div class="h-8 w-8 flex items-center justify-center rounded-full bg-white border border-slate-200 text-xs font-bold text-slate-600 shadow-sm">
                        {{ sub.subject_name?.charAt(0) }}
                    </div>
                    <div>
                        <div class="text-sm font-bold text-slate-900">{{ sub.subject_name }}</div>
                        <div class="text-xs text-slate-500">{{ sub.main_subject ? 'Main Subject' : 'Elective' }}</div>
                    </div>
                </div>
            </div>

            <div v-else class="space-y-6">
                <div class="bg-amber-50 border border-amber-100 p-4 rounded-sm text-xs text-amber-900">
                    <strong>Note:</strong> Please select your subjects according to your academic plan.
                </div>
                
                <div class="space-y-3">
                    <div v-for="group in availableGroups" :key="group.id" class="border border-slate-200 rounded-sm p-4">
                        <div class="text-sm font-bold text-slate-900 mb-3">{{ group.name }}</div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <label v-for="sub in group.subjects" :key="sub.id" class="flex items-center gap-3 cursor-pointer p-2 rounded-sm hover:bg-slate-50 border border-transparent hover:border-slate-100">
                                <input type="checkbox" v-model="selectedSubjectIds" :value="sub.id" class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" />
                                <span class="text-sm text-slate-700">{{ sub.name }}</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 border-t border-slate-100 pt-6">
                    <button class="rounded-sm border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="editing = false">Cancel</button>
                    <button class="rounded-sm bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700" :disabled="saving" @click="save">
                        {{ saving ? 'Saving...' : 'Save Subjects' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { inject, ref, computed } from 'vue'

// In reality we would fetch these from APIs, but let's assume parent provides basic state
const assignedSubjects = inject('studentAssignedSubjects', [])
const loading = inject('studentSubjectsLoading', false)

const editing = ref(false)
const saving = ref(false)
const selectedSubjectIds = ref([])

const availableGroups = ref([
    { id: 1, name: 'Compulsory Subjects', subjects: [{ id: 1, name: 'Bangla' }, { id: 2, name: 'English' }, { id: 3, name: 'ICT' }] },
    { id: 2, name: 'Elective Subjects', subjects: [{ id: 4, name: 'Physics' }, { id: 5, name: 'Chemistry' }, { id: 6, name: 'Biology' }, { id: 7, name: 'Higher Math' }] }
])

const startEdit = () => {
    selectedSubjectIds.value = assignedSubjects.value.map(s => s.subject_id)
    editing.value = true
}

const save = async () => {
    saving.value = true
    try {
        // Mock save
        await new Promise(r => setTimeout(r, 1000))
        editing.value = false
    } finally {
        saving.value = false
    }
}
</script>
