// ── Toast notifications ──────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('toast-container');
    if (!container) return;

    function showToast(message) {
        const el = document.createElement('div');
        el.className = [
            'toast-in pointer-events-auto',
            'flex items-center gap-3 px-4 py-3',
            'rounded-2xl bg-[#1A1A1A] text-white text-sm font-medium',
            'shadow-float w-full',
        ].join(' ');
        el.textContent = message;
        container.appendChild(el);

        setTimeout(() => {
            el.style.transition = 'opacity 0.2s ease, transform 0.2s ease';
            el.style.opacity = '0';
            el.style.transform = 'translateY(6px)';
            setTimeout(() => el.remove(), 220);
        }, 3500);
    }

    // Livewire 4 named-param dispatch → detail is an object {message: '...'}
    // Livewire 4 positional dispatch  → detail is an array  [{message: '...'}] or [value]
    window.addEventListener('toast', (e) => {
        const d = e.detail;
        const msg = d?.message ?? d?.[0]?.message ?? (typeof d?.[0] === 'string' ? d[0] : null);
        if (msg) showToast(msg);
    });
});
