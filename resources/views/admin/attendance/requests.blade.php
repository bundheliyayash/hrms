<x-admin-layout>
    <x-slot name="header">
        Correction Requests
    </x-slot>

    <div class="container-fluid py-0 px-0 h-100 overflow-hidden">
        <div class="row g-0 h-100" style="min-height: calc(100vh - 160px);">
            <!-- Left Sidebar: Requests List -->
            <div class="col-md-4 border-end bg-white" style="overflow-y: auto;">
                <div class="p-4 border-bottom sticky-top bg-white z-index-1">
                    <h5 class="fw-bold mb-1">Correction Requests</h5>
                    <p class="text-muted small mb-0">{{ $requests->total() }} pending requests from employees.</p>
                </div>
                
                <div class="list-group list-group-flush">
                    @forelse($requests as $req)
                    <a href="#" class="list-group-item list-group-item-action p-4 border-bottom-0 request-item" 
                       onclick="showDetails({{ json_encode($req) }}, {{ json_encode($req->user) }})">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="badge bg-light text-primary rounded-pill px-3">
                                @if($req->requested_type === 'full_day')
                                    FULL DAY ENTRY
                                @else
                                    {{ strtoupper(str_replace('_', ' ', $req->requested_type)) }}
                                @endif
                            </span>
                            <small class="text-muted">{{ \Carbon\Carbon::parse($req->created_at)->diffForHumans() }}</small>
                        </div>
                        <div class="fw-bold text-dark">{{ $req->user->name }}</div>
                        <div class="text-muted small mb-1">
                            <i class="bi bi-building me-1"></i>
                            {{ $req->user->employeeDetail->site->client->name ?? 'No Client' }} - {{ $req->user->employeeDetail->site->site_name ?? 'No Site' }}
                        </div>
                        <div class="text-muted small mb-2">Requested for: {{ $req->requested_date }} at {{ $req->requested_time }}</div>
                        <p class="text-muted small mb-0 text-truncate">{{ $req->reason }}</p>
                    </a>
                    @empty
                    <div class="p-5 text-center text-muted">
                        <i class="bi bi-inbox display-4 mb-3"></i>
                        <p>No pending requests.</p>
                    </div>
                    @endforelse
                </div>
                <div class="p-3">
                    {{ $requests->links() }}
                </div>
            </div>

            <!-- Right Pane: Details -->
            <div class="col-md-8 bg-light d-flex flex-column h-100" id="detailsPane">
                <div id="noSelection" class="m-auto text-center py-5">
                    <img src="https://illustrations.popsy.co/gray/work-from-home.svg" style="width: 250px;" class="mb-4 opacity-75">
                    <h4 class="text-muted">Select a request to view details</h4>
                    <p class="text-muted small">Choose any request from the left list to review and process.</p>
                </div>

                <div id="detailsContent" class="d-none w-100 h-100 bg-white">
                    <div class="p-5 overflow-auto h-100 text-dark">
                        <div class="d-flex justify-content-between align-items-start mb-5 pb-4 border-bottom">
                            <div class="d-flex align-items-center">
                                <div id="userLetter" class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3 fw-bold" style="width: 60px; height: 60px; font-size: 1.5rem;">
                                    Y
                                </div>
                                <div>
                                    <h3 id="userName" class="fw-bold mb-1">Yash Bundheliya</h3>
                                    <p id="userDetails" class="text-muted mb-0">EMP001 | Cleaning Professional</p>
                                </div>
                            </div>
                            <div class="text-end">
                                <div id="reqType" class="h5 fw-bold text-primary mb-1">CLOCK IN MISSED</div>
                                <div id="reqDate" class="text-muted small">Submitted on Jan 08, 2026</div>
                            </div>
                        </div>

                        <div class="row mb-5">
                            <div class="col-md-6 mb-4">
                                <label class="text-muted small text-uppercase fw-bold mb-2 d-inline-block">Requested Change</label>
                                <div class="p-3 bg-light rounded-3">
                                    <div class="row g-0">
                                        <div class="col-6">
                                            <div class="text-muted small">Date</div>
                                            <div id="targetDate" class="fw-bold">2026-01-08</div>
                                        </div>
                                        <div class="col-6">
                                            <div class="text-muted small">Target Time</div>
                                            <div id="targetTime" class="fw-bold">09:30 AM</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="text-muted small text-uppercase fw-bold mb-2 d-inline-block">Attachment</label>
                                <div id="attachmentBox" class="p-3 bg-light rounded-3 d-flex align-items-center">
                                    <i class="bi bi-paperclip me-2"></i>
                                    <span id="attachmentLink" class="small">No attachment provided</span>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="text-muted small text-uppercase fw-bold mb-2 d-inline-block">Reason from Employee</label>
                                <div id="reqReason" class="p-4 bg-light rounded-3 font-italic text-dark" style="border-left: 4px solid #dee2e6;">
                                    "Forgot to clock in due to heavy traffic at client site entrance."
                                </div>
                            </div>
                        </div>

                        <hr class="my-5">

                        <div class="card border-0 shadow-sm mb-5 bg-light p-4">
                            <h6 class="fw-bold text-dark mb-3">Admin Decision</h6>
                            <form id="handleForm" action="" method="POST">
                                @csrf
                                <input type="hidden" name="status" id="decisionField" value="approved">
                                <div class="mb-4">
                                    <label class="form-label small fw-bold text-dark">Admin Remarks (Compulsory for audit)</label>
                                    <textarea name="admin_remark" class="form-control" rows="3" placeholder="Explain your decision..." required></textarea>
                                </div>
                                <div class="d-flex gap-3">
                                    <button type="button" onclick="submitDecision('approved')" class="btn btn-success rounded-pill px-5 fw-bold">Approve Request</button>
                                    <button type="button" onclick="submitDecision('rejected')" class="btn btn-outline-danger rounded-pill px-5 fw-bold">Reject</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .request-item.active {
            background-color: #f8f9fa !important;
            border-left: 4px solid #0d6efd !important;
        }
        .z-index-1 { z-index: 1000; }
    </style>

    <script>
        function showDetails(req, user) {
            document.getElementById('noSelection').classList.add('d-none');
            document.getElementById('detailsContent').classList.remove('d-none');
            document.getElementById('userLetter').innerText = user.name.charAt(0);
            document.getElementById('userName').innerText = user.name;
            document.getElementById('userDetails').innerText = `Role: ${user.role}`;
            document.getElementById('reqType').innerText = req.requested_type.replace('_', ' ').toUpperCase();
            document.getElementById('reqDate').innerText = `Submitted on ${new Date(req.created_at).toLocaleDateString()}`;
            document.getElementById('targetDate').innerText = req.requested_date;
            document.getElementById('targetTime').innerText = req.requested_time;
            document.getElementById('reqReason').innerText = `"${req.reason}"`;
            const attachmentBox = document.getElementById('attachmentBox');
            if(req.attachment) {
                document.getElementById('attachmentLink').innerHTML = `<a href="/storage/${req.attachment}" target="_blank" class="text-primary fw-bold">View Attachment</a>`;
            } else {
                document.getElementById('attachmentLink').innerText = 'No attachment provided';
            }
            const userRole = "{{ Auth::user()->role }}";
            document.getElementById('handleForm').action = `/${userRole}/attendance/requests/${req.id}`;
            document.querySelectorAll('.request-item').forEach(item => item.classList.remove('active'));
            event.currentTarget.classList.add('active');
        }

        function submitDecision(status) {
            document.getElementById('decisionField').value = status;
            if(confirm(`Are you sure you want to ${status} this request?`)) {
                document.getElementById('handleForm').submit();
            }
        }
    </script>
</x-admin-layout>
