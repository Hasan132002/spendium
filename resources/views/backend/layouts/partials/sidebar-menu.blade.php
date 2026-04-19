@php
    $user = Auth::user();
    $canFinance = $user->can('family.budget.view')
        || $user->can('personal.expense.manage')
        || $user->can('family.expense.view')
        || $user->can('personal.income.manage')
        || $user->can('family.income.view')
        || $user->can('personal.fund_request.create')
        || $user->can('family.fund_request.view')
        || $user->can('personal.savings.manage');
    $canLoans = $user->can('family.loan.manage') || $user->can('personal.loan.contribute');
    $canSocial = $user->can('personal.post.manage') || $user->can('personal.profile.view');
    $canGoals = $user->can('family.goal.view') || $user->can('personal.goal.manage');
    $canFamilyMgmt = $user->can('family.member.invite') || $user->can('family.member.edit') || $user->can('family.member.remove');
@endphp

<nav
    x-data="{
        isDark: document.documentElement.classList.contains('dark'),
        textColor: '',
        submenus: {
            'roles-submenu': {{ Route::is('admin.roles.*') ? 'true' : 'false' }},
            'users-submenu': {{ Route::is('admin.users.*') ? 'true' : 'false' }},
            'monitoring-submenu': {{ Route::is('actionlog.*') ? 'true' : 'false' }},
            'settings-submenu': {{ Route::is('admin.settings.*') || Route::is('admin.translations.*') ? 'true' : 'false' }},
            'finance-submenu': false,
            'loans-submenu': false,
            'goals-submenu': false,
            'social-submenu': false
        },
        toggleSubmenu(id) { this.submenus[id] = !this.submenus[id]; },
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

            {{-- Dashboard --}}
            @can('dashboard.view')
                <li class="hover:menu-item-active">
                    <a :style="`color: ${textColor}`" href="{{ route('admin.dashboard') }}"
                        class="menu-item group {{ Route::is('admin.dashboard') ? 'menu-item-active' : 'menu-item-inactive' }}">
                        <img src="{{ asset('images/icons/dashboard.svg') }}" alt="Dashboard" class="menu-item-icon dark:invert">
                        <span class="menu-item-text">{{ __('Dashboard') }}</span>
                    </a>
                </li>
            @endcan

            @php echo ld_apply_filters('sidebar_menu_after_dashboard', '') @endphp

            {{-- Finance & Budget --}}
            @if ($canFinance)
                <li x-data class="hover:menu-item-active">
                    <button :style="`color: ${textColor}`"
                        class="menu-item group w-full text-left"
                        type="button" @click="toggleSubmenu('finance-submenu')">
                        <img src="{{ asset('images/icons/tv.svg') }}" alt="Finance Icon" class="menu-item-icon dark:invert">
                        <span class="menu-item-text">{{ __('Finance & Budget') }}</span>
                        <img src="{{ asset('images/icons/chevron-down.svg') }}" alt="Arrow" class="menu-item-arrow dark:invert transition-transform duration-300" :class="submenus['finance-submenu'] ? 'rotate-180' : ''">
                    </button>
                    <ul id="finance-submenu"
                        x-show="submenus['finance-submenu']"
                        x-transition:enter="transition-all ease-in-out duration-300"
                        x-transition:enter-start="opacity-0 max-h-0"
                        x-transition:enter-end="opacity-100 max-h-[600px]"
                        x-transition:leave="transition-all ease-in-out duration-300"
                        x-transition:leave-start="opacity-100 max-h-[600px]"
                        x-transition:leave-end="opacity-0 max-h-0"
                        class="submenu pl-12 mt-2 space-y-2 overflow-hidden" style="display: none">

                        @can('family.budget.view')
                            <li><a :style="`color: ${textColor}`" href="{{ url('admin/budget/family') }}" class="hover:menu-item-active block px-4 py-2 rounded-lg"><i class="bi bi-piggy-bank mr-2"></i>{{ __('Family Budget') }}</a></li>
                        @endcan

                        @if ($user->can('family.budget.view') || $user->can('personal.expense.manage'))
                            <li><a :style="`color: ${textColor}`" href="{{ url('admin/budget/assigned') }}" class="hover:menu-item-active block px-4 py-2 rounded-lg"><i class="bi bi-person-check mr-2"></i>{{ __('Assigned Budgets') }}</a></li>
                        @endif

                        @can('personal.expense.manage')
                            <li><a :style="`color: ${textColor}`" href="{{ url('admin/expenses/my') }}" class="hover:menu-item-active block px-4 py-2 rounded-lg"><i class="bi bi-wallet2 mr-2"></i>{{ __('My Expenses') }}</a></li>
                        @endcan

                        @can('family.expense.view')
                            <li><a :style="`color: ${textColor}`" href="{{ url('admin/expenses/family') }}" class="hover:menu-item-active block px-4 py-2 rounded-lg"><i class="bi bi-house-heart mr-2"></i>{{ __('Family Expenses') }}</a></li>
                        @endcan

                        @can('personal.income.manage')
                            <li><a :style="`color: ${textColor}`" href="{{ route('admin.incomes.my') }}" class="hover:menu-item-active block px-4 py-2 rounded-lg"><i class="bi bi-graph-up-arrow mr-2"></i>{{ __('My Incomes') }}</a></li>
                        @endcan

                        @can('family.income.view')
                            <li><a :style="`color: ${textColor}`" href="{{ route('admin.incomes.family') }}" class="hover:menu-item-active block px-4 py-2 rounded-lg"><i class="bi bi-graph-up mr-2"></i>{{ __('Family Incomes') }}</a></li>
                        @endcan

                        <li><a :style="`color: ${textColor}`" href="{{ url('admin/categories/all') }}" class="hover:menu-item-active block px-4 py-2 rounded-lg"><i class="bi bi-tags mr-2"></i>{{ __('Categories') }}</a></li>

                        @can('personal.fund_request.create')
                            <li><a :style="`color: ${textColor}`" href="{{ url('admin/fund-request/my') }}" class="hover:menu-item-active block px-4 py-2 rounded-lg"><i class="bi bi-envelope-check mr-2"></i>{{ __('My Fund Requests') }}</a></li>
                        @endcan

                        @can('family.fund_request.view')
                            <li><a :style="`color: ${textColor}`" href="{{ url('admin/fund-request/funds/all') }}" class="hover:menu-item-active block px-4 py-2 rounded-lg"><i class="bi bi-envelope-paper mr-2"></i>{{ __('All Fund Requests') }}</a></li>
                        @endcan

                        @can('personal.savings.manage')
                            <li><a :style="`color: ${textColor}`" href="{{ url('admin/savings/my') }}" class="hover:menu-item-active block px-4 py-2 rounded-lg"><i class="bi bi-piggy-bank-fill mr-2"></i>{{ __('My Savings') }}</a></li>
                            <li><a :style="`color: ${textColor}`" href="{{ url('admin/savings/history') }}" class="hover:menu-item-active block px-4 py-2 rounded-lg"><i class="bi bi-clock-history mr-2"></i>{{ __('Savings History') }}</a></li>
                            <li><a :style="`color: ${textColor}`" href="{{ url('admin/savings/end-of-month') }}" class="hover:menu-item-active block px-4 py-2 rounded-lg"><i class="bi bi-calendar2-check mr-2"></i>{{ __('EOM Rollover') }}</a></li>
                        @endcan
                    </ul>
                </li>
            @endif

            {{-- Loans --}}
            @if ($canLoans)
                <li x-data class="hover:menu-item-active">
                    <button :style="`color: ${textColor}`"
                        class="menu-item group w-full text-left"
                        type="button" @click="toggleSubmenu('loans-submenu')">
                        <img src="{{ asset('images/icons/tv.svg') }}" alt="Loans Icon" class="menu-item-icon dark:invert">
                        <span class="menu-item-text">{{ __('Loans') }}</span>
                        <img src="{{ asset('images/icons/chevron-down.svg') }}" alt="Arrow" class="menu-item-arrow dark:invert transition-transform duration-300" :class="submenus['loans-submenu'] ? 'rotate-180' : ''">
                    </button>
                    <ul id="loans-submenu"
                        x-show="submenus['loans-submenu']"
                        x-transition:enter="transition-all ease-in-out duration-300"
                        x-transition:enter-start="opacity-0 max-h-0"
                        x-transition:enter-end="opacity-100 max-h-[500px]"
                        x-transition:leave="transition-all ease-in-out duration-300"
                        x-transition:leave-start="opacity-100 max-h-[500px]"
                        x-transition:leave-end="opacity-0 max-h-0"
                        class="submenu pl-12 mt-2 space-y-2 overflow-hidden" style="display: none">

                        @can('family.loan.manage')
                            <li><a :style="`color: ${textColor}`" href="{{ url('admin/loan-categories') }}" class="hover:menu-item-active block px-4 py-2 rounded-lg"><i class="bi bi-list-ol mr-2"></i>{{ __('Loan Categories') }}</a></li>
                            <li><a :style="`color: ${textColor}`" href="{{ url('admin/loans') }}" class="hover:menu-item-active block px-4 py-2 rounded-lg"><i class="bi bi-cash-coin mr-2"></i>{{ __('Loans') }}</a></li>
                        @endcan

                        @can('personal.loan.contribute')
                            <li><a :style="`color: ${textColor}`" href="{{ url('admin/loan-contributions/my') }}" class="hover:menu-item-active block px-4 py-2 rounded-lg"><i class="bi bi-coin mr-2"></i>{{ __('My Contributions') }}</a></li>
                        @endcan
                    </ul>
                </li>
            @endif

            {{-- Goals --}}
            @if ($canGoals)
                <li x-data class="hover:menu-item-active">
                    <button :style="`color: ${textColor}`"
                        class="menu-item group w-full text-left"
                        type="button" @click="toggleSubmenu('goals-submenu')">
                        <img src="{{ asset('images/icons/tv.svg') }}" alt="Goals Icon" class="menu-item-icon dark:invert">
                        <span class="menu-item-text">{{ __('Goals') }}</span>
                        <img src="{{ asset('images/icons/chevron-down.svg') }}" alt="Arrow" class="menu-item-arrow dark:invert transition-transform duration-300" :class="submenus['goals-submenu'] ? 'rotate-180' : ''">
                    </button>
                    <ul id="goals-submenu"
                        x-show="submenus['goals-submenu']"
                        x-transition:enter="transition-all ease-in-out duration-300"
                        x-transition:enter-start="opacity-0 max-h-0"
                        x-transition:enter-end="opacity-100 max-h-[500px]"
                        x-transition:leave="transition-all ease-in-out duration-300"
                        x-transition:leave-start="opacity-100 max-h-[500px]"
                        x-transition:leave-end="opacity-0 max-h-0"
                        class="submenu pl-12 mt-2 space-y-2 overflow-hidden" style="display: none">

                        @can('personal.goal.manage')
                            <li><a :style="`color: ${textColor}`" href="{{ url('admin/goals/personal') }}" class="hover:menu-item-active block px-4 py-2 rounded-lg"><i class="bi bi-bullseye mr-2"></i>{{ __('My Goals') }}</a></li>
                        @endcan

                        @can('family.goal.view')
                            <li><a :style="`color: ${textColor}`" href="{{ url('admin/goals/family') }}" class="hover:menu-item-active block px-4 py-2 rounded-lg"><i class="bi bi-people-fill mr-2"></i>{{ __('Family Goals') }}</a></li>
                        @endcan
                    </ul>
                </li>
            @endif

            {{-- Community (Social) --}}
            @if ($canSocial)
                <li x-data class="hover:menu-item-active">
                    <button :style="`color: ${textColor}`"
                        class="menu-item group w-full text-left"
                        type="button" @click="toggleSubmenu('social-submenu')">
                        <img src="{{ asset('images/icons/tv.svg') }}" alt="Community Icon" class="menu-item-icon dark:invert">
                        <span class="menu-item-text">{{ __('Community') }}</span>
                        <img src="{{ asset('images/icons/chevron-down.svg') }}" alt="Arrow" class="menu-item-arrow dark:invert transition-transform duration-300" :class="submenus['social-submenu'] ? 'rotate-180' : ''">
                    </button>
                    <ul id="social-submenu"
                        x-show="submenus['social-submenu']"
                        x-transition:enter="transition-all ease-in-out duration-300"
                        x-transition:enter-start="opacity-0 max-h-0"
                        x-transition:enter-end="opacity-100 max-h-[500px]"
                        x-transition:leave="transition-all ease-in-out duration-300"
                        x-transition:leave-start="opacity-100 max-h-[500px]"
                        x-transition:leave-end="opacity-0 max-h-0"
                        class="submenu pl-12 mt-2 space-y-2 overflow-hidden" style="display: none">

                        @can('personal.post.manage')
                            <li><a :style="`color: ${textColor}`" href="{{ url('admin/posts') }}" class="hover:menu-item-active block px-4 py-2 rounded-lg"><i class="bi bi-newspaper mr-2"></i>{{ __('Posts') }}</a></li>
                            <li><a :style="`color: ${textColor}`" href="{{ url('admin/my-posts') }}" class="hover:menu-item-active block px-4 py-2 rounded-lg"><i class="bi bi-person-lines-fill mr-2"></i>{{ __('My Posts') }}</a></li>
                        @endcan

                        @can('personal.profile.view')
                            <li><a :style="`color: ${textColor}`" href="{{ url('admin/users/' . $user->id . '/followers') }}" class="hover:menu-item-active block px-4 py-2 rounded-lg"><i class="bi bi-person-down mr-2"></i>{{ __('Followers') }}</a></li>
                            <li><a :style="`color: ${textColor}`" href="{{ url('admin/users/' . $user->id . '/followings') }}" class="hover:menu-item-active block px-4 py-2 rounded-lg"><i class="bi bi-person-up mr-2"></i>{{ __('Followings') }}</a></li>
                            <li><a :style="`color: ${textColor}`" href="{{ url('admin/users/' . $user->id . '/profile-stats') }}" class="hover:menu-item-active block px-4 py-2 rounded-lg"><i class="bi bi-person-vcard mr-2"></i>{{ __('Profile Stats') }}</a></li>
                        @endcan
                    </ul>
                </li>
            @endif

            {{-- Reports --}}
            @if ($user->can('family.reports.view') || $canFinance)
                <li class="hover:menu-item-active">
                    <a :style="`color: ${textColor}`" href="{{ route('admin.reports.index') }}"
                        class="menu-item group {{ Route::is('admin.reports.*') ? 'menu-item-active' : 'menu-item-inactive' }}">
                        <i class="bi bi-bar-chart-line menu-item-icon text-lg"></i>
                        <span class="menu-item-text">{{ __('Reports') }}</span>
                    </a>
                </li>
            @endif

            {{-- Family Management (Head only) --}}
            @if ($canFamilyMgmt)
                <li class="hover:menu-item-active">
                    <a :style="`color: ${textColor}`" href="{{ route('admin.family.members.index') }}"
                        class="menu-item group {{ Route::is('admin.family.*') ? 'menu-item-active' : 'menu-item-inactive' }}">
                        <img src="{{ asset('images/icons/user.svg') }}" alt="Family Management" class="menu-item-icon dark:invert">
                        <span class="menu-item-text">{{ __('Family Management') }}</span>
                    </a>
                </li>
            @endif

            {{-- Roles & Permissions --}}
            @if ($user->can('role.create') || $user->can('role.view') || $user->can('role.edit') || $user->can('role.delete'))
                <li x-data class="hover:menu-item-active">
                    <button :style="`color: ${textColor}`"
                        class="menu-item group w-full text-left {{ Route::is('admin.roles.*') ? 'menu-item-active' : 'menu-item-inactive' }}"
                        type="button" @click="toggleSubmenu('roles-submenu')">
                        <img src="{{ asset('images/icons/key.svg') }}" alt="Roles Icon" class="menu-item-icon dark:invert">
                        <span class="menu-item-text" :style="`color: ${textColor}`">{{ __('Roles & Permissions') }}</span>
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
                        @can('role.view')
                            <li><a :style="`color: ${textColor}`" href="{{ route('admin.roles.index') }}" class="hover:menu-item-active block px-4 py-2 rounded-lg {{ Route::is('admin.roles.index') || Route::is('admin.roles.edit') ? 'menu-item-active' : 'menu-item-inactive' }}">{{ __('Roles') }}</a></li>
                        @endcan
                        @can('role.create')
                            <li><a :style="`color: ${textColor}`" href="{{ route('admin.roles.create') }}" class="hover:menu-item-active block px-4 py-2 rounded-lg {{ Route::is('admin.roles.create') ? 'menu-item-active' : 'menu-item-inactive' }}">{{ __('New Role') }}</a></li>
                        @endcan
                    </ul>
                </li>
            @endif

            @php echo ld_apply_filters('sidebar_menu_after_roles', '') @endphp

            {{-- Users --}}
            @if ($user->can('user.create') || $user->can('user.view') || $user->can('user.edit') || $user->can('user.delete'))
                <li x-data class="hover:menu-item-active">
                    <button :style="`color: ${textColor}`"
                        class="menu-item group w-full text-left {{ Route::is('admin.users.*') ? 'menu-item-active' : 'menu-item-inactive' }}"
                        type="button" @click="toggleSubmenu('users-submenu')">
                        <img src="{{ asset('images/icons/user.svg') }}" alt="Users Icon" class="menu-item-icon dark:invert">
                        <span class="menu-item-text">{{ __('Users') }}</span>
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
                        @can('user.view')
                            <li><a :style="`color: ${textColor}`" href="{{ route('admin.users.index') }}" class="hover:menu-item-active block px-4 py-2 rounded-lg {{ Route::is('admin.users.index') || Route::is('admin.users.edit') ? 'menu-item-active' : 'menu-item-inactive' }}">{{ __('All Users') }}</a></li>
                        @endcan
                        @can('user.create')
                            <li><a :style="`color: ${textColor}`" href="{{ route('admin.users.create') }}" class="hover:menu-item-active block px-4 py-2 rounded-lg {{ Route::is('admin.users.create') ? 'menu-item-active' : 'menu-item-inactive' }}">{{ __('New User') }}</a></li>
                        @endcan
                    </ul>
                </li>
            @endif

            {{-- Monitoring --}}
            @if ($user->can('pulse.view') || $user->can('actionlog.view'))
                <li x-data class="hover:menu-item-active">
                    <button :style="`color: ${textColor}`"
                        class="menu-item group w-full text-left {{ Route::is('actionlog.*') ? 'menu-item-active' : 'menu-item-inactive' }}"
                        type="button" @click="toggleSubmenu('monitoring-submenu')">
                        <img src="{{ asset('images/icons/tv.svg') }}" alt="Monitoring Icon" class="menu-item-icon dark:invert">
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
                        @can('actionlog.view')
                            <li><a href="{{ route('actionlog.index') }}" class="hover:menu-item-active block px-4 py-2 rounded-lg {{ Route::is('actionlog.index') ? 'menu-item-active' : 'menu-item-inactive' }}"><span :style="`color: ${textColor}`">{{ __('Action Logs') }}</span></a></li>
                        @endcan
                        @can('pulse.view')
                            <li><a href="{{ route('pulse') }}" class="hover:menu-item-active block px-4 py-2 rounded-lg menu-item-inactive" target="_blank"><span :style="`color: ${textColor}`">{{ __('Spendium Pulse') }}</span></a></li>
                        @endcan
                    </ul>
                </li>
            @endif

            @php echo ld_apply_filters('sidebar_menu_after_monitoring', '') @endphp
        </ul>
    </div>

    {{-- More Group --}}
    <div>
        <h3 class="mb-4 text-xs uppercase leading-[20px] text-gray-400 px-5">
            {{ __('More') }}
        </h3>

        <ul class="flex flex-col mb-6">

            {{-- Notifications (always visible for authenticated users) --}}
            <li class="hover:menu-item-active">
                <a :style="`color: ${textColor}`" href="{{ route('admin.notifications.index') }}"
                    class="menu-item group {{ Route::is('admin.notifications.*') ? 'menu-item-active' : 'menu-item-inactive' }}">
                    <i class="bi bi-bell menu-item-icon text-lg"></i>
                    <span class="menu-item-text">{{ __('Notifications') }}</span>
                </a>
            </li>

            {{-- Settings --}}
            @if ($user->can('settings.edit') || $user->can('translations.view'))
                <li x-data class="hover:menu-item-active">
                    <button :style="`color: ${textColor}`"
                        class="menu-item group w-full text-left {{ Route::is('admin.settings.*') || Route::is('admin.translations.*') ? 'menu-item-active' : 'menu-item-inactive' }}"
                        type="button" @click="toggleSubmenu('settings-submenu')">
                        <img src="{{ asset('images/icons/settings.svg') }}" alt="Settings Icon" class="menu-item-icon dark:invert">
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
                        @can('settings.edit')
                            <li><a :style="`color: ${textColor}`" href="{{ route('admin.settings.index') }}" class="hover:menu-item-active block px-4 py-2 rounded-lg {{ Route::is('admin.settings.index') ? 'menu-item-active' : 'menu-item-inactive' }}">{{ __('General Settings') }}</a></li>
                        @endcan
                        @canany(['translations.view', 'translations.edit'])
                            <li><a :style="`color: ${textColor}`" href="{{ route('admin.translations.index') }}" class="hover:menu-item-active block px-4 py-2 rounded-lg {{ Route::is('admin.translations.*') ? 'menu-item-active' : 'menu-item-inactive' }}">{{ __('Translations') }}</a></li>
                        @endcanany
                    </ul>
                </li>
            @endif

            {{-- Profile --}}
            <li class="hover:menu-item-active">
                <a :style="`color: ${textColor}`" href="{{ route('profile.edit') }}"
                    class="menu-item group {{ Route::is('profile.*') ? 'menu-item-active' : 'menu-item-inactive' }}">
                    <i class="bi bi-person-circle menu-item-icon text-lg"></i>
                    <span class="menu-item-text">{{ __('My Profile') }}</span>
                </a>
            </li>

            {{-- Logout --}}
            <li class="hover:menu-item-active">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button :style="`color: ${textColor}`" type="submit"
                        class="menu-item group w-full text-left menu-item-inactive">
                        <img src="{{ asset('images/icons/logout.svg') }}" alt="Logout" class="menu-item-icon dark:invert">
                        <span class="menu-item-text">{{ __('Logout') }}</span>
                    </button>
                </form>
            </li>
        </ul>
    </div>
</nav>
