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
                <div class="flex items-center gap-3">
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
            <div class="flex flex-col items-end gap-2">
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
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Left: actions + audit log --}}
        <div class="space-y-6 lg:col-span-2">
            {{-- Action panel --}}
            <div class="rounded-2xl border border-zinc-200 bg-white shadow-xs">
                <div class="border-b border-zinc-100 px-6 py-4">
                    <h2 class="text-sm font-semibold tracking-tight text-zinc-900">Workflow actions</h2>
                    <p class="mt-0.5 text-xs text-zinc-500">
                        @if ($currentUser)
                            Acting as <span class="font-medium text-zinc-700">{{ $currentUser->name }}</span> ({{ $currentUser->role->label() }}). Switch users from the top-right.
                        @else
                            Sign in via the button in the top-right to fire role-guarded transitions.
                        @endif
                    </p>
                </div>
                <div class="p-6 space-y-4">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-500">Reason / note (optional)</label>
                    <textarea wire:model="reason" rows="2" placeholder="Captured in the audit log alongside the transition…"
                              class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm shadow-xs placeholder:text-zinc-400 focus:border-emerald-500 focus:outline-hidden focus:ring-2 focus:ring-emerald-500/20"></textarea>

                    <div class="grid gap-2 sm:grid-cols-2">
                        @foreach ($enabledTransitions as $row)
                            @php
                                $intent = $row['intent'];
                                $btnClass = match ($intent) {
                                    'primary' => 'bg-emerald-600 text-white hover:bg-emerald-700 disabled:bg-emerald-600/30',
                                    'destructive' => 'bg-rose-600 text-white hover:bg-rose-700 disabled:bg-rose-600/30',
                                    'success' => 'bg-sky-600 text-white hover:bg-sky-700 disabled:bg-sky-600/30',
                                    default => 'bg-zinc-900 text-white hover:bg-zinc-800 disabled:bg-zinc-300',
                                };
                            @endphp
                            <button type="button"
                                    wire:click="fire('{{ $row['transition']->name }}')"
                                    @disabled(! $row['allowed'])
                                    title="{{ $row['allowed'] ? 'Fire this transition' : ($row['reason'] ?? 'Not available') }}"
                                    class="group flex flex-col items-start gap-1 rounded-lg px-4 py-3 text-left text-sm font-semibold transition disabled:cursor-not-allowed {{ $btnClass }}">
                                <span class="flex items-center gap-2">
                                    {{ $row['transition']->name }}
                                    @if ($row['transition']->guard)
                                        <span class="rounded bg-white/20 px-1.5 py-0.5 text-[10px] font-mono">{{ $row['transition']->guard }}</span>
                                    @endif
                                </span>
                                <span class="text-[11px] font-normal opacity-75">
                                    {{ implode(', ', $row['transition']->froms) }} → {{ implode(', ', $row['transition']->tos) }}
                                </span>
                                @if (! $row['allowed'] && $row['reason'])
                                    <span class="text-[11px] font-normal text-white/80">{{ $row['reason'] }}</span>
                                @endif
                            </button>
                        @endforeach
                    </div>
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
                            <div class="relative flex-none pt-1">
                                <span class="grid size-8 place-items-center rounded-full bg-zinc-100 text-[11px] font-semibold text-zinc-700">
                                    {{ $log->actor?->initials() ?? '··' }}
                                </span>
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

        {{-- Right: diagram --}}
        <div class="space-y-6">
            <div class="rounded-2xl border border-zinc-200 bg-white shadow-xs">
                <div class="border-b border-zinc-100 px-6 py-4">
                    <h2 class="text-sm font-semibold tracking-tight text-zinc-900">Workflow diagram</h2>
                    <p class="mt-0.5 text-xs text-zinc-500">Active places highlighted live.</p>
                </div>
                <div class="p-4">
                    <livewire:components.workflow-diagram
                        workflow-name="expense_approval"
                        :active-places="$activePlaces"
                        :key="'diagram-'.$expense->id.'-'.implode('-', $activePlaces)" />
                </div>
            </div>

            <div class="rounded-2xl border border-zinc-200 bg-white shadow-xs">
                <div class="border-b border-zinc-100 px-6 py-4">
                    <h2 class="text-sm font-semibold tracking-tight text-zinc-900">Marking</h2>
                    <p class="mt-0.5 text-xs text-zinc-500">Raw Petri-net token count per place.</p>
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
