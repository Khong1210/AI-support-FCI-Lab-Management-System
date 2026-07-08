@extends('layouts.admin')

@section('title', 'AI Multi-Course Batch Scheduler')
@section('page-title', 'AI Multi-Course Batch Scheduler')
@section('breadcrumb', 'AI Batch Scheduler')

@section('content')
<style>
    /* Premium UI Facelift Styles */
    .custom-card-radio {
        cursor: pointer;
        transition: all 0.25s ease;
        border: 2px solid #dee2e6;
    }
    .form-check-input:checked + .custom-card-radio {
        border-color: #0d6efd;
        background-color: #f8f9ff;
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.1);
    }
    .queue-badge {
        font-size: 0.8rem;
        padding: 0.35rem 0.6rem;
    }
    .step-locked {
        opacity: 0.5;
        pointer-events: none;
        cursor: not-allowed;
    }
    .step-enabled {
        opacity: 1;
        pointer-events: auto;
    }
    .step-indicator {
        display: inline-block;
        width: 30px;
        height: 30px;
        line-height: 30px;
        border-radius: 50%;
        background: #6c757d;
        color: white;
        text-align: center;
        font-weight: bold;
        margin-right: 8px;
    }
    .step-indicator.active {
        background: #0d6efd;
    }
    .step-indicator.completed {
        background: #28a745;
    }
    .step-container-block {
        background: #fff;
        padding: 1rem;
        border-radius: 0.5rem;
        border: 1px solid #dee2e6;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        margin-bottom: 0.75rem;
    }
    .solid-select {
        padding-top: 0.5rem !important;
        padding-bottom: 0.5rem !important;
        padding-left: 0.75rem !important;
        padding-right: 0.75rem !important;
        font-size: 0.875rem;
        border: 2px solid #ced4da;
        box-shadow: 0 1px 2px rgba(0,0,0,0.04);
    }
</style>

