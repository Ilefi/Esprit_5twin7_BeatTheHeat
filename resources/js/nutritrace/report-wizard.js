// 4-step report wizard (front/reports/create). Server-side validation stays authoritative;
// on a validation error the controller tells us which step to reopen.
export default function registerWizard(Alpine) {
    Alpine.data('ntReportWizard', (options) => ({
        step: options.step ?? 1,
        total: 4,
        type: options.type ?? '',
        targetType: options.targetType ?? 'product',
        targetId: options.targetId ?? '',
        search: '',
        description: options.description ?? '',
        links: options.links ?? '',
        files: [],
        consent: false,
        types: options.types,
        targets: options.targets,

        get progress() {
            return Math.round((this.step / this.total) * 100);
        },

        get typeLabel() {
            return this.types.find((t) => t.value === this.type)?.label ?? '—';
        },

        get filteredTargets() {
            const list = this.targets[this.targetType] ?? [];
            const query = this.search.trim().toLowerCase();

            return query ? list.filter((t) => t.name.toLowerCase().includes(query)) : list;
        },

        get targetLabel() {
            const list = this.targets[this.targetType] ?? [];

            return list.find((t) => String(t.id) === String(this.targetId))?.name ?? '—';
        },

        canContinue() {
            if (this.step === 1) return this.type !== '';
            if (this.step === 2) return this.targetId !== '';
            if (this.step === 3) return this.description.trim().length >= 30;
            return this.consent;
        },

        next() {
            if (this.canContinue() && this.step < this.total) {
                this.step++;
                this.focusStep();
            }
        },

        prev() {
            if (this.step > 1) {
                this.step--;
                this.focusStep();
            }
        },

        goTo(step) {
            if (step < this.step) {
                this.step = step;
                this.focusStep();
            }
        },

        setTargetType(type) {
            this.targetType = type;
            this.targetId = '';
            this.search = '';
        },

        focusStep() {
            this.$nextTick(() => this.$root.querySelector(`[data-step="${this.step}"] h2`)?.focus());
        },
    }));
}
