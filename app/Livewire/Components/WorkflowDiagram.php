<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use Laraflow\Contracts\WorkflowRegistryInterface;
use Laraflow\Export\MermaidExporter;
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

        $base = MermaidExporter::export($workflow->definition);

        $highlight = "\n    classDef active fill:#10b98120,stroke:#059669,stroke-width:2px,color:#047857,font-weight:600;\n";
        $highlight .= "    classDef done fill:#0ea5e91a,stroke:#0284c7,stroke-width:1.5px,color:#075985;\n";
        $highlight .= "    classDef rejected fill:#f43f5e1a,stroke:#e11d48,stroke-width:2px,color:#9f1239,font-weight:600;\n";
        $highlight .= "    classDef paid fill:#10b9811a,stroke:#059669,stroke-width:2px,color:#065f46,font-weight:600;\n";

        foreach ($this->activePlaces as $place) {
            $class = match (true) {
                $place === 'rejected' => 'rejected',
                $place === 'paid' => 'paid',
                str_ends_with($place, '_approved') || $place === 'approved' => 'done',
                default => 'active',
            };
            $highlight .= "    class {$place} {$class};\n";
        }

        $diagram = $base . $highlight;

        return view('livewire.components.workflow-diagram', [
            'diagram' => $diagram,
        ]);
    }
}
