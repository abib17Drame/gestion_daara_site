/**
 * Fonction de recherche d'élèves dans les tableaux et les listes
 * La recherche ne se fait que sur le prénom et le nom
 */
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            // Si le champ de recherche est vide, on affiche tous les éléments
            if (this.value.trim() === '') {
                showAllElements();
                return;
            }

            const searchText = this.value.toLowerCase().trim();

            // Filtrer les tableaux et les éléments de liste
            filterAll(searchText);
        });
    }

    // Fonction pour afficher tous les éléments (tableaux et listes)
    function showAllElements() {
        // Afficher toutes les lignes de tableaux
        document.querySelectorAll('table tbody tr').forEach(row => {
            row.style.display = '';
        });

        // Afficher tous les éléments de liste (pour mobile)
        document.querySelectorAll('.list-group-item, .student-item').forEach(item => {
            item.style.display = '';
        });
    }

    // Fonction pour filtrer tous les éléments (tableaux et listes)
    function filterAll(searchText) {
        // 1. Filtrer les tableaux
        filterTables(searchText);

        // 2. Filtrer les éléments de liste pour mobile
        filterListItems(searchText);
    }

    // Fonction pour filtrer les tableaux
    function filterTables(searchText) {
        const allTables = document.querySelectorAll('table');

        allTables.forEach(table => {
            const rows = table.querySelectorAll('tbody tr');

            rows.forEach(row => {
                let found = false;

                // Chercher dans les cellules avec attributs data-prenom et data-nom
                const prenomCell = row.querySelector('[data-prenom]');
                const nomCell = row.querySelector('[data-nom]');

                if (prenomCell && nomCell) {
                    // Utiliser les attributs data-* qui contiennent les valeurs exactes
                    const prenom = prenomCell.getAttribute('data-prenom').toLowerCase();
                    const nom = nomCell.getAttribute('data-nom').toLowerCase();

                    if (prenom.includes(searchText) || nom.includes(searchText)) {
                        found = true;
                    }
                } else {
                    // Solution de secours: chercher dans les cellules de prénom et nom
                    const cells = row.querySelectorAll('td');
                    if (cells.length >= 3) {
                        const prenom = cells[1] ? cells[1].textContent.toLowerCase() : '';
                        const nom = cells[2] ? cells[2].textContent.toLowerCase() : '';

                        if (prenom.includes(searchText) || nom.includes(searchText)) {
                            found = true;
                        }
                    }
                }

                // Afficher ou masquer la ligne selon le résultat
                row.style.display = found ? '' : 'none';
            });
        });
    }

    // Fonction pour filtrer les éléments de liste (sur mobile)
    function filterListItems(searchText) {
        const listItems = document.querySelectorAll('.list-group-item, .student-item');

        listItems.forEach(item => {
            let found = false;

            // Chercher uniquement dans les éléments avec attributs data-prenom et data-nom
            const elemWithPrenom = item.querySelector('[data-prenom]');
            const elemWithNom = item.querySelector('[data-nom]');

            if (elemWithPrenom && elemWithNom) {
                const prenom = elemWithPrenom.getAttribute('data-prenom').toLowerCase();
                const nom = elemWithNom.getAttribute('data-nom').toLowerCase();

                found = prenom.includes(searchText) || nom.includes(searchText);
            } else {
                // Solution de secours : chercher dans le titre h5 qui contient le nom complet
                const nameHeading = item.querySelector('h5');
                if (nameHeading) {
                    const fullName = nameHeading.textContent.toLowerCase();
                    found = fullName.includes(searchText);
                }
            }

            // Afficher ou masquer l'élément selon le résultat
            item.style.display = found ? '' : 'none';
        });
    }
});