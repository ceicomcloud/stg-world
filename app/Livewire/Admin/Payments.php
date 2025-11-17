<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\User\UserOrder;
use Illuminate\Support\Facades\DB;

#[Layout('components.layouts.admin')]
class Payments extends Component
{
    use WithPagination;

    public $perPage = 10;
    public $search = '';
    public $status = '';
    public $provider = '';

    protected function rules()
    {
        return [
            'search' => 'nullable|string|max:100',
            'status' => 'nullable|string|in:pending,paid,failed,refunded,canceled',
            'provider' => 'nullable|string|in:paypal',
        ];
    }

    public function updated($property)
    {
        if (in_array($property, ['search', 'status', 'provider'])) {
            $this->resetPage();
        }
    }

    public function getOrdersProperty()
    {
        $query = UserOrder::query()
            ->with('user')
            ->orderByDesc('created_at');

        if ($this->status) {
            $query->where('status', $this->status);
        }
        if ($this->provider) {
            $query->where('provider', $this->provider);
        }
        if ($this->search) {
            $s = strtolower(trim($this->search));
            $query->where(function ($q) use ($s) {
                $q->whereRaw('LOWER(package_key) LIKE ?', ["%{$s}%"]) 
                  ->orWhere('id', (int) $s)
                  ->orWhereRaw('LOWER(provider_order_id) LIKE ?', ["%{$s}%"]) 
                  ->orWhereHas('user', function ($uq) use ($s) {
                      $uq->whereRaw('LOWER(name) LIKE ?', ["%{$s}%"]) 
                         ->orWhereRaw('LOWER(email) LIKE ?', ["%{$s}%"]);
                  });
            });
        }

        return $query->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.admin.payments', [
            'orders' => $this->orders,
        ]);
    }
}