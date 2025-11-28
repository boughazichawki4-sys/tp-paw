// === Mise à jour des présences (remplace/analyse précédente) ===
function updateAttendance() {
  const tbody = document.querySelector('#attendanceTable tbody');
  if (!tbody) return;
  const rows = Array.from(tbody.querySelectorAll('tr'));

  rows.forEach(row => {
    const inputs = Array.from(row.querySelectorAll('input[type="checkbox"]'));
    if (inputs.length === 0) return; // skip non-data rows

    let absences = 0;
    let participations = 0;
    // inputs are ordered: Present, Participated, Present, Participated ...
    inputs.forEach((input, idx) => {
      if (idx % 2 === 0) {
        if (!input.checked) absences++;
      } else {
        if (input.checked) participations++;
      }
    });

    // Update cells (assume .abs and .par exist)
    const absCell = row.querySelector('.abs');
    const parCell = row.querySelector('.par');
    const msgCell = row.querySelector('.msg');
    if (absCell) absCell.textContent = absences;
    if (parCell) parCell.textContent = participations;

    // Apply color based on absences
    row.classList.remove('green', 'yellow', 'red');
    if (absences < 3) row.classList.add('green');
    else if (absences <= 4) row.classList.add('yellow');
    else row.classList.add('red');

    // Compose message
    let message = '';
    if (absences < 3) {
      message = (participations >= 4)
        ? 'Good attendance – Excellent participation'
        : 'Good attendance – You need to participate more';
    } else if (absences <= 4) {
      message = (participations >= 4)
        ? 'Warning – attendance low – Good participation'
        : 'Warning – attendance low – You need to participate more';
    } else {
      message = (participations >= 4)
        ? 'Excluded – too many absences – Good participation'
        : 'Excluded – too many absences – You need to participate more';
    }

    if (msgCell) {
      msgCell.textContent = `${message} (Absences: ${absences}, Participations: ${participations})`;
      msgCell.setAttribute('title', `${message} — Absences: ${absences}, Participations: ${participations}`);
    }
  });
}

// === Validation + ajout automatique d'un étudiant ===
// Validation patterns and helper
const patterns = {
  studentId: /^\d{8,}$/,
  lastName: /^[A-Za-zÀ-ÿ\s-]{2,}$/,
  firstName: /^[A-Za-zÀ-ÿ\s-]{2,}$/,
  email: /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/
};

const errorMessages = {
  studentId: 'Student ID must contain at least 8 numbers',
  lastName: 'Last name must be 2+ characters (letters, spaces, or hyphens)',
  firstName: 'First name must be 2+ characters (letters, spaces, or hyphens)',
  email: 'Please enter a valid email address (ex: user@domain.com)'
};

function validateField(field) {
  if (!field) return false;
  const value = field.value.trim();
  const pattern = patterns[field.id];
  const errorElement = document.getElementById(field.id + 'Error');
  
  // Check for empty value
  if (value === '') {
    if (errorElement) {
      errorElement.textContent = '❌ ' + errorMessages[field.id] || 'This field is required';
      errorElement.classList.add('show');
    }
    field.classList.add('invalid');
    return false;
  }

  let isValid = true;
  if (!pattern || !pattern.test(value)) {
    isValid = false;
    if (errorElement) {
      errorElement.textContent = '❌ ' + (errorMessages[field.id] || 'Invalid value');
      errorElement.classList.add('show');
    }
    field.classList.add('invalid');
  } else {
    if (errorElement) { 
      errorElement.textContent = ''; 
      errorElement.classList.remove('show'); 
    }
    field.classList.remove('invalid');
  }
  return isValid;
}

// Attach blur/input listeners for real-time validation
['studentId','lastName','firstName','email'].forEach(id => {
  const el = document.getElementById(id);
  if (el) {
    el.addEventListener('blur', () => validateField(el));
    el.addEventListener('input', () => validateField(el));
  }
});

