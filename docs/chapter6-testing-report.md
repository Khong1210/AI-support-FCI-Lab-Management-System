# Chapter 6: Testing

---

## 6.1 Test Data

### 6.1.1 Sample Test Data from Database Seeders

The system comes with sample data already prepared in the database seeders. Below are the real values you can use for testing.

#### Sample User Accounts

All user accounts use the same password: **12345678**

| Role | Username | Email | Role ID |
|------|----------|-------|---------|
| Admin | `admin-1` | `admin1@example.com` | 1 |
| Faculty Manager | `faculty-manager-1` | `faculty.manager1@example.com` | 2 |
| Lab Staff | `lab-staff-1` | `lab.staff1@example.com` | 3 |
| Lab Committee | `lab-committee-1` | `lab.committee1@example.com` | 4 |
| Lecturer 1 | `lecturer-1` | `lecturer1@example.com` | 5 |
| Lecturer 2 | `lecturer-2` | `lecturer2@example.com` | 5 |
| Lecturer 3 | `lecturer-3` | `lecturer3@example.com` | 5 |

#### Sample Laboratories

| Lab Name | Capacity | Status |
|----------|----------|--------|
| AR1001 | 30 | Available |
| AR1002 | 30 | Available |
| AR1003 | 30 | Available |
| AR2002 | 25 | Available |
| AR2003 | 25 | Available |

#### Sample Courses

| Course Code - Name | Hours per Week | Assigned Lecturer |
|--------------------|----------------|-------------------|
| MPU3023 - Islamic and Asian Civilization | 3 | lecturer-1 |
| CS1133 - Programming Fundamentals | 2 | lecturer-1 |
| CS1213 - Data Structures | 2 | lecturer-2 |
| CS2123 - Web Development Basics | 2 | lecturer-2 |
| CS2133 - Database Management Systems | 2 | lecturer-2 |
| CS3113 - Software Engineering | 2 | lecturer-3 |
| CS2243 - Computer Networks | 1 | lecturer-3 |
| CS3143 - Operating Systems | 2 | lecturer-3 |
| CS3223 - Artificial Intelligence | 1 | lecturer-1 |
| CS3243 - Cybersecurity Fundamentals | 1 | lecturer-2 |

#### Sample Semesters

| Semester Name | Start Date | End Date |
|---------------|------------|----------|
| Trimester 1 | 2025-11-03 | 2026-02-08 |
| Trimester 2 | 2026-03-30 | 2026-07-05 |
| Trimester 3 | 2026-08-10 | 2026-10-27 |

#### Sample Equipment (First 5 Items)

| Equipment Name | Serial Number | Type | Lab |
|----------------|---------------|------|-----|
| Desktop Computer - Dell Optiplex | DELL-OP-001-2024 | Computer | AR1001 |
| Monitor LG 27" 4K | LG-27-4K-001 | Monitor | AR1001 |
| Keyboard Mechanical RGB | MECH-RGB-001 | Peripheral | AR1001 |
| Mouse Logitech MX Master 3 | LOG-MX-001 | Peripheral | AR1001 |
| Printer HP LaserJet Pro | HP-LJ-001-2024 | Printer | AR1001 |

#### Sample Software (First 5 Items)

| Software Name | Version | Lab |
|---------------|---------|-----|
| Visual Studio Code | 1.96.0 | AR1001 |
| Python 3.11 | 3.11.5 | AR1001 |
| GCC Compiler | 13.2.0 | AR1001 |
| Git Version Control | 2.42.0 | AR1001 |
| Docker Desktop | 24.0.6 | AR1001 |

---

## 6.2 Test Cases

### 6.2.1 Test Case T02: Check That Only Admin Can Create a Lab. Manager Can Only Read and Update Data.

**Test Steps:**

1. Log in as **Admin** (username: `admin-1`, password: `12345678`).
2. Click "Laboratories" in the sidebar menu.
3. Click the "Add Laboratory" button.
4. Fill in the form with a new lab name (e.g., `AR3001`), set capacity to `20`, and set status to "Available". Then click the "Save" button.
5. Log out. Then log in as **Faculty Manager** (username: `faculty-manager-1`, password: `12345678`).
6. Go to the "Laboratories" page. Check if the "Add Laboratory" button is visible or clickable.
7. Try to open the create-lab page directly by typing `/laboratories/create` at the end of the website URL in the browser address bar.

**Expected Result:**

For Admin: the new lab is saved and appears in the lab list with the name `AR3001`. For Manager: the "Add Laboratory" button is hidden in the view. Accessing `/laboratories/create` by typing the URL directly returns a "403 Unauthorized" error page. The Manager can only view the lab list and edit existing labs.

---

