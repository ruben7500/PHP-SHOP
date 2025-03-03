<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>L'élégance au poignet | Montres de Luxe</title>
    <!-- Tailwind CSS via CDN -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Playfair Display', serif;
        }
        .transition {
            transition: all 0.3s ease;
        }
        .hero-overlay {
            background-color: rgba(0, 0, 0, 0.4);
        }
        .brand-image:hover {
            opacity: 1 !important;
        }
    </style>
</head>
<body class="antialiased text-gray-800">

    <!-- Header / Navigation -->
    <?php include 'includes/header.php'; ?>

    <!-- Hero Banner -->
    <section class="relative bg-gray-900">
        <div class="relative h-screen max-h-[800px] overflow-hidden">
            <img src="https://images.unsplash.com/photo-1587836374828-4dbafa94cf0e?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80" 
                 class="absolute inset-0 w-full h-full object-cover object-center" alt="Luxury Watch Collection">
            <div class="absolute inset-0 hero-overlay"></div>
            
            <div class="container mx-auto px-4 h-full flex items-center relative z-1">
                <div class="max-w-2xl">
                    <span class="inline-block px-3 py-1 bg-blue-600 text-white text-sm font-semibold tracking-wider uppercase mb-6 rounded">Collection Exclusive</span>
                    <h1 class="text-5xl md:text-6xl font-bold text-white leading-tight mb-6">L'élégance au poignet</h1>
                    <p class="text-xl text-white opacity-90 mb-8">Découvrez notre collection exclusive de montres de luxe qui allient tradition horlogère et design contemporain.</p>
                    
                    <div class="flex flex-wrap gap-4">
                        <a href="catalog.php" class="px-8 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition shadow-lg">
                            Explorer la collection
                        </a>
                        <a href="about.php" class="px-8 py-3 bg-transparent border-2 border-white text-white font-medium rounded-lg hover:bg-white hover:text-gray-900 transition">
                            Notre histoire
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="py-24 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold mb-4">Collections en vedette</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">Des pièces d'exception sélectionnées pour vous</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php
                // Récupérer les produits en vedette (les 3 plus récents avec stock > 0)
                $stmt = $conn->query("SELECT i.*, s.quantite FROM items i 
                                      JOIN stock s ON i.id = s.id_item 
                                      WHERE s.quantite > 0 
                                      ORDER BY i.date_publication DESC LIMIT 3");
                $featured_products = $stmt->fetchAll();
                
                foreach ($featured_products as $product):
                    // Déterminer le badge à afficher
                    $badge_text = "";
                    $badge_color = "";
                    
                    if (strtotime($product['date_publication']) > strtotime('-30 days')) {
                        $badge_text = "Nouveauté";
                        $badge_color = "bg-blue-600";
                    } elseif ($product['prix'] > 3000) {
                        $badge_text = "Premium";
                        $badge_color = "bg-purple-600";
                    } elseif ($product['quantite'] < 5) {
                        $badge_text = "Stock limité";
                        $badge_color = "bg-red-600";
                    } else {
                        $badge_text = "Best-seller";
                        $badge_color = "bg-green-600";
                    }
                ?>
                <div class="bg-white rounded-xl shadow-lg overflow-hidden transition transform hover:-translate-y-2 hover:shadow-xl">
                    <div class="relative">
                        <img src="<?= !empty($product['image']) ? $product['image'] : 'assets/images/placeholder.jpg' ?>" 
                             class="w-full h-80 object-cover" alt="<?= htmlspecialchars($product['nom']) ?>">
                        <span class="absolute top-4 right-4 <?= $badge_color ?> text-white px-3 py-1 rounded-full text-sm font-medium">
                            <?= $badge_text ?>
                        </span>
                    </div>
                    
                    <div class="p-6 text-center">
                        <h3 class="text-2xl font-bold mb-2"><?= htmlspecialchars($product['nom']) ?></h3>
                        <p class="text-gray-600 mb-4"><?= htmlspecialchars($product['marque']) ?></p>
                        <p class="text-blue-600 text-2xl font-bold mb-6"><?= number_format($product['prix'], 2, ',', ' ') ?> €</p>
                        <a href="product.php?id=<?= $product['id'] ?>" class="inline-block px-6 py-3 border-2 border-gray-900 text-gray-900 font-medium rounded-lg hover:bg-gray-900 hover:text-white transition">
                            Voir le détail
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="py-24 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold mb-4">Ce que disent nos clients</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">Des témoignages authentiques de passionnés d'horlogerie</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Testimonial 1 -->
                <div class="bg-gray-50 p-8 rounded-xl shadow-lg">
                    <div class="flex text-yellow-400 mb-4">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    
                    <p class="text-gray-700 italic mb-8">"Je cherchais une montre élégante pour les occasions spéciales. Le Chronographe Élégance est parfait, il attire tous les regards et sa précision est remarquable. Un investissement que je ne regrette pas."</p>
                    
                    <div class="flex items-center">
                        <img src="https://randomuser.me/api/portraits/men/54.jpg" alt="Client" class="w-12 h-12 rounded-full mr-4">
                        <div>
                            <h4 class="font-bold">Pierre Durand</h4>
                            <p class="text-gray-600 text-sm">Directeur commercial</p>
                        </div>
                    </div>
                </div>
                
                <!-- Testimonial 2 -->
                <div class="bg-gray-50 p-8 rounded-xl shadow-lg">
                    <div class="flex text-yellow-400 mb-4">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    
                    <p class="text-gray-700 italic mb-8">"J'ai offert une Aqua Profondeur à mon mari pour notre anniversaire de mariage. Il est ravi et ne la quitte plus, même pour nager. Un achat que je ne regrette absolument pas."</p>
                    
                    <div class="flex items-center">
                        <img src="https://randomuser.me/api/portraits/women/28.jpg" alt="Client" class="w-12 h-12 rounded-full mr-4">
                        <div>
                            <h4 class="font-bold">Sophie Martin</h4>
                            <p class="text-gray-600 text-sm">Architecte d'intérieur</p>
                        </div>
                    </div>
                </div>
                
                <!-- Testimonial 3 -->
                <div class="bg-gray-50 p-8 rounded-xl shadow-lg">
                    <div class="flex text-yellow-400 mb-4">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                    </div>
                    
                    <p class="text-gray-700 italic mb-8">"En tant que collectionneur, je suis extrêmement exigeant. Luxury Watches a su me conseiller et me proposer des pièces rares qui correspondent parfaitement à mes attentes. Un service haut de gamme."</p>
                    
                    <div class="flex items-center">
                        <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Client" class="w-12 h-12 rounded-full mr-4">
                        <div>
                            <h4 class="font-bold">Thomas Leroy</h4>
                            <p class="text-gray-600 text-sm">Investisseur</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Choose Us -->
    <section class="py-24 bg-gray-900 text-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold mb-4">Pourquoi nous choisir</h2>
                <p class="text-xl text-gray-300 max-w-3xl mx-auto">L'excellence horlogère à votre service</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
                <!-- Feature 1 -->
                <div class="text-center p-6">
                    <div class="bg-blue-600 bg-opacity-20 rounded-full w-24 h-24 flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-check-circle text-blue-500 text-4xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-4">Authenticité garantie</h3>
                    <p class="text-gray-400">Chaque montre est méticuleusement vérifiée et authentifiée par nos experts certifiés.</p>
                </div>
                
                <!-- Feature 2 -->
                <div class="text-center p-6">
                    <div class="bg-blue-600 bg-opacity-20 rounded-full w-24 h-24 flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-shipping-fast text-blue-500 text-4xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-4">Livraison sécurisée</h3>
                    <p class="text-gray-400">Livraison express et assurée pour chaque commande, avec suivi en temps réel.</p>
                </div>
                
                <!-- Feature 3 -->
                <div class="text-center p-6">
                    <div class="bg-blue-600 bg-opacity-20 rounded-full w-24 h-24 flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-undo text-blue-500 text-4xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-4">Satisfaction garantie</h3>
                    <p class="text-gray-400">30 jours pour changer d'avis, avec retour gratuit et remboursement intégral.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Newsletter -->
    <section class="py-24 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="max-w-3xl mx-auto text-center">
                <h2 class="text-3xl font-bold mb-6">Restez informé de nos nouveautés</h2>
                <p class="text-gray-600 mb-8">Inscrivez-vous à notre newsletter pour être le premier à découvrir nos nouvelles collections et offres exclusives.</p>
                
                <form class="flex flex-col sm:flex-row gap-4 max-w-lg mx-auto">
                    <input type="email" class="flex-grow px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-600 focus:border-blue-600 focus:outline-none" placeholder="Votre adresse email" required>
                    <button type="submit" class="px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition">S'inscrire</button>
                </form>
            </div>
        </div>
    </section>



    <?php include 'includes/footer.php'; ?>
</body>
</html>