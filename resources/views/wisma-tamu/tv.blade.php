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
            background: #02120c;
        }

        /* ---- Langit bintang kerlap-kerlip ---- */
        .tv-sky {
            position: fixed;
            inset: 0;
            z-index: 0;
            background: radial-gradient(ellipse at 50% 15%, #0b3d28 0%, #02120c 70%);
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

        .ember {
            position: absolute;
            bottom: -2vh;
            border-radius: 50%;
            background: radial-gradient(circle, #d9f99d, #10b981 70%);
            box-shadow: 0 0 6px 1px rgba(16, 185, 129, 0.7);
            animation-name: emberRise;
            animation-timing-function: ease-in;
            animation-fill-mode: forwards;
        }

        @keyframes emberRise {
            0%   { transform: translate(0, 0) scale(1); opacity: 0.9; }
            100% { transform: translate(var(--drift, 2vw), -100vh) scale(0.3); opacity: 0; }
        }

        /* Bulatan cahaya besar melayang pelan di background, kesan aesthetic */
        .aurora {
            position: absolute;
            border-radius: 50%;
            filter: blur(60px);
            opacity: 0.35;
            animation: auroraDrift 18s ease-in-out infinite;
        }

        .aurora.a1 { width: 40vw; height: 40vw; left: -8vw; top: -12vh; background: radial-gradient(circle, #10b981, transparent 70%); }
        .aurora.a2 { width: 36vw; height: 36vw; right: -10vw; top: 20vh; background: radial-gradient(circle, #34d399, transparent 70%); animation-delay: 4s; }
        .aurora.a3 { width: 32vw; height: 32vw; left: 30vw; bottom: -14vh; background: radial-gradient(circle, #6ee7b7, transparent 70%); animation-delay: 8s; }

        @keyframes auroraDrift {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50%      { transform: translate(3vw, -2vh) scale(1.12); }
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
            background: #10b981;
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
            gap: 1.1rem;
        }

        .tv-stat {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            font-size: 0.92rem;
            font-weight: 700;
            background: rgba(0,0,0,0.28);
            padding: 0.4rem 0.85rem;
            border-radius: 999px;
            transition: transform 0.25s ease;
        }

        .tv-stat.pop { animation: statPop 0.5s ease; }

        @keyframes statPop {
            0%   { opacity: 0.55; }
            100% { opacity: 1; }
        }

        .tv-stat b.terisi-count { color: #6ee7b7; }
        .tv-stat b.kosong-count { color: #a7f3d0; }

        .tv-live {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            font-weight: 800;
            letter-spacing: 0.1em;
            color: #f87171;
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

        .tv-legend .dot.kosong { background: linear-gradient(120deg, #0a3324, #0f5c3d); }
        .tv-legend .dot.terisi {
            background: linear-gradient(120deg, #10b981, #34d399, #6ee7b7);
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
            position: relative;
            display: flex;
            align-items: center;
            gap: 1.6vw;
            border-radius: 20px;
            padding: 1.6vh 2vw;
            border: 1px solid rgba(255, 255, 255, 0.14);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35), inset 0 1px 0 rgba(255, 255, 255, 0.25);
            opacity: 0;
            transform: translateY(18px);
            animation: rowRise 0.55s ease forwards;
            isolation: isolate;
            overflow: hidden;
        }

        @keyframes rowRise {
            to { opacity: 1; transform: translateY(0); }
        }

        /* Kosong: hijau tua tenang & glossy */
        .room-row.kosong {
            background: linear-gradient(160deg, #0b4230, #06301f 55%, #0b4230);
        }

        /* Terisi: gradasi hijau-emerald-mint bergerak + glow berdenyut lembut */
        .room-row.terisi {
            background: linear-gradient(110deg, #059669, #10b981, #34d399, #6ee7b7, #34d399, #10b981, #059669);
            background-size: 400% 400%;
            border-color: rgba(255, 255, 255, 0.28);
            animation: rowRise 0.55s ease forwards, warmFlow 6s ease infinite, pulseGlow 3s ease-in-out infinite;
        }

        @keyframes warmFlow {
            0%   { background-position: 0% 50%; }
            50%  { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        @keyframes pulseGlow {
            0%, 100% { box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35), inset 0 1px 0 rgba(255, 255, 255, 0.25), 0 0 0 rgba(16, 185, 129, 0); }
            50%      { box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35), inset 0 1px 0 rgba(255, 255, 255, 0.25), 0 0 28px rgba(52, 211, 153, 0.45); }
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
            position: relative;
            z-index: 1;
        }

        .room-number {
            font-size: clamp(2.2rem, 3.6vw, 3.1rem);
            font-weight: 900;
            width: 5vw;
            min-width: 82px;
            line-height: 1;
            position: relative;
            z-index: 1;
            color: #ffffff;
        }

        .room-status {
            font-size: clamp(1rem, 1.3vw, 1.25rem);
            text-transform: uppercase;
            letter-spacing: 0.06em;
            font-weight: 800;
            width: 8.5vw;
            min-width: 125px;
            color: rgba(255, 255, 255, 0.95);
            position: relative;
            z-index: 1;
        }

        .room-guest {
            flex: 1;
            font-size: clamp(1.3rem, 2vw, 1.9rem);
            font-weight: 700;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            position: relative;
            z-index: 1;
        }

        .room-checkout {
            font-size: clamp(0.95rem, 1.15vw, 1.1rem);
            color: rgba(255, 255, 255, 0.85);
            white-space: nowrap;
            position: relative;
            z-index: 1;
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
            background: linear-gradient(90deg, #10b981, #6ee7b7, #ffffff);
            border-radius: 999px;
        }

        .tv-progress-bar.running {
            animation: fillBar 5s linear forwards;
        }

        @keyframes fillBar {
            from { width: 0%; }
            to   { width: 100%; }
        }

        @media (max-width: 600px) {
    /* --- Header --- */
    .tv-topbar {
        flex-wrap: wrap;
        row-gap: 0.6vh;
    }
    .tv-brand {
        font-size: 1.15rem;
        white-space: nowrap;
        order: 1;
    }
    .tv-right {
        order: 2;
        gap: 0.8rem;
    }
    .tv-live {
        font-size: 0.7rem;
        padding: 0.25rem 0.6rem;
    }
    .tv-clock {
        font-size: 1rem;
    }
    .tv-slide-label {
        order: 3;
        flex-basis: 100%;
        font-size: 0.9rem;
        white-space: nowrap;
        padding-left: calc(15px + 0.8rem); /* sejajar dgn teks "Wisma Tamu", bukan titiknya */
    }

    /* --- List kamar --- */
    .tv-list {
        gap: 0.8vh;
        padding: 1vh 3vw 1vh;
    }
    .room-row {
        flex-wrap: wrap;
        row-gap: 0.3rem;
        padding: 1vh 3vw;
    }
    .room-icon {
        width: 44px;
        min-width: 44px;
        height: 44px;
        min-height: 44px;
    }
    .room-number {
        width: auto;
        min-width: 50px;
        font-size: 1.8rem;
    }
    .room-status {
        width: auto;
        min-width: 70px;
        font-size: 0.85rem;
    }
    .room-guest {
        font-size: 1rem;
        min-width: 0;
        margin-left: auto;   /* dorong nama ke pojok kanan, dekat status */
        text-align: right;
        flex: 0 1 auto;
    }
    .room-checkout {
        flex-basis: 100%;
        order: 5;
        white-space: normal;
        font-size: 0.8rem;
        margin-left: calc(44px + 1.6vw); /* sejajar dgn angka kamar, bukan mepet ikon */
    }
    .room-guest:empty,
    .room-checkout:empty {
        display: none;
    }
}
    </style>
</head>
<body>

<div class="tv-sky" id="tvSky">
    <div class="aurora a1"></div>
    <div class="aurora a2"></div>
    <div class="aurora a3"></div>
</div>

<div class="tv-viewport">
    <div class="tv-sheet pos-active" id="sheetA">
        <div class="tv-topbar">
            <div class="tv-brand"><span class="badge-dot"></span> Wisma Tamu</div>
            <div class="tv-slide-label" data-role="slide-label">Kamar 1 - 5</div>
            <div class="tv-right">
                <div class="tv-stat" data-role="stat"><i class="bi bi-houses-fill"></i> <b class="terisi-count" data-role="terisi-count">0</b> Terisi &middot; <b class="kosong-count" data-role="kosong-count">0</b> Kosong</div>
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
                <div class="tv-stat" data-role="stat"><i class="bi bi-houses-fill"></i> <b class="terisi-count" data-role="terisi-count">0</b> Terisi &middot; <b class="kosong-count" data-role="kosong-count">0</b> Kosong</div>
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
    const CYCLE_MS = 5000;

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
    let lastTerisiCount = null;

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

    function spawnEmber() {
        const ember = document.createElement('div');
        ember.className = 'ember';
        const size = (Math.random() * 5 + 3).toFixed(1);
        ember.style.width = size + 'px';
        ember.style.height = size + 'px';
        ember.style.left = (Math.random() * 100) + '%';
        ember.style.setProperty('--drift', ((Math.random() * 6) - 3).toFixed(1) + 'vw');
        const duration = (Math.random() * 4 + 6).toFixed(1) + 's';
        ember.style.animationDuration = duration;
        sky.appendChild(ember);
        setTimeout(() => ember.remove(), 10500);
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
                <div class="room-number">${String(kamar.nomor_kamar).padStart(2, '0')}</div>
                <div class="room-status">${kamar.terisi ? 'Terisi' : 'Kosong'}</div>
                <div class="room-guest">${guest}</div>
                <div class="room-checkout">${checkout}</div>
            </div>
        `;
    }

    function updateStats(sheet) {
        const terisi = kamarData.filter(k => k.terisi).length;
        const kosong = totalKamar - terisi;

        sheet.querySelector('[data-role="terisi-count"]').textContent = terisi;
        sheet.querySelector('[data-role="kosong-count"]').textContent = kosong;

        if (lastTerisiCount !== null && lastTerisiCount !== terisi) {
            const stat = sheet.querySelector('[data-role="stat"]');
            stat.classList.remove('pop');
            void stat.offsetWidth;
            stat.classList.add('pop');
        }
        lastTerisiCount = terisi;
    }

    function renderInto(sheet, slideIndex) {
        const start = slideIndex * ROOMS_PER_SLIDE;
        const rooms = kamarData.slice(start, start + ROOMS_PER_SLIDE);

        sheet.querySelector('[data-role="list"]').innerHTML = rooms.map(roomRowMarkup).join('');
        sheet.querySelector('[data-role="slide-label"]').textContent =
            `Kamar ${rooms[0].nomor_kamar} - ${rooms[rooms.length - 1].nomor_kamar}`;

        sheet.querySelectorAll('[data-role="dots"] .dot').forEach((d, i) => d.classList.toggle('active', i === slideIndex));

        updateStats(sheet);

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
    setInterval(spawnEmber, 900);

    fetchStatus().then(() => renderInto(front, currentSlide));
    setInterval(fetchStatus, 20000);
    setInterval(slideToNext, CYCLE_MS);
</script>

</body>
</html>
