@php $user = Auth::user(); @endphp
<nav
    x-data="{
        isDark: document.documentElement.classList.contains('dark'),
        textColor: '',
        submenus: {
            'roles-submenu': {{ Route::is('admin.roles.*') ? 'true' : 'false' }},
            'users-submenu': {{ Route::is('admin.users.*') ? 'true' : 'false' }},
            'monitoring-submenu': {{ Route::is('actionlog.*') ? 'true' : 'false' }},
            'settings-submenu': {{ Route::is('admin.settings.*') || Route::is('admin.translations.*') ? 'true' : 'false' }},
            'crm-submenu': {{ Route::is('admin.crm.*') ? 'true' : 'false' }}
        },
        toggleSubmenu(id) {
            this.submenus[id] = !this.submenus[id];
        },
        init() {
            this.updateColor();
            const observer = new MutationObserver(() => this.updateColor());
            observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
        },
        updateColor() {
            this.isDark = document.documentElement.classList.contains('dark');
            this.textColor = this.isDark 
                ? '{{ config('settings.sidebar_text_dark') }}' 
                : '{{ config('settings.sidebar_text_lite') }}';
        }
    }"
    x-init="init()"
    class="transition-all duration-300 ease-in-out"
>
    <div>
        <h3 class="mb-4 text-xs uppercase leading-[20px] text-gray-400 px-5">
            {{ __('Menu') }}
        </h3>

        <ul class="flex flex-col mb-6">
            @if ($user->can('dashboard.view'))
                <li class="hover:menu-item-active">
                    <a :style="`color: ${textColor}`" href="{{ route('admin.dashboard') }}"
                        class="menu-item group {{ Route::is('admin.dashboard') ? 'menu-item-active' : 'menu-item-inactive' }}">
                        <img src="{{ asset('images/icons/dashboard.svg') }}" alt="Dashboard" class="menu-item-icon dark:invert">
                        <span class="menu-item-text">{{ __('Dashboard') }}</span>
                    </a>
                </li>
            @endif
            @php echo ld_apply_filters('sidebar_menu_after_dashboard', '') @endphp

            @if ($user->can('role.create') || $user->can('role.view') || $user->can('role.edit') || $user->can('role.delete'))
                <li x-data class="hover:menu-item-active">
                    <button :style="`color: ${textColor}`"
                        class="menu-item group w-full text-left {{ Route::is('admin.roles.*') ? 'menu-item-active' : 'menu-item-inactive' }}"
                        type="button" @click="toggleSubmenu('roles-submenu')">
                        <img src="{{ asset('images/icons/key.svg') }}" alt="Roles Icon" class="menu-item-icon dark:invert">
                        <span class="menu-item-text" :style="`color: ${textColor}`"> {{ __('Roles & Permissions') }}</span>
                        <img src="{{ asset('images/icons/chevron-down.svg') }}" alt="Arrow" class="menu-item-arrow dark:invert transition-transform duration-300" :class="submenus['roles-submenu'] ? 'rotate-180' : ''">
                    </button>
                    <ul id="roles-submenu"
                        x-show="submenus['roles-submenu']"
                        x-transition:enter="transition-all ease-in-out duration-300"
                        x-transition:enter-start="opacity-0 max-h-0"
                        x-transition:enter-end="opacity-100 max-h-[500px]"
                        x-transition:leave="transition-all ease-in-out duration-300"
                        x-transition:leave-start="opacity-100 max-h-[500px]"
                        x-transition:leave-end="opacity-0 max-h-0"
                        class="submenu pl-12 mt-2 space-y-2 overflow-hidden">
                        @if ($user->can('role.view'))
                            <li>
                                <a :style="`color: ${textColor}`" href="{{ route('admin.roles.index') }}"
                                    class="hover:menu-item-active block px-4 py-2 rounded-lg {{ Route::is('admin.roles.index') || Route::is('admin.roles.edit') ? 'menu-item-active' : 'menu-item-inactive' }}">
                                    {{ __('Roles') }}
                                </a>
                            </li>
                        @endif
                        @if ($user->can('role.create'))
                            <li>
                                <a :style="`color: ${textColor}`" href="{{ route('admin.roles.create') }}"
                                    class="hover:menu-item-active block px-4 py-2 rounded-lg {{ Route::is('admin.roles.create') ? 'menu-item-active' : 'menu-item-inactive' }}">
                                    {{ __('New Role') }}
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif
            @php echo ld_apply_filters('sidebar_menu_after_roles', '') @endphp

            @if ($user->can('user.create') || $user->can('user.view') || $user->can('user.edit') || $user->can('user.delete'))
                <li x-data class="hover:menu-item-active">
                    <button :style="`color: ${textColor}`"
                        class="menu-item group w-full text-left {{ Route::is('admin.users.*') ? 'menu-item-active' : 'menu-item-inactive' }}"
                        type="button" @click="toggleSubmenu('users-submenu')">
                        <img src="{{ asset('images/icons/user.svg') }}" alt="Roles Icon" class="menu-item-icon dark:invert">
                        <span class="menu-item-text">{{ __('User') }}</span>
                        <img src="{{ asset('images/icons/chevron-down.svg') }}" alt="Arrow" class="menu-item-arrow dark:invert transition-transform duration-300" :class="submenus['users-submenu'] ? 'rotate-180' : ''">
                    </button>
                    <ul id="users-submenu"
                        x-show="submenus['users-submenu']"
                        x-transition:enter="transition-all ease-in-out duration-300"
                        x-transition:enter-start="opacity-0 max-h-0"
                        x-transition:enter-end="opacity-100 max-h-[500px]"
                        x-transition:leave="transition-all ease-in-out duration-300"
                        x-transition:leave-start="opacity-100 max-h-[500px]"
                        x-transition:leave-end="opacity-0 max-h-0"
                        class="submenu pl-12 mt-2 space-y-2 overflow-hidden">
                        @if ($user->can('user.view'))
                            <li>
                                <a :style="`color: ${textColor}`" href="{{ route('admin.users.index') }}"
                                    class="hover:menu-item-active block px-4 py-2 rounded-lg {{ Route::is('admin.users.index') || Route::is('admin.users.edit') ? 'menu-item-active' : 'menu-item-inactive' }}">
                                    {{ __('Users') }}
                                </a>
                            </li>
                        @endif
                        @if ($user->can('user.create'))
                            <li>
                                <a :style="`color: ${textColor}`" href="{{ route('admin.users.create') }}"
                                    class="hover:menu-item-active block px-4 py-2 rounded-lg {{ Route::is('admin.users.create') ? 'menu-item-active' : 'menu-item-inactive' }}">
                                    {{ __('New User') }}
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif

            <!-- @php echo ld_apply_filters('sidebar_menu_after_users', '') @endphp
            @if ($user->can('module.view'))
                <li class="hover:menu-item-active">
                    <a :style="`color: ${textColor}`" href="{{ route('admin.modules.index') }}"
                        class="menu-item group {{ Route::is('admin.modules.index') ? 'menu-item-active' : 'menu-item-inactive' }}">
                        <img src="{{ asset('images/icons/three-dice.svg') }}" alt="Roles Icon" class="menu-item-icon dark:invert">
                        <span class="menu-item-text">{{ __('Modules') }}</span>
                    </a>
                </li>
            @endif
            @php echo ld_apply_filters('sidebar_menu_after_modules', '') @endphp -->
            
            <!-- test -->
            <?php 
            // dd($user->getPermissionsViaRoles()->toArray());
            ?>    
            
            @if ($user->can('sale-orders.index'))
                <li class="hover:menu-item-active">
                    <a :style="`color: ${textColor}`" href="{{ route('admin.sale-orders.index') }}"
                        class="menu-item group {{ Route::is('admin.sale-orders.index') ? 'menu-item-active' : 'menu-item-inactive' }}">
                        <img src="{{ asset('images/icons/three-dice.svg') }}" alt="Roles Icon" class="menu-item-icon dark:invert">
                        <span class="menu-item-text">{{ __('Sales Order') }}</span>
                    </a>
                </li>
            @endif
            @php echo ld_apply_filters('sidebar_menu_after_modules', '') @endphp
            <!-- test -->

            @if ($user->can('pulse.view') || $user->can('actionlog.view'))
                <li x-data class="hover:menu-item-active">
                    <button :style="`color: ${textColor}`"
                        class="menu-item group w-full text-left {{ Route::is('actionlog.*') ? 'menu-item-active' : 'menu-item-inactive' }}"
                        type="button" @click="toggleSubmenu('monitoring-submenu')">
                        <img src="{{ asset('images/icons/tv.svg') }}" alt="Roles Icon" class="menu-item-icon dark:invert">
                        <span class="menu-item-text">{{ __('Monitoring') }}</span>
                        <img src="{{ asset('images/icons/chevron-down.svg') }}" alt="Arrow" class="menu-item-arrow dark:invert transition-transform duration-300" :class="submenus['monitoring-submenu'] ? 'rotate-180' : ''">
                    </button>
                    <ul id="monitoring-submenu"
                        x-show="submenus['monitoring-submenu']"
                        x-transition:enter="transition-all ease-in-out duration-300"
                        x-transition:enter-start="opacity-0 max-h-0"
                        x-transition:enter-end="opacity-100 max-h-[500px]"
                        x-transition:leave="transition-all ease-in-out duration-300"
                        x-transition:leave-start="opacity-100 max-h-[500px]"
                        x-transition:leave-end="opacity-0 max-h-0"
                        class="submenu pl-12 mt-2 space-y-2 overflow-hidden">
                        @if ($user->can('actionlog.view'))
                            <li>
                                <a href="{{ route('actionlog.index') }}"
                                    class="hover:menu-item-active block px-4 py-2 rounded-lg {{ Route::is('actionlog.index') ? 'menu-item-active' : 'menu-item-inactive text-white' }}">
                                    <span :style="`color: ${textColor}`">{{ __('Action Logs') }}</span>
                                </a>
                            </li>
                        @endif

                        @if ($user->can('pulse.view'))
                            <li>
                                <a href="{{ route('pulse') }}" class="hover:menu-item-active block px-4 py-2 rounded-lg menu-item-inactive"
                                    target="_blank">
                                    <span :style="`color: ${textColor}`">{{ __('Laravel Pulse') }}</span>
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif
            @php echo ld_apply_filters('sidebar_menu_after_monitoring', '') @endphp
        </ul>
    </div>

    <!-- Others Group -->
    <div>
        <h3 class="mb-4 text-xs uppercase leading-[20px] text-gray-400 px-5">
            {{ __('More') }}
        </h3>

        <ul class="flex flex-col mb-6">
            @if ($user->can('settings.edit') || $user->can('translations.view'))
                <li x-data class="hover:menu-item-active">
                    <button :style="`color: ${textColor}`"
                        class="menu-item group w-full text-left {{ Route::is('admin.settings.*') || Route::is('admin.translations.*') ? 'menu-item-active' : 'menu-item-inactive' }}"
                        type="button" @click="toggleSubmenu('settings-submenu')">
                        <img src="{{ asset('images/icons/settings.svg') }}" alt="Roles Icon" class="menu-item-icon dark:invert">
                        <span class="menu-item-text">{{ __('Settings') }}</span>
                        <img src="{{ asset('images/icons/chevron-down.svg') }}" alt="Arrow" class="menu-item-arrow dark:invert transition-transform duration-300" :class="submenus['settings-submenu'] ? 'rotate-180' : ''">
                    </button>
                    <ul id="settings-submenu"
                        x-show="submenus['settings-submenu']"
                        x-transition:enter="transition-all ease-in-out duration-300"
                        x-transition:enter-start="opacity-0 max-h-0"
                        x-transition:enter-end="opacity-100 max-h-[500px]"
                        x-transition:leave="transition-all ease-in-out duration-300"
                        x-transition:leave-start="opacity-100 max-h-[500px]"
                        x-transition:leave-end="opacity-0 max-h-0"
                        class="submenu pl-12 mt-2 space-y-2 overflow-hidden">
                        @if ($user->can('settings.edit'))
                            <li>
                                <a :style="`color: ${textColor}`" href="{{ route('admin.settings.index') }}"
                                    class="hover:menu-item-active block px-4 py-2 rounded-lg {{ Route::is('admin.settings.index') ? 'menu-item-active' : 'menu-item-inactive' }}">
                                    {{ __('General Settings') }}
                                </a>
                            </li>
                        @endif
                        @canany(['translations.view', 'translations.edit'])
                            <li>
                                <a :style="`color: ${textColor}`" href="{{ route('admin.translations.index') }}"
                                    class="hover:menu-item-active block px-4 py-2 rounded-lg {{ Route::is('admin.translations.*') ? 'menu-item-active' : 'menu-item-inactive' }}">
                                    {{ __('Translations') }}
                                </a>
                            </li>
                        @endcanany
                    </ul>
                </li>
            @endif

            <!-- Logout Menu Item -->
            <li class="hover:menu-item-active">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button :style="`color: ${textColor}`" type="submit"
                        class="menu-item group w-full text-left menu-item-inactive">
                        <img src="{{ asset('images/icons/logout.svg') }}" alt="Roles Icon" class="menu-item-icon dark:invert">
                        <span class="menu-item-text">{{ __('Logout') }}</span>
                    </button>
                </form>
            </li>
        </ul>
    </div>
</nav>
