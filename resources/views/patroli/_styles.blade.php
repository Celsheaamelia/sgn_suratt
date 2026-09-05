<style>
    @import url('https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500;600&display=swap');

    .patroli-page {
        --p-ink: #1a1d29;
        --p-muted: #6b7280;
        --p-line: #e5e7eb;
        --p-soft: #f8faf9;
        --p-green: #064e3b;
        --p-green-soft: #d1fae5;
        --p-radius: 18px;
        --p-shadow: 0 8px 22px rgba(6, 78, 59, 0.06);
        --p-shadow-hover: 0 14px 30px rgba(6, 78, 59, 0.12);
        font-family: 'Inter', -apple-system, sans-serif;
        color: var(--p-ink);
    }

    .patroli-title {
        font-family: 'Fraunces', Georgia, serif;
        font-weight: 700;
        color: var(--p-green);
        letter-spacing: -0.01em;
    }

    .patroli-subtitle {
        color: var(--p-muted);
        font-size: 0.92rem;
    }

    .patroli-eyebrow {
        font-family: 'IBM Plex Mono', ui-monospace, monospace;
        font-size: 0.72rem;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #059669;
        font-weight: 600;
    }

    /* ===== Cards ===== */
    .patroli-card {
        background: #fff;
        border: 1px solid var(--p-line);
        border-radius: var(--p-radius);
        box-shadow: var(--p-shadow);
    }

    .patroli-card .card-body { padding: 1.35rem 1.5rem; }

    .patroli-card-link {
        position: relative;
        transition: box-shadow 0.18s ease, transform 0.18s ease;
    }

    .patroli-card-link:hover {
        box-shadow: var(--p-shadow-hover);
        transform: translateY(-2px);
    }

    /* ===== Hero banner (kartu status shift / ringkasan atas) ===== */
    .patroli-hero {
        border-radius: 22px;
        background: linear-gradient(150deg, #064e3b 0%, #0d5a44 55%, #0f6b50 100%);
        color: #f0fdf4;
        box-shadow: 0 18px 40px rgba(6, 78, 59, 0.22);
        padding: 1.75rem 2rem;
        position: relative;
        overflow: hidden;
    }

    .patroli-hero::after {
        content: '';
        position: absolute;
        right: -60px;
        top: -60px;
        width: 220px;
        height: 220px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.05);
    }

    .patroli-hero-muted { color: rgba(240, 253, 244, 0.75); font-size: 0.88rem; }

    /* ===== Buttons ===== */
    .patroli-btn-brass {
        background: linear-gradient(135deg, #10b981, #047857);
        border: none;
        color: #fff;
        font-weight: 600;
        border-radius: 10px;
        padding: 0.55rem 1.1rem;
    }
    .patroli-btn-brass:hover { color: #fff; opacity: 0.92; }
    .patroli-btn-brass:disabled { opacity: 0.5; }

    .patroli-btn-ghost {
        border: 1px solid #d1d5db;
        color: #374151;
        border-radius: 10px;
        font-weight: 600;
        padding: 0.55rem 1.1rem;
        background: #fff;
    }
    .patroli-btn-ghost:hover { background: #f8faf9; color: #1f2937; }

    .patroli-btn-light {
        background: rgba(255, 255, 255, 0.14);
        border: 1px solid rgba(255, 255, 255, 0.3);
        color: #fff;
        font-weight: 600;
        border-radius: 10px;
        padding: 0.55rem 1.1rem;
    }
    .patroli-btn-light:hover { background: rgba(255, 255, 255, 0.22); color: #fff; }

    .patroli-btn-danger {
        background: linear-gradient(135deg, #ef4444, #b91c1c);
        border: none;
        color: #fff;
        font-weight: 600;
        border-radius: 10px;
        padding: 0.55rem 1.1rem;
    }
    .patroli-btn-danger:hover { color: #fff; opacity: 0.92; }

    .patroli-btn-icon {
        border: 1px solid var(--p-line);
        border-radius: 9px;
        padding: 0.35rem 0.6rem;
        background: #fff;
        color: #374151;
    }
    .patroli-btn-icon:hover { background: #f3f4f6; }

    /* ===== Status pills ===== */
    .patroli-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.3rem 0.75rem;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 600;
        white-space: nowrap;
    }
    .patroli-pill.aman      { background: #d1fae5; color: #047857; }
    .patroli-pill.temuan    { background: #fef3c7; color: #b45309; }
    .patroli-pill.bahaya    { background: #fee2e2; color: #b91c1c; }
    .patroli-pill.kosong    { background: #f1f5f9; color: #64748b; }
    .patroli-pill.berjalan  { background: #dbeafe; color: #1d4ed8; }
    .patroli-pill.selesai   { background: #e5e7eb; color: #374151; }
    .patroli-pill.terlambat { background: #fef3c7; color: #b45309; }
    .patroli-pill.ditangani { background: #f1f5f9; color: #475569; border: 1px solid var(--p-line); }

    /* ===== Stat tiles ===== */
    .patroli-stat {
        display: flex;
        align-items: center;
        gap: 0.9rem;
    }

    .patroli-stat-icon {
        width: 46px;
        height: 46px;
        min-width: 46px;
        border-radius: 13px;
        display: grid;
        place-items: center;
        font-size: 1.15rem;
        color: #fff;
    }
    .patroli-stat-icon.green  { background: linear-gradient(135deg, #34d399, #059669); }
    .patroli-stat-icon.blue   { background: linear-gradient(135deg, #60a5fa, #2563eb); }
    .patroli-stat-icon.amber  { background: linear-gradient(135deg, #fbbf24, #d97706); }
    .patroli-stat-icon.slate  { background: linear-gradient(135deg, #94a3b8, #475569); }

    .patroli-stat-value {
        font-family: 'Fraunces', Georgia, serif;
        font-weight: 700;
        font-size: 1.6rem;
        color: var(--p-ink);
        line-height: 1.1;
    }
    .patroli-stat-label { color: var(--p-muted); font-size: 0.82rem; }

    /* ===== Progress bar ===== */
    .patroli-progress {
        height: 8px;
        border-radius: 999px;
        background: #eef2f0;
        overflow: hidden;
    }
    .patroli-progress .bar {
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, #34d399, #059669);
        transition: width 0.3s ease;
    }
    .patroli-progress .bar.warn { background: linear-gradient(90deg, #fbbf24, #d97706); }

    /* ===== Checkpoint tile (grid) ===== */
    .patroli-cp-tile {
        border: 1px solid var(--p-line);
        border-radius: 16px;
        background: #fff;
        box-shadow: var(--p-shadow);
        border-left: 4px solid #cbd5e1;
        transition: box-shadow 0.18s ease, transform 0.18s ease;
    }
    .patroli-cp-tile:hover { box-shadow: var(--p-shadow-hover); transform: translateY(-2px); }
    .patroli-cp-tile.st-aman   { border-left-color: #10b981; }
    .patroli-cp-tile.st-temuan { border-left-color: #f59e0b; }
    .patroli-cp-tile.st-bahaya { border-left-color: #ef4444; }
    .patroli-cp-tile .card-body { padding: 1.1rem 1.2rem; }

    /* ===== Status pick (form scan) ===== */
    .patroli-status-pick input[type="radio"] { display: none; }
    .patroli-status-pick label {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.4rem;
        border: 2px solid var(--p-line);
        border-radius: 14px;
        padding: 1rem 0.5rem;
        color: #6b7280;
        font-weight: 600;
        font-size: 0.86rem;
        cursor: pointer;
        transition: all 0.15s ease;
        background: #fff;
    }
    .patroli-status-pick label i { font-size: 1.5rem; }
    .patroli-status-pick label:hover { border-color: #a7f3d0; }

    .patroli-status-pick input#status_aman:checked + label {
        border-color: #10b981; background: #ecfdf5; color: #047857;
    }
    .patroli-status-pick input#status_temuan:checked + label {
        border-color: #f59e0b; background: #fffbeb; color: #b45309;
    }
    .patroli-status-pick input#status_bahaya:checked + label {
        border-color: #ef4444; background: #fef2f2; color: #b91c1c;
    }

    /* ===== Form section (dipakai di form scan) ===== */
    .patroli-form-section .form-label { font-weight: 600; color: #1f2937; font-size: 0.9rem; }
    .patroli-form-section .form-control,
    .patroli-form-section .form-select {
        border: 1px solid #dbe3df;
        border-radius: 10px;
        padding: 0.6rem 0.85rem;
        font-size: 0.95rem;
    }
    .patroli-form-section .form-control:focus,
    .patroli-form-section .form-select:focus {
        border-color: #34d399;
        box-shadow: 0 0 0 0.2rem rgba(16, 185, 129, 0.15);
    }

    /* ===== Timeline (detail sesi) ===== */
    .patroli-timeline { position: relative; }
    .patroli-timeline::before {
        content: '';
        position: absolute;
        left: 21px;
        top: 8px;
        bottom: 8px;
        width: 2px;
        background: var(--p-line);
    }
    .patroli-timeline-item {
        position: relative;
        padding-left: 3.2rem;
        margin-bottom: 1rem;
    }
    .patroli-timeline-dot {
        position: absolute;
        left: 8px;
        top: 0.15rem;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: grid;
        place-items: center;
        color: #fff;
        font-size: 0.9rem;
        box-shadow: 0 0 0 4px #fff;
        z-index: 1;
    }
    .patroli-timeline-dot.aman   { background: #10b981; }
    .patroli-timeline-dot.temuan { background: #f59e0b; }
    .patroli-timeline-dot.bahaya { background: #ef4444; }

    /* ===== Table ===== */
    .patroli-table thead th {
        background: #f8faf9;
        border-bottom: 2px solid var(--p-line);
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        color: #4b5563;
        font-weight: 700;
    }
    .patroli-table td, .patroli-table th { vertical-align: middle; }

    /* ===== Empty state ===== */
    .patroli-empty {
        text-align: center;
        color: var(--p-muted);
        padding: 2.75rem 1rem;
        border: 1px dashed var(--p-line);
        border-radius: var(--p-radius);
        background: var(--p-soft);
    }
    .patroli-empty i { font-size: 1.8rem; color: #a7c9bc; }

    /* ===== Alerts ===== */
    .patroli-alert-success { background: #d1fae5; border: 1px solid #a7f3d0; color: #047857; border-radius: 12px; padding: 0.8rem 1.1rem; }
    .patroli-alert-info    { background: #dbeafe; border: 1px solid #bfdbfe; color: #1d4ed8; border-radius: 12px; padding: 0.8rem 1.1rem; }
    .patroli-alert-danger  { background: #fee2e2; border: 1px solid #fecaca; color: #b91c1c; border-radius: 12px; padding: 0.8rem 1.1rem; }

    /* ===== QR print card ===== */
    .patroli-qr-card {
        border: 1px solid var(--p-line);
        border-radius: 16px;
        text-align: center;
        padding: 1.3rem 1rem;
        background: #fff;
    }
    .patroli-qr-card h6 { font-family: 'Fraunces', Georgia, serif; font-weight: 700; color: var(--p-green); }

    @media print {
        .patroli-qr-card { break-inside: avoid; box-shadow: none; }
    }
</style>
