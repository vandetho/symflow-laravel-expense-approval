<div class="space-y-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-zinc-900">Expense requests</h1>
            <p class="mt-1 text-sm text-zinc-500">A live Petri-net workflow with parallel legal, finance &amp; manager review.</p>
        </div>
        <a href="{{ route('expenses.create') }}" wire:navigate
           class="inline-flex items-center gap-2 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-semibold text-white shadow-xs transition hover:bg-zinc-800">
            <svg class="size-4" viewBox="0 0 20 20" fill="currentColor"><path d="M10 3.75a.75.75 0 0 1 .75.75v4.75h4.75a.75.75 0 0 1 0 1.5h-4.75v4.75a.75.75 0 0 1-1.5 0v-4.75H4.5a.75.75 0 0 1 0-1.5h4.75V4.5a.75.75 0 0 1 .75-.75z"/></svg>
            New expense
        </a>
    </div>

    @php
        $stats = [
            ['label' => 'Total requests', 'value' => $totals['count'], 'format' => 'int'],
            ['label' => 'Total submitted', 'value' => $totals['amount'], 'format' => 'money'],
            ['label' => 'Awaiting review', 'value' => $totals['pending'], 'format' => 'money', 'tone' => 'amber'],
            ['label' => 'Reimbursed', 'value' => $totals['paid'], 'format' => 'money', 'tone' => 'emerald'],
        ];
    @endphp
    <dl class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($stats as $s)
            <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-xs">
                <dt class="text-xs font-semibold uppercase tracking-wider text-zinc-500">{{ $s['label'] }}</dt>
                <dd class="mt-2 text-2xl font-semibold tracking-tight {{ ($s['tone'] ?? null) === 'amber' ? 'text-amber-700' : (($s['tone'] ?? null) === 'emerald' ? 'text-emerald-700' : 'text-zinc-900') }}">
                    @if ($s['format'] === 'money')
                        ${{ number_format((float) $s['value'], 2) }}
                    @else
                        {{ number_format((int) $s['value']) }}
                    @endif
                </dd>
            </div>
        @endforeach
    </dl>

    <div class="flex items-center gap-3">
        <div class="relative flex-1 max-w-md">
            <svg class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-zinc-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 3.41 9.83l3.13 3.13a.75.75 0 1 0 1.06-1.06l-3.13-3.13A5.5 5.5 0 0 0 9 3.5zM5 9a4 4 0 1 1 8 0 4 4 0 0 1-8 0z" clip-rule="evenodd"/></svg>
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search by title or category…"
                   class="w-full rounded-lg border border-zinc-200 bg-white py-2 pl-9 pr-3 text-sm shadow-xs placeholder:text-zinc-400 focus:border-emerald-500 focus:outline-hidden focus:ring-2 focus:ring-emerald-500/20"/>
        </div>
        <div class="inline-flex rounded-lg border border-zinc-200 bg-white p-1 shadow-xs">
            <button wire:click="$set('view', 'kanban')"
                    class="rounded-md px-3 py-1 text-xs font-semibold transition {{ $view === 'kanban' ? 'bg-zinc-900 text-white' : 'text-zinc-600 hover:text-zinc-900' }}">
                Kanban
            </button>
            <button wire:click="$set('view', 'table')"
                    class="rounded-md px-3 py-1 text-xs font-semibold transition {{ $view === 'table' ? 'bg-zinc-900 text-white' : 'text-zinc-600 hover:text-zinc-900' }}">
                Table
            </button>
        </div>
    </div>

    @if ($view === 'kanban')
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-5">
            @foreach ($columns as $column)
                @php
                    $tones = [
                        'zinc' => ['border' => 'border-zinc-200', 'dot' => 'bg-zinc-400', 'text' => 'text-zinc-700'],
                        'amber' => ['border' => 'border-amber-200', 'dot' => 'bg-amber-500', 'text' => 'text-amber-800'],
                        'sky' => ['border' => 'border-sky-200', 'dot' => 'bg-sky-500', 'text' => 'text-sky-800'],
                        'emerald' => ['border' => 'border-emerald-200', 'dot' => 'bg-emerald-500', 'text' => 'text-emerald-800'],
                        'rose' => ['border' => 'border-rose-200', 'dot' => 'bg-rose-500', 'text' => 'text-rose-800'],
                    ];
                    $t = $tones[$column['tone']];
                @endphp
                <div class="flex flex-col rounded-xl border {{ $t['border'] }} bg-white">
                    <div class="flex items-center justify-between border-b border-zinc-100 px-4 py-3">
                        <div class="flex items-center gap-2">
                            <span class="size-2 rounded-full {{ $t['dot'] }}"></span>
                            <span class="text-xs font-semibold uppercase tracking-wider {{ $t['text'] }}">{{ $column['label'] }}</span>
                        </div>
                        <span class="rounded-full bg-zinc-100 px-2 py-0.5 text-[11px] font-semibold text-zinc-700">{{ $column['items']->count() }}</span>
                    </div>
                    <div class="flex flex-col gap-2 p-3">
                        @forelse ($column['items'] as $expense)
                            <a href="{{ route('expenses.show', $expense) }}" wire:navigate
                               class="group rounded-lg border border-zinc-100 bg-zinc-50/50 p-3 transition hover:border-emerald-200 hover:bg-white hover:shadow-xs">
                                <p class="line-clamp-2 text-sm font-semibold text-zinc-900 group-hover:text-emerald-700">{{ $expense->title }}</p>
                                <div class="mt-1 flex items-center justify-between text-xs text-zinc-500">
                                    <span>{{ $expense->category }}</span>
                                    <span class="font-mono font-semibold text-zinc-900">${{ number_format((float) $expense->amount, 2) }}</span>
                                </div>
                                <div class="mt-3 flex items-center justify-between">
                                    <div class="flex items-center gap-1.5">
                                        <span class="grid size-5 place-items-center rounded-full bg-zinc-200 text-[9px] font-semibold text-zinc-700">{{ $expense->requester->initials() }}</span>
                                        <span class="text-[11px] text-zinc-500">{{ $expense->requester->name }}</span>
                                    </div>
                                    <span class="text-[11px] text-zinc-400">{{ optional($expense->submitted_at ?? $expense->created_at)->diffForHumans() }}</span>
                                </div>
                            </a>
                        @empty
                            <div class="rounded-lg border border-dashed border-zinc-200 px-3 py-6 text-center text-xs text-zinc-400">
                                Nothing here yet.
                            </div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-xs">
            <table class="min-w-full divide-y divide-zinc-200">
                <thead class="bg-zinc-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">
                        <th class="px-4 py-3">Title</th>
                        <th class="px-4 py-3">Requester</th>
                        <th class="px-4 py-3">Category</th>
                        <th class="px-4 py-3 text-right">Amount</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Submitted</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @forelse ($expenses as $expense)
                        <tr class="cursor-pointer transition hover:bg-zinc-50" onclick="window.location='{{ route('expenses.show', $expense) }}'">
                            <td class="px-4 py-3">
                                <a href="{{ route('expenses.show', $expense) }}" wire:navigate class="text-sm font-semibold text-zinc-900 hover:text-emerald-700">{{ $expense->title }}</a>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="grid size-6 place-items-center rounded-full bg-zinc-200 text-[10px] font-semibold text-zinc-700">{{ $expense->requester->initials() }}</span>
                                    <span class="text-sm text-zinc-700">{{ $expense->requester->name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-zinc-600">{{ $expense->category }}</td>
                            <td class="px-4 py-3 text-right font-mono text-sm font-semibold text-zinc-900">${{ number_format((float) $expense->amount, 2) }}</td>
                            <td class="px-4 py-3"><x-status-pill :status="$expense->status" /></td>
                            <td class="px-4 py-3 text-sm text-zinc-500">{{ optional($expense->submitted_at ?? $expense->created_at)->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-sm text-zinc-500">No expense requests match your search.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
</div>
