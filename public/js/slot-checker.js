/**
 * AJAX Slot Checker — Client-side JavaScript Snippet
 * ===================================================
 * FCI Lab Management System
 *
 * Dynamically checks whether a lab room is available for a given
 * date + time window by calling the backend API endpoint:
 *
 *   GET /api/slot-checker?lab_id=X&date=Y&start_time=Z&end_time=W
 *
 * Expected response format:
 *   { "available": true }   — slot is free
 *   { "available": false }  — slot has a conflict
 *
 * Usage example:
 *   checkSlotAvailability(1, '2026-03-15', '08:00', '10:00')
 *     .then(result => {
 *       if (result.available) {
 *         console.log('Slot is free — proceed.');
 *       } else {
 *         console.log('Slot is occupied — pick another time.');
 *       }
 *     });
 */

/**
 * Query the server for slot availability.
 *
 * @param {number|string} labId      - The laboratory ID (e.g. 1)
 * @param {string}        date       - The target date in YYYY-MM-DD format
 * @param {string}        startTime  - Start time in HH:mm format (e.g. "08:00")
 * @param {string}        endTime    - End time in HH:mm format (e.g. "10:00")
 * @returns {Promise<{available: boolean}>} Resolves with the availability result
 */
async function checkSlotAvailability(labId, date, startTime, endTime) {
    const queryParams = new URLSearchParams({
        lab_id: labId,
        date: date,
        start_time: startTime,
        end_time: endTime,
    });

    const response = await fetch(`/api/slot-checker?${queryParams.toString()}`);

    if (!response.ok) {
        // If the server returns an error status, treat as unavailable by default
        console.error('Slot checker API returned status:', response.status);
        return { available: false };
    }

    const data = await response.json();

    // Expected structure: { available: true } or { available: false }
    return data;
}

// ─── Live Example: Bind to a form or input change event ─────────────────
// Uncomment and adapt the block below to wire it into your Blade templates.
//
// document.addEventListener('DOMContentLoaded', () => {
//     const labSelect   = document.getElementById('lab_id');
//     const dateInput   = document.getElementById('booking_date');
//     const startSelect = document.getElementById('start_time');
//     const endSelect   = document.getElementById('end_time');
//     const statusLabel = document.getElementById('slot-status');
//
//     async function refreshAvailability() {
//         const labId = labSelect?.value;
//         const date  = dateInput?.value;
//         const start = startSelect?.value;
//         const end   = endSelect?.value;
//
//         if (!labId || !date || !start || !end) return;
//
//         const result = await checkSlotAvailability(labId, date, start, end);
//
//         if (statusLabel) {
//             statusLabel.textContent = result.available
//                 ? '✔ Slot is available'
//                 : '✘ Slot is already occupied';
//             statusLabel.className = result.available
//                 ? 'text-success'
//                 : 'text-danger';
//         }
//     }
//
//     [labSelect, dateInput, startSelect, endSelect].forEach(el => {
//         el?.addEventListener('change', refreshAvailability);
//     });
// });