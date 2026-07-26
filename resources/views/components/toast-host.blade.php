{{--
    Frontend toast notifications, in the forge house style. A single host lives
    in the app layout and shows a stack of auto-dismissing toasts.

    Fire one from any Livewire component:
        $this->dispatch('mblan-notify', message: 'Klaar', type: 'success');
    (type: success | info | warning). It also catches the existing Jetstream
    "saved" event and the "login-required" event.
--}}
<div
    x-data="{
        toasts: [],
        push(detail) {
            detail = detail || {};
            const id = Date.now() + Math.random();
            this.toasts.push({
                id,
                message: detail.message || 'Klaar',
                type: detail.type || 'success',
            });
            const ttl = detail.duration || 4000;
            setTimeout(() => this.dismiss(id), ttl);
        },
        dismiss(id) {
            this.toasts = this.toasts.filter((t) => t.id !== id);
        },
    }"
    x-on:mblan-notify.window="push($event.detail)"
    x-on:saved.window="push({ message: 'Opgeslagen', type: 'success' })"
    x-on:login-required.window="push({ message: 'Log in om dit te doen', type: 'info' })"
    class="pointer-events-none fixed inset-x-0 top-4 z-[70] flex flex-col items-center gap-2 px-4 sm:items-end sm:pr-6"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 -translate-y-2 sm:translate-x-4"
            x-transition:enter-end="opacity-100 translate-y-0 sm:translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            x-on:click="dismiss(toast.id)"
            class="pointer-events-auto clip-corner metal-edge flex w-full max-w-sm cursor-pointer items-start gap-3 border-l-4 bg-forge-graphite/95 px-4 py-3 shadow-lg backdrop-blur"
            :class="{
                'border-primary-500': toast.type === 'success',
                'border-warning-400': toast.type === 'warning' || toast.type === 'error',
                'border-forge-steel': toast.type === 'info',
            }"
        >
            <span
                class="mt-0.5 font-pixel text-[9px] uppercase tracking-widest"
                :class="{
                    'text-primary-300': toast.type === 'success',
                    'text-warning-400': toast.type === 'warning' || toast.type === 'error',
                    'text-forge-steel': toast.type === 'info',
                }"
                x-text="toast.type === 'success' ? 'OK' : (toast.type === 'info' ? 'i' : '!')"
            ></span>
            <p class="text-sm text-forge-steel" x-text="toast.message"></p>
        </div>
    </template>
</div>
