const sidebar = document.querySelector('[data-app-sidebar]');
const sidebarOverlay = document.querySelector('[data-sidebar-overlay]');

const setSidebarOpen = (isOpen) => {
    if (!sidebar || !sidebarOverlay) {
        return;
    }

    sidebar.classList.toggle('-translate-x-full', !isOpen);
    sidebar.classList.toggle('translate-x-0', isOpen);
    sidebarOverlay.classList.toggle('hidden', !isOpen);
    document.body.classList.toggle('overflow-hidden', isOpen);
};

document.querySelectorAll('[data-sidebar-open]').forEach((button) => {
    button.addEventListener('click', () => setSidebarOpen(true));
});

document.querySelectorAll('[data-sidebar-close]').forEach((button) => {
    button.addEventListener('click', () => setSidebarOpen(false));
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        setSidebarOpen(false);
    }
});

window.addEventListener('resize', () => {
    if (window.innerWidth >= 1024) {
        setSidebarOpen(false);
    }
});

document.querySelectorAll('form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (!window.confirm(form.dataset.confirm)) {
            event.preventDefault();
        }
    });
});

document.querySelectorAll('[data-copy-password]').forEach((button) => {
    button.addEventListener('click', async () => {
        const password = document.querySelector('[data-temporary-password]')?.textContent?.trim();

        if (!password) {
            return;
        }

        try {
            await navigator.clipboard.writeText(password);
            button.textContent = 'Senha copiada';
        } catch {
            const passwordElement = document.querySelector('[data-temporary-password]');
            const selection = window.getSelection();
            const range = document.createRange();

            range.selectNodeContents(passwordElement);
            selection.removeAllRanges();
            selection.addRange(range);
            button.textContent = 'Senha selecionada';
        }
    });
});

const activityDate = document.querySelector('[data-activity-date]');
const movementDate = document.querySelector('[data-movement-date]');

if (activityDate && movementDate) {
    let movementDateWasEdited = movementDate.value !== '';

    movementDate.addEventListener('change', () => {
        movementDateWasEdited = true;
    });

    activityDate.addEventListener('change', () => {
        if (!movementDateWasEdited) {
            movementDate.value = activityDate.value;
        }

        movementDate.min = activityDate.value;
    });
}

const statusSelect = document.querySelector('[data-activity-status]');
const waitingFields = document.querySelector('[data-waiting-fields]');

const updateWaitingFields = () => {
    if (!statusSelect || !waitingFields) {
        return;
    }

    const isWaiting = statusSelect.value === 'aguardando';
    waitingFields.classList.toggle('hidden', !isWaiting);
    waitingFields.classList.toggle('contents', isWaiting);
    waitingFields.querySelectorAll('input, select').forEach((field) => {
        field.required = isWaiting;
    });
};

statusSelect?.addEventListener('change', updateWaitingFields);
updateWaitingFields();

const deadline = document.querySelector('[data-deadline]');
const deadlineWarning = document.querySelector('[data-deadline-warning]');

const updateDeadlineWarning = () => {
    if (!deadline || !deadlineWarning) {
        return;
    }

    deadlineWarning.classList.toggle('hidden', deadline.value === '' || deadline.value <= deadline.dataset.planEnd);
};

deadline?.addEventListener('change', updateDeadlineWarning);
updateDeadlineWarning();

document.querySelectorAll('[data-print-report]').forEach((button) => {
    button.addEventListener('click', () => window.print());
});
