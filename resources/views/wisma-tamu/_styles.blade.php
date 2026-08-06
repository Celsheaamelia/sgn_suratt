<style>
    .wisma-page {
        font-family: 'Inter', -apple-system, sans-serif;
    }

    .wisma-title {
        font-family: 'Fraunces', Georgia, serif;
        font-weight: 700;
        color: #064e3b;
    }

    .wisma-subtitle {
        color: #6b7280;
        font-size: 0.92rem;
    }

    .wisma-card {
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 8px 22px rgba(6, 78, 59, 0.05);
    }

    .wisma-card .card-header {
        background: transparent;
        border-bottom: 1px solid #eef2f0;
        border-radius: 16px 16px 0 0;
        padding: 1.1rem 1.3rem;
    }

    .wisma-btn-brass {
        background: linear-gradient(135deg, #10b981, #047857);
        border: none;
        color: #fff;
        font-weight: 600;
        border-radius: 10px;
        padding: 0.5rem 1.05rem;
    }

    .wisma-btn-brass:hover { color: #fff; opacity: 0.92; }

    .wisma-btn-ghost {
        border: 1px solid #d1d5db;
        color: #374151;
        border-radius: 10px;
        font-weight: 600;
        padding: 0.5rem 1.05rem;
    }

    .wisma-btn-icon {
        border: 1px solid #e5e7eb;
        border-radius: 9px;
        padding: 0.35rem 0.6rem;
        background: #fff;
        color: #374151;
    }

    .wisma-btn-icon:hover { background: #f3f4f6; }

    .wisma-required { color: #ef4444; }

    .wisma-table thead th {
        background: #f8faf9;
        border-bottom: 2px solid #e5e7eb;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        color: #4b5563;
    }

    .wisma-pill {
        display: inline-flex;
        align-items: center;
        padding: 0.28rem 0.7rem;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 600;
    }

    .wisma-pill.terisi { background: #fee2e2; color: #b91c1c; }
    .wisma-pill.kosong { background: #d1fae5; color: #047857; }

    .wisma-alert-success {
        background: #d1fae5;
        border: 1px solid #a7f3d0;
        color: #047857;
        border-radius: 12px;
        padding: 0.8rem 1.1rem;
    }

    .wisma-alert-danger {
        background: #fee2e2;
        border: 1px solid #fecaca;
        color: #b91c1c;
        border-radius: 12px;
        padding: 0.8rem 1.1rem;
    }

    /* ---- Form Input Tamu ---- */
    .wisma-form-section {
        background: #f8faf9;
        border: 1px solid #eef2f0;
        border-radius: 16px;
        padding: 1.6rem 1.7rem;
        margin-bottom: 1.6rem;
    }

    .wisma-form-section-title {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        font-family: 'Fraunces', Georgia, serif;
        font-weight: 700;
        font-size: 1.05rem;
        color: #064e3b;
        margin-bottom: 1.3rem;
    }

    .wisma-form-section-title i {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: grid;
        place-items: center;
        background: linear-gradient(135deg, #34d399, #059669);
        color: #fff;
        font-size: 0.95rem;
    }

    .wisma-form-section .form-label {
        font-weight: 600;
        color: #1f2937;
        font-size: 0.9rem;
        margin-bottom: 0.45rem;
    }

    .wisma-form-section .form-control,
    .wisma-form-section .form-select {
        border: 1px solid #dbe3df;
        border-radius: 10px;
        padding: 0.62rem 0.85rem;
        font-size: 0.95rem;
        background: #fff;
    }

    .wisma-form-section .form-control:focus,
    .wisma-form-section .form-select:focus {
        border-color: #34d399;
        box-shadow: 0 0 0 0.2rem rgba(16, 185, 129, 0.15);
    }

    .wisma-form-section .form-text {
        font-size: 0.8rem;
        margin-top: 0.35rem;
    }

    .wisma-form-section .row > div {
        margin-bottom: 1.1rem;
    }

    .wisma-form-section .row:last-child > div {
        margin-bottom: 0;
    }
</style>
