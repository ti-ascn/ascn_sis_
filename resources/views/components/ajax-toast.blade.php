<div id="ajax-toast-container"
    class="fixed bottom-6 left-1/2 -translate-x-1/2 z-[9999] flex flex-col-reverse gap-2 items-center pointer-events-none w-full max-w-sm px-4">
</div>

<script>
    window.showAjaxToast = function(message, type = 'success') {
        const container = document.getElementById('ajax-toast-container');
        if (!container) return;

        // Create new toast element
        const toast = document.createElement('div');

        // Select styles based on the type
        let textColorClass = '';
        let iconColorClass = '';
        let iconClass = '';
        let closeBtnClass = '';

        switch (type) {
            case 'create':
            case 'success':
                textColorClass = 'text-green-600 dark:text-green-400';
                iconColorClass = 'text-green-500 dark:text-green-400';
                iconClass = 'bi-check-circle';
                closeBtnClass =
                    'text-green-400 hover:text-green-600 dark:hover:text-green-300 hover:bg-green-50 dark:hover:bg-green-950/20';
                break;
            case 'edit':
                textColorClass = 'text-yellow-600 dark:text-yellow-400';
                iconColorClass = 'text-yellow-500 dark:text-yellow-400';
                iconClass = 'bi bi-pencil';
                closeBtnClass =
                    'text-yellow-400 hover:text-yellow-600 dark:hover:text-yellow-300 hover:bg-yellow-50 dark:hover:bg-yellow-950/20';
                break;
            case 'warning':
                textColorClass = 'text-yellow-600 dark:text-yellow-400';
                iconColorClass = 'text-yellow-500 dark:text-yellow-400';
                iconClass = 'bi-exclamation-circle';
                closeBtnClass =
                    'text-yellow-400 hover:text-yellow-600 dark:hover:text-yellow-300 hover:bg-yellow-50 dark:hover:bg-yellow-950/20';
                break;
            case 'delete':
            case 'danger':
            case 'error':
                textColorClass = 'text-red-600 dark:text-red-400';
                iconColorClass = 'text-red-500 dark:text-red-400';
                iconClass = 'bi-x-circle';
                closeBtnClass =
                    'text-red-400 hover:text-red-600 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-950/20';
                break;
            case 'info':
                textColorClass = 'text-sky-600 dark:text-sky-400';
                iconColorClass = 'text-sky-500 dark:text-sky-400';
                iconClass = 'bi-info-circle';
                closeBtnClass =
                    'text-sky-400 hover:text-sky-600 dark:hover:text-sky-300 hover:bg-sky-50 dark:hover:bg-sky-950/20';
                break;
            default:
                textColorClass = 'text-zinc-600 dark:text-zinc-300';
                iconColorClass = 'text-zinc-500 dark:text-zinc-400';
                iconClass = 'bi-bell';
                closeBtnClass =
                    'text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800/50';
        }

        toast.className =
            'pointer-events-auto flex items-center gap-3 w-full p-3 px-5 bg-white/95 dark:bg-zinc-950/95 backdrop-blur-md border border-zinc-200/80 dark:border-zinc-800/80 rounded-full shadow-[0_12px_40px_rgba(0,0,0,0.06)] dark:shadow-[0_12px_40px_rgba(0,0,0,0.3)] transition-all duration-300 ease-out transform translate-y-4 scale-95 opacity-0';

        toast.innerHTML = `
            <!-- Icon -->
            <div class="shrink-0 flex items-center justify-center">
                <i class="bi ${iconClass} ${iconColorClass} text-lg leading-none"></i>
            </div>
            <!-- Message -->
            <div class="flex-1 text-sm font-semibold pr-1 leading-snug ${textColorClass}">
                ${message}
            </div>
            <!-- Close Button -->
            <button type="button" class="shrink-0 transition-colors p-1 rounded-full ${closeBtnClass}">
                <i class="bi bi-x text-lg leading-none"></i>
            </button>
        `;

        // Add to container
        container.appendChild(toast);

        // Animate entrance
        requestAnimationFrame(() => {
            setTimeout(() => {
                toast.classList.remove('opacity-0', 'translate-y-4', 'scale-95');
                toast.classList.add('opacity-100', 'translate-y-0', 'scale-100');
            }, 10);
        });

        // Setup dismissal triggers
        let dismissTimeout = setTimeout(() => dismiss(), 4000);

        function dismiss() {
            clearTimeout(dismissTimeout);
            toast.classList.remove('opacity-100', 'translate-y-0', 'scale-100');
            toast.classList.add('opacity-0', 'translate-y-4', 'scale-95');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }

        // Close button click listener
        toast.querySelector('button').addEventListener('click', dismiss);
    };

    // Syntactic sugar helper methods for style calls
    window.showAjaxToast.success = (message) => window.showAjaxToast(message, 'success');
    window.showAjaxToast.error = (message) => window.showAjaxToast(message, 'error');
    window.showAjaxToast.danger = (message) => window.showAjaxToast(message, 'danger');
    window.showAjaxToast.warning = (message) => window.showAjaxToast(message, 'warning');
    window.showAjaxToast.info = (message) => window.showAjaxToast(message, 'info');
    window.showAjaxToast.create = (message) => window.showAjaxToast(message, 'create');
    window.showAjaxToast.edit = (message) => window.showAjaxToast(message, 'edit');
    window.showAjaxToast.delete = (message) => window.showAjaxToast(message, 'delete');
</script>
