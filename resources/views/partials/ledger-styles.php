<style>
    @import url('https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500;600&display=swap');

    :root {
        --ink:        #1c2b23;
        --ink-soft:   #3d4f45;
        --ledger:     #eef3ea;
        --ledger-line:#cdd9c8;
        --paper:      #fbfcf9;
        --brass:      #a9812f;
        --brass-dark: #8a6a24;
        --brass-tint: #f4ecd8;
        --line:       #dfe6da;
        --danger:     #b3432f;
        --danger-bg:  #fdf1ee;
        --success:    #3f6b4a;
        --success-bg: #eef5ef;

        --font-display: 'Fraunces', Georgia, serif;
        --font-body: 'Inter', -apple-system, sans-serif;
        --font-mono: 'IBM Plex Mono', ui-monospace, monospace;
    }

    .ledger-page { background: transparent; font-family: var(--font-body); color: var(--ink); min-height: 100vh; }

    .ledger-alert-success { background: var(--success-bg); border: 1px solid #cfe2d4; color: var(--success); border-radius: 0.75rem; font-size: 0.9rem; }
    .ledger-alert-danger { background: var(--danger-bg); border: 1px solid #f2d3cc; color: var(--danger); border-radius: 0.75rem; font-size: 0.9rem; }

    .ledger-card, .ledger-stamp, .ledger-status { border-radius: 0.9rem; border: 1px solid var(--line); box-shadow: 0 1px 2px rgba(28,43,35,0.05), 0 1px 10px rgba(28,43,35,0.04); }
    .ledger-card { background: var(--paper); }
    .ledger-card-header { background: transparent; border-bottom: 1px solid var(--line); padding: 1.5rem 1.75rem 1.1rem; }
    .ledger-title { font-family: var(--font-display); font-weight: 600; font-size: 1.55rem; color: var(--ink); letter-spacing: -0.01em; margin-bottom: 0.25rem; }
    .ledger-subtitle { color: var(--ink-soft); font-size: 0.85rem; }
    .ledger-card .card-body { padding: 1.75rem; }

    .ledger-form label { color: var(--ink); font-weight: 600; font-size: 0.82rem; letter-spacing: 0.01em; }
    .ledger-required { color: var(--brass-dark); }

    .ledger-form .form-control, .ledger-form .form-select, .ledger-toolbar .form-control, .ledger-toolbar .form-select {
        border-radius: 0.55rem; border: 1px solid var(--line); padding: 0.65rem 0.95rem; font-size: 0.92rem;
        font-family: var(--font-body); color: var(--ink); background-color: var(--paper);
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .ledger-form .form-control:focus, .ledger-form .form-select:focus, .ledger-toolbar .form-control:focus, .ledger-toolbar .form-select:focus {
        outline: none; border-color: var(--brass); box-shadow: 0 0 0 3px rgba(169,129,47,0.16);
    }
    .ledger-form .form-control[readonly], .ledger-form .form-control:disabled {
        background-color: var(--ledger); color: var(--ink-soft); border-color: var(--ledger-line);
        font-family: var(--font-mono); letter-spacing: 0.06em; cursor: not-allowed;
    }
    .ledger-help { color: #8a9587; font-size: 0.75rem; margin-top: 0.35rem; }

    .ledger-btn-ghost { background: transparent; color: var(--ink-soft); font-weight: 500; border: none; padding: 0.6rem 1rem; transition: color 0.15s ease; }
    .ledger-btn-ghost:hover { color: var(--ink); }

    .ledger-btn-brass {
        background: linear-gradient(180deg, #b8903f, var(--brass-dark)); box-shadow: 0 4px 14px rgba(138,106,36,0.35);
        border: none; color: #fff; font-weight: 600; letter-spacing: 0.01em; padding: 0.65rem 1.4rem; border-radius: 0.55rem;
    }
    .ledger-btn-brass:hover { background: linear-gradient(180deg, #c39a4c, #7a5c1f); box-shadow: 0 6px 18px rgba(138,106,36,0.42); color: #fff; }

    .ledger-btn-detail {
        background: transparent; border: 1px solid var(--ink); color: var(--ink-soft); font-family: var(--font-body);
        font-weight: 600; font-size: 0.8rem; padding: 0.4rem 0.9rem; border-radius: 0.5rem; transition: all 0.15s ease;
    }
    .ledger-btn-detail:hover { background: var(--ink); color: #fff; }

    .ledger-stamp { background: linear-gradient(160deg, #24382e 0%, var(--ink) 70%); border: 1px solid rgba(255,255,255,0.06); position: relative; overflow: hidden; color: var(--brass-tint); }
    .ledger-stamp-title { font-family: var(--font-display); font-weight: 600; font-size: 1.05rem; color: var(--brass-tint); }
    .ledger-stamp .fa-eye, .ledger-stamp .bi-eye { color: var(--brass); }
    .ledger-stamp-box { background: rgba(169,129,47,0.08); border: 1.5px dashed rgba(244,236,216,0.35); border-radius: 0.85rem; position: relative; padding: 1.1rem 1rem; }
    .ledger-stamp-label { color: rgba(244,236,216,0.65); font-family: var(--font-mono); font-size: 0.65rem; letter-spacing: 0.22em; text-transform: uppercase; }
    #previewNumber { display: inline-block; color: var(--brass-tint); font-family: var(--font-mono); font-weight: 600; font-size: 1.05rem; letter-spacing: 0.04em; word-break: break-all; }
    .ledger-stamp-key { color: rgba(244,236,216,0.55); font-size: 0.83rem; }
    .ledger-stamp-value { color: var(--brass-tint); font-family: var(--font-mono); font-weight: 600; font-size: 0.86rem; letter-spacing: 0.02em; }

    .ledger-status { background: var(--paper); }
    .ledger-status-title { font-family: var(--font-display); font-weight: 600; font-size: 1rem; color: var(--ink); }
    .ledger-status-dot { width: 0.5rem; height: 0.5rem; border-radius: 50%; background: var(--brass); box-shadow: 0 0 0 3px rgba(169,129,47,0.18); display: inline-block; flex-shrink: 0; }
    .ledger-status-line { color: var(--ink-soft); font-family: var(--font-mono); font-size: 0.82rem; }

    .ledger-badge { background: var(--brass-tint); color: var(--brass-dark); font-family: var(--font-mono); font-size: 0.78rem; font-weight: 600; letter-spacing: 0.03em; padding: 0.4rem 0.85rem; border-radius: 999px; white-space: nowrap; display: inline-block; border: 1px solid rgba(169,129,47,0.25); }
    .ledger-badge-riwayat { background: var(--brass-tint); color: var(--brass-dark); font-size: 0.72rem; font-weight: 600; padding: 0.15rem 0.5rem; border-radius: 999px; white-space: nowrap; display: inline-block; border: 1px solid rgba(169,129,47,0.25); margin-left: 0.35rem; }
    .ledger-btn-ghost:disabled, .ledger-btn-brass:disabled { opacity: 0.5; cursor: not-allowed; }
    .ledger-inline-status { font-size: 0.8rem; color: var(--ink-soft); display: inline-flex; align-items: center; gap: 0.3rem; white-space: nowrap; }
    .ledger-spinner {
        display: inline-block;
        width: 0.8rem;
        height: 0.8rem;
        border: 2px solid rgba(0,0,0,0.15);
        border-top-color: var(--brass-dark, #a9812f);
        border-radius: 50%;
        animation: ledger-spin 0.7s linear infinite;
    }
    @keyframes ledger-spin { to { transform: rotate(360deg); } }

    .ledger-table-title { font-family: var(--font-display); font-weight: 600; font-size: 1.1rem; color: var(--ink); }
    .ledger-table { width: 100%; margin-bottom: 0; border-collapse: collapse; }
    .ledger-table thead th { background: var(--ledger); color: var(--ink-soft); font-family: var(--font-mono); font-size: 0.7rem; letter-spacing: 0.1em; text-transform: uppercase; font-weight: 600; border-bottom: none; padding: 0.9rem 1.5rem; white-space: nowrap; }
    .ledger-table tbody tr { border-top: 1px solid var(--ledger-line); transition: background 0.15s ease; }
    .ledger-table tbody tr:hover { background: var(--brass-tint); }
    .ledger-table tbody td { padding: 1rem 1.5rem; vertical-align: middle; font-size: 0.9rem; }
    .ledger-table .ledger-nomor { color: var(--brass-dark); font-family: var(--font-mono); font-weight: 600; font-size: 0.84rem; letter-spacing: 0.02em; word-break: break-all; }
    .ledger-table .ledger-perihal { color: var(--ink); }
    .ledger-table .ledger-tujuan, .ledger-table .ledger-signatory { color: var(--ink-soft); }
    .ledger-table .ledger-tanggal { color: var(--ink-soft); font-family: var(--font-mono); font-size: 0.82rem; white-space: nowrap; }

    .ledger-status-pill { font-family: var(--font-mono); font-size: 0.68rem; font-weight: 600; letter-spacing: 0.05em; text-transform: uppercase; padding: 0.32rem 0.7rem; border-radius: 999px; white-space: nowrap; display: inline-block; }
    .ledger-status-pill.is-uploaded, .ledger-status-pill.is-active { background: var(--success-bg); color: var(--success); border: 1px solid #cfe2d4; }
    .ledger-status-pill.is-pending, .ledger-status-pill.is-draft { background: var(--brass-tint); color: var(--brass-dark); border: 1px solid rgba(169,129,47,0.25); }
    .ledger-status-pill.is-reserved { background: var(--danger-bg); color: var(--danger); border: 1px solid #f2d3cc; }
    .ledger-status-pill.is-done { background: var(--ledger); color: var(--ink-soft); border: 1px solid var(--ledger-line); }

    /* Kotak hasil pencarian karyawan (autofill) */
    .karyawan-search-wrap { position: relative; }
    .karyawan-search-results {
        position: absolute; top: calc(100% + 4px); left: 0; right: 0; z-index: 40;
        background: var(--paper); border: 1px solid var(--line); border-radius: 0.55rem;
        box-shadow: 0 8px 24px rgba(28,43,35,0.12); max-height: 260px; overflow-y: auto; display: none;
    }
    .karyawan-search-results.show { display: block; }
    .karyawan-search-item { padding: 0.6rem 0.85rem; cursor: pointer; font-size: 0.88rem; border-bottom: 1px solid var(--ledger-line); }
    .karyawan-search-item:last-child { border-bottom: none; }
    .karyawan-search-item:hover, .karyawan-search-item.is-active { background: var(--brass-tint); }
    .karyawan-search-item .nik { font-family: var(--font-mono); font-size: 0.75rem; color: var(--ink-soft); }
    .karyawan-selected-card { background: var(--ledger); border: 1px dashed var(--ledger-line); border-radius: 0.6rem; padding: 0.9rem 1rem; }

    @media (prefers-reduced-motion: reduce) { * { transition: none !important; } }
</style>