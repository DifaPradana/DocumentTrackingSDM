<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sedang Maintenance</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html,
        body {
            height: 100%;
        }

        body {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            background: #f8fafc;
            color: #0f172a;
            min-height: 100vh;
            min-height: 100dvh;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            padding: 20px;
        }

        /* Animated gradient blobs di background */
        .blob {
            position: fixed;
            border-radius: 50%;
            filter: blur(90px);
            opacity: 0.25;
            z-index: 0;
            animation: float 12s ease-in-out infinite;
            pointer-events: none;
        }

        .blob-1 {
            width: 400px;
            height: 400px;
            background: #818cf8;
            top: -100px;
            left: -100px;
        }

        .blob-2 {
            width: 350px;
            height: 350px;
            background: #38bdf8;
            bottom: -120px;
            right: -80px;
            animation-delay: -6s;
        }

        @keyframes float {

            0%,
            100% {
                transform: translate(0, 0) scale(1);
            }

            50% {
                transform: translate(30px, -30px) scale(1.1);
            }
        }

        .card {
            position: relative;
            z-index: 1;
            max-width: 500px;
            width: 100%;
            margin: auto;
            background: rgba(255, 255, 255, 0.75);
            border: 1px solid rgba(15, 23, 42, 0.06);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 48px 40px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(15, 23, 42, 0.08);
            animation: fadeUp 0.6s ease;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(16px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .icon-wrap {
            width: 72px;
            height: 72px;
            margin: 0 auto 24px;
            background: linear-gradient(135deg, #6366f1, #38bdf8);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 24px rgba(56, 189, 248, 0.3);
        }

        .icon-wrap svg {
            width: 36px;
            height: 36px;
            stroke: #ffffff;
        }

        h1 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 10px;
            letter-spacing: -0.02em;
            color: #0f172a;
        }

        p.subtitle {
            color: #64748b;
            font-size: 0.95rem;
            margin-bottom: 28px;
            line-height: 1.5;
        }

        .duration-info {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(99, 102, 241, 0.06);
            border: 1px solid rgba(99, 102, 241, 0.15);
            padding: 8px 16px;
            border-radius: 999px;
            font-size: 0.85rem;
            color: #475569;
            margin-bottom: 32px;
        }

        .duration-info strong {
            color: #4f46e5;
            font-weight: 600;
        }

        .countdown {
            display: flex;
            justify-content: center;
            gap: 14px;
            margin-bottom: 28px;
        }

        .time-box {
            background: #f1f5f9;
            border: 1px solid rgba(15, 23, 42, 0.05);
            border-radius: 16px;
            padding: 18px 22px;
            min-width: 78px;
            transition: transform 0.2s ease;
        }

        .time-box .value {
            font-size: 2.1rem;
            font-weight: 700;
            background: linear-gradient(135deg, #4f46e5, #0ea5e9);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-variant-numeric: tabular-nums;
        }

        .time-box .label {
            font-size: 0.7rem;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-top: 6px;
            display: block;
        }

        .progress-wrap {
            width: 100%;
            background: #e2e8f0;
            border-radius: 999px;
            height: 6px;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #4f46e5, #0ea5e9);
            width: 0%;
            border-radius: 999px;
            transition: width 1s linear;
        }

        .done-msg {
            color: #0ea5e9;
            font-size: 1.1rem;
            font-weight: 600;
        }

        footer {
            margin-top: 28px;
            font-size: 0.75rem;
            color: #94a3b8;
        }

        @media (max-width: 480px) {
            .card {
                padding: 36px 24px;
            }

            .time-box {
                min-width: 64px;
                padding: 14px 16px;
            }

            .time-box .value {
                font-size: 1.6rem;
            }
        }
    </style>
</head>

<body>
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>

    @php
    $maintenanceFile = storage_path('app/maintenance_until.txt');
    $targetTimeRaw = file_exists($maintenanceFile)
    ? trim(file_get_contents($maintenanceFile))
    : now()->addHour()->toIso8601String();
    $targetTime = \Illuminate\Support\Carbon::parse($targetTimeRaw);
    @endphp

    <div class="card">
        <div class="icon-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z" />
            </svg>
        </div>

        <h1>Sedang Maintenance</h1>

        <div class="duration-info">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10" />
                <polyline points="12 6 12 12 16 14" />
            </svg>
            Estimasi selesai: <strong>{{ $targetTime->translatedFormat('d M Y, H:i') }} WIB</strong>
        </div>

        <div class="countdown">
            <div class="time-box">
                <div class="value" id="hours">00</div><span class="label">Jam</span>
            </div>
            <div class="time-box">
                <div class="value" id="minutes">00</div><span class="label">Menit</span>
            </div>
            <div class="time-box">
                <div class="value" id="seconds">00</div><span class="label">Detik</span>
            </div>
        </div>

        <div class="progress-wrap">
            <div class="progress-bar" id="progressBar"></div>
        </div>

        <footer>Halaman ini akan otomatis reload saat sistem kembali online.</footer>
    </div>

    <script>
        const targetTime = new Date("{{ $targetTime->toIso8601String() }}").getTime();
        const startTime = new Date("{{ now()->toIso8601String() }}").getTime();
        const totalDuration = Math.max(1, (targetTime - startTime) / 1000);

        function updateCountdown() {
            const now = new Date().getTime();
            const distance = targetTime - now;

            if (distance <= 0) {
                document.querySelector('.countdown').innerHTML =
                    "<p class='done-msg'>✅ Selesai! Memuat ulang...</p>";
                clearInterval(timer);
                setTimeout(() => location.reload(), 1500);
                return;
            }

            const hours = Math.floor(distance / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            document.getElementById('hours').textContent = String(hours).padStart(2, '0');
            document.getElementById('minutes').textContent = String(minutes).padStart(2, '0');
            document.getElementById('seconds').textContent = String(seconds).padStart(2, '0');

            const elapsed = totalDuration - (distance / 1000);
            const progressPercent = Math.min(100, (elapsed / totalDuration) * 100);
            document.getElementById('progressBar').style.width = progressPercent + '%';
        }

        updateCountdown();
        let timer = setInterval(updateCountdown, 1000);
    </script>
</body>

</html>