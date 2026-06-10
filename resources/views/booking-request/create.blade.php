<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lab Booking Request — FCI Lab Management System</title>

    {{-- Bootstrap 5 CDN --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    {{-- Google Font --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --brand-primary: #1a56db;
            --brand-dark:    #1e2a4a;
            --brand-light:   #f0f4ff;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #e8eeff 0%, #f5f7ff 60%, #eef2ff 100%);
            min-height: 100vh;
        }

        /* ── Top Navigation Bar ── */
        .topbar {
            background: var(--brand-dark);
            padding: 0.85rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.18);
        }
        .topbar .brand-logo {
            width: 36px;
            height: 36px;
            background: var(--brand-primary);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 18px;
        }
        .topbar .brand-name {
            color: #fff;
            font-weight: 700;
            font-size: 1rem;
            letter-spacing: 0.3px;
        }
        .topbar .brand-sub {
            color: rgba(255,255,255,0.5);
            font-size: 0.75rem;
        }

        /* ── Hero Banner ── */
        .hero {
            background: linear-gradient(135deg, var(--brand-dark) 0%, #1a56db 100%);
            color: #fff;
            padding: 3rem 1rem 2.5rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        .hero h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            position: relative;
        }
        .hero p {
            font-size: 1rem;
            opacity: 0.85;
            max-width: 520px;
            margin: 0 auto;
            position: relative;
        }

        /* ── Card ── */
        .request-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(26,86,219,0.10), 0 1.5px 6px rgba(0,0,0,0.06);
            overflow: hidden;
            border: 1px solid rgba(26,86,219,0.08);
        }
        .card-head {
            background: var(--brand-light);
            border-bottom: 1px solid rgba(26,86,219,0.1);
            padding: 1.25rem 1.75rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .card-head-icon {
            width: 38px;
            height: 38px;
            background: var(--brand-primary);
            color: #fff;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }
        .card-head h5 {
            margin: 0;
            font-weight: 600;
            color: var(--brand-dark);
            font-size: 1rem;
        }
        .card-head p {
            margin: 0;
            font-size: 0.8rem;
            color: #6b7280;
        }

        /* ── Form Styling ── */
        .form-label {
            font-weight: 500;
            font-size: 0.875rem;
            color: #374151;
            margin-bottom: 5px;
        }
        .form-control, .form-select {
            border: 1.5px solid #d1d5db;
            border-radius: 8px;
            padding: 0.55rem 0.85rem;
            font-size: 0.9rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--brand-primary);
            box-shadow: 0 0 0 3px rgba(26,86,219,0.12);
        }
        textarea.form-control { resize: vertical; min-height: 100px; }

        /* ── Time Slot Unavailable Styling ── */
        select option:disabled {
            color: #9ca3af !important;
            background: #f3f4f6 !important;
            font-style: italic;
        }
        .time-select-wrapper {
            position: relative;
        }
        .availability-badge {
            display: none;
            font-size: 0.73rem;
            margin-top: 5px;
            gap: 8px;
        }
        .availability-badge.show {
            display: flex;
            flex-wrap: wrap;
        }
        .slot-chip {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 0.7rem;
            font-weight: 500;
        }
        .slot-chip.occupied   { background: #fee2e2; color: #991b1b; }
        .slot-chip.available  { background: #d1fae5; color: #065f46; }

        /* ── Availability Spinner ── */
        #availability-spinner {
            display: none;
            font-size: 0.8rem;
            color: var(--brand-primary);
            align-items: center;
            gap: 6px;
        }
        #availability-spinner.show { display: flex; }

        /* ── Submit Button ── */
        .btn-submit {
            background: linear-gradient(135deg, var(--brand-primary), #1d4ed8);
            color: #fff;
            border: none;
            border-radius: 9px;
            padding: 0.7rem 2rem;
            font-weight: 600;
            font-size: 0.95rem;
            transition: transform 0.15s, box-shadow 0.15s;
            box-shadow: 0 4px 15px rgba(26,86,219,0.25);
        }
        .btn-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(26,86,219,0.35);
            color: #fff;
        }

        /* ── Section Divider ── */
        .section-title {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: var(--brand-primary);
            border-bottom: 2px solid var(--brand-light);
            padding-bottom: 6px;
            margin-bottom: 1rem;
        }

        /* ── Alert ── */
        .alert-success-custom {
            background: #d1fae5;
            border-left: 4px solid #10b981;
            border-radius: 8px;
            color: #065f46;
            padding: 1rem 1.25rem;
        }
        .alert-danger-custom {
            background: #fee2e2;
            border-left: 4px solid #ef4444;
            border-radius: 8px;
            color: #991b1b;
            padding: 1rem 1.25rem;
        }
    </style>
</head>

<body>

{{-- ── Top Bar ── --}}
<div class="topbar">
    <div class="brand-logo"><i class="fas fa-flask"></i></div>
    <div>
        <div class="brand-name">FCI Lab Management System</div>
        <div class="brand-sub">Faculty of Computer &amp; Information Sciences</div>
    </div>
</div>

{{-- ── Hero Banner ── --}}
<div class="hero">
    <h1><i class="fas fa-calendar-plus me-2"></i>Lab Booking Request</h1>
    <p>Fill in the form below to request a computer laboratory reservation. Your request will be reviewed by the administrator.</p>
</div>

{{-- ── Main Content ── --}}
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-7">

            {{-- Success Flash --}}
            @if (session('success'))
                <div class="alert-success-custom mb-4 d-flex align-items-start gap-3">
                    <i class="fas fa-check-circle fa-lg mt-1"></i>
                    <div>
                        <strong>Request Submitted!</strong><br>
                        {{ session('success') }}
                    </div>
                </div>
            @endif

            {{-- Error Flash --}}
            @if ($errors->any())
                <div class="alert-danger-custom mb-4">
                    <strong><i class="fas fa-exclamation-circle me-1"></i>Please fix the following errors:</strong>
                    <ul class="mb-0 mt-2 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Form Card --}}
            <div class="request-card">
                <div class="card-head">
                    <div class="card-head-icon"><i class="fas fa-file-alt"></i></div>
                    <div>
                        <h5>Booking Request Form</h5>
                        <p>All fields marked with <span class="text-danger">*</span> are required.</p>
                    </div>
                </div>

                <form action="{{ url('/booking-request') }}" method="POST" class="p-4" id="bookingForm">
                    @csrf



                    {{-- ── Section: Booking Details ── --}}
                    <p class="section-title"><i class="fas fa-door-open me-1"></i>Booking Details</p>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label" for="lab_id">Laboratory / Room <span class="text-danger">*</span></label>
                            <select name="lab_id" id="lab_id"
                                    class="form-select @error('lab_id') is-invalid @enderror" required>
                                <option value="">— Select a laboratory —</option>
                                @foreach($laboratories as $lab)
                                    <option value="{{ $lab->id }}"
                                        {{ old('lab_id') == $lab->id ? 'selected' : '' }}>
                                        {{ $lab->lab_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('lab_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="date">Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" id="date"
                                   class="form-control @error('date') is-invalid @enderror"
                                   value="{{ old('date') }}"
                                   min="{{ date('Y-m-d') }}" required>
                            @error('date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- ── Availability Status ── --}}
                    <div id="availability-spinner" class="mb-2">
                        <div class="spinner-border spinner-border-sm" role="status"></div>
                        <span>Checking availability...</span>
                    </div>
                    <div class="availability-badge mb-3" id="availability-summary"></div>

                    {{-- ── Time Slots ── --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label" for="start_time">Start Time <span class="text-danger">*</span></label>
                            <div class="time-select-wrapper">
                                <select name="start_time" id="start_time"
                                        class="form-select @error('start_time') is-invalid @enderror" required>
                                    <option value="">— Select start time —</option>
                                    @for($h = 8; $h <= 17; $h++)
                                        @php $t = sprintf('%02d:00', $h); @endphp
                                        <option value="{{ $t }}"
                                            {{ old('start_time') == $t ? 'selected' : '' }}>
                                            {{ $t }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            @error('start_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="end_time">End Time <span class="text-danger">*</span></label>
                            <div class="time-select-wrapper">
                                <select name="end_time" id="end_time"
                                        class="form-select @error('end_time') is-invalid @enderror" required>
                                    <option value="">— Select end time —</option>
                                    @for($h = 9; $h <= 18; $h++)
                                        @php $t = sprintf('%02d:00', $h); @endphp
                                        <option value="{{ $t }}"
                                            {{ old('end_time') == $t ? 'selected' : '' }}>
                                            {{ $t }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            @error('end_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- ── Reason / Purpose ── --}}
                    <p class="section-title"><i class="fas fa-comment-alt me-1"></i>Purpose</p>
                    <div class="mb-4">
                        <label class="form-label" for="reason">Reason for Booking <span class="text-danger">*</span></label>
                        <textarea name="reason" id="reason" rows="4"
                                  class="form-control @error('reason') is-invalid @enderror"
                                  placeholder="Please describe the purpose of your booking (e.g., group study, project presentation, workshop...)"
                                  required>{{ old('reason') }}</textarea>
                        @error('reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- ── Submit ── --}}
                    <div class="d-flex align-items-center gap-3">
                        <button type="submit" class="btn btn-submit" id="submitBtn">
                            <i class="fas fa-paper-plane me-2"></i>Submit Request
                        </button>
                        <small class="text-muted">You will receive an email notification once reviewed.</small>
                    </div>
                </form>
            </div>

            {{-- Info Note --}}
            <div class="mt-3 text-center text-muted" style="font-size:0.8rem;">
                <i class="fas fa-info-circle me-1"></i>
                Operating hours: Monday – Saturday, 08:00 – 18:00.
                Requests are typically reviewed within 1 business day.
            </div>

        </div>
    </div>
</div>

{{-- Bootstrap 5 JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
{{-- jQuery --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script>
/**
 * ============================================================
 * AJAX Availability Checker
 * ============================================================
 * Fires when BOTH a laboratory AND a date have been selected.
 * Calls GET /api/booking-requests/availability to get blocked slots.
 * Disables (but does NOT remove) conflicting options in both
 * Start Time and End Time dropdowns, and adds "(Occupied)" label.
 * ============================================================
 */
$(function () {

    const CSRF_TOKEN    = $('meta[name="csrf-token"]').attr('content');
    const $labSelect    = $('#lab_id');
    const $dateInput    = $('#date');
    const $startSelect  = $('#start_time');
    const $endSelect    = $('#end_time');
    const $spinner      = $('#availability-spinner');
    const $summary      = $('#availability-summary');

    // All possible hours as canonical strings
    const START_HOURS = ['08:00','09:00','10:00','11:00','12:00','13:00','14:00','15:00','16:00','17:00'];
    const END_HOURS   = ['09:00','10:00','11:00','12:00','13:00','14:00','15:00','16:00','17:00','18:00'];

    /**
     * Determine which individual hour slots are blocked, given the
     * raw occupied-blocks array from the API.
     *
     * An hour slot "HH:00" is blocked if it falls WITHIN any occupied range.
     * For start_time: block any start ≥ occupiedStart AND start < occupiedEnd
     * For end_time:   block any end   > occupiedStart AND end ≤ occupiedEnd
     */
    function buildBlockedSets(occupiedBlocks) {
        const blockedStart = new Set();
        const blockedEnd   = new Set();

        occupiedBlocks.forEach(function(block) {
            const bs = block.start; // e.g. "08:00"
            const be = block.end;   // e.g. "10:00"

            START_HOURS.forEach(function(h) {
                // A start time is blocked if it falls within an existing reservation
                if (h >= bs && h < be) blockedStart.add(h);
            });

            END_HOURS.forEach(function(h) {
                // An end time is blocked if it falls within an existing reservation
                if (h > bs && h <= be) blockedEnd.add(h);
            });
        });

        return { blockedStart, blockedEnd };
    }

    /**
     * Re-render the Start Time <select> options,
     * disabling blocked ones and re-enabling the rest.
     */
    function applyToStartSelect(blockedStart) {
        const currentVal = $startSelect.val();
        $startSelect.find('option[data-hour]').each(function () {
            const h = $(this).data('hour');
            if (blockedStart.has(h)) {
                $(this).prop('disabled', true).text(h + ' (Occupied)');
            } else {
                $(this).prop('disabled', false).text(h);
            }
        });
        // Keep previously selected value if still valid
        $startSelect.val(currentVal);
        if ($startSelect.find('option:selected').prop('disabled')) {
            $startSelect.val('');
        }
    }

    /**
     * Re-render the End Time <select> options,
     * disabling blocked ones and re-enabling the rest.
     */
    function applyToEndSelect(blockedEnd) {
        const currentVal = $endSelect.val();
        $endSelect.find('option[data-hour]').each(function () {
            const h = $(this).data('hour');
            if (blockedEnd.has(h)) {
                $(this).prop('disabled', true).text(h + ' (Occupied)');
            } else {
                $(this).prop('disabled', false).text(h);
            }
        });
        $endSelect.val(currentVal);
        if ($endSelect.find('option:selected').prop('disabled')) {
            $endSelect.val('');
        }
    }

    /**
     * Show a visual summary of blocked ranges as chips below the selects.
     */
    function renderSummaryChips(occupiedBlocks) {
        $summary.empty().removeClass('show');
        if (!occupiedBlocks.length) {
            $summary.append('<span class="slot-chip available"><i class="fas fa-check-circle"></i> All slots available</span>');
        } else {
            occupiedBlocks.forEach(function(b) {
                $summary.append(
                    `<span class="slot-chip occupied"><i class="fas fa-ban"></i> ${b.start}–${b.end} Occupied</span>`
                );
            });
        }
        $summary.addClass('show');
    }

    /**
     * Reset all options back to enabled (when lab/date cleared).
     */
    function resetSelects() {
        $startSelect.find('option[data-hour]').prop('disabled', false).each(function() {
            $(this).text($(this).data('hour'));
        });
        $endSelect.find('option[data-hour]').prop('disabled', false).each(function() {
            $(this).text($(this).data('hour'));
        });
        $summary.empty().removeClass('show');
    }

    /**
     * Tag each option with a data-hour attribute so we can reference them reliably.
     */
    function tagSelectOptions() {
        $startSelect.find('option').not(':first').each(function() {
            $(this).attr('data-hour', $(this).val());
        });
        $endSelect.find('option').not(':first').each(function() {
            $(this).attr('data-hour', $(this).val());
        });
    }

    /**
     * Core: fetch availability from the server and apply to selects.
     */
    function fetchAvailability() {
        const labId = $labSelect.val();
        const date  = $dateInput.val();

        if (!labId || !date) {
            resetSelects();
            return;
        }

        $spinner.addClass('show');
        $summary.empty().removeClass('show');

        $.ajax({
            url: '/api/booking-requests/availability',
            method: 'GET',
            data: { lab_id: labId, date: date },
            headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
            success: function(occupiedBlocks) {
                const { blockedStart, blockedEnd } = buildBlockedSets(occupiedBlocks);
                applyToStartSelect(blockedStart);
                applyToEndSelect(blockedEnd);
                renderSummaryChips(occupiedBlocks);
            },
            error: function(xhr) {
                console.error('Availability check failed:', xhr);
                $summary.html('<span class="slot-chip" style="background:#fef3c7;color:#92400e;"><i class="fas fa-exclamation-triangle"></i> Could not load availability</span>').addClass('show');
            },
            complete: function() {
                $spinner.removeClass('show');
            }
        });
    }

    // ── Init ──
    tagSelectOptions();

    // ── Trigger on lab or date change ──
    $labSelect.on('change', fetchAvailability);
    $dateInput.on('change', fetchAvailability);

    // ── If form had old() values (after validation fail), trigger immediately ──
    if ($labSelect.val() && $dateInput.val()) {
        fetchAvailability();
    }

    // ── Prevent submitting if start >= end ──
    $('#bookingForm').on('submit', function(e) {
        const s = $startSelect.val();
        const en = $endSelect.val();
        if (s && en && s >= en) {
            e.preventDefault();
            alert('End time must be later than start time.');
        }
    });
});
</script>

</body>
</html>
