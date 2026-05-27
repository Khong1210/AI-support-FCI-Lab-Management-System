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

    <script type="importmap">
      {
        "imports": {
          "@google/generative-ai": "https://esm.run/@google/generative-ai"
        }
      }
    </script>

    <script type="module">
        import { GoogleGenerativeAI } from "@google/generative-ai";

        // 🌟 STEP 1: Paste your copied 'AIzaSy' key string from your Google AI Studio project below:
        const API_KEY = 'AIzaSyBZQZrbO6fSfIbcHBxj45sUJ7yR65rO6mQ'; 
        const genAI = new GoogleGenerativeAI(API_KEY);

        // Core Eloquent Data Model Mapping
        const schedules = @json($schedules ?? []);
        const software = @json($software ?? []);
        
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
            const scheduleLines = schedules.map((s) => 
                `- Laboratory: ${s.laboratory || s.lab || 'N/A'}, Day: ${s.day || s.weekday || 'N/A'}, Time: ${s.start_time || s.start || 'N/A'} - ${s.end_time || s.end || 'N/A'}, Activity: ${s.title || s.course || 'N/A'}`
            ).join('\n');

            const softwareByLab = (Array.isArray(software) ? software : []).reduce((acc, item) => {
                const labName = item.laboratory || item.lab || item.laboratory_name || item.location || 'Unknown';
                acc[labName] = acc[labName] || [];
                acc[labName].push(item.name || item.software || item.title || 'Unnamed');
                return acc;
            }, {});

            const softwareLines = Object.keys(softwareByLab).length
                ? Object.keys(softwareByLab).map(l => `- ${l}: ${softwareByLab[l].join(', ')}`).join('\n')
                : 'No software inventory provided.';

            return `You are the Lead Lab Scheduler for the FCI Management System.
Given the existing weekly schedule and software inventory data below, analyze the requested booking requirement to discover optimal non-conflicting time slots.

Current Schedule Data:
${scheduleLines || 'No existing schedules.'}

Software Inventory Data:
${softwareLines}

Faculty Manager Request:
"${userRequest}"

Constraints:
1. Identify 2-3 alternative available time slots for the requested laboratory.
2. Ensure ZERO overlapping conflicts exist with the provided schedule records.
3. If the request metadata is highly ambiguous, provide the most logical suggestion based on standard academic hours.
4. Output Format: Present each recommendation as a structured numbered list highlighting start/end times followed by a short analytical justification.
5. All terminology and outputs must strictly be in professional English.
6. CRITICAL GUARDRAIL: If any queried laboratory, software dependency, or configuration info is missing from the provided context dataset, explicitly state the word 'unknown' for that targeted laboratory asset. Do NOT hallucinate or assume resource availability. When 'unknown' is invoked, append exactly one clear recommended manual verification action item (e.g., 'Verify inventory via lab admin or network inventory registry').`;
        }

        // Direct Client-Side asynchronous execution loop
        async function analyzeRequest() {
            const userRequest = requestInput.value.trim();

            if (userRequest.length === 0) {
                responseContainer.innerHTML = '<p class="text-danger mb-0">Please enter a booking request before analysis.</p>';
                return;
            }

            responseContainer.innerHTML = '<p class="text-muted mb-0">⏳ Analyzing parameters against database context. Please wait...</p>';

            try {
                const prompt = buildPrompt(userRequest);

                // Utilizing SDK client layer directly. Bypasses the 404 backend proxy routes completely.
                const model = genAI.getGenerativeModel({ model: 'gemini-2.5-flash' });
                const result = await model.generateContent(prompt);
                const response = await result.response;
                const text = response.text();
                
                // Formats newline breaks gracefully within response canvas
                responseContainer.innerHTML = `
                    <div class="fw-semibold mb-3 text-success">✨ Recommended Slots Matrix:</div>
                    <div class="text-dark" style="line-height: 1.7; font-size: 14px;">${text.replace(/\n/g, '<br>')}</div>
                `;
            } catch (error) {
                responseContainer.innerHTML = `
                    <p class="text-danger mb-0">
                        <strong>AI Optimization Node Error:</strong> ${error.message ?? 'Inference processing failure. Verify client authorization key.'}
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