document.getElementById('studentForm').addEventListener('submit', async function(event) {
  event.preventDefault();
  
  // Clear previous alerts and errors
  const formAlert = document.getElementById('formAlert');
  if (formAlert) {
    formAlert.classList.remove('show', 'alert-error', 'alert-success');
    formAlert.textContent = '';
  }
  document.querySelectorAll('.error-message').forEach(e => { 
    e.textContent = ''; 
    e.classList.remove('show'); 
  });

  const fields = ['studentId','lastName','firstName','email'];
  let allValid = true;
  let firstInvalidField = null;
  
  fields.forEach(id => {
    const input = document.getElementById(id);
    if (!validateField(input)) {
      allValid = false;
      if (!firstInvalidField) firstInvalidField = input;
    }
  });
  
  if (!allValid) {
    // Show error alert
    if (formAlert) {
      formAlert.textContent = '⚠️ Veuillez corriger les erreurs ci-dessous';
      formAlert.classList.add('show', 'alert-error');
    }
    if (firstInvalidField) firstInvalidField.focus();
    return;
  }

  const studentId = document.getElementById('studentId').value.trim();
  const lastName = document.getElementById('lastName').value.trim();
  const firstName = document.getElementById('firstName').value.trim();
  const email = document.getElementById('email').value.trim();

  // Disable submit button during processing
  const submitBtn = this.querySelector('button[type="submit"]');
  const originalText = submitBtn.textContent;
  submitBtn.disabled = true;
  submitBtn.textContent = '⏳ Enregistrement...';

  try {
    // Save to MySQL via API using studentId as matricule
    const formData = new FormData();
    formData.append('fullname', `${lastName} ${firstName}`);
    formData.append('matricule', studentId);
    formData.append('group_id', email); // Using email as group_id for now

    const response = await fetch('api_add_student.php', {
      method: 'POST',
      body: formData
    });

    const data = await response.json();

    if (!data.success) {
      if (formAlert) {
        formAlert.textContent = `❌ ${data.message || 'Erreur lors de l\'enregistrement'}`;
        formAlert.classList.add('show', 'alert-error');
      }
      submitBtn.disabled = false;
      submitBtn.textContent = originalText;
      return;
    }

    // Add to attendance table
    const tbody = document.querySelector('#attendanceTable tbody');
    if (tbody) {
      const row = document.createElement('tr');
      row.style.animation = 'slideIn 0.4s ease-out';

      // Build row: id, last, first, then 6 pairs of checkboxes, then abs/par/msg with classes
      let inner = `
        <td>${escapeHtml(studentId)}</td>
        <td>${escapeHtml(lastName)}</td>
        <td>${escapeHtml(firstName)}</td>`;
      for (let i = 0; i < 6; i++) {
        inner += `<td><input type="checkbox" aria-label="Session ${i + 1} Present"></td><td><input type="checkbox" aria-label="Session ${i + 1} Participated"></td>`;
      }
      inner += `<td class="abs" aria-label="Total Absences"></td><td class="par" aria-label="Total Participations"></td><td class="msg"></td>`;
      row.innerHTML = inner;

      tbody.insertBefore(row, tbody.firstChild); // Add at top

      // Add change listeners to the new checkboxes
      row.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.addEventListener('change', updateAttendance));

      updateAttendance();
    }

    // Show success message in form alert
    if (formAlert) {
      formAlert.textContent = `✅ ${firstName} ${lastName} ajouté avec succès! (Sauvegardé en BD MySQL)`;
      formAlert.classList.add('show', 'alert-success');
      
      // Notifier les autres pages (manage_students.php) via localStorage
      try {
        localStorage.setItem('newStudentAdded', JSON.stringify({
          fullname: `${lastName} ${firstName}`,
          matricule: studentId,
          group_id: email,
          timestamp: new Date().toISOString()
        }));
        localStorage.setItem('syncTrigger', Date.now().toString());
      } catch (e) {
        console.warn('localStorage non disponible:', e);
      }
      
      // Auto-hide after 4 seconds
      setTimeout(() => {
        formAlert.classList.remove('show');
      }, 4000);
    }
    
    // Reset form
    this.reset();
    
    // Clear error messages
    document.querySelectorAll('.error-message').forEach(e => e.classList.remove('show'));
    
    // Focus back on first field
    document.getElementById('studentId').focus();

  } catch (error) {
    if (formAlert) {
      formAlert.textContent = `❌ Erreur réseau: ${error.message}`;
      formAlert.classList.add('show', 'alert-error');
    }
    console.error('Error:', error);
  } finally {
    submitBtn.disabled = false;
    submitBtn.textContent = originalText;
  }
});

