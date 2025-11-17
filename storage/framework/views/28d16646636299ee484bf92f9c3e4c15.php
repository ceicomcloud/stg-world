<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'label' => null,
    'placeholder' => '',
    'height' => '220px',
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'label' => null,
    'placeholder' => '',
    'height' => '220px',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<div
    class="alpine-editor"
    style="--editor-min-height: <?php echo e($height); ?>;"
    x-data="{ 
        value: <?php if ((object) ($attributes->wire('model')) instanceof \Livewire\WireDirective) : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e($attributes->wire('model')->value()); ?>')<?php echo e($attributes->wire('model')->hasModifier('live') ? '.live' : ''); ?><?php else : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e($attributes->wire('model')); ?>')<?php endif; ?>.live,
        normalize(html) {
            const tmp = document.createElement('div');
            tmp.innerHTML = html || '';
            const allowedExt = ['.png', '.jpg', '.jpeg', '.gif', '.webp', '.svg'];
            tmp.querySelectorAll('img').forEach((img) => {
                const src = (img.getAttribute('src') || '').trim();
                if (!src) { img.remove(); return; }
                if (src.startsWith('data:')) { img.remove(); return; }
                const lower = src.toLowerCase();
                const hasExt = allowedExt.some((ext) => lower.endsWith(ext));
                const isHttp = lower.startsWith('http://') || lower.startsWith('https://');
                const isRelative = lower.startsWith('/');
                if (!(isHttp || isRelative) || !hasExt) { img.remove(); return; }
            });
            return tmp.innerHTML;
        },
        async uploadImage() {
            const f = this.$refs.imageInput.files[0];
            if (!f) return;
            const fd = new FormData();
            fd.append('image', f);
            const res = await fetch('<?php echo e(route('editor.image.store')); ?>', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>' },
                body: fd
            });
            if (!res.ok) { alert('Échec de l\'upload image'); return; }
            const data = await res.json();
            const url = data.url;
            document.execCommand('insertImage', false, url);
            this.value = this.normalize(this.$refs.area.innerHTML);
            this.$refs.imageInput.value = '';
        }
    }"
    x-init="
        const area = $refs.area;
        const sync = () => { value = normalize(area.innerHTML); };
        area.innerHTML = normalize(value || '');
        area.addEventListener('input', sync);
        area.addEventListener('blur', sync);
        area.addEventListener('keyup', sync);
        area.addEventListener('paste', () => setTimeout(sync, 0));
        $watch('value', (v) => { if ((area.innerHTML || '') !== (v || '')) area.innerHTML = normalize(v || ''); });
    "
>
    <?php if($label): ?>
        <label class="block mb-2 text-sm font-medium text-slate-300"><?php echo e($label); ?></label>
    <?php endif; ?>

    <!-- input hors écran relié à Livewire via x-model: valeur toujours envoyée -->
    <input type="text" x-model="value" <?php echo e($attributes->whereStartsWith('wire:model')); ?>

           autocomplete="off"
           style="position:absolute;left:-9999px;width:1px;height:1px;opacity:0;pointer-events:none;" />

    <!-- Toolbar enrichie -->
    <div class="simple-toolbar">
        <!-- Styles de texte -->
        <button type="button" @click.prevent="document.execCommand('bold'); value = $refs.area.innerHTML" title="Gras"><i class="fas fa-bold"></i></button>
        <button type="button" @click.prevent="document.execCommand('italic'); value = $refs.area.innerHTML" title="Italique"><i class="fas fa-italic"></i></button>
        <button type="button" @click.prevent="document.execCommand('underline'); value = $refs.area.innerHTML" title="Souligné"><i class="fas fa-underline"></i></button>
        <button type="button" @click.prevent="document.execCommand('strikeThrough'); value = $refs.area.innerHTML" title="Barré"><i class="fas fa-strikethrough"></i></button>
        <span class="separator"></span>

        <!-- Titres / blocs -->
        <label class="sr-only" for="format-select">Format</label>
        <select id="format-select" class="format-select"
                @change="(()=>{ const t=$event.target.value; document.execCommand('formatBlock', false, t); value = $refs.area.innerHTML; })()">
            <option value="P">Paragraphe</option>
            <option value="H1">Titre 1</option>
            <option value="H2">Titre 2</option>
            <option value="H3">Titre 3</option>
            <option value="BLOCKQUOTE">Citation</option>
            <option value="PRE">Code</option>
        </select>
        <span class="separator"></span>

        <!-- Insérer une image (upload vers disque public) -->
        <button type="button" @click.prevent.stop="$refs.imageInput.click()" title="Insérer une image"><i class="fas fa-image"></i></button>
        <input type="file" x-ref="imageInput" accept="image/*" style="display:none" @change.prevent="uploadImage()">
        
        <!-- Listes -->
        <button type="button" @click.prevent="document.execCommand('insertUnorderedList'); value = $refs.area.innerHTML" title="Liste"><i class="fas fa-list-ul"></i></button>
        <button type="button" @click.prevent="document.execCommand('insertOrderedList'); value = $refs.area.innerHTML" title="Liste numérotée"><i class="fas fa-list-ol"></i></button>
        <span class="separator"></span>

        <!-- Alignement -->
        <button type="button" @click.prevent="document.execCommand('justifyLeft'); value = $refs.area.innerHTML" title="Aligner gauche"><i class="fas fa-align-left"></i></button>
        <button type="button" @click.prevent="document.execCommand('justifyCenter'); value = $refs.area.innerHTML" title="Centrer"><i class="fas fa-align-center"></i></button>
        <button type="button" @click.prevent="document.execCommand('justifyRight'); value = $refs.area.innerHTML" title="Aligner droite"><i class="fas fa-align-right"></i></button>
        <button type="button" @click.prevent="document.execCommand('justifyFull'); value = $refs.area.innerHTML" title="Justifier"><i class="fas fa-align-justify"></i></button>
        <span class="separator"></span>

        <!-- Liens -->
        <button type="button" @click.prevent="(()=>{ const url = prompt('URL du lien:'); if(url) { document.execCommand('createLink', false, url); value = $refs.area.innerHTML; } })()" title="Lien"><i class="fas fa-link"></i></button>
        <button type="button" @click.prevent="document.execCommand('unlink'); value = $refs.area.innerHTML" title="Supprimer lien"><i class="fas fa-unlink"></i></button>
        <span class="separator"></span>

        <!-- Nettoyage -->
        <button type="button" @click.prevent="document.execCommand('removeFormat'); value = $refs.area.innerHTML" title="Effacer format"><i class="fas fa-eraser"></i></button>
    </div>

    <!-- Zone contenteditable avec wire:ignore pour éviter les re-renders Livewire -->
    <div x-ref="area" wire:ignore class="simple-area" contenteditable="true" data-placeholder="<?php echo e($placeholder); ?>"></div>
