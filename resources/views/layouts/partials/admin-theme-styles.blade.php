<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        line-height: 1.6;
        color: #333;
        overflow-x: hidden;
        background: #f8f8f8;
    }
    nav {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.2rem 2rem;
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
        position: sticky;
        top: 0;
        z-index: 1000;
    }
    .logo {
        font-size: 1.8rem;
        font-weight: 700;
        color: #fff;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none;
    }
    .logo i { color: #ff6b35; font-size: 2rem; }
    .logo span {
        background: linear-gradient(135deg, #ff6b35, #ff9f1c);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .nav-buttons { display: flex; gap: 1rem; align-items: center; }
    .btn {
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    .page-header {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
        color: #fff;
        padding: 2.5rem 2rem 2rem;
        position: relative;
        overflow: hidden;
    }
    .page-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 420px;
        height: 420px;
        background: radial-gradient(circle, rgba(255, 107, 53, 0.18) 0%, transparent 70%);
        border-radius: 50%;
    }
    .header-inner {
        max-width: 1200px;
        margin: 0 auto;
        position: relative;
        z-index: 2;
    }
    .breadcrumbs {
        display: inline-flex;
        gap: 0.5rem;
        align-items: center;
        font-size: 0.95rem;
        color: rgba(255,255,255,0.75);
        margin-bottom: 0.75rem;
    }
    .breadcrumbs a {
        color: rgba(255,255,255,0.9);
        text-decoration: none;
    }
    .breadcrumbs a:hover { text-decoration: underline; text-underline-offset: 4px; }
    .page-title {
        font-size: 2.2rem;
        font-weight: 800;
        line-height: 1.15;
        margin-bottom: 0.5rem;
        background: linear-gradient(135deg, #fff, #ff9f1c);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .page-subtitle { color: rgba(255,255,255,0.78); max-width: 720px; }
    .admin-main {
        max-width: 1200px;
        margin: 0 auto;
        padding: 2rem 2rem 4rem;
    }
    .flash-banner { margin-bottom: 1.25rem; }
    .flash {
        border-radius: 14px;
        padding: 0.95rem 1.1rem;
        display: flex;
        gap: 0.75rem;
        align-items: flex-start;
        font-weight: 700;
        border: 1px solid rgba(0,0,0,0.06);
        box-shadow: 0 8px 22px rgba(0,0,0,0.06);
    }
    .flash.success {
        background: #ecfdf5;
        color: #065f46;
        border-color: rgba(16, 185, 129, 0.25);
    }
    .flash.success i { color: #10b981; margin-top: 2px; }
    .flash.error {
        background: #fef2f2;
        color: #991b1b;
        border-color: rgba(239, 68, 68, 0.25);
    }
    .flash.error i { color: #ef4444; margin-top: 2px; }
    .toolbar {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.25rem;
    }
    .toolbar h2 { font-size: 1.35rem; color: #1a1a2e; }
    .btn-action {
        padding: 0.85rem 1.2rem;
        background: linear-gradient(135deg, #ff6b35, #ff9f1c);
        color: #fff;
        border: none;
        border-radius: 10px;
        font-size: 1rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none;
        justify-content: center;
    }
    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 30px rgba(255, 107, 53, 0.25);
    }
    .btn-ghost {
        padding: 0.65rem 1rem;
        border-radius: 10px;
        border: 1px solid rgba(0,0,0,0.12);
        background: #fff;
        color: #1a1a2e;
        font-weight: 700;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.25s ease;
    }
    .btn-ghost:hover {
        border-color: rgba(255, 107, 53, 0.5);
        box-shadow: 0 8px 20px rgba(0,0,0,0.06);
    }
    .btn-danger {
        background: #fef2f2;
        color: #b91c1c;
        border-color: rgba(220, 38, 38, 0.35);
    }
    .btn-danger:hover { border-color: #dc2626; }
    .card-panel {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        border: 1px solid rgba(0,0,0,0.06);
        overflow: hidden;
    }
    .data-table-wrap { overflow-x: auto; }
    .data-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.95rem;
    }
    .data-table th,
    .data-table td {
        padding: 0.85rem 1rem;
        text-align: left;
        border-bottom: 1px solid rgba(0,0,0,0.06);
    }
    .data-table th {
        background: #f6f6f6;
        font-weight: 800;
        color: #1a1a2e;
        font-size: 0.82rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .data-table tr:hover td { background: rgba(255, 107, 53, 0.04); }
    .pill {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.25rem 0.6rem;
        border-radius: 999px;
        font-size: 0.8rem;
        font-weight: 800;
    }
    .pill-yes { background: #ecfdf5; color: #065f46; }
    .pill-no { background: #f3f4f6; color: #6b7280; }
    .row-actions { display: flex; flex-wrap: wrap; gap: 0.5rem; }
    .form-card { padding: 1.5rem 1.75rem 2rem; }
    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem 1.25rem;
    }
    .form-grid .full { grid-column: 1 / -1; }
    .field label {
        display: block;
        font-weight: 700;
        font-size: 0.88rem;
        color: #1a1a2e;
        margin-bottom: 0.35rem;
    }
    .field input,
    .field select {
        width: 100%;
        padding: 0.75rem 0.9rem;
        border-radius: 10px;
        border: 1px solid rgba(0,0,0,0.1);
        background: #f9fafb;
        font-size: 1rem;
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .field input:focus,
    .field select:focus {
        border-color: rgba(255, 107, 53, 0.55);
        box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.12);
        background: #fff;
    }
    .field .hint { font-size: 0.82rem; color: #6b7280; margin-top: 0.25rem; }
    .check-row {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        padding: 0.5rem 0;
    }
    .check-row input { width: auto; accent-color: #ff6b35; }
    .form-actions {
        margin-top: 1.5rem;
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        align-items: center;
    }
    .errors {
        background: #fef2f2;
        border: 1px solid rgba(239, 68, 68, 0.25);
        color: #991b1b;
        border-radius: 12px;
        padding: 1rem 1.1rem;
        margin-bottom: 1.25rem;
        font-size: 0.95rem;
    }
    .errors ul { margin: 0.35rem 0 0 1.1rem; }
    footer {
        background: #0f0f1e;
        color: #999;
        padding: 2rem;
        text-align: center;
        font-size: 0.9rem;
    }
    @media (max-width: 768px) {
        nav { flex-direction: column; gap: 1rem; }
        .form-grid { grid-template-columns: 1fr; }
        .page-title { font-size: 1.75rem; }
        .admin-main { padding: 1.25rem 1.1rem 3rem; }
    }
</style>
