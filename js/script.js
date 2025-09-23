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
    if (!form.checkValidity()) return; 

    // Fill form info
    const formInfo = document.getElementById('formInfo');
    formInfo.innerHTML = `
        <p><strong>Name:</strong> ${document.getElementById('name').value}</p>
        <p><strong>Email:</strong> ${document.getElementById('email').value}</p>
        <p><strong>Subject:</strong> ${document.getElementById('subject').value}</p>
        <p><strong>Message:</strong> ${document.getElementById('message').value}</p>
    `;

    formInfo.style.display = 'none';

    // Show modal
    const modal = document.getElementById('successModal');
    modal.style.display = 'flex';
    form.reset();
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


// Reservation Booking Submit to Database
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('reservationForm');
    const feedback = document.getElementById('reservationFeedback');

    form.addEventListener('submit', function(e) {
        form.addEventListener('submit', function(e) {
    e.preventDefault(); // prevent normal form submission

    const formData = new FormData(form);

    fetch('reserve.php', {  
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        feedback.textContent = data.message;  // always show PHP message
        feedback.style.color = data.success ? 'green' : 'red';
        console.log(data); // <-- log full JSON to see DB errors
        if (data.success) form.reset();
    })
    .catch(err => {
        feedback.textContent = "Error submitting reservation.";
        feedback.style.color = 'red';
        console.error(err);
    });
});
 
    });
});
