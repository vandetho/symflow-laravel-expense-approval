<div class="max-w-2xl space-y-6">
    <div class="flex items-center gap-2 text-sm text-zinc-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="hover:text-zinc-900">Expenses</a>
        <svg class="size-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02z" clip-rule="evenodd"/></svg>
        <span class="font-medium text-zinc-700">New</span>
    </div>

    <div>
        <h1 class="text-2xl font-semibold tracking-tight text-zinc-900">New expense request</h1>
        <p class="mt-1 text-sm text-zinc-500">Drafts start in the <code class="rounded bg-zinc-100 px-1 py-0.5 font-mono text-xs">draft</code> place. Submit to fan out to legal, finance &amp; manager review in parallel.</p>
    </div>

    <form wire:submit="save" class="space-y-4 rounded-2xl border border-zinc-200 bg-white p-6 shadow-xs">
        <div>
            <label for="title" class="block text-xs font-semibold uppercase tracking-wider text-zinc-500">Title</label>
            <input type="text" id="title" wire:model="title" placeholder="What is this expense for?"
                   class="mt-1 w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm shadow-xs placeholder:text-zinc-400 focus:border-emerald-500 focus:outline-hidden focus:ring-2 focus:ring-emerald-500/20"/>
            @error('title') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="description" class="block text-xs font-semibold uppercase tracking-wider text-zinc-500">Description</label>
            <textarea id="description" wire:model="description" rows="3" placeholder="Optional context for reviewers."
                      class="mt-1 w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm shadow-xs placeholder:text-zinc-400 focus:border-emerald-500 focus:outline-hidden focus:ring-2 focus:ring-emerald-500/20"></textarea>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="category" class="block text-xs font-semibold uppercase tracking-wider text-zinc-500">Category</label>
                <select id="category" wire:model="category"
                        class="mt-1 w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm shadow-xs focus:border-emerald-500 focus:outline-hidden focus:ring-2 focus:ring-emerald-500/20">
                    @foreach ($categories as $c)
                        <option value="{{ $c }}">{{ $c }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="amount" class="block text-xs font-semibold uppercase tracking-wider text-zinc-500">Amount (USD)</label>
                <input type="number" id="amount" step="0.01" wire:model="amount" placeholder="0.00"
                       class="mt-1 w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-right font-mono text-sm shadow-xs focus:border-emerald-500 focus:outline-hidden focus:ring-2 focus:ring-emerald-500/20"/>
                @error('amount') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('dashboard') }}" wire:navigate class="text-sm font-medium text-zinc-500 hover:text-zinc-900">Cancel</a>
            <button type="submit"
                    class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-xs transition hover:bg-emerald-700">
                Create draft
            </button>
        </div>
    </form>
</div>