### 6.2.2 Test Case T08: Check That Manager Can Do Full CRUD (Create, Read, Update, Delete) for Software.

**Test Steps:**

1. Log in as **Faculty Manager** (username: `faculty-manager-1`, password: `12345678`).
2. Click "Software" in the sidebar menu to view all software (Read).
3. Click the "Add Software" button. Fill in the form: Lab = `AR1001`, Software Name = `TestApp`, Version = `1.0`, Expiry Date = `2026-12-31`, Status = `Active`. Click "Save" (Create).
4. In the software list, find `TestApp` and click the "Edit" button. Change the version to `2.0` and click "Update" (Update).
5. Find `TestApp` again and click the "Delete" button. Confirm the delete action (Delete).
6. Check that `TestApp` no longer appears in the software list.

**Expected Result:**

The Manager can see all software in the list, add new software, edit existing software, and delete it. After deletion, `TestApp` is removed from the list and a success message is shown.

---

### 6.2.3 Test Case T11: Check That the AI Engine Can Generate and Save the Timetable Automatically.

**Test Steps:**

1. Log in as **Admin** (username: `admin-1`, password: `12345678`).
2. Click "AI Scheduler" in the sidebar menu.
3. From the "Semester" dropdown, select "Trimester 2".
4. In the "Prompt / Instructions" text box, type a simple instruction like: "Schedule CS1133 - Programming Fundamentals for lab AR1001."
5. Click the "Generate Schedule" button and wait for the AI response.
6. Review the AI-generated timetable slots that appear on the screen. Click the "Save Schedule" button to save the generated timetable.
7. Go to the "Schedules" page and check that the new schedule entry appears with the correct lab, course, day, and time.

**Expected Result:**

After clicking "Generate Schedule", the system shows a list of AI-generated timetable slots with course name, lab name, day of week, and time window. After clicking "Save Schedule", a success message is shown. The new timetable entry appears on the Schedules page with `is_recurring = 1` (weekly repeating).

---

### 6.2.4 Test Case T14: Check the Fault Ticket Workflow from Reported, to In-Progress, to Resolved.

**Test Steps:**

1. Log in as **Lecturer** (username: `lecturer-1`, password: `12345678`).
2. Click "Reports" in the sidebar menu.
3. Click the "Add Report" button. Fill in the form: Lab = `AR1001`, Issue Type = `Hardware Fault`, Description = `Monitor screen is flickering`, Reported Date = today's date. Click "Submit".
4. Log out. Then log in as **Lab Staff** (username: `lab-staff-1`, password: `12345678`).
5. Go to the "Reports" page. Find the new ticket in the list. Click the "Mark In Progress" button.
6. Click the "Resolve" button next to the same ticket.
7. Check that the ticket status changes through each step.

**Expected Result:**

After step 3, the ticket appears in the Reports list with status "Open". After step 5, the status changes to "In Progress". After step 6, the status changes to "Resolved". The lecturer can see their own ticket in the list and its status updates.

---

### 6.2.5 Test Case T30: Check That the System Blocks Double-Booking for the Same Room at the Same Time.

**Test Steps:**

1. Log in as **Admin** (username: `admin-1`, password: `12345678`).
2. Click "Bookings" in the sidebar menu. Then click "Add Booking".
3. Fill in the form: Lab = `AR1001`, Purpose = `First booking test`, Date = pick a Monday (e.g., 2026-05-04), Start Time = `08:00`, End Time = `10:00`. Click "Save".
4. The first booking is created. Now click "Add Booking" again.
5. Fill in the form again with the same details: same Lab (`AR1001`), same Date, Start Time = `08:00`, End Time = `10:00`. Click "Save".
6. Observe the response from the system.

**Expected Result:**

The first booking is saved successfully. When trying to create the second booking with the same room and same time, the system rejects it and shows an error message: "Time slot conflict detected." The second booking is not saved.

---

### 6.2.6 Test Case T31: Check That Regular Users Cannot Access Admin Pages by Typing the URL Directly.

**Test Steps:**

1. Log in as **Lecturer** (username: `lecturer-1`, password: `12345678`).
2. After logging in, look at the sidebar menu. Note which pages are available.
3. In the browser address bar, replace the current URL path with `/management/users` (e.g., `http://localhost/management/users`) and press Enter.
4. Clear the address bar and try `/ai-scheduler` and press Enter.
5. Clear the address bar and try `/laboratories/create` and press Enter.
6. Clear the address bar and try `/management/users/add` and press Enter.

**Expected Result:**

For every URL that the Lecturer is not allowed to access, the system shows a "403 Unauthorized" error page. The Lecturer can only access pages meant for their role (like Bookings, Reports, and viewing Schedules, Laboratories, Equipment, Software).