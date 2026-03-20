<x-client-portal-layout>
    <style>
        .doc-section { background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:1.75rem; margin-bottom:1.5rem; }
        .doc-section h4 { color:#0f4c75; border-bottom:2px solid #e2e8f0; padding-bottom:.5rem; margin-bottom:1rem; }
        .doc-section h5 { color:#1B6CA8; margin-top:1.25rem; }
        .step { display:flex; gap:1rem; margin-bottom:.75rem; }
        .step-num { width:28px;height:28px;background:#0f4c75;color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.8rem;flex-shrink:0;margin-top:2px; }
        .tip { background:#eff6ff; border-left:4px solid #3b82f6; padding:.6rem 1rem; border-radius:4px; font-size:.9rem; margin:.75rem 0; }
        .warn { background:#fff7ed; border-left:4px solid #f97316; padding:.6rem 1rem; border-radius:4px; font-size:.9rem; margin:.75rem 0; }
        .code-val { font-family:monospace; background:#f1f5f9; padding:1px 6px; border-radius:4px; font-size:.9rem; }
        .toc a { display:block; padding:.25rem 0; color:#0f4c75; text-decoration:none; }
        .toc a:hover { text-decoration:underline; }
        table.doc-table { width:100%; border-collapse:collapse; font-size:.9rem; }
        table.doc-table th { background:#0f4c75; color:#fff; padding:.5rem .75rem; text-align:left; }
        table.doc-table td { padding:.45rem .75rem; border-bottom:1px solid #f1f5f9; }
        table.doc-table tr:hover td { background:#f8fafc; }
    </style>

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-book me-2 text-primary"></i>Client Portal — User Manual</h4>
            <p class="text-muted small mb-0">How to use the CleanSheen Client Portal for attendance management</p>
        </div>
        <a href="{{ route('client.dashboard') }}" class="btn btn-outline-secondary btn-sm rounded-pill">
            <i class="bi bi-arrow-left me-1"></i> Dashboard
        </a>
    </div>

    <div class="row g-4">
        {{-- TOC --}}
        <div class="col-lg-3">
            <div class="doc-section sticky-top" style="top:1rem;">
                <h6 class="fw-bold mb-3">Contents</h6>
                <div class="toc small">
                    <a href="#login">1. Logging In</a>
                    <a href="#dashboard">2. Dashboard</a>
                    <a href="#day-attendance">3. Mark Daily Attendance</a>
                    <a href="#monthly-upload">4. Monthly Bulk Upload</a>
                    <a href="#time-formats">5. Time Format Guide</a>
                    <a href="#history">6. Viewing History</a>
                    <a href="#profile">7. Profile & Password</a>
                    <a href="#faq">8. Frequently Asked Questions</a>
                </div>
            </div>
        </div>

        <div class="col-lg-9">

            {{-- 1. Login --}}
            <div class="doc-section" id="login">
                <h4><i class="bi bi-box-arrow-in-right me-2"></i>1. Logging In</h4>
                <div class="step"><div class="step-num">1</div><div>Open the HRMS website in your browser.</div></div>
                <div class="step"><div class="step-num">2</div><div>Enter your <strong>email address</strong> and <strong>password</strong> provided by CleanSheen admin.</div></div>
                <div class="step"><div class="step-num">3</div><div>Click <strong>Login</strong>. You will be taken to your Client Dashboard.</div></div>
                <div class="tip">If you forgot your password, click <strong>Forgot Password</strong> on the login page, or contact CleanSheen admin to reset it.</div>
            </div>

            {{-- 2. Dashboard --}}
            <div class="doc-section" id="dashboard">
                <h4><i class="bi bi-speedometer2 me-2"></i>2. Dashboard</h4>
                <p>After logging in, your dashboard shows:</p>
                <ul>
                    <li><strong>Active Sites</strong> — Number of sites assigned to you.</li>
                    <li><strong>Present Today</strong> — How many employees are marked present across all your sites today.</li>
                    <li><strong>Absent Today</strong> — How many are marked absent.</li>
                </ul>
                <p>Each site card shows the site name, number of assigned employees, and today's present/absent count.</p>
                <p>Two buttons are available per site:</p>
                <ul>
                    <li><strong>Mark Today</strong> — Opens the daily attendance form for that site.</li>
                    <li><strong>Calendar icon</strong> — Opens the Monthly Bulk Upload page for that site.</li>
                </ul>
            </div>

            {{-- 3. Day Attendance --}}
            <div class="doc-section" id="day-attendance">
                <h4><i class="bi bi-calendar-day me-2"></i>3. Mark Daily Attendance</h4>
                <p>Use this to mark attendance for a single day at a time.</p>

                <div class="step"><div class="step-num">1</div><div>Click <strong>Mark Today</strong> on any site card from the dashboard.</div></div>
                <div class="step"><div class="step-num">2</div><div>Select the <strong>date</strong> using the date picker (today is pre-selected; you cannot select future dates).</div></div>
                <div class="step"><div class="step-num">3</div><div>The page shows two sections:
                    <ul class="mt-1">
                        <li><span class="badge bg-success">Already Recorded</span> — Employees who already have attendance for this date.</li>
                        <li><span class="badge bg-primary">Mark Attendance</span> — Employees still pending for this date.</li>
                    </ul>
                </div></div>
                <div class="step"><div class="step-num">4</div><div>For pending employees: tick the checkbox next to each employee you want to mark. Enter their <strong>Clock In</strong> and <strong>Clock Out</strong> times.</div></div>
                <div class="step"><div class="step-num">5</div><div>Click <strong>Select All</strong> to tick everyone at once.</div></div>
                <div class="step"><div class="step-num">6</div><div>Click <strong>Submit Attendance</strong>. Records are saved instantly.</div></div>

                <h5>Editing an Existing Record</h5>
                <p>In the <strong>Already Recorded</strong> section, if a record is <em>not locked</em>, you will see a <i class="bi bi-pencil"></i> edit button. Click it to update the clock in/out times.</p>
                <div class="warn">A <span class="badge bg-secondary">Locked</span> badge means the record has been locked by the admin after payroll processing. You cannot edit locked records. Contact CleanSheen admin if a correction is needed.</div>

                <h5>Excel Upload (Day-wise)</h5>
                <p>You can also use the Excel upload for a single day:</p>
                <div class="step"><div class="step-num">1</div><div>Click <strong>Download Template</strong>. An Excel file pre-filled with employee names opens.</div></div>
                <div class="step"><div class="step-num">2</div><div>Fill in <strong>Clock In</strong> (column D) and <strong>Clock Out</strong> (column E) for each employee.</div></div>
                <div class="step"><div class="step-num">3</div><div>Save the file and click <strong>Upload Filled Sheet</strong> → choose the file → click Import.</div></div>
            </div>

            {{-- 4. Monthly Upload --}}
            <div class="doc-section" id="monthly-upload">
                <h4><i class="bi bi-calendar-month me-2"></i>4. Monthly Bulk Upload</h4>
                <p>Use this to upload attendance for an entire month at once — all employees, all days, in one Excel file.</p>

                <div class="step"><div class="step-num">1</div><div>From the dashboard, click the <i class="bi bi-calendar-month"></i> calendar icon on a site card. Or go to <strong>Site → Monthly Upload</strong>.</div></div>
                <div class="step"><div class="step-num">2</div><div>Select the <strong>Site</strong>, <strong>Month</strong>, and <strong>Year</strong> using the filter on the left. Click <strong>Apply</strong>.</div></div>
                <div class="step"><div class="step-num">3</div><div>Click <strong>Download Monthly Template</strong>. This downloads an Excel file where:
                    <ul class="mt-1">
                        <li>Each <strong>row</strong> is one employee.</li>
                        <li>Each <strong>column from D onwards</strong> represents one day of the month.</li>
                        <li>Sundays are pre-marked <span class="code-val">H</span> in red — leave as is.</li>
                        <li>Days already recorded are pre-filled in <span style="color:#166534">green</span> — leave as is.</li>
                        <li>Yellow cells are blank — fill in attendance for those days.</li>
                    </ul>
                </div></div>
                <div class="step"><div class="step-num">4</div><div>Fill in each yellow cell with the attendance value (see Time Format Guide below).</div></div>
                <div class="step"><div class="step-num">5</div><div>Save the file. Return to the monthly upload page and use the <strong>Upload</strong> form to submit it.</div></div>
                <div class="step"><div class="step-num">6</div><div>The system imports all new records. Already-recorded days are automatically skipped — no duplicates.</div></div>

                <div class="tip">You can upload the same file multiple times safely. Only new (not yet recorded) days will be imported each time.</div>
            </div>

            {{-- 5. Time Formats --}}
            <div class="doc-section" id="time-formats">
                <h4><i class="bi bi-clock me-2"></i>5. Time Format Guide</h4>
                <p>All Excel cells (day-wise or monthly) accept these formats:</p>
                <table class="doc-table">
                    <thead><tr><th>You Type</th><th>What It Means</th><th>Example</th></tr></thead>
                    <tbody>
                        <tr>
                            <td><span class="code-val">P</span></td>
                            <td>Present — no clock times recorded</td>
                            <td>Employee was present, time not tracked</td>
                        </tr>
                        <tr>
                            <td><span class="code-val">9-6</span></td>
                            <td>Clock in 9:00 AM, clock out 6:00 PM (18:00)</td>
                            <td>Standard day shift</td>
                        </tr>
                        <tr>
                            <td><span class="code-val">8-5</span></td>
                            <td>Clock in 8:00 AM, clock out 5:00 PM (17:00)</td>
                            <td>Early shift</td>
                        </tr>
                        <tr>
                            <td><span class="code-val">9:30-6:30</span></td>
                            <td>Clock in 9:30 AM, clock out 6:30 PM (18:30)</td>
                            <td>With minutes</td>
                        </tr>
                        <tr>
                            <td><span class="code-val">9-18</span></td>
                            <td>Clock in 9:00 AM, clock out 6:00 PM (18:00)</td>
                            <td>Using 24-hour clock out</td>
                        </tr>
                        <tr>
                            <td><span class="code-val">9</span></td>
                            <td>Clock in 9:00 AM only (no clock out)</td>
                            <td>When clock-out time is unknown</td>
                        </tr>
                        <tr>
                            <td><span class="code-val">blank</span></td>
                            <td>Absent — no record created</td>
                            <td>Leave empty for absent days</td>
                        </tr>
                        <tr>
                            <td><span class="code-val">A</span></td>
                            <td>Absent — same as leaving blank</td>
                            <td>You can write A or leave blank</td>
                        </tr>
                        <tr>
                            <td><span class="code-val">H</span></td>
                            <td>Holiday — skipped, no record created</td>
                            <td>Sundays are auto-marked H</td>
                        </tr>
                    </tbody>
                </table>
                <div class="tip">
                    <strong>Smart PM detection:</strong> When the clock-out hour is smaller than the clock-in hour, the system automatically treats it as PM.
                    So <span class="code-val">9-6</span> means 09:00 to 18:00 (not 06:00). You do <strong>not</strong> need to write 18 — just write 6.
                </div>

                <h5>Day-wise Template Columns (one date)</h5>
                <table class="doc-table">
                    <thead><tr><th>Column</th><th>Label</th><th>What to Enter</th></tr></thead>
                    <tbody>
                        <tr><td>A</td><td>Employee ID</td><td><strong>Do not change</strong></td></tr>
                        <tr><td>B</td><td>Employee Name</td><td><strong>Do not change</strong></td></tr>
                        <tr><td>C</td><td>Designation</td><td><strong>Do not change</strong></td></tr>
                        <tr><td>D</td><td>Clock In</td><td>Enter time: 9, 9:30, 09:00</td></tr>
                        <tr><td>E</td><td>Clock Out</td><td>Enter time: 6, 18, 18:30</td></tr>
                        <tr><td>F</td><td>Notes</td><td>Optional remarks</td></tr>
                    </tbody>
                </table>

                <h5>Monthly Template Layout</h5>
                <table class="doc-table">
                    <thead><tr><th>Column</th><th>Content</th></tr></thead>
                    <tbody>
                        <tr><td>A, B, C</td><td>Employee ID, Name, Designation — <strong>do not change</strong></td></tr>
                        <tr><td>D, E, F… (one column per day)</td><td>Enter attendance for that day in any format above</td></tr>
                        <tr><td>Last column</td><td>Total PR count — calculated automatically, do not change</td></tr>
                    </tbody>
                </table>
            </div>

            {{-- 6. History --}}
            <div class="doc-section" id="history">
                <h4><i class="bi bi-clock-history me-2"></i>6. Viewing History</h4>
                <p>Go to <strong>History</strong> in the navigation menu to see all attendance records you have submitted across all sites and dates.</p>
                <p>You can filter by site and date range. The list shows employee name, date, clock in, clock out, and whether the record is locked.</p>
            </div>

            {{-- 7. Profile --}}
            <div class="doc-section" id="profile">
                <h4><i class="bi bi-person me-2"></i>7. Profile & Password</h4>
                <p>Click your name in the top navigation → <strong>Profile</strong>.</p>
                <ul>
                    <li>View your company name, contact person, and email.</li>
                    <li>Change your login password: enter current password, new password (minimum 8 characters), and confirm. Click <strong>Update Password</strong>.</li>
                </ul>
                <div class="warn">Keep your password secure. Do not share your login credentials.</div>
            </div>

            {{-- 8. FAQ --}}
            <div class="doc-section" id="faq">
                <h4><i class="bi bi-question-circle me-2"></i>8. Frequently Asked Questions</h4>

                <h5>I uploaded the Excel but nothing was imported — why?</h5>
                <p>Possible reasons:
                    <ul>
                        <li>The Clock In column (D) was left blank for all rows — the system skips rows with no clock-in.</li>
                        <li>All those days were already recorded — the system skips existing records.</li>
                        <li>The template was downloaded for a different date or site — re-download the template for the correct selection.</li>
                        <li>Employee IDs in column A were changed — do not edit columns A, B, C.</li>
                    </ul>
                </p>

                <h5>I see "Some rows had issues" after uploading — what does it mean?</h5>
                <p>The system will tell you exactly which rows had problems (e.g., unrecognized time format, employee not found). Other rows are still imported successfully. Fix only the listed rows and re-upload.</p>

                <h5>Can I mark attendance for past months?</h5>
                <p>Yes — you can mark attendance for any past date. Select the correct date in the daily form or download the monthly template for a past month. Future dates are blocked.</p>

                <h5>An employee appears in the list but they don't work at my site anymore — what should I do?</h5>
                <p>Contact CleanSheen admin to update the site assignment for that employee.</p>

                <h5>A record shows as "Locked" — can I edit it?</h5>
                <p>No. Locked records have been processed for payroll and cannot be changed. Contact CleanSheen admin if a correction is required.</p>

                <h5>How do I mark a half-day?</h5>
                <p>Currently the portal marks employees as fully present. Contact CleanSheen admin to adjust the attendance type (half-day, late) on their side if needed.</p>

                <h5>I cannot log in — what should I do?</h5>
                <p>Use the <strong>Forgot Password</strong> link on the login page, or contact CleanSheen admin to reset your account.</p>
            </div>

        </div>
    </div>
</x-client-portal-layout>
