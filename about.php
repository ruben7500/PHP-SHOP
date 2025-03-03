<?php include 'includes/header.php'; ?>

<div class="container mx-auto px-4 py-4">
    <h1 class="text-2xl font-bold mb-4">Qui sommes-nous ?</h1>
    
    <div class="flex flex-col md:flex-row">
        <div class="w-full md:w-1/2">
            <img src="assets/images/about-us.jpg" alt="Notre boutique" class="w-full rounded mb-4">
        </div>
        <div class="w-full md:w-1/2">
            <h2 class="text-xl font-semibold">Notre histoire</h2>
            <p>Fondée en 2015, Luxury Watches est née de la passion de deux amis passionnés d'horlogerie. Après des années passées à travailler dans l'industrie du luxe, nous avons décidé de créer notre propre boutique en ligne pour partager notre passion avec le plus grand nombre.</p>
            
            <p>Notre objectif : rendre accessible les plus belles pièces d'horlogerie tout en garantissant l'authenticité et la qualité de chaque montre proposée.</p>
            
            <h2 class="text-xl font-semibold mt-4">Notre engagement</h2>
            <p>Chez Luxury Watches, nous nous engageons à :</p>
            <ul class="list-disc pl-5">
                <li>Proposer uniquement des montres authentiques et certifiées</li>
                <li>Offrir un service client de qualité avant, pendant et après l'achat</li>
                <li>Garantir les meilleurs prix pour des montres d'exception</li>
                <li>Livrer rapidement et en toute sécurité vos précieuses acquisitions</li>
            </ul>
        </div>
    </div>
    
    <div class="mt-12">
        <div class="w-full">
            <h2 class="text-xl font-semibold text-center mb-4">Notre équipe</h2>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="mb-4">
                <div class="border rounded shadow text-center">
                    <img src="assets/images/team-1.jpg" class="w-full h-48 object-cover rounded-t" alt="Pierre Durand">
                    <div class="p-4">
                        <h5 class="text-lg font-semibold">Pierre Durand</h5>
                        <p class="text-gray-500">Co-fondateur & Directeur</p>
                        <p class="mt-2">Passionné d'horlogerie depuis plus de 20 ans, Pierre a travaillé pour les plus grandes maisons avant de fonder Luxury Watches.</p>
                    </div>
                </div>
            </div>
            
            <div class="mb-4">
                <div class="border rounded shadow text-center">
                    <img src="assets/images/team-2.jpg" class="w-full h-48 object-cover rounded-t" alt="Sophie Martin">
                    <div class="p-4">
                        <h5 class="text-lg font-semibold">Sophie Martin</h5>
                        <p class="text-gray-500">Co-fondatrice & Responsable Marketing</p>
                        <p class="mt-2">Avec son expertise en marketing digital et sa passion pour les belles montres, Sophie veille à vous offrir la meilleure expérience en ligne.</p>
                    </div>
                </div>
            </div>
            
            <div class="mb-4">
                <div class="border rounded shadow text-center">
                    <img src="assets/images/team-3.jpg" class="w-full h-48 object-cover rounded-t" alt="Thomas Leroy">
                    <div class="p-4">
                        <h5 class="text-lg font-semibold">Thomas Leroy</h5>
                        <p class="text-gray-500">Expert Horloger</p>
                        <p class="mt-2">Fort de ses 15 ans d'expérience dans la réparation et l'authentification de montres de luxe, Thomas est notre expert technique.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>