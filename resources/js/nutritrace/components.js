import { prefersReducedMotion } from './tokens';

const FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled]):not([type="hidden"]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

export default function registerComponents(Alpine) {
    // Modal with focus trap. Open with $dispatch('open-modal', 'name'), close with Esc, backdrop or $dispatch('close-modal').
    Alpine.data('ntModal', (name, show = false) => ({
        name,
        open: show,
        returnFocus: null,

        init() {
            this.$watch('open', (value) => {
                document.body.classList.toggle('overflow-hidden', value);

                if (value) {
                    this.returnFocus = document.activeElement;
                    this.$nextTick(() => this.focusables()[0]?.focus());
                } else {
                    this.returnFocus?.focus?.();
                }
            });

            if (show) {
                this.$nextTick(() => this.focusables()[0]?.focus());
            }
        },

        focusables() {
            return [...this.$refs.panel.querySelectorAll(FOCUSABLE)].filter((el) => el.offsetParent !== null);
        },

        trap(event) {
            const items = this.focusables();

            if (!items.length) return;

            const first = items[0];
            const last = items[items.length - 1];

            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        },

        openIf(detail) {
            if (detail === this.name || detail?.name === this.name) {
                this.open = true;
            }
        },
    }));

    // Counts from 0 to `target` once the element becomes visible.
    Alpine.data('ntCounter', (target, decimals = 0) => ({
        value: 0,
        target,

        get display() {
            return this.value.toLocaleString('fr-FR', { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
        },

        init() {
            if (prefersReducedMotion() || !('IntersectionObserver' in window)) {
                this.value = target;
                return;
            }

            const observer = new IntersectionObserver(([entry]) => {
                if (!entry.isIntersecting) return;

                observer.disconnect();
                const start = performance.now();
                const duration = 1600;
                const tick = (now) => {
                    const progress = Math.min((now - start) / duration, 1);
                    const eased = 1 - Math.pow(1 - progress, 3);
                    this.value = Number((target * eased).toFixed(decimals));
                    if (progress < 1) requestAnimationFrame(tick);
                };
                requestAnimationFrame(tick);
            }, { threshold: 0.4 });

            observer.observe(this.$el);
        },
    }));

    // Keyboard-accessible star rating input (radio-group semantics).
    Alpine.data('ntRatingInput', (value = 0) => ({
        value: Number(value) || 0,
        hover: 0,

        get shown() {
            return this.hover || this.value;
        },

        select(star) {
            this.value = star;
        },

        onKey(event) {
            const keys = { ArrowRight: 1, ArrowUp: 1, ArrowLeft: -1, ArrowDown: -1 };

            if (event.key in keys) {
                event.preventDefault();
                this.value = Math.min(5, Math.max(1, (this.value || 0) + keys[event.key]));
                this.$nextTick(() => this.$root.querySelector(`[data-star="${this.value}"]`)?.focus());
            }
        },
    }));

    // Drag & drop file zone, keeps the native <input type="file"> as the real field.
    Alpine.data('ntFileDrop', () => ({
        dragging: false,
        files: [],

        sync() {
            this.files = [...this.$refs.input.files].map((file) => ({
                name: file.name,
                size: file.size < 1048576 ? `${Math.round(file.size / 1024)} Ko` : `${(file.size / 1048576).toFixed(1)} Mo`,
            }));
        },

        drop(event) {
            this.dragging = false;
            this.$refs.input.files = event.dataTransfer.files;
            this.sync();
        },

        clear() {
            this.$refs.input.value = '';
            this.files = [];
        },
    }));

    // Live eco-score preview for the admin impact form. Same thresholds as App\Support\EcoScore.
    Alpine.data('ntEcoPreview', (initial) => ({
        co2: initial.co2 ?? 0,
        water: initial.water ?? 0,
        distance: initial.distance ?? 0,
        packaging: initial.packaging ?? 'recyclable',
        seasonal: initial.seasonal ?? true,

        get points() {
            let points = 100;
            points -= Math.min(40, Number(this.co2) * 6);
            points -= Math.min(20, Number(this.water) / 150);
            points -= Math.min(20, Number(this.distance) / 100);
            points -= { compostable: 0, recyclable: 5, mixed: 12, plastic: 18 }[this.packaging] ?? 10;
            points += this.seasonal ? 5 : -5;

            return Math.max(0, Math.min(100, Math.round(points)));
        },

        get grade() {
            const p = this.points;
            if (p >= 80) return 'A';
            if (p >= 65) return 'B';
            if (p >= 50) return 'C';
            if (p >= 35) return 'D';
            return 'E';
        },
    }));

    // Toast stack: flash messages from the session + window 'nt-toast' events.
    Alpine.data('ntToasts', (initial = []) => ({
        toasts: [],

        init() {
            initial.forEach((toast) => this.push(toast));
        },

        push(toast) {
            const id = Date.now() + Math.random();
            this.toasts.push({ id, type: toast.type ?? 'success', message: toast.message });
            setTimeout(() => this.remove(id), 5000);
        },

        remove(id) {
            this.toasts = this.toasts.filter((toast) => toast.id !== id);
        },
    }));
}
