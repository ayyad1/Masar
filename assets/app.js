'use strict';

const API_BASE = '../api';
const messageBox = document.querySelector('#page-message');

function showMessage(message) {
    if (!messageBox) return;
    messageBox.textContent = message;
    messageBox.hidden = false;
}

function hideMessage() {
    if (!messageBox) return;
    messageBox.textContent = '';
    messageBox.hidden = true;
}

function getQueryParam(name) {
    return new URLSearchParams(window.location.search).get(name);
}

function escapeHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

async function loadJobs() {
    const list = document.querySelector('#jobs-list');
    if (!list) return;

    try {
        const response = await fetch(`${API_BASE}/jobs.php`);
        const result = await response.json();
        if (!result.success) throw new Error(result.message);

        if (!result.data.length) {
            showMessage('لا توجد وظائف منشورة حاليًا.');
            return;
        }

        result.data.forEach((job) => {
            const card = document.createElement('article');
            card.className = 'job-card';
            card.innerHTML = `
                <h3>${escapeHtml(job.title)}</h3>
                <p><strong>الشركة:</strong> ${escapeHtml(job.company)}</p>
                <p><strong>الموقع:</strong> ${escapeHtml(job.location)}</p>
                <p>${escapeHtml(job.description).slice(0, 140)}...</p>
                <a class="button" href="job.html?id=${Number(job.id)}">عرض التفاصيل</a>
            `;
            list.appendChild(card);
        });
    } catch (error) {
        showMessage('تعذر تحميل الوظائف. تأكد من تشغيل PHP API.');
        console.error(error);
    }
}

async function loadJobDetails() {
    const details = document.querySelector('#job-details');
    if (!details) return;

    const id = getQueryParam('id');
    if (!id || !/^\d+$/.test(id)) {
        showMessage('معرف الوظيفة غير صالح.');
        return;
    }

    try {
        const response = await fetch(`${API_BASE}/jobs.php?id=${Number(id)}`);
        const result = await response.json();
        if (!result.success) throw new Error(result.message);
        const job = result.data;

        details.innerHTML = `
            <h1>${escapeHtml(job.title)}</h1>
            <p><strong>الشركة:</strong> ${escapeHtml(job.company)}</p>
            <p><strong>الموقع:</strong> ${escapeHtml(job.location)}</p>
            <p><strong>نوع العمل:</strong> ${escapeHtml(job.employment_type)}</p>
            <h2>الوصف</h2><p>${escapeHtml(job.description)}</p>
            <h2>المتطلبات</h2><p>${escapeHtml(job.requirements)}</p>
            <a class="button" href="apply.html?job_id=${Number(job.id)}">قدّم الآن</a>
        `;
    } catch (error) {
        showMessage('تعذر تحميل تفاصيل الوظيفة.');
        console.error(error);
    }
}

async function loadApplicationJob() {
    const form = document.querySelector('#application-form');
    if (!form) return;

    const id = getQueryParam('job_id');
    const hiddenId = document.querySelector('#job_id');
    const title = document.querySelector('#job-title');
    if (!id || !/^\d+$/.test(id)) {
        showMessage('معرف الوظيفة غير صالح.');
        return;
    }

    hiddenId.value = Number(id);
    const response = await fetch(`${API_BASE}/jobs.php?id=${Number(id)}`);
    const result = await response.json();
    if (!result.success) {
        showMessage('الوظيفة غير موجودة.');
        return;
    }
    title.textContent = `${result.data.title} - ${result.data.company}`;
}

async function submitApplication(event) {
    event.preventDefault();
    hideMessage();

    const form = event.currentTarget;
    const file = document.querySelector('#cv').files[0];
    if (!file || file.size > 5 * 1024 * 1024) {
        showMessage('يرجى اختيار CV صالح لا يتجاوز 5 MB.');
        return;
    }

    try {
        const response = await fetch(`${API_BASE}/submit_application.php`, {
            method: 'POST',
            body: new FormData(form)
        });
        const result = await response.json();
        if (!result.success) throw new Error(result.errors?.join(' ') || result.message);
        window.location.href = 'success.html';
    } catch (error) {
        showMessage(error.message || 'تعذر إرسال الطلب.');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    loadJobs();
    loadJobDetails();
    loadApplicationJob();
    document.querySelector('#application-form')?.addEventListener('submit', submitApplication);
});
