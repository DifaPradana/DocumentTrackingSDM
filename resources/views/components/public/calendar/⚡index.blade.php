<?php

use Livewire\Component;

new class extends Component
{
    public function render()
    {
        return $this->view()
            ->layout('layouts.main')
            ->title('DocTracker | Calendar');
    }
};
?>

<div>
    <livewire:public.calendar.show-calendar />
</div>