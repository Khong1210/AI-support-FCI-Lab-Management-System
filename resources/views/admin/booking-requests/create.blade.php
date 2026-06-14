@extends('layouts.admin')

@section('title', 'Booking Request Form')
@section('page-title', 'Laboratory Booking')
@section('breadcrumb', 'New Request')

@section('content')
{{-- 注入原本表单最完美的自定义高颜值样式 --}}
<style>
    .request-card {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        border: 1px solid #eef2f5;
        overflow: hidden;
        margin-bottom: 2rem;
    }
    .card-head {
        background: #f8fafc;
        padding: 1.5rem;
        border-bottom: 1px solid #edf2f7;
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    .card-head-icon {
        background: #e0f2fe;
        color: #0284c7;
        width: 45px;
        height: 45px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }
    .card-head h5 { margin: 0; font-weight: 700; color: #1e293b; }
    .card-head p { margin: 0; font-size: 0.85rem; color: #64748b; }
    .section-title {
        font-size: 0.9rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #475569;
        margin-top: 1.5rem;
        margin-bottom: 1rem;
        border-bottom: 2px solid #f1f5f9;
        padding-bottom: 0.5rem;
    }
    .alert-success-custom {
        background-color: #f0fdf4;
        border: 1px solid #bbf7d0;
        color: #166534;
        padding: 1rem;
        border-radius: 8px;
    }
    .alert-danger-custom {
        background-color: #fef2f2;
        border: 1px solid #fecaca;
        color: #991b1b;
        padding: 1rem;
        border-radius: 8px;
    }
    .slot-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.4rem 0.75rem;
        border-radius: 6px;
        font-size: 0.85rem;
        font-weight: 600;
        margin-right: 0.5rem;
        margin-bottom: 0.5rem;
    }
    .slot-chip.available { background: #dcfce7; color: #14532d; }
    .slot-chip.occupied { background: #fee2e2; color: #7f1d1d; }
    #availability-spinner {
        display: none;
        align-items: center;
        gap: 0.5rem;
        color: #64748b;
        font-size: 0.9rem;
    }
    #availability-spinner.show { display: inline-flex; }
    .btn-submit {
        background: #2563eb;
        color: white;
        font-weight: 600;
        padding: 0.6rem 1.5rem;
        border-radius: 6px;
        transition: all 0.2s;
    }
    .btn-submit:hover { background: #1d4ed8; color: white; }
</style>

<div class="row justify-content-center">
    <div class="col-lg-8">

        {{-- Success Flash --}}
        @if (session('status') || session('success'))
            <div class="alert-success-custom mb-4 d-flex align-items-start gap-3 shadow-sm">
                <i class="fas fa-check-circle fa-lg mt-1"></i>
                <div>
                    <strong>Request Submitted Successfully!</strong><br>
                    {{ session('status') ?? session('success') }}
                </div>
            </div>
        @endif

        {{-- Error Flash --}}
        @if ($errors->any())
            <div class="alert-danger-custom mb-4 shadow-sm">
                <strong><i class="fas fa-exclamation-circle me-1"></i>Please fix the following errors:</strong>
                <ul class="mb-0 mt-2 pl-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Form 核心卡片 --}}
        <div class="request-card">
            <div class="card-head">
                <div class="card-head-icon"><i class="fas fa-file-alt"></i></div>
                <div>
                    <h5>Booking Request Form</h5>
                    <p>All fields marked with <span class="text-danger">*</span> are required.</p>
                </div>
            </div>

            {{-- 统一指引向后台的 store 方法 --}}
            <form action="{{ url('/booking-requests') }}" method="POST" class="p-4" id="bookingForm">
                @csrf

                {{-- ── Section: Booking Details ── --}}
                <p class="section-title"><i class="fas fa-door-open me-1"></i>Booking Details</p>
                <div class="row mb-3">
                    <div class="col-md-6 form-group">
                        <label class="form-label font-weight-bold" for="lab_id">Laboratory / Room <span class="text-danger">*</span></label>
                        <select name="lab_id" id="lab_id" class="form-control @error('lab_id') is-invalid @enderror" required>
                            <option value="">— Select a laboratory —</option>
                            @foreach($laboratories as $lab)
                                <option value="{{ $lab->id }}" {{ old('lab_id') == $lab->id ? 'selected' : '' }}>
                                    {{ $lab->lab_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('lab_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    {{-- 初始移除了硬编码的 disabled，靠 JS 优雅管控开关 --}}
                    <div class="col-md-6 form-group">
                        <label class="form-label font-weight-bold" for="date">Date <span class="text-danger">*</span></label>
                        <input type="date" name="date" id="date"
                               class="form-control @error('date') is-invalid @enderror"
                               value="{{ old('date') }}"
                               min="{{ date('Y-m-d') }}" required>
                        @error('date')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- ── Availability Status ── --}}
                <div id="availability-spinner" class="mb-2">
                    <div class="spinner-border spinner-border-sm" role="status"></div>
                    <span>Checking scheduling constraints...</span>
                </div>
                <div class="availability-summary mb-3" id="availability-summary"></div>

                {{-- ── Time Slots ── --}}
                <div class="row mb-4">
                    <div class="col-md-6 form-group">
                        <label class="form-label font-weight-bold" for="start_time">Start Time <span class="text-danger">*</span></label>
                        <select name="start_time" id="start_time" class="form-control @error('start_time') is-invalid @enderror" required>
                            <option value="">— Select start time —</option>
                            @for($h = 8; $h <= 17; $h++)
                                @php $t = sprintf('%02d:00', $h); @endphp
                                <option value="{{ $t }}" {{ old('start_time') == $t ? 'selected' : '' }}>{{ $t }}</option>
                            @endfor
                        </select>
                        @error('start_time')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="form-label font-weight-bold" for="end_time">End Time <span class="text-danger">*</span></label>
                        <select name="end_time" id="end_time" class="form-control @error('end_time') is-invalid @enderror" required>
                            <option value="">— Select end time —</option>
                            @for($h = 9; $h <= 18; $h++)
                                @php $t = sprintf('%02d:00', $h); @endphp
                                <option value="{{ $t }}" {{ old('end_time') == $t ? 'selected' : '' }}>{{ $t }}</option>
                            @endfor
                        </select>
                        @error('end_time')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- ── Reason / Purpose ── --}}
                <p class="section-title"><i class="fas fa-comment-alt me-1"></i>Purpose</p>
                <div class="mb-4 form-group">
                    <label class="form-label font-weight-bold" for="reason">Reason for Booking <span class="text-danger">*</span></label>
                    <textarea name="reason" id="reason" rows="4"
                              class="form-control @error('reason') is-invalid @enderror"
                              placeholder="Please describe the purpose of your booking (e.g., group study, project presentation, workshop...)"
                              required>{{ old('reason') }}</textarea>
                    @error('reason')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                {{-- ── Submit & Actions ── --}}
                <div class="d-flex align-items-center gap-3">
                    <button type="submit" class="btn btn-submit shadow-sm" id="submitBtn">
                        <i class="fas fa-paper-plane me-2"></i>Submit Request
                    </button>
                    <a href="{{ url('/booking-requests') }}" class="btn btn-link text-secondary ml-2">Cancel</a>
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
@endsection

@push('scripts')
{{-- 确保引入高版本稳定 jQuery --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(function () {
    const CSRF_TOKEN    = $('meta[name="csrf-token"]').attr('content');
    const $labSelect    = $('#lab_id');
    const $dateInput    = $('#date');
    const $startSelect  = $('#start_time');
    const $endSelect    = $('#end_time');
    const $spinner      = $('#availability-spinner');
    const $summary      = $('#availability-summary');

    const START_HOURS = ['08:00','09:00','10:00','11:00','12:00','13:00','14:00','15:00','16:00','17:00'];
    const END_HOURS   = ['09:00','10:00','11:00','12:00','13:00','14:00','15:00','16:00','17:00','18:00'];

    // 完美复刻初始化：打上必要的位置标签
    function tagSelectOptions() {
        $startSelect.find('option').not(':first').each(function() { $(this).attr('data-hour', $(this).val()); });
        $endSelect.find('option').not(':first').each(function() { $(this).attr('data-hour', $(this).val()); });
    }

    function buildBlockedSets(occupiedBlocks) {
        const blockedStart = new Set();
        const blockedEnd   = new Set();

        if (occupiedBlocks && occupiedBlocks.length > 0) {
            occupiedBlocks.forEach(function(block) {
                const bs = block.start;
                const be = block.end;

                START_HOURS.forEach(function(h) { if (h >= bs && h < be) blockedStart.add(h); });
                END_HOURS.forEach(function(h)   { if (h > bs && h <= be) blockedEnd.add(h); });
            });
        }
        return { blockedStart, blockedEnd };
    }

    function applyToStartSelect(blockedStart) {
        const currentVal = $startSelect.val();
        $startSelect.find('option[data-hour]').each(function () {
            const h = $(this).data('hour');
            if (blockedStart.has(h)) {
                $(this).prop('disabled', true).text(h + ' (Occupied)').css('color', '#dc3545');
            } else {
                $(this).prop('disabled', false).text(h).css('color', '');
            }
        });
        $startSelect.val(currentVal);
        if ($startSelect.find('option:selected').prop('disabled')) $startSelect.val('');
    }

    function applyToEndSelect(blockedEnd) {
        const currentVal = $endSelect.val();
        $endSelect.find('option[data-hour]').each(function () {
            const h = $(this).data('hour');
            if (blockedEnd.has(h)) {
                $(this).prop('disabled', true).text(h + ' (Occupied)').css('color', '#dc3545');
            } else {
                $(this).prop('disabled', false).text(h).css('color', '');
            }
        });
        $endSelect.val(currentVal);
        if ($endSelect.find('option:selected').prop('disabled')) $endSelect.val('');
    }

    function renderSummaryChips(occupiedBlocks) {
        $summary.empty();
        if (!occupiedBlocks || !occupiedBlocks.length) {
            $summary.append('<span class="slot-chip available"><i class="fas fa-check-circle"></i> All slots available on this date</span>');
        } else {
            occupiedBlocks.forEach(function(b) {
                $summary.append(`<span class="slot-chip occupied"><i class="fas fa-ban"></i> ${b.start}–${b.end} Occupied / Pending</span>`);
            });
        }
    }

    function resetSelects() {
        $startSelect.find('option[data-hour]').prop('disabled', false).css('color', '').each(function() {
            $(this).text($(this).data('hour'));
        });
        $endSelect.find('option[data-hour]').prop('disabled', false).css('color', '').each(function() {
            $(this).text($(this).data('hour'));
        });
        $summary.empty();
    }

    // 动态管控状态机：防止提前乱点
    function evaluateFormState() {
        if ($labSelect.val()) {
            $dateInput.prop('disabled', false);
            if ($dateInput.val()) {
                $startSelect.prop('disabled', false);
                $endSelect.prop('disabled', false);
                return true;
            }
        } else {
            $dateInput.prop('disabled', true).val('');
        }
        $startSelect.prop('disabled', true).val('');
        $endSelect.prop('disabled', true).val('');
        resetSelects();
        return false;
    }

    function fetchAvailability() {
        if (!evaluateFormState()) return;

        $spinner.addClass('show');
        $summary.empty();

        $.ajax({
            // 💡 关键修复：这里的路径强制和你的新后台 Controller 保持一致！
            url: "{{ url('/booking-requests/check-availability') }}",
            method: 'GET',
            data: { lab_id: $labSelect.val(), date: $dateInput.val() },
            headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
            success: function(occupiedBlocks) {
                const { blockedStart, blockedEnd } = buildBlockedSets(occupiedBlocks);
                applyToStartSelect(blockedStart);
                applyToEndSelect(blockedEnd);
                renderSummaryChips(occupiedBlocks);
            },
            error: function(xhr) {
                console.error('Check failed:', xhr);
                $summary.html('<span class="slot-chip" style="background:#fee2e2;color:#991b1b;"><i class="fas fa-exclamation-triangle"></i> Availability service unreachable</span>');
            },
            complete: function() {
                $spinner.removeClass('show');
            }
        });
    }

    // ── 流程启动 ──
    tagSelectOptions();
    evaluateFormState();

    $labSelect.on('change', fetchAvailability);
    $dateInput.on('change', fetchAvailability);

    if ($labSelect.val() && $dateInput.val()) {
        fetchAvailability();
    }

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
@endpush