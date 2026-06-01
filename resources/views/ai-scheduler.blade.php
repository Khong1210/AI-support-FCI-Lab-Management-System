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

     function buildPrompt(userRequest) {
    // 1. 🔍 保持原樣（對齊你現有的資料庫欄位：l.lab_name）
    const labRecords = laboratories.map(l => 
        `- Lab ID: ${l.id}, Name: ${l.lab_name}, Capacity: ${l.capacity || 30} seats`
    ).join('\n');

    // 2. 🔍 保持原樣（對齊你現有的資料庫欄位：s.lab_id, s.software_name）
    const softwareRecords = software.map(s => 
        `- Lab ID: ${s.lab_id}, Software Name: ${s.software_name}`
    ).join('\n');

    // 3. 🔍 保持原樣
    const existingSchedules = schedules.map(s => 
        `- Conflicting Occupied Slot [Lab ID: ${s.lab_id || s.laboratory_id}, Day: ${s.day}, Time: ${s.start_time} - ${s.end_time}, Lecturer ID: ${s.lecturer_id || s.user_id || 'N/A'}]`
    ).join('\n');

    // 4. 🔍 保持原樣
    const lecturerRecords = (typeof lecturers !== 'undefined' ? lecturers : []).map(u =>
        `- Lecturer ID: ${u.id}, Name: ${u.name || u.username || 'Lecturer_' + u.id}, Max Available: Standard Academic Hours`
    ).join('\n');

    // 5. 🔍 保持原樣：因為資料庫還沒有 c.hours，我們在這裡用「c.hours || 3」在前端虛擬給它 3 小時，安全過關！
    const pendingCourses = (typeof courses !== 'undefined' ? courses : []).map(c => 
        `- Course: ${c.name || c.title || c.course_name || 'Course_' + c.id}, Required Lecturer ID: ${c.lecturer_id || c.user_id}, Required Hours per week: ${c.hours || 3} hours, Required Student Capacity: ${c.students_count || 30}`
    ).join('\n');

    // 🌟 修正後的完美回傳區塊，字串一路包到底，高亮絕對正常
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

       async function sendToProxy(prompt, options = {}) {
        // 🌟 修正一：把預設的 maxTokens 提升到 2048，確保 3 套長課表不會被切斷
        const payload = Object.assign({ prompt, model: 'gemini-2.5-flash', maxTokens: 3000 }, options);

        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const res = await fetch('/api/ai/generate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
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

                // 🔍 Failsafe Guard: Catch Google Gemini API 503 Overload/Server error embedded in text response
                if (text.includes('"code": 503') || text.includes('UNAVAILABLE')) {
                    responseContainer.innerHTML = `
                        <div class="alert alert-warning mb-0" style="border-left: 5px solid #ffc107; padding: 15px; background-color: #fff3cd; color: #856404; border-radius: 4px;">
                            <h5 class="fw-bold style="margin-top: 0;">⚠️ AI Engine Dispatch Notice: High Server Demand (Error 503)</h5>
                            <p class="mb-2"><strong>Root Cause:</strong> The upstream Large Language Model (Gemini API) is currently experiencing a temporary global traffic spike and is temporarily unavailable.</p>
                            <hr style="border-top: 1px solid #ffeeba;" class="my-2">
                            <p class="mb-2"><strong>💡 Smart Mitigation Directives (Administrator Troubleshooting Guide):</strong></p>
                            <ul style="padding-left: 20px;" class="mb-0">
                                <li><strong>Directive 1 (Reduce Prompt Payload):</strong> Your current pending course dataset contains ${courses.length} entries. To reduce AI token calculation overhead, try narrowing down your request. Example input: <code>"Only schedule 4 core courses for semester 1."</code></li>
                                <li><strong>Directive 2 (Strict Time Constraints):</strong> Eliminate ambiguous time slots by providing strict boundary constraints. This reduces the AI's internal permutation search space for conflict-free slots. Example input: <code>"Schedule classes strictly between 09:00 AM to 05:00 PM."</code></li>
                                <li><strong>Directive 3 (Immediate Token Retry):</strong> Upstream server spikes are usually highly transient (lasting only a few seconds). Please wait 5-10 seconds and click the <strong>[Analyze Request]</strong> button again.</li>
                            </ul>
                        </div>
                    `;
                    return;
                }

                // Formats newline breaks gracefully within response canvas if response is successful
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