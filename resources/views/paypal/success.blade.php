<x-layouts.app :title="'Paiement réussi'">
    <div class="paypal-page" style="max-width: 720px; margin: 40px auto; padding: 24px;">
        <div class="paypal-card" style="border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; text-align: center; background: #ffffff;">
            <div style="font-size: 48px; color: #16a34a; margin-bottom: 12px;">
                <i class="fas fa-check-circle" aria-hidden="true"></i>
            </div>
            <h1 style="font-size: 24px; margin-bottom: 8px;">Paiement réussi</h1>
            <p style="color: #6b7280; margin-bottom: 20px;">Votre paiement PayPal a été validé. Vous pouvez retourner au Commerce.</p>
            <a href="{{ route('game.trade') }}" style="display: inline-flex; align-items: center; gap: 8px; background: #2563eb; color: #fff; padding: 10px 16px; border-radius: 8px; text-decoration: none;">
                <i class="fas fa-exchange-alt" aria-hidden="true"></i>
                Retour au Commerce
            </a>
        </div>
    </div>
</x-layouts.app>