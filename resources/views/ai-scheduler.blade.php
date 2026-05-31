<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Scheduler | AI-Support FCI Lab Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUa6mY3V7mSI7xM9VHI6aO6aQ5zW5mQw2B9rw1lWvYJQiI6ht6f2L7jrZy3E" crossorigin="anonymous">
    <style>
        body {
            background-color: #f8fafc;
            color: #111827;
        }
        .scheduler-card {
            max-width: 900px;
            margin: 2.5rem auto;
        }
        .response-list li {
            margin-bottom: 0.8rem;
        }
        .form-note {
            font-size: 0.95rem;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="container scheduler-card">
        <div class="card shadow-sm border-0">
            <div class="card-body p-5">
                
                <div class="alert alert-info small" role="alert">
                    <strong>Demo Mode Active:</strong> Running client-side inference powered by Gemini 2.5 Flash. Production distribution builds will automatically fall back to secure server-side environment configurations.
                </div>

                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start mb-4">
                    <div>
                        <h1 class="h3 mb-2">AI Scheduler</h1>
                        <p class="text-muted mb-0">Analyze current lab schedules and receive high-quality recommendations for non-conflicting lab slots.</p>
                    </div>
                    <span class="badge bg-primary align-self-start mt-3 mt-md-0">Gemini 2.5 Flash Engine</span>
                </div>

                <div class="mb-4">
                    <label for="requestInput" class="form-label">Faculty Manager Booking Request</label>
                    <textarea id="requestInput" rows="4" class="form-control" placeholder="I need Lab A for 2 hours on Wednesday morning"></textarea>
                    <div class="form-note mt-2">Describe the lab, time range, weekday, and duration. The AI will recommend non-conflicting slots based on the current schedule.</div>
                </div>

                <div class="d-flex gap-2 mb-4">
                    <button id="analyzeButton" class="btn btn-primary">Analyze Request</button>
                    <button id="clearOutputButton" class="btn btn-outline-secondary">Clear Output</button>
                </div>

                <div class="mb-4">
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
                            <tbody id="scheduleTableBody">
                                </tbody>
                        </table>
                    </div>
                </div>

                <div>
                    <h2 class="h5 mb-3">AI Recommendation</h2>
                    <div id="responseContainer" class="border rounded-3 p-4 bg-white" style="min-height: 180px;">
                        <p class="text-muted mb-0">Enter a booking request and select Analyze Request to generate recommended non-conflicting slots.</p>
                    </div>
                </div>

            </div>
        </div>
    </div>

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

        // Handles rendering database structures to frontend tables
        function renderScheduleTable() {
            if (!Array.isArray(schedules) || schedules.length === 0) {
                scheduleTableBody.innerHTML = '<tr><td colspan="6" class="text-muted text-center">No schedule records available.</td></tr>';
                return;
            }

            scheduleTableBody.innerHTML = schedules.map((schedule) => {
                const lab = schedule.laboratory || schedule.laboratory_name || schedule.lab || 'Unknown';
                const day = schedule.day || schedule.weekday || 'Unknown';
                const start = schedule.start_time || schedule.start || 'N/A';
                const end = schedule.end_time || schedule.end || 'N/A';
                const details = schedule.title || schedule.course || schedule.description || '';

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

        // Dynamically structuralizes the analytical context payload
       function buildPrompt(userRequest) {
    // 1.  對齊你的 laboratories 表欄位：l.lab_name
    const labRecords = laboratories.map(l => 
        `- Lab ID: ${l.id}, Name: ${l.lab_name}, Capacity: ${l.capacity || 30} seats, Hardware: ${l.hardware || 'Standard High-Performance PC'}`
    ).join('\n');

    // 2.  精準對齊你的 software 表欄位（截圖中的 lab_id 和 software_name）
    const softwareRecords = software.map(s => 
        `- Lab ID: ${s.lab_id}, Software Name: ${s.software_name}`
    ).join('\n');

    // 3.  對齊你的 schedules 表欄位（如果有的話，做一點微調防錯）
    const existingSchedules = schedules.map(s => 
        `- Conflicting Occupied Slot [Lab ID: ${s.lab_id || s.laboratory_id}, Day: ${s.day}, Time: ${s.start_time} - ${s.end_time}, Lecturer ID: ${s.lecturer_id || s.user_id || 'N/A'}]`
    ).join('\n');

    // 4. ACTIVE LECTURERS 保持不變...
    const lecturerRecords = (typeof lecturers !== 'undefined' ? lecturers : []).map(u =>
        `- Lecturer ID: ${u.id}, Name: ${u.name}, Max Available: Standard Academic Hours`
    ).join('\n');

    // 5. 課程時數 Hours 補丁（解決你前面說的沒設定 hours 的問題，直接給預設 3 小時）
    const pendingCourses = (typeof courses !== 'undefined' ? courses : []).map(c => 
        `- Course: ${c.title || c.name}, Required Lecturer ID: ${c.lecturer_id || c.user_id}, Required Hours per week: ${c.hours || 3} hours, Required Student Capacity: ${c.students_count || 30}`
    ).join('\n');


 return `You are the Elite Academic AI Timetable Architect for the FCI Management System.

[STRICT DIRECTIVE: ELIMINATE ALL CHITCHAT & PREAMBLE]
- DO NOT introduce yourself. DO NOT say "As the Elite Academic AI Timetable Architect...".
- DO NOT write any long introductory analysis, observations, or descriptions of the dataset.
- START your response IMMEDIATELY with "### PROPOSAL OPTION 1". 
- Every single token must be saved for outputting the actual timetables. Move straight to the schedule generation!

Your core task is to dynamically generate THREE (3) distinct, comprehensive, conflict-free weekly timetable options based strictly on the provided backend datasets.

[BACKEND DATASET REGISTRY]
1. ALL REGISTERED LABORATORIES:
${labRecords || 'No lab data.'}

2. INSTALLED SOFTWARE INVENTORY:
${softwareRecords || 'No software data.'}

3. ACTIVE LECTURERS (FCI Faculty User Role 5 Profiles):
${lecturerRecords || 'No lecturer data.'}

4. PRE-EXISTING SCHEDULE RECORDS (MUST AVOID - ZERO OVERLAP CRITICAL):
${existingSchedules || 'No existing conflicting schedules.'}

5. PENDING COURSE ARRANGEMENTS FOR THIS SEMESTER (Tasks to Schedule):
${pendingCourses || 'No pending courses.'}

[FACULTY USER EXTRA DIRECTIONS]
"${userRequest}"

[CORE SCHEDULING CONSTRAINTS & LOGIC]
1. HARDWARE & CAPACITY MATCHING: A course can only be scheduled in a Laboratory if the lab's capacity >= course student count, AND all required software/hardware matches perfectly.
2. LECTURER NO-COLLISION LOCK: A lecturer CANNOT be scheduled to teach two different classes at the same time.
3. TIMETABLE PRIORITY (EARLIER IS BETTER): Prioritize slots starting from early morning (e.g., 08:00 AM / 09:00 AM) onwards from Monday to Friday.
4. VARIETY REQUIREMENT: Generate exactly THREE (3) distinct, alternative full-schedule options.

[MANDATORY OUTPUT FORMAT STRUCTURE]
You MUST start directly with the template below. No greeting, no summary:

=========================================
### PROPOSAL OPTION [X] (1, 2, or 3)
=========================================

#### 👨‍🏫 VIEWPOINT A: LECTURER-CENTRIC TIMETABLE
* **Lecturer Name:** [Name]
  - [Day], [Start Time] - [End Time] | Course: [Title] | Assigned Room: [Lab Name] | Justification: [Optimal slot]

#### 🏫 VIEWPOINT B: LABORATORY-CENTRIC TIMETABLE
* **Laboratory Room:** [Lab Name]
  - [Day], [Start Time] - [End Time] | Active Class: [Title] | Lecturer: [Name] | Software Verified: [Yes/No]

-----------------------------------------
[MANDATORY TRANSMISSION END SIGNAL]
When you have successfully completed generating all 3 options, you MUST explicitly output this exact string at the very end of your response to confirm completion:
"🎉 [SUCCESS END OF TRANSMISSION] - AI Agent out. Thank you and Goodbye!"`;
}

        // Server-side proxy function (API key stored securely in .env on server)
       async function sendToProxy(prompt, options = {}) {
        // 🌟 修正一：把預設的 maxTokens 提升到 2048，確保 3 套長課表不會被切斷
        const payload = Object.assign({ prompt, model: 'gemini-2.5-flash', maxTokens: 3000 }, options);

        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const res = await fetch('/api/ai/generate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                // 🌟 修正二：告訴後端我們接受純文字
                'Accept': 'text/plain, application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': token,
            },
            body: JSON.stringify(payload),
        });

        if (!res.ok) {
            const text = await res.text();
            throw new Error(`Proxy request failed (${res.status}): ${text}`);
        }

        // 🌟 🌟 終極修正：拋棄 res.json()！
        // 因為後端已經把 JSON 剥開了，這裡我們直接拿純文字字串（String）
        const pureText = await res.text();

        // 直接把純文字回傳回去，讓後面的 .replace(/\n/g, '<br>') 可以完美運行！
        return pureText;
    }

        // Direct request analysis using the server proxy
        async function analyzeRequest() {
            const userRequest = requestInput.value.trim();

            if (userRequest.length === 0) {
                responseContainer.innerHTML = '<p class="text-danger mb-0">Please enter a booking request before analysis.</p>';
                return;
            }

            responseContainer.innerHTML = '<p class="text-muted mb-0">⏳ Analyzing parameters against database context. Please wait...</p>';

            try {
                const prompt = buildPrompt(userRequest);
                const text = await sendToProxy(prompt, { maxTokens: 3000 });

                // Formats newline breaks gracefully within response canvas
                responseContainer.innerHTML = `
                    <div class="fw-semibold mb-3 text-success">✨ Recommended Slots Matrix:</div>
                    <div class="text-dark" style="line-height: 1.7; font-size: 14px;">${text.replace(/\n/g, '<br>')}</div>
                `;
            } catch (error) {
                responseContainer.innerHTML = `
                    <p class="text-danger mb-0">
                        <strong>Error:</strong> ${error.message ?? 'Request processing failed. Verify server proxy is configured.'}
                    </p>
                `;
                console.error(error);
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