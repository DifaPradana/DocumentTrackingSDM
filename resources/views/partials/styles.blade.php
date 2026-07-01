<link rel="stylesheet" href="{{ asset('assets/css/styles.min.css') }}" />
<style>
    /* ===== Scoped ke .fc-modernize, tidak akan nabrak CSS template lain ===== */
    .fc-modernize * {
        box-sizing: border-box;
    }

    .fc-modernize {
        font-family: inherit;
    }

    .fc-modernize .sr-only {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0;
    }

    .fc-modernize .cal-wrap {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
        min-height: 560px;
    }

    .fc-modernize .cal-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 20px;
        border-bottom: 1px solid #e5e7eb;
        flex-wrap: wrap;
        gap: 8px;
    }

    .fc-modernize .cal-nav {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .fc-modernize .btn-nav {
        width: 32px;
        height: 32px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        background: #ffffff;
        color: #1f2937;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        transition: background 0.15s;
    }

    .fc-modernize .btn-nav:hover {
        background: #f3f4f6;
    }

    .fc-modernize .btn-today {
        padding: 0 14px;
        height: 32px;
        border: none;
        border-radius: 6px;
        background: #5d87ff;
        color: #fff;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: opacity 0.15s;
    }

    .fc-modernize .btn-today:hover {
        opacity: 0.88;
    }

    .fc-modernize .cal-title {
        font-size: 18px;
        font-weight: 600;
        color: #1f2937;
    }

    .fc-modernize .cal-title-wrap {
        position: relative;
    }

    .fc-modernize .cal-title-btn {
        background: transparent;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 4px 8px;
        border-radius: 6px;
    }

    .fc-modernize .cal-title-btn:hover {
        background: #f3f4f6;
    }

    .fc-modernize .cal-title-btn::after {
        content: '';
        width: 0;
        height: 0;
        border-left: 4px solid transparent;
        border-right: 4px solid transparent;
        border-top: 5px solid #6b7280;
        margin-left: 2px;
    }

    .fc-modernize .fc-jump-popover {
        display: none;
        position: absolute;
        top: calc(100% + 6px);
        left: 0;
        z-index: 20;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
        padding: 12px;
        min-width: 220px;
    }

    .fc-modernize .fc-jump-popover.open {
        display: block;
    }

    .fc-modernize .fc-jump-row {
        display: flex;
        gap: 8px;
        margin-bottom: 10px;
    }

    .fc-modernize .fc-jump-select {
        flex: 1;
        height: 36px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        padding: 0 8px;
        font-size: 13px;
        color: #1f2937;
        background: #fff;
    }

    .fc-modernize .fc-jump-go {
        width: 100%;
        height: 34px;
        border: none;
        border-radius: 6px;
        background: #5d87ff;
        color: #fff;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
    }

    .fc-modernize .fc-jump-go:hover {
        opacity: 0.9;
    }

    .fc-modernize .btn-add-event {
        display: flex;
        align-items: center;
        gap: 6px;
        height: 32px;
        padding: 0 14px;
        border: none;
        border-radius: 6px;
        background: #13deb9;
        color: #fff;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: opacity 0.15s;
    }

    .fc-modernize .btn-add-event:hover {
        opacity: 0.88;
    }

    .fc-modernize .view-tabs {
        display: flex;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        overflow: hidden;
    }

    .fc-modernize .view-tab {
        padding: 6px 16px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        border: none;
        background: #ffffff;
        color: #6b7280;
        transition: all 0.15s;
    }

    .fc-modernize .view-tab.active {
        background: #5d87ff;
        color: #fff;
    }

    .fc-modernize .view-tab:not(.active):hover {
        background: #f3f4f6;
    }

    /* ===== Month view ===== */
    .fc-modernize .cal-head {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        border-bottom: 1px solid #e5e7eb;
    }

    .fc-modernize .cal-head-cell {
        padding: 10px 0;
        text-align: center;
        font-size: 12px;
        font-weight: 600;
        color: #6b7280;
        letter-spacing: 0.04em;
    }

    .fc-modernize .cal-body {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
    }

    .fc-modernize .cal-cell {
        min-height: 90px;
        padding: 6px 8px;
        border-right: 1px solid #e5e7eb;
        border-bottom: 1px solid #e5e7eb;
        position: relative;
        cursor: pointer;
        transition: background 0.1s;
    }

    .fc-modernize .cal-body .cal-cell:nth-child(7n) {
        border-right: none;
    }

    .fc-modernize .cal-cell:hover {
        background: #f9fafb;
    }

    .fc-modernize .cal-cell.today {
        background: rgba(93, 135, 255, 0.06);
    }

    .fc-modernize .cal-cell.today .day-num {
        background: #5d87ff;
        color: #fff;
        border-radius: 50%;
    }

    .fc-modernize .cal-cell.other-month {
        background: #fafafa;
    }

    .fc-modernize .cal-cell.other-month .day-num {
        color: #9ca3af;
    }

    .fc-modernize .day-num {
        font-size: 13px;
        font-weight: 500;
        color: #1f2937;
        margin-bottom: 4px;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .fc-modernize .event {
        border-radius: 4px;
        padding: 2px 6px;
        font-size: 11px;
        font-weight: 500;
        margin-bottom: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        cursor: pointer;
        transition: opacity 0.15s;
    }

    .fc-modernize .event:hover {
        opacity: 0.82;
    }

    .fc-modernize .event.violet {
        background: #635bff;
        color: #fff;
    }

    .fc-modernize .event.amber {
        background: #ffd648;
        color: #fff;
    }

    .fc-modernize .event.teal {
        background: #13deb9;
        color: #fff;
    }

    .fc-modernize .event.pink {
        background: #ff6692;
        color: #fff;
    }

    .fc-modernize .event.blue {
        background: #1a97f5;
        color: #fff;
    }

    @media (max-width: 576px) {
        .fc-modernize .cal-title-wrap {
            order: -1;
            width: 100%;
            text-align: center;
        }

        .fc-modernize .cal-title-btn {
            margin: 0 auto;
        }

        .fc-modernize .fc-jump-popover {
            left: 50%;
            transform: translateX(-50%);
        }
    }

    /* ===== Week / Day time-grid view ===== */
    .fc-time-view {
        max-height: 640px;
        overflow-y: auto;
    }

    .fc-time-scroll {
        display: grid;
        grid-template-columns: 56px 1fr;
        grid-template-rows: auto auto 1fr;
    }

    /* header row: tanggal */
    .fc-time-head {
        grid-column: 1 / -1;
        display: grid;
        grid-template-columns: 56px repeat(var(--fc-time-cols, 7), 1fr);
        position: sticky;
        top: 0;
        background: #fff;
        z-index: 3;
        border-bottom: 1px solid #e5e7eb;
    }

    .fc-time-head-cell {
        padding: 8px 4px;
        text-align: center;
        font-size: 12px;
        font-weight: 600;
        color: #374151;
    }

    .fc-time-head-cell.today {
        color: #5d87ff;
    }

    .fc-time-head-gutter {
        border-right: 1px solid #e5e7eb;
    }

    /* all-day row */
    .fc-allday-row {
        grid-column: 1 / -1;
        display: grid;
        grid-template-columns: 56px repeat(var(--fc-time-cols, 7), 1fr);
        border-bottom: 1px solid #e5e7eb;
        position: sticky;
        top: 37px;
        background: #fff;
        z-index: 2;
    }

    .fc-allday-label {
        font-size: 11px;
        color: #6b7280;
        padding: 6px 8px;
        border-right: 1px solid #e5e7eb;
        display: flex;
        align-items: flex-start;
    }

    .fc-allday-cell {
        border-right: 1px solid #e5e7eb;
        padding: 4px;
        min-height: 32px;
        position: relative;
    }

    .fc-allday-cell.fc-today-col {
        background: rgba(250, 230, 170, 0.35);
    }

    /* time grid body */
    .fc-time-body {
        grid-column: 1 / -1;
        display: grid;
        grid-template-columns: 56px repeat(var(--fc-time-cols, 7), 1fr);
        grid-template-rows: repeat(24, 44px);
        position: relative;
    }

    .fc-time-gutter-cell {
        border-right: 1px solid #e5e7eb;
        border-bottom: 1px solid #f3f4f6;
        font-size: 11px;
        color: #9ca3af;
        text-align: right;
        padding: 2px 8px 0 0;
        position: relative;
        top: -6px;
    }

    .fc-time-cell {
        border-right: 1px solid #e5e7eb;
        border-bottom: 1px solid #f3f4f6;
        cursor: pointer;
        transition: background 0.1s;
        position: relative;
    }

    .fc-time-cell:hover {
        background: #f9fafb;
    }

    .fc-time-cell.fc-today-col {
        background: rgba(250, 230, 170, 0.35);
    }

    .fc-time-cell.fc-today-col:hover {
        background: rgba(250, 230, 170, 0.5);
    }

    /* ===== Modal Add/Edit Event ===== */
    .fc-modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(15, 17, 21, 0.45);
        z-index: 1050;
        align-items: center;
        justify-content: center;
        padding: 16px;
    }

    .fc-modal-overlay.open {
        display: flex;
    }

    .fc-modal {
        background: #ffffff;
        border-radius: 16px;
        width: 100%;
        max-width: 460px;
        max-height: 90vh;
        overflow-y: auto;
        padding: 28px;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.2);
    }

    .fc-modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 8px;
    }

    .fc-modal-header h3 {
        font-size: 22px;
        font-weight: 700;
        color: #111827;
        margin: 0;
    }

    .fc-modal-close {
        border: none;
        background: transparent;
        cursor: pointer;
        font-size: 20px;
        color: #6b7280;
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
    }

    .fc-modal-close:hover {
        background: #f3f4f6;
    }

    .fc-modal-desc {
        font-size: 14px;
        color: #6b7280;
        line-height: 1.5;
        margin: 0 0 20px;
    }

    .fc-field {
        margin-bottom: 18px;
    }

    .fc-field label {
        display: block;
        font-size: 14px;
        font-weight: 500;
        color: #374151;
        margin-bottom: 6px;
    }

    .fc-field input[type="text"],
    .fc-field input[type="date"] {
        width: 100%;
        height: 44px;
        padding: 0 14px;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        font-size: 14px;
        color: #111827;
        background: #fff;
    }

    .fc-field input:focus {
        outline: none;
        border-color: #5d87ff;
        box-shadow: 0 0 0 3px rgba(93, 135, 255, 0.15);
    }

    .fc-color-options {
        display: flex;
        gap: 10px;
    }

    .fc-color-dot {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        border: 2px solid transparent;
        cursor: pointer;
        padding: 0;
        position: relative;
    }

    .fc-color-dot.violet {
        background: #534AB7;
    }

    .fc-color-dot.teal {
        background: #1D9E75;
    }

    .fc-color-dot.pink {
        background: #D4537E;
    }

    .fc-color-dot.blue {
        background: #378ADD;
    }

    .fc-color-dot.amber {
        background: #EF9F27;
    }

    .fc-color-dot.active {
        border-color: #111827;
        box-shadow: 0 0 0 2px #fff inset;
    }

    .fc-modal-actions {
        display: flex;
        gap: 10px;
        margin-top: 4px;
    }

    .fc-submit-btn {
        flex: 1;
        height: 46px;
        border: none;
        border-radius: 10px;
        background: #5d87ff;
        color: #fff;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        transition: opacity 0.15s;
    }

    .fc-submit-btn:hover {
        opacity: 0.9;
    }

    .fc-body-wrap {
        transition: opacity .15s ease;
    }

    .fc-body-wrap.fc-is-loading {
        opacity: .45;
        pointer-events: none;
    }

    .fc-delete-btn {
        flex: 0 0 auto;
        height: 46px;
        padding: 0 18px;
        border: 1px solid #fca5a5;
        border-radius: 10px;
        background: #fff;
        color: #dc2626;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.15s;
    }

    .fc-delete-btn:hover {
        background: #fef2f2;
    }

    .fc-field textarea,
    .fc-field input {
        width: 100%;
        box-sizing: border-box;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        padding: 8px 12px;
        font-family: inherit;
        font-size: 14px;
        resize: vertical;
    }

    .fc-field textarea:focus,
    .fc-field input:focus {
        outline: none;
        border-color: #8b5cf6;
        /* samakan dengan warna aksen tema kamu, misal violet */
        box-shadow: 0 0 0 2px rgba(139, 92, 246, 0.15);
    }

    /* Pastikan checkbox TIDAK kena rule width:100% / border ala text input */
    .fc-field input[type="checkbox"],
    .fc-field input[type="radio"] {
        width: auto;
        height: auto;
        padding: 0;
        border: none;
        box-shadow: none;
        accent-color: #8b5cf6;
        /* biar warnanya senada tema kamu */
    }

    /* Rapikan label + checkbox biar sejajar */
    .fc-field label:has(input[type="checkbox"]) {
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 400;
        cursor: pointer;
    }

    .fc-view-detail {
        padding: 4px 0 16px;
    }

    .fc-view-title-row {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 16px;
    }

    .fc-view-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .fc-view-title {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
        color: #1f2937;
        line-height: 1.3;
    }

    .fc-view-meta {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .fc-view-row {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        font-size: 14px;
        color: #4b5563;
    }

    .fc-view-row i {
        font-size: 16px;
        color: #9ca3af;
        margin-top: 1px;
        flex-shrink: 0;
        width: 16px;
        text-align: center;
    }

    .fc-view-desc span {
        white-space: pre-line;
        line-height: 1.5;
    }

    .fc-view-actions {
        display: flex;
        gap: 8px;
        padding-top: 12px;
        border-top: 1px solid #e5e7eb;
    }

    .fc-view-actions .btn {
        flex: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    /* warna dot mengikuti tema warna event yang sudah kamu punya */
    .fc-view-dot.violet {
        background: #8b5cf6;
    }

    .fc-view-dot.teal {
        background: #14b8a6;
    }

    .fc-view-dot.pink {
        background: #ec4899;
    }

    .fc-view-dot.blue {
        background: #3b82f6;
    }

    .fc-view-dot.amber {
        background: #f59e0b;
    }

    .fc-see-all {
        font-size: 13px;
        color: #8b5cf6;
        text-decoration: none;
        font-weight: 500;
    }

    .fc-see-all:hover {
        text-decoration: underline;
    }

    .fc-upcoming-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        padding: 40px 0;
        color: #9ca3af;
    }

    .fc-upcoming-empty i {
        font-size: 32px;
    }

    .fc-upcoming-empty p {
        margin: 0;
        font-size: 14px;
    }

    .fc-upcoming-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .fc-upcoming-item {
        display: flex;
        padding: 12px 14px;
        border-radius: 10px;
        background: #f9fafb;
        border-left: 3px solid transparent;
        transition: background 0.15s ease;
    }

    .fc-upcoming-item:hover {
        background: #f3f4f6;
    }

    .fc-upcoming-content {
        flex: 1;
        min-width: 0;
    }

    .fc-upcoming-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }

    .fc-upcoming-title {
        font-size: 14px;
        font-weight: 600;
        color: #1f2937;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        min-width: 0;
    }

    .fc-upcoming-meta {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 4px;
        font-size: 12px;
        color: #9ca3af;
    }

    .fc-upcoming-meta-item {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        white-space: nowrap;
    }

    .fc-upcoming-meta-item i {
        font-size: 13px;
    }

    .fc-upcoming-badge {
        flex-shrink: 0;
        font-size: 11px;
        font-weight: 600;
        padding: 2px 9px;
        border-radius: 999px;
        white-space: nowrap;
    }

    .fc-upcoming-badge.today {
        background: #ede9fe;
        color: #7c3aed;
    }

    .fc-upcoming-badge.tomorrow {
        background: #dbeafe;
        color: #2563eb;
    }

    /* border kiri berwarna, sesuai tema warna event */
    .fc-upcoming-item.violet {
        border-left-color: #8b5cf6;
    }

    .fc-upcoming-item.teal {
        border-left-color: #14b8a6;
    }

    .fc-upcoming-item.pink {
        border-left-color: #ec4899;
    }

    .fc-upcoming-item.blue {
        border-left-color: #3b82f6;
    }

    .fc-upcoming-item.amber {
        border-left-color: #f59e0b;
    }

    .fc-upcoming-item.is-done {
        background: #f3f4f6;
        opacity: 0.75;
    }

    .fc-upcoming-item.is-done .fc-upcoming-title {
        text-decoration: line-through;
        color: #9ca3af;
    }

    .fc-upcoming-check {
        color: #22c55e;
        font-size: 15px;
        margin-right: 4px;
    }

    .fc-upcoming-badge.done {
        background: #d1fae5;
        color: #059669;
    }

    .fc-upcoming-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    .fc-upcoming-actions {
        display: flex;
        align-items: center;
        gap: 4px;
        transition: opacity 0.15s ease;
        flex-shrink: 0;
    }

    .fc-upcoming-item:hover .fc-upcoming-actions {
        opacity: 1;
    }

    .fc-upcoming-action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        border-radius: 8px;
        border: none;
        background: transparent;
        color: #6b7280;
        cursor: pointer;
        transition: background 0.15s ease, color 0.15s ease;
        text-decoration: none;
    }

    .fc-upcoming-action-btn.success:hover {
        background: #ddfbec;
        color: #63bfa2;
    }

    .fc-upcoming-action-btn.danger {
        color: #dc2626;
    }

    .fc-upcoming-action-btn.danger:hover {
        background: #fee2e2;
        color: #dc2626;
    }


    .fc-upcoming-action-btn.warning:hover {
        background: #fef3c7;
        color: #d97706;
    }

    .fc-upcoming-action-btn.success:hover {
        background: #d1fae5;
        color: #059669;
    }

    .fc-upcoming-scroll {
        max-height: 360px;
        overflow-y: auto;
        padding-right: 6px;
    }

    .fc-upcoming-scroll::-webkit-scrollbar {
        width: 6px;
    }

    .fc-upcoming-scroll::-webkit-scrollbar-track {
        background: transparent;
    }

    .fc-upcoming-scroll::-webkit-scrollbar-thumb {
        background-color: #d1d5db;
        border-radius: 999px;
    }

    .fc-upcoming-scroll::-webkit-scrollbar-thumb:hover {
        background-color: #9ca3af;
    }

    .fc-upcoming-scroll {
        scrollbar-width: thin;
        scrollbar-color: #d1d5db transparent;
    }
</style>