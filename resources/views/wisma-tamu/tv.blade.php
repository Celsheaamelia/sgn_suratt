<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Wisma Tamu — Status Kamar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');

        * { box-sizing: border-box; }

        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            width: 100%;
            font-family: 'Inter', -apple-system, sans-serif;
            color: #ffffff;
            overflow: hidden;
            background: #0a0303;
        }

        /* ---- Langit bintang kerlap-kerlip ---- */
        .tv-sky {
            position: fixed;
            inset: 0;
            z-index: 0;
            background: radial-gradient(ellipse at 50% 15%, #2a0a0a 0%, #0a0303 70%);
            overflow: hidden;
        }

        .star {
            position: absolute;
            border-radius: 50%;
            background: #ffffff;
            animation-name: twinkle;
            animation-timing-function: ease-in-out;
            animation-iteration-count: infinite;
        }

        @keyframes twinkle {
            0%, 100% { opacity: 0.15; transform: scale(0.7); }
            50%      { opacity: 1;    transform: scale(1.2); }
        }

        .shooting-star {
            position: absolute;
            width: 3px;
            height: 3px;
            border-radius: 50%;
            background: #ffffff;
            box-shadow: 0 0 6px 1px rgba(255,255,255,0.9);
            animation: shoot 1.6s linear forwards;
        }

        .shooting-star::before {
            content: '';
            position: absolute;
            top: 50%;
            right: 0;
            width: 110px;
            height: 1.5px;
            background: linear-gradient(90deg, rgba(255,255,255,0.9), transparent);
            transform: translateY(-50%);
        }

        @keyframes shoot {
            0%   { transform: translate(0, 0); opacity: 1; }
            85%  { opacity: 1; }
            100% { transform: translate(-38vw, 24vh); opacity: 0; }
        }

        /* ---- Panggung slide ---- */
        .tv-viewport {
            position: fixed;
            inset: 0;
            z-index: 1;
        }

        .tv-sheet {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            transition: transform 0.65s cubic-bezier(.5,0,.2,1), opacity 0.55s ease;
            will-change: transform, opacity;
        }

        .tv-sheet.no-anim { transition: none !important; }

        .tv-sheet.pos-active { transform: translateX(0) rotate(0deg); opacity: 1; z-index: 3; }
        .tv-sheet.pos-enter  { transform: translateX(30%) rotate(2.5deg); opacity: 0; z-index: 2; }
        .tv-sheet.pos-exit   { transform: translateX(-32%) rotate(-3deg); opacity: 0; z-index: 2; }

        /* ---- Header ---- */
        .tv-topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 2.2vh 3vw 0;
        }

        .tv-brand {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            font-size: 1.35rem;
            font-weight: 800;
            letter-spacing: 0.02em;
        }

        .tv-brand .badge-dot {
            width: 15px;
            height: 15px;
            border-radius: 50%;
            background: linear-gradient(120deg, #ef4444, #f97316, #f59e0b);
            background-size: 300% 300%;
            animation: warmFlow 3s ease infinite, softPulse 2.2s ease-in-out infinite;
        }

        @keyframes softPulse {
            0%, 100% { box-shadow: 0 0 0 rgba(249, 115, 22, 0.5); }
            50%      { box-shadow: 0 0 16px 4px rgba(249, 115, 22, 0.55); }
        }

        .tv-slide-label {
            font-size: 1.05rem;
            font-weight: 700;
            color: rgba(255, 255, 255, 0.65);
            letter-spacing: 0.06em;
        }

        .tv-right {
            display: flex;
            align-items: center;
            gap: 1.4rem;
        }

        .tv-live {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            font-weight: 800;
            letter-spacing: 0.1em;
            color: #fca5a5;
            background: rgba(0,0,0,0.28);
            padding: 0.32rem 0.75rem;
            border-radius: 999px;
        }

        .tv-live .blink {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background: #ef4444;
            animation: blink 1.3s ease-in-out infinite;
        }

        @keyframes blink {
            0%, 100% { opacity: 1; box-shadow: 0 0 8px 2px rgba(239,68,68,0.9); }
            50%      { opacity: 0.3; box-shadow: 0 0 0 rgba(239,68,68,0); }
        }

        .tv-clock {
            font-size: 1.3rem;
            font-weight: 700;
            color: rgba(255, 255, 255, 0.92);
            font-variant-numeric: tabular-nums;
        }

        .tv-legend {
            display: flex;
            gap: 2rem;
            padding: 1.3vh 3vw 0;
        }

        .tv-legend span {
            display: flex;
            align-items: center;
            gap: 0.55rem;
            font-size: 1rem;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.75);
        }

        .tv-legend .dot {
            width: 15px;
            height: 15px;
            border-radius: 5px;
        }

        .tv-legend .dot.kosong { background: linear-gradient(120deg, #5b1010, #7f1d1d); }
        .tv-legend .dot.terisi {
            background: linear-gradient(120deg, #ef4444, #f97316, #f59e0b);
            background-size: 300% 300%;
            animation: warmFlow 3s ease infinite;
        }

        /* ---- List kamar ---- */
        .tv-list {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 1.4vh;
            padding: 1.6vh 3vw 2vh;
        }

        .room-row {
            display: flex;
            align-items: center;
            gap: 1.6vw;
            border-radius: 20px;
            padding: 1.6vh 2vw;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35);
            opacity: 0;
            transform: translateY(18px);
            animation: rowRise 0.55s ease forwards;
        }

        @keyframes rowRise {
            to { opacity: 1; transform: translateY(0); }
        }

        .room-row.kosong {
            background: linear-gradient(120deg, #3f0d0d, #23070a, #3f0d0d);
        }

        .room-row.terisi {
            background: linear-gradient(110deg, #b91c1c, #ef4444, #f97316, #f59e0b, #f97316, #ef4444, #b91c1c);
            background-size: 400% 400%;
            animation: rowRise 0.55s ease forwards, warmFlow 5s ease infinite, pulseGlow 2.6s ease-in-out infinite;
        }

        @keyframes warmFlow {
            0%   { background-position: 0% 50%; }
            50%  { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        @keyframes pulseGlow {
            0%, 100% { box-shadow: 0 0 0 rgba(249, 115, 22, 0); }
            50%      { box-shadow: 0 0 40px rgba(249, 115, 22, 0.55); }
        }

        .room-icon {
            width: 3.6vw;
            min-width: 58px;
            height: 3.6vw;
            min-height: 58px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            font-size: clamp(1.3rem, 1.8vw, 1.7rem);
            background: rgba(0, 0, 0, 0.25);
            flex-shrink: 0;
        }

        .room-row.terisi .room-icon i {
            animation: iconPulse 1.6s ease-in-out infinite;
        }

        @keyframes iconPulse {
            0%, 100% { transform: scale(1); }
            50%      { transform: scale(1.18); }
        }

        .room-number {
            font-size: clamp(2.2rem, 3.6vw, 3.1rem);
            font-weight: 900;
            width: 5vw;
            min-width: 82px;
            line-height: 1;
        }

        .room-number.text-gradient {
            background: linear-gradient(100deg, #ffffff 0%, #ffe6a0 25%, #ffffff 50%, #ffe6a0 75%, #ffffff 100%);
            background-size: 250% auto;
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            animation: shimmer 4s linear infinite;
        }

        @keyframes shimmer {
            to { background-position: -250% center; }
        }

        .room-status {
            font-size: clamp(1rem, 1.3vw, 1.25rem);
            text-transform: uppercase;
            letter-spacing: 0.06em;
            font-weight: 800;
            width: 8.5vw;
            min-width: 125px;
            color: rgba(255, 255, 255, 0.95);
        }

        .room-guest {
            flex: 1;
            font-size: clamp(1.3rem, 2vw, 1.9rem);
            font-weight: 700;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .room-checkout {
            font-size: clamp(0.95rem, 1.15vw, 1.1rem);
            color: rgba(255, 255, 255, 0.85);
            white-space: nowrap;
        }

        /* ---- Footer ---- */
        .tv-bottom {
            padding: 0 3vw 2.2vh;
        }

        .tv-dots {
            display: flex;
            justify-content: center;
            gap: 0.7rem;
            margin-bottom: 1.2vh;
        }

        .tv-dots .dot {
            width: 11px;
            height: 11px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.28);
            transition: background 0.3s ease, transform 0.3s ease;
        }

        .tv-dots .dot.active {
            background: #ffffff;
            transform: scale(1.3);
        }

        .tv-progress-track {
            height: 4px;
            width: 100%;
            background: rgba(255, 255, 255, 0.18);
            border-radius: 999px;
            overflow: hidden;
        }

        .tv-progress-bar {
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, #f97316, #f59e0b, #ffffff);
            border-radius: 999px;
        }

        .tv-progress-bar.running {
            animation: fillBar 10s linear forwards;
        }

        @keyframes fillBar {
            from { width: 0%; }
            to   { width: 100%; }
        }
    </style>
</head>
<body>

<div class="tv-sky" id="tvSky"></div>

<div class="tv-viewport">
    <div class="tv-sheet pos-active" id="sheetA">
        <div class="tv-topbar">
            <div class="tv-brand"><span class="badge-dot"></span> Wisma Tamu</div>
            <div class="tv-slide-label" data-role="slide-label">Kamar 1 - 5</div>
            <div class="tv-right">
                <div class="tv-live"><span class="blink"></span> LIVE</div>
                <div class="tv-clock" data-role="clock">--:--:--</div>
            </div>
        </div>
        <div class="tv-legend">
            <span><span class="dot kosong"></span> Kosong</span>
            <span><span class="dot terisi"></span> Terisi</span>
        </div>
        <div class="tv-list" data-role="list"></div>
        <div class="tv-bottom">
            <div class="tv-dots" data-role="dots"></div>
            <div class="tv-progress-track"><div class="tv-progress-bar" data-role="progress"></div></div>
        </div>
    </div>

    <div class="tv-sheet pos-enter" id="sheetB">
        <div class="tv-topbar">
            <div class="tv-brand"><span class="badge-dot"></span> Wisma Tamu</div>
            <div class="tv-slide-label" data-role="slide-label">Kamar 1 - 5</div>
            <div class="tv-right">
                <div class="tv-live"><span class="blink"></span> LIVE</div>
                <div class="tv-clock" data-role="clock">--:--:--</div>
            </div>
        </div>
        <div class="tv-legend">
            <span><span class="dot kosong"></span> Kosong</span>
            <span><span class="dot terisi"></span> Terisi</span>
        </div>
        <div class="tv-list" data-role="list"></div>
        <div class="tv-bottom">
            <div class="tv-dots" data-role="dots"></div>
            <div class="tv-progress-track"><div class="tv-progress-bar" data-role="progress"></div></div>
        </div>
    </div>
</div>

<script>
    const dataUrl = "{{ route('wisma-tamu.tv-data') }}";
    const totalKamar = {{ \App\Models\WismaTamu::TOTAL_KAMAR }};
    const ROOMS_PER_SLIDE = 5;
    const CYCLE_MS = 10000;

    const sky = document.getElementById('tvSky');
    const sheetA = document.getElementById('sheetA');
    const sheetB = document.getElementById('sheetB');

    let front = sheetA;
    let back = sheetB;

    let kamarData = Array.from({ length: totalKamar }, (_, i) => ({
        nomor_kamar: i + 1, terisi: false, nama_tamu: null, checkout: null,
    }));

    const totalSlides = Math.ceil(totalKamar / ROOMS_PER_SLIDE);
    let currentSlide = 0;

    function buildStars() {
        const count = 110;
        for (let i = 0; i < count; i++) {
            const star = document.createElement('div');
            star.className = 'star';
            const size = (Math.random() * 2.2 + 0.8).toFixed(1);
            star.style.width = size + 'px';
            star.style.height = size + 'px';
            star.style.left = (Math.random() * 100) + '%';
            star.style.top = (Math.random() * 100) + '%';
            star.style.animationDuration = (Math.random() * 3 + 2).toFixed(1) + 's';
            star.style.animationDelay = (Math.random() * 4).toFixed(1) + 's';
            sky.appendChild(star);
        }
    }

    function spawnShootingStar() {
        const star = document.createElement('div');
        star.className = 'shooting-star';
        star.style.left = (55 + Math.random() * 35) + '%';
        star.style.top = (Math.random() * 25) + '%';
        sky.appendChild(star);
        setTimeout(() => star.remove(), 1700);
    }

    function buildDots(sheet) {
        const wrap = sheet.querySelector('[data-role="dots"]');
        wrap.innerHTML = '';
        for (let i = 0; i < totalSlides; i++) {
            const dot = document.createElement('div');
            dot.className = 'dot';
            wrap.appendChild(dot);
        }
    }

    function renderClock() {
        const now = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        document.querySelectorAll('[data-role="clock"]').forEach(el => el.textContent = now);
    }

    function roomRowMarkup(kamar, i) {
        const statusKey = kamar.terisi ? 'terisi' : 'kosong';
        const guest = kamar.terisi ? (kamar.nama_tamu ?? '') : '';
        const checkout = kamar.terisi ? ('s/d ' + (kamar.checkout ?? '-')) : '';
        const icon = kamar.terisi ? 'bi-person-fill' : 'bi-door-closed';

        return `
            <div class="room-row ${statusKey}" style="animation-delay:${i * 0.09}s">
                <div class="room-icon"><i class="bi ${icon}"></i></div>
                <div class="room-number text-gradient">${String(kamar.nomor_kamar).padStart(2, '0')}</div>
                <div class="room-status">${kamar.terisi ? 'Terisi' : 'Kosong'}</div>
                <div class="room-guest">${guest}</div>
                <div class="room-checkout">${checkout}</div>
            </div>
        `;
    }

    function renderInto(sheet, slideIndex) {
        const start = slideIndex * ROOMS_PER_SLIDE;
        const rooms = kamarData.slice(start, start + ROOMS_PER_SLIDE);

        sheet.querySelector('[data-role="list"]').innerHTML = rooms.map(roomRowMarkup).join('');
        sheet.querySelector('[data-role="slide-label"]').textContent =
            `Kamar ${rooms[0].nomor_kamar} - ${rooms[rooms.length - 1].nomor_kamar}`;

        sheet.querySelectorAll('[data-role="dots"] .dot').forEach((d, i) => d.classList.toggle('active', i === slideIndex));

        const bar = sheet.querySelector('[data-role="progress"]');
        bar.classList.remove('running');
        void bar.offsetWidth;
        bar.classList.add('running');
    }

    function slideToNext() {
        currentSlide = (currentSlide + 1) % totalSlides;
        renderInto(back, currentSlide);

        front.classList.remove('pos-active');
        front.classList.add('pos-exit');

        back.classList.remove('pos-enter');
        back.classList.add('pos-active');

        const oldFront = front;
        front = back;
        back = oldFront;

        setTimeout(() => {
            back.classList.add('no-anim');
            back.classList.remove('pos-exit');
            back.classList.add('pos-enter');
            void back.offsetWidth;
            back.classList.remove('no-anim');
        }, 700);
    }

    async function fetchStatus() {
        try {
            const res = await fetch(dataUrl, { headers: { 'Accept': 'application/json' } });
            const json = await res.json();
            kamarData = json.kamar;
            renderInto(front, currentSlide);
        } catch (err) {
            console.error('Gagal ambil status kamar:', err);
        }
    }

    buildStars();
    buildDots(sheetA);
    buildDots(sheetB);
    renderClock();
    setInterval(renderClock, 1000);
    setInterval(spawnShootingStar, 4500);

    fetchStatus().then(() => renderInto(front, currentSlide));
    setInterval(fetchStatus, 20000);
    setInterval(slideToNext, CYCLE_MS);
</script>

</body>
</html>