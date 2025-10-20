// ===================== Sidebar Toggle =====================
//  This function toggles the visibility state of a sidebar by adding or removing a CSS class, 
//  updates icon visibility accordingly, and saves the collapsed state in localStorage. This ensures 
//  the sidebar's open or closed state persists across sessions.
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const iconExpand = document.getElementById('icon-expand');
    const iconCollapse = document.getElementById('icon-collapse');

    if (!sidebar) return;

    const isClosed = sidebar.classList.toggle('close');

    // Handle icons
    if (iconExpand && iconCollapse) {
        iconExpand.classList.toggle('hidden', !isClosed);
        iconCollapse.classList.toggle('hidden', isClosed);
    }

    // Save state
    localStorage.setItem('sidebarCollapsed', isClosed ? 'true' : 'false');
}

//  This code runs when the page finishes loading and checks if the sidebar should be 
//  collapsed based on a value saved in localStorage. It applies the appropriate CSS class 
//  to the sidebar and toggles the visibility of expand/collapse icons to reflect the saved 
//  state.
window.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('sidebar');

    if (!sidebar) return;

    // Apply saved state
    const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    if (isCollapsed) {
        sidebar.classList.add('close');
    }

    // Show correct icon on load
    const iconExpand = document.getElementById('icon-expand');
    const iconCollapse = document.getElementById('icon-collapse');
    if (isCollapsed) {
        iconExpand?.classList.remove('hidden');
        iconCollapse?.classList.add('hidden');
    } else {
        iconExpand?.classList.add('hidden');
        iconCollapse?.classList.remove('hidden');
    }
});

// ===================== Feedback Form Functions =====================
//  This function intercepts form submission, prevents the default behavior, and displays the 
//  submitted form data in a hidden container before showing a success modal. It also resets the 
//  form after submission.
function handleFormSubmit(event) {
    event.preventDefault();

    const form = event.target;
    if (!form.checkValidity()) return;

    const formInfo = document.getElementById('formInfo');
    if (formInfo) {
        formInfo.innerHTML = `
            <p><strong>Name:</strong> ${document.getElementById('name').value}</p>
            <p><strong>Email:</strong> ${document.getElementById('email').value}</p>
            <p><strong>Subject:</strong> ${document.getElementById('subject').value}</p>
            <p><strong>Message:</strong> ${document.getElementById('message').value}</p>
        `;
        formInfo.style.display = 'none';
    }

    const modal = document.getElementById('successModal');
    if (modal) modal.style.display = 'flex';

    form.reset();
}

// ===================== Page Load =====================
//  This script sets up event listeners once the DOM is loaded: it handles form submission and toggle 
//  behavior for displaying form info, manages the display of a success modal including closing it by 
//  clicking outside or on a close button, and implements a theme toggle with persistent state using 
//  localStorage. It also applies the saved theme preference when the page loads.
document.addEventListener('DOMContentLoaded', () => {

    // ---- Feedback Form Listeners ----
    const feedbackForm = document.getElementById('feedback-form');
    if (feedbackForm) feedbackForm.addEventListener('submit', handleFormSubmit);

    const toggleInfoBtn = document.getElementById('toggleInfoBtn');
    if (toggleInfoBtn) {
        toggleInfoBtn.addEventListener('click', () => {
            const formInfo = document.getElementById('formInfo');
            if (formInfo) {
                formInfo.style.display = formInfo.style.display === 'none' ? 'block' : 'none';
            }
        });
    }

    const closeModal = document.getElementById('closeModal');
    if (closeModal) {
        closeModal.addEventListener('click', () => {
            const modal = document.getElementById('successModal');
            if (modal) modal.style.display = 'none';
        });
    }

    // Close modal if click outside modal content
    window.addEventListener('click', (e) => {
        const modal = document.getElementById('successModal');
        if (modal && e.target === modal) {
            modal.style.display = 'none';
        }
    });

    // ---- Theme Toggle ----
    const themeBtn = document.getElementById('toggle-theme');
    if (themeBtn) {
        themeBtn.addEventListener('click', () => {
            document.body.classList.toggle('dark');
            localStorage.setItem(
                'theme',
                document.body.classList.contains('dark') ? 'dark' : 'light'
            );
        });
    }

    // Apply saved theme
    if (localStorage.getItem('theme') === 'dark') {
        document.body.classList.add('dark');
    }

});
