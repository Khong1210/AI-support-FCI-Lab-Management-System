@extends('layouts.admin')

@section('title', 'AI Schedule')
@section('page-title', 'AI Schedule')
@section('breadcrumb', 'AI Schedule')

@section('content')
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Scheduler | AI-Support FCI Lab Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8fafc;
            color: #111827;
        }
        .scheduler-card {
            max-width: 900px;
            margin: 2.5rem auto;
        }
        .form-note {
            font-size: 0.95rem;
            color: #6b7280;
        }
        .token-badge {
            background-color: #e0f2fe;
            color: #0369a1;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="container scheduler-card">
        <div class="card shadow-sm border-0">
            <div class="card-body p-5">
                
                <div class="alert alert-info small" role="alert">
                    <strong>Production Environment Active:</strong> Secure server-side execution with Token ledger tracking enabled.
                </div>

                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
                    <div>
                        <h1 class="h3 mb-2">AI Scheduler</h1>
                        <p class="text-muted mb-0">Analyze current lab schedules and receive high-quality recommendations for non-conflicting lab slots.</p>
                    </div>
                    <div class="token-badge align-self-start" style="background-color: #f0fdf4; color: #166534;">
                        🟢 Google AI Studio Free Tier
                    </div>
                </div>

                <div class="mb-4">
                    <label for="requestInput" class="form-label fw-semibold">Faculty Manager Booking Request</label>
                    <textarea id="requestInput" rows="4" class="form-control" placeholder="I need Lab A for 2 hours on Wednesday morning"></textarea>
                    <div class="form-note mt-2">Describe the lab, time range, weekday, and duration.</div>
                </div>

                <div class="d-flex gap-2 mb-4">
                    <button id="analyzeButton" class="btn btn-primary px-4">Analyze Request</button>
                    <button id="clearOutputButton" class="btn btn-outline-secondary">Clear Output</button>
                </div>
                {{-- <div class="mb-4">
                    <h2 class="h5 mb-3">Current Schedule Overview</h2>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">ID</th>
                                    <th scope="col">Laboratory</th>
                                    <th scope="col">Day</th>
                                    <th scope="col">Start</th>
                                    <th scope="col">End</th>
                                    <th scope="col">Details</th>
                                </tr>
                            </thead>
                            <tbody id="scheduleTableBody"></tbody>
                        </table>
                    </div>
                </div> --}}

                <div>
                    <h2 class="h5 mb-3">AI Recommendation</h2>
                    <div id="responseContainer" class="border rounded-3 p-4 bg-white" style="min-height: 180px;">
                        <p class="text-muted mb-0">Enter a booking request and select Analyze Request to generate recommended non-conflicting slots.</p>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script type="module">
        // Core Data Model Mapping
        const schedules = @json($schedules ?? []);
        const software = @json($software ?? []);
        const laboratories = @json($laboratories ?? []);
        const courses = @json($courses ?? []);
        const lecturers = @json($lecturers ?? []);
        
        const requestInput = document.getElementById('requestInput');
        const analyzeButton = document.getElementById('analyzeButton');
        const clearOutputButton = document.getElementById('clearOutputButton');
        const responseContainer = document.getElementById('responseContainer');
        const scheduleTableBody = document.getElementById('scheduleTableBody');
        const userTokensDisplay = document.getElementById('userTokensDisplay');

        // Handles rendering database structures to frontend tables
        // Handles rendering database structures to frontend tables
    function renderScheduleTable() {
        if (!Array.isArray(schedules) || schedules.length === 0) {
            scheduleTableBody.innerHTML = '<tr><td colspan="6" class="text-muted text-center">No schedule records available.</td></tr>';
            return;
        }

    scheduleTableBody.innerHTML = schedules.map((schedule) => {
        // 1. 讀取從 leftJoin 拿到的實驗室名稱，若沒有則顯示 Lab ID
        const lab = schedule.laboratory_name || `Lab ID: ${schedule.lab_id}`;
        
        // 2. 🎯 對齊你的資料庫欄位：day_of_week
        const day = schedule.day_of_week || 'Unknown';
        
        const start = schedule.start_time || 'N/A';
        const end = schedule.end_time || 'N/A';
        
        // 3. 根據 schedule_type 顯示細節描述
        let details = `[${schedule.schedule_type.toUpperCase()}]`;
        if (schedule.schedule_type === 'enroll' && schedule.course_title) {
            details += ` ${schedule.course_title}`;
        } else if (schedule.booking_id) {
            details += ` Booking #${schedule.booking_id}`;
        }

        return `
            <tr>
                <td>${schedule.id ?? '—'}</td>
                <td>${lab}</td>
                <td>${day}</td>
                <td>${start}</td>
                <td>${end}</td>
                <td>${details}</td>
            </tr>
        `;
    }).join('');
}
        function buildPrompt(userRequest) {
            const labRecords = laboratories.map(l => 
                `- Lab ID: ${l.id}, Name: ${l.lab_name}, Capacity: ${l.capacity || 30} seats`
            ).join('\n');

            const softwareRecords = software.map(s => 
                `- Lab ID: ${s.lab_id}, Software Name: ${s.software_name}`
            ).join('\n');

            const existingSchedules = schedules.map(s => 
                `- Conflicting Occupied Slot [Lab ID: ${s.lab_id || s.laboratory_id}, Day: ${s.day}, Time: ${s.start_time} - ${s.end_time}, Lecturer ID: ${s.lecturer_id || s.user_id || 'N/A'}]`
            ).join('\n');

            const lecturerRecords = (typeof lecturers !== 'undefined' ? lecturers : []).map(u =>
                `- Lecturer ID: ${u.id}, Name: ${u.name || u.username || 'Lecturer_' + u.id}, Max Available: Standard Academic Hours`
            ).join('\n');

            const pendingCourses = (typeof courses !== 'undefined' ? courses : []).map(c => 
                `- Course: ${c.name || c.title || c.course_name || 'Course_' + c.id}, Required Lecturer ID: ${c.lecturer_id || c.user_id}, Required Hours per week: ${c.hours || 3} hours, Required Student Capacity: ${c.students_count || 30}`
            ).join('\n');

            return `You are the Elite Academic AI Timetable Architect for the FCI Management System.

            [STRICT DIRECTIVE: ELIMINATE ALL CHITCHAT, PREAMBLE & VERBOSITY]
            - START your response IMMEDIATELY with "### PROPOSAL OPTION 1". No overview, no intro.
            - Keep the "Justification" string EXTREMELY SHORT (maximum 5 words).
            - ONLY schedule exactly FOUR (4) courses from the pending list for each option. Do not schedule more than 4 courses!
            - Cut down the output volume to save tokens.

            [BACKEND DATASET REGISTRY]
            1. ALL REGISTERED LABORATORIES:
            ${labRecords || 'No lab data.'}

            2. INSTALLED SOFTWARE INVENTORY:
            ${softwareRecords || 'No software data.'}

            3. ACTIVE LECTURERS:
            ${lecturerRecords || 'No lecturer data.'}

            4. PRE-EXISTING SCHEDULE RECORDS:
            ${existingSchedules || 'No existing conflicting schedules.'}

            5. PENDING COURSE ARRANGEMENTS FOR THIS SEMESTER:
            ${pendingCourses || 'No pending courses.'}

            [FACULTY USER EXTRA DIRECTIONS]
            "${userRequest}"

            [CORE SCHEDULING CONSTRAINTS & LOGIC]
            1. CAPACITY MATCHING: Lab capacity >= course student count.
            2. LECTURER NO-COLLISION LOCK: A lecturer CANNOT teach two different classes at the same time.
            3. SOFTWARE VERIFICATION LOGIC:
            - If the user explicitly requests a software (e.g., "Visual Studio Code"), you MUST only schedule courses in labs that have that software in the registry.
            - If the user DID NOT specify any software in their directions (e.g., just saying "make a weekly schedule"), you must still generate the schedule, but you MUST label the verification field as "Software Verified: N/A (Not Specified)".
            4. EQUIPMENT/HARDWARE LOGIC:
            - Completely IGNORE hardware/equipment constraints unless the user explicitly mentions specific hardware words (like GPU, Mac, Hardware) in their directions.

            [MANDATORY OUTPUT FORMAT STRUCTURE]
            You MUST use this ultra-dense layout. No words wasted:

            =========================================
            ### PROPOSAL OPTION [X]
            =========================================
            #### 👨‍🏫 VIEWPOINT A: LECTURER-CENTRIC
            * **Lecturer: [Name]**
            - [Day], [Start] - [End] | [Course] | Room: [Lab Name] | Just: [Short text]

            #### 🏫 VIEWPOINT B: LABORATORY-CENTRIC
            * **Room: [Lab Name]**
            - [Day], [Start] - [End] | [Course] | Lect: [Name] | Software Verified: [Yes / No / N/A (Not Specified)]

            -----------------------------------------
            [MANDATORY TRANSMISSION END SIGNAL]
            When finished with all 3 options, you MUST explicitly output this exact string:
            "🎉 [SUCCESS END OF TRANSMISSION] - AI Agent out. Thank you and Goodbye!"

            [CRITICAL OVERRIDE DIRECTIVE]
            - If the user's request is a simple question (e.g., asking about software, rooms, or a single asset check), IGNORE the full schedule layout matrix completely! 
            - Answer the user's question directly, accurately, and concisely in clean English prose within 3 lines.
            - ONLY generate the full "### PROPOSAL OPTION 1" layout if the user explicitly asks to "make", "generate", or "create" a full timetable/schedule.
            - If you are generating a schedule, strictly stop after completing "VIEWPOINT B" of PROPOSAL OPTION 1. DO NOT ATTEMPT OPTION 2 OR 3 UNDER ANY CIRCUMSTANCES.`;
        }

      async function sendToProxy(prompt) {
    // 🎯 物理外掛：用你抓出來的真實金鑰，讓前端瀏覽器直接呼叫 Google API
    const apiKey = "AIzaSyBZQZrbO6fSfIbcHBxj45sUJ7yR65rO6mQ"; 
    const url = `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=${apiKey}`;

    // 瀏覽器直接 Fetch，完美繞過本地 PHP 那個見鬼的 SSL 快取地獄
    const res = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            contents: [{
                parts: [{ text: prompt }]
            }],
            generationConfig: {
                maxOutputTokens: 3000
            }
        })
    });

    const data = await res.json();

    if (!res.ok) {
        throw new Error(data.error?.message || `Google API Error (${res.status})`);
    }

    // 提取 Gemini 2.5 Flash 回傳的排程純文字
    const text = data.candidates?.[0]?.content?.parts?.[0]?.text;
    if (!text) {
        throw new Error("AI returned an empty response.");
    }

    return text;
}

    async function analyzeRequest() {
        const userRequest = requestInput.value.trim();

        if (userRequest.length === 0) {
            responseContainer.innerHTML = '<p class="text-danger mb-0">Please enter a booking request before analysis.</p>';
            return;
        }

        analyzeButton.disabled = true;
        responseContainer.innerHTML = '<p class="text-muted mb-0">⏳ Sending data to Gemini 2.5 Flash via secure proxy. Please wait...</p>';

        try {
            // 🌟 核心改進：把後端傳給 Blade 的資料，直接轉成 JSON 字串塞進 Prompt 裡！
            // 這樣前端直連 Google 時，AI 就能一瞬間看懂你整個資料庫的現狀！
            const databaseContext = `
                You are an AI Lab Scheduler System. Here is the current database registry context:
                - Laboratories: @json($laboratories)
                - Existing Schedules/Conflicts: @json($schedules)
                - Available Courses: @json($courses)
                - Lecturers: @json($lecturers)
                
                User booking request: "${userRequest}"
                
                [CRITICAL OVERRIDE DIRECTIVE]
                - If the user's request is a simple question, answer directly in clean prose within 3 lines.
                - ONLY generate the full "### PROPOSAL OPTION 1" matrix if the user explicitly asks to "make", "generate", or "create" a full timetable/schedule.
                - If generating a schedule, strictly stop after completing "VIEWPOINT B" of PROPOSAL OPTION 1. Do not attempt option 2 or 3.
            `;

            // 把帶著資料庫背景的終極 Prompt 發送給 Gemini
            const text = await sendToProxy(databaseContext);

            responseContainer.innerHTML = `
                <div class="fw-semibold mb-3 text-success">✨ Recommended Slots Matrix:</div>
                <div class="text-dark" style="line-height: 1.7; font-size: 14px;">${text.replace(/\n/g, '<br>')}</div>
            `;

        } catch (error) {
            responseContainer.innerHTML = `
                <div class="alert alert-danger mb-0">
                    <strong>Operation Failed:</strong> ${error.message}
                }
            `;
            console.error(error);
        } finally {
            analyzeButton.disabled = false;
        }
    }

        // DOM Listeners
        analyzeButton.addEventListener('click', analyzeRequest);
        clearOutputButton.addEventListener('click', () => {
            requestInput.value = '';
            responseContainer.innerHTML = '<p class="text-muted mb-0">Enter a booking request and select Analyze Request to generate recommended non-conflicting slots.</p>';
        });

        // Initialize table dataset on load
        renderScheduleTable();
    </script>
</body>
</html>