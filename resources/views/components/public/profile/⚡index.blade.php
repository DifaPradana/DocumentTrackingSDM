<?php

use Livewire\Component;

new class extends Component
{
    public function render()
    {
        return $this->view()
            ->layout('layouts.main')
            ->title('DocTracker | Profile');
    }
};
?>

<div>
    <livewire:public.profile.edit-profile />
</div>