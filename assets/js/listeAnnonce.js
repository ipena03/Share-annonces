document.addEventListener("DOMContentLoaded", function() {
    const filterForm = document.getElementById("filterForm");
    if (filterForm) {
        filterForm.addEventListener("change", function(event) {
            event.preventDefault(); // Empêcher la soumission traditionnelle du formulaire

            // Récupérer les valeurs des filtres
            const type = document.querySelector('select[name="type"]').value;
            const sortBy = document.querySelector('select[name="sort_by"]').value;
            const sortOrder = document.querySelector('select[name="sort_order"]').value;

            // Envoyer la requête AJAX
            fetch(`{{ path('app_liste-annonces') }}?type=${type}&sort_by=${sortBy}&sort_order=${sortOrder}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.annonces) {
                    // Mettre à jour la liste des annonces dans le DOM
                    document.getElementById("annoncesList").innerHTML = data.annonces;
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
            });
        });
    }
});
