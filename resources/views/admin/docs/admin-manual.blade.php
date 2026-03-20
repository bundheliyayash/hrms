<x-admin-layout>
    <x-slot name="header">Admin User Manual — CleanSheen HRMS</x-slot>

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

    <div class="row g-4">
        {{-- TOC sidebar --}}
        <div class="col-lg-3">
            <div class="doc-section sticky-top" style="top:1rem;">
                <h6 class="fw-bold mb-3">Contents</h6>
                <div class="toc small">
                    <a href="#overview">1. System Overview</a>
                    <a href="#roles">2. User Roles</a>
                    <a href="#employees">3. Employee Management</a>
                    <a href="#attendance">4. Attendance</a>
                    <a href="#leaves">5. Leave Management</a>
                    <a href="#payroll">6. Payroll</a>
                    <a href="#clients">7. Client & Site Management</a>
                    <a href="#assignments">8. Daily Assignments</a>
                    <a href="#reports">9. Reports & Exports</a>
                    <a href="#settings">10. Settings</a>
                    <a href="#excel-formats">11. Excel Format Reference</a>
                </div>
                <hr>
                <a href="{{ route('client.manual') }}" class="btn btn-outline-primary btn-sm w-100 mt-1">
                    <i class="bi bi-people me-1"></i> View Client Manual
                </a>
            </div>
        </div>

        <div class="col-lg-9">

            {{-- 1. Overview --}}
            <div class="doc-section" id="overview">
                <h4><i class="bi bi-house me-2"></i>1. System Overview</h4>
                <p>CleanSheen HRMS manages employees deployed to client sites. Key concepts:</p>
                <ul>
                    <li><strong>Employees</strong> are deployed to <strong>Client Sites</strong> — either permanently (via Employee Detail → Site) or daily (via Daily Assignments).</li>
                    <li><strong>Attendance</strong> can be marked by employees themselves (clock in/out), by admin/manager, or by the <strong>client</strong> through their portal.</li>
                    <li><strong>Payroll</strong> is calculated monthly based on present days, OT, allowances, and statutory deductions (PF, ESIC, PT).</li>
                    <li><strong>Reports</strong> (Excel) are generated per client or company-wide and match the standard Clean Sheen reporting format.</li>
                </ul>
                <div class="tip">Access the system at <code>/admin/dashboard</code> after logging in as Admin or Manager.</div>
            </div>

            {{-- 2. Roles --}}
            <div class="doc-section" id="roles">
                <h4><i class="bi bi-shield-check me-2"></i>2. User Roles</h4>
                <table class="doc-table">
                    <thead><tr><th>Role</th><th>Login URL</th><th>Access</th></tr></thead>
                    <tbody>
                        <tr><td><strong>Admin</strong></td><td>/admin/dashboard</td><td>Full access — employees, payroll, reports, settings, clients</td></tr>
                        <tr><td><strong>Manager</strong></td><td>/admin/dashboard</td><td>View/manage subordinate attendance and leaves only</td></tr>
                        <tr><td><strong>Employee</strong></td><td>/employee/dashboard</td><td>Clock in/out, view own payslips and leaves</td></tr>
                        <tr><td><strong>Client</strong></td><td>/client/dashboard</td><td>Mark attendance for employees at their sites</td></tr>
                    </tbody>
                </table>
                <h5>Creating a New Admin/Manager/Employee Account</h5>
                <div class="step"><div class="step-num">1</div><div>Go to <strong>Employees → Add New Employee</strong>.</div></div>
                <div class="step"><div class="step-num">2</div><div>Fill name, email, role (Admin / Manager / Employee), and all required details.</div></div>
                <div class="step"><div class="step-num">3</div><div>System sends login credentials by email (if mail is configured) or you can note the temporary password.</div></div>
            </div>

            {{-- 3. Employee Management --}}
            <div class="doc-section" id="employees">
                <h4><i class="bi bi-people me-2"></i>3. Employee Management</h4>
                <h5>Adding an Employee</h5>
                <div class="step"><div class="step-num">1</div><div>Navigate to <strong>Admin → Employees → Add New</strong>.</div></div>
                <div class="step"><div class="step-num">2</div><div>Enter personal details, role (Employee), department, designation, joining date.</div></div>
                <div class="step"><div class="step-num">3</div><div>Set <strong>Site</strong> (permanent site assignment), <strong>Shift</strong>, <strong>Manager</strong>, and basic salary.</div></div>
                <div class="step"><div class="step-num">4</div><div>Enter bank details (Account No, IFSC, Bank Name) for salary processing.</div></div>
                <div class="step"><div class="step-num">5</div><div>Save. The employee can now log in and clock in/out.</div></div>

                <h5>Deactivating an Employee</h5>
                <p>Use the <strong>Toggle Status</strong> button on the employee list. Deactivated employees cannot log in, but their attendance and payroll history is preserved (soft delete).</p>

                <h5>Bulk Import via Excel</h5>
                <p>Go to <strong>Assignments → Import</strong> to bulk-assign employees to sites using an Excel upload.</p>
            </div>

            {{-- 4. Attendance --}}
            <div class="doc-section" id="attendance">
                <h4><i class="bi bi-calendar-check me-2"></i>4. Attendance</h4>
                <h5>How Attendance is Recorded</h5>
                <table class="doc-table">
                    <thead><tr><th>Source</th><th>How</th></tr></thead>
                    <tbody>
                        <tr><td>Employee self</td><td>Employee portal → Clock In / Clock Out (GPS captured)</td></tr>
                        <tr><td>Client portal</td><td>Client logs in → Marks attendance for their site employees</td></tr>
                        <tr><td>Admin manual</td><td>Admin → Attendance → Edit any record</td></tr>
                    </tbody>
                </table>

                <h5>Editing an Attendance Record (Admin)</h5>
                <div class="step"><div class="step-num">1</div><div>Go to <strong>Admin → Attendance</strong>.</div></div>
                <div class="step"><div class="step-num">2</div><div>Find the record by filtering date/employee.</div></div>
                <div class="step"><div class="step-num">3</div><div>Click the <strong>Edit</strong> (pencil) button. Update clock in, clock out, or status.</div></div>
                <div class="warn">Locked records cannot be edited. Records are locked after payroll is processed. To unlock, contact system administrator.</div>

                <h5>Correction Requests</h5>
                <p>Employees can submit correction requests from their portal. Go to <strong>Admin → Attendance → Requests</strong> to approve or reject them.</p>

                <h5>Exporting Attendance</h5>
                <p>Go to <strong>Reports → Attendance → Export Excel</strong>. Filter by month, year, and optionally by employee. This exports the raw attendance log.</p>
                <p>For a formatted <strong>Muster Roll</strong> (grid view), go to <strong>Reports → Muster Roll</strong>.</p>
            </div>

            {{-- 5. Leaves --}}
            <div class="doc-section" id="leaves">
                <h4><i class="bi bi-calendar-x me-2"></i>5. Leave Management</h4>
                <div class="step"><div class="step-num">1</div><div>Employees apply for leave from their portal.</div></div>
                <div class="step"><div class="step-num">2</div><div>Admin or Manager reviews pending leaves at <strong>Admin → Leaves</strong>.</div></div>
                <div class="step"><div class="step-num">3</div><div>Click <strong>Approve</strong> or <strong>Reject</strong>. Approved leaves affect payroll present-day count.</div></div>
                <div class="tip">View leave summary report at <strong>Reports → Leave Report</strong>.</div>
            </div>

            {{-- 6. Payroll --}}
            <div class="doc-section" id="payroll">
                <h4><i class="bi bi-cash-stack me-2"></i>6. Payroll</h4>
                <h5>Processing Payroll for a Month</h5>
                <div class="step"><div class="step-num">1</div><div>Go to <strong>Admin → Payroll → Generate</strong>.</div></div>
                <div class="step"><div class="step-num">2</div><div>Select the <strong>Month</strong> and <strong>Year</strong>. Choose an employee or process all.</div></div>
                <div class="step"><div class="step-num">3</div><div>Review the calculated values: Present Days, OT, Basic, HRA, Washing Allowance, PF, ESIC, PT, Advance, Net Salary.</div></div>
                <div class="step"><div class="step-num">4</div><div>Click <strong>Save Payroll</strong>. Status is set to <em>pending</em>.</div></div>
                <div class="step"><div class="step-num">5</div><div>After payment is made, mark each payroll as <strong>Paid</strong>.</div></div>

                <h5>Salary Formula Used</h5>
                <table class="doc-table">
                    <thead><tr><th>Component</th><th>Calculation</th></tr></thead>
                    <tbody>
                        <tr><td>Per Day Salary</td><td>Basic Salary ÷ Working Days (typically 26)</td></tr>
                        <tr><td>Gross Salary</td><td>Per Day × Present Days + OT Amount + Allowances</td></tr>
                        <tr><td>PF (Employee)</td><td>12% of Basic</td></tr>
                        <tr><td>ESIC (Employee)</td><td>0.75% of Gross</td></tr>
                        <tr><td>Professional Tax</td><td>₹200 if Gross > ₹12,000</td></tr>
                        <tr><td>Net Salary</td><td>Gross − PF − ESIC − PT − Advance − Other Deductions</td></tr>
                    </tbody>
                </table>

                <h5>Exporting Payroll</h5>
                <p>Go to <strong>Reports → Payroll → Export Excel</strong>. Select type:</p>
                <ul>
                    <li><strong>Standard</strong> — Name, PR, OT, OT Amt, Bank, Total, Cash, Remarks</li>
                    <li><strong>BigBasket Salary</strong> — Client-specific format with SR.NO., YES BANK, CASH, ADV, NET</li>
                    <li><strong>Wage Muster</strong> — Statutory format with PF/ESIC/PT breakdown</li>
                    <li><strong>Attendance Sheet</strong> — Monthly grid of P/HD/L per employee</li>
                </ul>
            </div>

            {{-- 7. Clients --}}
            <div class="doc-section" id="clients">
                <h4><i class="bi bi-buildings me-2"></i>7. Client & Site Management</h4>
                <h5>Adding a Client</h5>
                <div class="step"><div class="step-num">1</div><div>Go to <strong>Admin → Clients → Add New</strong>.</div></div>
                <div class="step"><div class="step-num">2</div><div>Enter client name, contact person, email, phone, service dates.</div></div>
                <div class="step"><div class="step-num">3</div><div>Save. The client will have a user account for portal login (email = client login).</div></div>

                <h5>Adding a Site</h5>
                <div class="step"><div class="step-num">1</div><div>Go to <strong>Admin → Sites → Add New</strong>.</div></div>
                <div class="step"><div class="step-num">2</div><div>Select the <strong>Client</strong>, enter site name, address, and GPS coordinates (latitude/longitude).</div></div>
                <div class="step"><div class="step-num">3</div><div>Set <strong>Radius (meters)</strong> — employee clock-in is only allowed within this radius from the site.</div></div>

                <h5>Assigning Employees to a Site (Permanent)</h5>
                <p>Edit the employee → set their <strong>Site</strong> in Employee Detail. This is their default permanent site.</p>
            </div>

            {{-- 8. Assignments --}}
            <div class="doc-section" id="assignments">
                <h4><i class="bi bi-diagram-3 me-2"></i>8. Daily Assignments</h4>
                <p>Daily assignments allow temporarily deploying an employee to a different site for specific dates.</p>
                <div class="step"><div class="step-num">1</div><div>Go to <strong>Admin → Assignments → Calendar</strong> to view the deployment calendar.</div></div>
                <div class="step"><div class="step-num">2</div><div>Click <strong>Bulk Assign</strong> to assign multiple employees to a site for a date range.</div></div>
                <div class="step"><div class="step-num">3</div><div>Employees appear in the client portal for that site on the assigned dates.</div></div>
                <div class="tip">Use <strong>Assignments → Import</strong> to bulk-upload assignments via Excel template.</div>
            </div>

            {{-- 9. Reports --}}
            <div class="doc-section" id="reports">
                <h4><i class="bi bi-file-earmark-spreadsheet me-2"></i>9. Reports & Exports</h4>
                <p>All reports are accessible from <strong>Admin → Reports</strong>.</p>

                <h5>Company-Wide Reports</h5>
                <table class="doc-table">
                    <thead><tr><th>Report</th><th>Description</th></tr></thead>
                    <tbody>
                        <tr><td>Attendance Log</td><td>Day-by-day attendance records with filters. Export to Excel.</td></tr>
                        <tr><td>Muster Roll</td><td>Monthly grid showing P/A/HD for all employees.</td></tr>
                        <tr><td>Leave Summary</td><td>All leaves with status. Export to Excel.</td></tr>
                        <tr><td>Payroll Summary</td><td>All payrolls processed. Export to Excel (4 types).</td></tr>
                        <tr><td>Wage Register</td><td>Statutory wage muster — select month/year.</td></tr>
                        <tr><td>System Report</td><td>Admin-only: User stats, attendance source breakdown.</td></tr>
                    </tbody>
                </table>

                <h5>Client-wise Reports (Excel, Multi-Sheet)</h5>
                <p>Go to <strong>Reports → Client-wise Reports</strong> section. Select client, month, year, and report type:</p>
                <table class="doc-table">
                    <thead><tr><th>Report Type</th><th>Sheets Generated</th><th>Matches Sample File</th></tr></thead>
                    <tbody>
                        <tr>
                            <td><strong>Client Salary Register</strong></td>
                            <td>Sheet 1: Summary (SR.NO, NAME, PR, BANK, CASH, ADV, NET, SIGNATURE)<br>+ One payslip sheet per employee</td>
                            <td>BB SALARY NOV 2025.xlsx</td>
                        </tr>
                        <tr>
                            <td><strong>Client Attendance Register</strong></td>
                            <td>Sheet 1: Daily P/A matrix (employees × days)<br>Sheet 2: Summary (present/absent counts)</td>
                            <td>BB Updated Attendance sheet.xlsx</td>
                        </tr>
                        <tr>
                            <td><strong>Client Wage Muster</strong></td>
                            <td>Sheet 1: Gross, Basic, HRA, Washing, PF, ESIC, PT, Adv, Net per employee</td>
                            <td>Wage Muster for Nov 2025.xlsx</td>
                        </tr>
                    </tbody>
                </table>
                <div class="warn">Client-wise reports only include employees who have payroll records for that month (salary/wage reports) or attendance records (attendance report). Generate payroll before exporting salary reports.</div>
            </div>

            {{-- 10. Settings --}}
            <div class="doc-section" id="settings">
                <h4><i class="bi bi-gear me-2"></i>10. Settings</h4>
                <p>Access via <strong>Admin → Settings</strong> (Admin role only).</p>
                <ul>
                    <li><strong>General Settings</strong> — Company name, working days per month, OT rate per hour.</li>
                    <li><strong>Profile Permissions</strong> — Which fields employees can edit in their profile.</li>
                    <li><strong>Notification Settings</strong> — Email alerts for leave approvals, payroll, etc.</li>
                    <li><strong>Role Management</strong> — Create and manage custom roles with menu permissions.</li>
                    <li><strong>Holiday Management</strong> — Add/edit national and company holidays. Holidays affect the "absent" vs "holiday" status in attendance reports.</li>
                </ul>
            </div>

            {{-- 11. Excel Format Reference --}}
            <div class="doc-section" id="excel-formats">
                <h4><i class="bi bi-table me-2"></i>11. Excel Format Reference</h4>
                <p>The following time formats are accepted in all Excel uploads:</p>
                <table class="doc-table">
                    <thead><tr><th>You Enter</th><th>Interpreted As</th><th>Use When</th></tr></thead>
                    <tbody>
                        <tr><td><span class="code-val">P</span></td><td>Present, no clock times</td><td>Simple present marking</td></tr>
                        <tr><td><span class="code-val">9-6</span></td><td>Clock in 09:00, Clock out 18:00</td><td>Standard shift (if out &lt; in, PM assumed)</td></tr>
                        <tr><td><span class="code-val">8-5</span></td><td>Clock in 08:00, Clock out 17:00</td><td>Early shift</td></tr>
                        <tr><td><span class="code-val">9-18</span></td><td>Clock in 09:00, Clock out 18:00</td><td>24-hour format</td></tr>
                        <tr><td><span class="code-val">9:30-18:30</span></td><td>Clock in 09:30, Clock out 18:30</td><td>With minutes</td></tr>
                        <tr><td><span class="code-val">9</span></td><td>Clock in 09:00 only</td><td>When clock-out unknown</td></tr>
                        <tr><td><span class="code-val">blank</span> or <span class="code-val">A</span></td><td>Absent — skipped</td><td>Employee did not attend</td></tr>
                        <tr><td><span class="code-val">H</span></td><td>Holiday — skipped</td><td>Public holiday / weekly off</td></tr>
                    </tbody>
                </table>
                <div class="tip">The system automatically adds 12 hours to the clock-out if it is numerically less than the clock-in. So <span class="code-val">9-6</span> becomes 09:00 → 18:00, not 09:00 → 06:00.</div>

                <h5>Day-wise Excel Template (Columns)</h5>
                <table class="doc-table">
                    <thead><tr><th>Column</th><th>Content</th><th>Editable?</th></tr></thead>
                    <tbody>
                        <tr><td>A</td><td>Employee ID</td><td>No — do not change</td></tr>
                        <tr><td>B</td><td>Employee Name</td><td>No</td></tr>
                        <tr><td>C</td><td>Designation</td><td>No</td></tr>
                        <tr><td>D</td><td>Clock In</td><td>Yes — enter time (9, 9:30, 09:00)</td></tr>
                        <tr><td>E</td><td>Clock Out</td><td>Yes — enter time (6, 18, 18:30)</td></tr>
                        <tr><td>F</td><td>Notes</td><td>Yes — optional remarks</td></tr>
                    </tbody>
                </table>

                <h5>Monthly Excel Template (Layout)</h5>
                <table class="doc-table">
                    <thead><tr><th>Column</th><th>Content</th></tr></thead>
                    <tbody>
                        <tr><td>A</td><td>Employee ID — do not change</td></tr>
                        <tr><td>B</td><td>Employee Name — do not change</td></tr>
                        <tr><td>C</td><td>Designation — do not change</td></tr>
                        <tr><td>D, E, F… (one per day)</td><td>Enter attendance for that day: P, 9-6, 9:30-18:30, or blank</td></tr>
                        <tr><td>Last column</td><td>Total PR — auto-calculated, do not change</td></tr>
                    </tbody>
                </table>
                <p class="small text-muted">Sundays are pre-marked <span class="code-val">H</span> (holiday) in red. Already-recorded days are pre-filled in green and will be skipped on upload.</p>
            </div>

        </div>
    </div>
</x-admin-layout>
