{{-- Notifications Bell Dropdown --}}
<div class="relative"
     x-data="{
        bellOpen: false,
        unread: 0,
        items: [],
        loaded: false,
        async loadLatest() {
            try {
                const res = await fetch('{{ route('admin.notifications.latest') }}', { headers: { 'Accept': 'application/json' } });
                const json = await res.json();
                this.unread = json.unread_count ?? 0;
                this.items = json.items ?? [];
                this.loaded = true;
            } catch (e) { console.error('Notification fetch failed', e); }
        },
        init() {
            this.loadLatest();
            setInterval(() => this.loadLatest(), 60000);
        }
     }"
     x-init="init()"
     @click.outside="bellOpen = false">

    <button @click="bellOpen = !bellOpen; if (bellOpen) loadLatest()"
            class="hover:text-dark-900 relative flex h-11 w-11 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white">
        <i class="bi bi-bell text-lg"></i>
        <span x-show="unread > 0"
              x-text="unread > 99 ? '99+' : unread"
              class="absolute -top-0.5 -right-0.5 inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-bold leading-none text-white bg-red-500 rounded-full"
              style="display: none"></span>
    </button>

    <div x-show="bellOpen"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="absolute right-0 mt-3 w-[360px] max-h-[480px] flex flex-col rounded-2xl border border-gray-200 bg-white shadow-theme-lg dark:border-gray-800 dark:bg-slate-800 z-100"
         style="display: none">

        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-gray-700">
            <h4 class="text-sm font-semibold text-gray-800 dark:text-white/90">{{ __('Notifications') }}</h4>
            <a href="{{ route('admin.notifications.index') }}" class="text-xs text-blue-600 hover:underline">{{ __('See all') }}</a>
        </div>

        <div class="flex-1 overflow-y-auto">
            <template x-if="loaded && items.length === 0">
                <div class="px-4 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                    <i class="bi bi-bell-slash text-3xl block mb-2 text-gray-300 dark:text-gray-700"></i>
                    {{ __('No notifications yet.') }}
                </div>
            </template>

            <template x-for="item in items" :key="item.id">
                <a :href="'{{ url('/admin/notifications') }}/' + item.id + '/read'"
                   class="flex items-start gap-3 px-4 py-3 border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-white/[0.03] transition-colors"
                   :class="!item.read ? 'bg-blue-50/30 dark:bg-blue-900/10' : ''">
                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center">
                        <i class="bi text-blue-600 dark:text-blue-400 text-sm" :class="item.icon"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white/90 truncate" x-text="item.title"></p>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5 line-clamp-2" x-text="item.message"></p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1" x-text="item.ago"></p>
                    </div>
                    <span x-show="!item.read" class="flex-shrink-0 w-2 h-2 bg-blue-500 rounded-full mt-2"></span>
                </a>
            </template>
        </div>

        <form x-show="unread > 0"
              action="{{ route('admin.notifications.mark-all-read') }}"
              method="POST"
              class="px-4 py-2 border-t border-gray-200 dark:border-gray-700"
              style="display: none">
            @csrf
            <button type="submit" class="w-full text-xs text-center text-blue-600 hover:text-blue-800 py-1">
                <i class="bi bi-check2-all mr-1"></i> {{ __('Mark all as read') }}
            </button>
        </form>
    </div>
</div>
