<div class="space-y-6">
    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-zinc-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="hover:text-zinc-900">Expenses</a>
        <svg class="size-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02z" clip-rule="evenodd"/></svg>
        <span class="font-medium text-zinc-700">#{{ $expense->id }}</span>
    </div>

    {{-- Header --}}
    <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-xs">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="text-xl font-semibold tracking-tight text-zinc-900">{{ $expense->title }}</h1>
                    <x-status-pill :status="$expense->status" />
                </div>
                @if ($expense->description)
                    <p class="mt-2 max-w-2xl text-sm text-zinc-600">{{ $expense->description }}</p>
                @endif
                <dl class="mt-5 grid grid-cols-2 gap-x-8 gap-y-3 sm:grid-cols-4">
                    <div>
                        <dt class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500">Requester</dt>
                        <dd class="mt-1 flex items-center gap-2 text-sm text-zinc-900">
                            <span class="grid size-6 place-items-center rounded-full bg-zinc-200 text-[10px] font-semibold text-zinc-700">{{ $expense->requester->initials() }}</span>
                            {{ $expense->requester->name }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500">Category</dt>
                        <dd class="mt-1 text-sm text-zinc-900">{{ $expense->category }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500">Submitted</dt>
                        <dd class="mt-1 text-sm text-zinc-900">{{ optional($expense->submitted_at)->format('M j, Y g:i a') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500">Amount</dt>
                        <dd class="mt-1 font-mono text-lg font-semibold text-zinc-900">${{ number_format((float) $expense->amount, 2) }}</dd>
                    </div>
                </dl>
            </div>
            <div class="rounded-lg bg-zinc-50 px-3 py-2 text-right">
                <div class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500">Active places</div>
                <div class="mt-1 flex flex-wrap justify-end gap-1">
                    @forelse ($activePlaces as $place)
                        <code class="rounded bg-white px-1.5 py-0.5 font-mono text-[11px] text-zinc-700 ring-1 ring-zinc-200">{{ $place }}</code>
                    @empty
                        <span class="text-xs text-zinc-400">none</span>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Workflow diagram: locally-rendered Mermaid with active places highlighted per request --}}
    <div class="rounded-2xl border border-zinc-200 bg-white shadow-xs">
        <div class="flex items-center justify-between border-b border-zinc-100 px-6 py-4">
            <div>
                <h2 class="text-sm font-semibold tracking-tight text-zinc-900">Workflow — live state</h2>
                <p class="mt-0.5 text-xs text-zinc-500">Mermaid render with active places highlighted per request.</p>
            </div>
        </div>
        <div class="p-4">
            <div class="min-h-[280px]">
                <livewire:components.workflow-diagram
                    workflow-name="expense_approval"
                    :active-places="$activePlaces"
                    :key="'diagram-'.$expense->id.'-'.implode('-', $activePlaces)" />
            </div>
        </div>
    </div>

    {{-- Canonical canvas: live iframe from symflowbuilder.com (interactive, pan/zoom) --}}
    <div class="rounded-2xl border border-zinc-200 bg-white shadow-xs">
        <div class="flex items-center justify-between border-b border-zinc-100 px-6 py-4">
            <div>
                <h2 class="text-sm font-semibold tracking-tight text-zinc-900">Canonical canvas</h2>
                <p class="mt-0.5 text-xs text-zinc-500">Live, pan-and-zoomable embed from symflowbuilder.com — same workflow, no per-request state.</p>
            </div>
            <a href="https://symflowbuilder.com/w/86b557637fa5a7aa" target="_blank" rel="noopener"
               class="inline-flex items-center gap-1.5 rounded-md border border-zinc-200 bg-white px-2.5 py-1 text-[11px] font-medium text-zinc-600 transition hover:border-emerald-300 hover:text-emerald-700">
                Open full size
                <svg class="size-3" viewBox="0 0 20 20" fill="currentColor"><path d="M11 3a1 1 0 1 0 0 2h2.586L7.293 11.293a1 1 0 1 0 1.414 1.414L15 6.414V9a1 1 0 1 0 2 0V4a1 1 0 0 0-1-1h-5z"/><path d="M5 5a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2v-3a1 1 0 1 0-2 0v3H5V7h3a1 1 0 1 0 0-2H5z"/></svg>
            </a>
        </div>
        <div class="p-2">
            @php $embedMarking = implode(',', array_map('urlencode', $activePlaces)); @endphp
            <iframe wire:key="symflowbuilder-iframe-{{ $embedMarking }}"
                    src="https://symflowbuilder.com/embed/86b557637fa5a7aa?branding=0&minimap=0&scenario=0{{ $embedMarking !== '' ? '&marking='.$embedMarking : '' }}"
                    width="100%" height="500"
                    class="rounded-lg"
                    style="border:0"
                    loading="lazy"
                    title="SymFlowBuilder workflow"></iframe>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            {{-- Action panel --}}
            <div class="rounded-2xl border border-zinc-200 bg-white shadow-xs">
                <div class="border-b border-zinc-100 px-6 py-4">
                    <h2 class="text-sm font-semibold tracking-tight text-zinc-900">Workflow actions</h2>
                    <p class="mt-0.5 text-xs text-zinc-500">
                        @if ($currentUser)
                            Acting as <span class="font-medium text-zinc-700">{{ $currentUser->name }}</span> ({{ $currentUser->role->label() }}). Switch users from the top-right.
                        @else
                            Sign in via the top-right to fire role-guarded transitions.
                        @endif
                    </p>
                </div>

                @if (! $currentUser)
                    <div class="border-b border-zinc-100 bg-amber-50/60 px-6 py-3 text-sm text-amber-800">
                        <strong class="font-semibold">Not signed in.</strong> Most actions require a role. Use the "Sign in to demo" button in the top-right.
                    </div>
                @endif

                <div class="space-y-5 p-6">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-500">Reason / note (optional)</label>
                        <textarea wire:model="reason" rows="2" placeholder="Captured in the audit log alongside the transition…"
                                  class="mt-1 w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm shadow-xs placeholder:text-zinc-400 focus:border-emerald-500 focus:outline-hidden focus:ring-2 focus:ring-emerald-500/20"></textarea>
                    </div>

                    {{-- Available now --}}
                    @if (count($grouped['available']) > 0)
                        <section>
                            <div class="mb-2 flex items-center gap-2">
                                <span class="size-2 rounded-full bg-emerald-500"></span>
                                <h3 class="text-xs font-semibold uppercase tracking-wider text-emerald-700">Available now</h3>
                            </div>
                            <div class="grid gap-2 sm:grid-cols-2">
                                @foreach ($grouped['available'] as $row)
                                    @php
                                        $btn = match ($row['intent']) {
                                            'primary' => 'bg-emerald-600 text-white hover:bg-emerald-700',
                                            'destructive' => 'bg-rose-600 text-white hover:bg-rose-700',
                                            'success' => 'bg-sky-600 text-white hover:bg-sky-700',
                                            default => 'bg-zinc-900 text-white hover:bg-zinc-800',
                                        };
                                    @endphp
                                    <button type="button"
                                            wire:click="fire('{{ $row['transition']->name }}')"
                                            class="group flex flex-col items-start gap-1 rounded-lg px-4 py-3 text-left text-sm font-semibold transition {{ $btn }}">
                                        <span class="flex items-center gap-2">
                                            {{ $row['transition']->name }}
                                            @if ($row['transition']->guard)
                                                <span class="rounded bg-white/20 px-1.5 py-0.5 font-mono text-[10px]">{{ $row['transition']->guard }}</span>
                                            @endif
                                        </span>
                                        <span class="text-[11px] font-normal opacity-80">
                                            {{ implode(', ', $row['transition']->froms) }} → {{ implode(', ', $row['transition']->tos) }}
                                        </span>
                                    </button>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    {{-- Awaiting another actor --}}
                    @if (count($grouped['awaiting']) > 0)
                        <section>
                            <div class="mb-2 flex items-center gap-2">
                                <span class="size-2 rounded-full bg-amber-500"></span>
                                <h3 class="text-xs font-semibold uppercase tracking-wider text-amber-700">
                                    Awaiting another actor
                                </h3>
                            </div>
                            <ul class="grid gap-2 sm:grid-cols-2">
                                @foreach ($grouped['awaiting'] as $row)
                                    <li class="flex flex-col gap-1 rounded-lg border border-amber-200 bg-amber-50/60 px-4 py-3 text-left">
                                        <div class="flex items-center gap-2 text-sm font-semibold text-amber-900">
                                            {{ $row['transition']->name }}
                                            @if ($row['transition']->guard)
                                                <span class="rounded bg-white/70 px-1.5 py-0.5 font-mono text-[10px] text-amber-800">{{ $row['transition']->guard }}</span>
                                            @endif
                                        </div>
                                        <span class="text-[11px] text-amber-800/80">
                                            {{ implode(', ', $row['transition']->froms) }} → {{ implode(', ', $row['transition']->tos) }}
                                        </span>
                                        <span class="text-[11px] font-medium text-amber-900">{{ $row['reason'] }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </section>
                    @endif

                    {{-- Inactive --}}
                    @if (count($grouped['inactive']) > 0)
                        <section x-data="{ open: false }">
                            <button type="button" @click="open = !open"
                                    class="flex w-full items-center justify-between gap-2 rounded-md px-2 py-1 text-left">
                                <span class="flex items-center gap-2">
                                    <span class="size-2 rounded-full bg-zinc-300"></span>
                                    <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Not in this state ({{ count($grouped['inactive']) }})</span>
                                </span>
                                <svg class="size-4 text-zinc-400 transition" :class="{ 'rotate-180': open }" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.06l3.71-3.83a.75.75 0 1 1 1.08 1.04l-4.25 4.39a.75.75 0 0 1-1.08 0L5.21 8.27a.75.75 0 0 1 .02-1.06z" clip-rule="evenodd"/></svg>
                            </button>
                            <ul x-show="open" x-cloak x-transition class="mt-2 grid gap-1 text-[11px] text-zinc-500 sm:grid-cols-2">
                                @foreach ($grouped['inactive'] as $row)
                                    <li class="rounded-md bg-zinc-50 px-3 py-2">
                                        <code class="font-mono font-semibold text-zinc-700">{{ $row['transition']->name }}</code>
                                        <span class="ml-1">— {{ $row['reason'] }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </section>
                    @endif
                </div>
            </div>

            {{-- Audit log --}}
            <div class="rounded-2xl border border-zinc-200 bg-white shadow-xs">
                <div class="border-b border-zinc-100 px-6 py-4">
                    <h2 class="text-sm font-semibold tracking-tight text-zinc-900">Audit timeline</h2>
                    <p class="mt-0.5 text-xs text-zinc-500">Captured by the <code class="font-mono">AuditLogMiddleware</code> on every fired transition.</p>
                </div>
                <ul class="divide-y divide-zinc-100">
                    @forelse ($expense->auditLogs as $log)
                        <li class="flex gap-4 px-6 py-4">
                            <div class="flex-none pt-1">
                                <span class="grid size-8 place-items-center rounded-full bg-zinc-100 text-[11px] font-semibold text-zinc-700">{{ $log->actor?->initials() ?? '··' }}</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-baseline gap-x-2">
                                    <span class="text-sm font-semibold text-zinc-900">{{ $log->actor?->name ?? 'System' }}</span>
                                    <span class="text-xs text-zinc-500">fired</span>
                                    <code class="rounded bg-zinc-100 px-1.5 py-0.5 font-mono text-[11px] text-zinc-800">{{ $log->transition }}</code>
                                    <span class="ml-auto text-xs text-zinc-400">{{ $log->occurred_at->diffForHumans() }}</span>
                                </div>
                                <div class="mt-1 flex flex-wrap items-center gap-1 text-[11px] text-zinc-500">
                                    @foreach ((array) $log->marking_before as $p)
                                        <code class="rounded bg-zinc-50 px-1 py-0.5 font-mono text-zinc-500 ring-1 ring-zinc-200">{{ $p }}</code>
                                    @endforeach
                                    <svg class="size-3 text-zinc-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02z" clip-rule="evenodd"/></svg>
                                    @foreach ((array) $log->marking_after as $p)
                                        <code class="rounded bg-emerald-50 px-1 py-0.5 font-mono text-emerald-700 ring-1 ring-emerald-200">{{ $p }}</code>
                                    @endforeach
                                </div>
                                @if ($log->reason)
                                    <p class="mt-2 rounded-md bg-zinc-50 px-3 py-2 text-sm italic text-zinc-700">"{{ $log->reason }}"</p>
                                @endif
                            </div>
                        </li>
                    @empty
                        <li class="px-6 py-12 text-center text-sm text-zinc-500">No transitions recorded yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        {{-- Right: marking + meta --}}
        <div class="space-y-6">
            <div class="rounded-2xl border border-zinc-200 bg-white shadow-xs">
                <div class="border-b border-zinc-100 px-6 py-4">
                    <h2 class="text-sm font-semibold tracking-tight text-zinc-900">Marking</h2>
                    <p class="mt-0.5 text-xs text-zinc-500">Petri-net token count per place.</p>
                </div>
                <div class="p-4">
                    @php $marking = $expense->getWorkflowMarking()->toArray(); @endphp
                    <ul class="grid grid-cols-2 gap-2 text-xs">
                        @foreach ($marking as $place => $tokens)
                            <li class="flex items-center justify-between rounded-md px-2 py-1 {{ $tokens > 0 ? 'bg-emerald-50 text-emerald-800 ring-1 ring-emerald-200' : 'text-zinc-500' }}">
                                <code class="font-mono">{{ $place }}</code>
                                <span class="font-semibold">{{ $tokens }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

        </div>
    </div>
</div>
