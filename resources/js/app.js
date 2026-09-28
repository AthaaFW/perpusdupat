import Alpine from 'alpinejs';

window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
    Alpine.store('toast', {
        show: false,
        type: 'success', // 'success' | 'error' | 'warning' | 'info'
        message: '',
        notify(message, type = 'success') {
            this.message = message;
            this.type = type;
            this.show = true;
            setTimeout(() => {
                this.show = false;
            }, 4500);
        },
        close() {
            this.show = false;
        }
    });
});

Alpine.start();
