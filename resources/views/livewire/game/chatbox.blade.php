<div page="chatbox">
    <!-- En-tête de la chatbox -->
    <div class="chatbox-header">
        <h3 class="chatbox-title">
            <i class="fas fa-comments"></i>
            Chat Global
        </h3>
        
        <!-- Sélecteur de canal -->
        <div class="channel-selector">
            <button 
                wire:click="switchChannel('general')" 
                class="channel-btn {{ $channel === 'general' ? 'active' : '' }}"
            >
                <i class="fas fa-globe"></i> Général
            </button>
            @if($currentUser && $currentUser->alliance_id)
                <button 
                    wire:click="switchChannel('alliance')" 
                    class="channel-btn {{ $channel === 'alliance' ? 'active' : '' }}"
                >
                    <i class="fas fa-shield-alt"></i> Alliance
                </button>
            @endif
        </div>
    </div>

    <!-- Zone des messages -->
    <div class="chatbox-messages" id="chatbox-messages" wire:poll.2s="loadMessages" style="max-height: 420px; overflow-y: auto;">
        @if(empty($messages))
            <div class="no-messages">
                <i class="fas fa-comment-slash"></i>
                <p>Aucun message dans ce canal. Soyez le premier à écrire !</p>
            </div>
        @else
            @foreach($messages as $msg)
                <div class="message {{ $msg['is_system_message'] ? 'system-message' : 'user-message' }}">
                    @if(!$msg['is_system_message'])
                        <div class="message-header">
                            @if(isset($msg['user']['role']))
                                @php
                                    $userModel = \App\Models\User::find($msg['user']['id']);
                                @endphp
                                @if($userModel)
                                    <span class="user-role role-{{ $msg['user']['role'] }}">
                                        <i class="{{ $userModel->getRoleIcon() }}"></i>
                                        {{ strtoupper($userModel->getRoleName()) }}
                                    </span>
                                @endif
                            @endif
                            <span class="username {{ ($userModel && ($userModel->vip_active ?? false) && ($userModel->vip_badge_enabled ?? true)) ? 'vip-frame' : '' }}">{{ $msg['user']['name'] ?? 'Utilisateur supprimé' }}</span>
                            <span class="timestamp">{{ \Carbon\Carbon::parse($msg['created_at'])->format('H:i') }}</span>
                        </div>
                    @endif
                    <div class="message-content">
                        {!! $msg['message'] !!}
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    <!-- Zone de saisie -->
    <div class="chatbox-input" x-data="chatFormatter()">
        <!-- Barre d'outils de formatage -->
        <div class="formatting-toolbar">
            <div class="emoji-picker-container">
                <button type="button" class="format-btn" @click.stop="toggleEmojiPicker()" title="Emojis">
                    <i class="fas fa-smile"></i>
                </button>
                <div class="emoji-picker" x-show="showEmojiPicker" x-transition>
                    <div class="emoji-grid">
                        <span class="emoji-option" @click="insertEmoji('😀')">😀</span>
                        <span class="emoji-option" @click="insertEmoji('😂')">😂</span>
                        <span class="emoji-option" @click="insertEmoji('😍')">😍</span>
                        <span class="emoji-option" @click="insertEmoji('🤔')">🤔</span>
                        <span class="emoji-option" @click="insertEmoji('😎')">😎</span>
                        <span class="emoji-option" @click="insertEmoji('😢')">😢</span>
                        <span class="emoji-option" @click="insertEmoji('😡')">😡</span>
                        <span class="emoji-option" @click="insertEmoji('👍')">👍</span>
                        <span class="emoji-option" @click="insertEmoji('👎')">👎</span>
                        <span class="emoji-option" @click="insertEmoji('❤️')">❤️</span>
                        <span class="emoji-option" @click="insertEmoji('🔥')">🔥</span>
                        <span class="emoji-option" @click="insertEmoji('⭐')">⭐</span>
                    </div>
                </div>
            </div>
            <button type="button" class="format-btn" @click="toggleFormat('bold')" :class="{ 'active': format.bold }" title="Gras">
                <i class="fas fa-bold"></i>
            </button>
            <button type="button" class="format-btn" @click="toggleFormat('italic')" :class="{ 'active': format.italic }" title="Italique">
                <i class="fas fa-italic"></i>
            </button>
            <div class="color-picker-container">
                <button type="button" class="format-btn" @click.stop="toggleColorPicker()" title="Couleur" :style="{ color: format.color }">
                    <i class="fas fa-palette"></i>
                </button>
                <div class="color-picker" x-show="showColorPicker" x-transition>
                    <div class="color-option" style="background: #ff4444;" @click="setTextColor('#ff4444')"></div>
                    <div class="color-option" style="background: #44ff44;" @click="setTextColor('#44ff44')"></div>
                    <div class="color-option" style="background: #4444ff;" @click="setTextColor('#4444ff')"></div>
                    <div class="color-option" style="background: #ffff44;" @click="setTextColor('#ffff44')"></div>
                    <div class="color-option" style="background: #ff44ff;" @click="setTextColor('#ff44ff')"></div>
                    <div class="color-option" style="background: #44ffff;" @click="setTextColor('#44ffff')"></div>
                    <div class="color-option" style="background: #ffffff;" @click="setTextColor('#ffffff')"></div>
                    <div class="color-option" style="background: #888888;" @click="setTextColor('#888888')"></div>
                </div>
            </div>

        </div>
        
        <form class="message-form">
            <div class="input-group">
                <input 
                    type="text" 
                    wire:model="message" 
                    placeholder="Tapez votre message..."
                    class="form-control"
                    id="messageInput"
                    maxlength="500"
                    autocomplete="off"
                >
                <button type="button" class="btn btn-primary" @click="sendFormattedMessage()">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
            @error('message')
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    {{ $message }}
                </div>
            @enderror
        </form>
    </div>

    <!-- Indicateur de frappe (pour future implémentation) -->
    <div class="typing-indicator" style="display: none;">
        <span class="typing-dots">
            <span></span>
            <span></span>
            <span></span>
        </span>
        <span class="typing-text">Quelqu'un est en train d'écrire...</span>
    </div>
