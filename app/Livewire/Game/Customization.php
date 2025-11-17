<?php

namespace App\Livewire\Game;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use App\Models\Planet\PlanetUnit;
use App\Models\Planet\PlanetShip;
use App\Models\Template\TemplateBuild;
use App\Models\User\UserAssetCustomization;

#[Layout('components.layouts.game')]
class Customization extends Component
{
    use WithFileUploads;

    public $items = [];
    public $editing = null; // template_build_id
    public $form = [
        'display_name' => '',
        'icon' => null,
    ];

    protected $rules = [
        'form.display_name' => 'nullable|string|max:50',
        'form.icon' => 'nullable|image|max:512', // 512KB
    ];

    public function mount()
    {
        $user = Auth::user();
        $planet = $user?->getActualPlanet();

        $this->items = [];

        if ($planet) {
            $units = PlanetUnit::with('unit')
                ->where('planet_id', $planet->id)
                ->get();
            foreach ($units as $pu) {
                $this->items[] = [
                    'template_id' => $pu->unit->id,
                    'type' => TemplateBuild::TYPE_UNIT,
                    'default_name' => $pu->unit->label ?? $pu->unit->name,
                    'default_icon' => asset('images/units/' . $pu->unit->icon),
                ];
            }

            $ships = PlanetShip::with('ship')
                ->where('planet_id', $planet->id)
                ->get();
            foreach ($ships as $ps) {
                $this->items[] = [
                    'template_id' => $ps->ship->id,
                    'type' => TemplateBuild::TYPE_SHIP,
                    'default_name' => $ps->ship->label ?? $ps->ship->name,
                    'default_icon' => asset('images/ships/' . $ps->ship->icon),
                ];
            }
        }
    }

    public function edit($templateId)
    {
        $this->editing = $templateId;
        $user = Auth::user();
        $override = Schema::hasTable('user_asset_customizations')
            ? UserAssetCustomization::where('user_id', $user->id)
                ->where('template_build_id', $templateId)->first()
            : null;
        $this->form['display_name'] = $override?->display_name ?? '';
        $this->form['icon'] = null;
    }

    public function save()
    {
        $this->validate();
        if (!Schema::hasTable('user_asset_customizations')) {
            $this->dispatch('swal:error', [
                'title' => 'Personnalisation',
                'text' => 'La migration n\'a pas encore été exécutée.'
            ]);
            return;
        }

        $user = Auth::user();
        $override = UserAssetCustomization::firstOrNew([
            'user_id' => $user->id,
            'template_build_id' => $this->editing,
        ]);
        $override->display_name = $this->form['display_name'] ?: null;

        if ($this->form['icon']) {
            $build = TemplateBuild::find($this->editing);
            $typeFolder = match ($build?->type) {
                TemplateBuild::TYPE_UNIT => 'units',
                TemplateBuild::TYPE_SHIP => 'ships',
                default => 'units',
            };

            // Stocker directement dans public/images/customization/{user_id}/{type}
            $baseDir = public_path('images/customization/' . $user->id . '/' . $typeFolder);
            File::ensureDirectoryExists($baseDir);

            $extension = strtolower($this->form['icon']->getClientOriginalExtension() ?: 'png');
            $filename = Str::uuid() . '.' . $extension;
            $targetPath = $baseDir . DIRECTORY_SEPARATOR . $filename;

            // Récupérer le chemin du fichier temporaire Livewire
            $tempPath = $this->form['icon']->getRealPath();
            if (!$tempPath || !File::exists($tempPath)) {
                \Log::error('[Customization] Temp file missing', ['path' => $tempPath]);
                $this->dispatch('swal:error', [
                    'title' => 'Upload',
                    'text' => 'Fichier temporaire introuvable, réessayez.'
                ]);
                return;
            }

            // Tenter un déplacement (plus fiable que la copie), avec repli en copie
            $moved = false;
            try {
                File::move($tempPath, $targetPath);
                $moved = File::exists($targetPath);
            } catch (\Throwable $e) {
                \Log::error('[Customization] Move failed', ['error' => $e->getMessage(), 'from' => $tempPath, 'to' => $targetPath]);
                // ignore and try copy
            }
            if (!$moved) {
                try {
                    File::copy($tempPath, $targetPath);
                } catch (\Throwable $e2) {
                    \Log::error('[Customization] Copy failed', ['error' => $e2->getMessage(), 'from' => $tempPath, 'to' => $targetPath]);
                    $this->dispatch('swal:error', [
                        'title' => 'Upload',
                        'text' => 'Impossible d\'écrire dans le dossier public: ' . $e2->getMessage()
                    ]);
                    return;
                }
            }
            if (!File::exists($targetPath)) {
                \Log::error('[Customization] Target file not found after write', ['path' => $targetPath]);
                $this->dispatch('swal:error', [
                    'title' => 'Upload',
                    'text' => 'Écriture de l\'image échouée, vérifiez les permissions de public/images.'
                ]);
                return;
            }

            // Chemin relatif pour asset()
            $override->icon_path = 'images/customization/' . $user->id . '/' . $typeFolder . '/' . $filename;
        }

        $override->status = 'approved';
        $override->save();

        \Log::info('[Customization] Override saved', ['user_id' => $user->id, 'template_build_id' => $this->editing]);
        $this->dispatch('swal:success', [
            'title' => 'Personnalisation',
            'text' => 'Vos changements ont été enregistrés.'
        ]);

        $this->editing = null;
        $this->form = ['display_name' => '', 'icon' => null];
    }

    public function render()
    {
        return view('livewire.game.customization', [
            'items' => $this->items,
            'editing' => $this->editing,
            'form' => $this->form,
        ]);
    }
}