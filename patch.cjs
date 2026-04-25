const fs = require('fs');
const frontendAppConfig = 'c:\\laragon\\www\\New folder\\ErpNew\\resources\\js\\apps\\frontend\\FrontendApp.vue';

let content = fs.readFileSync(frontendAppConfig, 'utf8');

const startMarker = `<div v-if="path === '/online-admission'" class="mt-3 rounded-sm border border-slate-300 bg-white p-4 sm:p-6">`;
const endMarker = `<div v-else-if="path === '/apply-fees'" class="mt-3 rounded-sm border border-slate-300 bg-white p-4 sm:p-6">`;

const startIdx = content.indexOf(startMarker);
const endIdx = content.indexOf(endMarker);

if(startIdx !== -1 && endIdx !== -1) {
    const replacement = `            <OnlineAdmissionForm v-if="path === '/online-admission'" />
            <OnlineAdmissionPayment v-else-if="path === '/online-admission-payment'" />
            <OnlineAdmissionInvoice v-else-if="path === '/online-admission-invoice'" />
            <OnlineAdmissionDownloadForm v-else-if="path === '/online-admission-download-form'" />
            
            `;

    content = content.substring(0, startIdx) + replacement + content.substring(endIdx);
    fs.writeFileSync(frontendAppConfig, content);
    console.log('Successfully replaced Online Admission logic in FrontendApp!');
} else {
    console.log('Markers not found!');
}