</div>

@push('scripts')
<script>
    function chatFormatter() {
        return {
            format: {
                bold: false,
                italic: false,
                color: null
            },
            showColorPicker: false,
            showEmojiPicker: false,
            
            init() {
                // Auto-scroll et événements Livewire
                this.setupLivewireEvents();
                
                // Écouter les clics extérieurs pour fermer les pickers
                document.addEventListener('click', (e) => {
                    if (!e.target.closest('.color-picker-container') && !e.target.closest('.emoji-picker-container')) {
                        this.showColorPicker = false;
                        this.showEmojiPicker = false;
                    }
                });
                
                // Gérer l'envoi avec Enter
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter' && e.target.classList.contains('message-input')) {
                        e.preventDefault();
                        this.sendFormattedMessage();
                    }
                });
                
                // Intercepter la soumission du formulaire
                const form = document.querySelector('.message-form');
                if (form) {
                    form.addEventListener('submit', (e) => {
                        e.preventDefault();
                        this.sendFormattedMessage();
                    });
                }
            },
            
            setupLivewireEvents() {
                document.addEventListener('livewire:initialized', () => {
                    const scrollToTop = () => {
                        const messagesContainer = document.getElementById('chatbox-messages');
                        if (messagesContainer) {
                            messagesContainer.scrollTop = 0;
                        }
                    };
                    
                    // Scroll automatique
                    Livewire.hook('morph.updated', () => {
                        setTimeout(scrollToTop, 100);
                    });
                    
                    setTimeout(scrollToTop, 100);
                    
                    // Événements de messages
                    Livewire.on('message-sent', (data) => {
                        Livewire.dispatch('message-received', data);
                        // Réinitialiser le formatage après envoi
                        this.resetFormat();
                        // Focus sur l'input
                        setTimeout(() => {
                            const input = document.querySelector('.message-input');
                            if (input) input.focus();
                        }, 100);
                    });
                });
            },
            
            toggleFormat(type) {
                this.format[type] = !this.format[type];
                this.applyFormatting();
            },
            
            toggleColorPicker() {
                this.showEmojiPicker = false;
                this.showColorPicker = !this.showColorPicker;
            },
            
            toggleEmojiPicker() {
                this.showColorPicker = false;
                this.showEmojiPicker = !this.showEmojiPicker;
            },
            
            setTextColor(color) {
                this.format.color = color;
                this.showColorPicker = false;
                this.applyFormatting();
            },
            
            insertEmoji(emoji) {
                const input = document.getElementById('messageInput');
                if (!input) return;
                
                const currentValue = input.value;
                const cursorPos = input.selectionStart;
                
                const newValue = currentValue.slice(0, cursorPos) + emoji + currentValue.slice(cursorPos);
                input.value = newValue;
                
                // Mettre à jour Livewire
                input.dispatchEvent(new Event('input', { bubbles: true }));
                
                // Repositionner le curseur
                const newPos = cursorPos + emoji.length;
                setTimeout(() => {
                    input.setSelectionRange(newPos, newPos);
                    input.focus();
                }, 0);
                
                this.showEmojiPicker = false;
            },
            
            applyFormatting() {
                const input = document.getElementById('messageInput');
                if (!input) return;
                
                let styles = [];
                
                if (this.format.bold) {
                    styles.push('font-weight: bold');
                }
                
                if (this.format.italic) {
                    styles.push('font-style: italic');
                }
                
                if (this.format.color) {
                    styles.push(`color: ${this.format.color}`);
                }
                
                input.style.cssText = styles.join('; ');
            },
            
            sendFormattedMessage() {
                // Envoyer les informations de formatage à Livewire
                this.$wire.set('messageFormat', this.format);
                
                // Envoyer le message
                this.$wire.call('sendMessage');
            },
            
            resetFormat() {
                this.format = {
                    bold: false,
                    italic: false,
                    color: null
                };
                this.showColorPicker = false;
                this.showEmojiPicker = false;
                
                // Réinitialiser le style de l'input
                const input = document.getElementById('messageInput');
                if (input) {
                    input.style.cssText = '';
                }
            }
        }
    }
</script>
@endpush