</div>

<style>
    .alpine-editor { display: block; }
    .simple-toolbar {
        background-color: #0f172a;
        border: 1px solid #334155;
        color: #e2e8f0;
        border-top-left-radius: 8px;
        border-top-right-radius: 8px;
        padding: 6px;
    }
    .simple-toolbar button {
        color: #e2e8f0;
        background: transparent;
        border: none;
        padding: 6px 8px;
        cursor: pointer;
    }
    .simple-toolbar .format-select {
        background: #0b1324;
        color: #e2e8f0;
        border: 1px solid #334155;
        border-radius: 6px;
        padding: 4px 8px;
        margin-right: 6px;
    }
    .simple-toolbar .color-label {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 0 6px;
        font-size: 12px;
    }
    .simple-toolbar .color-label input[type=color] {
        width: 24px;
        height: 24px;
        padding: 0;
        border: none;
        background: transparent;
        cursor: pointer;
    }
    .simple-toolbar .separator { display: inline-block; width: 1px; height: 20px; background: #334155; margin: 0 6px; }

    .simple-area {
        border: 1px solid #334155;
        border-top: none;
        border-bottom-left-radius: 8px;
        border-bottom-right-radius: 8px;
        background-color: #0b1324;
        color: #e2e8f0;
        min-height: var(--editor-min-height, 200px);
        padding: 12px 14px;
    }
    .simple-area:empty:before { content: attr(data-placeholder); color: #94a3b8; }
    .alpine-editor:focus-within .simple-toolbar { border-color: #60a5fa; box-shadow: 0 0 0 1px #60a5fa; }
    .alpine-editor:focus-within .simple-area { border-color: #60a5fa; }
</style>

<script>
    document.addEventListener('alpine:init', () => {
        // Améliore le support glisser-déposer et collage d'images sur toutes les instances
        Alpine.data('dummy', () => ({})); // noop pour s'assurer qu'Alpine est chargé
    });
    // Attache les handlers au prochain tick après init du composant
    queueMicrotask(() => {
        document.querySelectorAll('.alpine-editor .simple-area').forEach(area => {
            // Glisser-déposer d'images
            area.addEventListener('dragover', e => { e.preventDefault(); });
            area.addEventListener('drop', e => {
                e.preventDefault();
                const files = Array.from((e.dataTransfer && e.dataTransfer.files) || []);
                if (files.some(f => f.type && f.type.startsWith('image/'))) {
                    alert('Glisser-déposer d\'images locales désactivé. Utilisez une URL publique.');
                }
            });
            // Collage d'images depuis presse-papiers
            area.addEventListener('paste', e => {
                const items = (e.clipboardData && e.clipboardData.items) ? Array.from(e.clipboardData.items) : [];
                if (items.some(item => item.kind === 'file' && item.type && item.type.startsWith('image/'))) {
                    e.preventDefault();
                    alert('Collage d\'images locales désactivé. Hébergez l\'image et insérez son URL.');
                }
            });
        });
    });
</script><?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/components/input/tinymce.blade.php ENDPATH**/ ?>