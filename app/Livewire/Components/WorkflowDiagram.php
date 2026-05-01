<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use Laraflow\Contracts\WorkflowRegistryInterface;
use Laraflow\Data\WorkflowDefinition;
use Livewire\Component;

class WorkflowDiagram extends Component
{
    public string $workflowName;

    /** @var array<string> */
    public array $activePlaces = [];

    /**
     * @param  array<string>  $activePlaces
     */
    public function mount(string $workflowName, array $activePlaces = []): void
    {
        $this->workflowName = $workflowName;
        $this->activePlaces = $activePlaces;
    }

    public function render()
    {
        $registry = app(WorkflowRegistryInterface::class);
        $workflow = $registry->get($this->workflowName);

        $diagram = $this->buildFlowchart($workflow->definition, $this->activePlaces);

        return view('livewire.components.workflow-diagram', [
            'diagram' => $diagram,
        ]);
    }

    /**
     * Build a Mermaid flowchart with each place as a rounded node, each transition
     * as a labelled edge, and active places highlighted via classDef.
     *
     * @param  array<string>  $active
     */
    private function buildFlowchart(WorkflowDefinition $definition, array $active): string
    {
        $lines = [];
        $lines[] = 'flowchart LR';

        // Place nodes — rounded rectangles with description on a second line.
        foreach ($definition->places as $place) {
            $description = $place->metadata['description'] ?? null;
            $label = $description ? "{$place->name}\\n{$description}" : $place->name;
            $lines[] = "    {$place->name}([\"{$label}\"])";
        }

        // Transitions: place → place edges labelled with transition name
        // For multi-from / multi-to (Petri nets), draw an edge from each from to each to.
        foreach ($definition->transitions as $t) {
            $label = $t->name;
            if ($t->guard !== null) {
                $label .= " ({$t->guard})";
            }

            // Quoted edge label — Mermaid otherwise treats [ ] / { } in the label
            // as node-shape delimiters and bails with a parse error.
            $quoted = '"' . str_replace('"', '#quot;', $label) . '"';

            foreach ($t->froms as $from) {
                foreach ($t->tos as $to) {
                    $lines[] = "    {$from} -->|{$quoted}| {$to}";
                }
            }
        }

        // Style definitions
        $lines[] = '';
        $lines[] = '    classDef base fill:#fafafa,stroke:#a1a1aa,stroke-width:1px,color:#27272a';
        $lines[] = '    classDef active fill:#a7f3d0,stroke:#059669,stroke-width:3px,color:#064e3b,font-weight:700';
        $lines[] = '    classDef done fill:#bae6fd,stroke:#0284c7,stroke-width:2px,color:#075985,font-weight:600';
        $lines[] = '    classDef rejected fill:#fecdd3,stroke:#e11d48,stroke-width:3px,color:#9f1239,font-weight:700';
        $lines[] = '    classDef paid fill:#a7f3d0,stroke:#059669,stroke-width:3px,color:#064e3b,font-weight:700';

        // Apply base to all places, then override active ones
        $allPlaceNames = array_map(fn ($p) => $p->name, $definition->places);
        $lines[] = '    class ' . implode(',', $allPlaceNames) . ' base';

        foreach ($active as $place) {
            $class = match (true) {
                $place === 'rejected' => 'rejected',
                $place === 'paid' => 'paid',
                $place === 'approved', str_ends_with($place, '_approved') => 'done',
                default => 'active',
            };
            $lines[] = "    class {$place} {$class}";
        }

        return implode("\n", $lines) . "\n";
    }
}
