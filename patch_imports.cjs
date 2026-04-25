const fs = require('fs');
const frontendAppConfig = 'c:\\laragon\\www\\New folder\\ErpNew\\resources\\js\\apps\\frontend\\FrontendApp.vue';

let content = fs.readFileSync(frontendAppConfig, 'utf8');

const importReplacement = `import OnlineAdmissionForm from './views/OnlineAdmission/Form.vue'
import OnlineAdmissionPayment from './views/OnlineAdmission/Payment.vue'
import OnlineAdmissionInvoice from './views/OnlineAdmission/Invoice.vue'
import OnlineAdmissionDownloadForm from './views/OnlineAdmission/DownloadForm.vue'
import StudentLogin from './views/auth/StudentLogin.vue'
`;

const componentsReplacement = `components: {
        OnlineAdmissionForm,
        OnlineAdmissionPayment,
        OnlineAdmissionInvoice,
        OnlineAdmissionDownloadForm,
`;

content = content.replace(`import StudentLogin from './views/auth/StudentLogin.vue'\n`, importReplacement);
content = content.replace(`components: {\n`, componentsReplacement);

fs.writeFileSync(frontendAppConfig, content);
console.log('Successfully injected Online Admission imports into FrontendApp!');
