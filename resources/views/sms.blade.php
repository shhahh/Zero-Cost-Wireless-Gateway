<!DOCTYPE html>
<html lang="en">
<head>
    <title>Smart SMS Gateway</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<div class="container mt-5">
    
    <!-- HEADER -->
    <div class="text-center mb-4">
        <h2 class="fw-bold text-primary">📡 Zero-Cost Wireless Gateway</h2>
        <p class="text-muted">Turn your Android Phone into an SMS Server</p>
    </div>

    <div class="row">
        
        <!-- LEFT: STATUS & CONNECTION -->
        <div class="col-md-4">
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-dark text-white">
                    <i class="bi bi-cpu-fill"></i> System Status
                </div>
                <div class="card-body text-center">
                    @if($deviceConnected)
                        <div class="alert alert-success fw-bold">
                            <i class="bi bi-wifi"></i> Device Connected<br>
                            <small>{{ $deviceName }}</small>
                        </div>
                    @else
                        <div class="alert alert-danger fw-bold">
                            <i class="bi bi-x-circle"></i> No Device Found
                        </div>
                        <small class="text-muted">Connect USB First to Setup Wireless</small>
                    @endif
                </div>
            </div>

            <!-- Wireless Setup Box -->
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <i class="bi bi-broadcast"></i> Wireless Setup
                </div>
                <!-- Wireless Setup Box -->
<div class="card shadow-sm">
    <div class="card-header bg-info text-white">
        <i class="bi bi-magic"></i> Auto-Connect
    </div>
    <div class="card-body text-center">
        
        <!-- Status Icon -->
        <div id="status-icon" class="mb-3">
            <i class="bi bi-usb-plug text-muted" style="font-size: 40px;"></i>
        </div>

        <!-- The Magic Button -->
        <button id="detectBtn" class="btn btn-primary w-100 fw-bold">
            <i class="bi bi-search"></i> Detect & Connect
        </button>

        <p id="status-msg" class="mt-2 small text-muted">Click to auto-connect phone</p>
    </div>
</div>

<!-- AJAX SCRIPT (JQuery ki zaroorat nahi, Pure JS use karenge fast performance ke liye) -->
<script>
    document.getElementById('detectBtn').addEventListener('click', function() {
        const btn = this;
        const msg = document.getElementById('status-msg');
        const icon = document.getElementById('status-icon');

        // Loading State
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Scanning...';
        msg.innerText = "Configuring ADB Wireless...";
        msg.className = "mt-2 small text-warning fw-bold";

        // Call Laravel Backend
        fetch("{{ route('phone.detect') }}")
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                
                if (data.status) {
                    // Success UI
                    btn.innerHTML = '<i class="bi bi-wifi"></i> Connected';
                    btn.className = "btn btn-success w-100 fw-bold";
                    msg.innerHTML = `Connected to <b>${data.ip}</b>`;
                    msg.className = "mt-2 small text-success fw-bold";
                    icon.innerHTML = '<i class="bi bi-phone-vibrate text-success" style="font-size: 40px;"></i>';
                    
                    // Alert Sir
                    alert("Wireless Connection Successful! You can remove the USB cable now.");
                } else {
                    // Error UI
                    btn.innerHTML = '<i class="bi bi-arrow-repeat"></i> Retry';
                    btn.className = "btn btn-danger w-100 fw-bold";
                    msg.innerText = data.message;
                    msg.className = "mt-2 small text-danger fw-bold";
                }
            })
            .catch(error => {
                btn.disabled = false;
                btn.innerText = "Error";
                msg.innerText = "System Error. Check Console.";
                console.error(error);
            });
    });
</script>
            </div>
        </div>

        <!-- RIGHT: SEND SMS FORM -->
        <div class="col-md-8">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-chat-dots-fill"></i> Send Message</h5>
                </div>
                <div class="card-body">
                    
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <form action="{{ route('sms.send') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold">Mobile Number:</label>
                            <input type="text" name="mobile" class="form-control" placeholder="+919876543210" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Message:</label>
                            <textarea name="message" class="form-control" rows="4" placeholder="Type your message here..." required></textarea>
                        </div>

                        <button type="submit" class="btn btn-success w-100 py-2 fw-bold" {{ !$deviceConnected ? 'disabled' : '' }}>
                            <i class="bi bi-send-fill"></i> SEND SMS NOW
                        </button>
                        
                        @if(!$deviceConnected)
                            <p class="text-center text-danger small mt-2">Connect device to enable sending.</p>
                        @endif
                    </form>

                </div>
            </div>
        </div>

    </div>
</div>

</body>
</html>