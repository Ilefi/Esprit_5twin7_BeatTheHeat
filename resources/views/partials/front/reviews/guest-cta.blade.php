<div class="nt-card flex flex-col items-center gap-3 p-6 text-center sm:flex-row sm:text-left">
    <span class="grid h-12 w-12 shrink-0 place-items-center rounded-full bg-gold/25 text-gold-strong"><i class="fa-solid fa-star" aria-hidden="true"></i></span>
    <div class="flex-1">
        <p class="font-semibold">Vous avez goûté ce produit ?</p>
        <p class="text-sm text-muted-foreground">Connectez-vous pour donner votre avis et aider la communauté.</p>
    </div>
    <x-nt.button :href="route('login')" icon="fa-right-to-bracket">Se connecter</x-nt.button>
</div>
