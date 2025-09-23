function toggleSidebar() {
  const sidebar = document.getElementById('sidebar');
  const iconExpand = document.getElementById('icon-expand');
  const iconCollapse = document.getElementById('icon-collapse');

  sidebar.classList.toggle('close');
  iconExpand.classList.toggle('hidden');
  iconCollapse.classList.toggle('hidden');
}

// Feedback Form Functions

function handleFormSubmit(event) {
    event.preventDefault();

    const form = event.target;

    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const formData = new FormData(form);

    fetch(form.action || window.location.href, {
        method: 'POST',
        body: formData,
    }).then(response => response.json()).then(data => {
        if (data.success) {
            document.getElementById('successModal').style.display = 'flex';

            document.getElementById('formInfo').innerHTML = `
                <p><strong>Name:</strong> ${formData.get('name')}</p>
                <p><strong>Email:</strong> ${formData.get('email')}</p>
                <p><strong>Subject:</strong> ${formData.get('subject')}</p>
                <p><strong>Message:</strong> ${formData.get('message')}</p>
            `;

            form.reset();
        } else {
            alert("Submission failed:\n" + data.message);
        }
    }).catch(error => {
        console.error('Error submitting form:', error);
        alert('An error occurred submitting the form.');
    });
}

// Toggle form info
document.getElementById('toggleInfoBtn').addEventListener('click', () => {
    const formInfo = document.getElementById('formInfo');
    formInfo.style.display = (formInfo.style.display === 'none') ? 'block' : 'none';
});

// Close modal
document.getElementById('closeModal').addEventListener('click', () => {
    document.getElementById('successModal').style.display = 'none';
});

// Close if click outside modal content
window.addEventListener('click', (e) => {
    const modal = document.getElementById('successModal');
    if (e.target === modal) {
        modal.style.display = 'none';
    }
});

document.getElementById('feedback-form').addEventListener('submit', handleFormSubmit);