<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="container-fluid px-3">
    <div class="row">
        <div class="col-xl-4 col-lg-12 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-dark text-white fw-bold d-flex justify-content-between align-items-center py-2">
                    <div>
                        <i class="fas fa-sliders-h me-2"></i> Configure Course Requirements (6-Step Workflow)
                    </div>
                </div>
                <div class="card-body bg-light p-3"> <form id="queueConfigForm" onsubmit="event.preventDefault();">
                        
                        <div class="step-container-block" id="step1">
                            <label class="form-label fw-bold text-dark small">
                                <span class="step-indicator active">1</span>
                                Select Semester/Trimester:
                            </label>
                            <div class="d-flex gap-2">
                                <select id="semester_selector" class="form-select solid-select shadow-sm">
                                    <option value="" selected>-- Choose Semester --</option>
                                    @if(isset($semesters) && $semesters->count() > 0)
                                        @foreach($semesters as $sem)
                                            <option value="{{ $sem->id }}" data-start-date="{{ $sem->start_date }}">
                                                Trimester #{{ $sem->id }}
                                            </option>
                                        @endforeach
                                    @else
                                        <option value="1" data-start-date="2026-05-25">Trimester #1</option>
                                        <option value="2" data-start-date="2026-09-07">Trimester #2</option>
                                        <option value="3" data-start-date="2027-01-04">Trimester #3</option>
                                    @endif
                                </select>
                                <button type="button" id="resetSemesterBtn" class="btn btn-warning" style="display:none;">
                                    <i class="fas fa-redo"></i> Reset
                                </button>
                            </div>
                            <small class="text-muted" style="font-size: 0.75rem;">Semester auto-locks on selection. Use Reset to change.</small>
                        </div>

                        <div class="step-container-block step-locked" id="step2">
                            <label for="ai_course_id" class="form-label fw-bold text-dark small">
                                <span class="step-indicator">2</span>
                                Select Target Course:
                            </label>
                            <select id="ai_course_id" class="form-select solid-select shadow-sm" disabled>
                                <option value="">-- Choose Course --</option>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}" 
                                            data-name="{{ $course->course_name }}"
                                            data-lecturer-id="{{ $course->lecturer_id ?? '' }}"
                                            data-lecturer-name="{{ $course->lecturer_name ?? 'Unassigned' }}">
                                        {{ $course->course_code ?? 'CRK' }} - {{ $course->course_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="step-container-block step-locked" id="step3">
                            <label class="form-label fw-bold text-dark small">
                                <span class="step-indicator">3</span>
                                Select Primary Resource Focus:
                            </label>
                            <div class="row g-2">
                                <div class="col-4">
                                    <input type="radio" class="form-check-input d-none" name="core_constraint_type" id="radio_software" value="software" disabled>
                                    <label class="card custom-card-radio p-2 text-center rounded h-100" for="radio_software" style="cursor: pointer;">
                                        <i class="fas fa-code text-primary mb-1"></i>
                                        <span style="font-size: 0.75rem;" class="fw-bold d-block">Software</span>
                                    </label>
                                </div>
                                <div class="col-4">
                                    <input type="radio" class="form-check-input d-none" name="core_constraint_type" id="radio_hardware" value="hardware" disabled>
                                    <label class="card custom-card-radio p-2 text-center rounded h-100" for="radio_hardware" style="cursor: pointer;">
                                        <i class="fas fa-tools text-warning mb-1"></i>
                                        <span style="font-size: 0.75rem;" class="fw-bold d-block">Hardware</span>
                                    </label>
                                </div>
                                <div class="col-4">
                                    <input type="radio" class="form-check-input d-none" name="core_constraint_type" id="radio_laboratory" value="laboratory" disabled>
                                    <label class="card custom-card-radio p-2 text-center rounded h-100" for="radio_laboratory" style="cursor: pointer;">
                                        <i class="fas fa-door-open text-success mb-1"></i>
                                        <span style="font-size: 0.75rem;" class="fw-bold d-block">Lab</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="step-container-block step-locked" id="step4">
                            <label class="form-label fw-bold text-dark small">
                                <span class="step-indicator">4</span>
                                Select Specific Resource:
                            </label>
                            <div class="p-2 bg-white border rounded">
                                <div class="constraint-select-wrapper" id="wrapper_software">
                                    <label class="form-label text-muted small fw-bold" style="font-size: 0.7rem;">Required Software Module:</label>
                                    <select id="ai_software_name" class="form-select solid-select shadow-sm" disabled>
                                        <option value="">-- Select Software --</option>
                                        @foreach($softwares->unique('software_name') as $sw)
                                            <option value="{{ $sw->software_name }}">{{ $sw->software_name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="constraint-select-wrapper d-none" id="wrapper_hardware">
                                    <label class="form-label text-muted small fw-bold" style="font-size: 0.7rem;">Required Hardware Unit:</label>
                                    <select id="ai_equipment_name" class="form-select solid-select shadow-sm" disabled>
                                        <option value="">-- Select Hardware/Equipment --</option>
                                        @foreach($equipments->unique('equipment_name') as $eq)
                                            <option value="{{ $eq->equipment_name }}">{{ $eq->equipment_name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="constraint-select-wrapper d-none" id="wrapper_laboratory">
                                    <label class="form-label text-muted small fw-bold" style="font-size: 0.7rem;">Target Lab Destination:</label>
                                    <select id="ai_lab_id" class="form-select solid-select shadow-sm" disabled>
                                        <option value="">-- Select Laboratory --</option>
                                        @foreach($laboratories as $lab)
                                            <option value="{{ $lab->id }}" data-name="{{ $lab->lab_name }}">
                                                {{ $lab->lab_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="step-container-block step-locked" id="step5">
                            <label for="ai_time_preference" class="form-label fw-bold text-dark small">
                                <span class="step-indicator">5</span>
                                Preferred Schedule Shift:
                            </label>
                            <select id="ai_time_preference" class="form-select solid-select shadow-sm" disabled>
                                <option value="full_day" selected>Full Day</option>
                                <option value="morning">Morning</option>
                                <option value="afternoon">Afternoon</option>
                            </select>
                        </div>

                        <div class="step-container-block step-locked" id="step6">
                            <label class="form-label fw-bold text-dark small d-block">
                                <span class="step-indicator">6</span>
                                Add to Scheduling Queue:
                            </label>
                            <button type="button" id="addToQueueButton" class="btn btn-dark w-100 fw-bold shadow-sm" disabled>
                                <i class="fas fa-plus-circle me-2 text-info"></i> Add to Queue
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-8 col-lg-12">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-secondary text-white fw-bold d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-list-ol me-2"></i> Current Pending Queue</span>
                    <span id="queueCounter" class="badge bg-light text-dark fw-bold">0 Courses Queued</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 text-center">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 120px;">Step Index</th>
                                    <th class="text-start">Course Title</th>
                                    <th>Core Rule Constraint</th>
                                    <th>Shift Prefer</th>
                                    <th style="width: 100px;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="queueTableBody">
                                <tr>
                                    <td colspan="5" class="text-muted p-4">No arrangements staged yet. Configure left parameters and queue up courses.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white p-3 d-flex gap-2">
                    <button type="button" id="analyzeButton" class="btn btn-primary btn-lg fw-bold flex-grow-1 shadow-sm" disabled>
                        <i class="fas fa-bolt me-2"></i> Run Batch AI Optimization
                    </button>
                    <button type="button" id="clearQueueButton" class="btn btn-outline-danger">Clear All</button>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-success text-white fw-bold">
                    <i class="fas fa-network-wired me-2"></i> Optimized Global Matrix
                </div>
                <div id="responseContainer" class="card-body bg-white p-4" style="min-height: 150px;">
                    <p class="text-muted mb-0">Staged rows must be compiled inside the queue. Trigger the optimization executor above to compute global timetable alternatives.</p>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="module">
    // 🌟 Backend data injection
    const schedules = @json($schedules ?? []);
    const softwares = @json($softwares ?? []);
    const equipments = @json($equipments ?? []);
    const laboratories = @json($laboratories ?? []);
    const courses = @json($courses ?? []);
    const lecturers = @json($lecturers ?? []);
    let currentSemesterId = @json($currentSemesterId ?? 1);

    // 🔍【诊断节点1】打印后端传来的原始数据结构，确认真实键名
    console.log("【检查原始数据】schedules 第一条：", schedules[0]);
    console.log("【检查原始数据】laboratories 第一条：", laboratories[0]);

    let schedulingQueue = [];
    let workflowState = {
        step1Locked: false,
        step2Complete: false,
        step3Complete: false,
        step4Complete: false,
        step5Complete: false
    };

    const queueTableBody = document.getElementById('queueTableBody');
    const queueCounter = document.getElementById('queueCounter');
    const addToQueueButton = document.getElementById('addToQueueButton');
    const analyzeButton = document.getElementById('analyzeButton');
    const clearQueueButton = document.getElementById('clearQueueButton');
    const responseContainer = document.getElementById('responseContainer');
    const semesterSelector = document.getElementById('semester_selector');
    const resetSemesterBtn = document.getElementById('resetSemesterBtn');

    // ========================================================
    // STEP 1: SEMESTER AUTO-LOCK ON SELECTION
    // ========================================================
    semesterSelector.addEventListener('change', function() {
        if (this.value) {
            // Auto-lock immediately upon selection
            workflowState.step1Locked = true;
            currentSemesterId = this.value;
            
            // Lock semester selector
            this.disabled = true;
            resetSemesterBtn.style.display = 'inline-block';
            
            // Update step indicator
            document.querySelector('#step1 .step-indicator').classList.add('completed');
            
            // Enable Step 2
            enableStep('step2');
            document.getElementById('ai_course_id').disabled = false;
        } else {
            // Value is empty (e.g. triggered by Reset) — release Step 1 lock
            workflowState.step1Locked = false;
            document.querySelector('#step1 .step-indicator').classList.remove('completed');
            document.querySelector('#step1 .step-indicator').classList.add('active');
        }
    });

    resetSemesterBtn.addEventListener('click', function() {
        if (schedulingQueue.length > 0) {
            if (!confirm(' Resetting will clear all queued courses. Continue?')) {
                return;
            }
        }
        
        // Reset workflow state
        workflowState = {
            step1Locked: false,
            step2Complete: false,
            step3Complete: false,
            step4Complete: false,
            step5Complete: false
        };
        
        schedulingQueue = [];
        renderQueueTable();
        

        semesterSelector.querySelectorAll('option').forEach(opt => {
            opt.removeAttribute('selected');
            opt.selected = false;
        });
        semesterSelector.value = "";
        semesterSelector.selectedIndex = 0;
        semesterSelector.disabled = false;
        resetSemesterBtn.style.display = 'none';
        
        // Dispatch native 'change' so the workflow state machine
        // reacts to the now-empty value and releases Step 1 lock
        semesterSelector.dispatchEvent(new Event('change', { bubbles: true }));
        
        // Reset all step indicators: restore step1 as active, all others neutral
        document.querySelectorAll('.step-indicator').forEach((indicator, index) => {
            indicator.classList.remove('completed', 'active');
            if (index === 0) indicator.classList.add('active');
        });
        
        // Lock all steps except step 1
        disableStep('step2');
        disableStep('step3');
        disableStep('step4');
        disableStep('step5');
        disableStep('step6');
        
        // Disable all downstream inputs
        document.getElementById('ai_course_id').disabled = true;
        document.querySelectorAll('input[name="core_constraint_type"]').forEach(r => r.disabled = true);
        document.getElementById('ai_software_name').disabled = true;
        document.getElementById('ai_equipment_name').disabled = true;
        document.getElementById('ai_lab_id').disabled = true;
        document.getElementById('ai_time_preference').disabled = true;
        addToQueueButton.disabled = true;
        
        responseContainer.innerHTML = '<p class="text-muted mb-0">Staged rows must be compiled inside the queue. Trigger the optimization executor above to compute global timetable alternatives.</p>';
    });

    // ========================================================
    // STEP 2: COURSE SELECTION
    // ========================================================
    document.getElementById('ai_course_id').addEventListener('change', function() {
        if (this.value) {
            workflowState.step2Complete = true;
            document.querySelector('#step2 .step-indicator').classList.add('completed');
            
            // Enable Step 3
            enableStep('step3');
            document.querySelectorAll('input[name="core_constraint_type"]').forEach(radio => {
                radio.disabled = false;
            });
        }
    });

    // ========================================================
    // STEP 3: RESOURCE CATEGORY SELECTION
    // ========================================================
    document.querySelectorAll('input[name="core_constraint_type"]').forEach(radio => {
        radio.addEventListener('change', function() {
            workflowState.step3Complete = true;
            document.querySelector('#step3 .step-indicator').classList.add('completed');
            
            // Show appropriate wrapper
            document.querySelectorAll('.constraint-select-wrapper').forEach(wrapper => {
                wrapper.classList.add('d-none');
            });
            const targetWrapper = document.getElementById(`wrapper_${this.value}`);
            if (targetWrapper) {
                targetWrapper.classList.remove('d-none');
            }
            
            // Enable Step 4
            enableStep('step4');
            
            // Enable the appropriate select
            if (this.value === 'software') {
                document.getElementById('ai_software_name').disabled = false;
            } else if (this.value === 'hardware') {
                document.getElementById('ai_equipment_name').disabled = false;
            } else if (this.value === 'laboratory') {
                document.getElementById('ai_lab_id').disabled = false;
            }
        });
    });

    // ========================================================
    // STEP 4: SPECIFIC RESOURCE SELECTION
    // ========================================================
    document.getElementById('ai_software_name').addEventListener('change', function() {
        if (this.value) {
            completeStep4();
        }
    });
    
    document.getElementById('ai_equipment_name').addEventListener('change', function() {
        if (this.value) {
            completeStep4();
        }
    });
    
    document.getElementById('ai_lab_id').addEventListener('change', function() {
        if (this.value) {
            completeStep4();
        }
    });

    function completeStep4() {
        workflowState.step4Complete = true;
        document.querySelector('#step4 .step-indicator').classList.add('completed');
        
        // Enable Step 5 - "Full Day" is pre-selected default
        enableStep('step5');
        document.getElementById('ai_time_preference').disabled = false;
        
        // Auto-complete Step 5 + Step 6 simultaneously since "Full Day" is default
        workflowState.step5Complete = true;
        document.querySelector('#step5 .step-indicator').classList.add('completed');
        
        // Enable Step 6 (Add to Queue) immediately — no manual Step 5 interaction needed
        enableStep('step6');
        addToQueueButton.disabled = false;
    }

    // ========================================================
    // STEP 5: TIME PREFERENCE SELECTION (Optional - "Full Day" is default)
    // ========================================================
    document.getElementById('ai_time_preference').addEventListener('change', function() {
        completeStep5();
    });

    function completeStep5() {
        workflowState.step5Complete = true;
        document.querySelector('#step5 .step-indicator').classList.add('completed');
        
        // Enable Step 6
        enableStep('step6');
        addToQueueButton.disabled = false;
    }

    // ========================================================
    // HELPER FUNCTIONS FOR STEP MANAGEMENT
    // ========================================================
    function enableStep(stepId) {
        const step = document.getElementById(stepId);
        step.classList.remove('step-locked');
        step.classList.add('step-enabled');
        step.querySelector('.step-indicator').classList.add('active');
    }

    function disableStep(stepId) {
        const step = document.getElementById(stepId);
        step.classList.add('step-locked');
        step.classList.remove('step-enabled');
        step.querySelector('.step-indicator').classList.remove('active', 'completed');
    }

    // ========================================================
    // LOGIC: RENDER INTERACTIVE QUEUE DATA ROWS TO UI TABLE
    // ========================================================
    function renderQueueTable() {
        if (schedulingQueue.length === 0) {
            queueTableBody.innerHTML = `<tr><td colspan="5" class="text-muted p-4">No arrangements staged yet. Configure left parameters and queue up courses.</td></tr>`;
            queueCounter.innerText = "0 Courses Queued";
            analyzeButton.disabled = true;
            return;
        }

        queueCounter.innerText = `${schedulingQueue.length} Course(s) Queued`;
        analyzeButton.disabled = false;

        queueTableBody.innerHTML = schedulingQueue.map((item, index) => {
            return `
                <tr>
                    <td class="fw-bold text-dark">Step ${index + 1}</td>
                    <td class="text-start fw-semibold text-primary">${item.courseName}</td>
                    <td class="small text-muted">${item.constraintLabel}</td>
                    <td class="text-capitalize small">${item.timePreference}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-danger delete-queue-btn" data-index="${index}">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </td>
                </tr>
            `;
        }).join('');
    }

    // ========================================================
    // EVENT: ADD SELECTIONS CONTEXT INTO THE SCHEDULING CARTS
    // ========================================================
    addToQueueButton.addEventListener('click', () => {
        const courseSelect = document.getElementById('ai_course_id');
        const selectedCourseId = courseSelect.value;
        const selectedCourseName = courseSelect.options[courseSelect.selectedIndex]?.getAttribute('data-name') || '';

        if (!selectedCourseId) {
            alert(" Please specify a target Course registry row before saving.");
            return;
        }

        if (schedulingQueue.some(item => item.courseId === selectedCourseId)) {
            alert(" This course is already loaded inside the execution queue deck.");
            return;
        }

        const constraintType = document.querySelector('input[name="core_constraint_type"]:checked').value;
        let constraintValue = "";
        let constraintLabel = "";

        if (constraintType === 'software') {
            constraintValue = document.getElementById('ai_software_name').value;
            if (!constraintValue) { alert("Please allocate required Software asset context."); return; }
            constraintLabel = ` Software: ${constraintValue}`;
        } else if (constraintType === 'hardware') {
            constraintValue = document.getElementById('ai_equipment_name').value;
            if (!constraintValue) { alert("Please allocate required Hardware Asset context."); return; }
            constraintLabel = ` Hardware: ${constraintValue}`;
        } else if (constraintType === 'laboratory') {
            const labSelect = document.getElementById('ai_lab_id');
            constraintValue = labSelect.options[labSelect.selectedIndex]?.getAttribute('data-name') || '';
            if (!constraintValue) { alert("Please select target unique laboratory boundary."); return; }
            constraintLabel = ` Fixed Lab: ${constraintValue}`;
        }

        const timePreference = document.getElementById('ai_time_preference').value;

        // Capture lecturer data from the course <option> for AI Layer-2 collision detection
        const selectedOption = courseSelect.options[courseSelect.selectedIndex];
        const lecturerId   = selectedOption?.getAttribute('data-lecturer-id') || '';
        const lecturerName = selectedOption?.getAttribute('data-lecturer-name') || 'Unassigned';

        schedulingQueue.push({
            courseId: selectedCourseId,
            courseName: selectedCourseName,
            lecturerId: lecturerId,
            lecturerName: lecturerName,
            constraintType: constraintType,
            constraintValue: constraintValue,
            constraintLabel: constraintLabel,
            timePreference: timePreference
        });

        renderQueueTable();
        
        // Reset form for next entry (but keep semester locked)
        courseSelect.value = "";
        
        // Reset steps 2-6 for next course
        workflowState.step2Complete = false;
        workflowState.step3Complete = false;
        workflowState.step4Complete = false;
        workflowState.step5Complete = false;
        
        // Reset step indicators (except step 1)
        document.querySelector('#step2 .step-indicator').classList.remove('completed');
        document.querySelector('#step3 .step-indicator').classList.remove('completed');
        document.querySelector('#step4 .step-indicator').classList.remove('completed');
        document.querySelector('#step5 .step-indicator').classList.remove('completed');
        document.querySelector('#step6 .step-indicator').classList.remove('completed');
        
        // Disable steps 3-6
        disableStep('step3');
        disableStep('step4');
        disableStep('step5');
        disableStep('step6');
        
        // Uncheck radios
        document.querySelectorAll('input[name="core_constraint_type"]').forEach(r => {
            r.checked = false;
            r.disabled = true;
        });
        
        // Reset and disable selects
        document.getElementById('ai_software_name').value = "";
        document.getElementById('ai_software_name').disabled = true;
        document.getElementById('ai_equipment_name').value = "";
        document.getElementById('ai_equipment_name').disabled = true;
        document.getElementById('ai_lab_id').value = "";
        document.getElementById('ai_lab_id').disabled = true;
        document.getElementById('ai_time_preference').value = "full_day";
        document.getElementById('ai_time_preference').disabled = true;
        addToQueueButton.disabled = true;
        
        // Hide all wrappers
        document.querySelectorAll('.constraint-select-wrapper').forEach(w => w.classList.add('d-none'));
        document.getElementById('wrapper_software').classList.remove('d-none');
    });

    // EVENT: REMOVE SPECIFIC ELEMENT OUT OF THE STATE DECK
    queueTableBody.addEventListener('click', (e) => {
        const deleteButton = e.target.closest('.delete-queue-btn');
        if (deleteButton) {
            const index = parseInt(deleteButton.getAttribute('data-index'));
            schedulingQueue.splice(index, 1);
            renderQueueTable();
        }
    });

    // Clear Everything Trigger
    clearQueueButton.addEventListener('click', () => {
        if (schedulingQueue.length > 0) {
            if (!confirm(' Clear all queued courses?')) {
                return;
            }
        }
        schedulingQueue = [];
        renderQueueTable();
        responseContainer.innerHTML = '<p class="text-muted mb-0">Staged rows must be compiled inside the queue. Trigger the optimization executor above to compute global timetable alternatives.</p>';
    });

    // ========================================================
    // ENGINE CALL: PARSE BATCH QUEUE CARTS AND COMPUTE PROMPTS
    // ========================================================
    async function executeBatchOptimization() {
        if (schedulingQueue.length === 0) return;

        analyzeButton.disabled = true;
        responseContainer.innerHTML = `
            <div class="text-center p-4">
                <div class="spinner-border text-primary mb-3" role="status"></div>
                <p class="fw-bold text-primary mb-0">⏳ Deploying anti-collision matrices. Allocating slots via Secure Backend Proxy Engine...</p>
            </div>
        `;

        try {
            const compiledRequirementsText = schedulingQueue.map((item, idx) => {
                return `Course Requirement Demand Block #${idx + 1}:
                - Course Name: "${item.courseName}" (Database ID: ${item.courseId})
                - Assigned Lecturer: ${item.lecturerName} (Lecturer ID: ${item.lecturerId})
                - Target Asset Condition Rule: [Type: ${item.constraintType}, Target Value: "${item.constraintValue}"]
                - Target Time Slot Interval Strategy: ${item.timePreference}`;
            }).join('\n\n');

            // Frontend sends ONLY the raw course demand list.
            // All scheduling rules, existing timetable data, lab/lecturer mappings,
            // and collision constraints are handled EXCLUSIVELY by the backend System Prompt
            // to prevent stale-data interference and contradictory rule sets from confusing the AI.
            const totalGlobalContext = `SEMESTER_ID: ${currentSemesterId}

                [COURSE REQUIREMENT DEMAND BLOCKS]
                ${compiledRequirementsText};

                [CRITICAL MATRIX DISTRIBUTION CONSTRAINTS]
                1. You MUST actively utilize the entire 5-day academic week (Monday, Tuesday, Wednesday, Thursday, Friday).
                2. DO NOT cluster or bias rows into Monday through Wednesday. Thursday and Friday MUST be allocated to ensure even load balancing across available lab assets.
                3. If you do not distribute courses across all 5 days, the administration system will reject the matrix. Make sure Thursday and Friday have explicit rows assigned.`;

            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 120000);

            const res = await fetch("{{ route('ai-scheduler.generate') }}", {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ 
                    prompt: totalGlobalContext,
                    semester_id: currentSemesterId
                }),
                signal: controller.signal
            });
            clearTimeout(timeoutId);

            const data = await res.json();
            if (!res.ok || !data.success) throw new Error("API_LIMIT_OR_BACKEND_ERROR");

            let rawText = data.text;
            if (!rawText) throw new Error("EMPTY_AI_RESPONSE");

            rawText = rawText.replace(/\`\`\`json/g, '').replace(/\`\`\`/g, '').trim();
            const optimizedSlots = JSON.parse(rawText);
            
            renderResultTable(optimizedSlots, "AI Concurrent De-Collision Matrix Generated", "success");

        } catch (error) {
            console.warn("DeepSeek API unavailable – activating intelligent local fallback engine.", error);
            // ========================================================
            // INTELLIGENT LOCAL FALLBACK ANTI-COLLISION ENGINE
            // Runs when DeepSeek API is unreachable, out of quota, or
            // returns malformed data. Uses existing schedules data plus
            // software/equipment/lab mappings to generate conflict-free slots.
            // ========================================================
            const fallbackSlots = [];
            const dayNames = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"];
            const allTimeSlots = [
                { label: "08:00 AM - 10:00 AM", start: "08:00:00", end: "10:00:00", shift: "morning" },
                { label: "10:00 AM - 12:00 PM", start: "10:00:00", end: "12:00:00", shift: "morning" },
                { label: "02:00 PM - 04:00 PM", start: "14:00:00", end: "16:00:00", shift: "afternoon" },
                { label: "04:00 PM - 06:00 PM", start: "16:00:00", end: "18:00:00", shift: "afternoon" }
            ];

            // ── Helper: check if a (lab, day, start, end) conflicts with existing schedules ──
                    // ── Helper: 检查是否与数据库已有课表冲突 ──
            function hasConflict(labName, day, slotStart, slotEnd, currentLecturerId, currentCourseId) {
                return schedules.some(s => {
                    if (s.day_of_week !== day) return false;
                    
                    // 1. 机房冲突检测
                    const isLabConflict = (s.laboratory_name === labName && slotStart < s.end_time && slotEnd > s.start_time);
                    
                    // 2. 讲师冲突检测（防止同一个讲师同一时间在两个机房上课）
                    const isLecturerConflict = (s.lecturer_id && currentLecturerId && s.lecturer_id == currentLecturerId && slotStart < s.end_time && slotEnd > s.start_time);
                    
                    // 3. 相同课程冲突检测（防止同一门课同一时间上两节）
                    const isCourseConflict = (s.course_id && currentCourseId && s.course_id == currentCourseId && slotStart < s.end_time && slotEnd > s.start_time);

                    return isLabConflict || isLecturerConflict || isCourseConflict;
                });
            }
            // ── Helper: check if a lab has the required asset ──
            function labHasAsset(labName, constraintType, constraintValue) {
                if (constraintType === 'software') {
                    return softwares.some(sw => sw.software_name === constraintValue && sw.lab_room === labName);
                }
                if (constraintType === 'hardware') {
                    return equipments.some(eq => eq.equipment_name === constraintValue && eq.lab_room === labName);
                }
                // Fixed lab: must match exactly
                if (constraintType === 'laboratory') return labName === constraintValue;
                return true;
            }

            // Track assignments within this batch to prevent internal queue collisions
            const batchAssignments = [];

            schedulingQueue.forEach((queueItem) => {
                let assigned = null;
                const preferredShift = queueItem.timePreference;

                // Filter labs by asset requirement
                const eligibleLabs = laboratories.filter(l =>
                    labHasAsset(l.lab_name, queueItem.constraintType, queueItem.constraintValue)
                );

                // If no eligible labs found, fallback to all labs with warning
                const labsToTry = eligibleLabs.length > 0 ? eligibleLabs : laboratories;

                // Filter time slots by shift preference
                const slotsToTry = preferredShift === 'morning'
                    ? allTimeSlots.filter(ts => ts.shift === 'morning')
                    : preferredShift === 'afternoon'
                        ? allTimeSlots.filter(ts => ts.shift === 'afternoon')
                        : allTimeSlots;

                searchLoop:
                for (let attempt = 0; attempt < dayNames.length; attempt++) {
                    // 【核心改进】每次循环时，对工作日按“当前已分配课程数量”升序排序
                    // 哪天课最少，哪天就排在最前面，优先参与检索！
                    const sortedDays = [...dayNames].sort((a, b) => {
                        const countA = fallbackSlots.filter(s => s.day === a).length;
                        const countB = fallbackSlots.filter(s => s.day === b).length;
                        return countA - countB;
                    });

                    // 拿到当前最空闲的那一天
                    const day = sortedDays[attempt];

                    for (const lab of labsToTry) {
                        for (const ts of slotsToTry) {
                            // 检查数据库冲突
                            if (hasConflict(lab.lab_name, day, ts.start, ts.end)) continue;
                            
                            // 检查当前批次内部冲突
                            const batchConflict = batchAssignments.some(ba =>
                                ba.lab_name === lab.lab_name &&
                                ba.day === day &&
                                ts.start < ba.end && ts.end > ba.start
                            );
                            if (batchConflict) continue;
                            
                            // 检查讲师冲突
                            const lecturerConflict = batchAssignments.some(ba =>
                                ba.lecturerId && queueItem.lecturerId &&
                                ba.lecturerId === queueItem.lecturerId &&
                                ba.day === day &&
                                ts.start < ba.end && ts.end > ba.start
                            );
                            if (lecturerConflict) continue;

                            // 成功捕获最空闲工作日的干净时段
                            assigned = {
                                course_id: queueItem.courseId,
                                course_name: queueItem.courseName,
                                lab_id: lab.id,
                                lab_name: lab.lab_name,
                                day: day,
                                time_slot: `${day} ${ts.label}`,
                                verification: eligibleLabs.length > 0
                                    ? "✓ Engine — Dynamic Load Balanced"
                                    : "⚠ Engine — Balanced via Asset Approximation",
                                log: `Load-balancer routed into ${day} ${ts.label} (${lab.lab_name})`
                            };
                            
                            batchAssignments.push({
                                lab_name: lab.lab_name,
                                day: day,
                                start: ts.start,
                                end: ts.end,
                                lecturerId: queueItem.lecturerId
                            });
                            break searchLoop;
                        }
                    }
                }

                // Absolute fallback if no slot found at all
                if (!assigned) {
                    assigned = {
                        course_id: queueItem.courseId,
                        course_name: queueItem.courseName,
                        lab_id: null,
                        lab_name: "No Available Slot",
                        time_slot: "Unassignable",
                        verification: "✗ All slots exhausted or conflicting",
                        log: "Local engine could not find a non-colliding slot."
                    };
                }

                fallbackSlots.push(assigned);
            });

            renderResultTable(fallbackSlots, "Intelligent Local Anti-Collision Engine (AI Unavailable)", "warning");
            const noticeBanner = document.createElement('div');
            noticeBanner.className = 'alert alert-warning alert-dismissible fade show mb-3';
            noticeBanner.innerHTML = `
                <i class="fas fa-info-circle me-2"></i>
                <strong>Notice:</strong> DeepSeek AI is currently unavailable. Results were generated by the built-in anti-collision fallback engine and may not be optimally distributed. Please review before enrolling.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            responseContainer.insertBefore(noticeBanner, responseContainer.firstChild);
        } finally {
            analyzeButton.disabled = false;
        }
    }

    // ========================================================
    // UI RENDER: GENERATE ALTERNATIVES MATRIX TABLE
    // ========================================================

    // function renderResultTable(slotsArray, messageTitle, alertType) {
    //     let tableHtml = `
    //         <div class="alert alert-${alertType} d-flex align-items-center mb-3">
    //             <i class="fas ${alertType === 'success' ? 'fa-check-double' : 'fa-exclamation-triangle'} me-2"></i> 
    //             <div><strong>${messageTitle}:</strong> Matrix calculated successfully. Ready for active sync.</div>
    //         </div>
    function renderResultTable(slotsArray, messageTitle, alertType) {
        let tableHtml = `
           
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover align-middle text-center mt-2">
                    <thead class="table-dark">
                        <tr>
                            <th>Action</th>
                            <th>Sequence</th>
                            <th class="text-start">Staged Course Name</th>
                            <th>Assigned Free Lab Room</th>
                            <th>Solved Time Window</th>
                            <th>Target Constraint Verification</th>
                            <th>Dynamic Conflict Logs</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        slotsArray.forEach((slot, idx) => {
            // Resolve lab_id from lab_name in case the AI only sent the name
            let resolvedLabId = slot.lab_id;
            if (!resolvedLabId && laboratories.length > 0) {
                resolvedLabId = laboratories.find(l => l.lab_name === slot.lab_name)?.id ?? null;
            }
            // Fallback: if the queue knows this course, use its original lab constraint lab_id
            if (!resolvedLabId) {
                const queueEntry = schedulingQueue.find(q => q.courseId == slot.course_id);
                if (queueEntry && queueEntry.constraintType === 'laboratory') {
                    const labMatch = laboratories.find(l => l.lab_name === queueEntry.constraintValue);
                    if (labMatch) resolvedLabId = labMatch.id;
                }
            }

            // AI outputs "time_window" now; construct the full display slot from day_of_week + time_window
            const displaySlot = slot.time_window
                ? (slot.day_of_week ? `${slot.day_of_week} ${slot.time_window}` : slot.time_window)
                : (slot.time_slot || 'N/A');

            // AI outputs "conflict_log"; fallback to "verification" or "log" for backward compat
            const conflictInfo = slot.conflict_log || slot.verification || slot.log || 'N/A';

            tableHtml += `
                <tr>
                    <td>
                        <button class="btn btn-sm btn-success use-slot-btn" 
                                data-course-id="${slot.course_id || ''}" 
                                data-lab-id="${resolvedLabId || ''}" 
                                data-lab-name="${slot.lab_name || ''}" 
                                data-slot="${displaySlot}">
                            <i class="fas fa-calendar-check me-1"></i> Enroll/Book Row
                        </button>
                    </td>
                    <td><span class="badge bg-secondary">${idx + 1}</span></td>
                    <td class="text-start fw-bold text-dark">${slot.course_name || 'N/A'}</td>
                    <td><span class="badge bg-primary">${slot.lab_name || 'N/A'}</span></td>
                    <td class="text-success fw-semibold">${displaySlot}</td>
                    <td><span class="text-muted small">${conflictInfo}</span></td>
                    <td><span class="badge bg-light text-success">${conflictInfo}</span></td>
                </tr>
            `;
        });

        tableHtml += `
                    </tbody>
                </table>
            </div>
        `;
        responseContainer.innerHTML = tableHtml;
    }

    analyzeButton.addEventListener('click', executeBatchOptimization);

    // ========================================================
    // EVENT: COMMIT SINGLE ROW MATRIX VIA AJAX DYNAMIC INJECT
    // ========================================================
    document.addEventListener('click', async function (e) {
        const bookBtn = e.target.closest('.use-slot-btn');
        if (bookBtn) {
            const courseId = bookBtn.getAttribute('data-course-id');
            const labId = bookBtn.getAttribute('data-lab-id');
            const labName = bookBtn.getAttribute('data-lab-name');
            const timeSlot = bookBtn.getAttribute('data-slot');

            // Parse time slot: "Monday 08:00 AM - 10:00 AM"
            const tokens = timeSlot.split(' ');
            const dayOfWeek = tokens[0];
            
            let startHourStr = tokens[1];
            if (tokens[2] === 'PM' && !startHourStr.startsWith('12')) {
                const parts = startHourStr.split(':');
                startHourStr = `${parseInt(parts[0]) + 12}:${parts[1]}`;
            }
            let endHourStr = tokens[4];
            if (tokens[5] === 'PM' && !endHourStr.startsWith('12')) {
                const parts = endHourStr.split(':');
                endHourStr = `${parseInt(parts[0]) + 12}:${parts[1]}`;
            }

            const startTime = startHourStr.includes(':') && startHourStr.split(':').length === 2 ? `${startHourStr}:00` : startHourStr;
            const endTime = endHourStr.includes(':') && endHourStr.split(':').length === 2 ? `${endHourStr}:00` : endHourStr;

            bookBtn.disabled = true;
            bookBtn.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Syncing...`;

            try {
                const response = await fetch("{{ route('ai-scheduler.save') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({
                        semester_id: currentSemesterId,
                        day_of_week: dayOfWeek,
                        start_time: startTime,
                        end_time: endTime,
                        lab_id: labId,
                        course_id: courseId,
                        schedule_type: 'enroll'
                    })
                });

                const result = await response.json();

                if (result.success) {
                    alert(` Successfully Anchored into Active Timeline!\n\nReal Calculated Date: ${result.message}\nDatabase Row ID: ${result.schedule_id}\nRoom: ${labName}`);
                    const tableRow = bookBtn.closest('tr');
                    tableRow.style.transition = "all 0.4s ease";
                    tableRow.style.opacity = "0.4";
                    bookBtn.className = "btn btn-sm btn-secondary";
                    bookBtn.innerHTML = `<i class="fas fa-check-circle"></i> Committed`;
                } else {
                    alert(` Save Failed: ${result.message}`);
                    bookBtn.disabled = false;
                    bookBtn.innerHTML = `Enroll/Book Row`;
                }
            } catch (err) {
                console.error(err);
                alert(" System Registry Error or route disconnected.");
                bookBtn.disabled = false;
                bookBtn.innerHTML = `Enroll/Book Row`;
            }
        }
    });
</script>
@endsection
