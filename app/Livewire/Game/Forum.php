<?php

namespace App\Livewire\Game;

use App\Models\Forum\ForumCategory;
use App\Models\Forum\Forum as ForumModel;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.game')]
class Forum extends Component
{
    use WithPagination;

    public $currentView = 'categories'; // categories, forums
    public $selectedCategory = null;

    public function mount()
    {
        // Initialize with categories view
        $this->currentView = 'categories';
    }

    public function showCategories()
    {
        $this->currentView = 'categories';
        $this->selectedCategory = null;
        $this->resetPage();
    }

    public function showForums($categoryId)
    {
        $this->selectedCategory = ForumCategory::findOrFail($categoryId);
        $this->currentView = 'forums';
        $this->resetPage();
    }

    public function render()
    {
        $data = [];

        switch ($this->currentView) {
            case 'categories':
                $data['categories'] = ForumCategory::active()
                    ->ordered()
                    ->with(['forums' => function($query) {
                        $query->active()->ordered()->parents();
                    }])
                    ->get();
                break;

            case 'forums':
                $data['forums'] = $this->selectedCategory
                    ->forums()
                    ->active()
                    ->ordered()
                    ->parents()
                    ->with(['lastPost.user', 'children' => function($query) {
                        $query->active()->ordered();
                    }])
                    ->get();
                break;
        }

        return view('livewire.game.forum', $data);
    }
}