// === Helper function to escape HTML ===
function escapeHtml(text) {
  const map = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;'
  };
  return text.replace(/[&<>"']/g, m => map[m]);
}

// === Réagir à chaque modification ===
document.querySelectorAll("input[type='checkbox']").forEach(chk => {
  chk.addEventListener("change", updateAttendance);
});

// === Analyse au chargement ===
// update attendance on load
window.onload = updateAttendance;

// === jQuery: Highlight & Reset buttons ===
// Uses jQuery to find EXCELLENT students: many participations AND few/no absences
if (typeof $ !== 'undefined') {
  $(function() {
    $('#highlightBtn').on('click', function() {
      const rows = $('#attendanceTable tbody tr').filter(function() {
        const absText = $(this).find('.abs').text().trim();
        const parText = $(this).find('.par').text().trim();
        const abs = parseInt(absText, 10);
        const par = parseInt(parText, 10);
        
        // Excellent student: >= 4 participations AND <= 1 absence
        return !isNaN(abs) && !isNaN(par) && par >= 4 && abs <= 1;
      });

      if (rows.length === 0) {
        alert('⭐ No excellent students found! (Excellent = 4+ participations AND 0-1 absences)');
        return;
      }

      rows.each(function() {
        // simple fade animation (fade out / in / out / in)
        $(this).stop(true, true)
          .fadeTo(300, 0.25)
          .fadeTo(300, 1)
          .fadeTo(300, 0.25)
          .fadeTo(300, 1);

        // add a temporary background highlight (restored by reset)
        $(this).addClass('excellent-temp');
        setTimeout(() => $(this).removeClass('excellent-temp'), 1400);
      });

      // Show success message
      const successMsg = document.createElement('div');
      successMsg.className = 'success-message';
      successMsg.textContent = `⭐ ${rows.length} excellent student(s) highlighted!`;
      successMsg.style.animation = 'fadeOut 3s ease-in-out forwards';
      document.body.insertBefore(successMsg, document.body.firstChild);
      setTimeout(() => successMsg.remove(), 3000);
    });

    $('#resetBtn').on('click', function() {
      // Stop animations and clear inline opacity, remove temp class
      $('#attendanceTable tbody tr').each(function() {
        $(this).stop(true, true).css('opacity', '');
        $(this).removeClass('excellent-temp');
      });

  // Re-run the existing attendance analysis to restore green/yellow/red classes
  if (typeof updateAttendance === 'function') updateAttendance();
    });
  });
}

// === Search by name & Sorting (jQuery) ===
if (typeof $ !== 'undefined') {
  $(function() {
    const $tbody = $('#attendanceTable tbody');
    const $rows = () => $tbody.find('tr');

    // Search (filter) by first or last name
    $('#searchName').on('input', function() {
      const q = $(this).val().trim().toLowerCase();
      if (q === '') {
        $rows().show();
        return;
      }
      $rows().each(function() {
        const last = $(this).find('td:nth-child(2)').text().toLowerCase();
        const first = $(this).find('td:nth-child(3)').text().toLowerCase();
        const match = last.indexOf(q) !== -1 || first.indexOf(q) !== -1;
        $(this).toggle(match);
      });
    });

    function updateSortStatus(text) {
      $('#sortStatus').text(text);
    }

    // Sort by absences ascending
    $('#sortAbsBtn').on('click', function() {
      const rowsArray = $rows().filter(':visible').get();
      rowsArray.sort((a, b) => {
        const aVal = parseInt($(a).find('.abs').text(), 10) || 0;
        const bVal = parseInt($(b).find('.abs').text(), 10) || 0;
        return aVal - bVal;
      });
      // re-append in order
      rowsArray.forEach(r => $tbody.append(r));
      updateSortStatus('Currently sorted by absences (ascending)');
    });

    // Sort by participation descending
    $('#sortParBtn').on('click', function() {
      const rowsArray = $rows().filter(':visible').get();
      rowsArray.sort((a, b) => {
        const aVal = parseInt($(a).find('.par').text(), 10) || 0;
        const bVal = parseInt($(b).find('.par').text(), 10) || 0;
        return bVal - aVal;
      });
      rowsArray.forEach(r => $tbody.append(r));
      updateSortStatus('Currently sorted by participation (descending)');
    });
  });
}

// === Generate Attendance Report + Donut Chart ===
// === Generate Attendance Report (texte seulement) ===
document.addEventListener("DOMContentLoaded", () => {
  const reportBtn = document.getElementById("showReport");
  const reportOutput = document.getElementById("reportOutput");
  const ctx = document.getElementById("reportChart");
  let reportChart = null;

  if (reportBtn) {
    reportBtn.addEventListener("click", () => {
      const rows = document.querySelectorAll("#attendanceTable tbody tr");
      let totalStudents = 0, studentsPresent = 0, studentsParticipated = 0;

      rows.forEach((row) => {
        totalStudents++;
        const inputs = Array.from(row.querySelectorAll('input[type="checkbox"]'));
        let hasPresent = false;
        let hasParticipated = false;
        for (let i = 0; i < inputs.length; i += 2) {
          if (inputs[i] && inputs[i].checked) hasPresent = true;
          if (inputs[i + 1] && inputs[i + 1].checked) hasParticipated = true;
        }
        if (hasPresent) studentsPresent++;
        if (hasParticipated) studentsParticipated++;
      });

      if (reportOutput) {
        reportOutput.innerHTML = `
          🧾 <b>Attendance Overview</b><br>
          Total students: ${totalStudents}<br>
          Students present (≥1 session): ${studentsPresent}<br>
          Students participated (≥1 session): ${studentsParticipated}
        `;
      }

      // Show bar chart with the three counts
      if (typeof Chart !== 'undefined' && ctx) {
        if (reportChart) reportChart.destroy();
        reportChart = new Chart(ctx, {
          type: 'bar',
          data: {
            labels: ['Total students','Students present','Students participated'],
            datasets: [{
              label: 'Count',
              data: [totalStudents, studentsPresent, studentsParticipated],
              backgroundColor: ['#6b7280','#10b981','#3b82f6']
            }]
          },
          options: {
            responsive: true,
            plugins: { legend: { display: false }, title: { display: true, text: 'Attendance Overview' } },
            scales: { y: { beginAtZero: true, precision: 0 } }
          }
        });
      }
    });
  }
});

// === EXERCISE 5 : jQuery Hover & Click (delegated handlers) ===
if (typeof $ !== 'undefined') {
  $(function() {
    // delegate hover (mouseenter / mouseleave) and click to tbody rows
    $('#attendanceTable')
      .on('mouseenter', 'tbody tr', function() { $(this).addClass('highlight'); })
      .on('mouseleave', 'tbody tr', function() { $(this).removeClass('highlight'); })
      .on('click', 'tbody tr', function() {
        const lastName = $(this).find('td:nth-child(2)').text().trim();
        const firstName = $(this).find('td:nth-child(3)').text().trim();
        const absText = $(this).find('.abs').text().trim();
        const abs = parseInt(absText, 10);
        const absDisplay = isNaN(abs) ? 0 : abs;
        alert(`👩‍🎓 Student: ${firstName} ${lastName}\n📘 Absences: ${absDisplay}`);
      });
  });
}
