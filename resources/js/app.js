import './bootstrap';

// Marketing pages: staggered scroll-reveal for elements marked .mkt-reveal.
// Vanilla IntersectionObserver, no library — keeps the marketing layout
// dependency-free per the "no Livewire/no framework on these pages"
// decision made when the site was first built.
document.addEventListener('DOMContentLoaded', () => {
    const targets = document.querySelectorAll('.mkt-reveal');
    if (!targets.length) return;

    if (!('IntersectionObserver' in window)) {
        targets.forEach((el) => el.classList.add('mkt-in'));
        return;
    }

    const io = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                const el = entry.target;
                const delay = Number(el.dataset.revealDelay || 0);
                setTimeout(() => el.classList.add('mkt-in'), delay);
                io.unobserve(el);
            });
        },
        { threshold: 0.15 }
    );

    targets.forEach((el) => io.observe(el));
});
