<x-layouts.app :title="'Paiement annulé'">
    <div class="paypal-page" style="max-width: 720px; margin: 40px auto; padding: 24px;">
        <div class="paypal-card" style="border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; text-align: center; background: #ffffff;">
            <div style="font-size: 48px; color: #ef4444; margin-bottom: 12px;">
                <i class="fas fa-times-circle" aria-hidden="true"></i>
            </div>
            <h1 style="font-size: 24px; margin-bottom: 8px;">Paiement annulé</h1>
            <p style="color: #6b7280; margin-bottom: 20px;">Votre paiement a été annulé. Vous pouvez réessayer depuis la page Commerce.</p>
            <a href="{{ route('game.trade') }}" style="display: inline-flex; align-items: center; gap: 8px; background: #374151; color: #fff; padding: 10px 16px; border-radius: 8px; text-decoration: none;">
                <i class="fas fa-exchange-alt" aria-hidden="true"></i>
                Retour au Commerce
            </a>
        </div>
    </div>
</x-layouts.app>